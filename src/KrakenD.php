<?php

namespace OpenApi2KrakenD;

class KrakenD
{
    const DEFAULT_CONFIG = [
        '$schema' => 'https://www.krakend.io/schema/v2.4/krakend.json',
        'version' => 3,
    ];

    private array $data;

    /**
     * @param array{
     *     "$schema": string,
     *     "version": int
     * } $config
     */
    public function __construct(array $config = []) {
        $this->data = array_merge(self::DEFAULT_CONFIG, $config);
    }

    public function addEndpoint(array $endpoint): void
    {
        $this->data['endpoints'][] = $endpoint;
    }

    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Возвращает JSON в читабельном виде с отступом 2 пробела
     */
    public function toPrettyJson(): string
    {
        return str_replace(
            '    ',
            '  ',
            json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    public function toFile($file): void
    {
        file_put_contents($file, $this->toPrettyJson());
    }
}
