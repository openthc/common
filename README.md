# Common Library

This package includes a number of common configuration files, PHP classes and functions, Slim components and Twig templates.

## Lib

This directory is autoloaded, all the classes are in the **OpenTHC** namespace.


## Slim

There are some Slim components included as well

 * OpenTHC\App - a Slim App
 * OpenTHC\Middleware\Base
 * OpenTHC\Middleware\CORS
 * OpenTHC\Middleware\Log
 * OpenTHC\Middleware\RateLimit
 * OpenTHC\Middleware\Session
 * OpenTHC\Controller\Base
 * OpenTHC\Controller\OAuth2


## Twig

Adds the `./vendor/openthc/twig` path to the Twig loader.
We also provide some wrapper templates for `home.html` and `page-app.html`
