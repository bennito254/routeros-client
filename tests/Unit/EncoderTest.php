<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Tests\Unit;

use Bennito254\RouterOS\Protocol\Encoder;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    private Encoder $encoder;

    protected function setUp(): void
    {
        $this->encoder = new Encoder();
    }

    public function testEncodeWordSmallLength(): void
    {
        // "abc" has length 3. Encoding: chr(3) . "abc"
        $expected = chr(3) . 'abc';
        $this->assertSame($expected, $this->encoder->encodeWord('abc'));
    }

    public function testEncodeSentence(): void
    {
        $words = ['/login', '=name=admin'];
        $expected = chr(6) . '/login' . chr(11) . '=name=admin' . chr(0);
        $this->assertSame($expected, $this->encoder->encodeSentence($words));
    }
}
