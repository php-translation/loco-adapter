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

use FAPI\Localise\Exception\Domain\AssetConflictException;
use FAPI\Localise\Exception\Domain\NotFoundException;
use FAPI\Localise\LocoClient;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Translation\Common\Exception\StorageException;
use Translation\Common\Model\Message;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;

/**
 * Localize.biz.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Loco implements Storage, TransferableStorage
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

    /**
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $projectKey = $this->getApiKey($domain);
        $translation = $this->client->translations()->get($projectKey, $key, $locale)->getTranslation();
        $meta = [];

        return new Message($key, $domain, $locale, $translation, $meta);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Message $message)
    {
        $projectKey = $this->getApiKey($message->getDomain());
        $isNewAsset = true;
        try {
            // Create asset first
            $this->client->asset()->create($projectKey, $message->getKey());
            $this->client->translations()->create($projectKey, $message->getKey(), $message->getLocale(), $message->getTranslation());
        } catch (AssetConflictException $e) {
            // This is okey
            $isNewAsset = false;
        }

        if ($isNewAsset) {
            $this->client->translations()->create(
                $projectKey,
                $message->getKey(),
                $message->getLocale(),
                $message->getTranslation()
            );
        } else {
            try {
                $this->client->translations()->get(
                    $projectKey,
                    $message->getKey(),
                    $message->getLocale()
                );
            } catch (NotFoundException $e) {
                // Create only if not found.
                $this->client->translations()->create(
                    $projectKey,
                    $message->getKey(),
                    $message->getLocale(),
                    $message->getTranslation()
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(Message $message)
    {
        $projectKey = $this->getApiKey($message->getDomain());

        try {
            $this->client->translations()->create($projectKey, $message->getKey(), $message->getLocale(), $message->getTranslation());
        } catch (NotFoundException $e) {
            $this->create($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        $projectKey = $this->getApiKey($domain);
        $this->client->translations()->delete($projectKey, $key, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function export(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        $loader = new ArrayLoader();
        foreach ($this->domainToProjectId as $domain => $projectKey) {
            try {
                $data = $this->client->export()->locale(
                    $projectKey,
                    $locale,
                    'json',
                    ['format' => 'symfony']
                );
                $array = json_decode($data, true);
                $catalogue->addCatalogue(
                    $loader->load($array, $locale, $domain)
                );
            } catch (NotFoundException $e) {}
        }
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        foreach ($this->domainToProjectId as $domain => $projectKey) {
            $data = json_encode($catalogue->all($domain));
            $this->client->import()->import($projectKey, 'json', $data, ['locale' => $locale, 'async' => 1]);
        }
    }

    /**
     * @param string $domain
     *
     * @return string
     */
    private function getApiKey($domain)
    {
        if (isset($this->domainToProjectId[$domain])) {
            return $this->domainToProjectId[$domain];
        }

        throw new StorageException(sprintf('Api key for domain "%s" has not been configured.', $domain));
    }
}
