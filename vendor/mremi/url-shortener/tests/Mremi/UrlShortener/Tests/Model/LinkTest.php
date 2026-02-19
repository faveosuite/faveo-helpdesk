<?php

/*
 * This file is part of the Mremi\UrlShortener library.
 *
 * (c) Rémi Marseille <marseille.remi@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mremi\UrlShortener\Tests\Model;

use Mremi\UrlShortener\Model\Link;

/**
 * Tests Link class
 *
 * @author Rémi Marseille <marseille.remi@gmail.com>
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the createdAt property
     */
    public function testCreatedAt()
    {
        $link = new Link;

        $this->assertInstanceOf('\DateTime', $link->getCreatedAt());
    }
}
