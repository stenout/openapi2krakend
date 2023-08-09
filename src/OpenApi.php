<?php

namespace OpenApi2KrakenD;

class OpenApi
{
    private array $data;

    public function __construct(string $json)
    {
        $this->data = json_decode($json, true);
    }

    public static function fromJson(string $json): self
    {
        return new self($json);
    }

    public static function fromFile(string $file): self
    {
        return new self(file_get_contents($file));
    }

    public function paths(): array
    {
        return $this->data['paths'] ?? [];
    }

    public function components(): array
    {
        return $this->data['components'] ?? [];
    }

    public function toKrakenD(string $host, array $config = []): KrakenD
    {
        $krakenD = new KrakenD($host, $config);
        return (new Converter())->convert($this, $krakenD);
    }
}
