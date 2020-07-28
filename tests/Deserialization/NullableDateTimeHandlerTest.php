<?php
/**
 * This code is licensed under the MIT License.
 *
 * Copyright (c) 2018 Appwilio (http://appwilio.com), greabock (https://github.com/greabock), JhaoDa (https://github.com/jhaoda)
 * Copyright (c) 2018 Alexey Kopytko <alexey@kopytko.com> and contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

declare(strict_types=1);

namespace Tests\CdekSDK\Deserialization;

use CdekSDK\Responses\StatusReportResponse;
use CdekSDK\Serialization\Exception\DeserializationException;
use CdekSDK\Serialization\NullableDateTimeHandler;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\XmlDeserializationVisitor;
use Tests\CdekSDK\Fixtures\DateTimeExample;

/**
 * @covers \CdekSDK\Serialization\NullableDateTimeHandler
 * @covers \CdekSDK\Serialization\Exception\DeserializationException
 * @covers \CdekSDK\Serialization\Serializer
 */
class NullableDateTimeHandlerTest extends TestCase
{
    public function test_unserialize_missing_date()
    {
        $result = $this->getSerializer()->deserialize('<DateTimeExample Date="2019-06-01" DateTime="2019-06-02T22:11:41+00:00" />', DateTimeExample::class, 'xml');

        /** @var $result StatusReportResponse */
        $this->assertInstanceOf(DateTimeExample::class, $result);
        $this->assertNull($result->DateMixed);
    }

    public function test_unserialize_empty_date()
    {
        $result = $this->getSerializer()->deserialize('<DateTimeExample Date="" DateTime="" />', DateTimeExample::class, 'xml');

        /** @var $result StatusReportResponse */
        $this->assertInstanceOf(DateTimeExample::class, $result);
        $this->assertNull($result->Date);
        $this->assertNull($result->DateTime);
        $this->assertNull($result->DateMixed);
    }

    public function test_unserialize_date_with_time()
    {
        $result = $this->getSerializer()->deserialize('<DateTimeExample DateMixed="2019-06-03T23:22:15+00:00" />', DateTimeExample::class, 'xml');

        /** @var $result StatusReportResponse */
        $this->assertInstanceOf(DateTimeExample::class, $result);
        $this->assertNotNull($result->DateMixed);

        $this->assertSame('2019-06-03 23:22:15', $result->DateMixed->format('Y-m-d H:i:s'));
    }

    public function test_unserialize_date_without_time()
    {
        $result = $this->getSerializer()->deserialize('<DateTimeExample DateMixed="2019-06-03" />', DateTimeExample::class, 'xml');

        /** @var $result StatusReportResponse */
        $this->assertInstanceOf(DateTimeExample::class, $result);
        $this->assertNotNull($result->DateMixed);

        $this->assertSame('2019-06-03 00:00:00', $result->DateMixed->format('Y-m-d H:i:s'));
    }

    public function test_fails_on_invalid_date()
    {
        $this->expectException(RuntimeException::class);
        $this->expectException(\JMS\Serializer\Exception\RuntimeException::class);

        $this->getSerializer()->deserialize('<DateTimeExample DateTime="00:00:00" />', DateTimeExample::class, 'xml');
    }

    public function test_fails_on_unexpected_date_format_with_serializer_exception()
    {
        $this->expectException(\JMS\Serializer\Exception\RuntimeException::class);
        $this->expectExceptionMessageRegExp('/^Failed to deserialize Date="2000-01-01 00:00:00": .* expected.*format/');

        $this->getSerializer()->deserialize('<DateTimeExample Date="2000-01-01 00:00:00" />', DateTimeExample::class, 'xml');
    }

    public function test_fails_on_unexpected_date_format_with_our_exception()
    {
        $this->expectException(DeserializationException::class);
        $this->expectExceptionMessageRegExp('/^Failed to deserialize Date="2001-01-01 00:00:01": .* expected.*format/');

        $this->getSerializer()->deserialize('<DateTimeExample Date="2001-01-01 00:00:01" />', DateTimeExample::class, 'xml');
    }

    public function test_do_not_resets_time_if_not_needed()
    {
        $handler = new NullableDateTimeHandler();
        $visitor = new XmlDeserializationVisitor();
        $sxe = new \SimpleXMLElement('<date>2000-01-01_</date>');

        if (\date('H:i:s') === '00:00:00') {
            \sleep(1);
        }

        $date = $handler->deserializeDateTimeImmutableFromXml($visitor, $sxe, [
            'name'   => \DateTimeImmutable::class,
            'params' => [
                0 => 'Y-m-d\\TH:i:sP',
                1 => '',
                //2 => 'Y-m-d\\TH:i:sP',
                3 => 'Y-m-d_',
            ],
        ]);

        $this->assertNotSame('2000-01-01 00:00:00', $date->format('Y-m-d H:i:s'));
    }

    public function test_proxy_calls_the_parent()
    {
        $handler = new NullableDateTimeHandler();
        $this->assertSame('P1D', $handler->format(new \DateInterval('P1D')));
    }
}
