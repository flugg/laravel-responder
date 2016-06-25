# Laravel Adjuster

[![Latest Stable Version](https://poser.pugx.org/mangopixel/laravel-adjuster/v/stable?format=flat-square)](https://github.com/mangopixel/laravel-adjuster)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mangopixel/laravel-adjuster.svg?style=flat-square)](https://packagist.org/packages/mangopixel/laravel-adjuster)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)
[![Build Status](https://img.shields.io/travis/mangopixel/laravel-adjuster/master.svg?style=flat-square)](https://travis-ci.org/mangopixel/laravel-adjuster)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/mangopixel/laravel-adjuster.svg?style=flat-square)](https://scrutinizer-ci.com/g/mangopixel/laravel-adjuster/?branch=master)


A Laravel package for updating your Eloquent models indirectly using an adjustments table. This allows you to overwrite a model's attributes without changing the model directly. This can be useful in cases where you don't have control over the data flow of your models. 

A concrete example of its usefulness is when you feed a table with data from an API, and use a cron job to keep the table updated with the most recent data. In this case you might want to keep the table untouched so your changes are not overwritten by newer updates without you realising. Updating the table using an adjuster solves this problem as all adjustments you make to the data are stored in another table.

Most of the functionality lives in a trait, making it easy to use in your models. You can also adjust multiple models using the same adjustments table, as it uses polymorphic relationships. The package is well tested and extremely lightweight.

## Requirements

This package requires:
- PHP 7.0+
- Laravel 5.0+

The default adjustments migration also uses a JSON column to store changes. JSON columns are only supported in MySQL version 5.7 or higher and other databased that support Json. You may also change the migration data type to something else (like text) if your selected database doesn't support JSON columns.

## Installation

Install the package through Composer:

```shell
composer require mangopixel/laravel-responder
```

After updating Composer, append the following service provider to the `providers` key in `config/app.php`:

```php
Mangopixel\Responder\ResponderServiceProvider::class
```

You may also publish the package configuration file using the following Artisan command:

```shell
php artisan vendor:publish --provider="Mangopixel\Responder\ResponderServiceProvider"
```

The configuration file is well documented and you may edit it to suit your needs. You may also edit the migration file to your liking. However, make sure you update the `adjustable_column` and `changes_column` values in the configuration if you change the default column names.

## Usage

## License

Laravel Adjuster is free software distributed under the terms of the MIT license.
