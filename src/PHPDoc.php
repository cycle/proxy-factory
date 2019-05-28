<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Comment\Doc;

final class PHPDoc
{
    public static function writeInheritdoc(): Doc
    {
        $lines = [
            '/**',
            ' * {@inheritdoc}',
            ' */'
        ];

        return self::makeComment(join("\n", $lines));
    }

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

    private static function makeComment(string $comment): Doc
    {
        return new Doc($comment);
    }
}