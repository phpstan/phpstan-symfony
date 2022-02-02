<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\PhpDoc\StubFilesExtension;
use function class_exists;

class InputBagStubFilesExtension implements StubFilesExtension
{

	public function getFiles(): array
	{
		if (!class_exists('Symfony\Component\HttpFoundation\InputBag')) {
			return [];
		}

		return [
			__DIR__ . '/../../stubs/Symfony/Component/HttpFoundation/InputBag.stub',
			__DIR__ . '/../../stubs/Symfony/Component/HttpFoundation/Request.stub',
		];
	}

}
