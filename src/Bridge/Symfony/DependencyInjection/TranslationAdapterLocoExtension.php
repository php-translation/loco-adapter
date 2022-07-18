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
use Translation\PlatformAdapter\Loco\Model\LocoProject;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class TranslationAdapterLocoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $projects = [];
        $globalIndexParameter = $config['index_parameter'];
        foreach ($config['projects'] as $name => $data) {
            if (empty($data['index_parameter'])) {
                $data['index_parameter'] = $globalIndexParameter;
            }

            $projectDefinition = new Definition(LocoProject::class);
            $projectDefinition
                ->addArgument($name)
                ->addArgument($data);
            $projects[] = $projectDefinition;
        }

        $requestBuilder = (new Definition(RequestBuilder::class))
            ->addArgument(empty($config['httplug_message_factory']) ? null : new Reference($config['httplug_message_factory']));

        $clientConfigurator = (new Definition(HttpClientConfigurator::class))
            ->addArgument(empty($config['httplug_client']) ? null : new Reference($config['httplug_client']))
            ->addArgument(empty($config['httplug_uri_factory']) ? null : new Reference($config['httplug_uri_factory']));

        $apiDef = $container->register('php_translation.adapter.loco.raw');
        $apiDef->setClass(LocoClient::class)
            ->setFactory([LocoClient::class, 'configure'])
            ->setPublic(true)
            ->addArgument($clientConfigurator)
            ->addArgument(null)
            ->addArgument($requestBuilder);

        $adapterDef = $container->register('php_translation.adapter.loco');
        $adapterDef
            ->setClass(Loco::class)
            ->setPublic(true)
            ->addArgument($apiDef)
            ->addArgument($projects);
    }
}
