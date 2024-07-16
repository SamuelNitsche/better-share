# Better Share for Laravel

Better Share is a package for Laravel, which makes sharing your project easier.

It was inspired by [this video from Aaron Francis](https://www.youtube.com/watch?v=pT7e31DMTYY) and aims for a simpler
usage.

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

The package assumes that you have the `ngrok` binary installed on your system and available in your `$PATH`. You can
download it from [ngrok.com](https://ngrok.com/download).

## Usage

The package registers a `share` command in your Laravel project. You can use it like this:

```bash
php artisan share
```

## Roadmap

- [ ] Add support for Vite hot reloading
