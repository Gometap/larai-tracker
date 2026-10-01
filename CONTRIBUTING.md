# Contributing to Larai Tracker

First off, thank you for considering contributing to Larai Tracker! It's people like you who make the open-source community such an amazing place.

## Local Development

1. Clone the repository.
2. Run `composer install`.
3. Create a new branch for your feature or bugfix.
4. Add regression tests before changing behavior where practical.

## Coding Standards

Run the release checks before opening a pull request:

```bash
composer check
composer validate --strict --no-check-publish
composer audit --locked
```

## Pull Requests

1. Ensure all tests, Pint, Larastan, Composer validation, and dependency audit pass.
2. Update the README or documentation if necessary.
3. Use the provided Pull Request Template.

## License

By contributing, you agree that your contributions will be licensed under its Apache-2.0 License.
