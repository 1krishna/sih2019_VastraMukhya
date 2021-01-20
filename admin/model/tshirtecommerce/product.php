<?php

/**
 * @author 		tshirtecommerce - www.tshirtecommerce.com
 * @date  		September 13, 2017
 *
 * Version		4.2.0
 * 
 * @copyright  	Copyright (C) 2015 tshirtecommerce.com. All rights reserved.
 * @license	   	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ModelTshirtecommerceProduct extends Model
{
	public function get_product($product_id)
	{
		$print_type = '';
		$show_attribute = '';
		$design = '';
		$query = $this->db->query("
			SELECT p.`design_product_id`, p.`design_product_title_img` 
			FROM `".DB_PREFIX."product` p 
			WHERE `product_id`= '".(int)$product_id."'
		");
		if ($query->num_rows) {
			$design_product_id_temp = $query->rows[0]['design_product_id'];
			if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
			if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DIRECTORY_SEPARATOR.'tshirtecommerce');
			if (file_exists(ROOT.DS.'includes'.DS.'functions.php')) {
				include_once ROOT.DS.'includes'.DS.'functions.php';
				include_once ROOT.DS.'includes'.DS.'addons.php';
				$addons = new addons();
				$arr = explode(':', $design_product_id_temp);
				$design_product_id = (count($arr) > 1) ? (isset($arr[2]) ? $arr[2] : 0) : $design_product_id_temp;
				
				$file_product = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
				if (file_exists($file_product)) {
					$string = @file_get_contents($file_product);
					if ($string != false) {
						$products_temp = json_decode($string, true);
						foreach ($products_temp['products'] as $product) {
							if ($product['id'] == $design_product_id) {
								$print_type = isset($product['print_type']) ? $product['print_type'] : '';
								$show_attribute = isset($product['show_attribute']) ? $product['show_attribute'] : 0;
								$design = isset($product['design']) ? $product['design'] : '';
								break;
							}
						}
					}
				}

				$print_types = array(
					'screen'		=> $addons->__('settings_print_screen'),
					'DTG'			=> $addons->__('settings_print_DTG'),
					'sublimation'	=> $addons->__('settings_print_sublimation'),
					'embroidery'	=> $addons->__('settings_print_embroidery'),
				);
				
				/* @todo: Get print type from custom */
				/* Count files on addons/printings/ folder -- add to $print_types */
			}

			return array(
				'print_type' 		=> $print_type, 
				'show_attribute' 	=> $show_attribute, 
				'design' 			=> $design, 
				'print_types' 		=> $print_types
			);
		}
		return false;
	}

	/* @since version 4.2.0
	 * only use for Campaign addon
	 * get campaign information to view in back-office
	 * for fixed #1
	 */
	public function get_campaign_info($product_id)
	{
		$this->load->language('extension/module/tshirtecommerce');

		$running 	= $this->language->get('tshirtecommerce_addon_campaign_label_status_running');
		$ended 		= $this->language->get('tshirtecommerce_addon_campaign_label_status_ended');

		if ($product_id > 0) {
			$query = $this->db->query('
				SELECT p.`product_id`, p.`date_added`, p.`date_end`, p.`author`,
					IF( p.`date_end` > NOW(), "'.$running.'", "'.$ended.'") AS status,
					(SELECT SUM(op.`quantity`) FROM `'.DB_PREFIX.'order_product` op WHERE op.`product_id` = '.(int)$product_id.') AS sold
				FROM `'.DB_PREFIX.'product` p
				WHERE p.`product_id` = '.(int)$product_id.'
			');
			if ($query->num_rows) return $query->row;
		}
		return false;
	}
}

?>