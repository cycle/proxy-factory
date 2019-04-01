<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Naming;

use Cycle\ORM\Promise\NamingInterface;

class DatetimeNaming implements NamingInterface
{
    public function name(string $class): string
    {
        return "{$class}Proxy_{$this->timestamp()}";
    }

    private function timestamp(): string
    {
        $datetime = new \DateTime();

        return $datetime->format('Ymd_His_u');
    }
}