<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\Reflection\AdditionalConstructorsExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\Rules\Properties\ReadWritePropertiesExtension;
use PHPStan\Type\FileTypeMapper;
use function count;

class RequiredAutowiringExtension implements ReadWritePropertiesExtension, AdditionalConstructorsExtension
{

	/** @var FileTypeMapper */
	private $fileTypeMapper;

	public function __construct(FileTypeMapper $fileTypeMapper)
	{
		$this->fileTypeMapper = $fileTypeMapper;
	}

	public function isAlwaysRead(PropertyReflection $property, string $propertyName): bool
	{
		return false;
	}

	public function isAlwaysWritten(PropertyReflection $property, string $propertyName): bool
	{
		return false;
	}

	public function isInitialized(PropertyReflection $property, string $propertyName): bool
	{
		// If the property is public, check for @required on the property itself
		if (!$property->isPublic()) {
			return false;
		}

		if ($property->getDocComment() !== null && $this->isRequiredFromDocComment($property->getDocComment())) {
			return true;
		}

		// Check for the attribute version
		if ($property instanceof PhpPropertyReflection && count($property->getNativeReflection()->getAttributes('Symfony\Contracts\Service\Attribute\Required')) > 0) {
			return true;
		}

		return false;
	}

	public function getAdditionalConstructors(ClassReflection $classReflection): array
	{
		$additionalConstructors = [];
		$nativeReflection = $classReflection->getNativeReflection();

		foreach ($nativeReflection->getMethods() as $method) {
			if (!$method->isPublic()) {
				continue;
			}

			if ($method->getDocComment() !== false && $this->isRequiredFromDocComment($method->getDocComment())) {
				$additionalConstructors[] = $method->getName();
			}

			if (count($method->getAttributes('Symfony\Contracts\Service\Attribute\Required')) === 0) {
				continue;
			}

			$additionalConstructors[] = $method->getName();
		}

		return $additionalConstructors;
	}

	private function isRequiredFromDocComment(string $docComment): bool
	{
		$phpDoc = $this->fileTypeMapper->getResolvedPhpDoc(null, null, null, null, $docComment);

		foreach ($phpDoc->getPhpDocNodes() as $node) {
			// @required tag is available, meaning this property is always initialized
			if (count($node->getTagsByName('@required')) > 0) {
				return true;
			}
		}

		return false;
	}

}
