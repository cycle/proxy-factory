<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

class Stubs
{
    public function getContent(): string
    {
        return '<?php
declare(strict_types=1);

namespace StubNamespace;

class ProxyStub
{
}';
    }
}