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

class ControllerTshirtecommerceDesigner extends Controller {

	private $error = array();

	public function index()
	{
		if ($this->customer->isLogged()) {
			if ($this->is_session_started() === false) @session_start();

			$_SESSION['is_logged'] = array(
				'login' => true,
				'id' => $this->customer->getId(),
				'email' => $this->customer->getEmail()
			);
		}

		$data = array();
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', '', true)
		);
		
		$this->load->language('extension/module/tshirtecommerce');
		$this->load->model('tshirtecommerce/order');
		$this->load->model('setting/store');

		$check = false;

		$tshirtecommerce_logo_loading = $this->config->get('tshirtecommerce_logo_loading');
		if (null !== $tshirtecommerce_logo_loading) {
			$data['tshirtecommerce_logo_loading'] = $tshirtecommerce_logo_loading;
		} else {
			$data['tshirtecommerce_logo_loading'] = 'tshirtecommerce/assets/images/logo-loading.png';
		}
		$data['tshirtecommerce_text_loading'] = $this->language->get('tshirtecommerce_text_loading');
		
		if (isset($this->request->get['color'])) $color = $this->request->get['color'];
		if (isset($this->request->get['cart_id'])) $cart_id = $this->request->get['cart_id'];
		if (isset($this->request->get['edit'])) $edit = $this->request->get['edit'];
		if (isset($this->request->get['product_id']) && isset($this->request->get['parent_id'])) {
			$check = true;
			$product_id = $this->request->get['product_id'];
			$parent_id = $this->request->get['parent_id'];
		} elseif (isset($this->request->get['design']) && isset($this->request->get['parent_id'])) {
			$check = true;
			$product_id = $this->request->get['design'];
			$parent_id = $this->request->get['parent_id'];
		} else {
			$product_id = $this->config->get('tshirtecommerce_product');
			if ($product_id) {
				$product = $this->model_tshirtecommerce_order->getProduct((int)$product_id);
				if ($product !== false) {
					$check = true;
					$product_id = $product['design_product_id'];
					$parent_id 	= $product['product_id'];
				}
			}			
		}

		if ($this->request->server['HTTPS']) {
			$url = $this->config->get('config_ssl');
		} else {
			$url = $this->config->get('config_url');
		}
		// For mutiple store
		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();
		if (count($stores) > 1) {
			$store_id = $this->config->get('config_store_id');
			foreach ($stores as $store) {
				if ($store_id == $store['store_id']) {
					$url = $this->request->server['HTTPS'] ? $store['ssl'] : $store['url'];
					break;
				}
			}
		}
		$data['url'] = $url;

		$checkproduct = $this->model_tshirtecommerce_order->checkProductDesign($parent_id, $product_id);
		if ($check == true && $checkproduct == true) {
			$params = explode(':', $product_id);
			$view = '<div class="row-designer"></div>';
			
			if (count($params) > 1) {
				$data['urlDesignload'] = $url.'/tshirtecommerce/index.php?product='.$params[2].'&color='.$params[3].'&user='.$params[0].'&id='.$params[1].'&parent='.$parent_id;
				$data['product_id']	= $product_id;
				$data['parent_id']	= $parent_id;
				if (isset($cart_id)) {
					$data['cart_id'] = $cart_id;
					$data['urlDesignload'] .= '&cart_id='.$cart_id;
				}
			} else {
				if (isset($color)) {
					$data['urlDesignload'] = $url.'/tshirtecommerce/index.php?product='.$product_id.'&parent='.$parent_id.'&color='.$color;
					$data['color'] = $color;
				} elseif (isset($cart_id)) {
					$data['urlDesignload'] = $url.'/tshirtecommerce/index.php?product='.$product_id.'&parent='.$parent_id.'&cart_id='.$cart_id;
					$data['cart_id'] = $cart_id;
				} else {
					$data['urlDesignload'] = $url.'/tshirtecommerce/index.php?product='.$product_id.'&parent='.$parent_id;
				}
				$data['product_id']	= $product_id;				
				$data['parent_id'] = $parent_id;
			}

			$data['edit'] = isset($edit) ? $edit : 0;
		} else {
			$view = $this->language->get('text_product_not_found');	// Fixed #32805
		}
		
		$file = dirname(DIR_SYSTEM).'/tshirtecommerce/data/settings.json';
		if (file_exists($file)) {
			$tse_settings = @file_get_contents($file);
			$tse_settings = @json_decode($tse_settings, true);
			if (isset($tse_settings['site_name'])) $this->document->setTitle($tse_settings['site_name']);
			if (isset($tse_settings['meta_description'])) $this->document->setDescription($tse_settings['meta_description']);
			if (isset($tse_settings['meta_keywords'])) $this->document->setKeywords($tse_settings['meta_keywords']);
		}

		$file_product = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
		if (file_exists($file_product)) {
			$products = @file_get_contents($file_product);
			$products = @json_decode($products, true);
			$products = (isset($products['products']) && count($products['products'])) ? $products['products'] : array();
			if (count($products)) {
				foreach ($products as $tproduct) {
					if ($tproduct['id'] == $product_id) {
						if (isset($tproduct['meta_title'])) $this->document->setTitle($tproduct['meta_title']);
						if (isset($tproduct['meta_description'])) $this->document->setDescription($tproduct['meta_description']);
						if (isset($tproduct['meta_keyword'])) $this->document->setKeywords($tproduct['meta_keyword']);
						break;
					}
				}
			}
		}

		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($parent_id);
		$data['breadcrumbs'][] = array(
			'text' => $product_info['name'],
			'href' => $this->url->link('product/product', 'product_id=' . $parent_id, true)
		);
		
		$data['content'] = $view;
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('tshirtecommerce/design', $data));		
	}
	
	public function ajax()
	{
		$this->load->model('tshirtecommerce/order');
		$products = $this->model_tshirtecommerce_order->getProducts();
		if ($products == 0) return false;

		$array = array('products' => array(), 'categories' => array());

		$category_id = (isset($this->request->post['id']) && $this->request->post['id'] > 0) ? $this->request->post['id'] : 0;
		if ($category_id > 0) {
			$product_to_category = array();
			$query_product_of_category = $this->db->query("
				SELECT ptc.`product_id`
				FROM `".DB_PREFIX."product_to_category` ptc
				WHERE ptc.`category_id` = '".$category_id."'
			");
			if ($query_product_of_category->num_rows) $product_to_category = $query_product_of_category->rows;
			$cproducts = array();
			if (count($product_to_category)) {
				foreach ($product_to_category as $ptc) {
					if (isset($ptc['product_id'])) $cproducts[] = $ptc['product_id'];
				}
			}
			foreach ($products as $key => $product) {
				if (!in_array($product['product_id'], $cproducts)) unset($products[$key]);
			}

			// Get sub-categories
			$language_id = $this->config->get('config_language_id');
			$query_categories = $this->db->query("
				SELECT c.`category_id` as id, c.`parent_id`, d.`name` as title
				FROM `".DB_PREFIX."category` c
				INNER JOIN `".DB_PREFIX."category_description` d 
					ON c.`category_id` = d.`category_id`
				WHERE d.`language_id` = '".$language_id."' 
					AND c.`parent_id` = '".$category_id."'
				ORDER BY c.`sort_order`
			");
			if ($query_categories->num_rows) $array['categories'] = $query_categories->rows;
		}
		
		$design_ids = array();
		$design = array();
		$min_order = array();
		foreach ($products as $product) {
			$temp = explode(':', $product['design_product_id']);
			if (count($temp) == 1) {
				$design[$product['design_product_id']] = $product['product_id'];
				$design_ids[] = $product['design_product_id'];
				$min_order[$product['design_product_id']] = $product['minimum'];
			}			
		}

		$file = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
		$tproducts = array();
		if (file_exists($file)) {
			$string = @file_get_contents($file);
			if ($string != false) $tproducts = @json_decode($string, true);
		}

		if (count($tproducts['products'])) {
			foreach ($tproducts['products'] as $key => $tproduct) {
				if (!in_array($tproduct['id'], $design_ids)) {
					unset($tproducts[$key]);
				} else {
					$tproduct['parent_id'] = $design[$tproduct['id']];
					$tproduct['attribute'] = isset($tproduct['attributes']['name']) ? $this->getAttributes($tproduct['attributes']) : '';
					if(isset($min_order[$tproduct['id']]))
						$tproduct['attribute'] .= $this->quantity($min_order[$tproduct['id']]);
					$array['products'][] = $tproduct;
				}
			}
		}

		echo @json_encode($array);
		exit();
	}
	
	public function getProductDesign()
	{
		$this->load->model('tshirtecommerce/order');
		$products = $this->model_tshirtecommerce_order->getProducts();
		if ($products == 0) return false;
		
		$design_ids = '';
		foreach ($products as $product) {
			$temp = explode(':', $product['design_product_id']);
			if (count($temp) == 1) {
				if($design_ids == '')
					$design_ids = $product['design_product_id'];
				else
					$design_ids .= ','.$product['design_product_id'];
			}			
		}
		
		echo $design_ids;
		exit();
	}
	
	// get attribute of product
	public function getAttributes($attribute)
	{
		if(is_array($attribute))
			$attribute = (object) $attribute;
			
		if (isset($attribute->name) && $attribute->name != '') {
			$attrs = new stdClass();
			$attrs->name = is_string($attribute->name) ? json_decode($attribute->name) : $attribute->name;
			$attrs->titles = is_string($attribute->titles) ? json_decode($attribute->titles) : $attribute->titles;
			$attrs->prices = is_string($attribute->prices) ? json_decode($attribute->prices) : $attribute->prices;
			$attrs->type = is_string($attribute->type) ? json_decode($attribute->type) : $attribute->type;
			
			$html = '';
			for ($i = 0; $i < count($attrs->name); $i++) {
				$html .= '<div class="form-group product-fields">';
				$html .= '<label for="fields">'.$attrs->name[$i].'</label>';
				$id = 'attribute['.$i.']';
				$html .= $this->field($attrs->name[$i], $attrs->titles[$i], $attrs->prices[$i], $attrs->type[$i], $id);
				$html .= '</div>';
			}
			return $html;
		} else {
			return '';
		}
	
	}
	
	public function field($name, $title, $price, $type, $id)
	{
		$html = '<div class="dg-poduct-fields">';
		switch ($type) {
			case 'checkbox':
				for ($i = 0; $i < count($title); $i++) {
					$html .= '<label class="checkbox-inline">';
					$html .= 	'<input type="checkbox" name="'.$id.'['.$i.']" value="'.$i.'"> '.$title[$i];
					$html .= '</label><br />';
				}
			break;
			
			case 'selectbox':
				$html .= '<select class="form-control input-sm" name="'.$id.'">';
				for ($i=0; $i<count($title); $i++) {
					$html .= '<option value="'.$i.'">'.$title[$i].'</option>';
				}
				$html .= '</select><br />';
			break;
			
			case 'radio':
				for ($i = 0; $i < count($title); $i++) {
					$html .= '<label class="radio-inline">';
					$html .= 	'<input type="radio" name="'.$id.'" value="'.$i.'"> '.$title[$i];
					$html .= '</label><br />';
				}
			break;
			
			case 'textlist':
				$html .= '<style>.product-quantity{display:none;}</style><ul class="p-color-sizes list-number col-md-12">';
				for ($i = 0; $i < count($title); $i++) {
					$html .= '<li>';
					$html .= 	'<label>'.$title[$i].'</label>';
					$html .= 	'<input type="text" class="form-control input-sm size-number" name="'.$id.'['.$i.']">';					
					$html .= '</li>';
				}
				$html .= '</ul>';
			break;

			default:
				break;
		}
		$html .= '</div>';
		
		return $html;
	}
	
	public function quantity($min = 1, $name = 'Quantity', $name2 = 'minimum quantity: '){
		if ($min < 1) $min = 1;
		
		$html = '<div class="form-group product-fields product-quantity">';
		$html .= 	'<label class="col-sm-4">'.$name.'</label>';
		$html .= 	'<div class="col-sm-6">';
		$html .= 		'<input type="text" class="form-control input-sm" value="0" data-count="'.$min.'" name="quantity" id="quantity">';
		$html .= 	'</div>';
		$html .= 	'<span class="help-block"><small>'.$name2.$min.'</small></span>';
		$html .= '</div>';
		
		return $html;
	}

	public function categories()
	{
		$data = array('error' => 0, 'categories' => '');
		$parent_id = isset($this->request->post['parent_id']) ? $this->request->post['parent_id'] : 0;
		$language_id = $this->config->get('config_language_id');

		$categories = $this->db->query("
			SELECT c.`category_id` as id, c.`parent_id`, d.`name` as title
			FROM `".DB_PREFIX."category` c
			INNER JOIN `".DB_PREFIX."category_description` d 
				ON c.`category_id` = d.`category_id`
			WHERE d.`language_id` = '".$language_id."' 
				AND c.`parent_id` = '".$parent_id."'
			ORDER BY c.`sort_order`
		");
		if ($categories->num_rows) $data['categories'] = $categories->rows;

		echo @json_encode($data);
		return;
	}

	protected function is_session_started()
	{
	    if (php_sapi_name() !== 'cli') {
	        if (version_compare(phpversion(), '5.4.0', '>=')) {
	            return session_status() === PHP_SESSION_ACTIVE ? true : false;
	        } else {
	            return session_id() === '' ? false : true;
	        }
	    }
	    return false;
	}
}

?>