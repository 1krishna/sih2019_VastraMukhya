<?php 
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		September 07, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ControllerExtensionModuleTshirtecommerce extends Controller
{
	private $error;
	
	public function index()
	{	
		$this->install();
		
		// @since 4.2.1
		$str_token = version_compare(VERSION, '3.0.0.0', '<') ? 'token='.$this->session->data['token'] : 'user_token='.$this->session->data['user_token'];
		$str_ssl = version_compare(VERSION, '2.2.0.0', '<') ? 'SSL' : true;
		$this->load->model('tshirtecommerce/seo_url');
		$this->model_tshirtecommerce_seo_url->install();

		$this->load->language('extension/module/tshirtecommerce');
		$this->load->model('setting/setting');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->language('common/language');
		$this->load->model('localisation/language');
		$data['languages'] = array();
		$results = $this->model_localisation_language->getLanguages();
		foreach ($results as $result) {
			if ($result['status']) {
				$data['languages'][] = array(
					'name' => $result['name'],
					'code' => $result['code'],
					'tshirtecommerce_custom_text' => 'Custom Your Design'
				);
			}
		}
		$data['code'] = isset($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language');
		if ($this->request->server['HTTPS']) {
			$data['site_url'] = HTTPS_CATALOG;
		} else {
			$data['site_url'] = HTTP_CATALOG;
		}

		// save config
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$data['tshirtecommerce_customize_design_btn'] = '';

			if(isset($this->request->post['tshirtecommerce_customize_design_btn'])) {
				$data['tshirtecommerce_customize_design_btn'] = $this->request->post['tshirtecommerce_customize_design_btn'];
			}

			$this->model_setting_setting->editSetting('tshirtecommerce', $this->request->post);
			$data['product_id'] = (isset($this->request->post['tshirtecommerce_product']) ? $this->request->post['tshirtecommerce_product'] : 0);
			$data['category_id'] = (isset($this->request->post['tshirtecommerce_category']) ? $this->request->post['tshirtecommerce_category'] : 0);
			$data['tshirtecommerce_hide_addtocart'] = isset($this->request->post['tshirtecommerce_hide_addtocart']) ? $this->request->post['tshirtecommerce_hide_addtocart'] : 0;
			$data['tshirtecommerce_hide_addtocart_list'] = isset($this->request->post['tshirtecommerce_hide_addtocart_list']) ? $this->request->post['tshirtecommerce_hide_addtocart_list'] : 0;
			$data['tshirtecommerce_hide_design_button'] = isset($this->request->post['tshirtecommerce_hide_design_button']) ? $this->request->post['tshirtecommerce_hide_design_button'] : 0;
			$data['tshirtecommerce_downloadable'] = isset($this->request->post['tshirtecommerce_downloadable']) ? $this->request->post['tshirtecommerce_downloadable'] : 0;
			$data['tshirtecommerce_code'] = isset($this->request->post['tshirtecommerce_code']) ? $this->request->post['tshirtecommerce_code'] : '';
			
			$data['tshirtecommerce_send_order'] = isset($this->request->post['tshirtecommerce_send_order']) ? $this->request->post['tshirtecommerce_send_order'] : '';
			$data['tshirtecommerce_auto_send_order'] = isset($this->request->post['tshirtecommerce_auto_send_order']) ? $this->request->post['tshirtecommerce_auto_send_order'] : '';
			$data['tshirtecommerce_list_send_status'] = isset($this->request->post['tshirtecommerce_list_send_status']) ? $this->request->post['tshirtecommerce_list_send_status'] : '';

			if (isset($this->request->post['tshirtecommerce_logo_loading']) && !empty($this->request->post['tshirtecommerce_logo_loading']) && $this->request->post['tshirtecommerce_logo_loading'] != null) {
				$data['tshirtecommerce_logo_loading'] = $this->request->post['tshirtecommerce_logo_loading'];
			} else {
				$data['tshirtecommerce_logo_loading'] = $data['site_url'].'/tshirtecommerce/assets/images/logo-loading.png';
			}
			if (strpos($data['tshirtecommerce_logo_loading'], 'tshirtecommerce/assets/images/logo-loading.png') === false 
				&& strpos($data['tshirtecommerce_logo_loading'], $data['site_url']) === false) {
				$data['tshirtecommerce_logo_loading'] = $data['site_url'].'/image/'.$data['tshirtecommerce_logo_loading'];
			}
			
			$data['tshirtecommerce_allow_taxes'] = isset($this->request->post['tshirtecommerce_allow_taxes']) ? $this->request->post['tshirtecommerce_allow_taxes'] : 1;
			$data['tshirtecommerce_seo_enable'] = isset($this->request->post['tshirtecommerce_seo_enable']) ? $this->request->post['tshirtecommerce_seo_enable'] : 0;
			$data['tshirtecommerce_seo_default_keyword'] = isset($this->request->post['tshirtecommerce_seo_default_keyword']) ? $this->request->post['tshirtecommerce_seo_default_keyword'] : 'custom-your-design';
			$data['tshirtecommerce_seo_default_keyword'] = $this->seo_friendly_url($data['tshirtecommerce_seo_default_keyword']);
			$data['tshirtecommerce_seo_ideas_default_keyword'] = isset($this->request->post['tshirtecommerce_seo_ideas_default_keyword']) ? $this->request->post['tshirtecommerce_seo_ideas_default_keyword'] : 'design-ideas';
			$data['tshirtecommerce_seo_ideas_default_keyword'] = $this->seo_friendly_url($data['tshirtecommerce_seo_ideas_default_keyword']);

			// Check Code
			$data['tshirtecommerce_verify'] = array();
			if (!empty($data['tshirtecommerce_code'])) {
				$json_str = $this->openURL('http://updates.tshirtecommerce.com/verify_purchase.php?code='.$data['tshirtecommerce_code'].'&platform=opencart');
				if ($json_str !== false) $data['tshirtecommerce_verify'] = json_decode($json_str, true);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');
		} else {
			
			$data['tshirtecommerce_allow_taxes'] = (null !== $this->config->get('tshirtecommerce_allow_taxes')) ? $this->config->get('tshirtecommerce_allow_taxes') : 1;
			$data['tshirtecommerce_logo_loading'] = (null !== $this->config->get('tshirtecommerce_logo_loading')) ? $this->config->get('tshirtecommerce_logo_loading') : $data['site_url'].'/tshirtecommerce/assets/images/logo-loading.png';
			
			$data['product_id'] = $this->config->get('tshirtecommerce_product');
			$data['category_id'] = $this->config->get('tshirtecommerce_category');
			$data['tshirtecommerce_downloadable'] = (null !== $this->config->get('tshirtecommerce_downloadable')) ? $this->config->get('tshirtecommerce_downloadable') : 0;
			$data['tshirtecommerce_hide_addtocart'] = (null !== $this->config->get('tshirtecommerce_hide_addtocart')) ? $this->config->get('tshirtecommerce_hide_addtocart') : 0;
			$data['tshirtecommerce_hide_addtocart_list'] = (null !== $this->config->get('tshirtecommerce_hide_addtocart_list')) ? $this->config->get('tshirtecommerce_hide_addtocart_list') : 0;
			$data['tshirtecommerce_hide_design_button'] = (null !== $this->config->get('tshirtecommerce_hide_design_button')) ? $this->config->get('tshirtecommerce_hide_design_button') : 0;
			$data['tshirtecommerce_code'] = (null !== $this->config->get('tshirtecommerce_code')) ? $this->config->get('tshirtecommerce_code') : '';
			$data['tshirtecommerce_customize_design_btn'] = (null !== $this->config->get('tshirtecommerce_customize_design_btn')) ? $this->config->get('tshirtecommerce_customize_design_btn') : '';
			$data['tshirtecommerce_seo_enable'] = (null !== $this->config->get('tshirtecommerce_seo_enable')) ? $this->config->get('tshirtecommerce_seo_enable') : 0;
			$data['tshirtecommerce_seo_default_keyword'] = (null !== $this->config->get('tshirtecommerce_seo_default_keyword')) ? $this->config->get('tshirtecommerce_seo_default_keyword') : 'custom-your-design';
			$data['tshirtecommerce_seo_ideas_default_keyword'] = (null !== $this->config->get('tshirtecommerce_seo_ideas_default_keyword')) ? $this->config->get('tshirtecommerce_seo_ideas_default_keyword') : 'design-ideas';
			
			$data['tshirtecommerce_send_order'] = (null !== $this->config->get('tshirtecommerce_send_order')) ? $this->config->get('tshirtecommerce_send_order') : '';
			$data['tshirtecommerce_auto_send_order'] = (null !== $this->config->get('tshirtecommerce_auto_send_order')) ? $this->config->get('tshirtecommerce_auto_send_order') : '';
			$data['tshirtecommerce_list_send_status'] = (null !== $this->config->get('tshirtecommerce_list_send_status')) ? $this->config->get('tshirtecommerce_list_send_status') : '';

			// Check Code
			$data['tshirtecommerce_verify'] = array();
			if (!empty($data['tshirtecommerce_code'])) {
				$json_str = $this->openURL('http://updates.tshirtecommerce.com/verify_purchase.php?code='.$data['tshirtecommerce_code'].'&platform=opencart');
				if ($json_str !== false) $data['tshirtecommerce_verify'] = json_decode($json_str, true);
			}
		}

		$styles_array = explode(';', $data['tshirtecommerce_customize_design_btn']);
		$data['tshirtecommerce_customize_styles'] = array(
			'color' => 'ffffff',
			'border_color' => '7bcd40',
			'background_color' => '7bcd40',
			'font_size' => '13',
			'padding' => '10',
			'border_radius' => '0',
			'border_width' => '1',
			'border_style' => 'solid',
		);
		if (count($styles_array) > 0) {
			foreach ($styles_array as $style) {
				$style = str_replace(' ', '', $style);
				$css = explode(':', $style);
				if (count($css) > 1) {
					switch ($css[0]) {
						case 'color':
						case 'border-color':
						case 'background-color':
							$idx = str_replace('-', '_', $css[0]);
							$data['tshirtecommerce_customize_styles'][$idx] = str_replace('#', '', trim($css[1]));		
							break;

						case 'font-size':
						case 'padding':
						case 'border-radius':
						case 'border-width':
							$idx = str_replace('-', '_', $css[0]);
							$data['tshirtecommerce_customize_styles'][$idx] = str_replace('px', '', trim($css[1]));
							break;
						
						case 'border-style':
							$idx = str_replace('-', '_', $css[0]);
							$data['tshirtecommerce_customize_styles'][$idx] = trim($css[1]);
							break;

						default:
							break;
					}
				}
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		if (isset($this->request->post['tshirtecommerce_code'])) $data['success'] = "Success: You have modified settings!";
		if (isset($this->error['warning'])) $data['warning'] = $this->error['warning'];
		if (isset($this->error['code'])) $data['error_code'] = $this->error['code'];
		
		$data_warning_msg = 'Your server not support <strong>allow_url_fopen</strong>. Please upload your hosting with <strong>allow_url_fopen = ON</strong>. Click <a href="http://tshirtecommerce.com/wp-content/uploads/2015/04/allow_url_fopen.png" target="_blank"><strong>HERE</strong></a> to see update!';
		if (!ini_get('allow_url_fopen')) $data['warning'] = $data_warning_msg;

		// Get store setting of Tshirtecommerce
		$data['setting_store_tshirtecommerce'] = 0;
		$setting_file = dirname(DIR_SYSTEM).'/tshirtecommerce/data/settings.json';
		if (file_exists($setting_file)) {
			$setting_content = @file_get_contents($setting_file);
			if ($setting_content !== false) {
				$setting_json = json_decode($setting_content, true);
				if (isset($setting_json['store']) && isset($setting_json['store']['enable']) && $setting_json['store']['enable'] == 1) {
					$data['setting_store_tshirtecommerce'] = 1;
				}
			}
		}
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'user_token='.$this->session->data['user_token'], true),
			'separator' => false
		);	
		$data['link'] = $this->url->link('extension/module/tshirtecommerce', 'user_token='.$this->session->data['user_token'], true);
		$data['advanced_url'] = $this->url->link('catalog/tshirtecommerce', 'task=settings&user_token='.$this->session->data['user_token'], true);
	
		// get all product design
		$this->load->model('tshirtecommerce/order');
		$products = $this->model_tshirtecommerce_order->getProducts();
		$data['products'] = $products;
		
		/*get product category*/
		$pr_categories = $this->model_tshirtecommerce_order->getProductCategories();
		
		/*filter list parent categories design*/
		$list_pr_categories = array();
		foreach($products as $value)
		{
			foreach($pr_categories as $pr_category)
			{
				if($pr_category['product_id'] == $value['product_id'])
					$list_pr_categories[$pr_category['category_id']] = $pr_category['product_id'];
			}
		}
		
		/*get category*/
		$this->load->model('catalog/category');
		$categories = $this->model_catalog_category->getCategories();
		$result_cate = array();
		
		foreach($list_pr_categories as $key=>$prcate)
		{
			foreach($categories as $category)
			{
				if($category['category_id'] == $key)
				{
					if($category['parent_id'] == 0)
					{
						$result_cate[$category['category_id']] = $category;
					}else
					{
						$cate = $this->model_tshirtecommerce_order->getParentCategory($categories, $category['parent_id']);
						if(isset($cate['category_id']))
							$result_cate[$cate['category_id']] = $cate;
					}
				}
			}
		}
		
		$data['tshirtecommerce_categories'] = $result_cate;
		
		//echo '<pre>'; print_r($result_cate); exit;
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('tshirtecommerce');
		$settings['tshirtecommerce_menu'] = 1;
		$settings['tshirtecommerce_code'] = $data['tshirtecommerce_code'];
		$settings['tshirtecommerce_logo_loading'] = $data['tshirtecommerce_logo_loading'];
		$settings['tshirtecommerce_allow_taxes'] = $data['tshirtecommerce_allow_taxes'];
		$settings['tshirtecommerce_seo_enable'] = $data['tshirtecommerce_seo_enable'];
		$settings['tshirtecommerce_seo_default_keyword'] = $data['tshirtecommerce_seo_default_keyword'];
		$settings['tshirtecommerce_seo_ideas_default_keyword'] = $data['tshirtecommerce_seo_ideas_default_keyword'];
		$data['tshirtecommerce_seo_ideas_default_url'] = $this->language->get('tshirtecommerce_seo_ideas_default_url').'<a target="_blank" href="'. $data['site_url']. 'index.php?route=tshirtecommerce/ideas" > '.$data['site_url']. 'index.php?route=tshirtecommerce/ideas'.'</a>';

		$settings['tshirtecommerce_hide_addtocart'] = $data['tshirtecommerce_hide_addtocart'];
		$settings['tshirtecommerce_hide_addtocart_list'] = $data['tshirtecommerce_hide_addtocart_list'];
		$settings['tshirtecommerce_hide_design_button'] = $data['tshirtecommerce_hide_design_button'];

		$this->model_setting_setting->editSetting('tshirtecommerce', $settings);

		$data['tshirtecommerce_addon_campaign_yes'] = $this->language->get('tshirtecommerce_addon_campaign_yes');
		$data['tshirtecommerce_addon_campaign_no'] = $this->language->get('tshirtecommerce_addon_campaign_no');

		$data['tshirtecommerce_addon_campaign_label_setting_text'] = $this->language->get('tshirtecommerce_addon_campaign_label_setting_text');
		$data['tshirtecommerce_quicksetup_label'] = $this->language->get('tshirtecommerce_quicksetup_label');
		$data['tshirtecommerce_general_label'] = $this->language->get('tshirtecommerce_general_label');
		$data['tshirtecommerce_product_default_label'] = $this->language->get('tshirtecommerce_product_default_label');
		$data['tshirtecommerce_ideas_categories_default_label'] = $this->language->get('tshirtecommerce_ideas_categories_default_label');
		$data['tshirtecommerce_product_default_label_help'] = $this->language->get('tshirtecommerce_product_default_label_help');
		$data['tshirtecommerce_ideas_categories_default_label_help'] = $this->language->get('tshirtecommerce_ideas_categories_default_label_help');
		$data['tshirtecommerce_product_msg'] = $this->language->get('tshirtecommerce_product_msg');
		$data['tshirtecommerce_ideas_categories_msg'] = $this->language->get('tshirtecommerce_ideas_categories_msg');
		$data['tshirtecommerce_enable_taxes_label'] = $this->language->get('tshirtecommerce_enable_taxes_label');
		$data['tshirtecommerce_enable_taxes_label_help'] = $this->language->get('tshirtecommerce_enable_taxes_label_help');
		$data['tshirtecommerce_hide_add2cart_label'] = $this->language->get('tshirtecommerce_hide_add2cart_label');
		$data['tshirtecommerce_hide_add2cart_label_help'] = $this->language->get('tshirtecommerce_hide_add2cart_label_help');
		$data['tshirtecommerce_donwloadable_design_label'] = $this->language->get('tshirtecommerce_donwloadable_design_label');
		$data['tshirtecommerce_donwloadable_design_label_help'] = $this->language->get('tshirtecommerce_donwloadable_design_label_help');
		$data['tshirtecommerce_purchase_code_label'] = $this->language->get('tshirtecommerce_purchase_code_label');
		$data['tshirtecommerce_purchase_code_label_help'] = $this->language->get('tshirtecommerce_purchase_code_label_help');
		$data['tshirtecommerce_purchase_code_label_help_2'] = $this->language->get('tshirtecommerce_purchase_code_label_help_2');
		$data['tshirtecommerce_verified_lable'] = $this->language->get('tshirtecommerce_verified_lable');
		$data['tshirtecommerce_not_verified_lable'] = $this->language->get('tshirtecommerce_not_verified_lable');
		$data['tshirtecommerce_custom_button_label'] = $this->language->get('tshirtecommerce_custom_button_label');
		$data['tshirtecommerce_custom_button_label_fc'] = $this->language->get('tshirtecommerce_custom_button_label_fc');
		$data['tshirtecommerce_custom_button_label_fz'] = $this->language->get('tshirtecommerce_custom_button_label_fz');
		$data['tshirtecommerce_custom_button_label_pd'] = $this->language->get('tshirtecommerce_custom_button_label_pd');
		$data['tshirtecommerce_custom_button_label_bd'] = $this->language->get('tshirtecommerce_custom_button_label_bd');
		$data['tshirtecommerce_custom_button_label_bc'] = $this->language->get('tshirtecommerce_custom_button_label_bc');
		$data['tshirtecommerce_custom_button_label_bs'] = $this->language->get('tshirtecommerce_custom_button_label_bs');
		$data['tshirtecommerce_custom_button_label_sl'] = $this->language->get('tshirtecommerce_custom_button_label_sl');
		$data['tshirtecommerce_custom_button_label_dt'] = $this->language->get('tshirtecommerce_custom_button_label_dt');
		$data['tshirtecommerce_custom_button_label_bw'] = $this->language->get('tshirtecommerce_custom_button_label_bw');
		$data['tshirtecommerce_custom_button_label_bg'] = $this->language->get('tshirtecommerce_custom_button_label_bg');
		$data['tshirtecommerce_logo_loading_label'] = $this->language->get('tshirtecommerce_logo_loading_label');
		$data['tshirtecommerce_logo_loading_label_help'] = $this->language->get('tshirtecommerce_logo_loading_label_help');
		$data['tshirtecommerce_seo_action_save_label'] = $this->language->get('tshirtecommerce_seo_action_save_label');
		
		$data['tshirtecommerce_send_order_label'] = $this->language->get('tshirtecommerce_send_order_label');
		$data['tshirtecommerce_auto_send_order_label'] = $this->language->get('tshirtecommerce_auto_send_order_label');
		$data['tshirtecommerce_list_send_status_label'] = $this->language->get('tshirtecommerce_list_send_status_label');
		$data['tshirtecommerce_auto_order_description'] = $this->language->get('tshirtecommerce_send_order_description');
		$data['tshirtecommerce_auto_send_order_description'] = $this->language->get('tshirtecommerce_auto_send_order_description');
		
		/*get order status*/
		$data['order_statuses'] = array();
		$filter_data = array(
			'sort'  => 'name',
			'order' => 'ASC',
			'start' => 1,
			'limit' => $this->config->get('config_limit_admin')
		);
		$this->load->model('localisation/order_status');
		$this->load->language('localisation/order_status');

		$results = $this->model_localisation_order_status->getOrderStatuses($filter_data);

		foreach ($results as $result) {
			$data['order_statuses'][] = array(
				'order_status_id' => $result['order_status_id'],
				'name'            => $result['name'] . (($result['order_status_id'] == $this->config->get('config_order_status_id')) ? $this->language->get('text_default') : null),
			);
		}
		/*get order status*/
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['quicksetup'] = $this->getQuicksetup();

		$this->response->setOutput($this->load->view('extension/module/tshirtecommerce', $data));
	}
	
	public function install()
	{
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('tshirtecommerce');
		$settings['tshirtecommerce_menu'] = 1;
		$settings['tshirtecommerce_code'] = '';
		$settings['design_downloadable']  = 0;
		
		$this->model_setting_setting->editSetting('tshirtecommerce', $settings);
		
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."tshirtdesign_order` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`order_product_id` int(11) NOT NULL,
			`options` text NOT NULL,  
			PRIMARY KEY (`id`)
		)");
		
		// add field to product table
		$check = false;
		$result = $this->db->query( "DESCRIBE `".DB_PREFIX."product`" );
		foreach ($result->rows as $row) {
			if ($row['Field'] == 'design_product_id') {
				$check = true;
				break;
			}
		}
		
		if ($check === false) {
			$sql = "
				ALTER TABLE `".DB_PREFIX."product` 
				ADD `design_product_id` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
			";
			$this->db->query( $sql );
			
			$sql = "
				ALTER TABLE `".DB_PREFIX."product` 
				ADD `design_product_title_img` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
			";
			$this->db->query( $sql );
		}
	}

	protected function validate()
	{
		$this->language->load('extension/module/tshirtecommerce');
		
		if (!$this->user->hasPermission('modify', 'extension/module/tshirtecommerce')) $this->error['warning'] = $this->language->get('error_permission');
		if (!$this->error) {
			return true;
		}  else {
			return false;
		}	
	}

	protected function fnStrtolower($str)
	{
		if (is_array($str)) {
	            return false;
	        }
	        if (function_exists('mb_strtolower')) {
	            return mb_strtolower($str, 'utf-8');
	        }
	        return strtolower($str);
	}

	protected function openURL($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
	{
	    if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
	        $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)));
	    }
	    if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
	        return @file_get_contents($url, $use_include_path, $stream_context);
	    } elseif (function_exists('curl_init')) {
	        $curl = curl_init();
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($curl, CURLOPT_URL, $url);
	        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
	        curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	        if ($stream_context != null) {
	            $opts = stream_context_get_options($stream_context);
	            if (isset($opts['http']['method']) && $this->fnStrtolower($opts['http']['method']) == 'post') {
	                curl_setopt($curl, CURLOPT_POST, true);
	                if (isset($opts['http']['content'])) {
	                    parse_str($opts['http']['content']);
	                    curl_setopt($curl, CURLOPT_POSTFIELDS, array());
	                }
	            }
	        }
	        $content = curl_exec($curl);
	        curl_close($curl);
	        return $content;
	    } else {
	        return false;
	    }
	}

	protected function getQuicksetup()
	{
		$array = array(
			'layouts' 		=> array(),
			'languages' 	=> array(),
			'ajax_tool' 	=> 'index.php?route=tshirtecommerce/ajax/tool&user_token='.$this->session->data['user_token'],
			'ajax_clipart' 	=> 'index.php?route=tshirtecommerce/ajax/clipart&user_token='.$this->session->data['user_token'],
			'ajax_import' 	=> 'index.php?route=tshirtecommerce/ajax/import&user_token='.$this->session->data['user_token'],
		);

		$layout_json = dirname(DIR_SYSTEM).'/tshirtecommerce/data/layouts.json';

		if (file_exists($layout_json)) {
			$layouts = json_decode(@file_get_contents($layout_json), true);

			if ($layouts !== false && count($layouts)) $array['layouts'] = $layouts;
		}

		$language_json = dirname(DIR_SYSTEM).'/tshirtecommerce/data/languages.json';

		if (file_exists($language_json)) {
			$languages = json_decode(@file_get_contents($language_json), true);
			
			if ($languages !== false && count($languages)) $array['languages'] = $languages;
		}

		return $array;
	}

	protected function seo_friendly_url($string)
	{
		$string = str_replace(' ', '-', $string);
	    $string = str_replace(array('[\', \']'), '', $string);
	    $string = preg_replace('/\[.*\]/U', '', $string);
	    $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
	    $string = htmlentities($string, ENT_COMPAT, 'utf-8');
	    $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
	    $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
	    return strtolower(trim($string, '-'));
	}
}