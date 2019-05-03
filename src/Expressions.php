<?php
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use PhpParser\Node;

class Expressions
{
    public static function unsetFunc(string $object, string $property): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\FuncCall(
                new Node\Name('unset'),
                [new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property))]
            )
        );
    }

    public static function inArrayFunc(string $name, string $object, string $haystackProperty): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(new Node\Name('in_array'), [
                new Node\Arg(new Node\Expr\Variable($name)),
                new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $haystackProperty)),
                new Node\Arg(new Node\Expr\ConstFetch(new Node\Name('true')))
            ]
        );
    }

    public static function resolveIntoVar(string $var, string $object, string $property, string $method): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\Variable($var),
                new Node\Expr\MethodCall(
                    new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property),
                    $method
                )
            )
        );
    }
}