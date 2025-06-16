# SyncModelFillable

[![Latest Version on Packagist](https://img.shields.io/packagist/v/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)
[![Total Downloads](https://img.shields.io/packagist/dt/muzammal/syncmodelfillable.svg?style=flat-square)](https://packagist.org/packages/muzammal/syncmodelfillable)

**SyncModelFillable** is a Laravel package designed to help automatically add or update a model's `$fillable` fields with its database migration columns. 🎉 With just a simple Artisan command.


## ✨ Features  

- 🛠 Syncs model `$fillable` or `$guarded` properties with **migration** or **database schema**.
- 📦 Supports Laravel versions **8, 9, 10, 11, and 12**.
- ⚙️ Customizable to exclude specific **columns** or **column types**.
- 📂 Recursive scanning: `all` flag scans all subdirectories in `app/Models/` or custom paths.
- 📁 `--path=`: Scan models in any directory.
- 🚫 `--ignore`: Exclude specific models from sync.
- 🧹 Cross-platform [Pint](https://github.com/laravel/pint) support for clean, consistent formatting (Windows, macOS, Linux).
- 🧮 `--from-schema`: Sync directly from the database schema.
- 🧪 `--dry-run`: Preview changes without modifying files.
- 🔐 `--guarded`: Sync `$guarded` fields instead of `$fillable`.
- 🗂 Model backup support (`.php.backup` files).
- ⏪ Rollback support via `sync:fillable:rollback`.
- 🧬 Cross-database compatibility (MySQL, SQLite, PostgreSQL, SQL Server, etc.)

---

## 🚀 Installation

Install the package via Composer:

```bash
composer require muzammal/syncmodelfillable
```

(Optional) Publish the configuration file:

```bash
php artisan vendor:publish --tag=syncmodelfillable-config
```

Creates a `config/syncfillable.php` file to customize exclusions, backup behavior, namespace mappings, etc.

---

## 📘 Usage

This package provides two Artisan commands:

- `sync:fillable`: Sync model fields
- `sync:fillable:rollback`: Revert synced changes (uses backups)

### 🔹 Sync a Specific Model

```bash
php artisan sync:fillable Post
```

Scans `app/Models/` (including subdirectories) for the `Post` model and syncs its fields.

### 🔹 Sync All Models

```bash
php artisan sync:fillable all
```

- Recursively scans `app/Models/`
- Matches each model with migration/schema
- Updates `$fillable` or `$guarded` properties

### 🔹 Custom Path for Models

```bash
php artisan sync:fillable all --path=app/CustomModels
```

Scans models in a custom directory instead of `app/Models/`.

### 🔹 Exclude Models with `--ignore`

```bash
php artisan sync:fillable all --ignore=User,Product
```

> ℹ️ Note: `--ignore` has no effect when syncing a single model.

---

### 🔹 Sync from Database Schema

```bash
php artisan sync:fillable all --from-schema
```

Use this to sync from the actual **database schema** instead of migrations — great for legacy or dynamically altered databases.

---

### 🔹 Dry Run Mode

```bash
php artisan sync:fillable Post --dry-run
```

Outputs a preview of what will be changed:

```
Dry run: Would update app/Models/Post.php with fillable: ['title', 'slug', 'content']
```

---

### 🔹 Sync Guarded Fields

```bash
php artisan sync:fillable Post --guarded
```

This sets:

```php
protected $guarded = ['title', 'slug', 'content'];
```

---

### 🔹 Rollback Changes

Restore model files using `.backup` files:

```bash
php artisan sync:fillable:rollback all
```

Or for a single model:

```bash
php artisan sync:fillable:rollback Post
```

---

## ⚙️ Configuration

Publish the config:

```bash
php artisan vendor:publish --tag=syncmodelfillable-config
```

### Example: `config/syncfillable.php`

```php
return [
    // Columns to exclude from fillable/guarded arrays
    'excluded_columns' => [
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    // Column types to exclude (e.g., timestamp, json)
    'excluded_types' => [],

    // Custom callback to exclude columns
    'exclude_callback' => null,

    // Namespace mapping for model directories
    'namespace_map' => [
        'app/Models' => 'App\\Models',
    ],

    // Enable or disable model backups
    'model_backup' => true,
];
```

- `excluded_columns`: Common timestamps or sensitive fields.
- `excluded_types`: e.g. `timestamp`, `json`.
- `exclude_callback`: e.g.  
  ```php
  function ($column, $type) {
      return str_starts_with($column, 'hidden_');
  }
  ```
- `namespace_map`: For custom paths.
- `model_backup`: When `true`, creates `.php.backup` files for rollback.

---

## 🧹 Pint Formatting

After updating fields, model files are auto-formatted using Laravel Pint:

- **Windows:** `vendor\bin\pint.bat`
- **macOS/Linux:** `./vendor/bin/pint`

If Pint fails and backups are enabled, the original model is restored and an error is logged.

---

## 🧬 Database Compatibility

SyncModelFillable supports all Laravel-supported databases:

- MySQL
- SQLite
- PostgreSQL
- SQL Server

- With `--from-schema`: Uses Laravel's Schema facade.
- Without `--from-schema`: Parses migration files.

---

## 🔍 Examples

### ✅ Example 1: Syncing a Single Model

**Migration:**

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->timestamps();
});
```

**Command:**

```bash
php artisan sync:fillable Post
```

**Result in `app/Models/Post.php`:**

```php
protected $fillable = ['title', 'slug', 'content'];
```

---

### ✅ Example 2: Sync All Models Using Schema

```bash
php artisan sync:fillable all --from-schema
```

**Output:**

```
0/3 Updated User.php
1/3 Updated Post.php
2/3 Updated Category.php
3/3 Done.
```

---

### ✅ Example 3: Dry Run with Guarded

```bash
php artisan sync:fillable Post --guarded --dry-run
```

**Output:**

```
Dry run: Would update app/Models/Post.php with guarded: ['title', 'slug', 'content']
```

---

### ✅ Example 4: Rollback Changes

```bash
php artisan sync:fillable:rollback all
```

**Output:**

```
0/3 Restored User.php from backup.
1/3 Restored Post.php from backup.
2/3 Restored Category.php from backup.
3/3 Done.
```

---

## 📜 License

This package is open-source software licensed under the (LICENSE).
---

💡 Have suggestions or want to contribute? PRs and issues are welcome!
