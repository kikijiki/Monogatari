<?php 
$settings['languages'] = array('en', 'ja'); //In order of priority
$settings['site_title']['en'] = 'MONOGATARI';
$settings['site_title']['ja'] = '物語';
$settings['continue_reading']['en'] = '[continue reading]';
$settings['continue_reading']['ja'] = '【読み続く】';

$settings['base_url'] = '/';
$settings['layout'] = 'somatsu';

//false to disable, check the zf2 documentation for more options.
$settings['cache_options'] = array('cache_dir' => ROOT_DIR.'cache');
$settings['analytics_ua'] = false;
$settings['twig_config'] = array(
    'cache' => false,
    'autoescape' => false,
    'debug' => false
);

?>