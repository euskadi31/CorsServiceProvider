# Silex CORS Service Provider

[![Build Status](https://travis-ci.org/euskadi31/CorsServiceProvider.svg?branch=master)](https://travis-ci.org/euskadi31/CorsServiceProvider)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2c558cf6-6607-4eba-a1c1-08f60e1d14ae/mini.png)](https://insight.sensiolabs.com/projects/2c558cf6-6607-4eba-a1c1-08f60e1d14ae)

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
