<?php

namespace Cels\Aegis\Helpers;

use Illuminate\Support\Facades\App;
use Symfony\Component\Process\Process;

class Git
{
    public function hash(): ?string
    {
        return $this->command("git log --pretty=format:'%H' -n 1") ?: null;
    }

    protected function command($command)
    {
        $process = Process::fromShellCommandline($command, App::basePath())->setTimeout(1);

        $process->run();

        return trim($process->getOutput());
    }
}