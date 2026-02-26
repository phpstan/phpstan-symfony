<?php declare(strict_types = 1);

namespace MessengerHandleTrait;

use Symfony\Component\Messenger\HandleTrait;
use function PHPStan\Testing\assertType;

class RegularQuery {}
class RegularQueryResult {}
class RegularQueryHandler
{
    public function __invoke(RegularQuery $query): RegularQueryResult
    {
        return new RegularQueryResult();
    }
}

class TaggedQuery {}
class TaggedResult {}
class TaggedHandler
{
    public function handle(TaggedQuery $query): TaggedResult
    {
        return new TaggedResult();
    }
}

class MultiHandlersForTheSameMessageQuery {}
class MultiHandlersForTheSameMessageHandler1
{
    public function __invoke(MultiHandlersForTheSameMessageQuery $query): bool
    {
        return true;
    }
}
class MultiHandlersForTheSameMessageHandler2
{
    public function __invoke(MultiHandlersForTheSameMessageQuery $query): bool
    {
        return false;
    }
}

class HandleTraitClass {
    use HandleTrait;

    public function __invoke()
    {
        assertType(RegularQueryResult::class, $this->handle(new RegularQuery()));

        assertType(TaggedResult::class, $this->handle(new TaggedQuery()));

		$randomQuery = rand(0, 1) ? new RegularQuery() : new TaggedQuery();
		assertType(RegularQueryResult::class . '|' . TaggedResult::class, $this->handle($randomQuery));

        // HandleTrait will throw exception in fact due to multiple handle methods/handlers per single query
        assertType('mixed', $this->handle(new MultiHandlersForTheSameMessageQuery()));
    }
}

class QueryBus {
    use HandleTrait;

    public function dispatch(object $query)
    {
        return $this->handle($query);
    }

	public function dispatch2(object $query)
	{
		return $this->handle($query);
	}
}

interface QueryBusInterface {
	public function dispatch(object $query);
}

class QueryBusWithInterface implements QueryBusInterface {
	use HandleTrait;

	public function dispatch(object $query)
	{
		return $this->handle($query);
	}
}

class Controller {
    public function action()
    {
        $queryBus = new QueryBus();

        assertType(RegularQueryResult::class, $queryBus->dispatch(new RegularQuery()));

        assertType('bool', $queryBus->dispatch(new BooleanQuery()));
        assertType('int', $queryBus->dispatch(new IntQuery()));
        assertType('float', $queryBus->dispatch(new FloatQuery()));
        assertType('string', $queryBus->dispatch(new StringQuery()));

		$randomQuery = rand(0, 1) ? new IntQuery() : new StringQuery();
		assertType('int|string', $queryBus->dispatch($randomQuery));

        assertType(TaggedResult::class, $queryBus->dispatch(new TaggedQuery()));

		assertType(RegularQueryResult::class, $queryBus->dispatch2(new RegularQuery()));

		$queryBusWithInterface = new QueryBusWithInterface();

		assertType(RegularQueryResult::class, $queryBusWithInterface->dispatch(new RegularQuery()));

		$randomQueryBus = rand(0, 1) ? $queryBus : $queryBusWithInterface;
		assertType(RegularQueryResult::class, $randomQueryBus->dispatch(new RegularQuery()));

        // HandleTrait will throw exception in fact due to multiple handle methods/handlers per single query
        assertType('mixed', $queryBus->dispatch(new MultiHandlesForInTheSameHandlerQuery()));
        assertType('mixed', $queryBus->dispatch(new MultiHandlersForTheSameMessageQuery()));
    }
}
