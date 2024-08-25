<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use PHPStan\ShouldNotHappenException;
use Twig\Environment;
use function file_exists;
use function is_readable;
use function sprintf;

final class TwigEnvironmentResolver
{

	/** @var string|null */
	private $twigEnvironmentLoader;

	/** @var Environment|null */
	private $twigEnvironment;

	public function __construct(?string $twigEnvironmentLoader)
	{
		$this->twigEnvironmentLoader = $twigEnvironmentLoader;
	}

	private function getTwigEnvironment(): ?Environment
	{
		if ($this->twigEnvironmentLoader === null) {
			return null;
		}

		if ($this->twigEnvironment !== null) {
			return $this->twigEnvironment;
		}

		if (!file_exists($this->twigEnvironmentLoader)
			|| !is_readable($this->twigEnvironmentLoader)
		) {
			throw new ShouldNotHappenException(sprintf('Cannot load Twig environment. Check the parameters.symfony.twigEnvironmentLoader setting in PHPStan\'s config. The offending value is "%s".', $this->twigEnvironmentLoader));
		}

		return $this->twigEnvironment = require $this->twigEnvironmentLoader;
	}

	public function templateExists(string $name): bool
	{
		$twigEnvironment = $this->getTwigEnvironment();

		if ($twigEnvironment === null) {
			return true;
		}

		return $twigEnvironment->getLoader()->exists($name);
	}

}
