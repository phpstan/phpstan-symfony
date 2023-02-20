<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\BetterReflection\Reflector\Exception\IdentifierNotFound;
use PHPStan\BetterReflection\Reflector\Reflector;
use PHPStan\PhpDoc\StubFilesExtension;

class InputBagStubFilesExtension implements StubFilesExtension
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
			$this->reflector->reflectClass('Symfony\Component\HttpFoundation\InputBag');
		} catch (IdentifierNotFound $e) {
			return [];
		}

		return [
			__DIR__ . '/../../stubs/Symfony/Component/HttpFoundation/InputBag.stub',
			__DIR__ . '/../../stubs/Symfony/Component/HttpFoundation/Request.stub',
		];
	}

}
