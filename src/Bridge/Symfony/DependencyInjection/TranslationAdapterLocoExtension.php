<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\PlatformAdapter\Loco\Bridge\Symfony\DependencyInjection;

use APIPHP\Localise\LocoClient;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Translation\PlatformAdapter\Loco\Loco;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class TranslationAdapterLocoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container);
        $config = $this->processConfiguration($configuration, $configs);

        $domainToProjectMap = [];
        foreach ($config['projects'] as $domain => $data) {
            if (empty($data['domains'])) {
                $domainToProjectMap[$domain] = $data['api_key'];
            } else {
                foreach ($data['domains'] as $d) {
                    $domainToProjectMap[$d] = $data['api_key'];
                }
            }
        }

        // TODO Make sure we can inject the configure HTTP client and request builder into LocoClient
        $apiDef = $container->register('php_translation.adapter.loco.raw');
        $apiDef->setClass(LocoClient::class);

        $adapterDef = $container->register('php_translation.adapter.loco');
        $adapterDef
            ->setClass(Loco::class)
            ->addArgument($apiDef)
            ->addArgument($domainToProjectMap);
    }
}
