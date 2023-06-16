<?php declare(strict_types=1);

namespace DJTommek\Coordinates\Tests;

use DJTommek\Coordinates\CoordinatesImmutable;
use DJTommek\Coordinates\Exceptions\CoordinatesException;

final class CoordinatesImmutableTest extends CoordinatesTestAbstract
{
	private function randomCoords(): CoordinatesImmutable
	{
		return new CoordinatesImmutable($this->randomLat(), $this->randomLon());
	}

	/**
	 * @dataProvider validCoordinatesProvider
	 */
	public function testIsValid(mixed $latInput, mixed $lonInput, string $_): void
	{
		$this->assertTrue(CoordinatesImmutable::isLat($latInput));
		$this->assertTrue(CoordinatesImmutable::isLon($lonInput));
	}

	/**
	 * @dataProvider validCoordinatesProvider
	 */
	public function testCoordinatesImmutable(mixed $latInput, mixed $lonInput, string $keyExpected): void
	{
		$coords = new CoordinatesImmutable($latInput, $lonInput);
		$this->abstractTestCoordinates($coords, $keyExpected);

		$this->assertEqualsWithDelta($latInput, $coords->lat, 0.000_000_1);
		$this->assertEqualsWithDelta($lonInput, $coords->lon, 0.000_000_1);
	}

	/**
	 * @dataProvider validCoordinatesProvider
	 */
	public function testCoordinatesImmutableSafe(mixed $latInput, mixed $lonInput, string $keyExpected): void
	{
		$coords = CoordinatesImmutable::safe($latInput, $lonInput);
		$this->assertInstanceOf(CoordinatesImmutable::class, $coords);
		$this->abstractTestCoordinates($coords, $keyExpected);

		$this->assertEqualsWithDelta($latInput, $coords->lat, 0.000_000_1);
		$this->assertEqualsWithDelta($lonInput, $coords->lon, 0.000_000_1);
	}

	/**
	 * @dataProvider invalidCoordinateTypeProvider
	 */
	public function testInvalidValues(mixed $invalidValue): void
	{
		$this->assertNull(CoordinatesImmutable::safe($invalidValue, $invalidValue));

		$this->expectException(CoordinatesException::class);
		new CoordinatesImmutable($invalidValue, $invalidValue);
	}

	/**
	 * @dataProvider outOfRangeCoordinatesProvider
	 */
	public function testCoordsOutOfRange(mixed $latInput, mixed $lonInput): void
	{
		$this->assertFalse(
			CoordinatesImmutable::isLat($latInput)
			&& CoordinatesImmutable::isLon($lonInput),
		);

		$this->assertNull(CoordinatesImmutable::safe($latInput, $lonInput));

		$this->expectException(CoordinatesException::class);
		new CoordinatesImmutable($latInput, $lonInput);
	}

	public function testWithCoordinatesRandom(): void
	{
		$lat1 = self::randomLat();
		$lon1 = self::randomLon();

		$lat2 = self::randomLat();
		$lon3 = self::randomLon();

		$coords1 = new CoordinatesImmutable($lat1, $lon1);
		$this->assertSame($lat1, $coords1->lat);
		$this->assertSame($lon1, $coords1->lon);
		$key1 = $coords1->key();

		// Create new object from provided but change latitude int new object (original object must not change)
		$coords2 = $coords1->withLat($lat2);
		$this->assertInstanceOf(CoordinatesImmutable::class, $coords2);
		$this->assertNotSame($coords1, $coords2);

		$this->assertSame($lat1, $coords1->lat);
		$this->assertSame($lon1, $coords1->lon);
		$this->assertSame($key1, $coords1->key());
		// New object shares longitude, otherwise is different:
		$this->assertSame($coords1->lon, $coords2->lon);
		$this->assertNotSame($coords1->lat, $coords2->lat);
		$this->assertNotSame($coords1->key(), $coords2->key());

		// Create new object from provided but change longitude in new object (original object must not change)
		$coords3 = $coords1->withLon($lon3);
		$this->assertInstanceOf(CoordinatesImmutable::class, $coords3);
		$this->assertNotSame($coords1, $coords3);

		$this->assertSame($lat1, $coords1->lat);
		$this->assertSame($lon1, $coords1->lon);
		$this->assertSame($key1, $coords1->key());
		// New object shares latitude, otherwise is different:
		$this->assertSame($coords1->lat, $coords3->lat);
		$this->assertNotSame($coords1->lon, $coords3->lon);
		$this->assertNotSame($coords1->key(), $coords3->key());
	}

	/**
	 * @dataProvider validCoordinatesFromStringProvider
	 */
	public function testValidFromString(string $input, float $latExpected, float $lonExpected, string $separator = null): void
	{
		if ($separator === null) {
			$coords = CoordinatesImmutable::fromString($input);
		} else {
			$coords = CoordinatesImmutable::fromString($input, $separator);
		}
		$this->assertSame($latExpected, $coords->lat);
		$this->assertSame($lonExpected, $coords->lon);
	}

	/**
	 * @dataProvider invalidCoordinatesFromStringProvider
	 */
	public function testInvalidFromString(string $input, string $separator = null): void
	{
		if ($separator === null) {
			$result = CoordinatesImmutable::fromString($input);
		} else {
			$result = CoordinatesImmutable::fromString($input, $separator);
		}
		$this->assertNull($result);
	}

	/**
	 * @dataProvider validCoordinatesProvider
	 */
	public function testFromStringValid2(float|string $expectedLat, float|string $expectedLon, string $input): void
	{
		$coords = CoordinatesImmutable::fromString($input);
		$this->assertEqualsWithDelta($coords->lat, $expectedLat, 0.000_001);
		$this->assertEqualsWithDelta($coords->lon, $expectedLon, 0.000_001);
	}

	/**
	 * @dataProvider distanceProvider
	 */
	public function testDistance(float $expectedDistance, float $lat1, float $lon1, float $lat2, float $lon2): void
	{
		$coords1 = new CoordinatesImmutable($lat1, $lon1);
		$coords2 = new CoordinatesImmutable($lat2, $lon2);

		$this->abstractTestDistance($expectedDistance, $coords1, $coords2);
	}

	/**
	 * Generate random coordinates and compare distance between by using first and second set of method argument.
	 *
	 * @dataProvider iterator100Provider
	 */
	public function testDistanceGenerated(): void
	{
		$coords1 = $this->randomCoords();
		$coords2 = $this->randomCoords();

		$this->assertEqualsWithDelta(
			$coords1->distance($coords2),
			$coords2->distance($coords1),
			0.000_000_01,
		);
	}

	public function testDistanceStatic(): void
	{
		$this->assertSame(0.0, CoordinatesImmutable::distanceLatLon(50.087725, 14.4211267, 50.087725, 14.4211267));
		$this->assertSame(42.16747601866312, CoordinatesImmutable::distanceLatLon(50.087725, 14.4211267, 50.0873667, 14.4213203));
		$this->assertSame(1_825.0239867033586, CoordinatesImmutable::distanceLatLon(36.6323425, -121.9340617, 36.6219297, -121.9182533));

		$this->assertEqualsWithDelta( // same coordinates, just switched
			CoordinatesImmutable::distanceLatLon(50, 14, 51, 15),
			CoordinatesImmutable::distanceLatLon(51, 15, 50, 14),
			0.000_000_01,
		);
		$this->assertSame(4_532.050463078125, CoordinatesImmutable::distanceLatLon(50.08904, 14.42890, 50.07406, 14.48797));
		$this->assertSame(11_471_646.428581407, CoordinatesImmutable::distanceLatLon(-50.08904, 14.42890, 50.07406, -14.48797));
	}

	/**
	 * Generate random coordinates and compare distance between by using first and second set of method argument.
	 *
	 * @dataProvider iterator100Provider
	 */
	public function testDistanceStaticGenerated(): void
	{
		$lat1 = $this->randomLat();
		$lon1 = $this->randomLon();

		$lat2 = $this->randomLat();
		$lon2 = $this->randomLon();

		$this->assertEqualsWithDelta(
			CoordinatesImmutable::distanceLatLon($lat1, $lon1, $lat2, $lon2),
			CoordinatesImmutable::distanceLatLon($lat2, $lon2, $lat1, $lon1),
			0.000_000_01,
		);
	}

	/**
	 * @dataProvider invalidLatitudesProvider
	 */
	public function testisLatInvalid(mixed $input): void
	{
		$this->assertFalse(CoordinatesImmutable::isLat($input), sprintf('Input "%s" should not be valid latitude.', $input));
	}

	/**
	 * @dataProvider invalidLongitudesProvider
	 */
	public function testisLonInvalid(mixed $input): void
	{
		$this->assertFalse(CoordinatesImmutable::isLon($input), sprintf('Input "%s" should not be valid longitude.', $input));
	}

	/**
	 * @param array<array{float, float}> $polygon
	 *
	 * @dataProvider polygonsProvider
	 */
	public function testIsInPolygon(array $polygon, float $lat, float $lon): void
	{
		$coords = new CoordinatesImmutable($lat, $lon);
		$this->assertTrue($coords->isInPolygon($polygon));

		$coordsOut = new CoordinatesImmutable(12, -55);
		$this->assertFalse($coordsOut->isInPolygon($polygon));
	}
}
