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
    private $indexParameter;

    /**
     * @var array
     */
    private $domains;

    /**
     * @param string $name
     * @param array  $config
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->apiKey = $config['api_key'] ?? null;
        $this->indexParameter = $config['index_parameter'] ?? null;
        $this->domains = empty($config['domains']) ? [$name] : $config['domains'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string|null
     */
    public function getIndexParameter()
    {
        return $this->indexParameter;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param string $domain
     *
     * @return bool
     */
    public function hasDomain(string $domain)
    {
        return in_array($domain, $this->domains);
    }

    /**
     * @return bool Returning true means that domains are expected to be managed with tags.
     */
    public function isMultiDomain()
    {
        return count($this->domains) > 1;
    }
}
