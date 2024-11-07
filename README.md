# SyncModelFillable

A Laravel package to help you automatically sync a model's fillable fields with its database migration columns. This package provides a command to update the `$fillable` property in your models based on the columns defined in your migration files.

## Features

- Syncs model `$fillable` properties with migration columns.
- Supports Laravel 8, 9, 10, and 11.
- Allows configuration of columns to exclude, like timestamps.

## Installation

1. **Install the package via Composer:**

   ```bash
   composer require muzammal/syncmodelfillable

    (Optional) Publish the configuration file:

    If you want to customize which columns should be excluded from the $fillable fields, publish the configuration file:

    php artisan vendor:publish --provider="Muzammal\Syncmodelfillable\SyncModelFillableServiceProvider"

    This will create a config/syncfillable.php file where you can specify columns to exclude (like created_at, updated_at, etc.).

Usage

This package provides an Artisan command sync:fillable that you can use to sync a model's $fillable fields with its migration columns.
1. Sync a Specific Model's Fillable Fields

To sync the fillable fields of a specific model, run the command with the model name. For example, if you have a model named User:

php artisan sync:fillable User

This will:

    Look for the User model in the app/Models directory.
    Find the migration file associated with the model’s database table.
    Update the $fillable property in the model with the columns from the migration file.

2. Sync All Models in app/Models

To sync all models in your app/Models directory, use all as the parameter:

php artisan sync:fillable all

This will:

    Look for all models in the app/Models directory.
    Match each model with its migration file.
    Update the $fillable property for each model.

Configuration

The configuration file syncfillable.php allows you to specify which columns to exclude from the $fillable fields. By default, it excludes common timestamp columns (created_at, updated_at, deleted_at).

Example configuration (config/syncfillable.php):

return [
    'excluded_columns' => ['created_at', 'updated_at', 'deleted_at'],
];

Add any column names here that you don't want included in your $fillable fields.
Testing

If you want to test this package in your Laravel application, follow these steps:

    Install Development Dependencies:

    To ensure you have all the necessary testing packages, run:

composer install --dev

Run the Tests:

Run the following command to execute the tests for this package:

    vendor/bin/phpunit

    This will run the tests defined in the package and verify that the command behaves as expected.

Example

Suppose you have a User model with a migration that defines columns such as name, email, and password. Running the following command:

php artisan sync:fillable User

Would automatically set the $fillable fields in User.php like this:

protected $fillable = ['name', 'email', 'password'];

License

This package is open-source software licensed under the MIT license.