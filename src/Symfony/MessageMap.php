<?php

declare(strict_types=1);

namespace PHPStan\Symfony;

final class MessageMap
{
    /** @var Message[] */
    private $messages = [];

    /**
     * @param Message[] $messages
     */
    public function __construct(array $messages)
    {
        foreach ($messages as $message) {
            $this->messages[$message->getClass()] = $message;
        }
    }

    public function getMessageForClass(string $class): ?Message
    {
        return $this->messages[$class] ?? null;
    }

    public function hasMessageForClass(string $class): bool
    {
        return array_key_exists($class, $this->messages);
    }
}
