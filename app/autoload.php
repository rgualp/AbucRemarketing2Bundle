<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
//use Symfony\Component\ClassLoader\UniversalClassLoader;

//$loader_bundle = new UniversalClassLoader();
$loader = require __DIR__.'/../vendor/autoload.php';

$loader->add('Imagine',__DIR__.'/../vendor/imagine/lib');  
$loader->add('Avalanche',__DIR__.'/../vendor/bundles');

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';

    $loader->add('', __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
