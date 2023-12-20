<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Provider;

/**
 * Chain provider class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class ChainProvider
{
    /**
     * @var array
     */
    private $providers = array();

    /**
     * Adds the given provider to the chain
     *
     * @param UrlShortenerProviderInterface $provider A provider instance
     */
    public function addProvider(UrlShortenerProviderInterface $provider)
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * Gets a provider by name
     *
     * @param string $name A provider name
     *
     * @return UrlShortenerProviderInterface
     *
     * @throws \RuntimeException If the provider does not exist
     */
    public function getProvider($name)
    {
        if (!$this->hasProvider($name)) {
            throw new \RuntimeException(sprintf('Unable to retrieve the provider named: "%s"', $name));
        }

        return $this->providers[$name];
    }

    /**
     * Gets the chain providers
     *
     * @return UrlShortenerProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns TRUE whether the given name identifies a configured provider
     *
     * @param string $name A provider name
     *
     * @return boolean
     */
    public function hasProvider($name)
    {
        return array_key_exists($name, $this->providers);
    }
}
