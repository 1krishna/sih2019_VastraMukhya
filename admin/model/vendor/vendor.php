<?php
class ModelVendorVendor extends Model {
	public function addVendor($data) {
		
		$sql="INSERT INTO " . DB_PREFIX . "vendor set display_name='".$this->db->escape($data['display_name'])."',firstname='".$this->db->escape($data['firstname'])."',lastname='".$this->db->escape($data['lastname'])."',email='".$this->db->escape($data['email'])."',image='".$this->db->escape($data['image'])."',telephone='".$this->db->escape($data['telephone'])."',fax='".$this->db->escape($data['fax'])."',about='".$this->db->escape($data['about'])."',company='".$this->db->escape($data['company'])."',address_1='".$this->db->escape($data['address_1'])."',address_2='".$this->db->escape($data['address_2'])."',status='".(int)$data['status']."',product_status='".(int)$data['product_status']."',city='".$this->db->escape($data['city'])."',postcode='".$this->db->escape($data['postcode'])."',country_id='".$this->db->escape($data['country_id'])."',zone_id='".$this->db->escape($data['zone_id'])."',salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',logo='".$data['logo']."',store_about='".$this->db->escape($data['store_about'])."',banner='".$data['banner']."',payment_method='".$this->db->escape($data['payment_method'])."',paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='".$this->db->escape($data['tax_number'])."',shipping_charge='".$this->db->escape($data['shipping_charge'])."',commission='".$this->db->escape($data['commission'])."',facebook_url = '" . $this->db->escape($data['facebook_url']) . "',google_url = '" . $this->db->escape($data['google_url']) . "',
		date_added=now()";
		$this->db->query($sql);
		
		$vendor_id = $this->db->getLastId();
				
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_description SET vendor_id='".(int)$vendor_id."',language_id = '" . (int)$language_id ."',name='".$this->db->escape($value['name'])."',description='".$this->db->escape($value['description'])."',meta_description='".$this->db->escape($value['meta_description'])."',meta_keyword='".$this->db->escape($value['meta_keyword'])."',shipping_policy='".$this->db->escape($value['shipping_policy'])."',return_policy='".$this->db->escape($value['return_policy'])."'"); 
			}
		}
		return $vendor_id;
	}

	public function editVendor($vendor_id, $data) {
		$sql="update " . DB_PREFIX . "vendor set display_name='".$this->db->escape($data['display_name'])."',email='".$this->db->escape($data['email'])."',firstname='".$this->db->escape($data['firstname'])."',lastname='".$this->db->escape($data['lastname'])."',image='".$this->db->escape($data['image'])."',postcode='".$this->db->escape($data['postcode'])."',telephone='".$this->db->escape($data['telephone'])."',fax='".$this->db->escape($data['fax'])."',about='".$this->db->escape($data['about'])."',company='".$this->db->escape($data['company'])."',address_1='".$this->db->escape($data['address_1'])."',address_2='".$this->db->escape($data['address_2'])."',status='".(int)$data['status']."',product_status='".(int)$data['product_status']."',city='".$this->db->escape($data['city'])."',country_id='".$this->db->escape($data['country_id'])."',zone_id='".$this->db->escape($data['zone_id'])."',logo='".$data['logo']."',store_about='".$this->db->escape($data['store_about'])."',banner='".$data['banner']."',payment_method='".$this->db->escape($data['payment_method'])."',paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='".$this->db->escape($data['tax_number'])."',shipping_charge='".$this->db->escape($data['shipping_charge'])."',commission='".$this->db->escape($data['commission'])."',facebook_url = '" . $this->db->escape($data['facebook_url']) . "',google_url = '" . $this->db->escape($data['google_url']) . "',map_url = '" . $this->db->escape($data['map_url']) . "',
		date_modified=now() where vendor_id='".$vendor_id."'";
	 	$this->db->query($sql);
		
		if ($data['password']) {
			$this->db->query("UPDATE " . DB_PREFIX . "vendor SET salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE vendor_id = '" . (int)$vendor_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_description SET vendor_id='".(int)$vendor_id."',language_id = '" . (int)$language_id ."',name='".$this->db->escape($value['name'])."',description='".$this->db->escape($value['description'])."',meta_description='".$this->db->escape($value['meta_description'])."',meta_keyword='".$this->db->escape($value['meta_keyword'])."',shipping_policy='".$this->db->escape($value['shipping_policy'])."',return_policy='".$this->db->escape($value['return_policy'])."'"); 
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'vendor_id=" . (int)$vendor_id . "'");
		
		if (isset($data['vendor_seo_url'])) {
			foreach ($data['vendor_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'vendor_id=" . (int)$vendor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
	}

	public function approve($vendor_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "vendor SET approved = '1' WHERE vendor_id = '" . (int)$vendor_id . "'");
		
		/// Seller Approve To Mail ///
		
		$this->load->model('vendor/mail');
		$sellertype = 'seller_approve_mail';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		$seller_info = $this->model_vendor_vendor->getVendor($vendor_id);
		//print_r($seller_info);die();
		/*Status Enabled*/
		if(isset($mailinfo['sellertype'])){
			$find = array(
				'{dname}',										
				'{emails}',										
				'{loginlink}'										
			);
			
			if(isset($seller_info['displa_name'])) {
				$dname = $seller_info['display_name'];
			} else {
				$dname='';
			}
			
			if(isset($seller_info['email'])) {
				$emails = $seller_info['email'];
			} else {
				$emails='';
			}
			
			$replace = array(
				'dname'		=> $dname,
				'emails'	=> $emails,
				'loginlink' => $this->url->link('vendor/login', '', 'SSL') . "\n\n"
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
	public function Disapprove($vendor_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "vendor SET approved = '0' WHERE vendor_id = '" . (int)$vendor_id . "'");
		
		/// Seller Approve To Mail ///
		
		$this->load->model('vendor/mail');
		$sellertype = 'seller_disapprove_mail';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		$seller_info = $this->model_vendor_vendor->getVendor($vendor_id);
		//print_r($seller_info);die();
		/*Status Enabled*/
		if(isset($mailinfo['sellertype'])){
			$find = array(
				'{disname}',										
				'{email}',										
				'{loginlink}'										
			);
			
			if(isset($seller_info['displa_name'])) {
				$disname = $seller_info['display_name'];
			} else {
				$disname='';
			}
			
			if(isset($seller_info['email'])) {
				$email = $seller_info['email'];
			} else {
				$email='';
			}
			
			$replace = array(
				'disname'		=> $disname,
				'email'	=> $email,
				'loginlink' => $this->url->link('vendor/login', '', 'SSL') . "\n\n"
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

			$mail->setTo($email);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
					
		}
	}
	
	public function deleteVendor($vendor_id) {
		$sql="delete  from " . DB_PREFIX . "vendor where vendor_id='".$vendor_id."'";
		$query=$this->db->query($sql);
		$sql="delete  from " . DB_PREFIX . "vendor_description where vendor_id='".$vendor_id."'";
		$query=$this->db->query($sql);
	}
    
    public function getVendoproductdelete($vendor_id) {
		$delproduct_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_to_product WHERE vendor_id='".(int)$vendor_id."'");
		foreach ($query->rows as $result) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET status = '0' WHERE product_id = '" . (int)$result['product_id'] . "'");
	 	}
	}

	
	public function getVendor($vendor_id){
		$sql = "SELECT DISTINCT *,v.vendor_id as vendor_id FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id='".$vendor_id."' AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query=$this->db->query($sql);
		
		return $query->row;
	}

	
	public function getVendorStoreDescriptions($vendor_id) {
		$store_descriptio_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		foreach ($query->rows as $result) {
			$store_descriptio_data[$result['language_id']] = array(
				'name'=> $result['name'],
				'meta_keyword'=> $result['meta_keyword'],
				'description'=> $result['description'],
				'meta_description'=> $result['meta_description'],
				'shipping_policy'=> $result['shipping_policy'],
				'return_policy'=> $result['return_policy'],
			);
	 	}
		return $store_descriptio_data;
	}

	public function getVendors($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor where vendor_id<>0";
		
		if (isset($data['filter_firstname'])){
		 	$sql .=" and firstname like '".$this->db->escape($data['filter_firstname'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" and status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$sort_data = array(
			'firstname',
			'lastname',
			'email'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY firstname";
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

	public function getTotalVendor($data) {
		$sql ="SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor where vendor_id<>0";
		
		if (isset($data['filter_firstname'])){
		 	$sql .=" and firstname like '".$this->db->escape($data['filter_firstname'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" and status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getVendorByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row;
	}
	
	public function getVendorSeoUrls($vendor_id) {
		$vendor_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'vendor_id=" . (int)$vendor_id . "'");

		foreach ($query->rows as $result) {
			$vendor_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $vendor_seo_url_data;
	}
}
