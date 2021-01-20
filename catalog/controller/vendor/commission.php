<?php
class ControllerVendorCommission extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', 'SSL'));
		}
		$this->load->language('vendor/commission');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/commission');

		$this->getList();
	}

	protected function getList() {
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.date_added';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

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
			'href' => $this->url->link('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/commission')
		);
		
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}


		$data['commissions'] = array();

		$filter_data = array(
			'vendor_id' => $this->vendor->getId(),
			'sort'      => $sort,
			'order'     => $order,
			'start'     => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'     => $this->config->get('config_limit_admin')
		);

		$this->load->model('vendor/vendor');

		$commission_total = $this->model_vendor_commission->getTotalCommissionReport($filter_data);
		$reports = $this->model_vendor_commission->getCommissionReports($filter_data);
	 	//print_r($reports); 
	 	$commi_total=0;
		foreach($reports as $report){
			$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
			if(isset($sellers['firstname'])){
				$sellername = $sellers['firstname'];
			} else {
				$sellername ='';
			}
			
		 	$currency_info = $this->model_vendor_commission->getOrderCurrency($report['order_id']);
		
			if(isset($currency_info['currency_code'])) {
				$currency = $currency_info['currency_code'];
			} else {
				$currency=$this->config->get('config_currency');
			}
			$totalcommission = $this->model_vendor_commission->getTotaCommission($filter_data,$report['vendor_id']);
			$commi_total+=$totalcommission ;
			
			$data['commissions'][] = array(
				'order_product_id'=>$report['order_product_id'],
				'sellername'	 =>$sellername,
				'name'			 =>$report['name'],
				'model'			 =>$report['model'],
				'quantity'		 =>$report['quantity'],
				'price'			 =>$report['price'],
				'totalcommission'=> $this->currency->format($totalcommission,$currency),
				'commissionper'	 =>$report['commissionper'],
				'commissionfix'	 =>$report['commissionfix'],
				'date_added'	 =>$report['date_added'],
			);
		}
		
		$data['heading_title'] 		= $this->language->get('heading_title');

		$data['text_list'] 			= $this->language->get('text_list');
		$data['text_no_results'] 	= $this->language->get('text_no_results');
		$data['text_confirm'] 		= $this->language->get('text_confirm');
		$data['text_enabled'] 		= $this->language->get('text_enabled');
		$data['text_disabled'] 		= $this->language->get('text_disabled');

		$data['column_vendor']  	= $this->language->get('column_vendor');
		$data['column_name']  		= $this->language->get('column_name');
		$data['column_model']  		= $this->language->get('column_model');
		$data['column_quantity']    = $this->language->get('column_quantity');
		$data['column_price']  		= $this->language->get('column_price');
		$data['column_commission']  = $this->language->get('column_commission');
		$data['column_commissionfixed']= $this->language->get('column_commissionfixed');
		$data['column_commissiontotal']= $this->language->get('column_commissiontotal');
		$data['column_date']  		= $this->language->get('column_date');

		$data['entry_product'] 		= $this->language->get('entry_product');
		$data['entry_author'] 		= $this->language->get('entry_author');
		$data['entry_rating'] 		= $this->language->get('entry_rating');
		$data['entry_status'] 		= $this->language->get('entry_status');
		$data['entry_date_added'] 	= $this->language->get('entry_date_added');

		
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

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_vendor']		 = $this->url->link('vendor/commission','&sort=vendor'.$url , 'SSL');
		$data['sort_name']   		 = $this->url->link('vendor/commission','&sort=name'.$url , 'SSL');
		$data['sort_model']			 = $this->url->link('vendor/commission','&sort=model'.$url , 'SSL');
		$data['sort_quantity']   	 = $this->url->link('vendor/commission','&sort=quantity'.$url , 'SSL');
		$data['sort_price']   		 = $this->url->link('vendor/commission','&sort=price'.$url , 'SSL');
		$data['sort_commission']   	 = $this->url->link('vendor/commission','&sort=commission'.$url , 'SSL');
		$data['sort_commissionfixed']= $this->url->link('vendor/commission','&sort=commissionfixed'.$url , 'SSL');
		$data['sort_commissiontotal']= $this->url->link('vendor/commission','&sort=commissiontotal'.$url , 'SSL');
		$data['sort_date']   		 = $this->url->link('vendor/commission','&sort=date'.$url , 'SSL');

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination 	   = new Pagination();
		$pagination->total = $commission_total;
		$pagination->page  = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url   = $this->url->link('vendor/commission',$url . 'page={page}', 'SSL');

		$data['pagination']= $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($commission_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($commission_total - $this->config->get('config_limit_admin'))) ? $commission_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $commission_total, ceil($commission_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer'] = $this->load->controller('vendor/footer');

		$this->response->setOutput($this->load->view('vendor/commission', $data));
	}

}
