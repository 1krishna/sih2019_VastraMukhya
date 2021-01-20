<?php
class ControllerVendorMybalance extends Controller {
	public function index() {
		$this->load->language('vendor/mybalance');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/mybalance');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		$data['balance'] = $this->url->link('vendor/income');

		$filter_data=array(
			'vendor_id' 	=> $this->vendor->getId(),
			//'customer_id' 	=> $this->customer->getId()
		);

		//$seller_info = $this->model_vendor_vendor->getVendor($filter_data);

		$data['total'] = $this->model_vendor_mybalance->getVendorTotal($filter_data);

		$data['totalcommission'] = $this->model_vendor_mybalance->getTotalAmount($filter_data);
		
		$data['totalamount'] = $data['total']-$data['totalcommission'];

		$data['payamount'] = $this->model_vendor_mybalance->getAmount($filter_data);

		$data['remaining_amount'] = $this->currency->format($data['totalamount']-$data['payamount'],$this->session->data['currency']);
		
		return $this->load->view('vendor/mybalance', $data);
	}
}
