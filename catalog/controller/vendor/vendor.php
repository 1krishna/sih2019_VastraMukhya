<?php
class ControllerVendorVendor extends Controller {
	private $error = array();

	public function index() {
		if ($this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/success', '', true));
		}
		
		$this->load->language('vendor/vendor');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
		
		$this->load->model('vendor/vendor');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_vendor_vendor->addVendor($this->request->post);
			$this->vendor->login($this->request->post['email'], $this->request->post['password']);			
			$this->response->redirect($this->url->link('vendor/success'));
		}
		
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/dashboard', '', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/vendor', '', true)
		);
		
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('vendor/login', '', true));
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		$data['entry_firstname'] 	= $this->language->get('entry_firstname');
		$data['entry_lastname'] 	= $this->language->get('entry_lastname');
		$data['entry_email'] 		= $this->language->get('entry_email');
		$data['entry_telephone'] 	= $this->language->get('entry_telephone');
		$data['entry_fax'] 			= $this->language->get('entry_fax');
		$data['entry_company'] 		= $this->language->get('entry_company');
		$data['entry_address_1'] 	= $this->language->get('entry_address_1');
		$data['entry_address_2'] 	= $this->language->get('entry_address_2');
		$data['entry_postcode'] 	= $this->language->get('entry_postcode');
		$data['entry_city'] 		= $this->language->get('entry_city');
		$data['entry_country'] 		= $this->language->get('entry_country');
		$data['entry_zone'] 		= $this->language->get('entry_zone');
		$data['entry_newsletter'] 	= $this->language->get('entry_newsletter');
		$data['entry_password'] 	= $this->language->get('entry_password');
		$data['entry_confirm'] 		= $this->language->get('entry_confirm');
		$data['entry_about'] 		= $this->language->get('entry_about');
		$data['entry_image'] 		= $this->language->get('entry_image');
		$data['entry_display_name'] = $this->language->get('entry_display_name');
		$data['entry_bankname']  		= $this->language->get('entry_bankname');
		$data['entry_bnumber']  		= $this->language->get('entry_bnumber');
		$data['entry_swiftcode']  		= $this->language->get('entry_swiftcode');
		$data['entry_aname']  			= $this->language->get('entry_aname');
		$data['entry_anumber']  		= $this->language->get('entry_anumber');
		$data['entry_Emailid']  		= $this->language->get('entry_Emailid');
		$data['entry_method']  			= $this->language->get('entry_method');
		$data['text_bank']  			= $this->language->get('text_bank');
		$data['text_paypal']  			= $this->language->get('text_paypal');
		
		$data['entry_storename'] 		= $this->language->get('entry_storename');
		$data['entry_description'] 		= $this->language->get('entry_description');
		$data['entry_shippingpolicy'] 	= $this->language->get('entry_shippingpolicy');
		$data['entry_returnpolicy'] 	= $this->language->get('entry_returnpolicy');
		$data['entry_metakeyword'] 		= $this->language->get('entry_metakeyword');
		$data['entry_metadescription'] 	= $this->language->get('entry_metadescription');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_phone'] 			= $this->language->get('entry_phone');
		$data['entry_address'] 			= $this->language->get('entry_address');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_detail'] 			= $this->language->get('entry_detail');
		$data['entry_tax'] 				= $this->language->get('entry_tax');
		$data['entry_charges'] 			= $this->language->get('entry_charges');
		$data['entry_url'] 				= $this->language->get('entry_url');
		$data['entry_logo'] 			= $this->language->get('entry_logo');
		$data['entry_banner'] 			= $this->language->get('entry_banner');
		$data['entry_store_about'] 		= $this->language->get('entry_store_about');
		$data['entry_mapurl'] 		    = $this->language->get('entry_mapurl');
		$data['button_upload'] 			= $this->language->get('button_upload');
		$data['button_banner'] 			= $this->language->get('button_banner');
		$data['button_add'] 			= $this->language->get('button_add');
		$data['tab_general'] 			= $this->language->get('tab_general');
		$data['tab_data'] 			    = $this->language->get('tab_data');
		
		$data['tab_seller'] 		    = $this->language->get('tab_seller');
		$data['tab_generalstore'] 		= $this->language->get('tab_generalstore');
		$data['tab_datastore'] 			= $this->language->get('tab_datastore');
		$data['tab_payment'] 			= $this->language->get('tab_payment');

		$data['help_product'] = $this->language->get('help_product');
		
		$data['button_continue'] 	= $this->language->get('button_continue');
		$data['button_upload'] 		= $this->language->get('button_upload');
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['display_name'])) {
			$data['error_display_name'] = $this->error['display_name'];
		} else {
			$data['error_display_name'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}
		
		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}
		
		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}
		
		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}
		
		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}
		
		if (isset($this->error['city'])) {
			$data['error_city'] = $this->error['city'];
		} else {
			$data['error_city'] = '';
		}
		
		
		if (isset($this->error['country'])) {
			$data['error_country'] = $this->error['country'];
		} else {
			$data['error_country'] = '';
		}
		
		if (isset($this->error['zone'])) {
			$data['error_zone'] = $this->error['zone'];
		} else {
			$data['error_zone'] = '';
		}
		
		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}
		
		if (isset($this->error['confirm'])) {
			$data['error_confirm'] = $this->error['confirm'];
		} else {
			$data['error_confirm'] = '';
		}
		
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['paypal'])) {
			$data['error_paypal'] = $this->error['paypal'];
		} else {
			$data['error_paypal'] = '';
		}

		if (isset($this->error['bank_account_name'])) {
			$data['error_bank_account_name'] = $this->error['bank_account_name'];
		} else {
			$data['error_bank_account_name'] = '';
		}

		if (isset($this->error['bank_account_number'])) {
			$data['error_bank_account_number'] = $this->error['bank_account_number'];
		} else {
			$data['error_bank_account_number'] = '';
		}
		
		$data['action'] = $this->url->link('vendor/vendor', '', true);
		if (isset($this->request->post['display_name'])) {
			$data['display_name'] = $this->request->post['display_name'];
		} else {
			$data['display_name'] = '';
		}
		
		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} else {
			$data['firstname'] = '';
		}
		
		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} else {
			$data['lastname'] = '';
		}
		
		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}
		
		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} else {
			$data['telephone'] = '';
		}
		
		if (isset($this->request->post['fax'])) {
			$data['fax'] = $this->request->post['fax'];
		} else {
			$data['fax'] = '';
		}
		
		if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} else {
			$data['company'] = '';
		}	
		
		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1'];
		} else {
			$data['address_1'] = '';
		}
		
		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2'];
		} else {
			$data['address_2'] = '';
		}
		
		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['shipping_address']['postcode'])) {
			$data['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$data['postcode'] = '';
		}
		
		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} else {
			$data['city'] = '';
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		if (isset($this->request->post['paypal'])) {
			$data['paypal'] = $this->request->post['paypal'];
		} else {
			$data['paypal'] = '';
		}

		if (isset($this->request->post['bank_name'])) {
			$data['bank_name'] = $this->request->post['bank_name'];
		} else {
			$data['bank_name'] = '';
		}

		if (isset($this->request->post['bank_branch_number'])) {
			$data['bank_branch_number'] = $this->request->post['bank_branch_number'];
		} else {
			$data['bank_branch_number'] = '';
		}

		if (isset($this->request->post['bank_swift_code'])) {
			$data['bank_swift_code'] = $this->request->post['bank_swift_code'];
		} else {
			$data['bank_swift_code'] = '';
		}

		if (isset($this->request->post['bank_account_name'])) {
			$data['bank_account_name'] = $this->request->post['bank_account_name'];
		} else {
			$data['bank_account_name'] = '';
		}

		if (isset($this->request->post['bank_account_number'])) {
			$data['bank_account_number'] = $this->request->post['bank_account_number'];
		} else {
			$data['bank_account_number'] = '';
		}
		
		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = (int)$this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$data['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}
		
		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = (int)$this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$data['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$data['zone_id'] = '';
		}
		
		
		
		$this->load->model('localisation/country');
		$data['countries'] = $this->model_localisation_country->getCountries();
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('tool/image');
	 	if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
	  		$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['image'])) {
		  	$data['thumb'] = $this->model_tool_image->resize($store_info['image'], 100, 100);
		} else {
		  	$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
//// Seller Store///
		
		if (isset($this->request->post['bank_detail'])) {
			$data['bank_detail'] = $this->request->post['bank_detail'];
		} else {
			$data['bank_detail'] = '';
		}
		
		if (isset($this->request->post['store_about'])) {
			$data['store_about'] = $this->request->post['store_about'];
		} else {
			$data['store_about'] = '';
		}
		
		if (isset($this->request->post['tax_number'])) {
			$data['tax_number'] = $this->request->post['tax_number'];
		} else {
			$data['tax_number'] = '';
		}
		
		if (isset($this->request->post['shipping_charge'])) {
			$data['shipping_charge'] = $this->request->post['shipping_charge'];
		} else {
			$data['shipping_charge'] = '';
		}
		
		if (isset($this->request->post['keyword'])) {
			$data['keyword'] = $this->request->post['keyword'];
		} else {
			$data['keyword'] = '';
		}
		
		if (isset($this->request->post['map_url'])) {
			$data['map_url'] = $this->request->post['map_url'];
		} else {
			$data['map_url'] = '';
		}
		if (isset($this->request->post['about'])) {
			$data['about'] = $this->request->post['about'];
		} else {
			$data['about'] = '';
		}
		
		
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} else {
			$data['image'] = '';
		}
		
		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} else {
			$data['logo'] = '';
		}
		
		if (isset($this->request->post['store_description'])) {
			$data['store_description'] = $this->request->post['store_description'];
		}  else {
			$data['store_description'] = array();
		}
		
		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} else {
			$data['banner'] = '';
		}
		
		$this->load->model('tool/image');
	 	if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
	  		$data['thumb_logo'] = $this->model_tool_image->resize($this->request->post['logo'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['logo'])) {
		  	$data['thumb_logo'] = $this->model_tool_image->resize($store_info['logo'], 100, 100);
		} else {
		  	$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (isset($this->request->post['banner']) && is_file(DIR_IMAGE . $this->request->post['banner'])) {
	  		$data['thumb_banner'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($store_info) && is_file(DIR_IMAGE . $store_info['banner'])) {
		  	$data['thumb_banner'] = $this->model_tool_image->resize($store_info['banner'], 100, 100);
		} else {
		  	$data['thumb_banner'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
		
//// Seller Store///		
		
		$data['column_left'] 	= $this->load->controller('vendor/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('common/footer');
		$data['header'] 		= $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('vendor/vendor', $data));
	}
	
	private function validate() {
		if ((utf8_strlen(trim($this->request->post['display_name'])) < 1) || (utf8_strlen(trim($this->request->post['display_name'])) > 32)) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}

		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}
		
		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}
		
		$email_info = $this->model_vendor_vendor->getVendorByEmail($this->request->post['email']);

		if (!isset($this->request->get['vendor_id'])) {
			if ($email_info) {
				$this->error['warning'] = $this->language->get('error_email_match');
			}
		} else {
			if ($email_info && ($this->request->get['vendor_id'] != $email_info['vendor_id'])) {
				$this->error['warning'] = $this->language->get('error_email_match');
			}
		}
		
		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}
		
		if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}
		
		if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
			$this->error['city'] = $this->language->get('error_city');
		}
		
		$this->load->model('localisation/country');
		
		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);
		
		
		if ($this->request->post['country_id'] == '') {
			$this->error['country'] = $this->language->get('error_country');
		}
		
		if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
			$this->error['zone'] = $this->language->get('error_zone');
		}
				
		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->language->get('error_password');
		}
		
		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}
		
		foreach ($this->request->post['store_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->request->post['payment_method'] == 'paypal') {
			if ($this->request->post['paypal'] == '') {
				$this->error['paypal'] = $this->language->get('error_paypal');
			}
		} elseif ($this->request->post['payment_method'] == 'banktransfer') {
			if ($this->request->post['bank_account_name'] == '') {
				$this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
			}

			if ($this->request->post['bank_account_number'] == '') {
				$this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
			}
		}
		
		return !$this->error;
	}
	
	public function autocomplete(){
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'firstname';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		$this->load->model('vendor/vendor');
			
		$filter_data = array(
		'sort'  => $sort,
		'order' => $order,
		//'filter_name' => $filter_name,
		'start' => ($page - 1) * $this->config->get('config_limit_admin'),
		'limit' => $this->config->get('config_limit_admin')
		);
		$results=$this->model_vendor_vendor->getVendors($filter_data);

		foreach ($results as $result) {

		$json[] = array(
		'vendor_id'  => $result['vendor_id'],
		'firstname'   => strip_tags(html_entity_decode($result['firstname'], ENT_QUOTES, 'UTF-8'))
		);
		}
		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['firstname'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function upload(){
		$this->load->language('tool/upload');
		$json = array();
		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name'])) {
			// Sanitize the filename
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
			// Validate the filename length
			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 64)) {
				$json['error'] = $this->language->get('error_filename');
			}
			// Allowed file extension types
			$allowed = array();
			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));
			$filetypes = explode("\n", $extension_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Allowed file mime types
			$allowed = array();
			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));
			$filetypes = explode("\n", $mime_allowed);
			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}
			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_filetype');

			}
			// Check to see if any PHP files are trying to be uploaded
			$content = file_get_contents($this->request->files['file']['tmp_name']);
			if (preg_match('/\<\?php/i', $content)) {
				$json['error'] = $this->language->get('error_filetype');
			}
			// Return any upload error
			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		$this->load->model('tool/image');
		if (!$json) {
			$targetDir = DIR_IMAGE.'catalog/';
			$file = $filename;
			$location = $targetDir.$file;
			$location1 = 'catalog/'.$file;
			$location2 = 'catalog/'.$file;
			move_uploaded_file($this->request->files['file']['tmp_name'], $location);
			$json['location1'] =$location1;
			$json['location2'] =$this->model_tool_image->resize($location1, 150, 150);
			$json['success'] = $this->language->get('text_upload');
		}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
	}
	
}