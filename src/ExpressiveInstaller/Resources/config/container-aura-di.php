<?php

use Aura\Di\ContainerBuilder;

// Load configuration
if (isset($_SERVER['APPLICATION_ID']) && !empty($_SERVER['APPLICATION_ID'])) {
    // for Google App Engine
    $config = require __DIR__ . '/config-gae.php';
}else{
    $config = require __DIR__ . '/config.php';
}

// Build container
$builder = new ContainerBuilder();
$container = $builder->newInstance();

// Inject config
$container->set('config', $config);

// Inject factories
foreach ($config['dependencies']['factories'] as $name => $object) {
    $container->set($object, $container->lazyNew($object));
    $container->set($name, $container->lazyGetCall($object, '__invoke', $container));
}

// Inject invokables
foreach ($config['dependencies']['invokables'] as $name => $object) {
    $container->set($name, $container->lazyNew($object));
}

return $container;
