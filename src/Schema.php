<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Valentin V (Vvval)
 */
declare(strict_types=1);

namespace Cycle\ORM\Promise;

use Cycle\ORM\ORMInterface;

final class Schema
{
    public const RESOLVER_PROPERTY      = '__resolver';
    public const UNSET_PROPERTIES_CONST = '__UNSET_PROPERTIES';
    public const INIT_METHOD            = '__init';

    public const INIT_DEPENDENCIES = [
        'orm'   => ORMInterface::class,
        'role'  => 'string',
        'scope' => 'array'
    ];

    private const USE_STMTS = [
        PromiseInterface::class,
        Resolver::class,
        ProxyFactoryException::class,
        ORMInterface::class
    ];

    /** @var ConflictResolver */
    private $resolver;

    public function __construct(ConflictResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function useStmts(Declaration\DeclarationInterface $class, ?Declaration\DeclarationInterface $parent): array
    {
        $useStmts = self::USE_STMTS;
        if ($parent !== null && !empty($parent->getFullName()) && $class->getNamespaceName() !== $parent->getNamespaceName()) {
            $useStmts[] = $parent->getFullName();
        }

        return $useStmts;
    }

    public function initMethodName(?Declaration\Structure $structure): string
    {
        if ($structure === null) {
            return $this->resolver->resolve([], Schema::INIT_METHOD)->fullName();
        }

        return $this->resolver->resolve($structure->methodNames(), Schema::INIT_METHOD)->fullName();
    }

    public function resolverPropertyName(?Declaration\Structure $structure): string
    {
        if ($structure === null) {
            return $this->resolver->resolve([], Schema::RESOLVER_PROPERTY)->fullName();
        }

        return $this->resolver->resolve($structure->properties, Schema::RESOLVER_PROPERTY)->fullName();
    }

    public function unsetPropertiesConstName(?Declaration\Structure $structure): string
    {
        if ($structure === null) {
            return $this->resolver->resolve([], Schema::UNSET_PROPERTIES_CONST)->fullName('_');
        }

        return $this->resolver->resolve($structure->constants, Schema::UNSET_PROPERTIES_CONST)->fullName('_');
    }

    public function propertyType(): string
    {
        return Utils::shortName(Resolver::class);
    }
}