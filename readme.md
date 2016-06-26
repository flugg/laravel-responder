# Laravel Responder

[![Latest Stable Version](https://poser.pugx.org/mangopixel/laravel-responder/v/stable?format=flat-square)](https://github.com/mangopixel/laravel-responder)
[![Packagist Downloads](https://img.shields.io/packagist/dt/mangopixel/laravel-responder.svg?style=flat-square)](https://packagist.org/packages/mangopixel/laravel-responder)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](license.md)
[![Build Status](https://img.shields.io/travis/mangopixel/laravel-responder/master.svg?style=flat-square)](https://travis-ci.org/mangopixel/laravel-responder)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/mangopixel/laravel-responder.svg?style=flat-square)](https://scrutinizer-ci.com/g/mangopixel/laravel-responder/?branch=master)

__Work in progress, do not use in production!__

## Requirements

This package requires:
- PHP 7.0+
- Laravel 5.0+

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

## Usage

## License

Laravel Responder is free software distributed under the terms of the MIT license.
