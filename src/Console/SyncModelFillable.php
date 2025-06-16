<?php

namespace Muzammal\Syncmodelfillable\Console;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class SyncModelFillable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fillable {name} {--path=} {--ignore=} {--dry-run} {--guarded} {--from-schema}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync model fillable or guarded fields with migration columns or database schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $ignore = $this->option('ignore');
        $ignoreList = $ignore ? explode(',', $ignore) : [];

        // Ensure the name argument is provided
        if (!$name) {
            $this->error("Error: Missing argument. You must specify a model name or use 'all'.");
            return Command::FAILURE;
        }

        // Determine the directory to scan
        $customPath = $this->option('path');
        $basePath = $customPath ? base_path($customPath) : app_path('Models');

        // Check if the directory exists before proceeding
        if (!is_dir($basePath)) {
            $this->error("Error: The directory '{$basePath}' does not exist.");
            return Command::FAILURE;
        }

        if (Str::lower($name) === 'all') {
            $this->updateAllModels($basePath, $ignoreList);
        } else {
            if (in_array($name, $ignoreList)) {
                $this->warn("Skipping ignored model: {$name}");
                return Command::SUCCESS;
            }

            $this->updateSingleModel($name, $basePath);
        }

        return Command::SUCCESS;
    }

    /**
     * Update fillable/guarded fields for all models in a directory (recursively).
     */
    protected function updateAllModels($directory, array $ignoreList)
    {
        // Only include .php files, exclude .backup files
        $modelFiles = collect(File::allFiles($directory))
            ->filter(fn($file) => $file->getExtension() === 'php' && !Str::endsWith($file->getFilename(), '.backup'));

        $this->output->progressStart(count($modelFiles));

        foreach ($modelFiles as $modelFile) {
            $modelName = $modelFile->getFilenameWithoutExtension();

            if (in_array($modelName, $ignoreList)) {
                $this->warn("Skipping {$modelName} model as it is in the ignore list.");
                $this->output->progressAdvance();
                continue;
            }

            $this->updateSingleModel($modelName, $directory);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    /**
     * Update the fillable/guarded fields for a single model.
     */
    protected function updateSingleModel($name, $directory)
    {
        $modelName = ucfirst($name);
        $modelFiles = collect(File::allFiles($directory))
            ->filter(fn($file) => $file->getExtension() === 'php' && !Str::endsWith($file->getFilename(), '.backup'));

        $modelFile = $modelFiles->first(fn($file) => $file->getFilenameWithoutExtension() === $modelName);

        if (!$modelFile) {
            $this->error("Model {$modelName} does not exist in {$directory}.");
            return;
        }

        $modelPath = $modelFile->getPathname();
        $className = $this->getClassNameFromPath($modelPath);

        // Validate that the file is a valid Eloquent model
        if (!$this->isValidModelFile($modelPath, $className)) {
            $this->warn("{$modelName} is not a valid Eloquent model. Skipping.");
            return;
        }

        // Get table name and connection
        $tableInfo = $this->getModelTableName($modelPath, $modelName);
        $tableName = $tableInfo['table'];
        $connection = $tableInfo['connection'];

        // Get columns from schema or migrations
        if ($this->option('from-schema')) {
            $columns = $this->getColumnsFromSchema($tableName, $connection);
        } else {
            $migrationFiles = $this->getMigrationFilesByTableName($tableName);
            $columns = !empty($migrationFiles) ? $this->extractColumnsFromMigration($migrationFiles) : [];
        }

        if ($columns) {
            // Log the intended change
            $this->logChange($modelPath, $columns, 'before');

            if ($this->option('dry-run')) {
                $property = $this->option('guarded') ? 'guarded' : 'fillable';
                $this->info("Dry run: Would update {$modelPath} with {$property}: ['" . implode("', '", $columns) . "']");
            } else {
                $this->updateModelFillable($modelPath, $columns);
                $this->info("Updated " . ($this->option('guarded') ? 'guarded' : 'fillable') . " fields for {$modelName} model.");
                $this->logChange($modelPath, $columns, 'after');
            }
        } else {
            $this->warn("No columns found for table '{$tableName}' for model '{$modelName}'.");
        }
    }

    /**
     * Validate that a file is a valid Eloquent model.
     */
    protected function isValidModelFile($modelPath, $className)
    {
        if (!File::exists($modelPath)) {
            return false;
        }

        // Use a unique include to avoid redeclaration
        $included = include_once $modelPath;

        if ($included === false) {
            return false;
        }

        return class_exists($className) && is_subclass_of($className, \Illuminate\Database\Eloquent\Model::class);
    }

    /**
     * Retrieve the table name and connection from the model.
     */
    protected function getModelTableName($modelPath, $modelName)
    {
        $table = Str::snake(Str::plural($modelName));
        $connection = config('database.default');

        if (File::exists($modelPath)) {
            $className = $this->getClassNameFromPath($modelPath);
            if ($this->isValidModelFile($modelPath, $className)) {
                $reflection = new ReflectionClass($className);
                $instance = $reflection->newInstance();

                // Detect SoftDeletes trait and exclude 'deleted_at'
                $traits = class_uses_recursive($className);
                if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, $traits)) {
                    $excludedColumns = config('syncfillable.excluded_columns', []);
                    if (!in_array('deleted_at', $excludedColumns)) {
                        config(['syncfillable.excluded_columns' => array_merge($excludedColumns, ['deleted_at'])]);
                    }
                }

                // Get connection name
                if ($reflection->hasProperty('connection')) {
                    $property = $reflection->getProperty('connection');
                    $property->setAccessible(true);
                    $connection = $property->getValue($instance) ?? $connection;
                }

                // Get table name
                if ($reflection->hasProperty('table')) {
                    $property = $reflection->getProperty('table');
                    $property->setAccessible(true);
                    $table = $property->getValue($instance) ?? $table;
                }
            }
        }

        return ['table' => $table, 'connection' => $connection];
    }

    /**
     * Get migration files related to the table name.
     */
    protected function getMigrationFilesByTableName($tableName)
    {
        $migrationFiles = File::allFiles(database_path('migrations'));
        $relatedMigrations = collect($migrationFiles)->filter(function ($file) use ($tableName) {
            $content = File::get($file);
            return Str::contains($content, "'{$tableName}'") || Str::contains($file->getFilename(), $tableName);
        });

        return $relatedMigrations->sortBy(function ($file) {
            return $file->getMTime();
        })->values()->all();
    }

    /**
     * Extract columns from migration files.
     */
    protected function extractColumnsFromMigration($migrationFiles)
    {
        $columns = [];
        $excludedColumns = config('syncfillable.excluded_columns', []);
        $excludedTypes = config('syncfillable.excluded_types', []);
        $excludeCallback = config('syncfillable.exclude_callback');

        foreach ((array)$migrationFiles as $migrationFile) {
            $content = File::get($migrationFile);
            preg_match_all('/\$table->(\w+)\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);

            foreach ($matches[2] as $index => $column) {
                $type = $matches[1][$index];
                if (!in_array($column, $excludedColumns) && !in_array($type, $excludedTypes)) {
                    if ($excludeCallback && call_user_func($excludeCallback, $column, $type)) {
                        continue;
                    }
                    $columns[] = $column;
                }
            }
        }

        return array_unique($columns);
    }

    /**
     * Get columns from the database schema.
     */
    protected function getColumnsFromSchema($tableName, $connection)
    {
        try {
            $excludedColumns = config('syncfillable.excluded_columns', []);
            return array_diff(
                Schema::connection($connection)->getColumnListing($tableName),
                $excludedColumns
            );
        } catch (\Exception $e) {
            $this->error("Error accessing schema for table '{$tableName}': {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Update the model's fillable or guarded fields.
     */
    protected function updateModelFillable($modelPath, array $columns)
    {
        $property = $this->option('guarded') ? 'guarded' : 'fillable';
        $arrayContent = implode("', '", $columns);
        $propertyLine = "protected \${$property} = ['{$arrayContent}'];\n";

        $modelContent = File::get($modelPath);
        $originalContent = $modelContent;

        if (Str::contains($modelContent, "protected \${$property}")) {
            $modelContent = preg_replace("/protected \${$property} = \[.*?\];/s", $propertyLine, $modelContent);
        } else {
            $modelContent = preg_replace('/{/', "{\n{$propertyLine}", $modelContent, 1);
        }

        // Conditionally create backup based on config
        if (config('syncfillable.model_backup', true)) {
            File::put($modelPath . '.backup', $originalContent);
        }


        File::put($modelPath, $modelContent);

        // Format the model file using Pint
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $pintBinary = $isWindows ? 'vendor\\bin\\pint.bat' : './vendor/bin/pint';

        $process = new Process([$pintBinary, $modelPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error("Error formatting the model file: {$modelPath}");
            $this->line($process->getErrorOutput());
             // Restore from backup if available and formatting fails
            if (config('syncfillable.model_backup', true) && File::exists($modelPath . '.backup')) {
                File::move($modelPath . '.backup', $modelPath);
            }
            return;
        }
    }

    /**
     * Extract the full class name from the file path with namespace mapping.
     */
    protected function getClassNameFromPath($path)
    {
        $relativePath = str_replace(base_path(), '', $path);
        $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
        $className = trim($classPath, '\\');

        // Map directory to namespace
        $namespaceMap = config('syncfillable.namespace_map', [
            'app/Models' => 'App\\Models',
        ]);

        foreach ($namespaceMap as $dir => $namespace) {
            if (Str::startsWith($relativePath, '/' . $dir) || Str::startsWith($relativePath, '\\' . $dir)) {
                $className = $namespace . str_replace([$dir, '/'], ['', '\\'], $relativePath);
                $className = str_replace('.php', '', $className);
                break;
            }
        }

        return trim($className, '\\');
    }

    /**
     * Log changes to a file.
     */
    protected function logChange($modelPath, array $columns, $stage)
    {
        $property = $this->option('guarded') ? 'guarded' : 'fillable';
        $logMessage = sprintf(
            "[%s] %s update for %s: %s = ['%s']\n",
            now()->toDateTimeString(),
            ucfirst($stage),
            $modelPath,
            $property,
            implode("', '", $columns)
        );
        File::append(storage_path('logs/syncfillable.log'), $logMessage);
    }

    /**
     * Rollback changes for a single model or all models.
     */
    public function rollback($name, $directory)
    {
        if (Str::lower($name) === 'all') {
            $modelFiles = collect(File::allFiles($directory))
                ->filter(fn($file) => $file->getExtension() === 'php' && !Str::endsWith($file->getFilename(), '.backup'));
            foreach ($modelFiles as $modelFile) {
                $this->restoreBackup($modelFile->getPathname());
            }
        } else {
            $modelName = ucfirst($name);
            $modelPath = "{$directory}/{$modelName}.php";
            $this->restoreBackup($modelPath);
        }
    }

    /**
     * Restore a model file from its backup.
     */
    protected function restoreBackup($modelPath)
    {
        $backupPath = $modelPath . '.backup';
        if (File::exists($backupPath)) {
            File::move($backupPath, $modelPath);
            $this->info("Restored {$modelPath} from backup.");
            $logMessage = sprintf("[%s] Rolled back %s\n", now()->toDateTimeString(), $modelPath);
            File::append(storage_path('logs/syncfillable.log'), $logMessage);
        } else {
            $this->warn("No backup found for {$modelPath}.");
        }
    }

    /**
     * Clean up stale backup files.
     */
    protected function cleanupBackups($directory)
    {
        $backupFiles = collect(File::allFiles($directory))
            ->filter(fn($file) => Str::endsWith($file->getFilename(), '.backup'));

        foreach ($backupFiles as $backupFile) {
            File::delete($backupFile->getPathname());
            $this->info("Deleted stale backup: {$backupFile->getPathname()}");
        }
    }
}