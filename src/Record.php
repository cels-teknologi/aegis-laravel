<?php

namespace Cels\Aegis;

use Cels\Aegis\Contracts\Aegisable;
use Cels\Aegis\Contracts\AegisExceptionInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Throwable;

class Record implements Arrayable
{
    /** @var Throwable The throwable exception. */
    protected $exception;

    /** @var array The probable root cause of exception. */
    protected $cause;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
        $this->cause = $this->determineCause();
    }

    public function determineCause(): array
    {
        $cause = [
            'file' => $this->exception->getFile(),
            'line' => $this->exception->getLine(),
        ];
        $ignores = [];
        $finished = false;
        $traces = \array_merge([$cause, ], $this->exception->getTrace());

        foreach (config('aegis.ignore') as $ignore) {
            $ignores[] = base_path($ignore);
        }

        foreach ($traces as $trace) {
            $path = $trace['file'];
            foreach ($ignores as $ignore) {
                if (Str::startsWith($path, $ignore)) {
                    continue;
                }

                $cause = [
                    'file' => $path,
                    'line' => $trace['line'],
                ];
                $finished = true;
                break;
            }

            if ($finished) {
                break;
            }
        }

        return $cause;
    }

    /**
     * Get the exception identifier to determine uniqueness.
     *
     * @return array
     */
    public function generateCodePreview($file, $line): array
    {
        $preview = [];
        $lines = \abs((int) config('aegis.lines', 7));
        $content = \file($file);
        $max = \count($content);

        for ($i = -1 * $lines; $i <= $lines; $i++) {
            $currentLine = $line + $i;
            $idx = $currentLine - 1;

            if ($idx < 0 || $currentLine > count($content)) {
                continue;
            }

            if (empty(\trim($content[$idx]))) {
                continue;
            }

            $preview[] = [$currentLine, \trim($content[$idx], "\n\r"), ];
        }

        return $preview;
    }

    public function formatTraces(): array
    {
        $traces = [];

        foreach ($this->exception->getTrace() as $trace) {
            $traces[] = \array_filter(\array_merge($trace, [
                'type' => null,
                'args' => null,
                'file' => Str::replaceFirst(
                    base_path(),
                    '',
                    $trace['file'],
                ),
            ]));
        }

        return $traces;
    }

    public function formatException(): array
    {
        $preview = $this->generateCodePreview(
            $this->exception->getFile(),
            $this->exception->getLine(),
        );

        $formatted = [
            'type' => \get_class($this->exception),
            'message' => $this->exception->getMessage(),
            'traces' => $this->formatTraces(),
            'code' => $this->exception->getCode(),
            'file' => [
                'name' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'hash' => \hash_file('md5', $this->exception->getFile()),
                'preview' => $preview,
            ],
        ];

        if ($this->cause['file'] !== $this->exception->getFile()) {
            $causePreview = $this->generateCodePreview(
                $this->cause['file'],
                $this->cause['line'],
            );

            $formatted['cause'] = [
                'name' => $this->cause['file'],
                'line' => $this->cause['line'],
                'hash' => \hash_file('md5', $this->cause['file']),
                'preview' => $causePreview,
            ];
        }

        return $formatted;
    }

    /**
     * Get the exception identifier to determine uniqueness.
     *
     * @return array
     */
    public function generateKey(): string
    {
        $data = $this->toArray();
        return "aegis___{$data['classname']}_{$data['message']}_{$data['line']}_{$data['file_name']}";
    }

    /**
     * Guess the release of application using Git's commit SHA
     * 
     * @return string|null The commit string.
     */
    public function guessRelease()
    {
        $path = base_path('.git/');

        if (!file_exists($path)) {
            return null;
        }
    
        $head = trim(substr(file_get_contents($path . 'HEAD'), 4));
    
        $hash = trim(file_get_contents(sprintf($path . $head)));
    
        return $hash;
    }

    /**
     * Get the exception instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $user = Request::user();

        $release = config('aegis.release');
        if (empty($release)) {
            $release = $this->guessRelease();
        }

        $data = [
            'environment' => App::environment(),

            'method' => Request::method(),
            'fullUrl' => Request::fullUrl(),

            'release' => $release,
            'dist' => config('aegis.dist'),

            'exception' => $this->formatException(),

            'variables' => [
                'ua_string' => Request::server('HTTP_USER_AGENT'),
                'host_ip' => Request::server('SERVER_ADDR'),
                'client_ip' => Request::server('REMOTE_ADDR'),
            ],
        ];

        if ($user && config('aegis.user.collect', true)) {
            if ($user instanceof Aegisable) {
                $data['user'] = $user->toAegis();
            }
            elseif (\is_array($user)) {
                $data['user'] = $user;
            }
            elseif ($user instanceof Arrayable || \method_exists($user, 'toArray')) {
                $data['user'] = $user->toArray();
            }
            elseif (\method_exists($user, 'getKey')) {
                $data['user'] = ['id' => $user->getKey(), ];
            }
            elseif (config('aegis.user.force', true)) {
                if ($user instanceof Jsonable) {
                    $data['user'] = \json_decode($user->toJson(), true);
                }
                else {
                    try {
                        $stringified = $user . '';
                        $data['user'] = $stringified;
                    }
                    finally {
                    }
                }
            }
        }

        if ($hostname = \gethostname()) {
            $data['variables']['host_name'] = $hostname;
        }

        if ($this->exception instanceof AegisExceptionInterface) {
            $merge = $this->exception->getOverwriteData();

            $merge['extra'] = $this->exception->getExtraMetadata();
            $merge['tags'] = $this->exception->getTags();

            $data = \array_merge($data, \array_filter($merge));
        }

        return $data;
    }
}
