<?php declare(strict_types = 1);

namespace PHPStan\Symfony;

use function sprintf;

final class PhpUrlGeneratingRoutesMapFactory implements UrlGeneratingRoutesMapFactory
{

	/** @var string|null */
	private $urlGeneratingRoutesFile;

	public function __construct(Configuration $configuration)
	{
		$this->urlGeneratingRoutesFile = $configuration->getUrlGeneratingRoutesFile();
	}

	public function create(): UrlGeneratingRoutesMap
	{
		if ($this->urlGeneratingRoutesFile === null) {
			return new FakeUrlGeneratingRoutesMap();
		}

		if (file_exists($this->urlGeneratingRoutesFile) === false) {
			throw new UrlGeneratingRoutesFileNotExistsException(sprintf('File %s containing route generator information does not exist.', $this->urlGeneratingRoutesFile));
		}

		$urlGeneratingRoutes = require $this->urlGeneratingRoutesFile;

		if (!is_array($urlGeneratingRoutes)) {
			throw new UrlGeneratingRoutesFileNotExistsException(sprintf('File %s containing route generator information cannot be parsed.', $this->urlGeneratingRoutesFile));
		}

		/** @var \PHPStan\Symfony\UrlGeneratingRoutesDefinition[] $routes */
		$routes = [];
		foreach ($urlGeneratingRoutes as $routeName => $routeConfiguration) {
			if (!is_string($routeName)) {
				continue;
			}

			if (!is_array($routeConfiguration) || !isset($routeConfiguration[1]['_controller'])) {
				continue;
			}

			$routes[] = new UrlGeneratingRoute($routeName, $routeConfiguration[1]['_controller']);
		}

		return new DefaultUrlGeneratingRoutesMap($routes);
	}

}
