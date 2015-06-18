# Silex CORS Service Provider

[![Build Status](https://travis-ci.org/euskadi31/CorsServiceProvider.svg?branch=master)](https://travis-ci.org/euskadi31/CorsServiceProvider)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/07bf7885-e810-48e8-9043-e30f49f1f2e7/mini.png)](https://insight.sensiolabs.com/projects/07bf7885-e810-48e8-9043-e30f49f1f2e7)

The CorsServiceProvider provides [CORS](http://enable-cors.org/) support as middleware for your silex 2.0 application. CORS
allows you to make AJAX requests across domains. CORS uses OPTIONS requests to make preflight requests. Because silex
doesn't have functionality for serving OPTIONS request by default, this service goes through all of your routes and
generates the necessary OPTIONS routes.


## Install

Add `euskadi31/cors-service-provider` to your `composer.json`:

    % php composer.phar require euskadi31/cors-service-provider:~1.0

## Usage

### Configuration

```php
<?php

$app = new Silex\Application;

$app->register(new \Euskadi31\Silex\Provider\CorsServiceProvider);
```

## License

CorsServiceProvider is licensed under [the MIT license](LICENSE.md).
