# Contributing to SyncModelFillable

🎉 Thank you for considering contributing to **SyncModelFillable**!  
Your support helps make this Laravel package even better. Below are the guidelines and best practices for contributing.

---

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How to Contribute](#how-to-contribute)
  - [Reporting Bugs](#🐞-reporting-bugs)
  - [Suggesting Features](#💡-suggesting-features)
  - [Submitting Pull Requests](#🚀-submitting-pull-requests)
- [Coding Standards](#🧹-coding-standards)
- [Running Tests](#🧪-running-tests)
- [Versioning](#📌-versioning)
- [License](#📜-license)

---

## 📜 Code of Conduct

Please review our [Code of Conduct](https://www.contributor-covenant.org/version/2/1/code_of_conduct/) before participating.  
We are committed to fostering a welcoming and respectful environment.

---

## 🏁 Getting Started

1. **Fork** the repository.
2. **Clone** your fork:

   ```bash
   git clone https://github.com/your-username/syncmodelfillable.git
   cd syncmodelfillable
   ```

3. Install dependencies:

   ```bash
   composer install
   ```

4. Set up local development and testing as needed.

---

## 💡 How to Contribute

### 🐞 Reporting Bugs

- Use [GitHub Issues](https://github.com/muzammal/syncmodelfillable/issues) to report bugs.
- Provide a clear and concise title.
- Include:
  - Laravel version
  - PHP version
  - Steps to reproduce
  - Expected vs actual behavior
  - Any relevant error output or logs

### 💡 Suggesting Features

- Create a GitHub Issue with `[Feature]` in the title.
- Describe the feature, its purpose, and potential benefits.
- Propose usage syntax if applicable.

### 🚀 Submitting Pull Requests

1. Create a new branch from `main`:

   ```bash
   git checkout -b feature/your-feature-name
   ```

2. Make your changes.
3. Format your code using [Laravel Pint](https://github.com/laravel/pint):

   ```bash
   ./vendor/bin/pint
   ```

4. Run the test suite (if available).
5. Commit using clear, conventional messages:

   ```bash
   git commit -m "feat: add support for --guarded flag"
   ```

6. Push to your fork and open a **Pull Request** targeting the `main` branch.

---

## 🧹 Coding Standards

- Follow **PSR-12** coding standards.
- Use **Laravel Pint** for code formatting.
- Keep pull requests focused on a single feature/fix.
- Add PHPDoc blocks and inline comments where helpful.
- Avoid breaking backward compatibility unless necessary.

---

## 🧪 Running Tests

> *Coming soon*: PHPUnit test coverage for critical features.

For now, please manually verify:
- The command works as expected (`sync:fillable`, `rollback`, etc.).
- Existing functionality is not broken.
- Test on supported Laravel and PHP versions if possible.

---

## 📌 Versioning

This package follows [Semantic Versioning](https://semver.org/).  
Please structure your pull requests accordingly.

- `MAJOR` for breaking changes
- `MINOR` for new features
- `PATCH` for bug fixes

---

## 📜 License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).

---

Thank you again for helping improve **SyncModelFillable**!  
Let’s make Laravel development faster and cleaner together 🚀
