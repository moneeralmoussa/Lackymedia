<?php

namespace AuditBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UnauditedEntitiesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $datadog_config = $container->getExtensionConfig('data_dog_audit');
        $entities = $datadog_config[0];
        $auditSubscriber = $container->getDefinition('datadog.event_subscriber.audit');
        $auditSubscriber->addMethodCall('addUnauditedEntities', $entities);
    }
}
