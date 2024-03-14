<?php

namespace Cels\Aegis;

use Monolog\Handler\AbstractProcessingHandler;

class MonologHandler extends AbstractProcessingHandler
{
    /**
     * @param  Aegis  $aegis
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(protected Aegis $aegis)
    {
        //
    }

    /**
     * Forward Monolog FormattedRecord write process to Aegis.
     *
     * @param  array|LogRecord  $record The Monolog FormattedRecord.
     * @see https://github.com/Seldaek/monolog/blob/main/src/Monolog/Handler/AbstractProcessingHandler.php
     */
    protected function write($record): void
    {
        if (\array_key_exists('exception', $record['context'])
            && isset($record['context']['exception'])
            && $record['context']['exception'] instanceof \Throwable
        ) {
            $this->aegis->handle($record['context']['exception']);

            return;
        }
    }
}
