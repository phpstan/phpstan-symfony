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

	private FileTypeMapper $fileTypeMapper;

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

		$declaringClass = $property->getDeclaringClass();
		$declaringTrait = null;
		if ($property instanceof PhpPropertyReflection && $property->getDeclaringTrait() !== null) {
			$declaringTrait = $property->getDeclaringTrait()->getName();
		}

		if (
			$property->getDocComment() !== null
			&& $declaringClass->getFileName() !== null
			&& $this->isRequiredFromDocComment($declaringClass->getFileName(), $declaringClass->getName(), $declaringTrait, null, $property->getDocComment())
		) {
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

		foreach ($nativeReflection->getBetterReflection()->getMethods() as $method) {
			if (!$method->isPublic()) {
				continue;
			}

			if ($method->getImplementingClass()->getName() !== $nativeReflection->getName()) {
				continue;
			}

			$declaringTrait = null;
			if ($method->getDeclaringClass()->isTrait()) {
				$declaringTrait = $method->getDeclaringClass()->getName();
			}

			if (
				$method->getDocComment() !== null
				&& $method->getFileName() !== null
				&& $this->isRequiredFromDocComment($method->getFileName(), $nativeReflection->getName(), $declaringTrait, $method->getName(), $method->getDocComment())
			) {
				$additionalConstructors[] = $method->getName();
			}

			if (count($method->getAttributesByName('Symfony\Contracts\Service\Attribute\Required')) === 0) {
				continue;
			}

			$additionalConstructors[] = $method->getName();
		}

		return $additionalConstructors;
	}

	private function isRequiredFromDocComment(string $fileName, string $className, ?string $traitName, ?string $functionName, string $docComment): bool
	{
		$phpDoc = $this->fileTypeMapper->getResolvedPhpDoc($fileName, $className, $traitName, $functionName, $docComment);

		foreach ($phpDoc->getPhpDocNodes() as $node) {
			// @required tag is available, meaning this property is always initialized
			if (count($node->getTagsByName('@required')) > 0) {
				return true;
			}
		}

		return false;
	}

}
