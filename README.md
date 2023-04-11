# [Coordinates](https://github.com/DJTommek/coordinates)

Data object for storing valid coordinates on Earth in decimal [WGS-84](https://en.wikipedia.org/wiki/World_Geodetic_System) format.

[![Packagist Version](https://img.shields.io/packagist/v/DJTommek/coordinates?label=Packagist&style=flat-square)](https://packagist.org/packages/djtommek/mapycz-api)
[![GitHub Repo stars](https://img.shields.io/github/stars/DJTommek/coordinates?label=Github%20stars&style=flat-square)](https://github.com/DJTommek/coordinates)

## Installation

```
composer require djtommek/coordinates
```

## Usage example

```php
<?php
$prague = new \DJTommek\Coordinates\Coordinates(50.0875, 14.4213);
printf('Prague: %s', $prague); // Prague: 50.087500,14.421300

$berlin = new \DJTommek\Coordinates\CoordinatesImmutable(52.518611, 13.408333);
$distance = $prague->distance($berlin);

printf('Distance between Prague and Berlin is %d km', $distance / 1000);
// 'Distance between Prague and Berlin is 279 km'
```

See source code for more methods and tests for more examples.

## Testing

```
composer test
```
