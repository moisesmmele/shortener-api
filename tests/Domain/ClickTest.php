<?php

namespace Moises\ShortenerApi\Tests\Domain;

use Moises\ShortenerApi\Domain\Entities\Click;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Click::class)]
class ClickTest extends TestCase
{
    #[Test]
    public function setUtcTimestamp_accepts_valid_format()
    {
        $validDatetimeString = '2024-12-30 23:59:59';
        $click = new Click();
        $click->setUtcTimestamp($validDatetimeString);
        $outputDatetimeString = $click->getUtcTimestamp();
        $this->assertEquals($validDatetimeString, $outputDatetimeString);
    }

    #[Test]
    public function setUtcTimestamp_throws_exception_when_invalid_format()
    {
        $this->expectException(\DomainException::class);
        $invalidDatetimeString = '2024-30-12 24:69:69';
        $click = new Click();
        $click->setUtcTimestamp($invalidDatetimeString);
    }

}