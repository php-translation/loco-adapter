<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\Loco\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Translation\PlatformAdapter\Loco\Model\LocoProject;

class LocoProjectTest extends TestCase
{
    /**
     * @var LocoProject
     */
    private $locoProject;

    protected function setUp(): void
    {
        $this->locoProject = new LocoProject('domain', ['api_key' => 'test', 'status' => '!untranslated,!rejected', 'index_parameter' => 'text']);
    }

    public function testWithEmptyConfig()
    {
        $locoProject = new LocoProject('domain', []);
        $this->assertNull($locoProject->getIndexParameter());
        $this->assertNull($locoProject->getApiKey());
    }

    public function testGetName()
    {
        $this->assertEquals('domain', $this->locoProject->getName());
    }

    public function testGetApiKey()
    {
        $this->assertEquals('test', $this->locoProject->getApiKey());
    }

    public function testGetStatus()
    {
        $this->assertEquals('!untranslated,!rejected', $this->locoProject->getStatus());
    }

    public function testGetIndexParameter()
    {
        $this->assertEquals('text', $this->locoProject->getIndexParameter());
    }
}
