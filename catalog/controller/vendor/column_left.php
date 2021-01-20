<?php
class ControllerVendorColumnLeft extends Controller {
	public function index() {
		$this->load->language('vendor/column_left');
		$vendor_id = $this->vendor->getId();
		
		$data['text_dashboard'] = $this->language->get('text_dashboard');
		$data['text_attribute'] = $this->language->get('text_attribute');
		$data['text_attribute_group'] = $this->language->get('text_attribute_group');
		$data['text_information'] = $this->language->get('text_information');
		$data['text_download'] = $this->language->get('text_download');
		$data['text_manufacturer'] = $this->language->get('text_manufacturer');
		$data['text_product'] = $this->language->get('text_product');
		$data['text_review'] = $this->language->get('text_review');
		$data['text_option'] = $this->language->get('text_option');
		$data['text_recurring'] = $this->language->get('text_recurring');
		$data['text_seller'] = $this->language->get('text_seller');
		$data['text_manageseller'] = $this->language->get('text_manageseller');
		$data['text_store'] = $this->language->get('text_store');
		$data['text_profile'] = $this->language->get('text_profile');
		$data['text_change'] = $this->language->get('text_change');
		$data['text_income'] = $this->language->get('text_income');
		$data['text_commission'] = $this->language->get('text_commission');
		
		$data['text_orders'] = $this->language->get('text_orders');
		$data['text_question'] = $this->language->get('text_question');
		
		$data['dashboard'] = $this->url->link('vendor/dashboard', '',true);		
		$data['attribute'] = $this->url->link('vendor/attribute', '',true);		
		$data['attribute_group'] = $this->url->link('vendor/attribute_group', '',true);		
		$data['download'] = $this->url->link('vendor/download', '',true);		
		$data['information'] = $this->url->link('vendor/information', '',true);		
		$data['manufacturer'] = $this->url->link('vendor/manufacturer', '',true);		
		$data['question'] = $this->url->link('vendor/questions', '',true);		
		$data['option'] = $this->url->link('vendor/option', '',true);		
		$data['product'] = $this->url->link('vendor/product', '',true);		
		$data['recurring'] = $this->url->link('vendor/recurring', '',true);		
		$data['review'] = $this->url->link('vendor/review', '',true);		
		$data['vendor'] = $this->url->link('vendor/vendor', '',true);		
		$data['edit_seller'] = $this->url->link('vendor/edit', 'vendor_id='. $vendor_id, true);		
		$data['change'] = $this->url->link('vendor/changepassword', '',true);		
		$data['store_info'] = $this->url->link('vendor/store', '',true);		
		$data['seller_profile'] = $this->url->link('vendor/vendor_profile', '',true);		
		$data['seller_income'] = $this->url->link('vendor/income', '',true);		
		$data['seller_commission'] = $this->url->link('vendor/commission', '',true);		
		$data['orders'] = $this->url->link('vendor/order_report', '',true);		
			return $this->load->view('vendor/column_left', $data);
		
		
		
	}
}
