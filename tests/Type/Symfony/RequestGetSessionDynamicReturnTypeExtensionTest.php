<?php declare(strict_types = 1);

namespace PHPStan\Type\Symfony;

use Iterator;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function strpos;

final class RequestGetSessionDynamicReturnTypeExtensionTest extends ExtensionTestCase
{

	/**
	 * @dataProvider getContentProvider
	 */
	public function testGetContent(string $expression, string $type): void
	{
		$this->processFile(
			__DIR__ . '/request_get_session.php',
			$expression,
			$type,
			new RequestGetSessionDynamicReturnTypeExtension(new Standard())
		);
	}

	/** @return MethodTypeSpecifyingExtension[] */
	protected function getMethodTypeSpecifyingExtensions(): array
	{
		return [
			new RequestTypeSpecifyingExtension($this->createBroker(), new Standard()),
		];
	}

	/**
	 * @return Iterator<int, array{string, string}>
	 */
	public function getContentProvider(): Iterator
	{
		$ref = new ReflectionMethod(Request::class, 'getSession');
		$doc = (string) $ref->getDocComment();

		$checkedTypeString = SessionInterface::class;
		if (strpos($doc, '@return SessionInterface|null') !== false) {
			$checkedTypeString .= '|null';
		}

		yield ['$session1', $checkedTypeString];
		yield ['$session2', SessionInterface::class];
	}

}
