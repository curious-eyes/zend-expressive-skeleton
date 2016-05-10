<?php

use Zend\Stdlib\ArrayUtils;

/**
 * Configuration files are loaded in a specific order. First ``global.php``, then ``*.global.php``.
 * then ``local.php`` and finally ``*.local.php``. This way local settings overwrite global settings.
 *
 * The configuration can be cached. This can be done by setting ``config_cache_enabled`` to ``true``.
 *
 * Obviously, if you use closures in your config you can't cache it.
 */

$cachedConfigKey = 'cache_app_config';

$mc = new Memcached();

// Try to load the cached config
if(!($config = $mc->get($cachedConfigKey))) {
    if ($mc->getResultCode() == Memcached::RES_NOTFOUND) {
        $config = [];
    }
    $confdir = __DIR__ . '/autoload';
    if ($handle = opendir($confdir)) {
        $pattern = '/(global|local)\.php$/';
        $files = [];
        while (false !== ($file = readdir($handle))) {
            if (preg_match($pattern, $file) === 1) {
                $fn = implode('.', array_reverse(explode('.', $file)));
                // syslog(LOG_INFO, $fn);
                $files[] = $fn;
            }
        }
        // syslog(LOG_INFO, '----');
        asort($files);
        foreach($files as $file) {
            $fn = implode('.', array_reverse(explode('.', $file)));
            // syslog(LOG_INFO, $fn);
            $config = ArrayUtils::merge($config, include $confdir.'/'.$fn);
        }
        closedir($handle);
    }

    // Cache config if enabled
    if (isset($config['config_cache_enabled']) && $config['config_cache_enabled'] === true) {
        $mc->set($cachedConfigKey, $config);
    }
}

// Return an ArrayObject so we can inject the config as a service in Aura.Di
// and still use array checks like ``is_array``.
return new ArrayObject($config, ArrayObject::ARRAY_AS_PROPS);
