<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\Resolver;

class UseStmtsTest extends BaseProxyPrinterTest
{
    private const USE_STMTS = [
        Resolver::class,
        PromiseInterface::class,
        ProxyFactoryException::class,
        ORMInterface::class,
    ];

    /**
     * @throws \ReflectionException
     */
    public function testSameNamespace(): void
    {
        $classname = Fixtures\Entity::class;
        $as = 'EntityProxy' . str_replace('\\', '', __CLASS__) . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output), $this->fetchExternalDependencies($class->getFullName(), self::USE_STMTS));
    }

    /**
     * @throws \ReflectionException
     */
    public function testDistinctNamespace(): void
    {
        $classname = Fixtures\Entity::class;
        $as = "\EntityProxy" . str_replace('\\', '', __CLASS__) . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertSame($this->fetchUseStatements($output),
            $this->fetchExternalDependencies($class->getFullName(), array_merge(self::USE_STMTS, [$classname])));
    }

    private function fetchUseStatements(string $code): array
    {
        $uses = [];
        foreach (explode("\n", $code) as $line) {
            if (mb_stripos($line, 'use') !== 0) {
                continue;
            }

            $uses[] = trim(mb_substr($line, 4), " ;\r\n");
        }

        sort($uses);

        return $uses;
    }

    /**
     * @param string $class
     * @param array  $types
     *
     * @return array
     * @throws \ReflectionException
     */
    private function fetchExternalDependencies(string $class, array $types = []): array
    {
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            if (!$parameter->hasType() || $parameter->getType()->isBuiltin()) {
                continue;
            }

            $types[] = $parameter->getType()->getName();
        }

        sort($types);

        return $types;
    }
}