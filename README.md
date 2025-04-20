# SyncModelFillable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)
[![Total Downloads](https://img.shields.io/packagist/dt/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)

**SyncModelFillable** is a Laravel package designed to help automatically add or update a model's `$fillable` fields with its database migration columns. 🎉 With just a simple Artisan command.


## ✨ Features  

- 🛠️ Syncs model `$fillable` properties with migration columns.  
- 📦 Supports Laravel versions 8, 9, 10, 11, and 12.  
- ⚙️ Customizable to exclude specific columns, like timestamps.  
- 🔄 **New:**  
  - The `all` flag now **recursively scans all subdirectories** inside `app/Models/`.  
  - The `--path=` option allows selecting a **custom directory** for scanning models.  
  - The `--ignore` flag lets you exclude specific models during sync.
  - 🧹 **Cross-platform Pint support:** Automatically formats the model file after updating the `$fillable` fields, ensuring proper code formatting on all operating systems (Windows, macOS, Linux).

---

## 🚀 Installation  

1. **Install the package via Composer:**  

   ```bash
   composer require muzammal/syncmodelfillable
   ```  

2. **(Optional) Publish the configuration file:**  

   ```bash
   php artisan vendor:publish --tag=syncmodelfillable-config
   ```  

   This will create a `config/syncfillable.php` file where you can specify columns to exclude (such as `created_at`, `updated_at`, `deleted_at`, etc.).  

---

## 📘 Usage  

This package provides an Artisan command `sync:fillable` to sync a model's `$fillable` fields with its database migration columns.  

### 🔹 Sync a Specific Model  

To sync the `$fillable` fields of a specific model inside `app/Models/`, including nested folders run this:  

```bash
php artisan sync:fillable Post
```

### 🔹 Sync All Models (Including Nested Folders)  

To sync all models inside `app/Models/`, including nested folders:  

```bash
php artisan sync:fillable all
```

This will:  
- Scan **all subdirectories** inside `app/Models/` (e.g., `app/Models/Fintech/AnotherFolder/AnotherFolder`).  
- Match each model with its migration file.  
- generate the `$fillable` properties accordingly.  

### 🔹 **New:** Custom Path for Models  

You can specify a custom path instead of using `app/Models/` by using the `--path=` option.  

```bash
php artisan sync:fillable --path=app/CustomModels
```

This will:  
- Scan **app/CustomModels/** instead of `app/Models/`.  
- Sync all models found in that directory.  

### 🔹 **New:** Exclude Models with the `--ignore` Flag  

To exclude specific models from the sync operation:  

```bash
php artisan sync:fillable all --ignore=User
```

You can also pass multiple models:  

```bash
php artisan sync:fillable all --ignore=User,Product,Order
```

If syncing a single model, the `--ignore` flag is not applicable:  

```bash
php artisan sync:fillable Product
```

---

## ⚙️ Configuration  

The configuration file `syncfillable.php` allows you to exclude certain columns from `$fillable`. By default, common timestamp columns (`created_at`, `updated_at`, `deleted_at`) are excluded.  

**Example configuration:**  

```php
return [
    'excluded_columns' => ['created_at', 'updated_at', 'deleted_at'],
];
```

---

## 🧹 **Cross-Platform Support for Pint**  

After updating the `$fillable` fields, the model file will be automatically formatted using [Pint](https://github.com/laravel/pint). This ensures that your code is properly formatted on all operating systems, including Windows, macOS, and Linux. 

- On **Windows**, Pint is run via `vendor\\bin\\pint.bat`.  
- On **macOS/Linux**, Pint is executed with `./vendor/bin/pint`.

This ensures your code maintains consistency and adheres to best practices without requiring manual formatting. ✨

---

## 🔍 Example  

If your `Post` model has a migration defining `name`, `slug`, and `content` columns, running:  

```bash
php artisan sync:fillable Post
```

Would automatically generate `$fillable` in `Post.php`

```php
protected $fillable = ['name', 'slug', 'content'];
```
Additionally, after updating the `$fillable` fields, the `Post.php` file will be formatted to ensure clean code.

---

## 📜 License  

This package is open-source software licensed under the MIT license.
