<?php

namespace Cels\Aegis\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Config;

trait GuzzleHttpClientHelpers
{
    /** @var ClientInterface|null The GuzzleHTTP Client instance. */
    protected $client;

    /**
     * Get the GuzzleHTTP Client instance currently in use, or create one if it doesn't exist.
     *
     * @return ClientInterface
     */
    public function getGuzzleHttpClient(): ClientInterface
    {
        if (!isset($this->client)) {
            $this->client = new Client([
                'base_uri' => Config::get('aegis.http.base_uri', 'https://aegis.cels.co.id'),
                'timeout' => Config::get('aegis.http.timeout', 10),
            ]);
        }

        return $this->client;
    }

    /**
     * Set the GuzzleHTTP Client instance to be used.
     *
     * @param ClientInterface $client
     */
    public function setGuzzleHttpClient(ClientInterface $client)
    {
        $this->client = $client;
    }
}
