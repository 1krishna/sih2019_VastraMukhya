<?php 

// Only using for Opencart
// From v 4.1.5

error_reporting(0);
@session_start();
date_default_timezone_set('America/Los_Angeles');
define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

if (isset($_GET['type'])) {
	$type = $_GET['type'];
} else {
	$type = '';
}

require_once ROOT.DS.'includes'.DS.'functions.php';
$dg = new dg();
$lang = $dg->lang();

include_once (ROOT.DS.'includes'.DS.'addons.php');					
$addons = new addons();

require_once ROOT.DS.'includes'.DS.'functionso.php';
$dgo = new dgo();

switch ($type) {
	case 'saveDesignAdmin':
		$dgo->saveDesignAdmin();
		break;

	default:
		break;
}

?>