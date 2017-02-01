# MySQL Library for PHP

[![Build Status](https://travis-ci.org/kodols/php-mysql-library.svg?branch=master)](https://travis-ci.org/kodols/php-mysql-library)
[![Latest Stable Version](https://poser.pugx.org/kodols/PHP-MySQL-Library/v/stable.svg)](https://packagist.org/packages/kodols/php-mysql-library)
[![Total Downloads](https://poser.pugx.org/kodols/PHP-MySQL-Library/downloads.svg)](packagist.org/packages/kodols/php-mysql-library)
[![License](https://poser.pugx.org/kodols/PHP-MySQL-Library/license.svg)](packagist.org/packages/kodols/php-mysql-library)

This library will give you access to everything you need from MySQL in PHP. 

See our [Object Reference](https://github.com/kodols/PHP-MySQL-Library/wiki) to learn what is possible, or view our [demo folder](https://github.com/kodols/PHP-MySQL-Library/tree/master/demo) for examples.

## About

The library is a hybrid between PHP's PDO and generic query building.

Started as a helper class for in-house projects and grown into a usefull tool for Kodols Ltd. A decision was made to make this library public give others the chance to use a well oriented library for database management.

Library will keep getting support and upgrades as this has been become the goto library for Kodols Ltd. to use for our projects.

This library supports access to the native PDO methods, few helper methods to simplify commonly used methods and a query builder to really give you access to build cool statements.

### Prerequisites

This library will work on PHP 5.4 or later

### Installing

You can clone this git repository into your project 

```
git clone git://github.com/kodols/PHP-MySQL-Library.git
```

or you can use composer

```
composer require kodols/php-mysql-library
```

## Deployment

The integration process is very simple, you call the main library class, add a configuration and you are all set.
However we would recommend spend some time to really prepare the environment so you get the best out of this library.
The library is based in `src/` with its namespace `\Kodols\MySQL`. In our examples we will use a demo variable $KML (Kodols MySQL Library), but of course you can use any variable you like. 

```
$KML = new \Kodols\MySQL\Library;
```

Once you have successfuly create the object you need to add MySQL connection details to it. The library supports multiple configurations attached and multiple connections established in the same request.

```
$config = $KML->newConfiguration();
$config->setHostname('my.host.com');
$config->setUsername('web');
$config->setPassword('42412');
$config->setDatabase('project');
```
The `$config` variable is `\Kodols\MySQL\Configuration` object, see Wiki for in depth details.

Once configuration is created you can attach it to the main library via `$KML->attachConfiguration($config);` and start using the library. However if your environment has multiple database endpoints, or maybe you have production, development and local environment, then you can setup it as follows:
```
$config = $KML->newConfiguration();
$config->setHostname('my.host.com');
...
$KML->attachConfiguration($config, 'live');
```
So when you call the server variable you can choose with of the server endpoints you want to use.

## Contributors

We welcome a good idea, bug fixes, suggestions and requests.
This list will be updated every time a new contribution has been made.

* **Edgars Kohs** - *author* - [Kodols Ltd.](http://www.kodols.com)

## License

This project is licensed under the MIT License - see the [https://github.com/kodols/php-mysql-library/blob/master/LICENSE](LICENSE) file for details
