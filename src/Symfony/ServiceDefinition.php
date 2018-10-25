<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

interface ServiceDefinition
{

	public function getId(): string;

	public function getClass(): ?string;

	public function isPublic(): bool;

	public function isSynthetic(): bool;

	public function getAlias(): ?string;

}
