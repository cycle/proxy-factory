<?php

declare(strict_types=1);

namespace Cycle\ORM\Promise\Tests;

use Cycle\ORM\Promise\Materizalizer\ModificationInspector;
use Cycle\ORM\Promise\Tests\Fixtures\ModificationInspector\Inspected;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;

class ModificationInspectorTest extends TestCase
{

    /**
     * @throws \ReflectionException
     */
    public function testDate(): void
    {
        $lastDate = null;
        $files = glob($this->filesDirectory() . DIRECTORY_SEPARATOR . '*');

        foreach ($files as $file) {
            if (is_file($file)) {
                $date = new \DateTime('@' . filemtime($file));
                if ($date > $lastDate) {
                    $lastDate = $date;
                }
            }
        }

        $this->assertEquals($lastDate, $this->inspector()->getLastModifiedDate(new \ReflectionClass(Inspected::class)));
    }

    private function filesDirectory(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'ModificationInspector';
    }

    /**
     * @return \Cycle\ORM\Promise\Materizalizer\ModificationInspector
     */
    private function inspector(): ModificationInspector
    {
        $container = new Container();

        return $container->get(ModificationInspector::class);
    }
}
