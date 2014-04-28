Laravel Localization Helpers
============================

LLH is a set of tools to help you manage translations in your Laravel project.

## Installation

Add the following line in the `require` array of the `composer.json` file :  
`"potsky/laravel-localization-helpers" : "dev-master"`

Add the following line in the `providers` array of the `app/config/app.php` configuration file :
`'Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider',`

Execute `php artisan list` and you should view the new commands:

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


## Usage

- `localization:missing` : this command parses all your code and generate according lang files in all `lang/XXX/` directories.

## Support

Use the github issue system to open a issue and ask for something.

