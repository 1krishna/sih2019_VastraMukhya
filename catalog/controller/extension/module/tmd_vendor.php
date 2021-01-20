<?php
class ControllerExtensionModuleTmdVendor extends Controller {
	public function index() {
		$this->load->language('extension/module/tmd_vendor');

		$data['heading_title'] = $this->language->get('heading_title');

		/// seller Product Start ////
			$data['text_sellerinformation'] = $this->language->get('text_sellerinformation');
			$data['text_contactseller'] = $this->language->get('text_contactseller');
			$data['text_seller'] = $this->language->get('text_seller');
			$data['text_from'] = $this->language->get('text_from');
			$data['text_totalproducts'] = $this->language->get('text_totalproducts');
			
			$data['headingbg'] = $this->config->get('tmd_vendor_bgcolor');
			$data['textcolor'] = $this->config->get('tmd_vendor_textcolor');
			$imagetype = $this->config->get('tmd_vendor_imagetype');
			if($imagetype=='round'){
			$data['imgeborder'] = 'roundborder';
			} elseif($imagetype=='rect'){
				
			$data['imgeborder'] = 'rectborder';
			}
			
			
			$vendor_status = $this->config->get('tmd_vendor_status');
			
			$imagewidths = $this->config->get('tmd_vendor_imgwidth');
			$imageheights = $this->config->get('tmd_vendor_imgheight');
			
			if(!empty($imagewidths)){
				$imagewidth = $imagewidths;
			} else {
				$imagewidth = 100;
				
			}

			if(!empty($imageheights)){
				$imageheight = $imageheights;
			} else {
				$imageheight = 100;
				
			}	
			
			$this->load->model('vendor/vendor');
			$this->load->model('tool/image');
			$sellerproduct_info = $this->model_vendor_vendor->getSellerProduct($this->request->get['product_id']);
			if(isset($sellerproduct_info['vendor_id'])) {
				$seller_info = $this->model_vendor_vendor->getSellerInfo($sellerproduct_info['vendor_id']);
				
			}
			
			
			if(isset($seller_info['display_name'])){
				$dname = $seller_info['display_name'];
			} else {
				$dname='';
			}
			
			if(isset($seller_info['countryname'])){
				$data['countryname'] = $seller_info['countryname'];
			} else {
				$data['countryname']='';
			}
			
			if(isset($sellerproduct_info['vendor_id'])){
			$seller_vendor_id = $sellerproduct_info['vendor_id'];
			} else {				
			$seller_vendor_id='';	
			}
			$totalcount = $this->model_vendor_vendor->getTotalProducts($seller_vendor_id);
			
			
			if(isset($totalcount)){
				$data['totalproducts'] = $totalcount;
			} else {
				$data['totalproducts']='';
			}
			
			if(isset($seller_info['vendor_id'])){
				$vendor_ids = $seller_info['vendor_id'];
			} else {
				$vendor_ids='';
			}
			
			if(!empty($seller_info['image'])){
				$sellerimage = $this->model_tool_image->resize($seller_info['image'],$imagewidth,$imageheight);
			} else {
				$sellerimage = $this->model_tool_image->resize('placeholder.png',$imagewidth,$imageheight);
			}
			
			$data['sellerimage'] = $sellerimage;
			$data['dname'] = $dname;
			$data['href']  =$this->url->link('vendor/vendor_profile&vendor_id='.$vendor_ids, '', true);
			$data['vendor_ids']   = $vendor_ids;
					
		/// seller Product End ////	
		
	if(!empty($vendor_ids || $vendor_status=='1')){
		return $this->load->view('extension/module/tmd_vendor', $data);
		}
			
	}
}