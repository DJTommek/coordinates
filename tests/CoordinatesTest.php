<?php declare(strict_types=1);

namespace DJTommek\Coordinates\Tests;

use DJTommek\Coordinates\Coordinates;
use DJTommek\Coordinates\Exceptions\CoordinatesException;

final class CoordinatesTest extends CoordinatesTestAbstract
{
	private function randomCoords(): Coordinates
	{
		return new Coordinates($this->randomLat(), $this->randomLon());
	}

	/**
	 * @dataProvider validCoordinatesProvider
	 */
	public function testCoordinates(mixed $latInput, mixed $lonInput, string $keyExpected): void
	{
		$coords = new Coordinates($latInput, $lonInput);
		$this->abstractTestCoordinates($coords, $keyExpected);

		$this->assertEqualsWithDelta($latInput, $coords->lat, 0.000_000_1);
		$this->assertEqualsWithDelta($lonInput, $coords->lon, 0.000_000_1);
	}

	/**
	 * @dataProvider outOfRangeCoordinatesProvider
	 */
	public function testCoordsOutOfRange(mixed $latInput, mixed $lonInput): void
	{
		$this->expectException(CoordinatesException::class);
		new Coordinates($latInput, $lonInput);
	}

	/**
	 * @dataProvider iterator100Provider
	 */
	public function testSetCoordinatesRandom(): void
	{
		$lat1 = self::randomLat();
		$lon1 = self::randomLon();

		$lat2 = self::randomLat();
		$lon3 = self::randomLon();

		$coords1 = new Coordinates($lat1, $lon1);
		$this->assertSame($lat1, $coords1->lat);
		$this->assertSame($lon1, $coords1->lon);
		$key1 = $coords1->key();

		// Test latitude
		// Updating object and returning self, no new object is being created
		$coords2 = $coords1->setLat($lat2);
		$this->assertSame($coords1, $coords2);

		$this->assertSame($coords1->lat, $lat2);
		$this->assertSame($coords1->lon, $lon1);
		$this->assertNotSame($key1, $coords1->key());

		// Set coordinate back to original value using magic setter
		$coords1->lat = $lat1;
		$this->assertSame($coords1->lat, $lat1);
		$this->assertSame($key1, $coords1->key());

		// Test longitude
		// Updating object and returning self, no new object is being created
		$coords3 = $coords1->setLon($lon3);
		$this->assertSame($coords1, $coords3);

		$this->assertSame($coords1->lat, $lat1);
		$this->assertSame($coords1->lon, $lon3);
		$this->assertNotSame($key1, $coords1->key());

		// Set coordinate back to original value using magic setter
		$coords1->lon = $lon1;
		$this->assertSame($coords1->lon, $lon1);
		$this->assertSame($key1, $coords1->key());
	}

	/**
	 * @dataProvider validCoordinatesFromStringProvider
	 */
	public function testValidFromString(string $input, float $latExpected, float $lonExpected, string $separator = null)
	{
		if ($separator === null) {
			$coords = Coordinates::fromString($input);
		} else {
			$coords = Coordinates::fromString($input, $separator);
		}
		$this->assertSame($latExpected, $coords->lat);
		$this->assertSame($lonExpected, $coords->lon);
	}

	/**
	 * @dataProvider invalidCoordinatesFromStringProvider
	 */
	public function testInvalidFromString(string $input, string $separator = null)
	{
		if ($separator === null) {
			$result = Coordinates::fromString($input);
		} else {
			$result = Coordinates::fromString($input, $separator);
		}
		$this->assertNull($result);
	}

	/**
	 * @dataProvider validCoordinatesProvider
	 */
	public function testFromStringValid2(float|string $expectedLat, float|string $expectedLon, string $input): void
	{
		$coords = Coordinates::fromString($input);
		$this->assertEqualsWithDelta($coords->lat, $expectedLat, 0.000_001);
		$this->assertEqualsWithDelta($coords->lon, $expectedLon, 0.000_001);
	}

	public function testDistanceBasic(): void
	{
		$prague = new Coordinates(50.0875, 14.4213);
		$berlin = new Coordinates(52.51861112345, 13.40833312345);

		$this->abstractTestDistanceBasic($prague, $berlin);
	}

	/**
	 * @dataProvider distanceProvider
	 */
	public function testDistance(float $expectedDistance, float $lat1, float $lon1, float $lat2, float $lon2): void
	{
		$coords1 = new Coordinates($lat1, $lon1);
		$coords2 = new Coordinates($lat2, $lon2);

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
		$this->assertSame(0.0, Coordinates::distanceLatLon(50.087725, 14.4211267, 50.087725, 14.4211267));
		$this->assertSame(42.16747601866312, Coordinates::distanceLatLon(50.087725, 14.4211267, 50.0873667, 14.4213203));
		$this->assertSame(1_825.0239867033586, Coordinates::distanceLatLon(36.6323425, -121.9340617, 36.6219297, -121.9182533));

		$this->assertEqualsWithDelta( // same coordinates, just switched
			Coordinates::distanceLatLon(50, 14, 51, 15),
			Coordinates::distanceLatLon(51, 15, 50, 14),
			0.000_000_01,
		);
		$this->assertSame(4_532.050463078125, Coordinates::distanceLatLon(50.08904, 14.42890, 50.07406, 14.48797));
		$this->assertSame(11_471_646.428581407, Coordinates::distanceLatLon(-50.08904, 14.42890, 50.07406, -14.48797));
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
			Coordinates::distanceLatLon($lat1, $lon1, $lat2, $lon2),
			Coordinates::distanceLatLon($lat2, $lon2, $lat1, $lon1),
			0.000_000_01,
		);
	}
}
