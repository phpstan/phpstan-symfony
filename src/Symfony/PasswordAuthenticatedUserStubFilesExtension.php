<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\PhpDoc\StubFilesExtension;
use function interface_exists;

class PasswordAuthenticatedUserStubFilesExtension implements StubFilesExtension
{

	public function getFiles(): array
	{
		if (!interface_exists('Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface')) {
			return [];
		}

		return [
			__DIR__ . '/../../stubs/Symfony/Component/Security/Core/User/PasswordAuthenticatedUserInterface.stub',
			__DIR__ . '/../../stubs/Symfony/Component/Security/Core/User/PasswordUpgraderInterface.stub',
		];
	}

}
