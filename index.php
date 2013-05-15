<?php

define('ROOT_DIR', (realpath(dirname(__FILE__)) == '/' ? '/' : realpath(dirname(__FILE__)) . '/'));
define('CONTENT_DIR', ROOT_DIR . 'content/');
define('ENGINE_DIR', ROOT_DIR . 'engine/');
define('LIB_DIR', ROOT_DIR . 'lib/');
define('LAYOUT_DIR', ROOT_DIR . 'layout/');

require(ENGINE_DIR . 'Monogatari.php');

require_once(LIB_DIR . 'php-markdown/Michelf/markdown.php');
require_once(LIB_DIR . 'php-markdown/Michelf/markdownExtra.php');
require_once(LIB_DIR . 'php-smartypants/smartypants.php');
require_once(LIB_DIR . 'twig/lib/Twig/Autoloader.php');
require_once(LIB_DIR . 'zf2/library/Zend/Loader/StandardAutoloader.php');

$l =  new Zend\Loader\StandardAutoloader(array(
	'autoregister_zf' => true,
	'namespaces' => array(
		'Zend\Cache' => ENGINE_DIR . 'zend/Cache',
		'Zend\Loader' => ENGINE_DIR . 'zend/Loader'
	)));
$l->register();

$monogatari = new Monogatari();

?>