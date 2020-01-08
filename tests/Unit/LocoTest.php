<?php

declare(strict_types=1);

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\Loco\Tests\Unit;

use FAPI\Localise\Hydrator\Hydrator;
use FAPI\Localise\LocoClient;
use Http\Client\HttpClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Translation\PlatformAdapter\Loco\Loco;
use Translation\PlatformAdapter\Loco\Model\LocoProject;

class LocoTest extends TestCase
{
    /**
     * @var LocoClient
     */
    private $client;

    /**
     * @var HttpClient|MockObject
     */
    private $httpClient;

    /**
     * @var Hydrator|MockObject
     */
    private $hydrator;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->hydrator = $this->createMock(Hydrator::class);
        $this->client = new LocoClient($this->httpClient, $this->hydrator);
    }

    public function testOverridesTheDefaultLocaleWhenUsingTranslationKeys(): void
    {
        $locoProject = new LocoProject('main', ['api_key' => 'FooBar', 'index_parameter' => 'id']);
        $loco = new Loco($this->client, [$locoProject]);

        $catalogue = new MessageCatalogue('nl', []);

        $response = $this->createMock(ResponseInterface::class);
        $this->httpClient
            ->method('sendRequest')
            ->with(
                $this->callback(
                    // Capture the request body so we can make assertions on it later on
                    function (RequestInterface $argument) use (&$body): bool {
                        $body = $argument->getBody()->__toString();

                        return true;
                    }
                )
            )
            ->willReturn($response);

        $stream = $this->createMock(StreamInterface::class);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn('{}');
        $response->method('getStatusCode')->willReturn(201);

        $loco->import($catalogue);

        $this->assertStringContainsString(
            '<xliff xmlns="urn:oasis:names:tc:xliff:document:2.0" version="2.0" srcLang="en" trgLang="nl">',
            $body
        );
    }
}
