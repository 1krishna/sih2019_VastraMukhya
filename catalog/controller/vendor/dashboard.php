<?php
class ControllerVendorDashboard extends Controller {
	private $error = array();

	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}	
		$this->load->language('vendor/dashboard');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/notification');
		
		$this->document->setTitle($this->language->get('heading_title'));
				
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/dashboard', '', true)
		);
				
		$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/login', '', true));
		$data['heading_title'] 		= $this->language->get('heading_title');
		$data['text_yes'] 			= $this->language->get('text_yes');
		$data['text_no'] 			= $this->language->get('text_no');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		
		$data['button_continue'] 	= $this->language->get('button_continue');
		$data['button_upload'] 		= $this->language->get('button_upload');
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['total'] = $this->load->controller('vendor/total');
		$data['totalorder'] = $this->load->controller('vendor/totalorder');
		$data['totalproduct'] = $this->load->controller('vendor/totalproduct');
		$data['latestorder'] = $this->load->controller('vendor/latestorder');
		// 09 06 2018 ///
		$data['mybalance'] = $this->load->controller('vendor/mybalance');
		// 09 06 2018 ///
		
/// Seller Notification Start //		
		$data['sellernotifi']=array();
		$sellernotifis = $this->model_vendor_notification->getSellerMessages();
	
		foreach($sellernotifis as $sellernotifi){
			$data['sellernotifi'][]=array(
				'notification_id' => $sellernotifi['notification_id'],
				'message' 		  => html_entity_decode($sellernotifi['message']),
			);
			
		}
	
				
		$data['notifications']=array();
		$notinfos = $this->model_vendor_notification->getSellerNotification();
		foreach($notinfos as $notinfo){
			$data['notifications'][]=array(
				'notification_id' => $notinfo['notification_id'],
				'message' 		  => html_entity_decode($notinfo['message']),
			);
		}
/// Seller Notification End //	
		
		$data['column_left'] 	= $this->load->controller('vendor/column_left');
		$data['footer'] 		= $this->load->controller('vendor/footer');
		$data['header'] 		= $this->load->controller('vendor/header');
		
		
		$this->response->setOutput($this->load->view('vendor/dashboard', $data));
	}
		
}
