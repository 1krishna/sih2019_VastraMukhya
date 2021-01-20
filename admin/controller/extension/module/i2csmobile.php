<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionModuleI2csmobile extends Controller {
	private $error = array();

	public function index() {
		if(version_compare(VERSION, '2.3') >= 0){
			// 2.3 or later versions
			$this->load->language('extension/module/i2csmobile');
		}else{
			$this->load->language('module/i2csmobile');
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post["i2csmobile_product_category"] = json_encode($this->request->post['product_category']);
			$this->model_setting_setting->editSetting('i2csmobile', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			if(version_compare(VERSION, '2.3') >= 0){
				// 2.3 or later versions
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', 'SSL'));
			}else{
				$this->response->redirect($this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL'));
			}
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		
		$data['tab_settings'] = $this->language->get('tab_settings');
		$data['tab_analytics'] = $this->language->get('tab_analytics');
		$data['tab_push'] = $this->language->get('tab_push');
		$data['tab_auth'] = $this->language->get('tab_auth');
		
		$data['entry_mobile_api'] = $this->language->get('entry_mobile_api');
		$data['entry_mobile_user_group'] = $this->language->get('entry_mobile_user_group');
		$data['entry_main_banner'] = $this->language->get('entry_main_banner');
		$data['entry_offer_banner'] = $this->language->get('entry_offer_banner');
		$data['entry_offers_module_banner'] = $this->language->get('entry_offers_module_banner');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_featured_module'] = $this->language->get('entry_featured_module');
		$data['entry_auth_google'] = $this->language->get('entry_auth_google');
		$data['entry_auth_facebook'] = $this->language->get('entry_auth_facebook');
		$data['entry_auth_twitter'] = $this->language->get('entry_auth_twitter');
		$data['entry_auth_google_id'] = $this->language->get('entry_auth_google_id');
		$data['entry_auth_google_secret'] = $this->language->get('entry_auth_google_secret');
		$data['entry_auth_facebook_id'] = $this->language->get('entry_auth_facebook_id');
		$data['entry_auth_facebook_secret'] = $this->language->get('entry_auth_facebook_secret');
		$data['entry_auth_twitter_key'] = $this->language->get('entry_auth_twitter_key');
		$data['entry_auth_twitter_secret'] = $this->language->get('entry_auth_twitter_secret');
		$data['entry_status'] = $this->language->get('entry_status');
		
		$data['help_mobile_api'] = $this->language->get('help_mobile_api');
		$data['help_mobile_user_group'] = $this->language->get('help_mobile_user_group');
		$data['help_main_banner'] = $this->language->get('help_main_banner');
		$data['help_offer_banner'] = $this->language->get('help_offer_banner');
		$data['help_offers_module_banner'] = $this->language->get('help_offers_module_banner');
		$data['help_category'] = $this->language->get('help_category');
		$data['help_featured_module'] = $this->language->get('help_featured_module');
		$data['help_analytics'] = $this->language->get('help_analytics');
		$data['help_push'] = $this->language->get('help_push');
		$data['help_auth_google'] = $this->language->get('help_auth_google');
		$data['help_auth_facebook'] = $this->language->get('help_auth_facebook');
		$data['help_auth_twitter'] = $this->language->get('help_auth_twitter');
		
		
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/i2csmobile', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		if(version_compare(VERSION, '2.3') >= 0){
			// 2.3 or later versions
			$data['action'] = $this->url->link('extension/module/i2csmobile', 'user_token=' . $this->session->data['user_token'], 'SSL');
			$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');
		}else{
			$data['action'] = $this->url->link('module/i2csmobile', 'user_token=' . $this->session->data['user_token'], 'SSL');
			$data['cancel'] = $this->url->link('extension', 'user_token=' . $this->session->data['user_token'], 'SSL');
		}
		
		/*if (isset($this->request->post['i2csmobile_mobile_api'])) {
			$data['i2csmobile_mobile_api'] = $this->request->post['i2csmobile_mobile_api'];
		} else {
			$data['i2csmobile_mobile_api'] = $this->config->get('i2csmobile_mobile_api');
		}*/
		
		if (isset($this->request->post['i2csmobile_main_banner'])) {
			$data['i2csmobile_main_banner'] = $this->request->post['i2csmobile_main_banner'];
		} else {
			$data['i2csmobile_main_banner'] = $this->config->get('i2csmobile_main_banner');
		}
		
		if (isset($this->request->post['i2csmobile_offer_banner'])) {
			$data['i2csmobile_offer_banner'] = $this->request->post['i2csmobile_offer_banner'];
		} else {
			$data['i2csmobile_offer_banner'] = $this->config->get('i2csmobile_offer_banner');
		}
		
		if (isset($this->request->post['i2csmobile_mobile_user_group'])) {
			$data['i2csmobile_mobile_user_group'] = $this->request->post['i2csmobile_mobile_user_group'];
		} else {
			$data['i2csmobile_mobile_user_group'] = $this->config->get('i2csmobile_mobile_user_group');
		}
		
		if (isset($this->request->post['i2csmobile_offers_module_banner'])) {
			$data['i2csmobile_offers_module_banner'] = $this->request->post['i2csmobile_offers_module_banner'];
		} else {
			$data['i2csmobile_offers_module_banner'] = $this->config->get('i2csmobile_offers_module_banner');
		}
		
		if (isset($this->request->post['i2csmobile_featured_module'])) {
			$data['i2csmobile_featured_module'] = $this->request->post['i2csmobile_featured_module'];
		} else {
			$data['i2csmobile_featured_module'] = $this->config->get('i2csmobile_featured_module');
		}
		
		if (isset($this->request->post['i2csmobile_auth_google_id'])) {
			$data['i2csmobile_auth_google_id'] = $this->request->post['i2csmobile_auth_google_id'];
		} else {
			$data['i2csmobile_auth_google_id'] = $this->config->get('i2csmobile_auth_google_id');
		}
		
		if (isset($this->request->post['i2csmobile_auth_google_secret'])) {
			$data['i2csmobile_auth_google_secret'] = $this->request->post['i2csmobile_auth_google_secret'];
		} else {
			$data['i2csmobile_auth_google_secret'] = $this->config->get('i2csmobile_auth_google_secret');
		}
		
		if (isset($this->request->post['i2csmobile_auth_facebook_id'])) {
			$data['i2csmobile_auth_facebook_id'] = $this->request->post['i2csmobile_auth_facebook_id'];
		} else {
			$data['i2csmobile_auth_facebook_id'] = $this->config->get('i2csmobile_auth_facebook_id');
		}
		
		if (isset($this->request->post['i2csmobile_auth_facebook_secret'])) {
			$data['i2csmobile_auth_facebook_secret'] = $this->request->post['i2csmobile_auth_facebook_secret'];
		} else {
			$data['i2csmobile_auth_facebook_secret'] = $this->config->get('i2csmobile_auth_facebook_secret');
		}
		
		if (isset($this->request->post['i2csmobile_auth_twitter_key'])) {
			$data['i2csmobile_auth_twitter_key'] = $this->request->post['i2csmobile_auth_twitter_key'];
		} else {
			$data['i2csmobile_auth_twitter_key'] = $this->config->get('i2csmobile_auth_twitter_key');
		}
		
		if (isset($this->request->post['i2csmobile_auth_twitter_secret'])) {
			$data['i2csmobile_auth_twitter_secret'] = $this->request->post['i2csmobile_auth_twitter_secret'];
		} else {
			$data['i2csmobile_auth_twitter_secret'] = $this->config->get('i2csmobile_auth_twitter_secret');
		}

		$this->load->model('catalog/category');
		$this->load->model('customer/customer_group');
		$this->load->model('design/banner');
		$this->load->model('setting/module');
		
		$settings = array();
		$settings['i2csmobile_product_category'] = array();
		$settings = $this->model_setting_setting->getSetting('i2csmobile');
		$settings_cats = $settings['i2csmobile_product_category'];
		// Categories
		if (isset($this->request->post['product_category'])) {
			$categories = $this->request->post['product_category'];
		} elseif (isset($settings_cats) && $settings_cats != null&& !empty($settings_cats)&& $settings_cats != "null") {
			$categories = json_decode($settings_cats);
		} else {
			$categories = array();
		}

		$data['product_categories'] = array();
		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name' => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}
		
		$data['featured_modules'] = array();
		$featured_modules = $this->model_setting_module->getModulesByCode("featured");
		foreach ($featured_modules as $featured_module) {
			if ($featured_module) {
				$data['featured_modules'][] = array(
					'module_id' => $featured_module['module_id'],
					'name' => $featured_module['name']
				);
			}
		}
		
		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
		$data['banners'] = $this->model_design_banner->getBanners();
		$data['user_token'] = $this->session->data['user_token'];
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/module/i2csmobile', $data));
	}

	protected function validate() {
		if(version_compare(VERSION, '2.3') >= 0){
			// 2.3 or later versions
			if (!$this->user->hasPermission('modify', 'extension/module/i2csmobile')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
		}else{
			if (!$this->user->hasPermission('modify', 'module/i2csmobile')) {
				$this->error['warning'] = $this->language->get('error_permission');
			}
		}

		return !$this->error;
	}
}