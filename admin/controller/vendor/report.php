<?php
class ControllerVendorReport  extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('vendor/report');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/report');	
		$this->getList();
	}
	
	public function delete() {
		$this->load->language('vendor/report');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/report');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $order_product_id){
				$this->model_vendor_report->deleteReport($order_product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	
	public function getList() {
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
		 	$filter_order_id = '';
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
		 	$filter_customer = '';
		}

		if (isset($this->request->get['filter_seller'])) {
			$filter_seller = $this->request->get['filter_seller'];
		} else {
		 	$filter_seller = '';
		}

		
		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
		 	$filter_status = '';
		}
		
		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
		 	$filter_date = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$url = '';
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}

		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}
	 	
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		$data['delete'] = $this->url->link('vendor/report/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);	
		$data['reports'] = array();

		$filter_data = array(
			'filter_order_id'  => $filter_order_id,
			'filter_customer'  => $filter_customer,
			'filter_seller'  => $filter_seller,
			'filter_status'    => $filter_status,
			'filter_date'      => $filter_date,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		$this->load->model('vendor/vendor');
		$report_total = $this->model_vendor_report->getTotalReport($filter_data);
		
		$reports = $this->model_vendor_report->getReports($filter_data);
	 	
	 	if(isset($reports)) {
			foreach($reports as $report){
				$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
				if(isset($sellers['firstname'])){
					$sellername = $sellers['firstname'];
				} else {
					$sellername ='';
				}
				$status_info = $this->model_vendor_report->getOrderStatus($report['order_status_id']);
				if(isset($status_info['name'])){
					$statusname = $status_info['name'];
				} else {
					$statusname ='';
				}
				$data['reports'][] = array(
					'order_product_id'=>$report['order_product_id'],
					'order_id'      =>$report['order_id'],
					'firstname'     =>$report['firstname'],
					'name'     		=>$report['name'],
					'total'     	=>$report['total'],
					'sellername'    =>$sellername,
					'statusname'    =>$statusname,
					//02 June 2018//
					'date_added'	=>date($this->language->get('date_format_short'), strtotime($report['date_added'])),
					//02 June 2018//
					'view'          => $this->url->link('vendor/report/view', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $report['order_id'] . $url, true)
				);
			}
		}
	 	
   		
		$data['heading_title']          = $this->language->get('heading_title');
		$data['text_list']           	= $this->language->get('text_list');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm']			= $this->language->get('text_confirm');
		$data['text_none'] 				= $this->language->get('text_none');
	 	$data['text_enable']            = $this->language->get('text_enable');
		$data['text_disable']           = $this->language->get('text_disable');
		$data['text_select']            = $this->language->get('text_select');
		$data['text_missing']           = $this->language->get('text_missing');
		$data['column_order_id']	    = $this->language->get('column_order_id');
		$data['column_seller']		    = $this->language->get('column_seller');
		$data['column_customer']		= $this->language->get('column_customer');
		$data['column_product']			= $this->language->get('column_product');
		$data['column_total']			= $this->language->get('column_total');
		$data['column_status']			= $this->language->get('column_status');
		$data['column_date']			= $this->language->get('column_date');
		$data['column_action']			= $this->language->get('column_action');
		$data['entry_order_id']			= $this->language->get('entry_order_id');
		$data['entry_customer']			= $this->language->get('entry_customer');
		$data['entry_seller']			= $this->language->get('entry_seller');
		$data['entry_status']			= $this->language->get('entry_status');
		$data['entry_date']			    = $this->language->get('entry_date');
		$data['button_remove']          = $this->language->get('button_remove');
		$data['button_delete']          = $this->language->get('button_delete');
		$data['button_filter']          = $this->language->get('button_filter');
		$data['button_view']            = $this->language->get('button_view');
		$data['text_confirm']           = $this->language->get('text_confirm');
		$data['name']                   = $this->language->get('name');
		$data['user_token']                  = $this->session->data['user_token'];
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}

		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
	 
		$data['sort_order_id']  = $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
		$data['sort_seller']    = $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . '&sort=vop.seller' . $url, true);
		$data['sort_customer']  = $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . '&sort=o.customer' . $url, true);
		$data['sort_product']   = $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . '&sort=vop.product' . $url, true);
		$data['sort_status']  	= $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . '&sort=o.status' . $url, true);
		$data['sort_date']  	= $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . '&sort=vop.date' . $url, true);
				  
		$url = '';
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}

		if (isset($this->request->get['filter_seller'])) {
			$url .= '&filter_seller=' . $this->request->get['filter_seller'];
		}
		
		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}
		
		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . $this->request->get['filter_date'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
        
		$pagination 		= new Pagination();
		$pagination->total 	= $report_total;
		$pagination->page  	= $page;
		$pagination->limit 	= $this->config->get('config_limit_admin');
		$pagination->url   	= $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($report_total - $this->config->get('config_limit_admin'))) ? $report_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $report_total, ceil($report_total / $this->config->get('config_limit_admin')));
		
		
		$data['filter_order_id']	= $filter_order_id;
		$data['filter_customer']	= $filter_customer;
		$data['filter_seller']		= $filter_seller;
		$data['filter_status']		= $filter_status;
		$data['filter_date']		= $filter_date;

		$this->load->model('vendor/vendor');
		if(isset($data['filter_seller'])) {
			$vendor_info = $this->model_vendor_vendor->getVendor($data['filter_seller']);
		}

		if(isset($vendor_info['firstname'])) {
			$data['sellernme'] = $vendor_info['firstname'];
		} else {
			$data['sellernme'] ='';
		}

		$this->load->model('customer/customer');
		if(isset($data['filter_customer'])) {
			$customer_info = $this->model_customer_customer->getCustomer($data['filter_customer']);
		}

		if(isset($customer_info['firstname'])) {
			$data['customernme'] = $customer_info['firstname'];
		} else {
			$data['customernme'] ='';
		}

		$data['sort']		= $sort;
		$data['order']		= $order;
		
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('vendor/report_list', $data));
	}
	
	public function view() {
		$this->load->language('vendor/report');
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
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_view'),
			'href' => $this->url->link('vendor/report', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		
		$data['invoice'] = $this->url->link('vendor/report/invoice', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
		
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
		$data['text_byseller']  		= $this->language->get('text_byseller');
		$data['column_order_id']	    = $this->language->get('column_order_id');
		$data['column_product']		    = $this->language->get('column_product');
		$data['column_model']		    = $this->language->get('column_model');
		$data['column_quantity']		= $this->language->get('column_quantity');
		$data['column_price']		    = $this->language->get('column_price');
		$data['column_total']		    = $this->language->get('column_total');
		$data['column_orderstatus']		= $this->language->get('column_orderstatus');
		$data['column_tracking']		= $this->language->get('column_tracking');
		$data['button_invoice_print']	= $this->language->get('button_invoice_print');
		
		$data['user_token'] = $this->session->data['user_token'];
		

		$this->load->model('vendor/report');
		$order_info = $this->model_vendor_report->getOrder($order_id);
		$data['order_id'] 		= $order_info['order_id'];
		$data['date_added'] 	= $order_info['date_added'];
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method']= $order_info['shipping_method'];
		
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
		$this->load->model('vendor/product');
		$this->load->model('vendor/vendor');
		$data['products']=array();
		$products = $this->model_vendor_report->getOrderProduct($this->request->get['order_id']);
		foreach($products as $product){
	
			$this->load->model('localisation/order_status');
			$seller_info = $this->model_vendor_vendor->getVendor($product['vendor_id']);
			if(isset($seller_info['name'])){
				$sellername = $seller_info['display_name'];
			} else {
				$sellername ='';
			}
			if(isset($seller_info['vendor_id'])){
				$ids = $seller_info['vendor_id'];
			} else {
				$ids ='';
			}
			$status_info = $this->model_localisation_order_status->getOrderStatus($product['order_status_id']);
			if(isset($status_info['name'])){
				$statusname = $status_info['name'];
			} else {
				$statusname='';
			}
			
			$data['products'][]=array(
				'order_product_id' => $product['order_product_id'],
				'order_id' => $product['order_id'],
				'name' 		=> $product['name'],
				'model' 	=> $product['model'],
				'quantity'	=> $product['quantity'],
				'tracking' 	=> $product['tracking'],
				'sellername'=> $sellername,
				'statusname'=> $statusname,
				'price'    	=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    	=> $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
				'href'      => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'] . $url, true),
				'sellerhref'=> $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $ids . $url, true)
			);
			
		}
		
		$data['totals'] = array();

		$totals = $this->model_vendor_report->getOrderTotals($this->request->get['order_id']);

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
		
		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('vendor/view_list', $data));
		
	}
	
	public function invoice() {
		$this->load->language('vendor/report');

		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

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
		$data['text_invoice']  			= $this->language->get('text_invoice');
		$data['text_telephone']  		= $this->language->get('text_telephone');
		$data['text_email']  		    = $this->language->get('text_email');
		$data['column_order_id']	    = $this->language->get('column_order_id');
		$data['column_product']		    = $this->language->get('column_product');
		$data['column_model']		    = $this->language->get('column_model');
		$data['column_quantity']		= $this->language->get('column_quantity');
		$data['column_price']		    = $this->language->get('column_price');
		$data['column_total']		    = $this->language->get('column_total');
		$data['column_orderstatus']		= $this->language->get('column_orderstatus');
		$data['column_tracking']		= $this->language->get('column_tracking');
		$data['button_invoice_print']	= $this->language->get('button_invoice_print');

		$this->load->model('vendor/report');
		$order_info = $this->model_vendor_report->getOrder($this->request->get['order_id']);
		$data['order_id'] 		= $order_info['order_id'];
		$data['date_added'] 	= $order_info['date_added'];
		$data['payment_method'] = $order_info['payment_method'];
		$data['shipping_method']= $order_info['shipping_method'];
		$data['telephone']		= $order_info['telephone'];
		$data['email']		    = $order_info['email'];
		
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
		
		$data['products']=array();
		$products = $this->model_vendor_report->getOrderProduct($this->request->get['order_id']);
		foreach($products as $product){
			
			
			$this->load->model('localisation/order_status');
			
			$status_info = $this->model_localisation_order_status->getOrderStatus($product['order_status_id']);
			if(isset($status_info['name'])){
				$statusname = $status_info['name'];
			} else {
				$statusname='';
			}
				$data['products'][]=array(
				'order_product_id' => $product['order_product_id'],
				'order_id' => $product['order_id'],
				'name' 		=> $product['name'],
				'model' 	=> $product['model'],
				'statusname'=> $statusname,
				'quantity'	=> $product['quantity'],
				'price'    	=> $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    	=> $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
			);
		}
		
		$data['totals'] = array();

		$totals = $this->model_vendor_report->getOrderTotals($this->request->get['order_id']);

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		$this->response->setOutput($this->load->view('vendor/invoice', $data));
	}
	    
	function addtrack(){
		
		$json = array();
		$this->load->model('vendor/report');
		$this->load->language('vendor/report');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			$this->model_vendor_report->addTracks($this->request->post['order_id'],$this->request->post);
			//print_r($this->request->post);die();
			$json['success'] = $this->language->get('text_success');
			
		}
		//$this->view();
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	function addorderstatus(){
		
		$json = array();
		$this->load->model('vendor/report');
		$this->load->language('vendor/report');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			
			$this->model_vendor_report->addOrdeStatus($this->request->get['order_id'],$this->request->post);
			
			$json['success'] = $this->language->get('text_success');
			
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/report')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
}
?>