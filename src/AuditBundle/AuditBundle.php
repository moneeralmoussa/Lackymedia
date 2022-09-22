<?php

namespace AuditBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use AuditBundle\DependencyInjection\UnauditedEntitiesCompilerPass;

class AuditBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new UnauditedEntitiesCompilerPass());
    }
}
