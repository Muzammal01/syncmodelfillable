<?php

namespace Muzammal\Syncmodelfillable\Tests;

use Orchestra\Testbench\TestCase;
use Muzammal\Syncmodelfillable\SyncModelFillableServiceProvider;
use Illuminate\Support\Facades\File;

class SyncModelFillableTest extends TestCase
{
    /**
     * Register the package's service provider
     */
    protected function getPackageProviders($app)
    {
        return [
            SyncModelFillableServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_run_sync_fillable_command()
    {
        // Create a dummy model file and migration file for testing
        $modelPath = app_path('Models/DummyModel.php');
        $migrationPath = database_path('migrations/2023_01_01_000000_create_dummy_table.php');

        // Create a dummy model file
        File::put($modelPath, "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass DummyModel extends Model {}\n");

        // Create a dummy migration file
        File::put($migrationPath, "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration {\n    public function up() {\n        Schema::create('dummy_models', function (Blueprint \$table) {\n            \$table->id();\n            \$table->string('name');\n            \$table->timestamps();\n        });\n    }\n};");

        // Run the command
        $this->artisan('sync:fillable', ['name' => 'DummyModel'])
            ->expectsOutput('Updated fillable fields for DummyModel model.')
            ->assertExitCode(0);

        // Assert that the fillable array was updated in the model file
        $updatedModelContent = File::get($modelPath);
        $this->assertStringContainsString("protected \$fillable = ['id', 'name'];", $updatedModelContent);

        // Clean up
        File::delete($modelPath);
        File::delete($migrationPath);
    }
}
