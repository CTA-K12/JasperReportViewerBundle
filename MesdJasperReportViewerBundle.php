<?php

namespace Mesd\Jasper\ReportViewerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;

class MesdJasperReportViewerBundle extends Bundle
{
    public function registerCommands(Application $application){
        parent::registerCommands($application);
    }

    public function build(ContainerBuilder $container) {
        parent::build($container);
    }
}