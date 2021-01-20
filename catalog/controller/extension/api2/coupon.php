<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Coupon extends Controller {
	public function index() {
		$this->load->language('api/coupon');
		// Delete past coupon in case there is an error
		unset($this->session->data['coupon']);
		$json = array();
		
		if (isset($this->request->post['coupon'])) {
			$coupon = $this->request->post['coupon'];
		} else {
			$coupon = '';
		}
		if(version_compare(VERSION, '2.3') >= 0){
			// 2.3 or later versions
			$this->load->model('extension/total/coupon');
			$coupon_info = $this->model_extension_total_coupon->getCoupon($coupon);
		}else if(version_compare(VERSION, '2.1') >= 0){
			// 2.1 or later versions
			$this->load->model('total/coupon');
			$coupon_info = $this->model_total_coupon->getCoupon($coupon);
		}else{
			$this->load->model('checkout/coupon');
			$coupon_info = $this->model_checkout_coupon->getCoupon($coupon);
		}
		if ($coupon_info) {
			$this->session->data['coupon'] = $this->request->post['coupon'];
			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->language->get('error_coupon');
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
}