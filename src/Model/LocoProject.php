<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\Loco\Model;

/**
 * Represents a project from loco.
 */
final class LocoProject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $indexParameter;

    /**
     * @var array
     */
    private $domains;

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->apiKey = $config['api_key'] ?? null;
        $this->status = $config['status'] ?? null;
        $this->indexParameter = $config['index_parameter'] ?? null;
        $this->domains = empty($config['domains']) ? [$name] : $config['domains'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getIndexParameter(): ?string
    {
        return $this->indexParameter;
    }

    public function getDomains(): array
    {
        return $this->domains;
    }

    public function hasDomain(string $domain): bool
    {
        return \in_array($domain, $this->domains);
    }

    /**
     * Returning true means that domains are expected to be managed with tags.
     */
    public function isMultiDomain(): bool
    {
        return \count($this->domains) > 1;
    }
}
