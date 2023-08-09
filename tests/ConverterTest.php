<?php

namespace OpenApi2KrakenD\Tests;

use OpenApi2KrakenD\Converter;
use OpenApi2KrakenD\KrakenD;
use OpenApi2KrakenD\OpenApi;
use PHPUnit\Framework\TestCase;

class ConverterTest extends TestCase
{
    /**
     * @dataProvider dataForConvert
     */
    public function testConvert(OpenApi $openApi, string $krakendJson)
    {
        $krakenD = new KrakenD('https://krakend.test');
        $this->assertEquals(
            $krakendJson,
            (new Converter())->convert($openApi, $krakenD)->toPrettyJson()
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
