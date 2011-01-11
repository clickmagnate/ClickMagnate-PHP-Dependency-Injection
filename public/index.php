<?php

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
defined('APPLICATION_ENV')  || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/../library'), get_include_path() )));

require_once 'Zend/Loader/Autoloader.php';

$loader = Zend_Loader_Autoloader::getInstance();
$loader->setFallbackAutoloader(true);

$iniLocation = APPLICATION_PATH . '/configs/application.ini';
$config = new Zend_Config_Ini($iniLocation,'development');
Zend_Registry::set('config', $config);

$application = new Zend_Application(APPLICATION_ENV, $iniLocation);
$application->bootstrap()->run();