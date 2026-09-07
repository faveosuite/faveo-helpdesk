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

/**
 * Authentication interface
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
interface AuthenticationInterface
{
    /**
     * Calls Bit.ly API to get an access token
     *
     * @return string
     */
    public function getAccessToken();
}
