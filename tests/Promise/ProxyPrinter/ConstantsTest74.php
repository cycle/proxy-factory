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

class ConstantsTest74 extends BaseProxyPrinterTest
{
    /**
     * @throws \Cycle\ORM\Promise\Exception\ProxyFactoryException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function testConstValues(): void
    {
        $classname = Fixtures\EntityWithProperties74::class;
        $as = self::NS . __CLASS__ . __LINE__;
        $reflection = new \ReflectionClass($classname);

        $parent = Declarations::createParentFromReflection($reflection);
        $class = Declarations::createClassFromName($as, $parent);

        $output = $this->make($reflection, $class, $parent);
        $output = ltrim($output, '<?php');
        print_r(PHP_EOL . '74 version' . PHP_EOL);
        $this->assertStringContainsString(
            'PUBLIC_PROPERTIES = [\'publicProperty\', \'publicPropertyWithDefaults\'];',
            $output
        );

        $this->assertStringContainsString(
            'UNSET_PROPERTIES = [\'publicProperty\', \'publicPropertyWithDefaults\'];',
            $output
        );
    }
}
