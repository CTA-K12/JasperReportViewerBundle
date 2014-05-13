<?php

namespace MESD\Jasper\ReportViewerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('mesd_jasper_report_viewer')
            ->addDefaultsIfNotSet()
            ->children()
            ->end()
        ;
        return $builder;
    }
}