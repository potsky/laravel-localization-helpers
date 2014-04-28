Laravel Localization Helpers
============================

LLH is a set of tools to help you manage translations in your Laravel project.

## Installation

1 - Add the following line in the `require` array of the `composer.json` file :  
`"potsky/laravel-localization-helpers" : "dev-master"`

2 - Update your installation : `composer update`

3 - Add the following line in the `providers` array of the `app/config/app.php` configuration file :
`'Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider',`

Now execute `php artisan list` and you should view the new *localization* commands:

```
...
key
  key:generate                Set the application key
localization
  localization:missing        Parse all translations in app directory and build all lang files
migrate
  migrate:install             Create the migration repository
...
```

## Configuration

To configure your fresh installed package, please create a configuration file by executing :

`php artisan config:publish potsky/laravel-localization-helpers`

Then you can modify the configuration in file `app/config/packages/potsky/laravel-localization-helpers/config.php`.

## Usage

### `localization:missing`

This command parses all your code and generate according lang files in all `lang/XXX/` directories.

Use `php artisan help localization:missing` for more informations about options.

*Examples*

...

## Support

Use the github issue system to open a issue and ask for something.

