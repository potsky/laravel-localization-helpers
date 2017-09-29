Laravel Localization Helpers
============================

[![Latest Stable Version](https://poser.pugx.org/potsky/laravel-localization-helpers/v/stable.svg)](https://packagist.org/packages/potsky/laravel-localization-helpers)
[![Latest Unstable Version](https://poser.pugx.org/potsky/laravel-localization-helpers/v/unstable.svg)](https://packagist.org/packages/potsky/laravel-localization-helpers)
[![Build Status](https://travis-ci.org/potsky/laravel-localization-helpers.svg)](https://travis-ci.org/potsky/laravel-localization-helpers)
[![Coverage Status](https://coveralls.io/repos/potsky/laravel-localization-helpers/badge.svg?service=github)](https://coveralls.io/github/potsky/laravel-localization-helpers)
[![Total Downloads](https://poser.pugx.org/potsky/laravel-localization-helpers/downloads.svg)](https://packagist.org/packages/potsky/laravel-localization-helpers)
[![Stories in Ready](https://badge.waffle.io/potsky/laravel-localization-helpers.png?label=ready&title=Ready)](https://waffle.io/potsky/laravel-localization-helpers)

## This branch is the current dev branch

LLH is a set of artisan commands to manage translations in your Laravel project. Key features :

- parse your code and generate lang files
- translate your sentences automatically, thanks to Microsoft Translator API
- configure output according to your code style

## Table of contents

1. [Installation](#1-installation)
1. [Configuration](#2-configuration)
1. [Usage](#3-usage)
1. [Support](#4-support)
1. [Upgrade Notices](#5-upgrade-notices)
1. [Change Log](#6-change-log)
1. [Contribute](#7-contribute)

## 1. Installation

- Choose your version according to the version compatibility matrix:

| Laravel  | Lumen    | Package
|:---------|:---------|:----------
| 4.2.x    |          | 2.0.x (EOL last version is 2.0.4)
| 5.0.x    |          | 2.1.x
| 5.1.x    | 5.1.x    | 2.2.x
| 5.2.x    | 5.2.x    | 2.3.x
| 5.3.x    | 5.3.x    | 2.4.x
| 5.4.x    | 5.4.x    | 2.5.x
| 5.5.x    | 5.5.x    | 2.6.x

- Add the following line in the `require-dev` array of the `composer.json` file and replace the version if needed according to your Laravel version:
    ```php
    "potsky/laravel-localization-helpers" : "2.6.*"
    ```

- Update your installation : `composer update`

- For Laravel, add the following lines in the `AppServiceProvider` array of the `config/app.php` configuration file :
    ```php
    Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider::class,
    ```

	On Laravel 5.5, if you don't use the package in production, disable auto-loading and register it only on `local` or `dev`: 

	- Add the following lines in the `register` method of the `AppServiceProvider` :
		```php
		public function register()
		{
			if ($this->app->environment() === 'dev') { // or local or whatever
				$this->app->register(\Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider::class);
			}
		}
		```

    - Disable to auto-register provider by adding these lines in the `composer.json` file:
		```php
		"extra" : {
			"laravel" : {
				"dont-discover" : [
					"potsky/laravel-localization-helpers"
				]
			}
		}
		```

- For Lumen, add the following lines in the `bootstrap/app.php` file :
	```php
	$app->register( Potsky\LaravelLocalizationHelpers\LaravelLocalizationHelpersServiceProvider::class );
	$app->configure('laravel-localization-helpers');
	```

- Now execute `php artisan list` and you should view the new *localization* commands:
    ```
    ...
    localization
    localization:clear          Remove lang backup files
    localization:find           Display all files where the argument is used as a lemma
    localization:missing        Parse all translations in app directory and build all lang files
    ...
    ```

In Laravel, you can add the facade in the Aliases if you need to manage translations in your code :

```php
'LocalizationHelpers' => Potsky\LaravelLocalizationHelpers\Facade\LocalizationHelpers::class
```

## 2. Configuration

To configure your fresh installed package, please create a configuration file by executing :

```bash
php artisan vendor:publish
```

Then you can modify the configuration in file :

```bash
config/laravel-localization-helpers.php
```

Add new folders to search for, add your own lang methods or functions, ...

> For Lumen, copy manually the [configuration file](https://github.com/potsky/laravel-localization-helpers/blob/master/src/config/config-laravel5.php) as `config/laravel-localization-helpers.php`.

### Backup files

You should not include backup lang files in GIT or other versioning systems.

In your `laravel` folder, add this in `.gitignore` file :

```bash
# Do not include backup lang files
resources/lang/*/[a-zA-Z]*20[0-9][0-9][0-1][0-9][0-3][0-9]_[0-2][0-9][0-5][0-9][0-5][0-9].php
```

## 3. Usage

### 3.1 Command `localization:missing`

This command parses all your code and generates translations according to lang files in all `lang/XXX/` directories.

Use `php artisan help localization:missing` for more informations about options.

#### *Examples*

##### Generate all lang files

```bash
php artisan localization:missing
```

##### Generate all lang files without prompt

```bash
php artisan localization:missing -n
```

##### Generate all lang files without backuping old files

```bash
php artisan localization:missing -b
```

##### Generate all lang files with automatic translations

```bash
php artisan localization:missing -t
```

> You need to set your Microsoft Bing Translator credentials
> More informations here : <https://github.com/potsky/microsoft-translator-php-sdk#user-content-2-configuration>

##### Generate all lang files without keeping obsolete lemmas

```bash
php artisan localization:missing -o
```

##### Generate all lang files without any comment for new found lemmas

```bash
php artisan localization:missing -c
```

##### Generate all lang files without header comment

```bash
php artisan localization:missing -d
```

##### Generate all lang files and set new lemma values

3 commands below produce the same output:

```bash
php artisan localization:missing
php artisan localization:missing -l
php artisan localization:missing -l "TODO: %LEMMA"
```

You can customize the default generated values for unknown lemmas.

The following command let new values empty:

```bash
php artisan localization:missing -l ""
```

The following command prefixes all lemma values with "Please translate this : "

```bash
php artisan localization:missing -l "Please translate this : %LEMMA"
```

The following command set all lemma values to null to provide fallback translations to all missing values.

```bash
php artisan localization:missing -l null
```

The following command set all lemma values to "Please translate this !"

```bash
php artisan localization:missing -l 'Please translate this !'
```

##### Silent option for shell integration

```bash
#!/bin/bash

php artisan localization:missing -s
if [ $? -eq 0 ]; then
echo "Nothing to do dude, GO for release"
else
echo "I will not release in production, lang files are not clean"
fi
```

##### Simulate all operations (do not write anything) with a dry run

```bash
php artisan localization:missing -r
```

##### Open all must-edit files at the end of the process

```bash
php artisan localization:missing -e
```

You can edit the editor path in your configuration file. By default, editor is *Sublime Text* on *Mac OS X* :

```php
'editor_command_line' => '/Applications/Sublime\\ Text.app/Contents/SharedSupport/bin/subl'
```

For *PHPStorm* on *Mac OS X*:

```php
'editor_command_line' => '/usr/local/bin/phpstorm'
```

### 3.2 Command `localization:find`

This command will search in all your code for the argument as a lemma.

Use `php artisan help localization:find` for more informations about options.

#### *Examples*

##### Find regular lemma

```bash
php artisan localization:find Search
```

##### Find regular lemma with verbose

```bash
php artisan localization:find -v Search
```

##### Find regular lemma with short path displayed

```bash
php artisan localization:find -s "Search me"
```

##### Find lemma with a regular expression

```bash
php artisan localization:find -s -r "@Search.*@"
php artisan localization:find -s -r "/.*me$/"
```

> PCRE functions are used

### 3.3 Command `localization:clear`

This command will remove all backup lang files.

Use `php artisan help localization:clear` for more informations about options.

#### *Examples*

##### Remove all backups

```bash
php artisan localization:clear
```

##### Remove backups older than 7 days

```bash
php artisan localization:clear -d 7
```

## 4. Support

Use the [github issue tool](https://github.com/potsky/laravel-localization-helpers/issues) to open an issue or ask for something.

## 5. Upgrade notices

### From `v2.x.5` to `v2.x.6`

- PHPCSFixer has changed. Previous fixers are not supported anymore. Take a look at the [configuration file](https://github.com/potsky/laravel-localization-helpers/tree/master/src/config) in the package to check new rules.

### From `v2.x.4` to `v2.x.5`

- Parameter `dot_notation_split_regex` has been added in the [configuration file](https://github.com/potsky/laravel-localization-helpers/tree/master/src/config). Add it in your configuration file.

### From `v2.x.1` to `v2.x.2`

- Parameter `obsolete_array_key` has been added in the [configuration file](https://github.com/potsky/laravel-localization-helpers/tree/master/src/config). Add it in your configuration file.

### From `v1.x.x` to `v2.x.x`

- First you need to update your composer file to set the correct version
- Take a look at the [configuration file](https://github.com/potsky/laravel-localization-helpers/tree/master/src/config) in the package to add new parameters you don't have in your current package configuration file.

## 6. Change Log

### v2.x.6

- change: support Laravel 5.5
- change: update PHPCSFixer, rules have changed!
- change: translation package has been updated

### v2.x.5

- new: `dot_notation_split_regex` has been added to automatically handle dots in lemma ([#59](https://github.com/potsky/laravel-localization-helpers/issues/59))
- fix: ignore vendors lang folders ([#59](https://github.com/potsky/laravel-localization-helpers/issues/59))
- new: you can now ignore specific lang files and not only lang families ([#44](https://github.com/potsky/laravel-localization-helpers/issues/44))
- fix: in dry run mode, lang files were still created ([#54](https://github.com/potsky/laravel-localization-helpers/issues/54))
- change: try to handle dynamic lemma errors ([#53](https://github.com/potsky/laravel-localization-helpers/issues/53))
- change: change composer requirements to minimize lumen requirements ([#49](https://github.com/potsky/laravel-localization-helpers/issues/49))
- change: handle indirect translation calls ([#47](https://github.com/potsky/laravel-localization-helpers/issues/47))

### v2.x.4

- new: Support of Laravel 5.3 from the `2.4.4` branch ([#41](https://github.com/potsky/laravel-localization-helpers/issues/41))
- new: Track multi-line function calls ([#33](https://github.com/potsky/laravel-localization-helpers/issues/33))
- new: Support translation's fallback by providing null in new value ([#38](https://github.com/potsky/laravel-localization-helpers/issues/38))
- change: Use package `friendsofphp/php-cs-fixer` instead of `fabpot/php-cs-fixer` ([#28](https://github.com/potsky/laravel-localization-helpers/issues/28))

### v2.x.3

- new: adding possibility to disable check for obsolete lemmas ([#27](https://github.com/potsky/laravel-localization-helpers/pull/27))
- fix: Short-Option for "output-flat" and "php-file-extension" changed because the two-letter-code doesn't work ([#27](https://github.com/potsky/laravel-localization-helpers/pull/27))

### v2.x.2

- show obsolete lemma when it is in array ([#21](https://github.com/potsky/laravel-localization-helpers/issues/21))
- fix a bug when using obsolete option ([#22](https://github.com/potsky/laravel-localization-helpers/issues/22))

### v2.x.1

- fix a bug when using backup files and when a dot is in your laravel installation path ([#20](https://github.com/potsky/laravel-localization-helpers/issues/20))

### v2.x.0

- new command `localization:clear` to remove backups
- new option to specify output formatting ([#17](https://github.com/potsky/laravel-localization-helpers/issues/17))
- new option to specify flat arrays style output ([#18](https://github.com/potsky/laravel-localization-helpers/issues/18))
- new option to let the command translate sentences for you with Bing Translator
- new translations are now:
	- marked with the `TODO:` prefix by default (*if you ran two times the missing artisan command without translating lemma next to the first run, your missing translation were lost in the lang file. Now by default, just search for TODO in your lang file!*)
	- translated of course if option `t` is used
	- shorten to their minimal value ( `trans( 'message.child.this is a text' )` will now generate `['child'] => 'TODO: this is a text',` and no more `['child'] => 'TODO: child.this is a text',`)   

Internally :

- totally refactored
- unit tests
- test coverage
- facade to let you use localization helpers in your code (translations, find missing translations, etc...)

### v1.3.3

- End of life. Version 1.x is no more supported and no longer works. Please use the correct version according to your laravel version.

### v1.3.2

- fix incompatibility with Laravel 5.2 ([#16](https://github.com/potsky/laravel-localization-helpers/issues/16))

### v1.3.1

- add resource folder for Laravel 5

### v1.3

- add full support for Laravel 5

### v1.2.2

- add support for @lang and @choice in Blade templates (by Jesper Ekstrand)

### v1.2.1

- add `lang_folder_path` parameter in configuration file to configure the custom location of your lang files
- check lang files in `app/lang` by default for Laravel 4.x
- check lang files in `app/resources/lang` by default for Laravel 5

### v1.2

- support for Laravel 5 (4.3)
- add `ignore_lang_files` parameter in configuration file to ignore lang files (useful for `validation` file for example)

## 7. Contribute

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Commit your changes (`git commit -am 'Added some feature'`)
4. Push to the branch (`git push origin my-new-feature`)
5. Create new Pull Request

Tests are in `tests`. To run the tests: `vendor/bin/phpunit`.

Coverage cannot decrease next a merge. To track file coverage, run `vendor/bin/phpunit --coverage-html coverage` and open `coverage/index.html` to check uncovered lines of code.

Dev badges :
[![Dev Status](https://travis-ci.org/potsky/laravel-localization-helpers.svg?branch=dev)](https://travis-ci.org/potsky/laravel-localization-helpers)
[![Dev Coverage Status](https://coveralls.io/repos/potsky/laravel-localization-helpers/badge.svg?branch=dev&service=github)](https://coveralls.io/github/potsky/laravel-localization-helpers?branch=dev)
