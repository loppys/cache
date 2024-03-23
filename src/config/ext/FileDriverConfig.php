<?php

namespace Vengine\Cache\config\ext;

use Vengine\Cache\config\DriverConfig;

class FileDriverConfig extends DriverConfig
{
    protected string $folder = '';

    public function getFolder(): string
    {
        return $this->folder;
    }
}
