<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider\Google;

use Guzzle\Http\Client;

use Mremi\UrlShortener\Exception\InvalidApiResponseException;
use Mremi\UrlShortener\Model\LinkInterface;
use Mremi\UrlShortener\Provider\UrlShortenerProviderInterface;

/**
 * Google provider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class GoogleProvider implements UrlShortenerProviderInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * @param string $apiKey  A Google API key, optional
     * @param array  $options An array of options used to do the shorten/expand request
     */
    public function __construct($apiKey = null, array $options = array())
    {
        $this->apiKey  = $apiKey;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google';
    }

    /**
     * {@inheritdoc}
     */
    public function shorten(LinkInterface $link)
    {
        $client = $this->createClient();

        $request = $client->post($this->getUri(), array(
            'Content-Type' => 'application/json'
        ), json_encode(array(
            'longUrl' => $link->getLongUrl(),
        )), $this->options);

        $response = $this->validate($request->send()->getBody(true));

        $link->setShortUrl($response->id);
    }

    /**
     * {@inheritdoc}
     */
    public function expand(LinkInterface $link)
    {
        $client = $this->createClient();

        $request = $client->get($this->getUri(array(
            'shortUrl' => $link->getShortUrl(),
        )), array(), $this->options);

        $response = $this->validate($request->send()->getBody(true), true);

        $link->setLongUrl($response->longUrl);
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
        return new Client('https://www.googleapis.com/urlshortener/v1/url');
    }

    /**
     * Gets the URI
     *
     * @param array $parameters An array of parameters, optional
     *
     * @return null|string
     */
    private function getUri(array $parameters = array())
    {
        if ($this->apiKey) {
            $parameters = array_merge($parameters, array('key' => $this->apiKey));
        }

        if (0 === count($parameters)) {
            return null;
        }

        return sprintf('?%s', http_build_query($parameters));
    }

    /**
     * Validates the Google's response and returns it whether the status code is 200
     *
     * @param string  $apiRawResponse An API response, as it returned
     * @param boolean $checkStatus    TRUE whether the status code has to be checked, default FALSE
     *
     * @return object
     *
     * @throws InvalidApiResponseException
     */
    private function validate($apiRawResponse, $checkStatus = false)
    {
        $response = json_decode($apiRawResponse);

        if (null === $response) {
            throw new InvalidApiResponseException('Google response is probably mal-formed because cannot be json-decoded.');
        }

        if (property_exists($response, 'error')) {
            throw new InvalidApiResponseException(sprintf('Google returned status code "%s" with message "%s".',
                property_exists($response->error, 'code') ? $response->error->code : '',
                property_exists($response->error, 'message') ? $response->error->message : ''
            ));
        }

        if (!property_exists($response, 'id')) {
            throw new InvalidApiResponseException('Property "id" does not exist within Google response.');
        }

        if (!property_exists($response, 'longUrl')) {
            throw new InvalidApiResponseException('Property "longUrl" does not exist within Google response.');
        }

        if (!$checkStatus) {
            return $response;
        }

        if (!property_exists($response, 'status')) {
            throw new InvalidApiResponseException('Property "status" does not exist within Google response.');
        }

        if ('OK' !== $response->status) {
            throw new InvalidApiResponseException(sprintf('Google returned status code "%s".', $response->status));
        }

        return $response;
    }
}
