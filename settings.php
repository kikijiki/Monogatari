<?php 
$settings['languages'] = array('en', 'ja'); //In order of priority
$settings['site_title']['en'] = 'MONOGATARI';
$settings['site_title']['ja'] = '物語';
$settings['continue_reading']['en'] = '[continue reading]';
$settings['continue_reading']['ja'] = '【読み続く】';
$settings['base_url'] = '/';
$settings['layout'] = 'somatsu';
$settings['analytics_ua'] = null;
$settings['cache_options'] = array('cache_dir' => ROOT_DIR.'cache'); //false to disable, check zf2 for more options
$settings['twig_config'] = array(
    'cache' => false,
    'autoescape' => false,
    'debug' => false
);

?>