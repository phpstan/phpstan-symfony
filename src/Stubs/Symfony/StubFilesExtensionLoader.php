<?php declare(strict_types = 1);

namespace PHPStan\Stubs\Symfony;

use Composer\InstalledVersions;
use OutOfBoundsException;
use PHPStan\PhpDoc\StubFilesExtension;
use function class_exists;
use function dirname;
use function version_compare;

class StubFilesExtensionLoader implements StubFilesExtension
{

	public function getFiles(): array
	{
		$stubsDir = dirname(__DIR__, 3) . '/stubs';
		$files = [];

		if ($this->isInstalledVersionBelow('symfony/security-bundle', '6.3.0.0')) {
			$files[] = $stubsDir . '/Symfony/Bundle/SecurityBundle/DependencyInjection/Security/Factory/AuthenticatorFactoryInterface.stub';
			$files[] = $stubsDir . '/Symfony/Bundle/SecurityBundle/DependencyInjection/Security/Factory/FirewallListenerFactoryInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/console', '6.2.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Console/Command.stub';
			$files[] = $stubsDir . '/Symfony/Component/Console/Exception/ExceptionInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Console/Exception/InvalidArgumentException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Console/Exception/LogicException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Console/Helper/HelperInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/console', '8.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Console/Output/OutputInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/dependency-injection', '6.2.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/DependencyInjection/ContainerBuilder.stub';
			$files[] = $stubsDir . '/Symfony/Component/DependencyInjection/Extension/ExtensionInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/event-dispatcher-contracts', '3.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/EventDispatcher/EventDispatcherInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/event-dispatcher', '5.4.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/EventDispatcher/EventSubscriberInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/EventDispatcher/GenericEvent.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/form', '6.2.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Form/Exception/ExceptionInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Form/Exception/RuntimeException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Form/Exception/TransformationFailedException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Form/DataTransformerInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/form', '8.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Form/ChoiceList/Loader/ChoiceLoaderInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/http-foundation', '5.4.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/HttpFoundation/HeaderBag.stub';
			$files[] = $stubsDir . '/Symfony/Component/HttpFoundation/Session.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/http-foundation', '6.2.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/HttpFoundation/Cookie.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/http-foundation', '7.4.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/HttpFoundation/ParameterBag.stub';

			if (!$this->isInstalledVersionBelow('symfony/http-foundation', '5.1.0.0')) {
				$files[] = $stubsDir . '/Symfony/Component/HttpFoundation/InputBag.stub';
				$files[] = $stubsDir . '/Symfony/Component/HttpFoundation/Request.stub';
			}
		}

		if ($this->isInstalledVersionBelow('symfony/messenger', '6.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Messenger/Envelope.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/messenger', '8.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Messenger/TraceableMessageBus.stub';
			$files[] = $stubsDir . '/Symfony/Component/Messenger/StampInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/process', '5.4.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Process/Exception/LogicException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Process/Process.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/property-access', '8.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/Exception/AccessException.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/Exception/ExceptionInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/Exception/InvalidArgumentException.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/Exception/RuntimeException.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/Exception/UnexpectedTypeException.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/PropertyAccessor.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/PropertyAccessorInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/PropertyAccess/PropertyPathInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/security-acl', '3.2.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Security/Acl/Model/AclInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Security/Acl/Model/EntryInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/security-core', '8.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Security/Core/Authentication/Token/TokenInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Security/Core/Authorization/Voter/VoterInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/security-core', '6.3.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Security/Core/Authorization/Voter/Voter.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/serializer', '7.0.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Encoder/ContextAwareDecoderInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Normalizer/ContextAwareDenormalizerInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Normalizer/ContextAwareNormalizerInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/serializer', '7.4.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Normalizer/DenormalizerInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/serializer', '8.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Encoder/DecoderInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Encoder/EncoderInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/BadMethodCallException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/CircularReferenceException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/ExceptionInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/ExtraAttributesException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/InvalidArgumentException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/LogicException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/RuntimeException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Exception/UnexpectedValueException.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Normalizer/DenormalizableInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Normalizer/NormalizableInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Serializer/Normalizer/NormalizerInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/validator', '5.4.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Validator/ConstraintViolationInterface.stub';
			$files[] = $stubsDir . '/Symfony/Component/Validator/ConstraintViolationListInterface.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/validator', '7.1.0.0')) {
			$files[] = $stubsDir . '/Symfony/Component/Validator/Constraint.stub';
			$files[] = $stubsDir . '/Symfony/Component/Validator/Constraints/Composite.stub';
			$files[] = $stubsDir . '/Symfony/Component/Validator/Constraints/Compound.stub';
		}

		if ($this->isInstalledVersionBelow('symfony/cache-contracts', '3.2.1.0')) {
			$files[] = $stubsDir . '/Symfony/Contracts/Cache/CacheInterface.stub';
			$files[] = $stubsDir . '/Symfony/Contracts/Cache/CallbackInterface.stub';
			$files[] = $stubsDir . '/Symfony/Contracts/Cache/ItemInterface.stub';
			$files[] = $stubsDir . '/Psr/Cache/CacheException.stub';
			$files[] = $stubsDir . '/Psr/Cache/CacheItemInterface.stub';
			$files[] = $stubsDir . '/Psr/Cache/InvalidArgumentException.stub';
		}

		if ($this->isInstalledVersionBelow('twig/twig', '3.15.0.0')) {
			$files[] = $stubsDir . '/Twig/Node/Node.stub';
		}

		return $files;
	}

	private function isInstalledVersionBelow(string $package, string $maxVersion): bool
	{
		if (!class_exists(InstalledVersions::class)) {
			return false;
		}

		try {
			$installedVersion = InstalledVersions::getVersion($package);
		} catch (OutOfBoundsException $e) {
			return false;
		}

		return $installedVersion !== null && version_compare($installedVersion, $maxVersion, '<');
	}

}
