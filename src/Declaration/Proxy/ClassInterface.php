<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Declaration\Proxy;

interface ClassInterface
{
    public function getShortName(): string;

    public function getNamespaceName(): ?string;

    public function getFullName(): string;
}