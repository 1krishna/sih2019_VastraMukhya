<?php 

$default_tcolor = isset($this->request->get['color']) ? $this->request->get['color'] : '';
$tsImages = array();
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
$ts_product_info = $this->db->query('
	SELECT `design_product_id`
	FROM `'.DB_PREFIX.'product`
	WHERE `product_id` = '.(int)$product_id.'
	')->row;
$ts_design_product_id = $ts_product_info['design_product_id'];
$ts_design_product_id_tmp = explode(':', $ts_design_product_id);
if (count($ts_design_product_id_tmp) > 1) {
	$ts_design_product_id = isset($ts_design_product_id_tmp[2]) ? $ts_design_product_id_tmp[2] : 0;
	$user_id = $ts_design_product_id_tmp[0];
	$design_id = $ts_design_product_id_tmp[1];
	if (empty($default_tcolor)) {
		$default_tcolor = isset($ts_design_product_id_tmp[3]) ? $ts_design_product_id_tmp[3] : '';
	}
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
				$tmp['popup'] = $site_url.'/tshirtecommerce/'.$img_;
				$tmp['thumb'] = $site_url.'/tshirtecommerce/'.$img_;
				$tsImages[] = $tmp;
			}
		}
	}
} else {
	$show_attribute_ = false;
   	$temp_design = array();
   	foreach ($ts_products as $tsproduct) {
   		if ($tsproduct->id == $ts_design_product_id) {
			$temp_design = $tsproduct->design;
			if (isset($tsproduct->show_attribute)) {
				$show_attribute_ = true;
			}
			break;
   		}
   	}
   	if (empty($default_tcolor) && count($temp_design) > 0 && count($temp_design->color_hex) > 0) {
   		$default_tcolor = $temp_design->color_hex[0];
   	}
   	$index_ = 0;
   	if (count($temp_design) > 0) {
	   	foreach ($temp_design->color_hex as $key_ => $value_) {
			if ($value_ == $default_tcolor) {
				$index_ = $key_;
				break;
			}
	   	}
   	}
   	if (count($temp_design) > 0 && count($temp_design->front) > 0) {
   		foreach ($temp_design->front as $key_ => $front_img) {
   			$tmp = array();
   			if ($show_attribute_) {
   				if ($key_ == $index_ && !empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
					break;
				}
   			} else {
   				if (!empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
				}
   			}
   		}
   	}
   	if (count($temp_design) > 0 && count($temp_design->back) > 0) {
   		foreach ($temp_design->back as $key_ => $front_img) {
   			$tmp = array();
   			if ($show_attribute_) {
   				if ($key_ == $index_ && !empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
					break;
				}
   			} else {
   				if (!empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
				}
   			}
   		}
   	}
   	if (count($temp_design) > 0 && count($temp_design->left) > 0) {
   		foreach ($temp_design->left as $key_ => $front_img) {
   			$tmp = array();
   			if ($show_attribute_) {
   				if ($key_ == $index_ && !empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
					break;
				}
   			} else {
   				if (!empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
				}
   			}
   		}
   	}
   	if (count($temp_design) > 0 && count($temp_design->right) > 0) {
   		foreach ($temp_design->right as $key_ => $front_img) {
   			$tmp = array();
   			if ($show_attribute_) {
   				if ($key_ == $index_ && !empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
					break;
				}
   			} else {
   				if (!empty($front_img)) {
					$ts_image = $dg->getImgage($front_img);
					$tmp['popup'] = $ts_image;
					$tmp['thumb'] = $ts_image;
					$tsImages[] = $tmp;
				}
   			}
   		}
   	}
}
sort($tsImages);

$this->load->model('setting/setting');
$tshirtecommerce_allow_images_val = $this->config->get('tshirtecommerce_allow_images');
if ($tshirtecommerce_allow_images_val !== null) {
	$tshirtecommerce_allow_images = $tshirtecommerce_allow_images_val;
} else {
	$tshirtecommerce_allow_images = 1;
}
switch ($tshirtecommerce_allow_images) {
	case 2:
		$this->data['images'] = $tsImages;
		if (count($tsImages) > 0) {
			$this->data['popup'] = $tsImages[0]['popup'];
			$this->data['thumb'] = $tsImages[0]['thumb'];
		}
		break;

	case 3:
		$this->data['images'] = array_merge($data['images'], $tsImages);
		break;

	default:
		break;
}

$this->data['tshirtecommerce_color'] = $default_tcolor;
$this->data['tcolor_link'] = $this->url->link(
	'product/product', 
	'product_id='.(int)$this->request->get['product_id']
);
$this->data['tshirtecommerce_designer_link'] = $this->url->link(
	'tshirtecommerce/designer', 
	'parent_id='.(int)$this->request->get['product_id']
);
if (isset($this->data['design_product_id']) && !empty($this->data['design_product_id'])) {
	$this->data['tshirtecommerce_designer_link'] .= '&product_id='.$this->data['design_product_id'];
}
if (isset($tshirtecommerce_color) && !empty($tshirtecommerce_color)) {
	$this->data['tshirtecommerce_designer_link'] .= '&color='.$tshirtecommerce_color;
}

?>