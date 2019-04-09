<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\ORM\Promise\Materizalizer\EvalMaterializer;
use Cycle\ORM\Promise\Materizalizer\FileMaterializer;
use Cycle\ORM\Promise\Tests\Fixtures\Entity;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class MaterializerTest extends TestCase
{
    private const NS = 'Cycle\ORM\Promise\Tests\Promises';

    public function setUp()
    {
        $files = glob($this->filesDirectory() . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    /**
     * @dataProvider evalDataProvider
     *
     * @param string $className
     * @param string $code
     *
     * @throws \ReflectionException
     */
    public function testEvalMaterialize(string $className, string $code): void
    {
        $this->assertFalse(class_exists($className));

        $materializer = new EvalMaterializer();
        $materializer->materialize($code, $className, new \ReflectionClass(Entity::class));

        $this->assertTrue(class_exists($className));
    }

    public function evalDataProvider(): array
    {
        return [
            ['EvalTestOne', '<?php class EvalTestOne {}'],
            ['EvalTestTwo', '<? class EvalTestTwo {}'],
            ['EvalTestThree', 'class EvalTestThree {}'],
        ];
    }

    /**
     * @dataProvider fileDataProvider
     *
     * @param string $className
     * @param string $code
     *
     * @throws \ReflectionException
     */
    public function testFileMaterializer(string $className, string $code): void
    {
        $fullClassName = '\\' . self::NS . '\\' . $className;
        $this->assertFalse(class_exists($fullClassName));

        $container = new Container();
        /** @var FileMaterializer $materializer */
        $materializer = $container->make(FileMaterializer::class, ['directory' => $this->filesDirectory()]);
        $materializer->materialize($code, $className, new \ReflectionClass(Entity::class));

        $this->assertTrue(class_exists($fullClassName));
    }

    public function fileDataProvider(): array
    {
        $ns = self::NS;

        $output = [];
        $classes = ['FileTestOne', 'FileTestTwo', 'FileTestThree'];
        $codePrefixes = ['<?php', '<?', ''];
        foreach ($classes as $i => $class) {
            $output[] = [
                $class,
                "{$codePrefixes[$i]} namespace $ns; class $class {}"
            ];
        }

        return $output;
    }

    private function filesDirectory(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'promises';
    }
}