<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\PhpDoc\StubFilesExtension;

class PasswordAuthenticatedUserStubFilesExtension implements StubFilesExtension
{

	/** @var Reflector */
	private $reflector;

	public function __construct(
		Reflector $reflector
	)
	{
		$this->reflector = $reflector;
	}

	public function getFiles(): array
	{
		try {
			$this->reflector->reflectClass('Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface');
		} catch (IdentifierNotFound $e) {
			return [];
		}

		return [
			__DIR__ . '/../../stubs/Symfony/Component/Security/Core/User/PasswordAuthenticatedUserInterface.stub',
			__DIR__ . '/../../stubs/Symfony/Component/Security/Core/User/PasswordUpgraderInterface.stub',
		];
	}

}
