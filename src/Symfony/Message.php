<?php

declare(strict_types=1);

namespace PHPStan\Symfony;

final class Message
{
    /** @var string */
    private $class;

    /** @var array */
    private $returnTypes;

    public function __construct(string $class, array $returnTypes)
    {
        $this->class = $class;
        $this->returnTypes = $returnTypes;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getReturnTypes(): array
    {
        return $this->returnTypes;
    }

    public function countReturnTypes(): int
    {
        return count($this->returnTypes);
    }
}
