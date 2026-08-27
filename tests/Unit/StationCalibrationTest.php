<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Admin\StationController;
use ReflectionClass;

class StationCalibrationTest extends TestCase
{
    private function invokeParseGoogleMapsUrl(string $url): ?array
    {
        $controller = new StationController();
        $reflector = new ReflectionClass($controller);
        $method = $reflector->getMethod('parseGoogleMapsUrl');
        return $method->invoke($controller, $url);
    }

    public function test_it_parses_google_maps_at_coordinates()
    {
        $url = 'https://www.google.com/maps/place/Umbulan/@-7.7572565,112.9314949,17z/data=!3m1!4b1';
        $result = $this->invokeParseGoogleMapsUrl($url);

        $this->assertNotNull($result);
        $this->assertEquals('-7.7572565', $result['latitude']);
        $this->assertEquals('112.9314949', $result['longitude']);
    }

    public function test_it_parses_google_maps_query_param_coordinates()
    {
        $url = 'https://maps.google.com/?q=-7.123456,112.654321';
        $result = $this->invokeParseGoogleMapsUrl($url);

        $this->assertNotNull($result);
        $this->assertEquals('-7.123456', $result['latitude']);
        $this->assertEquals('112.654321', $result['longitude']);
    }

    public function test_it_parses_google_maps_3d_data_coordinates()
    {
        $url = 'https://www.google.com/maps/place/Office/data=!4m2!3m1!1s0x0:0x0!3d-7.5812341!4d112.7212341';
        $result = $this->invokeParseGoogleMapsUrl($url);

        $this->assertNotNull($result);
        $this->assertEquals('-7.5812341', $result['latitude']);
        $this->assertEquals('112.7212341', $result['longitude']);
    }

    public function test_it_parses_google_maps_center_coordinates()
    {
        $url = 'https://maps.google.com/?center=-7.2574719,112.7520883&zoom=15';
        $result = $this->invokeParseGoogleMapsUrl($url);

        $this->assertNotNull($result);
        $this->assertEquals('-7.2574719', $result['latitude']);
        $this->assertEquals('112.7520883', $result['longitude']);
    }

    public function test_it_parses_raw_coordinates_string()
    {
        $raw = ' -7.7572565 , 112.9314949 ';
        $result = $this->invokeParseGoogleMapsUrl($raw);

        $this->assertNotNull($result);
        $this->assertEquals('-7.7572565', $result['latitude']);
        $this->assertEquals('112.9314949', $result['longitude']);
    }
}
