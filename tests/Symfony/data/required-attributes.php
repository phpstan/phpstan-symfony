<?php // lint >= 8.0

namespace RequiredAttributesTest;

use Symfony\Contracts\Service\Attribute\Required;

class TestAttributes
{
	#[Required]
	public string $one;

	private string $two;

	public string $three;

	private string $four;

	#[Required]
	public function setTwo(int $two): void
	{
		$this->two = $two;
	}

	public function getTwo(): int
	{
		return $this->two;
	}

	public function setFour(int $four): void
	{
		$this->four = $four;
	}

	public function getFour(): int
	{
		return $this->four;
	}
}
