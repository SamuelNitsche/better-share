# Better Share for Laravel

Better Share is a package for Laravel, which makes sharing your project easier.

## Installation

You can install the package via composer:

```bash
composer require samuelnitsche/better-share
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="SamuelNitsche\BetterShare\BetterShareServiceProvider" --tag="config"
```

## Requirements

The package assumes that you have the `ngrok` binary installed on your system and available in your `$PATH`. You can download it from [ngrok.com](https://ngrok.com/download).

## Usage

The package registers a `share` command in your Laravel project. You can use it like this:

```bash
php artisan share
```


