<?php declare(strict_types=1);

namespace DJTommek\Coordinates\Tests;

use DJTommek\Coordinates\CoordinatesInterface;

final class DummyCoordinates implements CoordinatesInterface
{
	public function __construct(
		public readonly float $lat,
		public readonly float $lon,
		public readonly float $altitude,
	)
	{
	}

	public function getLat(): float
	{
		return $this->lat;
	}

	public function getLon(): float
	{
		return $this->lon;
	}

	public function getLatLon(string $delimiter = ','): string
	{
		return $this->lat . $delimiter . $this->lon;
	}
}
