<?php

/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\Promise\Exception\ProxyFactoryException;
use PhpParser\Node;

final class Expressions
{
    /**
     * @param string $object
     * @param string $property
     * @return Node\Expr\FuncCall
     */
    public static function unsetFunc(string $object, string $property): Node\Expr\FuncCall
    {
        return self::funcCall('unset', [
            new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property))
        ]);
    }

    /**
     * @param string $object
     * @param string $property
     * @return Node\Expr\FuncCall
     */
    public static function issetFunc(string $object, string $property): Node\Expr\FuncCall
    {
        return self::funcCall('isset', [
            new Node\Arg(new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property))
        ]);
    }

    /**
     * @param string $name
     * @param string $object
     * @param string $haystackConst
     * @return Node\Expr\FuncCall
     */
    public static function inConstArrayFunc(string $name, string $object, string $haystackConst): Node\Expr\FuncCall
    {
        return self::funcCall('in_array', [
            new Node\Arg(new Node\Expr\Variable($name)),
            new Node\Arg(new Node\Expr\ClassConstFetch(new Node\Name($object), $haystackConst)),
            new Node\Arg(self::const('true'))
        ]);
    }

    /**
     * @param Node\Expr $condition
     * @param Node\Stmt $stmt
     * @return Node\Stmt\If_
     */
    public static function throwExceptionOnNull(Node\Expr $condition, Node\Stmt $stmt): Node\Stmt\If_
    {
        $if = new Node\Stmt\If_(self::notNull($condition));
        $if->stmts[] = $stmt;
        $if->else = new Node\Stmt\Else_();
        $if->else->stmts[] = self::throwException(
            Utils::shortName(ProxyFactoryException::class),
            'Promise not loaded'
        );

        return $if;
    }

    /**
     * @param string $name
     * @return Node\Expr\ConstFetch
     */
    public static function const(string $name): Node\Expr\ConstFetch
    {
        return new Node\Expr\ConstFetch(new Node\Name($name));
    }

    public static function resolveIntoVar(
        string $var,
        string $object,
        string $property,
        string $method
    ): Node\Stmt\Expression {
        return new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\Variable($var),
                self::resolveMethodCall($object, $property, $method)
            )
        );
    }

    /**
     * @param string $object
     * @param string $property
     * @param string $method
     * @return Node\Expr\MethodCall
     */
    public static function resolveMethodCall(string $object, string $property, string $method): Node\Expr\MethodCall
    {
        return new Node\Expr\MethodCall(self::resolvePropertyFetch($object, $property), $method);
    }

    /**
     * @param string $object
     * @param string $property
     * @return Node\Expr\PropertyFetch
     */
    public static function resolvePropertyFetch(string $object, string $property): Node\Expr\PropertyFetch
    {
        return new Node\Expr\PropertyFetch(new Node\Expr\Variable($object), $property);
    }

    /**
     * @param Node\Expr $expr
     * @return Node\Expr\BinaryOp\Identical
     */
    public static function equalsFalse(Node\Expr $expr): Node\Expr\BinaryOp\Identical
    {
        return new Node\Expr\BinaryOp\Identical($expr, self::const('false'));
    }

    /**
     * @param Node\Expr $expr
     * @return Node\Expr\BinaryOp\NotIdentical
     */
    public static function notNull(Node\Expr $expr): Node\Expr\BinaryOp\NotIdentical
    {
        return new Node\Expr\BinaryOp\NotIdentical($expr, self::const('null'));
    }

    /**
     * @param string $name
     * @param array  $args
     * @param array  $attributes
     * @return Node\Expr\FuncCall
     */
    private static function funcCall(string $name, array $args = [], array $attributes = []): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(new Node\Name($name), $args, $attributes);
    }

    /**
     * @param string $class
     * @param string $message
     * @return Node\Stmt\Throw_
     */
    private static function throwException(string $class, string $message): Node\Stmt\Throw_
    {
        return new Node\Stmt\Throw_(
            new Node\Expr\New_(new Node\Name($class), [new Node\Arg(new Node\Scalar\String_($message))])
        );
    }
}
