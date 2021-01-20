<?php
class ModelVendorReport extends Model {
	
	public function addTracks($order_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_order_product set
		tracking='".(int)$data['tracking']."',
		date_modified=now() where order_id='".$order_id."'";
		$this->db->query($sql);
	}
	
	public function addOrdeStatus($order_id,$data) {
		$sql="update " . DB_PREFIX . "vendor_order_product set
		order_status_id='".(int)$data['order_status_id']."',
		date_modified=now() where order_id='".$order_id."'";
		$this->db->query($sql);
		
		
		/// Seller Order Status To Mail ///
		$this->load->model('vendor/mail');
		$this->load->model('vendor/vendor');
		$this->load->model('vendor/report');
		$sellertype = 'seller_order_status_update_email';
		
		$mailinfo = $this->model_vendor_report->getMailInfo($sellertype);
		
		$sellerorder_info = $this->model_vendor_report->getSellerOrder($order_id);
		$seller_info = $this->model_vendor_vendor->getVendor($sellerorder_info['vendor_id']);
		//print_r($seller_info);die();
		/*Status Enabled*/
		if(isset($mailinfo['status'])){
			$find = array(
				'{emails}',										
			);
			
			if(isset($seller_info['email'])) {
				$emails = $seller_info['email'];
			} else {
				$emails='';
			}
			
			$replace = array(
				'email' 	=> $emails,
			);
			

			$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));

			$message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['message']))));
			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($emails);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
					
		}
		
	}
	
	public function getSellerOrder($order_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public  function getMailInfo($sellertype){
		$query=$this->db->query("select * from " . DB_PREFIX . "vendor_mail vm LEFT JOIN " . DB_PREFIX . "vendor_mail_language vml on(vm.mail_id=vml.mail_id) where vm.sellertype='" .$sellertype."' and vml.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}
	
 	public function deleteReport($order_product_id){		
		$sql="delete  from " . DB_PREFIX . "vendor_order_product where order_product_id='".$order_product_id."'";
		$query=$this->db->query($sql);
 	} 
	
	public function getOrder($order_id){
		$sql="select * from `" . DB_PREFIX . "order` where order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getOrderStatus($order_status_id){
		$sql="select * from " . DB_PREFIX . "order_status where order_status_id='".$order_status_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getOrderProduct($order_id){
		$sql="SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getOrderTotals($order_id){
		$sql="select * from " . DB_PREFIX . "order_total where order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
		
 	public function getReports($data){
		$sql = "SELECT *,vop. order_status_id FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id) WHERE o.order_id<>0";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vop.vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" and o.customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" and vop.order_status_id like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" and o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$sort_data = array(
			'vop.order_product_id',
			'vop.name',
			'vop.date_added',
			'o.order_id',
			'o.firstname'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY o.order_id";
		}
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
		 	$sql .= " DESC";
		} else {
		 	$sql .= " ASC";
		}
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;	
 	}
 	public function getTotalReport($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id) WHERE vop.order_id<>0";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vop.vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" and o.customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" and vop.order_status_id like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" and o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getSellerProduct($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}
	
	public function deleteOrderReport($vendor_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id = '" . (int)$vendor_id . "'");
	}
	
}
?>