<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\PromiseInterface;
use Cycle\ORM\Promise\Utils;

class DeclarationTest extends BaseProxyPrinterTest
{
    /**
     * @throws \ReflectionException
     */
    public function testDeclaration(): void
    {
        $classname = Fixtures\Entity::class;
        $as = 'EntityProxy' . __LINE__;

        $reflection = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);
        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $this->assertStringNotContainsString('abstract', $output);
        $this->assertStringContainsString(sprintf(
            'class %s extends %s implements %s',
            $as,
            Utils::shortName($classname),
            Utils::shortName(PromiseInterface::class)
        ), $output);

        $proxy = $this->makeProxyObject($classname, $class->getFullName());

        $this->assertInstanceOf($class->getFullName(), $proxy);
        $this->assertInstanceOf($classname, $proxy);
        $this->assertInstanceOf(PromiseInterface::class, $proxy);
    }

    /**
     * @dataProvider traitsDataProvider
     *
     * @param string $classname
     * @param string $as
     *
     * @throws \ReflectionException
     */
    public function testTraits(string $classname, string $as): void
    {
        $reflection = new \ReflectionClass($classname);
        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);
        $this->assertStringNotContainsString(' use ', $this->make($reflection, $class, $parent));
    }

    public function traitsDataProvider(): array
    {
        return [
            [Fixtures\EntityWithoutTrait::class, 'EntityProxy' . __LINE__],
            [Fixtures\EntityWithTrait::class, 'EntityProxy' . __LINE__],
        ];
    }

    /**
     * @param string $className
     * @param string $proxyFullName
     *
     * @return object
     */
    private function makeProxyObject(string $className, string $proxyFullName)
    {
        return $this->container->make($proxyFullName, ['role' => $className, 'scope' => []]);
    }
}