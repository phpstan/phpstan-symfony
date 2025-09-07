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

        // HandleTrait will throw exception in fact due to multiple handle methods/handlers per single query
        assertType('mixed', $this->handle(new MultiHandlersForTheSameMessageQuery()));
    }
}
