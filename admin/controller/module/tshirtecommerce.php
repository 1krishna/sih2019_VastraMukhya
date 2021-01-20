<?php 
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		2016-05-17
 * 
 * API 			4.1.5
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ControllerModuleTshirtecommerce extends Controller
{
	private $error;
	
	public function index()
	{
		
		$this->install();
		
		$this->load->language('module/tshirtecommerce');
		$this->load->model('setting/setting');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->ini_jobs();

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
			$data['tshirtecommerce_customize_design_btn'] = 'color: #ffffff;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);background-color: #5bb75b;background-image: linear-gradient(to bottom, #62c462, #51a351);background-repeat: repeat-x;border-color: #51a351 #51a351 #387038;padding: 10px 16px;font-size: 15px;border-radius: 4px;box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);width: 100%;display: block;line-height: 1.3333333;font-weight: 400;text-align: center;';

			if(isset($this->request->post['tshirtecommerce_customize_design_btn'])) {
				$data['tshirtecommerce_customize_design_btn'] = $this->request->post['tshirtecommerce_customize_design_btn'];
			}

			// Validate Shcedule Jobs
			$data['tshirtecommerce_jobs'] = isset($this->request->post['tshirtecommerce_jobs']) ? $this->request->post['tshirtecommerce_jobs'] : 0;
			$data['tshirtecommerce_jobs_plan'] = isset($this->request->post['tshirtecommerce_jobs_plan']) ? $this->request->post['tshirtecommerce_jobs_plan'] : 0;

			if ($data['tshirtecommerce_jobs_plan'] == 0) {
				$plan_hours = 1;
				$plan_daily = 0;
				$plan = isset($this->request->post['tshirtecommerce_jobs_plan_hours']) ? (int)$this->request->post['tshirtecommerce_jobs_plan_hours'] : 1;
			} elseif ($data['tshirtecommerce_jobs_plan'] == 1) {
				$plan_hours = 0;
				$plan_daily = 1;
				$plan = 0;
			}

			$days = isset($this->request->post['tshirtecommerce_jobs_plan_daily']) ? $this->request->post['tshirtecommerce_jobs_plan_daily'] : 0;
			$hours = isset($this->request->post['tshirtecommerce_jobs_plan_daily_hours']) ? $this->request->post['tshirtecommerce_jobs_plan_daily_hours'] : 0;
			$minutes = isset($this->request->post['tshirtecommerce_jobs_plan_daily_minutes']) ? $this->request->post['tshirtecommerce_jobs_plan_daily_minutes'] : 0;

			// Check exits with id = 1
			$totals = $this->db->query("
				SELECT COUNT(*) AS total 
				FROM `".DB_PREFIX."tshirtecommerce_jobs` 
				ORDER BY `id` DESC LIMIT 1
			");
			$total = $totals->row['total'];
			if ($data['tshirtecommerce_jobs'] == 1) {
				if ($total == 0) {
					// Insert task to database if not exist
					$this->db->query("
						INSERT INTO `".DB_PREFIX."tshirtecommerce_jobs`(`name`,`plan_hours`,`plan_daily`,`days`,`hours`,`minutes`,`plan`,`last_sync`) 
						VALUES('Auto sync store',".$plan_hours.",".$plan_daily.",".$days.",".$hours.",".$minutes.",".$plan.",NOW())
					");
				} else {
					// Get max id
					$sqlmax = $this->db->query("SELECT MAX(`id`) AS id FROM `".DB_PREFIX."tshirtecommerce_jobs`");
					$id_max = $sqlmax->row['id'];
					$date = new DateTime();
					// Update task to database if exist
					$this->db->query("UPDATE `".DB_PREFIX."tshirtecommerce_jobs` SET
						`plan_hours` = ".$plan_hours.",
						`plan_daily` = ".$plan_daily.",
						`days` = ".$days.",
						`hours` = ".$hours.",
						`minutes` = ".$minutes.",
						`plan` = ".$plan.",
						`last_sync` = NOW() 
						WHERE `id` = ".$id_max."
					");
				}
			}

			$this->model_setting_setting->editSetting('tshirtecommerce', $this->request->post);
			$data['product_id'] = (isset($this->request->post['tshirtecommerce_product']) ? $this->request->post['tshirtecommerce_product'] : 0);
			$data['tshirtecommerce_hide_addtocart'] = isset($this->request->post['tshirtecommerce_hide_addtocart']) ? $this->request->post['tshirtecommerce_hide_addtocart'] : 0;
			$data['tshirtecommerce_downloadable'] = isset($this->request->post['tshirtecommerce_downloadable']) ? $this->request->post['tshirtecommerce_downloadable'] : 0;
			$data['tshirtecommerce_code'] = isset($this->request->post['tshirtecommerce_code']) ? $this->request->post['tshirtecommerce_code'] : '';

			// Adding custom design button on Home page
			$data['tshirtecommerce_home'] = isset($this->request->post['tshirtecommerce_home']) ? $this->request->post['tshirtecommerce_home'] : 0;
			$data['tshirtecommerce_search'] = isset($this->request->post['tshirtecommerce_search']) ? $this->request->post['tshirtecommerce_search'] : 0;
			$data['tshirtecommerce_category'] = isset($this->request->post['tshirtecommerce_category']) ? $this->request->post['tshirtecommerce_category'] : 0;
			$data['tshirtecommerce_compare'] = isset($this->request->post['tshirtecommerce_compare']) ? $this->request->post['tshirtecommerce_compare'] : 0;
			$data['tshirtecommerce_special'] = isset($this->request->post['tshirtecommerce_special']) ? $this->request->post['tshirtecommerce_special'] : 0;
			$data['tshirtecommerce_manufacturer'] = isset($this->request->post['tshirtecommerce_manufacturer']) ? $this->request->post['tshirtecommerce_manufacturer'] : 0;
			$data['tshirtecommerce_feature'] = isset($this->request->post['tshirtecommerce_feature']) ? $this->request->post['tshirtecommerce_feature'] : 0;
			$data['tshirtecommerce_bestseller'] = isset($this->request->post['tshirtecommerce_bestseller']) ? $this->request->post['tshirtecommerce_bestseller'] : 0;
			$data['tshirtecommerce_lastest'] = isset($this->request->post['tshirtecommerce_lastest']) ? $this->request->post['tshirtecommerce_lastest'] : 0;
			$data['tshirtecommerce_special_m'] = isset($this->request->post['tshirtecommerce_special_m']) ? $this->request->post['tshirtecommerce_special_m'] : 0;

			if (isset($this->request->post['tshirtecommerce_logo_loading']) && !empty($this->request->post['tshirtecommerce_logo_loading']) && $this->request->post['tshirtecommerce_logo_loading'] != null) {
				$data['tshirtecommerce_logo_loading'] = $this->request->post['tshirtecommerce_logo_loading'];
			} else {
				$data['tshirtecommerce_logo_loading'] = $data['site_url'].'/tshirtecommerce/assets/images/logo-loading.png';
			}
			if (strpos($data['tshirtecommerce_logo_loading'], 'tshirtecommerce/assets/images/logo-loading.png') === false 
				&& strpos($data['tshirtecommerce_logo_loading'], $data['site_url']) === false) {
				$data['tshirtecommerce_logo_loading'] = $data['site_url'].'/image/'.$data['tshirtecommerce_logo_loading'];
			}
			$data['tshirtecommerce_text_loading'] = isset($this->request->post['tshirtecommerce_text_loading']) ? $this->request->post['tshirtecommerce_text_loading'] : 'The Design Tool is Loading...';
			$data['tshirtecommerce_allow_taxes'] = isset($this->request->post['tshirtecommerce_allow_taxes']) ? $this->request->post['tshirtecommerce_allow_taxes'] : 1;
			$data['tshirtecommerce_allow_images'] = isset($this->request->post['tshirtecommerce_allow_images']) ? $this->request->post['tshirtecommerce_allow_images'] : 1;

			// Check Code
			$data['tshirtecommerce_verify'] = array();
			if (!empty($data['tshirtecommerce_code'])) {
				$json_str = $this->openURL('http://updates.tshirtecommerce.com/verify_purchase.php?code='.$data['tshirtecommerce_code'].'&platform=opencart');
				if ($json_str !== false) $data['tshirtecommerce_verify'] = json_decode($json_str, true);
			}

			if (isset($this->request->post['tshirtecommerce_customize_design_text'])) {
				foreach ($this->request->post['tshirtecommerce_customize_design_text'] as $key => $value) {
					foreach ($data['languages'] as &$language) {
						if ($language['code'] == $key) {
							$language['tshirtecommerce_custom_text'] = $value;
						}
					}
				}
			}
			$this->session->data['success'] = $this->language->get('text_success');
		} else {
			$data['tshirtecommerce_allow_images'] = (null !== $this->config->get('tshirtecommerce_allow_images')) ? $this->config->get('tshirtecommerce_allow_images') : 1;
			$data['tshirtecommerce_allow_taxes'] = (null !== $this->config->get('tshirtecommerce_allow_taxes')) ? $this->config->get('tshirtecommerce_allow_taxes') : 1;
			$data['tshirtecommerce_logo_loading'] = (null !== $this->config->get('tshirtecommerce_logo_loading')) ? $this->config->get('tshirtecommerce_logo_loading') : $data['site_url'].'/tshirtecommerce/assets/images/logo-loading.png';
			$data['tshirtecommerce_text_loading'] = (null !== $this->config->get('tshirtecommerce_text_loading')) ? $this->config->get('tshirtecommerce_text_loading') : 'The Design Tool is Loading...';
			$data['product_id'] = $this->config->get('tshirtecommerce_product');
			$data['tshirtecommerce_downloadable'] = (null !== $this->config->get('tshirtecommerce_downloadable')) ? $this->config->get('tshirtecommerce_downloadable') : 0;
			$data['tshirtecommerce_hide_addtocart'] = (null !== $this->config->get('tshirtecommerce_hide_addtocart')) ? $this->config->get('tshirtecommerce_hide_addtocart') : 0;
			$data['tshirtecommerce_code'] = (null !== $this->config->get('tshirtecommerce_code')) ? $this->config->get('tshirtecommerce_code') : '';
			$custom_design_default_css = 'color: #ffffff;text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);background-color: #5bb75b;background-image: linear-gradient(to bottom, #62c462, #51a351);background-repeat: repeat-x;border-color: #51a351 #51a351 #387038;padding: 10px 16px;font-size: 15px;border-radius: 4px;box-shadow: inset 0 1px 0 rgba(255,255,255,.2), 0 1px 2px rgba(0,0,0,.05);width: 100%;display: block;line-height: 1.3333333;font-weight: 400;text-align: center;';
			$data['tshirtecommerce_customize_design_btn'] = (null !== $this->config->get('tshirtecommerce_customize_design_btn')) ? $this->config->get('tshirtecommerce_customize_design_btn') : $custom_design_default_css;
			$data['tshirtecommerce_jobs'] = (null !== $this->config->get('tshirtecommerce_jobs')) ? $this->config->get('tshirtecommerce_jobs') : 0;
			$data['tshirtecommerce_jobs_plan'] = (null !== $this->config->get('tshirtecommerce_jobs_plan')) ? $this->config->get('tshirtecommerce_jobs_plan') : 1;

			// Adding custom design button on tshirtecommerce_16635.xml
			$data['tshirtecommerce_home'] = (null !== $this->config->get('tshirtecommerce_home')) ? $this->config->get('tshirtecommerce_home') : 0;
			$data['tshirtecommerce_search'] = (null !== $this->config->get('tshirtecommerce_search')) ? $this->config->get('tshirtecommerce_search') : 0;
			$data['tshirtecommerce_category'] = (null !== $this->config->get('tshirtecommerce_category')) ? $this->config->get('tshirtecommerce_category') : 0;
			$data['tshirtecommerce_compare'] = (null !== $this->config->get('tshirtecommerce_compare')) ? $this->config->get('tshirtecommerce_compare') : 0;
			$data['tshirtecommerce_special'] = (null !== $this->config->get('tshirtecommerce_special')) ? $this->config->get('tshirtecommerce_special') : 0;
			$data['tshirtecommerce_manufacturer'] = (null !== $this->config->get('tshirtecommerce_manufacturer')) ? $this->config->get('tshirtecommerce_manufacturer') : 0;
			$data['tshirtecommerce_feature'] = (null !== $this->config->get('tshirtecommerce_feature')) ? $this->config->get('tshirtecommerce_feature') : 0;
			$data['tshirtecommerce_bestseller'] = (null !== $this->config->get('tshirtecommerce_bestseller')) ? $this->config->get('tshirtecommerce_bestseller') : 0;
			$data['tshirtecommerce_lastest'] = (null !== $this->config->get('tshirtecommerce_lastest')) ? $this->config->get('tshirtecommerce_lastest') : 0;
			$data['tshirtecommerce_special_m'] = (null !== $this->config->get('tshirtecommerce_special_m')) ? $this->config->get('tshirtecommerce_special_m') : 0;

			// Check Code
			$data['tshirtecommerce_verify'] = array();
			if (!empty($data['tshirtecommerce_code'])) {
				$json_str = $this->openURL('http://updates.tshirtecommerce.com/verify_purchase.php?code='.$data['tshirtecommerce_code'].'&platform=opencart');
				if ($json_str !== false) $data['tshirtecommerce_verify'] = json_decode($json_str, true);
			}
			$tshirtecommerce_customize_design_text = $this->config->get('tshirtecommerce_customize_design_text');
			if (null !== $tshirtecommerce_customize_design_text && !empty($tshirtecommerce_customize_design_text)) {
				if (!is_array($tshirtecommerce_customize_design_text)) {
					$tshirtecommerce_customize_design_texts = @json_decode($tshirtecommerce_customize_design_text, true);
				} else {
					$tshirtecommerce_customize_design_texts = $tshirtecommerce_customize_design_text;
				}
				
				foreach ($data['languages'] as &$language) {
					foreach ($tshirtecommerce_customize_design_texts as $key => $value) {
						if ($language['code'] == $key) $language['tshirtecommerce_custom_text'] = $value;
					}
				}
			}
		}

		$totals = $this->db->query("SELECT * FROM `".DB_PREFIX."tshirtecommerce_jobs` ORDER BY `id` DESC LIMIT 1");

		$data['tshirtecommerce_jobs_plan_hours'] = isset($totals->row['plan_hours']) ? $totals->row['plan'] : 1;
		$data['tshirtecommerce_days'] = (isset($totals->row['plan_daily']) && isset($totals->row['days'])) ? $totals->row['days'] : 0;
		$data['tshirtecommerce_hours'] = (isset($totals->row['plan_daily']) && isset($totals->row['hours'])) ? (int)$totals->row['hours'] : 0;
		$data['tshirtecommerce_minutes'] = (isset($totals->row['plan_daily']) && isset($totals->row['minutes'])) ? (int)$totals->row['minutes'] : 0;

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
			'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], true),
			'separator' => false
		);	
		$data['link'] = $this->url->link('module/tshirtecommerce', 'token='.$this->session->data['token'], true);
	
		// get all product design
		$this->load->model('tshirtecommerce/order');
		$products = $this->model_tshirtecommerce_order->getProducts();
		$data['products'] = $products;

		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('tshirtecommerce');
		$settings['tshirtecommerce_menu'] = 1;
		$settings['tshirtecommerce_code'] = $data['tshirtecommerce_code'];
		$settings['tshirtecommerce_logo_loading'] = $data['tshirtecommerce_logo_loading'];
		$settings['tshirtecommerce_text_loading'] = $data['tshirtecommerce_text_loading'];
		$settings['tshirtecommerce_allow_taxes'] = $data['tshirtecommerce_allow_taxes'];
		$settings['tshirtecommerce_allow_images'] = $data['tshirtecommerce_allow_images'];

		$data['tshirtecommerce_customize_design_text'] = array();
		foreach ($data['languages'] as &$language) {
			$data['tshirtecommerce_customize_design_text'][$language['code']] = $language['tshirtecommerce_custom_text'];
		}
		$settings['tshirtecommerce_customize_design_text'] = @json_encode($data['tshirtecommerce_customize_design_text']);

		$this->model_setting_setting->editSetting('tshirtecommerce', $settings);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['quicksetup'] = $this->getQuicksetup();

		$this->response->setOutput($this->load->view('module/tshirtecommerce', $data));
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
		$this->language->load('module/tshirtecommerce');
		
		if (!$this->user->hasPermission('modify', 'module/tshirtecommerce')) $this->error['warning'] = $this->language->get('error_permission');
		if (!$this->error) {
			return true;
		}  else {
			return false;
		}	
	}

	private function openURL($url)
	{		
		if (ini_get('allow_url_fopen')) {
			$data = @file_get_contents($url);
			if ($data == false) return false;
		} else {		
			$ch = @curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = @curl_exec($ch);
			@curl_close($ch);
		}
		return $data;
	}

	protected function ini_jobs()
	{
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tshirtecommerce_jobs_logs` (
			`id` int(11) 		NOT NULL AUTO_INCREMENT,
			`date_start` 		datetime,
			`date_finish` 		datetime,
			PRIMARY KEY 		(`id`)
		)");

		$this->db->query(" CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "tshirtecommerce_jobs` (
			`id` 				int(11) NOT NULL AUTO_INCREMENT,
			`name` 				VARCHAR(128)        DEFAULT NULL,
			`plan_minutes` 		tinyint(1) NOT NULL DEFAULT '0',
			`plan_hours` 		tinyint(1) NOT NULL DEFAULT '0',
			`plan_daily` 		tinyint(1) NOT NULL DEFAULT '1',
			`plan_weekly` 		tinyint(1) NOT NULL DEFAULT '0',
			`days`   			int(2) 				DEFAULT '1',
			`hours`   			int(2) 				DEFAULT '0',
			`minutes`   		int(2) 				DEFAULT '0',
			`plan`              int(10) 			DEFAULT '1',
			`run_only_once` 	tinyint(1) NOT NULL DEFAULT '0',
			`last_sync`			datetime            DEFAULT NULL,
			`status`			tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY 		(`id`)
		)");
	}

	protected function getQuicksetup()
	{
		$array = array(
			'layouts' 		=> array(),
			'languages' 	=> array(),
			'ajax_tool' 	=> 'index.php?route=tshirtecommerce/ajax/tool&token='.$this->session->data['token'],
			'ajax_clipart' 	=> 'index.php?route=tshirtecommerce/ajax/clipart&token='.$this->session->data['token'],
			'ajax_import' 	=> 'index.php?route=tshirtecommerce/ajax/import&token='.$this->session->data['token'],
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
}

?>