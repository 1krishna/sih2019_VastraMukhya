<?php 
/**
 * @author tshirtecommerce - https://tshirtecommerce.com
 * @date: August 26 2016
 * 
 * API
 * 
 * @copyright  Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 *
 */

$arr = array('opencart', 'store.php');
$abspath = str_replace($arr, '', __FILE__);

if (defined('DS') == false) define('DS', DIRECTORY_SEPARATOR);
if (defined('ROOT') == false) define('ROOT', $abspath);

if (file_exists(ROOT.DS.'includes'.DS.'functions.php')) {
	include_once (ROOT.DS.'includes'.DS.'functions.php');
	$dg = new dg();
	$settings = $dg->getSetting();
	if (isset($settings->store) 
		&& isset($settings->store->api) 
		&& $settings->store->api != '' 
		&& isset($settings->store->verified) 
		&& $settings->store->verified == 1 
		&& isset($settings->store->enable) 
		&& $settings->store->enable == 1 
	) {
		include_once(ROOT.DS.'api.php');
		$api = new API($settings->store->api);
		$api->updateArts();
		$api->updateIdeas();
	}
}
echo 'The data synchronization is successful.';
return;