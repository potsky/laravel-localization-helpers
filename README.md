Laravel Localization Helpers
============================

[![Latest Stable Version](https://poser.pugx.org/potsky/laravel-localization-helpers/v/stable.svg)](https://packagist.org/packages/potsky/laravel-localization-helpers)
[![Latest Unstable Version](https://poser.pugx.org/potsky/laravel-localization-helpers/v/unstable.svg)](https://packagist.org/packages/potsky/laravel-localization-helpers)
[![Total Downloads](https://poser.pugx.org/potsky/laravel-localization-helpers/downloads.svg)](https://packagist.org/packages/potsky/laravel-localization-helpers)



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

Add new folders to search for, add your own lang methods or functions, ...

## Usage

### Command `localization:missing`

This command parses all your code and generate according lang files in all `lang/XXX/` directories.

Use `php artisan help localization:missing` for more informations about options.

#### *Examples*

##### Generate all lang files

```
php artisan localization:missing
```

##### Generate all lang files without prompt

```
php artisan localization:missing -n
```

##### Generate all lang files without backuping old files

```
php artisan localization:missing -b
```

##### Generate all lang files without keeping obsolete lemmas

```
php artisan localization:missing -o
```

##### Generate all lang files without any comment for new found lemmas

```
php artisan localization:missing -c
```

##### Generate all lang files without header comment

```
php artisan localization:missing -d
```

##### Generate all lang files and set new lemma values

3 commands below produce the same output:
```
php artisan localization:missing
php artisan localization:missing -l
php artisan localization:missing -l "%LEMMA"
```

You can customize the default generated values for unknown lemmas.

The following command let new values empty:

```
php artisan localization:missing -l ""
```

The following command prefixes all lemmas values with "Please translate this : "

```
php artisan localization:missing -l "Please translate this : %LEMMA"
```

The following command prefixes all lemmas values with "Please translate this !"

```
php artisan localization:missing -l 'Please translate this !'
```

##### Silent option for shell integration

```
#!/bin/bash

php artisan localization:missing -s
if [ $? -eq 0 ]; then
    echo "Nothing to do dude, GO for release"
else
    echo "I will not release in production, lang files are not clean"
fi
```



### Command `localization:find`

This command will search in all your code for the argument as a lemma.

Use `php artisan help localization:find` for more informations about options.

#### *Examples*

##### Find regular lemma

```
php artisan localization:find Search
```

##### Find regular lemma with verbose

```
php artisan localization:find -v Search
```

##### Find regular lemma with short path displayed

```
php artisan localization:find -s "Search me"
```

##### Find lemma with a regular expression

```
php artisan localization:find -s -r "@Search.*@"
php artisan localization:find -s -r "/.*me$/"
```

> PCRE functions are used

## Support

Use the github issue system to open a issue and ask for something.

