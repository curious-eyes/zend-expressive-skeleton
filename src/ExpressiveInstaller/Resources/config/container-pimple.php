<?php

use Xtreamwayz\Pimple\Container;

// Load configuration
if (isset($_SERVER['APPLICATION_ID']) && !empty($_SERVER['APPLICATION_ID'])) {
    // for Google App Engine
    $config = require __DIR__ . '/config-gae.php';
}else{
    $config = require __DIR__ . '/config.php';
}

// Build container
$container = new Container();

// Inject config
$container['config'] = $config;

// Inject factories
foreach ($config['dependencies']['factories'] as $name => $object) {
    $container[$name] = function ($c) use ($object, $name) {
        if ($c->has($object)) {
            $factory = $c->get($object);
        } else {
            $factory = new $object();
            $c[$object] = $factory;
        }

        return $factory($c, $name);
    };
}
// Inject invokables
foreach ($config['dependencies']['invokables'] as $name => $object) {
    $container[$name] = function ($c) use ($object) {
        return new $object();
    };
}

return $container;
