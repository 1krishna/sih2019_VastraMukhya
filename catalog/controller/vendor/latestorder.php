<?php
class ControllerVendorLatestOrder extends Controller {
	public function index() {
		$this->load->language('vendor/latestorder');
		$this->load->model('vendor/vendor');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		$data['text_select'] = $this->language->get('text_select');
		$data['button_view'] = $this->language->get('button_view');
		$data['button_save'] = $this->language->get('button_save');

		$this->load->model('localisation/order_status');
		$data['order_statuss'] = $this->model_localisation_order_status->getOrderStatuses($data);
		
		$data['orders'] = array();
		$filter_data=array(
			'vendor_id' => $this->vendor->getId(),
			'customer_id' => $this->customer->getId(),
			'sort' => 'date_added',
			'order' => 'DESC',
		);
		$orders = $this->model_vendor_vendor->getOrders($filter_data);
		
		foreach($orders as $order){
			
			$status_info = $this->model_vendor_vendor->getOrderStatus($order['order_status_id']);

			if(isset($status_info['name'])){
				$statusname = $status_info['name'];
			} else {
				$statusname ='';
			}
			//print_r($status_info);die();
			
			$data['orders'][]=array(
				'order_product_id'	=> $order['order_product_id'],
				'order_status_id'	=> $order['order_status_id'],
				'order_id'	=> $order['order_id'],
				'firstname' => $order['firstname'],
				/// 09 06 2018 ///
				'date_added'=> date($this->language->get('date_format_short'), strtotime($order['date_added'])),
				/// 09 06 2018 ///
				'total' 	=> $this->currency->format($order['total'], $this->config->get('config_currency')),
				'statusname'=> $statusname,
				'view'      => $this->url->link('vendor/latestorder/letestview', '&order_id=' . $order['order_id'])
			);
		}
		
		
		$data['column_order_id'] = $this->language->get('column_order_id');
		$data['column_customer'] = $this->language->get('column_customer');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_date_added'] = $this->language->get('column_date_added');
		$data['column_total'] = $this->language->get('column_total');
		$data['column_action'] = $this->language->get('column_action');
			return $this->load->view('vendor/latestorder', $data);
		
	}

	public function letestview() {
		$this->load->language('vendor/latestorder');
		$this->load->model('tool/upload');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
		 	$order_id = 0;
		}
					
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}
		
		$url = '';
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_view'),
			'href' => $this->url->link('vendor/dashboard')
		);
		
		$this->document->setTitle($this->language->get('heading_view'));
		$data['heading_view']          = $this->language->get('heading_view');
		$data['text_view']           	= $this->language->get('text_view');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_none'] 				= $this->language->get('text_none');
	 	$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['text_select']            = $this->language->get('text_select');
		$data['text_payment_address']   = $this->language->get('text_payment_address');
		$data['text_shipping_address']  = $this->language->get('text_shipping_address');
		$data['text_details']  			= $this->language->get('text_details');
		$data['text_order']  			= $this->language->get('text_order');
		$data['text_Payment']  			= $this->language->get('text_Payment');
		$data['text_shipping']  		= $this->language->get('text_shipping');
		$data['text_date']  			= $this->language->get('text_date');
		$data['column_order_id']	    = $this->language->get('column_order_id');
		$data['column_product']		    = $this->language->get('column_product');
		$data['column_model']		    = $this->language->get('column_model');
		$data['column_quantity']		= $this->language->get('column_quantity');
		$data['column_price']		    = $this->language->get('column_price');
		$data['column_total']		    = $this->language->get('column_total');
		$data['column_orderstatus']		= $this->language->get('column_orderstatus');
		$data['column_tracking']		= $this->language->get('column_tracking');
		$data['text_byseller']		    = $this->language->get('text_byseller');
		$data['button_invoice_print']		    = $this->language->get('button_invoice_print');
		

		$this->load->model('vendor/vendor');
		$order_info = $this->model_vendor_vendor->getOrder($this->request->get['order_id']);
		
		$data['order_id'] 		= $order_info['order_id'];
		$data['date_added'] 	= $order_info['date_added'];
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method']= $order_info['shipping_method'];
		
		$data['invoice'] = $this->url->link('vendor/latestorder/invoice', '&order_id=' . $order_info['order_id']);
		
		// Payment Address
		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'country'   => $order_info['payment_country']
		);

		$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		// Shipping Address
		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'country'   => $order_info['shipping_country']
		);

		$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
		
		
		$products = $this->model_vendor_vendor->getSellerOrders($this->request->get['order_id']);
		
		foreach($products as $product){
			$this->load->model('localisation/order_status');
			$this->load->model('vendor/option');
			$seller_info = $this->model_vendor_vendor->getVendor($product['vendor_id']);
			
			
			if(isset($seller_info['display_name'])){
				$sellername = $seller_info['display_name'];
			} else {
				$sellername='';
			}
			if(isset($seller_info['vendor_id'])){
				$ids = $seller_info['vendor_id'];
			} else {
				$ids='';
			}
			
			$status_info = $this->model_localisation_order_status->getOrderStatus($product['order_status_id']);
			
			if(isset($status_info['name'])){
				$statusname = $status_info['name'];
			} else {
				$statusname='';
			}
				$option_data = array();
					
					$options = $this->model_vendor_option->getOrderOptions($order_id, $product['order_product_id']);

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $value
						);
					}
					
			$data['products'][]=array(
				'order_product_id' => $product['order_product_id'],
				'order_status_id' => $product['order_status_id'],
				'statusname'=> $statusname,
				'order_id' 	=> $product['order_id'],
                //15 2 2019 //
				'product_id' 	=> $product['product_id'],
				//15 2 2019 //
				'name' 		=> $product['name'],
				'model' 	=> $product['model'],
				'quantity'	=> $product['quantity'],
				'tracking' 	=> $product['tracking'],
				'sellername'=> $sellername,
				'option'   => $option_data,
				'price'    	=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    	=> $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
				'href'      => $this->url->link('product/product','&product_id=' . $product['product_id'] . $url, true),
				'sellerhref'=> $this->url->link('vendor/vendor_profile','&vendor_id=' . $ids . $url, true)
			);
		}
		
		$data['totals'] = array();

		$totals = $this->model_vendor_vendor->getOrderTotals($this->request->get['order_id']);

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
			);
		}
		
		$this->load->model('localisation/order_status');
		$data['order_statuss'] = $this->model_localisation_order_status->getOrderStatuses($data);
		
		if (isset($this->request->post['order_status_id'])) {
			$data['order_status_id'] = $this->request->post['order_status_id'];
		} else {
			$data['order_status_id'] = '';
		}
		
		$data['header']      = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer']      = $this->load->controller('vendor/footer');
		
		$this->response->setOutput($this->load->view('vendor/letestview_list', $data));
		
	}
	
	function addtrack(){
		
		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/latestorder');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			$this->model_vendor_vendor->addTracks($this->request->post['order_id'],$this->request->post);
			
			$json['success'] = $this->language->get('text_success');
			
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	function addorderstatus(){
		
		$json = array();
		$this->load->model('localisation/order_status');
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/latestorder');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			$order_id = $this->model_vendor_vendor->addOrdeStatus($this->request->post['order_id'],$this->request->post);
			$json['success'] = $this->language->get('text_success');

			
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function invoice() {

		$this->load->language('vendor/latestorder');
		$this->load->model('localisation/order_status');
		$this->load->model('setting/setting');		
		$this->load->model('vendor/vendor');
		$this->load->model('tool/upload');
		$this->load->model('vendor/option');
				
		$data['text_invoice'] = $this->language->get('text_invoice');
		$data['text_order_detail'] = $this->language->get('text_order_detail');
		$data['text_telephone'] = $this->language->get('text_telephone');
		$data['text_fax'] = $this->language->get('text_fax');
		$data['text_email'] = $this->language->get('text_email');
		$data['text_website'] = $this->language->get('text_website');
		$data['text_invoice_no'] = $this->language->get('text_invoice_no');
		$data['text_order_id'] = $this->language->get('text_order_id');
		$data['text_payment_method'] = $this->language->get('text_payment_method');
		$data['text_shipping_method'] = $this->language->get('text_shipping_method');
		$data['text_payment_address'] = $this->language->get('text_payment_address');
		$data['text_shipping_address'] = $this->language->get('text_shipping_address');
		$data['text_byseller']		   = $this->language->get('text_byseller');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
		 	$order_id = 0;
		}
		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');
		
		$url = '';
		
		$data['orders'] = array();
		
		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_vendor_vendor->getOrder($order_id);
			
			// Make sure there is a shipping method
			if ($order_info && $order_info['shipping_code']) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
				}

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}
				
				
				if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_code'],
					'country'   => $order_info['payment_country']
				);
				
				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
				
				
				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
					$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$product_data = array();

				$products = $this->model_vendor_vendor->getSellerOrders($order_id);
	
				foreach ($products as $product) {
				
				$seller_info = $this->model_vendor_vendor->getVendor($product['vendor_id']);
				
				
				$status_info = $this->model_localisation_order_status->getOrderStatus($product['order_status_id']);
				if(isset($status_info['name'])){
					$statusname = $status_info['name'];
				} else {
					$statusname='';
				}
			
				if(isset($seller_info['display_name'])){
					$sellername = $seller_info['display_name'];
				} else {
					$sellername='';
				}
				
				if(isset($seller_info['vendor_id'])){
					$ids = $seller_info['vendor_id'];
				} else {
					$ids='';
				}
				
					$option_data = array();
					
					$options = $this->model_vendor_option->getOrderOptions($order_id, $product['order_product_id']);

					foreach ($options as $option) {
						if ($option['type'] != 'file') {
							$value = $option['value'];
						} else {
							$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

							if ($upload_info) {
								$value = $upload_info['name'];
							} else {
								$value = '';
							}
						}

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $value
						);
					}
					
					
					$product_data[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'sellername'=> $sellername,
						'statusname'=> $statusname,
						'sellerhref'=> $this->url->link('vendor/vendor_profile','&vendor_id=' . $ids . $url, true),	
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
					);
					
				}
				
				$total_data = array();

				$totals = $this->model_vendor_vendor->getOrderTotals($order_id);

				foreach ($totals as $total) {
					$total_data[] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}
				
				$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'email'            => $order_info['email'],
					'telephone'        => $order_info['telephone'],
					'shipping_address' => $shipping_address,
					'payment_address' => $payment_address,
					'payment_method'  => $order_info['payment_method'],
					'shipping_method'  => $order_info['shipping_method'],
					'product'          => $product_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment'])
				);
			}
		}
		
		
			
		$this->response->setOutput($this->load->view('vendor/order_invoice', $data));
	}
	
}
