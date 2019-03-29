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
use Translation\Common\Model\MessageInterface;
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
    private $projects = [];

    /**
     * @param LocoClient    $client
     * @param LocoProject[] $projects
     */
    public function __construct(LocoClient $client, array $projects)
    {
        $this->client = $client;
        $this->projects = $projects;
    }

    /**
     * {@inheritdoc}
     */
    public function get($locale, $domain, $key)
    {
        $project = $this->getProject($domain);

        try {
            $translation = $this->client->translations()->get($project->getApiKey(), $key, $locale)->getTranslation();
        } catch (\FAPI\Localise\Exception $e) {
            return null;
        }
        $meta = [];

        return new Message($key, $domain, $locale, $translation, $meta);
    }

    /**
     * {@inheritdoc}
     */
    public function create(MessageInterface $message)
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
    public function update(MessageInterface $message)
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
        foreach ($this->projects as $project) {
            foreach ($project->getDomains() as $domain) {
                try {
                    $params = [
                        'format' => 'symfony',
                        'status' => '!untranslated,!rejected',
                        'index' => $project->getIndexParameter(),
                    ];

                    if ($project->isMultiDomain()) {
                        $params['filter'] = $domain;
                    }

                    $data = $this->client->export()->locale($project->getApiKey(), $locale, 'xliff', $params);
                    $catalogue->addCatalogue(XliffConverter::contentToCatalogue($data, $locale, $domain));
                } catch (NotFoundException $e) {
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function import(MessageCatalogueInterface $catalogue)
    {
        $locale = $catalogue->getLocale();
        foreach ($this->projects as $project) {
            foreach ($project->getDomains() as $domain) {
                $data = XliffConverter::catalogueToContent($catalogue, $domain);
                $params = [
                    'locale' => $locale,
                    'async' => 1,
                    'index' => $project->getIndexParameter(),
                ];

                if ($project->isMultiDomain()) {
                    $params['tag-all'] = $domain;
                }

                $this->client->import()->import($project->getApiKey(), 'xliff', $data, $params);
            }
        }
    }

    private function getProject($domain): LocoProject
    {
        foreach ($this->projects as $project) {
            if ($project->hasDomain($domain)) {
                return $project;
            }
        }

        throw new StorageException(sprintf('Project for "%s" domain was not found.', $domain));
    }
}
