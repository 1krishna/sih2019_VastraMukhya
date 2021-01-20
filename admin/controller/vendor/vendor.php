<?php
class ControllerVendorVendor extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('vendor/vendor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');
		$this->load->model('vendor/store');
 
		$this->getList();
	}

	public function add() {
		$this->load->language('vendor/vendor');
		$this->load->model('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_vendor_vendor->addVendor($this->request->post);
			//echo "<pre>";
			//print_r($this->request->post);die();
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

			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('vendor/vendor');
		$this->load->model('vendor/store');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			$this->model_vendor_vendor->editVendor($this->request->get['vendor_id'], $this->request->post);
			//echo "<pre>";
			//print_r($this->request->post);die();
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

			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('vendor/vendor');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/vendor');
		$this->load->model('vendor/store');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $vendor_id) {
				$this->model_vendor_vendor->deleteVendor($vendor_id);
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

			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
	
	public function approve(){
		$this->load->language('vendor/vendor');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/vendor');
		$approves = array();
		if (isset($this->request->post['selected'])){
			$approve = $this->request->post['selected'];
		} 
		elseif (isset($this->request->get['vendor_id'])){
			$approves[] = $this->request->get['vendor_id'];
		}
		if ($approves && $this->validateApprove()){
			foreach($approves as $vendor_id){
				$this->model_vendor_vendor->approve($vendor_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList(); 
	}

	public function disapprove(){
		$this->load->language('vendor/vendor');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/vendor');
		$approves = array();
		if (isset($this->request->post['selected'])){
			$approve = $this->request->post['selected'];
		} 
		elseif (isset($this->request->get['vendor_id'])){
			$approves[] = $this->request->get['vendor_id'];
		}
		if ($approves && $this->validateDesapprove()){
			foreach($approves as $vendor_id){
				$this->model_vendor_vendor->Disapprove($vendor_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList(); 

	 }	

	protected function getList() {
		
		if (isset($this->request->get['filter_firstname'])) {
			$filter_firstname = $this->request->get['filter_firstname'];
		} else {
			$filter_firstname = '';
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
			$sort = 'name';
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

		$url = '';
		
		if (isset($this->request->get['filter_firstname'])) {
			$url .= '&filter_firstname=' . $this->request->get['filter_firstname'];
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
			'href' => $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('vendor/vendor/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('vendor/vendor/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['vendors'] = array();

		$filter_data = array(
			'filter_firstname' => $filter_firstname,
			'filter_status' => $filter_status,
			'filter_date' => $filter_date,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		//$delproduct = $this->model_vendor_vendor->getVendoproductdelete($filter_data);

		$vendor_total = $this->model_vendor_vendor->getTotalVendor($filter_data);

		$results = $this->model_vendor_vendor->getVendors($filter_data);
		$this->load->model('tool/image');
		foreach ($results as $result) {
			
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}
			
			if (!$result['approved']) {
				$approve = $this->url->link('vendor/vendor/approve', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . $url, true);
			} else {
				$approve = '';
			}
			
			if ($result['approved']) {
				$disapproved = $this->url->link('vendor/vendor/disapprove', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . $url, true);
			} else {
				$disapproved = '';
			}
			
			$data['vendors'][] = array(
				'vendor_id'       => $result['vendor_id'],
				'display_name'    => $result['display_name'],
				'firstname'       => $result['firstname'],
				'lastname'        => $result['lastname'],
				'email'           => $result['email'],
				'status'          => ($result['status'] ? $this->language->get('text_enable') : $this->language->get('text_disable')),
				'approve'		  => $approve,
				'disapproved'	  => $disapproved,
				'image'		      => $image,
				'edit'            => $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $result['vendor_id'] . $url, true)
			);
		}

		$data['heading_title'] 			= $this->language->get('heading_title');
		$data['user_token'] 					= $this->session->data['user_token'];
		$data['text_list'] 				= $this->language->get('text_list');
		$data['text_no_results'] 		= $this->language->get('text_no_results');
		$data['text_confirm'] 			= $this->language->get('text_confirm');
		$data['text_enable'] 			= $this->language->get('text_enable');
		$data['text_disable'] 			= $this->language->get('text_disable');
		$data['text_select'] 			= $this->language->get('text_select');

		$data['column_display_name'] 	= $this->language->get('column_display_name');
		$data['column_name'] 			= $this->language->get('column_name');
		$data['column_firstname'] 		= $this->language->get('column_firstname');
		$data['column_lastname'] 		= $this->language->get('column_lastname');
		$data['column_email'] 			= $this->language->get('column_email');
		$data['column_status'] 			= $this->language->get('column_status');
		$data['column_image'] 			= $this->language->get('column_image');
		$data['column_approve'] 		= $this->language->get('column_approve');
		
		$data['column_sort_order'] 		= $this->language->get('column_sort_order');
		$data['column_action'] 			= $this->language->get('column_action');
		
		$data['entry_firstname'] 		= $this->language->get('entry_firstname');	
		$data['entry_status'] 			= $this->language->get('entry_status');	
		$data['entry_date'] 			= $this->language->get('entry_date');	
		
		$data['button_add'] 			= $this->language->get('button_add');
		$data['button_edit'] 			= $this->language->get('button_edit');
		$data['button_delete'] 			= $this->language->get('button_delete');
		$data['button_filter'] 			= $this->language->get('button_filter');
		$data['button_approve'] 		= $this->language->get('button_approve');
		$data['button_desapprove'] 		= $this->language->get('button_desapprove');

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
		
		if (isset($this->request->get['filter_firstname'])) {
			$url .= '&filter_firstname=' . $this->request->get['filter_firstname'];
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

		$data['sort_image'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=image' . $url, true);
		$data['sort_display_name'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=display_name' . $url, true);
		$data['sort_firstname'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=firstname' . $url, true);
		$data['sort_lastname'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=lastname' . $url, true);
		$data['sort_email'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_status'] 	= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_sort_order']= $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);
		
		$url = '';
		
		if (isset($this->request->get['filter_firstname'])) {
			$url .= '&filter_firstname=' . $this->request->get['filter_firstname'];
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

		$pagination = new Pagination();
		$pagination->total = $vendor_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($vendor_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($vendor_total - $this->config->get('config_limit_admin'))) ? $vendor_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $vendor_total, ceil($vendor_total / $this->config->get('config_limit_admin')));
		
		$data['filter_firstname'] = $filter_firstname;
		$data['filter_status']    = $filter_status;
		$data['filter_date'] 	  = $filter_date;
		
		$data['sort'] 			  = $sort;
		$data['order'] 			  = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('vendor/vendor_list', $data));
	}

	protected function getForm() {
		$data['heading_title'] 			= $this->language->get('heading_title');

		$data['text_form'] 				= !isset($this->request->get['vendor_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_enabled'] 			= $this->language->get('text_enabled');
		$data['text_disabled'] 			= $this->language->get('text_disabled');
		$data['text_default'] 			= $this->language->get('text_default');
		$data['text_percent'] 			= $this->language->get('text_percent');
		$data['text_amount'] 			= $this->language->get('text_amount');
		$data['text_select'] 			= $this->language->get('text_select');
		
		$data['text_none'] 				= $this->language->get('text_none');
		$data['text_enable'] 			= $this->language->get('text_enable');
		$data['text_disable'] 			= $this->language->get('text_disable');
		
		$data['column_name'] 			= $this->language->get('column_name');
		$data['column_lastname'] 		= $this->language->get('column_lastname');
		
		$data['entry_display_name'] 	= $this->language->get('entry_display_name');
		$data['entry_firstname'] 		= $this->language->get('entry_firstname');
		$data['entry_lastname'] 		= $this->language->get('entry_lastname');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_telephone'] 		= $this->language->get('entry_telephone');
		$data['entry_fax'] 				= $this->language->get('entry_fax');
		$data['entry_company'] 			= $this->language->get('entry_company');
		$data['entry_address_1'] 		= $this->language->get('entry_address_1');
		$data['entry_address_2'] 		= $this->language->get('entry_address_2');
		$data['entry_status'] 			= $this->language->get('entry_status');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_password'] 		= $this->language->get('entry_password');
		$data['entry_confirmpassword'] 	= $this->language->get('entry_confirmpassword');
		$data['entry_image'] 			= $this->language->get('entry_image');
		$data['entry_about'] 			= $this->language->get('entry_about');
		
		// Store Data ///
		
		$data['entry_name'] 			= $this->language->get('entry_name');
		$data['entry_description'] 		= $this->language->get('entry_description');
		$data['entry_shipping_policy'] 	= $this->language->get('entry_shipping_policy');
		$data['entry_return_policy'] 	= $this->language->get('entry_return_policy');
		$data['entry_meta_keyword'] 	= $this->language->get('entry_meta_keyword');
		$data['entry_meta_description'] = $this->language->get('entry_meta_description');
		$data['entry_vendorname'] 		= $this->language->get('entry_vendorname');
		$data['entry_email'] 			= $this->language->get('entry_email');
		$data['entry_phone'] 			= $this->language->get('entry_phone');
		$data['entry_address'] 			= $this->language->get('entry_address');
		$data['entry_city'] 			= $this->language->get('entry_city');
		$data['entry_country'] 			= $this->language->get('entry_country');
		$data['entry_zone'] 			= $this->language->get('entry_zone');
		$data['entry_postcode'] 		= $this->language->get('entry_postcode');
		$data['entry_bank_detail'] 		= $this->language->get('entry_bank_detail');
		$data['entry_tax_number'] 		= $this->language->get('entry_tax_number');
		$data['entry_shipping_charge'] 	= $this->language->get('entry_shipping_charge');
		$data['entry_url'] 				= $this->language->get('entry_url');
		$data['entry_banner'] 			= $this->language->get('entry_banner');
		$data['entry_logo'] 			= $this->language->get('entry_logo');
		$data['entry_about'] 			= $this->language->get('entry_about');
		$data['entry_commission']       = $this->language->get('entry_commission');
		$data['entry_productstatus']    = $this->language->get('entry_productstatus');
		$data['entry_bankname']  		= $this->language->get('entry_bankname');
		$data['entry_bnumber']  		= $this->language->get('entry_bnumber');
		$data['entry_swiftcode']  		= $this->language->get('entry_swiftcode');
		$data['entry_aname']  			= $this->language->get('entry_aname');
		$data['entry_anumber']  		= $this->language->get('entry_anumber');
		$data['entry_Emailid']  		= $this->language->get('entry_Emailid');
		$data['entry_method']  			= $this->language->get('entry_method');
		$data['text_bank']  			= $this->language->get('text_bank');
		$data['text_paypal']  			= $this->language->get('text_paypal');
		$data['text_autoapprove']    	= $this->language->get('text_autoapprove');
		$data['text_pending']    		= $this->language->get('text_pending');
		$data['entry_facebook_url'] 	= $this->language->get('entry_facebook_url');
		$data['entry_google_url'] 		= $this->language->get('entry_google_url');
		$data['entry_mapurl'] 			= $this->language->get('entry_mapurl');
		
		// Store Data ///
		
		$data['tab_seller'] 		    = $this->language->get('tab_seller');
		$data['tab_generalstore'] 		= $this->language->get('tab_generalstore');
		$data['tab_datastore'] 			= $this->language->get('tab_datastore');
		$data['tab_commission'] 		= $this->language->get('tab_commission');
		

		$data['help_keyword'] = $this->language->get('help_keyword');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

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
		
		if (isset($this->error['fax'])) {
			$data['error_fax'] = $this->error['fax'];
		} else {
			$data['error_fax'] = '';
		}
		
		if (isset($this->error['company'])) {
			$data['error_company'] = $this->error['company'];
		} else {
			$data['error_company'] = '';
		}
		
		if (isset($this->error['address_1'])) {
			$data['error_address_1'] = $this->error['address_1'];
		} else {
			$data['error_address_1'] = '';
		}
		
		if (isset($this->error['address_2'])) {
			$data['error_address_2'] = $this->error['address_2'];
		} else {
			$data['error_address_2'] = '';
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

		/*if (isset($this->error['postcode'])) {
			$data['error_postcode'] = $this->error['postcode'];
		} else {
			$data['error_postcode'] = '';
		}
		
		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}
		
		if (isset($this->error['confirmpassword'])) {
			$data['error_confirmpassword'] = $this->error['confirmpassword'];
		} else {
			$data['error_confirmpassword'] = '';
		}*/
		
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

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['vendor_id'])) {
			$data['action'] = $this->url->link('vendor/vendor/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('vendor/vendor/edit', 'user_token=' . $this->session->data['user_token'] . '&vendor_id=' . $this->request->get['vendor_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('vendor/vendor', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['vendor_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$vendor_info=$this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);
			
		}
		
		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['display_name'])) {
			$data['display_name'] = $this->request->post['display_name'];
		} elseif (isset($vendor_info['display_name'])){
			$data['display_name'] = $vendor_info['display_name'];
		} else {
			$data['display_name'] = '';
		}

		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($vendor_info['firstname'])){
			$data['firstname'] = $vendor_info['firstname'];
		} else {
			$data['firstname'] = '';
		}
		

		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif (isset($vendor_info['lastname'])){
			$data['lastname'] = $vendor_info['lastname'];
		} else {
			$data['lastname'] = '';
		}
		
		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (isset($vendor_info['email'])){
			$data['email'] = $vendor_info['email'];
		} else {
			$data['email'] = '';
		}
		
		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($vendor_info['telephone'])){
			$data['telephone'] = $vendor_info['telephone'];
		} else {
			$data['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$data['fax'] = $this->request->post['fax'];
		} elseif (isset($vendor_info['fax'])){
			$data['fax'] = $vendor_info['fax'];
		} else {
			$data['fax'] = '';
		}
		
		if (isset($this->request->post['about'])) {
			$data['about'] = $this->request->post['about'];
		} elseif (isset($vendor_info['about'])){
			$data['about'] = $vendor_info['about'];
		} else {
			$data['about'] = '';
		}
		
		if (isset($this->request->post['company'])) {
			$data['company'] = $this->request->post['company'];
		} elseif (isset($vendor_info['company'])){
			$data['company'] = $vendor_info['company'];
		} else {
			$data['company'] = '';
		}
		
		if (isset($this->request->post['address_1'])) {
			$data['address_1'] = $this->request->post['address_1'];
		} elseif (isset($vendor_info['address_1'])){
			$data['address_1'] = $vendor_info['address_1'];
		} else {
			$data['address_1'] = '';
		}
		
		if (isset($this->request->post['address_2'])) {
			$data['address_2'] = $this->request->post['address_2'];
		} elseif (isset($vendor_info['address_2'])){
			$data['address_2'] = $vendor_info['address_2'];
		} else {
			$data['address_2'] = '';
		}
		
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (isset($vendor_info['status'])){
			$data['status'] = $vendor_info['status'];
		} else {
			$data['status'] = '';
		}
		
		if (isset($this->request->post['product_status'])) {
			$data['product_status'] = $this->request->post['product_status'];
		} elseif (isset($vendor_info['product_status'])){
			$data['product_status'] = $vendor_info['product_status'];
		} else {
			$data['product_status'] = '';
		}
		
		if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} elseif (isset($vendor_info['city'])){
			$data['city'] = $vendor_info['city'];
		} else {
			$data['city'] = '';
		}
		
		if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (isset($vendor_info['postcode'])){
			$data['postcode'] = $vendor_info['postcode'];
		} else {
			$data['postcode'] = '';
		}
		
		if (isset($this->request->post['country_id'])) {
			$data['country_id'] = $this->request->post['country_id'];
		} elseif (isset($vendor_info['country_id'])){
			$data['country_id'] = $vendor_info['country_id'];
		} else {
			$data['country_id'] = '';
		}
		
		if (isset($this->request->post['zone_id'])) {
			$data['zone_id'] = $this->request->post['zone_id'];
		} elseif (isset($vendor_info['zone_id'])){
			$data['zone_id'] = $vendor_info['zone_id'];
		} else {
			$data['zone_id'] = '';
		}
		
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (isset($vendor_info['image'])){
			$data['image'] = $vendor_info['image'];
		} else {
			$data['image'] = '';
		}
		
		$this->load->model('tool/image');
			
		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($vendor_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			
//// Seller Store Start ///
				
		if (isset($this->request->post['store_description'])) {
			$data['store_description'] = $this->request->post['store_description'];
		} elseif (isset($vendor_info)) {
			$data['store_description'] = $this->model_vendor_vendor->getVendorStoreDescriptions($this->request->get['vendor_id']);
		} else {
			$data['store_description'] = array();
		}
		
		if (isset($this->request->post['store_about'])) {
			$data['store_about'] = $this->request->post['store_about'];
		} elseif (isset($vendor_info['store_about'])){
			$data['store_about'] = $vendor_info['store_about'];
		} else {
			$data['store_about'] = '';
		}
		
				
		if (isset($this->request->post['banner'])) {
			$data['banner'] = $this->request->post['banner'];
		} elseif (isset($vendor_info['banner'])){
			$data['banner'] = $vendor_info['banner'];
		} else {
			$data['banner'] = '';
		}
		
		
		if (isset($this->request->post['logo'])) {
			$data['logo'] = $this->request->post['logo'];
		} elseif (isset($vendor_info['logo'])){
			$data['logo'] = $vendor_info['logo'];
		} else {
			$data['logo'] = '';
		}
		
		if (isset($this->request->post['tax_number'])) {
			$data['tax_number'] = $this->request->post['tax_number'];
		} elseif (isset($vendor_info['tax_number'])){
			$data['tax_number'] = $vendor_info['tax_number'];
		} else {
			$data['tax_number'] = '';
		}

		if (isset($this->request->post['facebook_url'])) {
			$data['facebook_url'] = $this->request->post['facebook_url'];
		} elseif (isset($vendor_info['facebook_url'])){
			$data['facebook_url'] = $vendor_info['facebook_url'];
		} else {
			$data['facebook_url'] = '';
		}

		if (isset($this->request->post['google_url'])) {
			$data['google_url'] = $this->request->post['google_url'];
		} elseif (isset($vendor_info['google_url'])){
			$data['google_url'] = $vendor_info['google_url'];
		} else {
			$data['google_url'] = '';
		}
		
		if (isset($this->request->post['map_url'])) {
			$data['map_url'] = $this->request->post['map_url'];
		} elseif (isset($vendor_info['map_url'])){
			$data['map_url'] = $vendor_info['map_url'];
		} else {
			$data['map_url'] = '';
		}

		if (isset($this->request->post['shipping_charge'])) {
			$data['shipping_charge'] = $this->request->post['shipping_charge'];
		} elseif (isset($vendor_info['shipping_charge'])){
			$data['shipping_charge'] = $vendor_info['shipping_charge'];
		} else {
			$data['shipping_charge'] = '';
		}
		
		if (isset($this->request->post['vendor_seo_url'])) {
			$data['vendor_seo_url'] = $this->request->post['vendor_seo_url'];
		} elseif (isset($this->request->get['vendor_id'])) {
			$data['vendor_seo_url'] = $this->model_vendor_vendor->getVendorSeoUrls($this->request->get['vendor_id']);
		} else {
			$data['vendor_seo_url'] = array();
		}	
		
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}
		
		
		
		if (isset($this->request->post['vendor_id'])) {
			$data['vendor_id'] = $this->request->post['vendor_id'];
		} elseif (isset($vendor_info['vendor_id'])){
			$data['vendor_id'] = $vendor_info['vendor_id'];
		} else {
			$data['vendor_id'] = '';
		}
		
		if (isset($this->request->post['commission'])) {
			$data['commission'] = $this->request->post['commission'];
		} elseif (isset($vendor_info['commission'])){
			$data['commission'] = $vendor_info['commission'];
		} else {
			$data['commission'] = '';
		}

		if (isset($this->request->post['payment_method'])) {
			$data['payment_method'] = $this->request->post['payment_method'];
		} elseif (!empty($vendor_info)) {
			$data['payment_method'] = $vendor_info['payment_method'];
		} else {
			$data['payment_method'] = 'paypal';
		}

		if (isset($this->request->post['paypal'])) {
			$data['paypal'] = $this->request->post['paypal'];
		} elseif (!empty($vendor_info)) {
			$data['paypal'] = $vendor_info['paypal'];
		} else {
			$data['paypal'] = '';
		}

		if (isset($this->request->post['bank_name'])) {
			$data['bank_name'] = $this->request->post['bank_name'];
		} elseif (!empty($vendor_info)) {
			$data['bank_name'] = $vendor_info['bank_name'];
		} else {
			$data['bank_name'] = '';
		}

		if (isset($this->request->post['bank_branch_number'])) {
			$data['bank_branch_number'] = $this->request->post['bank_branch_number'];
		} elseif (!empty($vendor_info)) {
			$data['bank_branch_number'] = $vendor_info['bank_branch_number'];
		} else {
			$data['bank_branch_number'] = '';
		}

		if (isset($this->request->post['bank_swift_code'])) {
			$data['bank_swift_code'] = $this->request->post['bank_swift_code'];
		} elseif (!empty($vendor_info)) {
			$data['bank_swift_code'] = $vendor_info['bank_swift_code'];
		} else {
			$data['bank_swift_code'] = '';
		}

		if (isset($this->request->post['bank_account_name'])) {
			$data['bank_account_name'] = $this->request->post['bank_account_name'];
		} elseif (!empty($vendor_info)) {
			$data['bank_account_name'] = $vendor_info['bank_account_name'];
		} else {
			$data['bank_account_name'] = '';
		}

		if (isset($this->request->post['bank_account_number'])) {
			$data['bank_account_number'] = $this->request->post['bank_account_number'];
		} elseif (!empty($vendor_info)) {
			$data['bank_account_number'] = $vendor_info['bank_account_number'];
		} else {
			$data['bank_account_number'] = '';
		}
		
		$this->load->model('tool/image');
		
		if (isset($this->request->post['banner']) && is_file(DIR_IMAGE . $this->request->post['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($this->request->post['banner'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['banner'])) {
			$data['thumb_banner'] = $this->model_tool_image->resize($vendor_info['banner'], 100, 100);
		} else {
			$data['thumb_banner'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		if (isset($this->request->post['logo']) && is_file(DIR_IMAGE . $this->request->post['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($this->request->post['logo'], 100, 100);
		} elseif (!empty($vendor_info) && is_file(DIR_IMAGE . $vendor_info['logo'])) {
			$data['thumb_logo'] = $this->model_tool_image->resize($vendor_info['logo'], 100, 100);
		} else {
			$data['thumb_logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}
		
		$data['banner_placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		$data['logo_placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		
//// Seller Store End ///
			

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/vendor_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['display_name']) < 2) || (utf8_strlen($this->request->post['display_name']) > 64)) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}

		if ((utf8_strlen($this->request->post['firstname']) < 2) || (utf8_strlen($this->request->post['firstname']) > 64)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen($this->request->post['lastname']) < 2) || (utf8_strlen($this->request->post['lastname']) > 64)) {
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
		
		if ((utf8_strlen($this->request->post['telephone']) < 2) || (utf8_strlen($this->request->post['telephone']) > 64)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}
		
		if ((utf8_strlen($this->request->post['address_1']) < 2) || (utf8_strlen($this->request->post['address_1']) > 64)) {
			$this->error['address_1'] = $this->language->get('error_address_1');
		}
		
		if ((utf8_strlen($this->request->post['city']) < 2) || (utf8_strlen($this->request->post['city']) > 64)) {
			$this->error['city'] = $this->language->get('error_city');
		}
		
		/*if ((utf8_strlen($this->request->post['postcode']) < 2) || (utf8_strlen($this->request->post['postcode']) > 64)) {
			$this->error['postcode'] = $this->language->get('error_postcode');
		}
		
		if ((utf8_strlen($this->request->post['password']) < 2) || (utf8_strlen($this->request->post['password']) > 64)) {
			$this->error['password'] = $this->language->get('error_password');
		}*/
		
		foreach ($this->request->post['store_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 3) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

		if ($this->request->post['payment_method'] == 'paypal') {
			if ((utf8_strlen($this->request->post['paypal']) > 96) || !filter_var($this->request->post['paypal'], FILTER_VALIDATE_EMAIL)) {
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

		if ($this->request->post['vendor_seo_url']) {
			
			$this->load->model('vendor/seo_url');
			
			foreach ($this->request->post['vendor_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}							
						
						$seo_urls = $this->model_vendor_seo_url->getSeoUrlsByKeyword($keyword);
						
						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['vendor_id']) || (($seo_url['query'] != 'vendor_id=' . $this->request->get['vendor_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');
							}
						}
					}
				}
			}
		}
		
		return !$this->error;
	}
	
	protected function validateApprove(){
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	    return !$this->error;
	}
	
	protected function validateDesapprove(){
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	    return !$this->error;

	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/vendor')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	return !$this->error;
	}

	public function country() {
		$json = array();

		$this->load->model('localisation/country');

		$country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

		if ($country_info) {
			$this->load->model('localisation/zone');

			$json = array(
				'country_id'        => $country_info['country_id'],
				'name'              => $country_info['name'],
				'iso_code_2'        => $country_info['iso_code_2'],
				'iso_code_3'        => $country_info['iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
				'status'            => $country_info['status']
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function autocomplete(){
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
		$accounts = $this->model_vendor_vendor->getVendors($filter_data);
		foreach ($accounts as $account) {

		$json[] = array(
		'vendor_id'  => $account['vendor_id'],
		'firstname'   => strip_tags(html_entity_decode($account['firstname'], ENT_QUOTES, 'UTF-8'))
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
	
}