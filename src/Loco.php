<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\Loco;

use APIPHP\Localise\LocoClient;
use Translation\Common\Model\Message;
use Translation\Common\Storage;

/**
 * Localize.biz.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Loco implements Storage
{
    /**
     * @var LocoClient
     */
    private $client;

    /**
     * @var array
     */
    private $domainToProjectId = [];

    /**
     * @param LocoClient $client
     * @param array      $domainToProjectId
     */
    public function __construct(LocoClient $client, array $domainToProjectId)
    {
        $this->client = $client;
        $this->domainToProjectId = $domainToProjectId;
    }

    public function get($locale, $domain, $key)
    {
        $translation = $this->client->translations()->show($this->domainToProjectId[$domain], $key, $locale)->getTranslation();
        $meta = [];

        return new Message($key, $domain, $locale, $translation, $meta);
    }

    public function update(Message $message)
    {
        $this->client->translations()->create($this->domainToProjectId[$message->getDomain()], $message->getKey(), $message->getLocale(), $message->getTranslation());
    }

    public function delete($locale, $domain, $key)
    {
        $this->client->translations()->show($this->domainToProjectId[$domain], $key, $locale);
    }
}
