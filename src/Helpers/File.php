<?php

namespace Cels\Aegis\Helpers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class File
{
    public function __construct(protected string $filename)
    {
        //
        if (!\file_exists($filename)) {
            throw new FileNotFoundException("File '{$filename}' does not exist.");
        }
    }

    /**
     * Generate a file preview
     *
     * @return array
     */
    public function preview($line, $lines = 15): array
    {
        $preview = [];
        $content = \file($this->filename);
        $max = \count($content);
        if ($line > 15 && $max - $line < 15) {
            $line = \max(1, $max - 15);
        }

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

    /**
     * Get file relative path
     */
    public function relativePath(): string
    {
        return self::relativePathOf($this->filename);
    }

    /**
     * Transform path to relative path
     * 
     * @static
     */
    public static function relativePathOf($path): string
    {
        return Str::replaceFirst(
            App::basePath(),
            '',
            $path,
        );
    }
}