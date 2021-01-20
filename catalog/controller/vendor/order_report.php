<?php
class ControllerVendorOrderReport  extends Controller {
	private $error = array();
	public function index() {
		$this->load->language('vendor/order_report');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('vendor/order_report');	
		$this->getList();
	}
	
	public function getList() {
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/order_report');
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

		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
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
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
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
			'href' => $this->url->link('common/dashboard', '' , true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/order_report', '' , true)
		);

		$data['delete'] = $this->url->link('vendor/order_report/delete', '' , true);

		$data['reports'] = array();

		$filter_data = array(
			'filter_order_id'  => $filter_order_id,
			'filter_customer'  => $filter_customer,
			
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);
		
		
		$report_total = $this->model_vendor_order_report->getTotalReport($filter_data);
		$reports = $this->model_vendor_order_report->getReports($filter_data);
	 	
	 	if(isset($reports)) {
			foreach($reports as $report){
				$sellers = $this->model_vendor_vendor->getVendor($report['vendor_id']);
				
				$status_info = $this->model_vendor_order_report->getOrderStatus($report['order_status_id']);
				if(isset($status_info['name'])){
					$statusname = $status_info['name'];
				} else {
					$statusname ='';
				}
				$data['reports'][] = array(
					'order_product_id'=>$report['order_product_id'],
					'order_id'      =>$report['order_id'],
					'firstname'     =>$report['firstname']. ' '.$report['lastname'],
					'name'     		=>$report['name'],
					'total'     	=>$report['total'],
					
					'statusname'    =>$statusname,
					'date_added'	=>date($this->language->get('date_format_short'), strtotime($report['date_added'])),
					'view'      => $this->url->link('vendor/latestorder/letestview', '&order_id=' . $report	['order_id'])
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
	 
		$data['sort_order_id']  = $this->url->link('vendor/order_report', '' . '&sort=o.order_id' . $url, true);
		
		$data['sort_customer']  = $this->url->link('vendor/order_report', '' . '&sort=o.customer' . $url, true);
		
		$data['sort_status']  	= $this->url->link('vendor/order_report', '' . '&sort=o.status' . $url, true);
		$data['sort_date']  	= $this->url->link('vendor/order_report', '' . '&sort=vop.date' . $url, true);
				  
		$url = '';
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
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
		$pagination->url   	= $this->url->link('vendor/order_report', '' . $url . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($report_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($report_total - $this->config->get('config_limit_admin'))) ? $report_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $report_total, ceil($report_total / $this->config->get('config_limit_admin')));
		
		
		$data['filter_order_id']	= $filter_order_id;
		$data['filter_customer']	= $filter_customer;
		
		$data['sort']		= $sort;
		$data['order']		= $order;
		
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$data['header']      = $this->load->controller('vendor/header');
		$data['column_left'] = $this->load->controller('vendor/column_left');
		$data['footer']      = $this->load->controller('vendor/footer');
		
		$this->response->setOutput($this->load->view('vendor/order_report', $data));
	}
	
	
}
?>