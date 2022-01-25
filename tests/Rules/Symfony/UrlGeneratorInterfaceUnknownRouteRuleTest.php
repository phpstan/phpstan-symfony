<?php declare(strict_types = 1);

namespace PHPStan\Rules\Symfony;

use PHPStan\Rules\Rule;
use PHPStan\Symfony\Configuration;
use PHPStan\Symfony\PhpUrlGeneratingRoutesMapFactory;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<UrlGeneratorInterfaceUnknownRouteRule>
 */
final class UrlGeneratorInterfaceUnknownRouteRuleTest extends RuleTestCase
{

	protected function getRule(): Rule
	{
		return new UrlGeneratorInterfaceUnknownRouteRule((new PhpUrlGeneratingRoutesMapFactory(new Configuration(['urlGeneratingRulesFile' => __DIR__ . '/url_generating_routes.php'])))->create());
	}

	public function testGenerate(): void
	{
		if (!class_exists('Symfony\Bundle\FrameworkBundle\Controller\AbstractController')) {
			self::markTestSkipped();
		}

		$this->analyse(
			[
				__DIR__ . '/ExampleControllerWithRouting.php',
			],
			[
				[
					'Route with name "unknown" does not exist.',
					17,
				],
			]
		);
	}

	public static function getAdditionalConfigFiles(): array
	{
		return [
			__DIR__ . '/../../../extension.neon',
		];
	}

}
