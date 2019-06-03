<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

class Stubs
{
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