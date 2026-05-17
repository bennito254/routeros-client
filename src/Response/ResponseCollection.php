<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Response;

class ResponseCollection implements \Countable, \IteratorAggregate
{
    /**
     * @param ResponseSentence[] $sentences
     */
    public function __construct(private array $sentences = [])
    {
    }

    public function add(ResponseSentence $sentence): void
    {
        $this->sentences[] = $sentence;
    }

    public function first(): ?ResponseSentence
    {
        return $this->sentences[0] ?? null;
    }

    public function last(): ?ResponseSentence
    {
        $count = count($this->sentences);
        return $count > 0 ? $this->sentences[$count - 1] : null;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->sentences as $sentence) {
            // We only expose !re attributes typically when calling toArray
            if ($sentence->isType(ResponseSentence::TYPE_RE)) {
                $result[] = $sentence->getAttributes();
            }
        }
        return $result;
    }

    public function count(): int
    {
        return count($this->sentences);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->sentences);
    }

    public function getSentences(): array
    {
        return $this->sentences;
    }
}
