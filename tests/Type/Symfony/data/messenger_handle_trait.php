<?php declare(strict_types = 1);

namespace MessengerHandleTrait;

use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
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

class StringQuery {}
class IntQuery {}
class FloatQuery {}
class MultiQueryHandler implements MessageSubscriberInterface
{
    public static function getHandledMessages(): iterable
    {
        yield StringQuery::class;
        yield IntQuery::class => ['method' => 'handleInt'];
        yield FloatQuery::class => ['method' => 'handleFloat'];
        yield StringQuery::class => ['method' => 'handleString'];
    }

    public function __invoke(StringQuery $query): string
    {
        return 'string result';
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

    // todo add handle method with union return type?
}

class HandleTraitClass {
    use HandleTrait;

    public function __invoke()
    {
        assertType(RegularQueryResult::class, $this->handle(new RegularQuery()));
        assertType('int', $this->handle(new IntQuery()));
        assertType('float', $this->handle(new FloatQuery()));

        // HandleTrait will throw exception in fact due to multiple handlers per single query
        assertType('mixed', $this->handle(new StringQuery()));
    }
}
