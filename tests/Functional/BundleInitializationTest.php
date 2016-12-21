<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\Loco\Tests\Functional;

use Nyholm\BundleTest\BaseBundleTestCase;
use Translation\PlatformAdapter\Loco\Bridge\Symfony\TranslationAdapterLocoBundle;
use Translation\PlatformAdapter\Loco\Loco;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function getBundleClass()
    {
        return TranslationAdapterLocoBundle::class;
    }

    public function testRegisterBundle()
    {
        $this->bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->has('php_translation.adapter.loco'));
        $service = $container->get('php_translation.adapter.loco');
        $this->assertInstanceOf(Loco::class, $service);
    }
}
