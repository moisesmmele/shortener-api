<?php

namespace Moises\ShortenerApi\Tests\Domain;

use Moises\ShortenerApi\Domain\Entities\Link;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LinkTest extends TestCase
{
    #[Test]
    public function setId_throws_exception_when_negative(): void
    {
        $invalidId = -1;
        $link = new Link();
        $this->expectException(\DomainException::class);
        $link->setId($invalidId);
    }

    #[Test]
    public function setId_throws_exception_when_zero(): void
    {
        $invalidId = 0;
        $link = new Link();
        $this->expectException(\DomainException::class);
        $link->setId($invalidId);
    }

    #[Test]
    public function setLongUrl_throws_exception_when_url_is_empty(): void
    {
        $emptyUrl = '';
        $link = new Link();
        $this->expectException(\DomainException::class);
        $link->setLongUrl($emptyUrl);
    }

    #[Test]
    public function setLongUrl_throws_exception_when_url_has_no_scheme(): void
    {
        $invalidUrl = 'www.google.com';
        $link = new Link();
        $this->expectException(\DomainException::class);
        $link->setLongUrl($invalidUrl);
    }

    #[Test]
    public function setLongUrl_throws_exception_when_url_has_invalid_chars():void
    {
        $invalidUrl = 'http://google .com';
        $link = new Link();
        $this->expectException(\DomainException::class);
        $link->setLongUrl($invalidUrl);
    }

    #[Test]
    public function setLongUrl_throws_exception_when_url_has_more_than_2048_chars(): void
    {
        $ooo = str_repeat('o', 2049);
        $invalidUrl = 'http://goo'. $ooo . 'gle.com';
        $link = new Link();
        $this->expectException(\DomainException::class);
        $link->setLongUrl($invalidUrl);
    }

    #[Test]
    public function setShortcode_throws_exception_when_shortcode_is_longer_than_provided_max_length(): void
    {
        $maxLength = 8;
        $invalidShortcode = str_repeat('o', $maxLength + 1);
        $link = new Link();
        $link->setShortcodeMaxLength($maxLength);
        $this->expectException(\DomainException::class);
        $link->setShortcode($invalidShortcode);
    }

    #[Test]
    public function setShortcode_must_decapitalize_provided_shortcode(): void
    {
        $maxLength = 8;
        $capitalizedShortcode = str_repeat('A', $maxLength);
        $link = new Link();
        $link->setShortcodeMaxLength($maxLength);
        $link->setShortcode($capitalizedShortcode);

        $reflection = new \ReflectionClass(Link::class);
        $property = $reflection->getProperty('shortcode');

        $this->assertEquals($property->getValue($link), strtolower($capitalizedShortcode));
    }
}