<?php

define('ROOT_DIR', (realpath(dirname(__FILE__)) == '/' ? '/' : realpath(dirname(__FILE__)) . '/'));
define('CONTENT_DIR', ROOT_DIR . 'content/');
define('ENGINE_DIR', ROOT_DIR . 'engine/');
define('LAYOUT_DIR', ROOT_DIR . 'layout/');

require(ENGINE_DIR . 'Monogatari.php');

require_once(ENGINE_DIR . 'markdown.php');
require_once(ENGINE_DIR . 'markdownExtra.php');
require_once(ENGINE_DIR . 'smartypants.php');
require_once(ENGINE_DIR . 'twig/Autoloader.php');
require_once(ENGINE_DIR . 'zend/Loader/StandardAutoloader.php');

$l =  new Zend\Loader\StandardAutoloader(array(
	'autoregister_zf' => true,
	'namespaces' => array(
		'Zend\Cache' => ENGINE_DIR . 'zend/Cache',
		'Zend\Loader' => ENGINE_DIR . 'zend/Loader'
	)));
$l->register();

$monogatari = new Monogatari();

?>