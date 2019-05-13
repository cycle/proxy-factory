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

    public static function issetFunc(string $object, string $property): Node\Expr\FuncCall
    {
        return
            new Node\Expr\FuncCall(
                new Node\Name('isset'),
                [new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property))]

            );
    }

    public static function inConstArrayFunc(string $name, string $object, string $haystackConst): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(new Node\Name('in_array'), [
                new Node\Arg(new Node\Expr\Variable($name)),
                new Node\Arg(new Node\Expr\ClassConstFetch(new Node\Name($object), $haystackConst)),
                new Node\Arg(new Node\Expr\ConstFetch(new Node\Name('true')))
            ]
        );
    }

    public static function resolveIntoVar(string $var, string $object, string $property, string $method): Node\Stmt\Expression
    {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\Variable($var),
                self::resolveMethodCall($object, $property, $method)
            )
        );
    }

    public static function resolveMethodCall(string $object, string $property, string $method): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall(self::resolvePropertyFetch($object, $property), $method);
    }

    public static function resolvePropertyFetch(string $object, string $property): Node\Expr\PropertyFetch
    {
        return new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property);
    }
}