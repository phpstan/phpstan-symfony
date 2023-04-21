<?php // lint >= 7.4

namespace RequiredAnnotationTest;

class TestAnnotations
{
	/** @required */
	public string $one;

	private string $two;

	public string $three;

	private string $four;

	/**
	 * @required
	 */
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
