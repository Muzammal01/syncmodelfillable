<?php

namespace Muzammal\Syncmodelfillable\Console;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class SyncModelFillable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fillable {name} {--path=} {--ignore=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync model fillable fields with migration columns';

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

        $modelFiles = File::allFiles($basePath);
        $modelFile = collect($modelFiles)->first(fn($file) => $file->getFilenameWithoutExtension() === $name);

        if (!$modelFile) {
            $this->error("Error: Model '{$name}' does not exist in {$basePath} or its subdirectories.");
            return Command::FAILURE;
        }

        $this->updateSingleModel($name, $basePath);
    }

    return Command::SUCCESS;
}
   

    /**
     * Update fillable fields for all models in a directory (recursively).
     */
    protected function updateAllModels($directory, array $ignoreList)
    {
        $modelFiles = File::allFiles($directory);

        foreach ($modelFiles as $modelFile) {
            $modelName = $modelFile->getFilenameWithoutExtension();

            if (in_array($modelName, $ignoreList)) {
                $this->warn("Skipping {$modelName} model as it is in the ignore list.");
                continue;
            }

            $this->updateSingleModel($modelName, $directory);
        }
    }

    /**
     * Update the fillable fields for a single model.
     */
    protected function updateSingleModel($name, $directory)
    {
        $modelName = ucfirst($name);
        $modelFiles = File::allFiles($directory);

        $modelFile = collect($modelFiles)->first(fn($file) => $file->getFilenameWithoutExtension() === $modelName);

        if (!$modelFile) {
            $this->error("Model {$modelName} does not exist in {$directory}.");
            return;
        }

        $modelPath = $modelFile->getPathname();

        // Retrieve the table name from the model, if specified
        $tableName = $this->getModelTableName($modelPath, $modelName);

        // Find the migration file based on the table name
        $migrationFile = $this->getMigrationFileByTableName($tableName);

        if ($migrationFile) {
            $columns = $this->extractColumnsFromMigration($migrationFile);

            if ($columns) {
                $this->updateModelFillable($modelPath, $columns);
                $this->info("Updated fillable fields for {$modelName} model.");
            } else {
                $this->warn("No columns found in migration for table '{$tableName}'.");
            }
        } else {
            $this->warn("Migration file for table '{$tableName}' not found for model '{$modelName}'.");
        }
    }

    /**
     * Retrieve the table name from the model.
     */
    protected function getModelTableName($modelPath, $modelName)
    {
        if (File::exists($modelPath)) {
            require_once $modelPath;
            $className = $this->getClassNameFromPath($modelPath);
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                if ($reflection->hasProperty('table')) {
                    $property = $reflection->getProperty('table');
                    $property->setAccessible(true);
                    $instance = $reflection->newInstance();
                    return $property->getValue($instance) ?? Str::snake(Str::plural($modelName));
                }
            }
        }
        return Str::snake(Str::plural($modelName));
    }

    /**
     * Get the migration file based on the table name.
     */
    protected function getMigrationFileByTableName($tableName)
    {
        return collect(File::allFiles(database_path('migrations')))
            ->first(fn($file) => Str::contains($file->getFilename(), "create_{$tableName}_table"));
    }

    /**
     * Extract columns from the migration file.
     */
    protected function extractColumnsFromMigration($migrationFile)
    {
    $content = File::get($migrationFile);
    preg_match_all('/\$table->\w+\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);

    $excludedColumns = config('syncfillable.excluded_columns', ['created_at', 'updated_at', 'deleted_at']);
    $columns = array_filter($matches[1] ?? [], fn($column) => !in_array($column, $excludedColumns));
    
    return array_unique($columns);
    }

    /**
     * Update the model's fillable fields.
     */
    protected function updateModelFillable($modelPath, array $columns)
    {
        $fillableArray = implode("', '", $columns);
        $fillableLine = "protected \$fillable = ['{$fillableArray}'];\n";

        $modelContent = File::get($modelPath);

        // Check if $fillable exists in the model
        if (Str::contains($modelContent, 'protected $fillable')) {
            // Update existing $fillable
            $modelContent = preg_replace('/protected \$fillable = \[.*?\];/s', $fillableLine, $modelContent);
        } else {
            // Insert $fillable after class declaration
            $modelContent = preg_replace('/{/', "{\n{$fillableLine}", $modelContent, 1);
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
            return;
        }
    }

    /**
     * Extract the full class name from the file path.
     */
    protected function getClassNameFromPath($path)
    {
        $relativePath = str_replace(base_path(), '', $path);
        $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
        return trim($classPath, '\\');
    }
}
