# SyncModelFillable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)
[![Total Downloads](https://img.shields.io/packagist/dt/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)

**SyncModelFillable** is a Laravel package designed to help automatically add or update a model's `$fillable` fields with its database migration columns. 🎉 With just a simple Artisan command.
## ✨ Features

- 🛠️ Syncs model `$fillable` properties with migration columns.
- 📦 Supports Laravel versions 8, 9, 10, 11, and 12.
- ⚙️ Customizable to exclude specific columns, like timestamps.
- 🔄 **New**: Added a `--ignore` flag to exclude specific models during sync.

---

## 🚀 Installation

1. **Install the package via Composer:**

```bash
composer require muzammal/syncmodelfillable
```

2. **(Optional) Publish the configuration file:**

If you'd like to customize which columns are excluded from the `$fillable` fields, publish the configuration file:

```bash
php artisan vendor:publish --tag=config
```

This will create a `config/syncfillable.php` file where you can specify columns to exclude (such as `created_at`, `updated_at`, `deleted_at` etc.).

---

## 📘 Usage

This package provides an Artisan command `sync:fillable` that lets you sync a model's `$fillable` fields with its migration columns.

### Sync a Specific Model's `$fillable` Fields

To sync the `$fillable` fields of a specific model, use the command with the model name. For example, if you have a model named `Post`:

```bash
php artisan sync:fillable Post
```

This will:
- Look for the `Post` model in the `app/Models` directory.
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

### **New**: Exclude Models with the `--ignore` Flag

You can now exclude specific models from the sync operation using the `--ignore` flag. For example:

```bash
php artisan sync:fillable all --ignore=User
```

This will sync all models except `User`. You can also pass multiple models to ignore:

```bash
php artisan sync:fillable all --ignore=User,Product,Order
```

If you want to run the sync for a single model, ignoring doesn't apply here:

```bash
php artisan sync:fillable Product
```

### **How It Works**
- The `--ignore` flag allows you to pass a comma-separated list of model names to exclude during the sync process.
- If a model is listed in the ignore list, it will be skipped during the sync.

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

## 🔍 Example

Suppose you have a `Post` model with a migration that defines columns such as `name`, `slug`, and `content`. Running the following command:

```bash
php artisan sync:fillable Post
```

Would automatically set the `$fillable` fields in `Post.php` as follows:

```php
protected $fillable = ['name', 'slug', 'content'];
```

---

## 📜 License

This package is open-source software licensed under the MIT license.
