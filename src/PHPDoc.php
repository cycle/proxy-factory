<?php

/**
 * Spiral Framework.
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
        $lines = [
            '/**',
            ' * {@inheritdoc}',
            ' */'
        ];

        return self::makeComment(join("\n", $lines));
    }

    /**
     * @param string $type
     * @return Doc
     */
    public static function writeProperty(string $type): Doc
    {
        $lines = [
            '/**',
            ' * @internal',
            " * @var $type",
            ' */'
        ];

        return self::makeComment(join("\n", $lines));
    }

    /**
     * @param string $comment
     * @return Doc
     */
    private static function makeComment(string $comment): Doc
    {
        return new Doc($comment);
    }
}
