<?php
class ModelVendorEnquiry extends Model {
	
	public function addEnquiry($product_id,$data) {
		
		$sql="INSERT INTO " . DB_PREFIX . "vendor_inquiry set customer_id='".$this->customer->getId()."',vendor_id='".(int)$data['vendor_id']."',product_id='".(int)$product_id."',name='".$this->db->escape($data['name'])."',email='".$this->db->escape($data['email'])."',telephone='".$this->db->escape($data['telephone'])."',description='".$this->db->escape($data['description'])."',status='1',date_added=now()";
		$this->db->query($sql);

	/// Seller And Customer To Mail ///
		$this->load->model('vendor/mail');
		$this->load->model('vendor/product');
		$this->load->model('account/customer');
		$this->load->model('vendor/vendor');
		
		$sellertype = 'seller_and_customer_enquiry_email';
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);

		if(isset($this->request->get['product_id'])) {
			$product_info = $this->model_vendor_product->getProduct($this->request->get['product_id']);
		}
		
		if(isset($product_info['product_id'])) {
			$vendor_info = $this->getProductVendor($product_info['product_id']);
		}
 		
		
		
		if(isset($vendor_info['vendor_id'])) {
			$seller_info = $this->model_vendor_vendor->getVendor($vendor_info['vendor_id']);
		} else {
			$seller_info = $this->model_vendor_vendor->getVendor($data['vendor_id']);
		}

		if(isset($seller_info['email'])) {
			$data['selleremail'] = $seller_info['email'];
		} else {
			$data['selleremail'] ='';
		}

		if(isset($mailinfo['status'])){
			if($this->customer->getId()) {
				$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
			}

			if(isset($customer_info['firstname'])) {
				$data['customername'] = $customer_info['firstname'].' '. $customer_info['lastname'];
			} else {
				$data['customername'] ='';
			}
			if(isset($customer_info['email'])) {
				$data['customeremail'] = $customer_info['email'];
			} else {
				$data['customeremail'] ='';
			}
			
			$find = array(
			 	'{name}',									
			 	'{email}',									
			 	'{telephone}',									
			 	'{customername}',									
			 	'{customeremail}',									
			 	'{message}'									
			);

			$replace = array(
				'name'    		=> $data['name'],
				'email'    		=> $data['email'],
				'telephone'    	=> $data['telephone'],
				'customername'  => $data['customername'],
				'customeremail' => $data['customeremail'],
				'message' => $data['description'],
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
			
			$mail->setTo($data['selleremail']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
			
		}
	
	}

	public function getTotalgetEnquiries($data){
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_inquiry WHERE vendor_id='".$this->vendor->getId()."'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_product'])) {
			$sql .= " AND product_id LIKE '" . $this->db->escape($data['filter_product']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$sql .= " AND status LIKE '" . $this->db->escape($data['filter_status']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND date_added LIKE '" . $this->db->escape($data['filter_date_added']) . "%'";
		}

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getEnquiries($data){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_inquiry WHERE vendor_id='".$this->vendor->getId()."'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_product'])) {
			$sql .= " AND product_id LIKE '" . $this->db->escape($data['filter_product']) . "%'";
		}

		if (!empty($data['filter_status'])) {
			$sql .= " AND status LIKE '" . $this->db->escape($data['filter_status']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND date_added LIKE '" . $this->db->escape($data['filter_date_added']) . "%'";
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getCustomer($customer_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id='".(int)$customer_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	public function getProductVendor($product_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product WHERE product_id ='".$product_id. "'";
								
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getProduct($product_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "product WHERE product_id='".$product_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
}
?>