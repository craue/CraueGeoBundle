<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

// needed for Symfony < 2.7
AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
