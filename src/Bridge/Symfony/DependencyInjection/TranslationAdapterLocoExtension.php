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

use FAPI\Localise\HttpClientConfigurator;
use FAPI\Localise\LocoClient;
use FAPI\Localise\RequestBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
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

        $requestBuilder = (new Definition(RequestBuilder::class))
            ->addArgument(new Reference($config['httplug_message_factory']));

        $clientConfigurator = (new Definition(HttpClientConfigurator::class))
            ->addArgument(new Reference($config['httplug_client']))
            ->addArgument(new Reference($config['httplug_uri_factory']));

        $apiDef = $container->register('php_translation.adapter.loco.raw');
        $apiDef->setClass(LocoClient::class)
            ->setFactory([LocoClient::class, 'configure'])
            ->addArgument($clientConfigurator)
            ->addArgument(null)
            ->addArgument($requestBuilder);

        $adapterDef = $container->register('php_translation.adapter.loco');
        $adapterDef
            ->setClass(Loco::class)
            ->addArgument($apiDef)
            ->addArgument($domainToProjectMap);
    }
}
