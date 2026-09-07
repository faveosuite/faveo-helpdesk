<?php

/*
 * This file is part of the Fetch library.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fetch\Test;

use Fetch\MIME;

/**
 * @package Fetch
 * @author  Robert Hafner <tedivm@tedivm.com>
 * @author  Sergey Linnik <linniksa@gmail.com>
 */
class MIMETest extends \PHPUnit_Framework_TestCase
{
    public function decodeData()
    {
        return array(
            array(null, null),
            array('Just text', 'Just text'),
            array('Keith Moore <moore@cs.utk.edu>', '=?US-ASCII?Q?Keith_Moore?= <moore@cs.utk.edu>'),
            array('Keld Jørn Simonsen <keld@dkuug.dk>', '=?ISO-8859-1?Q?Keld_J=F8rn_Simonsen?= <keld@dkuug.dk>'),
            array('André Pirard <PIRARD@vm1.ulg.ac.be>', '=?ISO-8859-1?Q?Andr=E9?= Pirard <PIRARD@vm1.ulg.ac.be>'),
            array(
                'If you can read this you understand the example.',
                '=?ISO-8859-1?B?SWYgeW91IGNhbiByZWFkIHRoaXMgeW8=?='
                . PHP_EOL .
                '=?ISO-8859-2?B?dSB1bmRlcnN0YW5kIHRoZSBleGFtcGxlLg==?='
            ),
        );
    }

    /**
     * @dataProvider decodeData
     *
     * @param string $expected
     * @param string $text
     * @param string $charset
     */
    public function testDecode($expected, $text, $charset = 'UTF-8')
    {
        self::assertSame($expected, MIME::decode($text, $charset));
    }
}
