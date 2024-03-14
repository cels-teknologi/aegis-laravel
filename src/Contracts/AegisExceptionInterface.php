<?php

namespace Cels\Aegis\Contracts;

interface AegisExceptionInterface
{
    public function getExtraMetadata(): array;

    public function getOverwriteData(): array;

    public function getTags(): array;
}
