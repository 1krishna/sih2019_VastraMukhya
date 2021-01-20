<?php
class ControllerVendorTotalOrder extends Controller {
	public function index() {
		$this->load->language('vendor/totalorder');
		$this->load->model('vendor/vendor');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');
		$filter_data=array(
			'vendor_id' 	=> $this->vendor->getId(),
			//'customer_id' 	=> $this->customer->getId()
		);
		$data['totalorder'] = $this->model_vendor_vendor->getTotalOrders($filter_data);
		/* new code update */
		$data['orderhref'] = $this->url->link('vendor/order_report');
		/* new code update */
		return $this->load->view('vendor/totalorder', $data);
	}
}
