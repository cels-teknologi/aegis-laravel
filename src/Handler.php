<?php

namespace Cels\Aegis;

use Monolog\Handler\AbstractProcessingHandler;
use Throwable;

class Handler extends AbstractProcessingHandler
{
    /** @var Aegis $aegis The aegis instance. */
    protected $aegis;

    /**
     * @param LaraBug $laraBug
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(Aegis $aegis)
    {
        $this->aegis = $aegis;
    }

    /**
     * Forward Monolog FormattedRecord write process to Aegis.
     *
     * @param array $record The Monolog FormattedRecord.
     * @see https://github.com/Seldaek/monolog/blob/2.x/src/Monolog/Handler/AbstractProcessingHandler.php
     */
    protected function write(array $record): void
    {
        if (\array_key_exists('exception', $record['context'])
            && isset($record['context']['exception'])
            && $record['context']['exception'] instanceof Throwable
        ) {
            $this->aegis->handle($record['context']['exception']);

            return;
        }
    }
}
