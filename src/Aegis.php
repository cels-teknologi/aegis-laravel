<?php

namespace Cels\Aegis;

use Cels\Aegis\Contracts\ClientWrapperInterface;
use Cels\Aegis\Http\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;

class Aegis
{
    /** @var ClientWrapperInterface The GuzzleHTTP Client wrapper instance. */
    protected $client;

    /**
     * @param ClientWrapperInterface $client The GuzzleHTTP Client wrapper instance.
     */
    public function __construct(ClientWrapperInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Handle any caught throwables.
     *
     * @param \Throwable $exception The caught throwable exception.
     * @param array $tags Additional tags.
     * @param array $extras Additional metadata to store.
     * @return mixed|null
     */
    public function handle(\Throwable $exception)
    {
        if (!$this->reportableEnvironment()) {
            return false;
        }

        if ($this->isRateLimited()) {
            return false;
        }

        return $this->client->report(new Record($exception));
    }

    /**
     * Whether the current environment allows reporting.
     *
     * @return bool
     */
    public function isRateLimited(): bool
    {
        $rate = (int) (((float) Config::get('aegis.rate')) * 100);
        return \random_int(0, 99) >= $rate;
    }

    /**
     * Whether the current environment allows reporting.
     *
     * @return bool
     */
    public function reportableEnvironment(): bool
    {
        return \in_array(
            App::environment(),
            Config::get('aegis.environments'),
        );
    }
}
