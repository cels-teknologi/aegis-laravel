<?php

namespace Cels\Aegis;

use Cels\Aegis\Contracts\ClientWrapperInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
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
     * @param  int  $psr3Level
     * @param  \Throwable  $exception  The caught throwable exception.
     * @param  array  $context  Log context.
     * @param  array  $extra  Additional metadata to store.
     * @return  mixed|null
     */
    public function handle(
        int $psr3Level,
        $message = '',
        $context = [],
        $extra = [],
    ) {
        if (!$this->reportableEnvironment()) {
            return false;
        }

        $isThrowable = (\array_key_exists('exception', $context)
            && isset($context['exception'])
            && $context['exception'] instanceof \Throwable
        );
        $onlyThrowables = (bool) Config::get('aegis.only_throwables', false);
        $record = new Record(
            $message,
            $context,
            $extra,
            $isThrowable ? $context['exception'] : null,
            $psr3Level,
        );

        $r = 0;
        if ($r = (int) Cache::get($record->generateKey())) {
            if ($this->isRateLimited()) {
                return false;
            }
        }

        Cache::set($record->generateKey(), $r + 1);

        if ($onlyThrowables && !$isThrowable) {
            return;
        }
        return $this->client->report($record, [
            'rate' => (int) (((float) Config::get('aegis.rate')) * 100),
            'occurence' => $r + 1,
        ]);
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
