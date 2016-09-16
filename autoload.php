<?php

/*
 * This file is part of Huxtable\Core
 */
namespace Huxtable\Core;

$pathBaseCore	= __DIR__;
$pathSrcCore	= $pathBaseCore . '/src/Core';
$pathVendorCore	= $pathBaseCore . '/vendor';

/*
 * Initialize autoloading
 */
include_once( $pathSrcCore . '/Autoloader.php' );
Autoloader::register();

/*
 * Initialize vendor autoloading
 */
include_once( $pathVendorCore . '/spyc/autoload.php' );
