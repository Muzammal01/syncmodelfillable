# SyncModelFillable

A Laravel package to help automatically sync a model's `$fillable` fields with its database migration columns. This package provides commands to keep your model properties up-to-date with your migration files effortlessly.

## Features

- Syncs model `$fillable` properties with migration columns.
- Supports Laravel versions 8, 9, 10, and 11.
- Customizable to exclude specific columns, like timestamps.

## Installation

1. **Install the package via Composer:**

   ```bash
   composer require muzammal/syncmodelfillable

    (Optional) Publish the configuration file:

    If you'd like to customize which columns are excluded from the $fillable fields, publish the configuration file:

    php artisan vendor:publish --provider="Muzammal\Syncmodelfillable\SyncModelFillableServiceProvider"

    This will create a config/syncfillable.php file where you can specify columns to exclude (such as created_at, updated_at, etc.).

Usage

This package provides an Artisan command sync:fillable that lets you sync a model's $fillable fields with its migration columns.
1. Sync a Specific Model's $fillable Fields

To sync the $fillable fields of a specific model, use the command with the model name. For example, if you have a model named User:

php artisan sync:fillable User

What This Command Does:

    Looks for the User model in the app/Models directory.
    Finds the migration file associated with the model’s database table.
    Updates the $fillable property in the model with the columns from the migration file.

2. Sync All Models in app/Models

To sync all models in the app/Models directory, use all as the parameter:

php artisan sync:fillable all

What This Command Does:

    Looks for all models in the app/Models directory.
    Matches each model with its migration file.
    Updates the $fillable property for each model.

Configuration

You can configure which columns to exclude from the $fillable fields in config/syncfillable.php. By default, common timestamp columns (created_at, updated_at, deleted_at) are excluded.

Example configuration:

return [
    'excluded_columns' => ['created_at', 'updated_at', 'deleted_at'],
];

Add any column names here that you want to exclude from the $fillable fields.
Testing

To test this package in your Laravel application, follow these steps:

    Install Development Dependencies:

composer install --dev

Run the Tests:

Execute the tests with the following command:

    vendor/bin/phpunit

    This will run the tests for the package, verifying that the command behaves as expected.

Example

Suppose you have a User model with a migration that defines columns such as name, email, and password. Running the following command:

php artisan sync:fillable User

Would automatically set the $fillable fields in User.php as follows:

protected $fillable = ['name', 'email', 'password'];

License

This package is open-source software licensed under the MIT license.
