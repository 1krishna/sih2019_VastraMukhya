<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Voucher extends Controller {
	public function index() {
		$this->load->language('api/voucher');
		// Delete past voucher in case there is an error
		unset($this->session->data['voucher']);
		$json = array();
		
		$this->load->model('total/voucher');
		if (isset($this->request->post['voucher'])) {
			$voucher = $this->request->post['voucher'];
		} else {
			$voucher = '';
		}
		$voucher_info = $this->model_total_voucher->getVoucher($voucher);
		if ($voucher_info) {
			$this->session->data['voucher'] = $this->request->post['voucher'];
			$json['success'] = $this->language->get('text_success');
		} else {
			$json['error'] = $this->language->get('error_voucher');
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