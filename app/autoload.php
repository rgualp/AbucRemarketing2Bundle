<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('Imagine',__DIR__.'/../vendor/imagine/lib');
$loader->add('Avalanche',__DIR__.'/../vendor/bundles');
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
