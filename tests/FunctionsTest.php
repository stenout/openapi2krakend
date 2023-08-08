<?php

namespace OpenApi2KrakenD\Tests;

use OpenApi2KrakenD\OpenApi;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider dataForConvert
     */
    public function testConvert(OpenApi $openApi, string $krakendJson)
    {
        $this->assertEquals(
            $krakendJson,
            \OpenApi2KrakenD\convert( $openApi, 'https://krakend.test')->toPrettyJson()
        );
    }

    public static function dataForConvert(): iterable
    {
        $testDataDir = __DIR__ . '/testdata';
        yield [
            'openApi' => OpenApi::fromFile("{$testDataDir}/openapi.json"),
            'krakendJson' => file_get_contents("{$testDataDir}/krakend.json"),
        ];
    }
}
