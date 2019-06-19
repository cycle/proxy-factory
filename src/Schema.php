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
use Cycle\ORM\Promise\Exception\ProxyFactoryException;

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

    /**
     * @param ConflictResolver $resolver
     */
    public function __construct(ConflictResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param Declaration\DeclarationInterface $class
     * @param Declaration\DeclarationInterface $parent
     * @return array
     */
    public function useStmts(Declaration\DeclarationInterface $class, Declaration\DeclarationInterface $parent): array
    {
        $useStmts = self::USE_STMTS;
        if ($class->getNamespaceName() !== $parent->getNamespaceName()) {
            $useStmts[] = $parent->getFullName();
        }

        return $useStmts;
    }

    /**
     * @param Declaration\Structure $structure
     * @return string
     */
    public function initMethodName(Declaration\Structure $structure): string
    {
        return $this->resolver->resolve($structure->methodNames(), Schema::INIT_METHOD)->fullName();
    }

    /**
     * @param Declaration\Structure $structure
     * @return string
     */
    public function resolverPropertyName(Declaration\Structure $structure): string
    {
        return $this->resolver->resolve($structure->properties, Schema::RESOLVER_PROPERTY)->fullName();
    }

    /**
     * @param Declaration\Structure $structure
     * @return string
     */
    public function unsetPropertiesConstName(Declaration\Structure $structure): string
    {
        return $this->resolver->resolve($structure->constants, Schema::UNSET_PROPERTIES_CONST)->fullName('_');
    }

    /**
     * @return string
     */
    public function propertyType(): string
    {
        return Utils::shortName(Resolver::class);
    }
}