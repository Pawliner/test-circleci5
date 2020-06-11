<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Composer\Autoload;

/**
 * ClassLoader implements a PSR-0, PSR-4 and classmap class loader.
 *
 *     $loader = new \Composer\Autoload\ClassLoader();
 *
 *     // register classes with namespaces
 *     $loader->add('Symfony\Component', __DIR__.'/component');
 *     $loader->add('Symfony',           __DIR__.'/framework');
 *
 *     // activate the autoloader
 *     $loader->register();
 *
 *     // to enable searching the include path (eg. for PEAR pa