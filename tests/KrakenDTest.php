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
    public function testConstruct(string $host, array $config, array $expectedConfig)
    {
        $krakenD = new KrakenD($host, $config);
        $reflectionKrakenD = new ReflectionClass('OpenApi2KrakenD\KrakenD');
        $actual = $reflectionKrakenD->getProperty('data')->getValue($krakenD);

        $this->assertEquals($host, $krakenD->host);
        $this->assertEquals($expectedConfig, $actual);
    }

    public static function dataForTestConstruct(): iterable
    {
        $host = 'https://krakend.test';

        yield 'default config' => [
            'host' => $host,
            'config' => [],
            'expectedConfig' => KrakenD::DEFAULT_CONFIG,
        ];

        $config = [
            '$schema' => 'https://www.krakend.io/schema/v2.1/krakend.json',
            'version' => 2,
            'disable_rest' => false,
        ];
        yield 'custom config' => [
            'host' => $host,
            'config' => $config,
            'expectedConfig' => $config,
        ];
    }
}
