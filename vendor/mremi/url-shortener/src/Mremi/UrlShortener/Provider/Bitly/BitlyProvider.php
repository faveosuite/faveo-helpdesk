<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider\Bitly;

use Guzzle\Http\Client;

use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Bit.ly provider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class BitlyProvider implements UrlShortenerProviderInterface
{
    /**
     * @var AuthenticationInterface
     */
    private $auth;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param AuthenticationInterface $auth    An authentication instance
     * @param array                   $options An array of options used to do the shorten/expand request
     */
    public function __construct(AuthenticationInterface $auth, array $options = array())
    {
        $this->auth    = $auth;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'bitly';
    }

    /**
     * {@inheritdoc}
     *
     * @param LinkInterface $link   A link instance
     * @param string        $domain The domain to use, optional (bit.ly | j.mp | bitly.com)
     *
     * @throws InvalidApiResponseException
     */
    public function shorten(LinkInterface $link, $domain = null)
    {
        $client = $this->createClient();

        $request = $client->get(sprintf('/v3/shorten?access_token=%s&longUrl=%s&domain=%s',
            $this->auth->getAccessToken(),
            urlencode($link->getLongUrl()),
            $domain
        ), array(), $this->options);

        $response = $this->validate($request->send()->getBody(true));

        $link->setShortUrl($response->data->url);
    }

    /**
     * {@inheritdoc}
     *
     * @param LinkInterface $link A link instance
     * @param string        $hash A Bit.ly hash
     *
     * @throws InvalidApiResponseException
     */
    public function expand(LinkInterface $link, $hash = null)
    {
        $client = $this->createClient();

        $request = $client->get(sprintf('/v3/expand?access_token=%s&shortUrl=%s&hash=%s',
            $this->auth->getAccessToken(),
            urlencode($link->getShortUrl()),
            $hash
        ), array(), $this->options);

        $response = $this->validate($request->send()->getBody(true));

        $link->setLongUrl($response->data->expand[0]->long_url);
    }

    /**
     * Creates a client.
     *
     * This method is mocked in unit tests in order to not make a real request,
     * so visibility must be protected or public.
     *
     * @return Client
     */
    protected function createClient()
    {
        return new Client('https://api-ssl.bitly.com');
    }

    /**
     * Validates the Bit.ly's response and returns it whether the status code is 200
     *
     * @param string $apiRawResponse
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Bit.ly response is probably mal-formed because cannot be json-decoded.');
        }

        if (!property_exists($response, 'status_code')) {
            throw new InvalidApiResponseException('Property "status_code" does not exist within Bit.ly response.');
        }

        if (200 !== $response->status_code) {
            throw new InvalidApiResponseException(sprintf('Bit.ly returned status code "%s" with message "%s"',
                $response->status_code,
                property_exists($response, 'status_txt') ? $response->status_txt : ''
            ));
        }

        return $response;
    }
}
