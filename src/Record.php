<?php

namespace Cels\Aegis;

use Cels\Aegis\Contracts\Aegisable;
use Cels\Aegis\Contracts\AegisExceptionInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Throwable;

class Record implements Arrayable
{
    /** @var Throwable The throwable exception. */
    protected $exception;

    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
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
     * Get the exception instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $user = Request::user();

        $data = [
            'environment' => App::environment(),

            'method' => Request::method(),
            'fullUrl' => Request::fullUrl(),

            'classname' => \get_class($this->exception),
            'message' => $this->exception->getMessage(),
            'trace' => $this->exception->getTraceAsString(),
            'line' => $this->exception->getLine(),
            'code' => $this->exception->getCode(),
            'file_name' => $this->exception->getFile(),
            'file_hash' => \hash_file('md5', $this->exception->getFile()),

            'release' => config('aegis.release', null),
            'dist' => config('aegis.dist', null),

            'vars_ua_string' => Request::server('HTTP_USER_AGENT'),
            'vars_server_addr' => Request::server('SERVER_ADDR'),
            'vars_remote_addr' => Request::server('REMOTE_ADDR'),
        ];

        if ($user && config('aegis.user.collect', true)) {
            if ($user instanceof Aegisable) {
                $data['user'] = $user->toAegis();
            }
            elseif ($user instanceof Model || \method_exists($user, 'getKey')) {
                $data['user'] = $user->getKey();
            }
            elseif (config('aegis.user.force', true)) {
                if ($user instanceof Jsonable) {
                    $data['user'] = $user->toJson();
                }
                elseif ($user instanceof Arrayable) {
                    $data['user'] = \json_encode($user->toArray());
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
            $data['vars_hostname'] = $hostname;
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
