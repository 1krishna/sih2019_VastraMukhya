<?php
class ModelVendorquestions extends Model {
	public function addAnswers($user_question_id, $data) {
		$sql="INSERT INTO " . DB_PREFIX . "user_questions_answer SET 
		answer='".$this->db->escape($data['answer'])."',
		user_question_id='". $user_question_id."',
		date_added = now()";
		$this->db->query($sql);
		$user_question_answer_id = $this->db->getLastId();
		
		$user_info = $this->model_vendor_questions->getusers();	
		if(!empty($user_info['username'])){
			$usename = $user_info['username'];
		} else {
			$usename ='';	
		}
		if(!empty($user_info['email'])){
		$usenameemail = $user_info['email'];
		} else {
			$usenameemail ='';	
		}
		$this->db->query("UPDATE " . DB_PREFIX . "user_questions_answer SET name='".$usename."', email='".$usenameemail."'where user_question_answer_id='".$user_question_answer_id."'");

	}

	public function getquestions($data) {
		/* update */
		$sql = "SELECT *,uq.name as username FROM " . DB_PREFIX . "user_questions uq LEFT JOIN " . DB_PREFIX . "product_description pd ON (uq.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "vendor_to_product vtp ON (vtp.product_id = pd.product_id)  where uq.user_question_id<>0 AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		/* update */
		

		if (!empty($data['filter_name'])) {
			$sql .= " AND uq.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_product'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_product']) . "%'";
		}

		if (isset($data['filter_vendor'])) {
			$sql .= "AND vtp.vendor_id LIKE '" .$this->db->escape($data['filter_vendor']) . "%'";
		}

		$sort_data = array(
			'uq.name',
			'pd.name'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY uq.date_added";
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
		//echo "<pre>";
		//print_r($query); die();
		return $query->rows;
	}
    
    
	public function getQuestionById($user_question_id){		
		$data=array();
		$query = $this->db->query("SELECT *,uqa.date_added as answerdata FROM " . DB_PREFIX . "user_questions uq LEFT JOIN " . DB_PREFIX . "user_questions_answer uqa ON(uqa.user_question_id=uq.user_question_id) WHERE uq.user_question_id=".$user_question_id);
		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		
		foreach($query->rows as $result){
			$action = array();
			if($result['answered']==0){
				$action[] = array('text' => $this->language->get('text_answer'));
				$get_answer=$this->language->get('text_waiting');
				$get_answer_on ='';
			}else{
				$action[] = array('text' => $this->language->get('text_answered'));
				$get_answers=$this->model_catalog_questions->getAnswer($result['user_question_id']);
				$get_answer=$get_answers['answer'];
				$get_answer_on = date($this->language->get('date_format_short'), strtotime($get_answers['date_added']));
			}
			$product=$this->model_catalog_product->getProduct($result['product_id']);
			if ($product['image'] && file_exists(DIR_IMAGE . $product['image'])) {
				$image = $this->model_tool_image->resize($product['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.jpg', 40, 40);
			}
			$data=array(
					'user_question_id' 	=> $result['user_question_id'],
					'name'        		=> $result['name'],
					'product_name'   	=> $product['name'],
					'product_image'        		=> $image,
					'email'        		=> $result['email'],
					'question'        	=> $result['question'],
					'date_added'        => date( $this->language->get('date_format_short'),strtotime( $result['date_added'])),
					'selected'    		=> isset($this->request->post['selected']) && in_array($result['user_question_id'], $this->request->post['selected']),
					'action'      		=> $action,
					'answered'      	=> $result['answered'],
					'get_answer' 		=>  $get_answer,
					'get_answer_on' 	=>  $get_answer_on,
					'showquestion' 	=>  $result['showquestion'],
				);
		}
		return $data;
		
	}
	public function getTotalquestions($data) {
		//$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "user_questions");
		/* update */
		//$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "user_questions uq LEFT JOIN " . DB_PREFIX . "product_description pd ON (uq.product_id = pd.product_id)  where uq.user_question_id<>0 AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$sql = "SELECT COUNT(*) AS total ,uq.name as username FROM " . DB_PREFIX . "user_questions uq LEFT JOIN " . DB_PREFIX . "product_description pd ON (uq.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "vendor_to_product vtp ON (vtp.product_id = pd.product_id)  where uq.user_question_id<>0 AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
	
		/* update */
		

		if (!empty($data['filter_name'])) {
			$sql .= " AND uq.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_product'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_product']) . "%'";
		}

		if (isset($data['filter_vendor'])) {
			$sql .= "AND vtp.vendor_id LIKE '" .$this->db->escape($data['filter_vendor']) . "%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function addReply($user_question_id,$data){
		$this->db->query("insert into " . DB_PREFIX . "user_questions_answer set user_question_id='".$user_question_id."' , answer='".$this->db->escape($data['answer'])."',date_added=now()");

		$this->db->query("UPDATE " . DB_PREFIX . "user_questions SET answered=1 ,showquestion='".$data['showquestion']."' where user_question_id='".$user_question_id."'");

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_questions where user_question_id='".$user_question_id."'");
		if($query->row){

			$this->language->load('catalog/questions');
			$subject = sprintf($this->language->get('text_subject'), $this->config->get('config_name'));
			$message = sprintf($this->language->get('text_welcome'), $this->db->escape($query->row['name'])) . "<br /><br />";
			$message .= $this->language->get('text_questionDate').' '. date($this->language->get('date_format_short'),strtotime($this->db->escape($query->row['date_added']))) .'<br /><br />';
			$message .= $this->language->get('text_question'). '<br /><br />';
			$message .= 'Q: '.$this->db->escape($query->row['question']).'<br /><br />';

			$message .=  'A: '.$data['answer']. "\n\n";

			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($this->db->escape($query->row['email']));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
			$mail->setHtml(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$mail->send();
			
		}
	}
	
	public function getAnswer($user_question_id){
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "user_questions_answer where user_question_id='".$user_question_id."'")->row;		
	}
	
	public function getAnswers($user_question_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_questions_answer where user_question_id='".$user_question_id."' ORDER BY date_added DESC");		
		return $query->rows;
	}
	
	public function getusers(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user where user_id<>0");		
		return $query->row;
	}
	
	public function deletequestions($user_question_id){
		$this->db->query("DELETE FROM " . DB_PREFIX . "user_questions WHERE user_question_id = '" . (int)$user_question_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "user_questions_answer WHERE user_question_id = '" . (int)$user_question_id . "'");
	}
	
	public function deleteanswers($user_question_answer_id){
	$this->db->query("DELETE FROM " . DB_PREFIX . "user_questions_answer WHERE user_question_answer_id = '" . (int)$user_question_answer_id . "'");
	}
	
	public function approve($user_question_id){		
		$this->db->query("UPDATE " . DB_PREFIX . "user_questions SET approved = '1' WHERE user_question_id = '" . (int)$user_question_id . "'");	
	
	}
	
	public function Disapprove($user_question_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "user_questions SET approved = '0' WHERE user_question_id = '" . (int)$user_question_id . "'");
		
		
	}
	public function approveanswer($user_question_answer_id){		
		$this->db->query("UPDATE " . DB_PREFIX . "user_questions_answer SET approved = '1' WHERE user_question_answer_id = '" . (int)$user_question_answer_id . "'");	
	
	}
	
	public function Disapproveanswer($user_question_answer_id){
		
		$this->db->query("UPDATE " . DB_PREFIX . "user_questions_answer SET approved = '0' WHERE user_question_answer_id = '" . (int)$user_question_answer_id . "'");
		
		
	}	
	
	public function getQuestion($user_question_id){
		$query = $this->db->query("SELECT *, pd.name AS pname FROM " . DB_PREFIX . "user_questions uq LEFT JOIN " . DB_PREFIX . "product_description pd ON(uq.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product p ON(uq.product_id = p.product_id) WHERE uq.user_question_id='".$user_question_id."'");		
		return $query->row;
	}

	/// new code start

	public function getAnswersVendorName ($product_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor_to_product where product_id='".$product_id."'");		
		return $query->row;
	}
		public function getAnswersSingal($user_question_id){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "user_questions where user_question_id='".$user_question_id."'");		
		return $query->row;
	}

	public function getVendor($vendor_id=0) {
		$query = $this->db->query("SELECT DISTINCT *,v.vendor_id as vendor_id FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd ON (v.vendor_id = vd.vendor_id) WHERE v.vendor_id = '" . (int)$vendor_id . "' AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}


	/// new code  end 
}
?>