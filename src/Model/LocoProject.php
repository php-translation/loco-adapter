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
     * @param string $name
     * @param array  $config
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->apiKey = $config['api_key'] ?? null;
        $this->indexParameter = $config['index_parameter'] ?? null;
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
}
