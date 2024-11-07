<?php

namespace Muzammal\Syncmodelfillable\Console;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncModelFillable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fillable {name}';

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

        if (Str::lower($name) === 'all') {
            $this->updateAllModels();
        } else {
            $this->updateSingleModel($name);
        }
    }

    /**
     * Update the fillable fields for a single model.
     */
    protected function updateSingleModel($name)
    {
        $modelName = ucfirst($name);
        $modelPath = app_path("Models/{$modelName}.php");

        if (!File::exists($modelPath)) {
            $this->error("Model {$modelName} does not exist.");
            return;
        }

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
            $this->error("Migration file for table '{$tableName}' not found.");
        }
    }

    /**
     * Update fillable fields for all models in the app/Models directory.
     */
    protected function updateAllModels()
    {
        $modelFiles = File::allFiles(app_path('Models'));

        foreach ($modelFiles as $modelFile) {
            $modelName = $modelFile->getFilenameWithoutExtension();
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
    }

    /**
     * Retrieve the table name from the model.
     *
     * @param string $modelPath
     * @param string $modelName
     * @return string
     */
    protected function getModelTableName($modelPath, $modelName)
    {
        // Use reflection to get the table property if it exists
        if (File::exists($modelPath)) {
            require_once $modelPath;
            if (class_exists("App\\Models\\{$modelName}")) {
                $reflection = new ReflectionClass("App\\Models\\{$modelName}");
                if ($reflection->hasProperty('table')) {
                    $property = $reflection->getProperty('table');
                    $property->setAccessible(true);
                    $instance = $reflection->newInstance();
                    $table = $property->getValue($instance);
                    if ($table) {
                        return $table;
                    }
                }
            }
        }

        // Default to plural snake_case of the model name
        return Str::snake(Str::plural($modelName));
    }

    /**
     * Get the migration file based on the table name.
     *
     * @param string $tableName
     * @return \SplFileInfo|null
     */
    protected function getMigrationFileByTableName($tableName)
    {
        return collect(File::allFiles(database_path('migrations')))
            ->first(fn($file) => Str::contains($file->getFilename(), "create_{$tableName}_table"));
    }

    /**
     * Extract columns from the migration file.
     *
     * @param \SplFileInfo $migrationFile
     * @return array
     */
    protected function extractColumnsFromMigration($migrationFile)
    {
        $content = File::get($migrationFile);
        preg_match_all('/\$table->\w+\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches);

        $excludedColumns = config('syncfillable.excluded_columns', ['created_at', 'updated_at', 'deleted_at']);
        return array_filter($matches[1] ?? [], fn($column) => !in_array($column, $excludedColumns));
    }

    /**
     * Update the model's fillable fields.
     *
     * @param string $modelPath
     * @param array $columns
     */
    protected function updateModelFillable($modelPath, array $columns)
    {
        $fillableArray = implode("', '", $columns);
        $fillableLine = "    protected \$fillable = ['{$fillableArray}'];\n";

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
    }
}
