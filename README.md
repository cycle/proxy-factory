# Proxy Factory
[![Latest Stable Version](https://poser.pugx.org/cycle/proxy-factory/version)](https://packagist.org/packages/cycle/proxy-factory)
[![Build Status](https://travis-ci.org/cycle/proxy-factory.svg?branch=master)](https://travis-ci.org/cycle/proxy-factory)
[![Codecov](https://codecov.io/gh/cycle/proxy-factory/branch/master/graph/badge.svg)](https://codecov.io/gh/cycle/proxy-factory/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/cycle/proxy-factory/badges/quality-score.png)](https://scrutinizer-ci.com/g/cycle/proxy-factory/)


## Usage
```php

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Factory;
use Cycle\ORM\Promise\ReferenceInterface;

class Example
{
    /** @var Factory */
    private $factory;

    /** @var ORMInterface */
    private $orm;

    public function __construct(Factory $factory, ORMInterface $orm)
    {
        $this->factory = $factory;
        $this->orm = $orm;
    }

    /**
     * @param string $role
     * @param array  $scope
     *
     * @return ReferenceInterface|null
     * @throws \Cycle\ORM\Promise\ProxyFactoryException
     */
    public function promise(string $role, array $scope): ?ReferenceInterface
    {
        return $this->factory->promise($this->orm, $role, $scope);
    }
}
```