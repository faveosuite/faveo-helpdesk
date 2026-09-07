<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Model;

use Mremi\UrlShortener\Provider\ChainProvider;

/**
 * Link manager class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class LinkManager implements LinkManagerInterface
{
    /**
     * @var ChainProvider
     */
    protected $chainProvider;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor
     *
     * @param ChainProvider $chainProvider A chain provider instance
     * @param string        $class         The Link class namespace, optional
     */
    public function __construct(ChainProvider $chainProvider, $class = 'Mremi\UrlShortener\Model\Link')
    {
        $this->chainProvider = $chainProvider;
        $this->class         = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return new $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProviderAndShortUrl($providerName, $shortUrl)
    {
        $provider = $this->chainProvider->getProvider($providerName);

        $link = $this->create();
        $link->setProviderName($provider->getName());
        $link->setShortUrl($shortUrl);

        $provider->expand($link);

        return $link;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByProviderAndLongUrl($providerName, $longUrl)
    {
        $provider = $this->chainProvider->getProvider($providerName);

        $link = $this->create();
        $link->setProviderName($provider->getName());
        $link->setLongUrl($longUrl);

        $provider->shorten($link);

        return $link;
    }
}
