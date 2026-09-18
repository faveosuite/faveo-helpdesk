<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider;

use Mremi\UrlShortener\Provider\ChainProvider;

/**
 * Tests ChainProvider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class ChainProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that an unknown provider throws an exception
     *
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Unable to retrieve the provider named: "foo"
     */
    public function testUnknownProvider()
    {
        $chainProvider = new ChainProvider;
        $chainProvider->getProvider('foo');
    }

    /**
     * Tests to add and get some providers
     */
    public function testAddAndGetProviders()
    {
        $chainProvider = new ChainProvider;

        $bitlyProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Bitly\BitlyProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $bitlyProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('bitly'));

        $chainProvider->addProvider($bitlyProvider);

        $this->assertEquals($bitlyProvider, $chainProvider->getProvider('bitly'));
        $this->assertArrayHasKey('bitly', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('bitly'));
        $this->assertCount(1, $chainProvider->getProviders());

        $googleProvider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Google\GoogleProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $googleProvider
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('google'));

        $chainProvider->addProvider($googleProvider);

        $this->assertEquals($googleProvider, $chainProvider->getProvider('google'));
        $this->assertArrayHasKey('google', $chainProvider->getProviders());
        $this->assertTrue($chainProvider->hasProvider('google'));
        $this->assertCount(2, $chainProvider->getProviders());
    }
}
