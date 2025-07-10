<?php declare(strict_types = 1);

namespace MessengerHandleTrait;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\HandleTrait;
use function PHPStan\Testing\assertType;

class BooleanQuery {}
class StringQuery {}
class IntQuery {}
class FloatQuery {}
class MultiQueryHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield BooleanQuery::class;
        yield IntQuery::class => ['method' => 'handleInt'];
        yield FloatQuery::class => ['method' => 'handleFloat'];
        yield StringQuery::class => ['method' => 'handleString'];
    }

    public function __invoke(BooleanQuery $query): bool
    {
        return true;
    }

    public function handleInt(IntQuery $query): int
    {
        return 0;
    }

    public function handleFloat(FloatQuery $query): float
    {
        return 0.0;
    }

    public function handleString(StringQuery $query): string
    {
        return 'string result';
    }
}

class MultiHandlesForInTheSameHandlerQuery {}
class MultiHandlesForInTheSameHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield MultiHandlesForInTheSameHandlerQuery::class;
        yield MultiHandlesForInTheSameHandlerQuery::class => ['priority' => '0'];
    }

    public function __invoke(MultiHandlesForInTheSameHandlerQuery $query): bool
    {
        return true;
    }
}

class HandleTraitClass {
    use HandleTrait;

    public function __invoke()
    {
        assertType('bool', $this->handle(new BooleanQuery()));
        assertType('int', $this->handle(new IntQuery()));
        assertType('float', $this->handle(new FloatQuery()));
        assertType('string', $this->handle(new StringQuery()));

        // HandleTrait will throw exception in fact due to multiple handle methods/handlers per single query
        assertType('mixed', $this->handle(new MultiHandlesForInTheSameHandlerQuery()));
    }
}
