<?php

namespace OpenApi2KrakenD;

class KrakenD
{
    const DEFAULT_CONFIG = [
        '$schema' => 'https://www.krakend.io/schema/v2.4/krakend.json',
        'version' => 3,
        'disable_rest' => true,
    ];

    public string $host;
    private array $data;

    /**
     * @param string $host
     * @param array{
     *     "$schema": string,
     *     "version": int,
     *     "disable_rest": bool,
     * } $config
     */
    public function __construct(string $host, array $config = []) {
        $this->host = $host;
        $this->data = array_merge(self::DEFAULT_CONFIG, $config);
    }

    public function addEndpoints(array $endpoints): void
    {
        $this->data['endpoints'] = $endpoints;
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
