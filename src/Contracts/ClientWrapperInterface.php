<?php

namespace Cels\Aegis\Contracts;

use Cels\Aegis\Record;
use GuzzleHttp\ClientInterface;

interface ClientWrapperInterface
{
    /**
     * Handle reporting of an exception.
     *
     * @param Record $record The exception data being handled.
     */
    public function report(Record $record);

    /**
     * Get the GuzzleHTTP Client instance currently in use, or create one if it doesn't exist.
     *
     * @return ClientInterface
     */
    public function getGuzzleHttpClient(): ClientInterface;

    /**
     * Set the GuzzleHTTP Client instance to be used.
     *
     * @return void
     */
    public function setGuzzleHttpClient(ClientInterface $client);
}
