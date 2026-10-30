<?php declare(strict_types = 1);

namespace AutowireCallableRules;

use Closure;
use Symfony\Component\DependencyInjection\Attribute\AutowireCallable;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

class Formatter
{

	public function format(string $value): string
	{
		return $value;
	}

	private function hidden(): void
	{
	}

}

interface WrongConverter
{
	public function convert(int $value): int;
}

interface MultipleMethods
{
	public function first(): void;
	public function second(): void;
}

class Consumer
{
	public function __construct(
		#[AutowireCallable(service: Formatter::class, method: 'missing')]
		Closure $missing,
		#[AutowireCallable(service: Formatter::class, method: 'hidden')]
		Closure $hidden,
		#[AutowireCallable(callable: [Formatter::class, 'format'])]
		Closure $static,
		#[AutowireCallable(service: Formatter::class, method: 'format')]
		WrongConverter $wrong,
		#[AutowireCallable(service: Formatter::class, method: 'format')]
		MultipleMethods $multiple
	)
	{
	}
}

class MagicFormatter
{
	/**
	 * @param list<mixed> $arguments
	 * @return string
	 */
	public function __call(string $name, array $arguments)
	{
		return $name;
	}
}

class MagicConsumer
{
	public function __construct(
		#[AutowireCallable(service: MagicFormatter::class, method: 'format', lazy: false)]
		Closure $format
	)
	{
	}
}
