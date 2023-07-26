<?php

namespace OpenApi2KrakenD\Tests;

use OpenApi2KrakenD\KrakenD;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class KrakenDTest extends TestCase
{
    /**
     * @covers KrakenD::__construct
     * @dataProvider dataForTestConstruct
     */
    public function testConstruct(array $config, array $expected)
    {
        $krakenD = new KrakenD($config);
        $reflectionKrakenD = new ReflectionClass('OpenApi2KrakenD\KrakenD');
        $actual = $reflectionKrakenD->getProperty('data')->getValue($krakenD);

        $this->assertEquals($expected, $actual);
    }

    public static function dataForTestConstruct(): iterable
    {
        yield 'default config' => [
            'config' => [],
            'expected' => KrakenD::DEFAULT_CONFIG,
        ];

        $config = [
            '$schema' => 'https://www.krakend.io/schema/v2.1/krakend.json',
            'version' => 2,
        ];
        yield 'custom config' => [
            'config' => $config,
            'expected' => $config,
        ];
    }
}
