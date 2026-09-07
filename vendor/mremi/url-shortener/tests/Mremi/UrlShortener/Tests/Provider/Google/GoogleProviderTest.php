<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Provider\Google;

use Mremi\UrlShortener\Provider\Google\GoogleProvider;

/**
 * Tests GoogleProvider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class GoogleProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object
     */
    private $provider;

    /**
     * Tests the getUri method with no API key and no parameters
     */
    public function testGetUriWithNoApiKeyAndNoParameters()
    {
        $method = new \ReflectionMethod($this->provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($this->provider);

        $this->assertNull($uri);
    }

    /**
     * Tests the getUri method with no API key and some parameters
     */
    public function testGetUriWithNoApiKeyAndSomeParameters()
    {
        $method = new \ReflectionMethod($this->provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($this->provider, array('foo' => 'bar'));

        $this->assertEquals('?foo=bar', $uri);
    }

    /**
     * Tests the getUri method with API key and no parameters
     */
    public function testGetUriWithApiKeyAndNoParameters()
    {
        $provider = new GoogleProvider('secret');

        $method = new \ReflectionMethod($provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($provider);

        $this->assertEquals('?key=secret', $uri);
    }

    /**
     * Tests the getUri method with API key and some parameters
     */
    public function testGetUriWithApiKeyAndSomeParameters()
    {
        $provider = new GoogleProvider('secret');

        $method = new \ReflectionMethod($provider, 'getUri');
        $method->setAccessible(true);

        $uri = $method->invoke($provider, array('foo' => 'bar'));

        $this->assertEquals('?foo=bar&key=secret', $uri);
    }

    /**
     * Tests the shorten method throws exception if Google returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google response is probably mal-formed because cannot be json-decoded.
     */
    public function testShortenThrowsExceptionIfResponseApiIsString()
    {
        $this->mockClient($this->getMockResponseAsString(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testShortenThrowsExceptionIfApiResponseIsError()
    {
        $this->mockClient($this->getMockResponseWithError(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoId()
    {
        $this->mockClient($this->getMockResponseWithNoId(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testShortenThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $this->mockClient($this->getMockResponseWithNoLongUrl(), 'post');

        $this->provider->shorten($this->getBaseMockLink());
    }

    /**
     * Tests the shorten method with a valid Google's response
     */
    public function testShortenWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $link = $this->getMockLongLink();
        $link
            ->expects($this->once())
            ->method('setShortUrl')
            ->with($this->equalTo('http://goo.gl/fbsS'));

        $this->mockClient($response, 'post');

        $this->provider->shorten($link);
    }

    /**
     * Tests the expand method throws exception if Google returns a string
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google response is probably mal-formed because cannot be json-decoded.
     */
    public function testExpandThrowsExceptionIfResponseApiIsString()
    {
        $this->mockClient($this->getMockResponseAsString(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns an error response
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "400" with message "Required"
     */
    public function testExpandThrowsExceptionIfApiResponseIsError()
    {
        $this->mockClient($this->getMockResponseWithError(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no id
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "id" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoId()
    {
        $this->mockClient($this->getMockResponseWithNoId(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no longUrl
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "longUrl" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoLongUrl()
    {
        $this->mockClient($this->getMockResponseWithNoLongUrl(), 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns a response with no status
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Property "status" does not exist within Google response.
     */
    public function testExpandThrowsExceptionIfApiResponseHasNoStatus()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $this->mockClient($response, 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method throws exception if Google returns an invalid status code
     *
     * @expectedException        \Mremi\UrlShortener\Exception\InvalidApiResponseException
     * @expectedExceptionMessage Google returned status code "KO".
     */
    public function testExpandThrowsExceptionIfApiResponseHasInvalidStatusCode()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/",
 "status": "KO"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $this->mockClient($response, 'get');

        $this->provider->expand($this->getBaseMockLink());
    }

    /**
     * Tests the expand method with a valid Google's response
     */
    public function testExpandWithValidApiResponse()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/",
 "status": "OK"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        $link = $this->getMockShortLink();
        $link
            ->expects($this->once())
            ->method('setLongUrl')
            ->with($this->equalTo('http://www.google.com/'));

        $this->mockClient($response, 'get');

        $this->provider->expand($link);
    }

    /**
     * Initializes the provider
     */
    protected function setUp()
    {
        $this->provider = $this->getMockBuilder('Mremi\UrlShortener\Provider\Google\GoogleProvider')
            ->setMethods(array('createClient'))
            ->getMock();
    }

    /**
     * Cleanups the provider
     */
    protected function tearDown()
    {
        unset($this->provider);
    }

    /**
     * Gets a mocked response
     *
     * @return object
     */
    private function getBaseMockResponse()
    {
        return $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Returns an invalid response string
     *
     * @return object
     */
    private function getMockResponseAsString()
    {
        $response = $this->getBaseMockResponse();

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('foo'));

        return $response;
    }

    /**
     * Returns a response object with "error" node
     *
     * @return object
     */
    private function getMockResponseWithError()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "error": {
  "errors": [
   {
    "domain": "global",
    "reason": "required",
    "message": "Required",
    "locationType": "parameter",
    "location": "resource.longUrl"
   }
  ],
  "code": 400,
  "message": "Required"
 }
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Returns a response object with no "id" node
     *
     * @return object
     */
    private function getMockResponseWithNoId()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "longUrl": "http://www.google.com/"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Returns a response object with no "longUrl" node
     *
     * @return object
     */
    private function getMockResponseWithNoLongUrl()
    {
        $response = $this->getBaseMockResponse();

        $apiRawResponse = <<<JSON
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS"
}
JSON;

        $response
            ->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($apiRawResponse));

        return $response;
    }

    /**
     * Mocks the client
     *
     * @param object $response      A mocked response
     * @param string $requestMethod A request method (get|post)
     */
    private function mockClient($response, $requestMethod)
    {
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $client = $this->getMock('Guzzle\Http\ClientInterface');
        $client
            ->expects($this->once())
            ->method($requestMethod)
            ->will($this->returnValue($request));

        $this->provider
            ->expects($this->once())
            ->method('createClient')
            ->will($this->returnValue($client));
    }

    /**
     * Gets mock of link
     *
     * @return object
     */
    private function getBaseMockLink()
    {
        return $this->getMock('Mremi\UrlShortener\Model\LinkInterface');
    }

    /**
     * Gets mock of short link
     *
     * @return object
     */
    private function getMockShortLink()
    {
        $link = $this->getBaseMockLink();

        $link
            ->expects($this->once())
            ->method('getShortUrl')
            ->will($this->returnValue('http://goo.gl/fbsS'));

        return $link;
    }

    /**
     * Gets mock of long link
     *
     * @return object
     */
    private function getMockLongLink()
    {
        $link = $this->getBaseMockLink();

        $link
            ->expects($this->once())
            ->method('getLongUrl')
            ->will($this->returnValue('http://www.google.com/'));

        return $link;
    }
}
