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
    public function setId_throws_exception_when_zero()
    {
        $invalidId = 0;
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setId($invalidId);
    }
    #[Test]
    public function setId_throws_exception_when_negative()
    {
        $invalidId = -1;
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setId($invalidId);
    }
    #[Test]
    public function setLinkId_throws_exception_when_zero()
    {
        $invalidId = 0;
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setLinkId($invalidId);
    }
    #[Test]
    public function setLinkId_throws_exception_when_negative()
    {
        $invalidId = -1;
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setLinkId($invalidId);
    }
    #[Test]
    public function setUtcTimestamp_accepts_valid_string_format(): void
    {
        $click = new Click();

        $validDateTimeString = '2024-12-30 23:59:59';
        $validDateTimeObject = new \DateTimeImmutable($validDateTimeString);

        $click->setUtcTimestamp($validDateTimeString);

        $reflection = new \ReflectionClass($click);
        $property = $reflection->getProperty('utcTimestamp');

        $this->assertTrue($property->getValue($click) instanceof \DateTimeImmutable);
        $this->assertEquals($validDateTimeObject, $property->getValue($click));
    }
    #[Test]
    public function setUtcTimestamp_accepts_DateTimeImmutable(): void
    {
        $click = new Click();

        $validDateTimeString = '2024-12-30 23:59:59';
        $validDateTimeObject = new \DateTimeImmutable($validDateTimeString);

        $click->setUtcTimestamp($validDateTimeObject);

        $reflection = new \ReflectionClass($click);
        $property = $reflection->getProperty('utcTimestamp');

        $this->assertTrue($property->getValue($click) instanceof \DateTimeImmutable);
        $this->assertEquals($validDateTimeObject, $property->getValue($click));
    }
    #[Test]
    public function setUtcTimestamp_throws_exception_when_invalid_format(): void
    {
        $click = new Click();

        $invalidDatetimeString = '2024-30-12 24:69:69';

        $this->expectException(\DomainException::class);
        $click->setUtcTimestamp($invalidDatetimeString);
    }
    #[Test]
    public function getUtcTimestamp_returns_valid_datetime_object(): void
    {
        $click = new Click();

        $validDateTimeString = '2024-12-30 23:59:59';
        $validDateTimeObject = new \DateTimeImmutable($validDateTimeString);

        $click->setUtcTimestamp($validDateTimeObject);
        $timestamp = $click->getUtcTimestamp();

        $reflection = new \ReflectionClass($click);
        $property = $reflection->getProperty('utcTimestamp');

        $this->assertTrue($timestamp instanceof \DateTimeImmutable);
        $this->assertEquals($validDateTimeObject, $property->getValue($click));
    }
    #[Test]
    public function getUtcTimestampString_returns_valid_datetime_string(): void
    {
        $click = new Click();

        $validDateTimeString = '2024-12-30 23:59:59';

        $click->setUtcTimestamp($validDateTimeString);
        $timestampString = $click->getUtcTimestampString();

        $this->assertEquals($validDateTimeString, $timestampString);
    }
    #[Test]
    public function generateUtcTimestamp_sets_utcTimestamp_to_valid_dateTimeImmutable_now(): void
    {
        $click = new Click();

        $before = new \DateTimeImmutable();
        $click->generateUtcTimestamp();
        $after = new \DateTimeImmutable();


        $reflection = new \ReflectionClass($click);
        $property = $reflection->getProperty('utcTimestamp');

        $this->assertTrue($property->getValue($click) instanceof \DateTimeImmutable);
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $property->getValue($click)->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $property->getValue($click)->getTimestamp());
    }
    #[Test]
    public function setSourceIp_accepts_valid_string_format(): void
    {
        $validIpFormat = '123.123.123.123';
        $click = new Click();

        $click->setSourceIp($validIpFormat);

        $reflection = new \ReflectionClass($click);
        $property = $reflection->getProperty('sourceIp');
        $this->assertEquals($validIpFormat, $property->getValue($click));
    }
    #[Test]
    public function setSourceIp_throws_exception_when_invalid_string(): void
    {
        $invalidString = "not an IP address";
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setSourceIp($invalidString);
    }
    #[Test]
    public function setSourceIp_throws_exception_when_ip_out_of_range(): void
    {
        $invalidIpFormat = '257.257.257.257';
        $click = new Click();

        $this->expectException(\DomainException::class);
        $click->setSourceIp($invalidIpFormat);
    }
    #[Test]
    public function setReferrer_accepts_valid_referrer(): void
    {
        $validReferrer = 'http://www.referrer.com/path/to/referrer.html?query=params';

        $click = new Click();
        $click->setReferrer($validReferrer);

        $reflection = new \ReflectionClass($click);
        $property = $reflection->getProperty('referrer');

        $this->assertEquals($validReferrer, $property->getValue($click));
    }
    #[Test]
    public function setReferrer_throws_exception_when_malformed_url(): void
    {
        $invalidReferrer = 'http://www.<referrer.com/path/to/referrer.html';
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setReferrer($invalidReferrer);
    }
    #[Test]
    public function setReferrer_throws_exception_when_no_schema(): void
    {
        $invalidReferrer = 'www.referrer.com';
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setReferrer($invalidReferrer);
    }
    #[Test]
    public function setReferrer_throws_exception_when_url_contains_spaces(): void
    {
        $invalidReferrer = 'www. referrer.com';
        $click = new Click();
        $this->expectException(\DomainException::class);
        $click->setReferrer($invalidReferrer);
    }
}
