<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Address extends Controller {
	public function getZones(){
		if(!isset($this->request->post['country_id']))
			$json['error'] = "No country_id";
		else{
			$country_id = $this->request->post['country_id'];
			
			$this->load->model('localisation/zone');
			
			if (!isset($country_id)){
				$country_id = $this->config->get('config_country_id');
			}

			$json['zones'] = $this->model_localisation_zone->getZonesByCountryId($country_id);
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
	
	public function index() {
		$this->load->language('checkout/checkout');
		
		$json = array();
		
		if (isset($this->session->data['payment_address']['address_id'])) {
			$json['address_id'] = $this->session->data['payment_address']['address_id'];
		} else {
			$json['address_id'] = $this->customer->getAddressId();
		}

		$this->load->model('account/address');
		
		
		$this->load->model('localisation/country');
		$json['countries'] = $this->model_localisation_country->getCountries();
		

		$json['addresses'] = $this->model_account_address->getAddresses();

		if (isset($this->session->data['payment_address']['country_id'])) {
			$json['country_id'] = $this->session->data['payment_address']['country_id'];
		} else {
			$json['country_id'] = $this->config->get('config_country_id');
		}

		if (isset($this->session->data['payment_address']['zone_id'])) {
			$json['zone_id'] = $this->session->data['payment_address']['zone_id'];
		} else {
			$json['zone_id'] = '';
		}

		// Custom Fields
		$this->load->model('account/custom_field');

		$json['custom_fields'] = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

		if (isset($this->session->data['payment_address']['custom_field'])) {
			$json['payment_address_custom_field'] = $this->session->data['payment_address']['custom_field'];
		} else {
			$json['payment_address_custom_field'] = array();
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

	public function save() {
		$this->load->language('checkout/checkout');

		$json = array();

		// Validate if customer is logged in.
		if (!$this->customer->isLogged()) {
			$json['error'] = "Not logged in";
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 406 Not Acceptable');
		}

		if (!$json) {
			if (isset($this->request->post['payment_address']) && $this->request->post['payment_address'] == 'existing') {
				$this->load->model('account/address');

				if (empty($this->request->post['address_id'])) {
					$json['error']['warning'] = $this->language->get('error_address');
				} elseif (!in_array($this->request->post['address_id'], array_keys($this->model_account_address->getAddresses()))) {
					$json['error']['warning'] = $this->language->get('error_address');
				}

				if (!$json) {
					// Default Payment Address
					$this->load->model('account/address');

					$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->post['address_id']);

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);
				}
			} else {
				if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
					$json['error']['firstname'] = $this->language->get('error_firstname');
				}

				if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
					$json['error']['lastname'] = $this->language->get('error_lastname');
				}

				if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
					$json['error']['address_1'] = $this->language->get('error_address_1');
				}

				if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 32)) {
					$json['error']['city'] = $this->language->get('error_city');
				}

				$this->load->model('localisation/country');

				$country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

				if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
					$json['error']['postcode'] = $this->language->get('error_postcode');
				}

				if ($this->request->post['country_id'] == '') {
					$json['error']['country'] = $this->language->get('error_country');
				}

				if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '') {
					$json['error']['zone'] = $this->language->get('error_zone');
				}

				// Custom field validation
				$this->load->model('account/custom_field');

				$custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

				foreach ($custom_fields as $custom_field) {
					if (($custom_field['location'] == 'address') && $custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
						$json['error']['custom_field' . $custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
					}
				}

				if (!$json) {
					// Default Payment Address
					$this->load->model('account/address');

					$this->request->post["company"] = "";
					
					if(isset($this->request->post['address_id'])){
						$this->model_account_address->editAddress($this->request->post['address_id'], $this->request->post);
						$address_id = $this->request->post['address_id'];
					}else{
						$address_id = $this->model_account_address->addAddress($this->request->post);
					}
					
					$this->session->data['payment_address'] = $this->model_account_address->getAddress($address_id);

					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);

					$this->load->model('account/activity');

					$activity_data = array(
						'customer_id' => $this->customer->getId(),
						'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
					);

					$this->model_account_activity->addActivity('address_add', $activity_data);
				}
			}
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
	
	
	public function doCurl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = json_decode(curl_exec($ch), true);
		curl_close($ch);
		return $data;
	}

	public function geolocationaddress() {
		$url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $this->request->post['lat'] . "," .  $this->request->post['lng'];
		$google_data = $this->doCurl($url);
		
		$address_line1 = array();
		$address_line2 = array();
		
		if(!empty($google_data) && $google_data['status'] == "OK"){
			$address_components = $google_data['results'][0]['address_components'];
			foreach($address_components as $address_component){
				if(in_array('postal_code', $address_component['types'])){
					$postal = $address_component['short_name'];
				} else if(in_array('country', $address_component['types'])){
					$country = $address_component['short_name'];
					$this->load->model('localisation/country');
					$countries = $this->model_localisation_country->getCountries();
					foreach($countries as $v){
						if($v['iso_code_2'] == $country){
							$country = $v['country_id'];
							break;
						}
					}
				}else if(in_array('locality', $address_component['types'])){
					$city = $address_component['long_name'];
				}else if(in_array('street_number', $address_component['types'])){
					$address_line1[] = $address_component['long_name'];
				}else if(in_array('route', $address_component['types'])){
					$address_line1[] = $address_component['long_name'];
				}else if(in_array('administrative_area_level_1', $address_component['types'])){
					$state = $address_component['long_name'];
				}else if(in_array('sublocality', $address_component['types'])){
					$address_line2[] = $address_component['long_name'];
				}
			}
		}
		
		$json = array(
					'address_line1' => join(', ', $address_line1),
					'address_line2' => join(', ', $address_line2),
					'city' => $city,
					'postalcode' => $postal,
					'country' => $country,
					'state' => $state
				);
		

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
}