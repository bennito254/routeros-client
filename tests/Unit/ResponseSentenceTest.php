<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Tests\Unit;

use Bennito254\RouterOS\Response\ResponseSentence;
use PHPUnit\Framework\TestCase;

class ResponseSentenceTest extends TestCase
{
    public function testParseDoneSentence(): void
    {
        $words = ['!done', '=ret=12345'];
        $sentence = ResponseSentence::parse($words);

        $this->assertTrue($sentence->isType(ResponseSentence::TYPE_DONE));
        $this->assertSame('12345', $sentence->getAttribute('ret'));
        $this->assertNull($sentence->getTag());
    }

    public function testParseReSentenceWithTag(): void
    {
        $words = ['!re', '=name=ether1', '=type=ether', '.tag=1'];
        $sentence = ResponseSentence::parse($words);

        $this->assertTrue($sentence->isType(ResponseSentence::TYPE_RE));
        $this->assertSame('ether1', $sentence->getAttribute('name'));
        $this->assertSame('ether', $sentence->getAttribute('type'));
        $this->assertSame('1', $sentence->getTag());
    }

    public function testParseTrapSentence(): void
    {
        $words = ['!trap', '=message=no such item', '=category=0'];
        $sentence = ResponseSentence::parse($words);

        $this->assertTrue($sentence->isType(ResponseSentence::TYPE_TRAP));
        $this->assertSame('no such item', $sentence->getAttribute('message'));
        $this->assertSame('0', $sentence->getAttribute('category'));
    }
}
