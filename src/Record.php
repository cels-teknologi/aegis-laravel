<?php

namespace Cels\Aegis;

use Cels\Aegis\Contracts\Aegisable;
use Cels\Aegis\Contracts\AegisExceptionInterface;
use Cels\Aegis\Helpers\File;
use Cels\Aegis\Helpers\Git;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Monolog\Logger;

class Record implements Arrayable
{
    protected Git $git;
    protected $traces;

    public function __construct(
        protected string $message,
        protected array $context,
        protected array $extra,
        protected ?\Throwable $throwable = null,
        protected int $psr3Level = 100,
    ) {
        $this->git = new Git;
        $this->traces = $this->buildTraces();
    }

    /**
     * Get the throwable identifier to determine uniqueness.
     *
     * @return string
     */
    public function generateKey(): string
    {
        $jsonEncoded = \json_encode($this->traces[0]);
        return "aegis___{$jsonEncoded}";
    }

    /**
     * Get the throwable instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $release = Config::get('aegis.release');
        if (empty($release)) {
            $release = $this->git->hash();
        }

        $data = [
            'level' => $this->psr3Level,
            'environment' => App::environment(),

            'method' => Request::method(),
            'fullUrl' => Request::fullUrl(),

            'release' => $release,
            'dist' => Config::get('aegis.dist'),

            'message' => $this->message,
            'throwable' => !! $this->throwable,
            'traces' => $this->traces,
            'context' => $this->context,
            'extra' => $this->extra,

            'variables' => [
                'version' => [
                    'laravel' => App::version(),
                    'php' => PHP_VERSION,
                ],
                'ua_string' => Request::server('HTTP_USER_AGENT'),
                'host_ip' => Request::server('SERVER_ADDR'),
                'client_ip' => Request::server('REMOTE_ADDR'),
            ],
        ];

        $collects = [];
        if (Config::get('aegis.collect.env', false)) {
            $collects['env'] = [...$_ENV];
        }
        if (($user = Request::user()) && Config::get('aegis.collect.user', true)) {
            try {
                if ($user instanceof Aegisable) {
                    $collects['user'] = $user->toAegis();
                }
                else if (\is_array($user)) {
                    $collects['user'] = $user;
                }
                else if ($user instanceof Arrayable || \method_exists($user, 'toArray')) {
                    $collects['user'] = $user->toArray();
                }
                else if (\method_exists($user, 'getKey')) {
                    $collects['user'] = ['id' => $user->getKey()];
                }
                else if ($user instanceof Jsonable) {
                    $collects['user'] = \json_decode($user->toJson(), true);
                }
                else {
                    $collects['user'] = $user . '';
                }
            }
            finally { }
        }
        $data['collects'] = $collects;

        if ($hostname = \gethostname()) {
            $data['variables']['host_name'] = $hostname;
        }

        if ($this->throwable instanceof AegisExceptionInterface) {
            $merge = $this->throwable->getOverwriteData();

            $merge['extra'] = $this->throwable->getExtraMetadata();
            $merge['tags'] = $this->throwable->getTags();

            $data = [
                ...$data,
                ...\array_filter($merge),
            ];
        }

        return \array_filter($data);
    }

    protected function buildTraces(): array
    {
        $ignores = ['vendor', ...Config::get('aegis.ignore', [])];
        $backtrace = \debug_backtrace(0);
        $raw = [
            ...($this->throwable ? [
                'file' => $this->throwable->getFile(),
                'line' => $this->throwable->getLine(),
                'class' => \get_class($this->throwable),
                'type' => '{thrown}'
            ] : [
                'file' => '{unknown}',
                'type' => '{unknown}',
            ]),
            ...$this->throwable ? $this->throwable->getTrace() : $backtrace,
        ];
        $cause = false;
        $traces = [];

        foreach ($raw as $i => $trace) {
            if ($i <= 0) {
                continue;
            }
            $paths = \array_filter(\preg_split('/[\\\\\/]/', File::relativePathOf(
                \array_key_exists('file', $raw[$i - 1]) ? $raw[$i - 1]['file'] : '{unknown}',
            )));
            $overwrite = ['file' => \implode('/', $paths)];
            if (!\in_array($paths[0], $ignores)) {
                $cause = true;
                $overwrite['cause'] = 1;
            }
            $traces[] = \array_filter(\array_merge([
                'type' => null,
                'args' => null,
                'class' => '{closure}',
                'preview' => \array_key_exists('file', $raw[$i - 1]) && ((int) $raw[$i - 1]['line']) > 0
                    ? (new File($raw[$i - 1]['file']))
                        ->preview($raw[$i - 1]['line'], (int) Config::get('aegis.lines', 15))
                    : [],
            ], $trace, $overwrite));
        }

        if (!$cause) {
            $traces[0]['cause'] = true;
        }

        return $traces;
    }
}
