<?php // lint >= 8.1

namespace Bug468;

use Symfony\Contracts\Service\Attribute\Required;

interface MyQueryInterface {
	public function execute(): array;
}

trait GenreFetcherTrait
{
	private MyQueryInterface $query;

	protected function dosomething(): array
	{
		return $this->query->execute();
	}

	#[Required]
	public function setQuery(
		MyQueryInterface $query,
    ): void {
		$this->query = $query;
	}
}

class HelloWorld
{
	use GenreFetcherTrait;

}
