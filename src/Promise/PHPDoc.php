<?php

/**
 * Spiral Framework. Cycle ProxyFactory
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */

declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Comment\Doc;

final class PHPDoc
{
    /**
     * @return Doc
     */
    public static function writeInheritdoc(): Doc
    {
        return self::makeComment([
            '/**',
            ' * {@inheritdoc}',
            ' */'
        ]);
    }

    /**
     * @param string $type
     * @return Doc
     */
    public static function writeProperty(string $type): Doc
    {
        return self::makeComment([
            '/**',
            ' * @internal',
            " * @var $type",
            ' */'
        ]);
    }

    /**
     * @param array|string $comment
     * @return Doc
     */
    private static function makeComment($comment): Doc
    {
        return new Doc(is_array($comment) ? implode("\n", $comment) : $comment);
    }
}
