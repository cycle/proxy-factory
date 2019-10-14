<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

final class Stubs
{
    /**
     * @return string
     */
    public function getContent(): string
    {
        $lines = [
            '<?php',
            'declare(strict_types=1);',
            'namespace StubNamespace;',
            'class ProxyStub {}'
        ];

        return join("\n", $lines);
    }
}
