<?php

declare(strict_types=1);

namespace Acme\CodeQualityBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class AcmeCodeQualityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load('services.yaml');

        // Set bundle config directory as parameter
        $container->setParameter(
            'acme_code_quality.config_dir',
            __DIR__ . '/../../config/quality-tools'
        );
    }

    public function getAlias(): string
    {
        return 'acme_code_quality';
    }
}
