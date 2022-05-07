<?php

namespace Cels\Aegis\Traits;

trait AegisExceptionHelpers
{
    public function getExtraMetadata(): array
    {
        return \property_exists($this, 'extra') && \is_array($this->extra)
            ? $this->extra
            : [];
    }

    public function getOverwriteData(): array
    {
        return \property_exists($this, 'overwrite') && \is_array($this->overwrite)
            ? $this->overwrite
            : [];
    }

    public function getTags(): array
    {
        return \property_exists($this, 'tags') && \is_array($this->tags)
            ? $this->tags
            : [];
    }
}
