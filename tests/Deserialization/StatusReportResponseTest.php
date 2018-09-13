<?php
/*
 * This code is licensed under the MIT License.
 *
 * Copyright (c) 2018 appwilio <appwilio.com>
 * Copyright (c) 2018 JhaoDa <jhaoda@gmail.com>
 * Copyright (c) 2018 greabock <greabock17@gmail.com>
 * Copyright (c) 2018 Alexey Kopytko <alexey@kopytko.com>
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

use CdekSDK\Common\Reason;
use CdekSDK\Common\State;
use CdekSDK\Common\Status;
use CdekSDK\Responses\StatusReportResponse;
use Tests\CdekSDK\Fixtures\FixtureLoader;

/**
 * @covers \CdekSDK\Responses\StatusReportResponse
 * @covers \CdekSDK\Responses\Types\Message
 * @covers \CdekSDK\Common\Order
 * @covers \CdekSDK\Common\Status
 * @covers \CdekSDK\Common\Reason
 */
class StatusReportResponseTest extends TestCase
{
    public function test_it_reads_example_response()
    {
        $response = $this->getSerializer()->deserialize(FixtureLoader::load('StatusReportResponse.xml'), StatusReportResponse::class, 'xml');

        /** @var $response StatusReportResponse */
        $this->assertInstanceOf(StatusReportResponse::class, $response);

        $this->assertSame('2000-12-31', $response->getDateFirst()->format('Y-m-d'));
        $this->assertSame('2018-08-10', $response->getDateLast()->format('Y-m-d'));

        $this->assertCount(2, $response->getOrders());

        $order = $response->getOrders()[0];

        $this->assertSame('1000028000', $order->getDispatchNumber());
        $this->assertSame('2080965069', $order->getNumber());
        $this->assertSame('2018-04-06', $order->DeliveryDate->format('Y-m-d'));
        $this->assertSame('Руслан Альбертович', $order->getRecipientName());

        $this->assertInstanceOf(Reason::class, $order->getDelayReason());
        $this->assertEmpty($order->getDelayReason()->Code);

        $this->assertInstanceOf(Reason::class, $order->getReason());
        $this->assertEmpty($order->getReason()->Code);

        $status = $order->getStatus();
        $this->assertInstanceOf(Status::class, $status);

        $this->assertSame('2018-04-06', $status->getDate()->format('Y-m-d'));
        $this->assertSame(4, $status->getCode());
        $this->assertSame('Вручен', $status->getDescription());
        $this->assertSame(1081, $status->getCityCode());
        $this->assertSame('Нальчик', $status->getCityName());

        $states = $status->getStates();
        $firstState = reset($states);

        $this->assertInstanceOf(State::class, $firstState);

        $this->assertSame('2018-03-21', $firstState->Date->format('Y-m-d'));
        $this->assertSame(1, $firstState->Code);
        $this->assertSame('Создан', $firstState->Description);
        $this->assertSame('Москва', $firstState->CityName);
        $this->assertSame(44, $firstState->CityCode);

        $lastState = end($states);

        $this->assertInstanceOf(State::class, $lastState);

        $this->assertSame('2018-04-06', $lastState->Date->format('Y-m-d'));
        $this->assertSame(4, $lastState->Code);
        $this->assertSame('Вручен', $lastState->Description);
        $this->assertSame('Нальчик', $lastState->CityName);
        $this->assertSame(1081, $lastState->CityCode);
    }
}