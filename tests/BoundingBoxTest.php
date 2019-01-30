<?php

namespace Geokit;

class BoundingBoxTest extends TestCase
{
    /**
     * @param BoundingBox  $b
     * @param float $w
     * @param float $s
     * @param float $e
     * @param float $n
     */
    protected function assertBoundingBox(BoundingBox $b, $s, $w, $n, $e)
    {
        $this->assertEquals($s, $b->getSouthWest()->getLatitude());
        $this->assertEquals($w, $b->getSouthWest()->getLongitude());
        $this->assertEquals($n, $b->getNorthEast()->getLatitude());
        $this->assertEquals($e, $b->getNorthEast()->getLongitude());
    }

    public function testConstructorShouldAcceptLatLngsAsFirstAndSecondArgument()
    {
        $bbox = new BoundingBox(new LatLng(2.5678, 1.1234), new LatLng(4.5678, 3.1234));

        $this->assertInstanceOf('\Geokit\LatLng', $bbox->getSouthWest());
        $this->assertEquals(1.1234, $bbox->getSouthWest()->getLongitude());
        $this->assertEquals(2.5678, $bbox->getSouthWest()->getLatitude());

        $this->assertInstanceOf('\Geokit\LatLng', $bbox->getNorthEast());
        $this->assertEquals(3.1234, $bbox->getNorthEast()->getLongitude());
        $this->assertEquals(4.5678, $bbox->getNorthEast()->getLatitude());
    }

    public function testConstructorShouldThrowExceptionForInvalidSouthCoordinate()
    {
        $this->expectException(Exception\LogicException::class);
        new BoundingBox(new LatLng(1, 90), new LatLng(0, 90));
    }

    public function testGetCenterShouldReturnALatLngObject()
    {
        $bbox = new BoundingBox(new LatLng(2.5678, 1.1234), new LatLng(4.5678, 3.1234));
        $center = new LatLng(3.5678, 2.1234);

        $this->assertEquals($center, $bbox->getCenter());

        $bbox = new BoundingBox(new LatLng(-45, 179), new LatLng(45, -179));
        $center = new LatLng(0, 180);

        $this->assertEquals($center, $bbox->getCenter());
    }

    public function testGetSpanShouldReturnALatLngObject()
    {
        $bbox = new BoundingBox(new LatLng(2.5678, 1.1234), new LatLng(4.5678, 3.1234));

        $span = new LatLng(2, 2);

        $this->assertEquals($span, $bbox->getSpan());
    }

    public function testContains()
    {
        $bbox = new BoundingBox(new LatLng(-122, 37), new LatLng(-123, 38));

        $this->assertTrue($bbox->contains(new LatLng(-122, 37)));
        $this->assertTrue($bbox->contains(new LatLng(-123, 38)));

        $this->assertFalse($bbox->contains(new LatLng(-70, -12)));
    }

    public function testExtendInACircle()
    {
        $bbox = new BoundingBox(new LatLng(0, 0), new LatLng(0, 0));
        $bbox = $bbox->extend(new LatLng(0, 1));
        $bbox = $bbox->extend(new LatLng(1, 0));
        $bbox = $bbox->extend(new LatLng(0, -1));
        $bbox = $bbox->extend(new LatLng(-1, 0));
        $this->assertBoundingBox($bbox, -1, -1, 1, 1);
    }

    public function testUnion()
    {
        $bbox = new BoundingBox(new LatLng(37, -122), new LatLng(37, -122));

        $bbox = $bbox->union(new BoundingBox(new LatLng(-38, 123), new LatLng(38, -123)));
        $this->assertBoundingBox($bbox, -38, 123, 38, -122);
    }

    public function testCrossesAntimeridian()
    {
        $bbox = new BoundingBox(new LatLng(-45, 179), new LatLng(45, -179));

        $this->assertTrue($bbox->crossesAntimeridian());
        $this->assertEquals(90, $bbox->getSpan()->getLatitude());
        $this->assertEquals(2, $bbox->getSpan()->getLongitude());
    }

    public function testCrossesAntimeridianViaExtend()
    {
        $bbox = new BoundingBox(new LatLng(-45, 179), new LatLng(45, -179));

        $bbox = $bbox->extend(new LatLng(90, -180));

        $this->assertTrue($bbox->crossesAntimeridian());
        $this->assertEquals(90, $bbox->getSpan()->getLatitude());
        $this->assertEquals(2, $bbox->getSpan()->getLongitude());
    }

    public function testExpand()
    {
        $bbox = new BoundingBox(new LatLng(-45, 179), new LatLng(45, -179));

        $expandedBbox = $bbox->expand(new Distance(100));

        $this->assertEquals(
            -45.00089932036373,
            $expandedBbox->getSouthWest()->getLatitude()
        );
        $this->assertEquals(
            178.99872816894472,
            $expandedBbox->getSouthWest()->getLongitude()
        );
        $this->assertEquals(
            45.00089932036373,
            $expandedBbox->getNorthEast()->getLatitude()
        );
        $this->assertEquals(
            -178.99872816894472,
            $expandedBbox->getNorthEast()->getLongitude()
        );
    }

    public function testShrink()
    {
        $bbox = new BoundingBox(
            new LatLng(-45.000898315284132, 178.99872959034192),
            new LatLng(45.000898315284132, -178.99872959034192)
        );

        $shrinkedBbox = $bbox->shrink(
            new Distance(100)
        );

        $this->assertEquals(
            -44.999998994920404,
            $shrinkedBbox->getSouthWest()->getLatitude()
        );
        $this->assertEquals(
            179.00000144133816,
            $shrinkedBbox->getSouthWest()->getLongitude()
        );
        $this->assertEquals(
            44.999998994920404,
            $shrinkedBbox->getNorthEast()->getLatitude()
        );
        $this->assertEquals(
            -179.00000144133816,
            $shrinkedBbox->getNorthEast()->getLongitude()
        );
    }

    public function testShrinkTooMuch()
    {
        $bbox = new BoundingBox(
            new LatLng(1, 1),
            new LatLng(1, 1)
        );

        $shrinkedBbox = $bbox->shrink(
            new Distance(100)
        );

        $this->assertEquals(
            1,
            $shrinkedBbox->getSouthWest()->getLatitude()
        );
        $this->assertEquals(
            1,
            $shrinkedBbox->getSouthWest()->getLongitude()
        );
        $this->assertEquals(
            1,
            $shrinkedBbox->getNorthEast()->getLatitude()
        );
        $this->assertEquals(
            1,
            $shrinkedBbox->getNorthEast()->getLongitude()
        );
    }

    public function testToPolygon()
    {
        $bbox = new BoundingBox(
            new LatLng(0, 0),
            new LatLng(10, 10)
        );

        $polygon = $bbox->toPolygon();

        $this->assertCount(5, $polygon);
        $this->assertTrue($polygon->isClosed());

        $array = iterator_to_array($polygon);

        $this->assertEquals(
            0,
            $array[0]->getLatitude()
        );
        $this->assertEquals(
            0,
            $array[0]->getLongitude()
        );

        $this->assertEquals(
            0,
            $array[1]->getLatitude()
        );
        $this->assertEquals(
            10,
            $array[1]->getLongitude()
        );

        $this->assertEquals(
            10,
            $array[2]->getLatitude()
        );
        $this->assertEquals(
            10,
            $array[2]->getLongitude()
        );

        $this->assertEquals(
            10,
            $array[3]->getLatitude()
        );
        $this->assertEquals(
            0,
            $array[3]->getLongitude()
        );
    }
}
