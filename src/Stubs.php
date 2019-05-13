<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

class Stubs
{
    private const FILENAME = 'stubs' . DIRECTORY_SEPARATOR . 'ProxyStub.php';

    public function getContent(): string
    {
        return file_get_contents($this->getStubFilename());
    }

    private function getStubFilename(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::FILENAME;
    }
}