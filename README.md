# SyncModelFillable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/muzammal/syncmodelfillable/run-tests.yml?branch=main&label=Tests)](https://github.com/muzammal/syncmodelfillable/actions?query=workflow%3ATests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)

**SyncModelFillable** is a Laravel package designed to help automatically sync a model's `$fillable` fields with its database migration columns. 🎉 With just a simple Artisan command, you can keep your model properties up-to-date with your migration files effortlessly.

## 📚 Documentation, Installation, and Usage Instructions

For detailed installation and usage instructions, please see the [documentation](https://github.com/muzammal/syncmodelfillable).

---

## ✨ Features

- 🛠️ Syncs model `$fillable` properties with migration columns.
- 📦 Supports Laravel versions 8, 9, 10, and 11.
- ⚙️ Customizable to exclude specific columns, like timestamps.

---

## 🚀 Installation

1. **Install the package via Composer:**

    ```bash
    composer require muzammal/syncmodelfillable
    ```

2. **(Optional) Publish the configuration file:**

    If you'd like to customize which columns are excluded from the `$fillable` fields, publish the configuration file:

    ```bash
    php artisan vendor:publish --provider="Muzammal\Syncmodelfillable\SyncModelFillableServiceProvider"
    ```

    This will create a `config/syncfillable.php` file where you can specify columns to exclude (such as `created_at`, `updated_at`, etc.).

---

## 📘 Usage

This package provides an Artisan command `sync:fillable` that lets you sync a model's `$fillable` fields with its migration columns.

### Sync a Specific Model's `$fillable` Fields

To sync the `$fillable` fields of a specific model, use the command with the model name. For example, if you have a model named `User`:

```bash
php artisan sync:fillable User
```

This will:
- Look for the `User` model in the `app/Models` directory.
- Find the migration file associated with the model’s database table.
- Update the `$fillable` property in the model with the columns from the migration file.

### Sync All Models in `app/Models`

To sync all models in the `app/Models` directory, use `all` as the parameter:

```bash
php artisan sync:fillable all
```

This will:
- Look for all models in the `app/Models` directory.
- Match each model with its migration file.
- Update the `$fillable` property for each model.

---

## ⚙️ Configuration

The configuration file `syncfillable.php` allows you to specify which columns to exclude from the `$fillable` fields. By default, common timestamp columns (`created_at`, `updated_at`, `deleted_at`) are excluded.

**Example configuration:**

```php
return [
    'excluded_columns' => ['created_at', 'updated_at', 'deleted_at'],
];
```

Add any column names here that you want to exclude from the `$fillable` fields.

---

## 🧪 Testing

To test this package in your Laravel application, follow these steps:

1. **Install Development Dependencies:**
    ```bash
    composer install --dev
    ```

2. **Run the Tests:**
    Execute the tests with the following command:
    ```bash
    vendor/bin/phpunit
    ```
    This will run the tests for the package, verifying that the command behaves as expected.

---

## 🔍 Example

Suppose you have a `User` model with a migration that defines columns such as `name`, `email`, and `password`. Running the following command:

```bash
php artisan sync:fillable User
```

Would automatically set the `$fillable` fields in `User.php` as follows:

```php
protected $fillable = ['name', 'email', 'password'];
```

---

## 🤝 Contributing

Please see the [CONTRIBUTING](CONTRIBUTING.md) file for details on how to contribute to this project.

---

## 📅 Changelog

Please see the [CHANGELOG](CHANGELOG.md) file for more information on recent changes.

---

## 📜 License

This package is open-source software licensed under the MIT license.
