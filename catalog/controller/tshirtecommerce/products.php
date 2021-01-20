<?php 
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		September 13, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ControllerTshirtecommerceProducts extends Controller
{
	
	public function get_products_linked()
	{
		$arr = array();
		$product_array = $this->db->query("
			SELECT `design_product_id` 
			FROM `".DB_PREFIX."product` 
			WHERE `design_product_id` IS NOT NULL AND `design_product_id` <> ''
		")->rows;
		if ($product_array !=false && count($product_array) > 0) {
			foreach ($product_array as $value) {
				$temp = explode(':', $value['design_product_id']);
				$arr[] = (count($temp) > 1) ? (isset($temp[2]) ? $temp[2] : 0) : $value['design_product_id'];
			}
		}
		$arr = array_unique($arr);

		echo json_encode($arr);
		return;
	}

	public function get_product_changed()
	{
		$json = array();
		if (isset($this->request->post['id'])) {
			$design_product_id = $this->request->post['id'];
			$query = $this->db->query("SELECT `product_id` AS parent_id,`quantity` AS max_order,`price`,`minimum` AS min_order ,`design_product_id` AS design_id FROM `" . DB_PREFIX . "product` WHERE `design_product_id`='".$design_product_id."'");
			$json = $query->row;
		}
		
		echo json_encode($json);
		return;
	}
	
	public function get_product_url()
	{
		$url 	= '';
		if( isset($_GET['post_id']) )
		{
			$product_link = $this->url->link('product/product', 'product_id=' . $_GET['post_id']);
			$product_link = str_replace('amp;', '', $product_link);
			if(strpos($product_link, '?'))
			{
				$url = $product_link.'&user_design=';
			}
			else
			{
				$url = $product_link.'user_design::';
			}
		}
		echo $url;
		exit();
	}
	
	public function get_product_user_design()
	{
		if (!session_id()){
			session_start();
		}

		if(isset($_GET['design_id']))
		{
			$key 			= md5($_GET['design_id']);
			$design 		= '';
			if(isset($_SESSION['user_designs']))
			{
				$designs 		= $_SESSION['user_designs'];
				if(isset($designs[$key]))
				{
					$design 			= $designs[$key];
					$vectors 			= json_decode(stripslashes($design['vectors']));
					$design['vectors'] 	= $vectors;
				}
			}
			echo json_encode($design);
			exit();
		}

		if( isset($_POST['design_id']) && isset($_POST['product_id']) && isset($_POST['vectors']) )
		{
			$data = array(
				'design_id' => $_POST['design_id'],
				'product_id' => $_POST['product_id'],
				'vectors' => $_POST['vectors'],
			);
			$key 			= md5($_POST['design_id']);

			$designs 		= array();
			if(isset($_SESSION['user_designs']))
			{
				$designs 	= $_SESSION['user_designs'];
			}
			$designs[$key] 	= $data;
			$_SESSION['user_designs'] = $designs;
		}
	}
}