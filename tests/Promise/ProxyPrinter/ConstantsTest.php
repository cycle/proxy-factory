<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests\ProxyPrinter;

use Cycle\ORM\Promise\Declaration\Declarations;
use Cycle\ORM\Promise\Printer;

use function Cycle\ORM\Promise\php74;

class ConstantsTest extends BaseProxyPrinterTest
{
    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testConstValues(): void
    {
        $classname = Fixtures\EntityWithoutConstConflicts::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringContainsString(
            'PUBLIC_PROPERTIES = [\'publicProperty\', \'publicPropertyWithDefaults\'];',
            $output
        );

        if (php74()) {
            $this->assertStringContainsString(
                'UNSET_PROPERTIES = [\'publicPropertyWithDefaults\'];',
                $output
            );
        } else {
            $this->assertStringContainsString(
                'UNSET_PROPERTIES = [\'publicProperty\', \'publicPropertyWithDefaults\'];',
                $output
            );
        }
    }

    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testWithoutConflicts(): void
    {
        $classname = Fixtures\EntityWithoutConstConflicts::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('PUBLIC_CONST ', $output);
        $this->assertStringNotContainsString('PROTECTED_CONST ', $output);
        $this->assertStringNotContainsString('PRIVATE_CONST ', $output);
        $this->assertStringContainsString(Printer::PUBLIC_PROPERTIES_CONST . ' ', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($as);
        $this->assertArrayHasKey('PUBLIC_CONST', $reflection->getConstants());
        $this->assertArrayHasKey('PROTECTED_CONST', $reflection->getConstants());
        $this->assertArrayHasKey(Printer::PUBLIC_PROPERTIES_CONST, $reflection->getConstants());
    }

    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testWithConflicts(): void
    {
        $classname = Fixtures\EntityWithConstConflicts::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');

        $this->assertStringNotContainsString('PUBLIC_CONST ', $output);
        $this->assertStringNotContainsString('PROTECTED_CONST ', $output);
        $this->assertStringNotContainsString('PRIVATE_CONST ', $output);
        $this->assertStringNotContainsString(Printer::PUBLIC_PROPERTIES_CONST . ' ', $output);
        $this->assertStringContainsString(Printer::PUBLIC_PROPERTIES_CONST . '_2 ', $output);

        $this->assertFalse(class_exists($class->getFullName()));

        eval($output);

        $reflection = new \ReflectionClass($as);
        $this->assertArrayHasKey('PUBLIC_CONST', $reflection->getConstants());
        $this->assertArrayHasKey('PROTECTED_CONST', $reflection->getConstants());
        $this->assertArrayHasKey(Printer::PUBLIC_PROPERTIES_CONST, $reflection->getConstants());
        $this->assertArrayHasKey(Printer::PUBLIC_PROPERTIES_CONST . '_2', $reflection->getConstants());
    }
}
