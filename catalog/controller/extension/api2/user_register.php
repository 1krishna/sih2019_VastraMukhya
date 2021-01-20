<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2UserRegister extends Controller {
	private $error = array();

	public function index() {
		$json = array();
		
		if ($this->customer->isLogged()) {
			return;
		}

		$this->load->language('account/register');

		$this->load->model('account/customer');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->request->post['agree'] = 1;
			
			$i2csmobile_mobile_user_group = $this->config->get('i2csmobile_mobile_user_group');
			if (isset($this->request->post['customer_group_id']) && !empty($this->request->post['customer_group_id'])) {
				
			} else if(isset($i2csmobile_mobile_user_group)) {
				$this->request->post['customer_group_id'] = $this->config->get('i2csmobile_mobile_user_group');
			} else {
				$this->request->post['customer_group_id'] = $this->config->get('config_customer_group_id');
			}
			
			if(!isset($this->request->post["company"]))
				$this->request->post["company"] = "";
			
			if(!isset($this->request->post["fax"]))
				$this->request->post["fax"] = "";
			
			$customer_id = $this->model_account_customer->addCustomer($this->request->post);

			// Clear any previous login attempts for unregistered accounts.
			$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);

			$this->customer->login($this->request->post['email'], $this->request->post['password']);

			unset($this->session->data['guest']);

			// Add to activity log
			$this->load->model('account/activity');

			$activity_data = array(
				'customer_id' => $customer_id,
				'name'        => $this->request->post['firstname'] . ' ' . $this->request->post['lastname']
			);

			$this->model_account_activity->addActivity('register', $activity_data);
			
			$json['customer_info'] = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
		}

		if (isset($this->error['warning'])) {
			$json['error_warning'] = $this->error['warning'];
		} else {
			$json['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$json['error_firstname'] = $this->error['firstname'];
		} else {
			$json['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$json['error_lastname'] = $this->error['lastname'];
		} else {
			$json['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$json['error_email'] = $this->error['email'];
		} else {
			$json['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$json['error_telephone'] = $this->error['telephone'];
		} else {
			$json['error_telephone'] = '';
		}

		if (isset($this->error['address_1'])) {
			$json['error_address_1'] = $this->error['address_1'];
		} else {
			$json['error_address_1'] = '';
		}

		if (isset($this->error['city'])) {
			$json['error_city'] = $this->error['city'];
		} else {
			$json['error_city'] = '';
		}

		if (isset($this->error['postcode'])) {
			$json['error_postcode'] = $this->error['postcode'];
		} else {
			$json['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$json['error_country'] = $this->error['country'];
		} else {
			$json['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$json['error_zone'] = $this->error['zone'];
		} else {
			$json['error_zone'] = '';
		}

		if (isset($this->error['custom_field'])) {
			$json['error_custom_field'] = $this->error['custom_field'];
		} else {
			$json['error_custom_field'] = array();
		}

		if (isset($this->error['password'])) {
			$json['error_password'] = $this->error['password'];
		} else {
			$json['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$json['error_confirm'] = $this->error['confirm'];
		} else {
			$json['error_confirm'] = '';
		}

		$i2csmobile_mobile_user_group = $this->config->get('i2csmobile_mobile_user_group');
		if (isset($this->request->post['customer_group_id']) && !empty($this->request->post['customer_group_id'])) {
			$json['customer_group_id'] = $this->request->post['customer_group_id'];
		} else if(isset($i2csmobile_mobile_user_group)) {
			$json['customer_group_id'] = $this->config->get('i2csmobile_mobile_user_group');
		} else {
			$json['customer_group_id'] = $this->config->get('config_customer_group_id');
		}

		if (isset($this->request->post['firstname'])) {
			$json['firstname'] = $this->request->post['firstname'];
		} else {
			$json['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$json['lastname'] = $this->request->post['lastname'];
		} else {
			$json['lastname'] = '';
		}

		if (isset($this->request->post['email'])) {
			$json['email'] = $this->request->post['email'];
		} else {
			$json['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$json['telephone'] = $this->request->post['telephone'];
		} else {
			$json['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$json['fax'] = $this->request->post['fax'];
		} else {
			$json['fax'] = '';
		}

		if (isset($this->request->post['company'])) {
			$json['company'] = $this->request->post['company'];
		} else {
			$json['company'] = '';
		}

		if (isset($this->request->post['address_1'])) {
			$json['address_1'] = $this->request->post['address_1'];
		} else {
			$json['address_1'] = '';
		}

		if (isset($this->request->post['address_2'])) {
			$json['address_2'] = $this->request->post['address_2'];
		} else {
			$json['address_2'] = '';
		}

		if (isset($this->request->post['postcode'])) {
			$json['postcode'] = $this->request->post['postcode'];
		} elseif (isset($this->session->data['shipping_address']['postcode'])) {
			$json['postcode'] = $this->session->data['shipping_address']['postcode'];
		} else {
			$json['postcode'] = '';
		}

		if (isset($this->request->post['city'])) {
			$json['city'] = $this->request->post['city'];
		} else {
			$json['city'] = '';
		}

		if (isset($this->request->post['country_id'])) {
			$json['country_id'] = $this->request->post['country_id'];
		} elseif (isset($this->session->data['shipping_address']['country_id'])) {
			$json['country_id'] = $this->session->data['shipping_address']['country_id'];
		} else {
			$json['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->request->post['zone_id'])) {
			$json['zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($this->session->data['shipping_address']['zone_id'])) {
			$json['zone_id'] = $this->session->data['shipping_address']['zone_id'];
		} else {
			$json['zone_id'] = '';
		}

		// Custom Fields
		$this->load->model('account/custom_field');

		$json['custom_fields'] = $this->model_account_custom_field->getCustomFields();

		if (isset($this->request->post['custom_field'])) {
			if (isset($this->request->post['custom_field']['account'])) {
				$account_custom_field = $this->request->post['custom_field']['account'];
			} else {
				$account_custom_field = array();
			}

			if (isset($this->request->post['custom_field']['address'])) {
				$address_custom_field = $this->request->post['custom_field']['address'];
			} else {
				$address_custom_field = array();
			}

			$json['register_custom_field'] = $account_custom_field + $address_custom_field;
		} else {
			$json['register_custom_field'] = array();
		}

		if (isset($this->request->post['password'])) {
			$json['password'] = $this->request->post['password'];
		} else {
			$json['password'] = '';
		}

		if (isset($this->request->post['confirm'])) {
			$json['confirm'] = $this->request->post['confirm'];
		} else {
			$json['confirm'] = '';
		}

		if (isset($this->request->post['newsletter'])) {
			$json['newsletter'] = $this->request->post['newsletter'];
		} else {
			$json['newsletter'] = '';
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Credentials: true');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function validate() {
		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if (!empty($this->request->post['telephone']) && ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32))) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if (!empty($this->request->post['address_1']) && ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128))) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}

		if (!empty($this->request->post['city']) && ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128))) {
			$this->error['city'] = $this->language->get('error_city');
		}

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

		if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}

		if (!empty($this->request->post['country_id']) && (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '')) {
			$this->error['zone'] = $this->language->get('error_zone');
		}

		// Customer Group
		if (isset($this->request->post['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->post['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->post['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		// Custom field validation
		$this->load->model('account/custom_field');

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
				$this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
			}
		}

		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
			$this->error['password'] = $this->language->get('error_password');
		}

		if ($this->request->post['confirm'] != $this->request->post['password']) {
			$this->error['confirm'] = $this->language->get('error_confirm');
		}

		// Captcha
		if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('register', (array)$this->config->get('config_captcha_page'))) {
			$captcha = $this->load->controller('captcha/' . $this->config->get('config_captcha') . '/validate');

			if ($captcha) {
				$this->error['captcha'] = $captcha;
			}
		}

		return !$this->error;
	}

	public function customfield() {
		$json = array();

		$this->load->model('account/custom_field');

		// Customer Group
		if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
			$customer_group_id = $this->request->get['customer_group_id'];
		} else {
			$customer_group_id = $this->config->get('config_customer_group_id');
		}

		$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

		foreach ($custom_fields as $custom_field) {
			$json[] = array(
				'custom_field_id' => $custom_field['custom_field_id'],
				'required'        => $custom_field['required']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
