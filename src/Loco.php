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
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Yaml\Yaml;
use Translation\Common\Exception\StorageException;
use Translation\Common\Model\Message;
use Translation\Common\Storage;
use Translation\Common\TransferableStorage;
use Translation\PlatformAdapter\Loco\Model\LocoProject;
use Translation\SymfonyStorage\XliffConverter;

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
     * @var LocoProject[]
     */
    private $projectsByDomain = [];

    /**
     * @param LocoClient    $client
     * @param LocoProject[] $projectsByDomain
     */
    public function __construct(LocoClient $client, array $projectsByDomain)
    {
        $this->client = $client;
        $this->projectsByDomain = $projectsByDomain;
    }

    /**
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $projectKey = $this->getProject($domain);

        try {
            $translation = $this->client->translations()->get($projectKey, $key, $locale)->getTranslation();
        } catch (\FAPI\Localise\Exception $e) {
            return null;
        }
        $meta = [];

        return new Message($key, $domain, $locale, $translation, $meta);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Message $message)
    {
        $project = $this->getProject($message->getDomain());
        $isNewAsset = true;

        try {
            // Create asset first
            $this->client->asset()->create($project->getApiKey(), $message->getKey());
        } catch (AssetConflictException $e) {
            // This is okey
            $isNewAsset = false;
        }

        $translation = $message->getTranslation();

        // translation is the same as the key, so we will set it to empty string
        // as it was not translated and stats on loco will be unaffected
        if ($message->getKey() === $message->getTranslation()) {
            $translation = '';
        }

        if ($isNewAsset) {
            $this->client->translations()->create(
                $project->getApiKey(),
                $message->getKey(),
                $message->getLocale(),
                $translation
            );
        } else {
            try {
                $this->client->translations()->get(
                    $project->getApiKey(),
                    $message->getKey(),
                    $message->getLocale()
                );
            } catch (NotFoundException $e) {
                // Create only if not found.
                $this->client->translations()->create(
                    $project->getApiKey(),
                    $message->getKey(),
                    $message->getLocale(),
                    $translation
                );
            }
        }

        $this->client->asset()->tag($project->getApiKey(), $message->getKey(), $message->getDomain());

        if (!empty($message->getMeta('parameters'))) {
            // Pretty print the Meta field via YAML export
            $dump = Yaml::dump(['parameters' => $message->getMeta('parameters')], 4, 5);
            $dump = str_replace("     -\n", '', $dump);
            $dump = str_replace('     ', "\xC2\xA0", $dump); // no break space

            $this->client->asset()->patch(
                $project->getApiKey(),
                $message->getKey(),
                null,
                null,
                null,
                $dump
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(Message $message)
    {
        $project = $this->getProject($message->getDomain());
        try {
            $this->client->translations()->create($project->getApiKey(), $message->getKey(), $message->getLocale(), $message->getTranslation());
        } catch (NotFoundException $e) {
            $this->create($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($locale, $domain, $key)
    {
        $project = $this->getProject($domain);

        $this->client->translations()->delete($project->getApiKey(), $key, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function export(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        foreach ($this->projectsByDomain as $domain => $project) {
            try {
                $data = $this->client->export()->locale(
                    $project->getApiKey(),
                    $locale,
                    'xliff',
                    ['format' => 'symfony', 'status' => 'translated', 'index' => $project->getIndexParameter()]
                );

                $catalogue->addCatalogue(XliffConverter::contentToCatalogue($data, $locale, $domain));
            } catch (NotFoundException $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        foreach ($this->projectsByDomain as $domain => $project) {
            $data = XliffConverter::catalogueToContent($catalogue, $domain);
            $this->client->import()->import(
                $project->getApiKey(),
                'xliff',
                $data,
                ['locale' => $locale, 'async' => 1, 'index' => $project->getIndexParameter()]
            );
        }
    }

    private function getProject($domain): LocoProject
    {
        if (isset($this->projectsByDomain[$domain])) {
            return $this->projectsByDomain[$domain];
        }

        throw new StorageException(sprintf('Project for "%s" domain was not found.', $domain));
    }
}
