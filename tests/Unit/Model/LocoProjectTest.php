<?php

namespace Translation\PlatformAdapter\Loco\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Translation\PlatformAdapter\Loco\Model\LocoProject;

class LocoProjectTest extends TestCase
{
    /**
     * @var LocoProject
     */
    private $locoProject;

    public function setUp()
    {
        $this->locoProject = new LocoProject('domain', ['api_key' => 'test', 'index_parameter' => 'text']);
    }

    public function testGetName()
    {
        $this->assertEquals('domain', $this->locoProject->getName());
    }

    public function testGetApiKey()
    {
        $this->assertEquals('test', $this->locoProject->getApiKey());
    }

    public function testGetIndexParameter()
    {
        $this->assertEquals('text', $this->locoProject->getIndexParameter());
    }

}
