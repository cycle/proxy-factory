<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Materizalizer\ClassLocator;

use Spiral\Tokenizer\TokenizerInterface;
use Symfony\Component\Finder\Finder;

class LocatorFactory
{
    /** @var TokenizerInterface */
    private $tokenizer;

    public function __construct(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function create(string $directory): Locator
    {
        $finder = new Finder();
        $finder->in($directory);

        return new Locator($this->tokenizer, $finder);
    }
}