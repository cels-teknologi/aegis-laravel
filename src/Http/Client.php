<?php

namespace Cels\Aegis\Http;

use Cels\Aegis\Contracts\ClientWrapperInterface;
use Cels\Aegis\Record;
use Cels\Aegis\Traits\GuzzleHttpClientHelpers;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class Client implements ClientWrapperInterface
{
    use GuzzleHttpClientHelpers;

    /** @var string The Aegis project slug. */
    protected $project;

    /** @var string The Aegis project authentication token. */
    protected $token;

    /**
     * @param string $project The project slug.
     * @param string $token The project authentication token.
     */
    public function __construct(
        string $project,
        string $token,
        ClientInterface $client = null,
    ) {
        $this->client = $client;
        $this->project = $project;
        $this->token = $token;
        $this->auth = \base64_encode("{$project}:{$token}");
    }

    /**
     * Handle reporting of an exception.
     *
     * @param Record $record The exception data being handled.
     */
    public function report(Record $record)
    {
        try {
            return $this->getGuzzleHttpClient()
                ->request('POST', Config::get('aegis.http.endpoint'), [
                    'headers' => [
                        'Authorization' => "Basic {$this->auth}",
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'Aegis-PHP-Client/1.0',
                    ],
                    'json' => $record->toArray(),
                    'verify' => Config::get('aegis.http.verify_ssl', true),
                ]);
        }
        catch (\Exception $e) {
            Log::emergency($e);
        }
    }
}
