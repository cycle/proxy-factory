<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Naming;

use Cycle\ORM\Promise\NamingInterface;

class DatetimeNaming implements NamingInterface
{
    public function name(string $class): string
    {
        $datetime = new \DateTime();

        return $class . 'Proxy_' . $datetime->format('Ymd_His_u');
    }
}