<?php
class ModelVendorVendor extends Model {
	public function addVendor($data) {
		$sql = "INSERT INTO " . DB_PREFIX . "vendor SET display_name = '" . $this->db->escape($data['display_name']) . "',firstname = '" . $this->db->escape($data['firstname']) . "',lastname = '" . $this->db->escape($data['lastname']) . "',email = '" . $this->db->escape($data['email']) . "',telephone = '" . $this->db->escape($data['telephone']) . "',fax = '" . $this->db->escape($data['fax']) . "',company = '" . $this->db->escape($data['company']) . "',address_1 = '" . $this->db->escape($data['address_1']) . "',address_2 = '" . $this->db->escape($data['address_2']) . "',map_url = '" . $this->db->escape($data['map_url']) . "',city = '" . $this->db->escape($data['city']) . "',country_id = '" .$data['country_id'] . "',zone_id = '" .$data['zone_id'] . "',salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',status = '1',approved = '1',about = '" . $this->db->escape($data['about']) . "',image='".$data['image']."',logo='".$data['logo']."',store_about='".$this->db->escape($data['store_about'])."',banner='".$data['banner']."',payment_method='".$this->db->escape($data['payment_method'])."',paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='".$this->db->escape($data['tax_number'])."',shipping_charge='".$this->db->escape($data['shipping_charge'])."',date_added = NOW()";
		$this->db->query($sql);
		$vendor_id = $this->db->getLastId();
				
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_description SET vendor_id='".(int)$vendor_id."',language_id = '" . (int)$language_id ."',name='".$this->db->escape($value['name'])."',description='".$this->db->escape($value['description'])."',meta_description='".$this->db->escape($value['meta_description'])."',meta_keyword='".$this->db->escape($value['meta_keyword'])."',shipping_policy='".$this->db->escape($value['shipping_policy'])."',return_policy='".$this->db->escape($value['return_policy'])."'"); 
			}
		}
		
		// SEO URL
		if (isset($data['vendor_seo_url'])) {
			foreach ($data['vendor_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'vendor_id=" . (int)$vendor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		/// Seller Signup To Mail ///
		$this->load->model('vendor/mail');
		$sellertype = 'seller_signup_mail';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		
		/*Status Enabled*/
		if(isset($mailinfo['status'])){

			$this->load->model('localisation/country');
			$this->load->model('localisation/zone');
			$country_info = $this->model_localisation_country->getCountry($data['country_id']);
			if(isset($country_info['name'])) {
				$countryname = $country_info['name'];
			} else {
				$countryname = '';
			}

			$zone_info = $this->model_localisation_zone->getZone($data['zone_id']);
			if(isset($zone_info['name'])) {
				$zonename = $zone_info['name'];
			} else {
				$zonename = '';
			}

			$find = array(
				'{display_name}',
				'{email}',											
				'{telephone}',											
				'{address_1}',										
				'{company}',										
				'{countryname}',										
				'{zonename}',										
				'{city}',										
				'{loginlink}'										
			);
			$replace = array(
				'display_name'	=> $data['display_name'],
				'email' 		=> $data['email'],
				'telephone' 	=> $data['telephone'],
				'address_1' 	=> $data['address_1'],
				'company' 		=> $data['company'],
				'countryname' 	=> $countryname,
				'zonename' 		=> $zonename,
				'city' 		    => $data['city'],
				'loginlink' 	=> $this->url->link('vendor/login', '', 'SSL') . "\n\n"
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

			$mail->setTo($data['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
					
		}

		return $vendor_id;			
			
    /// Seller Signup To Mail ///		
		
	}
	
	public function editVendor($vendor_id,$data){
		
		
		$sql="update " . DB_PREFIX . "vendor set display_name = '" . $this->db->escape($data['display_name']) . "',firstname = '" . $this->db->escape($data['firstname']) . "',lastname = '" . $this->db->escape($data['lastname']) . "',telephone = '" . $this->db->escape($data['telephone']) . "',fax = '" . $this->db->escape($data['fax']) . "',company = '" . $this->db->escape($data['company']) . "',address_1 = '" . $this->db->escape($data['address_1']) . "',address_2 = '" . $this->db->escape($data['address_2']) . "',map_url = '" . $this->db->escape($data['map_url']) . "',facebook_url = '" . $this->db->escape($data['facebook_url']) . "',google_url = '" . $this->db->escape($data['google_url']) . "',city = '" . $this->db->escape($data['city']) . "',country_id = '" .$data['country_id'] . "',zone_id = '" .$data['zone_id'] . "',about = '" . $this->db->escape($data['about']) . "',image='".$data['image']."',logo='".$data['logo']."',store_about='".$this->db->escape($data['store_about'])."',banner='".$data['banner']."',payment_method='".$this->db->escape($data['payment_method'])."',paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "',tax_number='".$this->db->escape($data['tax_number'])."',shipping_charge='".$this->db->escape($data['shipping_charge'])."',store_logowidth='".$this->db->escape($data['store_logowidth'])."', store_logoheight='".$this->db->escape($data['store_logoheight'])."', store_bannerwidth='".$this->db->escape($data['store_bannerwidth'])."',  store_bannerheight='".$this->db->escape($data['store_bannerheight'])."', status = '1',approved = '1' where vendor_id='".$this->vendor->getId()."'";
		
		$this->db->query($sql);
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_description WHERE vendor_id = '" . (int)$vendor_id . "'");
		if (isset($data['store_description'])) {
			foreach ($data['store_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_description SET vendor_id='".(int)$vendor_id."',language_id = '" . (int)$language_id ."',name='".$this->db->escape($value['name'])."',description='".$this->db->escape($value['description'])."',meta_description='".$this->db->escape($value['meta_description'])."',meta_keyword='".$this->db->escape($value['meta_keyword'])."',shipping_policy='".$this->db->escape($value['shipping_policy'])."',return_policy='".$this->db->escape($value['return_policy'])."'"); 
			}
		}
		
		// SEO URL
		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'vendor_id=" . (int)$vendor_id . "'");
		
		if (isset($data['vendor_seo_url'])) {
			foreach ($data['vendor_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'vendor_id=" . (int)$vendor_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
	}

	public function verifyPassword($vendor_id,$data){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . $this->vendor->getId() . "' AND (password = SHA1(CONCAT(salt, SHA1(CONCAT(salt, SHA1('" . $this->db->escape($data['oldpassword']) . "'))))) OR password = '" . $this->db->escape(md5($data['oldpassword'])) . "')");
		
		return $query->row['total'];
	}

	public function editPassword($vendor_id,$data){
		$sql = "update " .DB_PREFIX . "vendor SET salt = '" . $this->db->escape($salt = token(9)) . "',password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "',date_modified=now() WHERE vendor_id = '" . $this->vendor->getId() ."'";
		$query = $this->db->query($sql);
	}
	
	public function getVendorStoreDescriptions($vendor_id) {
		$store_descriptio_data = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX ."vendor_description WHERE vendor_id = '" . (int)$this->vendor->getId() . "'");
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
	
	public function getOrderStatus($order_status_id){
		$sql="select * from " . DB_PREFIX . "order_status where order_status_id='".$order_status_id."' and language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getVendor($vendor_id=0){
		/* 01-02-2019 approved code */
		$sql = "SELECT *,v.vendor_id as vendor_id FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) LEFT JOIN ".DB_PREFIX."vendor_review vr ON(v.vendor_id = vr.vendor_id) WHERE v.vendor_id='".$vendor_id."' AND approved!=0";
		$query=$this->db->query($sql);
		
		return $query->row;
	}

	public function getStorename($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id='".$vendor_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}

	public function getVendorLogo($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id='".$this->vendor->getId()."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function getVendors($data){
		/* 01-02-2019 approved code */
			$sql="select * from " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd on(v.vendor_id = vd.vendor_id) where v.vendor_id<>0  AND v.approved!=0  AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
	public function getVendorByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row;
	}
	
	public function getTotalVendorByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row['total'];
	}
	
	public function getTotalReviews($vendor_id){
		$sql="select count(*) as total from " . DB_PREFIX . "vendor_review where vendor_id='".$this->vendor->getId()."'";
		
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".$data['vendor_id']."'";
		}
		$query=$this->db->query($sql);
		return $query->row['total'];
	}
	
	
	
	
	public function getTotalOrders($vendor_id){
		$sql="select count(*) as total from " . DB_PREFIX . "vendor_order_product where vendor_id='".$this->vendor->getId()."'";
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".$data['vendor_id']."'";
		}
		$query=$this->db->query($sql);
		return $query->row['total'];
	}

	public function getOrder($order_id){
		$sql="select * from `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (o.order_id = vop.order_id)  where o.order_id='".$order_id."' AND vop.vendor_id!=0";
		
		$query = $this->db->query($sql);
	
		return $query->row;
	}

	public function getOrderProduct($order_product_id){
		$sql="select * from " . DB_PREFIX . "vendor_order_product where order_product_id='".$order_product_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getOrderTotals($order_id){
		$sql="select * from " . DB_PREFIX . "order_total where order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getOrders($data){
		$sql = "SELECT *,vop. order_status_id, vop.total as vtotal FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id) WHERE vop.vendor_id!=0";
		
		//$sql .= " ORDER BY  vop.order_id DESC";
		
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".$data['vendor_id']."'";
		}
		
		$sql .= " ORDER BY  vop.order_id DESC";

		$query = $this->db->query($sql);
	
		return $query->rows;	
 	}
	
	public function getAboutStore($vendor_id){
		$sql="select * from " . DB_PREFIX . "vendor where vendor_id='".$vendor_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function getReviewField($review_id){
		$sql="select * from " . DB_PREFIX . "vendor_review_field_submit where review_id='".(int)$review_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function getReviews($data){
		$sql="select * from " . DB_PREFIX . "vendor_review where review_id<>0";
			
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".$data['vendor_id']."'";
		}
		
		//$sql .= " ORDER BY  date_added DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
// Seller Review Start ///	
	public function getFieldReviews($vendor_id){
		$sql="select * from " . DB_PREFIX . "vendor_review WHERE vendor_id='".$vendor_id."' AND status =1";
		
		//$sql .= " ORDER BY  date_added DESC";
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		
		$query=$this->db->query($sql);
	//	print_r($query->rows); die();
		return $query->rows;
	}
	
	public function getField($review_id, $vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit vrfs LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrfs.rf_id = vrfd.rf_id) LEFT JOIN ". DB_PREFIX ."vendor_review vr ON (vrfs.review_id = vr.review_id) WHERE vrfs.vendor_id = '".$vendor_id."' AND vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "' and vrfs.review_id='".$review_id."'";
		
		$query=$this->db->query($sql);
		return $query->rows;
	}
	
// Seller Review End ///
	
	public function getProductReview($vendor_id){
		$sql="select * from " . DB_PREFIX . "vendor_to_review where vendor_id='".$this->vendor->getId()."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function getProductReviews($vendor_id){
		$sql="select * from " . DB_PREFIX . "vendor_to_review where vendor_id='".$vendor_id."'";
				
		$query=$this->db->query($sql);
		return $query->row;
	}
			
	public function getReviewFields($data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_review_field vrf LEFT JOIN " . DB_PREFIX . "vendor_review_field_description vrfd ON (vrf.rf_id = vrfd.rf_id) WHERE vrfd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (isset($data['filter_status'])){
		 	$sql .=" and vrf.status like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_name'])){
		 	$sql .=" and vrfd.field_name like '".$this->db->escape($data['filter_name'])."%'";
		}
		
		$sort_data = array(
			'vrfd.field_name',
			'vrf.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY vrfd.field_name";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
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
	
	public function getProduct($product_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' and p.product_id='".$product_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}
	
	public function addReview($data,$vendor_id){
		$sql="INSERT INTO " . DB_PREFIX . "vendor_review set text='".$this->db->escape($data['text'])."',vendor_id='".$vendor_id."',customer_id='".$this->customer->getId()."',status=1,date_added=now()";
		$this->db->query($sql);
		$review_id = $this->db->getLastId();
		
		if (isset($data['reviewfield'])) {
			foreach ($data['reviewfield'] as $key => $value) {
				$this->db->query("INSERT INTO " .DB_PREFIX . "vendor_review_field_submit SET 
				review_id ='" . (int)$review_id . "',
				rf_id ='" . (int)$key . "',
				vendor_id='".$vendor_id."',
				customer_id='".$this->customer->getId()."',
				value='".$this->db->escape($value)."'
				"); 
			}
		}
		/* new code */
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "vendor_to_review SET review_id = '" . (int)$review_id . "', vendor_id = '" . $this->vendor->getId() . "'");
		/* new code */
	}
	
	public function getFieldSubmits($review_id) {
		$form_field_data = array();
		
		$form_field_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_review_field_submit where review_id='".$review_id."'");
		
		foreach ($form_field_query->rows as $key => $form_field) {
			
			$form_field_data[] = array(
				'rf_id' 		=> $form_field['rf_id'],
				'review_id' 		=> $form_field['review_id'],
				'value' 		=> $form_field['value']
			);
			
		}
		return $form_field_data;
	}
	
	public function getTotalSellerReview($vendor_id) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review WHERE vendor_id='".$vendor_id."' AND status=1 AND review_id<>0";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getTotalCollections($vendor_id){
		//$sql="select count(*) as total from " . DB_PREFIX . "vendor_to_product where vendor_id='".$vendor_id."'";
		$sql = "SELECT count(*) as total FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product p ON (v2p.product_id = p.product_id) WHERE v2p.vendor_id='".$vendor_id."' and status=1";
		$query=$this->db->query($sql);
		return $query->row['total'];
	}
	
	
	public function getProRev($product_id){
		$sql="select * from " . DB_PREFIX . "review where product_id='".$product_id."'";
		$query=$this->db->query($sql);
		return $query->row;
	}

	public function getWriteReview($vendor_id){
	
		$sql="select * from " . DB_PREFIX . "vendor_review where customer_id='".$this->customer->getId()."' AND vendor_id='".$vendor_id."'";
				
		$query=$this->db->query($sql);
		return $query->row;
	}

	public function getVendorProduct($data){
		
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product v2p LEFT JOIN " . DB_PREFIX . "product_to_category v2c ON (v2p.product_id = v2c.product_id) WHERE v2p.vendor_id<>0";
		
		if(isset($data['vendor_id'])){
			$sql .= " and vendor_id='".$data['vendor_id']."'";
		}

		$query=$this->db->query($sql);
		return $query->rows;
	}
	
	public function getSellerProduct($product_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_to_product WHERE product_id='".$product_id."'";
		
		$query=$this->db->query($sql);
		return $query->row;
	}
		
	public function getSellerInfo($vendor_id){
		$sql="select *, c.name as countryname From " . DB_PREFIX . "vendor v LEFT JOIN ".DB_PREFIX."country c on(v.country_id = c.country_id) where v.vendor_id='".$vendor_id."'";
		
		$query=$this->db->query($sql);
		
		return $query->row;
	}
	
	
	
// Add Tracking	// 
	public function addTracks($order_id, $data) {
		$sql="update " . DB_PREFIX . "vendor_order_product set
		tracking='".(int)$data['tracking']."',
		date_modified=now() where order_id='".$order_id."'";
		$this->db->query($sql);
	}
// Add Tracking	 //	
	
// Add Order Status	// 
	public function addOrdeStatus($order_id,$data) {
		$sql="update " . DB_PREFIX . "vendor_order_product set
		order_status_id='".$data['order_status_id']."',
		date_modified=now() where order_id='".$order_id."'";
		
		$this->db->query($sql);
		/* 30-01-2019 */
		$this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id='".$data['order_status_id']."',
		date_modified=now() WHERE order_id = '" . (int)$order_id . "'");
		/* 30-01-2019 */
		
		/// Seller Order Status To Mail ///
		$this->load->model('vendor/mail');
		$this->load->model('vendor/vendor');
		$sellertype = 'seller_order_status_update_email';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		
		$sellerorder_info = $this->model_vendor_vendor->getSellerOrder($order_id);
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
		return $order_id;
		
	}
	
	public function getSellerOrder($order_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='".$order_id."' AND vendor_id!=0";
		$query = $this->db->query($sql);
		return $query->row;
	}
    
    //new code//
    public function getSellerOrders($order_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product where order_id='".$order_id."' AND vendor_id!=0";
		$query = $this->db->query($sql);
		return $query->rows;
	}
    //new code//
	
	public function getLayouts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "layout";

		$sort_data = array('name');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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
	
	public function getTotalCategory($vendor_id, $category_id){
		$sql="select COUNT(*) as total from " . DB_PREFIX . "product_to_category pc LEFT JOIN ".DB_PREFIX ."vendor_to_product vp on(pc.product_id = vp.product_id) WHERE pc.category_id ='" . (int)$category_id . "' AND vp.vendor_id='".$vendor_id."'";
		
		$query=$this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getTotalProducts($vendor_id){
		$sql="select count(*) as total from " . DB_PREFIX . "vendor_to_product where vendor_id='".$vendor_id."'";
		$query=$this->db->query($sql);
	
		return $query->row['total'];
	}
// Add Order Status	 //	

	//// Product Review ////

	public function getProReview($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "review pr LEFT JOIN " . DB_PREFIX . "vendor_to_product v2r ON (pr.product_id = v2r.product_id) WHERE v2r.vendor_id='".$vendor_id."' and status='1'";
		
		$query=$this->db->query($sql);
		return $query->rows;
	}

	public function getTotalProductReview($vendor_id) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review pr LEFT JOIN " . DB_PREFIX . "vendor_to_product v2r ON (pr.product_id = v2r.product_id) WHERE v2r.vendor_id='".$vendor_id."' and status='1'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalProduct($vendor_id) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "vendor_to_product v2p ON (p.product_id = v2p.product_id) WHERE v2p.vendor_id='".$vendor_id."' and status=1";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function addFollow($vendor_id,$data) {
		$sql="INSERT INTO " . DB_PREFIX . "vendor_follow set
		customer_id='".(int)$this->customer->getId()."',
		vendor_id='".$data['vendor_id']."',
		date_added=now()";
		$this->db->query($sql);
	}

	public function getFollow($vendor_id){
		$sql = "select * from " . DB_PREFIX ."vendor_follow where vendor_id='".$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	
	}
	public function getFollows($vendor_id){
		$sql = "select * from " . DB_PREFIX ."vendor_follow where vendor_id='".$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->rows;
	
	}
		
	public function getDelete($vendor_id){
		$sql="delete  from " . DB_PREFIX . "vendor_follow where vendor_id='".$vendor_id."'";
		$query=$this->db->query($sql);
	}

	public function getTotalFollowers($vendor_id) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_follow WHERE vendor_id='".$vendor_id."'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	//// Product Review ////
	
	public function getCustomerOrder($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row;
	}

	public function getCustomerOrderStatus($order_status_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "'");

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
	
	
    ///new code///
     public function getCustomer($customer_id){
		$sql="SELECT * FROM " . DB_PREFIX . "customer where customer_id='".$customer_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
    
    public function getCustomerlog($customer_id){
		$sql="SELECT * FROM " . DB_PREFIX . "customer where customer_id='".$this->customer->getId()."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
    
    public function getVendorProductFeature($product_id){
		$sql="SELECT * FROM " . DB_PREFIX . "vendor_to_product where product_id='".$product_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
    
    public function getTotalVendors($data = array()) {
		/* 01-02-2019 approved code */
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . (int)$vendor_id . "' AND approved!=0");

		return $query->row['total'];
	}

	public function getVendorSumValue($vendor_id) {
    	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_review_field_submit WHERE vendor_id = '" . (int)$vendor_id . "'");
    	
    	$query1 = $this->db->query("SELECT SUM(value) AS total FROM " . DB_PREFIX . "vendor_review_field_submit WHERE vendor_id = '" . (int)$vendor_id . "'");
     
       if(!empty($query->row['total'])){

		return $query1->row['total']/$query->row['total'];

		}            	
	      	
    }
	///new code///
			
}