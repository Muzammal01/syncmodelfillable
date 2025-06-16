<?php

namespace Muzammal\Syncmodelfillable\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RollbackSyncModelFillable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:fillable:rollback {name} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback model fillable/guarded field updates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $customPath = $this->option('path');
        $basePath = $customPath ? base_path($customPath) : app_path('Models');

        if (!is_dir($basePath)) {
            $this->error("Error: The directory '{$basePath}' does not exist.");
            return Command::FAILURE;
        }

        if (Str::lower($name) === 'all') {
            $modelFiles = collect(File::allFiles($basePath))
                ->filter(fn($file) => $file->getExtension() === 'php' && !Str::endsWith($file->getFilename(), '.backup'));

            $this->output->progressStart(count($modelFiles));

            foreach ($modelFiles as $modelFile) {
                $this->restoreBackup($modelFile->getPathname());
                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
        } else {
            $modelName = ucfirst($name);
            $modelPath = "{$basePath}/{$modelName}.php";
            $this->restoreBackup($modelPath);
        }

        return Command::SUCCESS;
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
}