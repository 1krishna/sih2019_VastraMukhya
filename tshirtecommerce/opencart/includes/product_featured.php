<?php 
$site_url = $this->config->get('config_url');
if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
	$site_url = $this->config->get('config_ssl');
}
if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
include_once(ROOT.'/includes/functions.php');
include_once(ROOT.'/includes/functionso.php');
$dg = new \dgo();
$ts_products = $dg->getProducts();
$id_product_ = isset($product_info['product_id']) ? $product_info['product_id'] : $result['product_id'];
$ts_product_info = $this->db->query('
	SELECT `design_product_id`
	FROM `'.DB_PREFIX.'product`
	WHERE `product_id` = '.(int)$id_product_.'
	')->row;
$ts_design_product_id = $ts_product_info['design_product_id'];
$ts_design_product_id_tmp = explode(':', $ts_design_product_id);
$image_ = '';
if (count($ts_design_product_id_tmp) > 1) {
	$ts_design_product_id = isset($ts_design_product_id_tmp[2]) ? $ts_design_product_id_tmp[2] : 0;
	$user_id = $ts_design_product_id_tmp[0];
	$design_id = $ts_design_product_id_tmp[1];
	$cache = $dg->cache('admin');
	$designs = $cache->get($user_id);
	if ($designs == null || empty ($designs[$design_id])) {
		$cache = $dg->cache();
		$designs = $cache->get($user_id);
	}
	$design_ = $designs[$design_id];
	if (isset($design_['images']) && count($design_['images']) > 0) {
		foreach ($design_['images'] as $img_) {
			if (!empty($img_)) {
				$image_ = $site_url.'/tshirtecommerce/'.$img_;
			}
		}
	}
} else {
	$temp_design = array();
	foreach ($ts_products as $tsproduct) {
		if ($tsproduct->id == $ts_design_product_id) {
			$temp_design = $tsproduct->design;
		}
	}
	$ts_check_get_image = false;
	if (!$ts_check_get_image && count($temp_design) > 0 && count($temp_design->front) > 0) {
		foreach ($temp_design->front as $key_ => $front_img) {
			$tmp = array();
			if (!empty($front_img)) {
				$image_ = $dg->getImgage($front_img);
				$ts_check_get_image = true;
				break;
			}
		}
	}
	if (!$ts_check_get_image && count($temp_design) > 0 && count($temp_design->back) > 0) {
		foreach ($temp_design->back as $key_ => $front_img) {
			$tmp = array();
			if (!empty($front_img)) {
				$image_ = $dg->getImgage($front_img);
				$ts_check_get_image = true;
				break;
			}
		}
	}
	if (!$ts_check_get_image && count($temp_design) > 0 && count($temp_design->left) > 0) {
		foreach ($temp_design->left as $key_ => $front_img) {
			$tmp = array();
			if (!empty($front_img)) {
				$image_ = $dg->getImgage($front_img);
				$ts_check_get_image = true;
				break;
			}
		}
	}
	if (!$ts_check_get_image && count($temp_design) > 0 && count($temp_design->right) > 0) {
		foreach ($temp_design->right as $key_ => $front_img) {
			$tmp = array();
			if (!empty($front_img)) {
				$image_ = $dg->getImgage($front_img);
				$ts_check_get_image = true;
				break;
			}
		}
	}
}

$this->load->model('setting/setting');
$tshirtecommerce_allow_images_val = $this->config->get('tshirtecommerce_allow_images');
if ($tshirtecommerce_allow_images_val !== null) {
	$tshirtecommerce_allow_images = $tshirtecommerce_allow_images_val;
} else {
	$tshirtecommerce_allow_images = 1;
}

switch ($tshirtecommerce_allow_images) {
	case 2:
		if (!empty($image_)) {
			$image = $image_;
		}
		break;

	default:
		break;
}

?>