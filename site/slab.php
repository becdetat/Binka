<?php
error_reporting(E_ALL);

define('SLAB_ROOT', __FILE__);
define('SLAB_APP', dirname(SLAB_ROOT).'/app');
define('SLAB_LIB', dirname(SLAB_ROOT).'/lib');
require_once(SLAB_LIB.'/bootstrap.php');
$actionResult = Dispatcher::dispatch();
$actionResult->render();
?>