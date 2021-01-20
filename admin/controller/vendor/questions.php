<?php
class ControllerVendorquestions extends Controller {
	private $error = array();

	public function index() {
		$this->language->load('vendor/questions');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/questions');

		$this->getList();
	}
	public function answer() {
		$this->language->load('vendor/questions');
		$this->load->model('vendor/questions');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {


			if (empty( $this->request->post['answer'])) {
				$json['error'] = $this->language->get('error_noreplay');
			}

			if (!isset($json['error'])) {
			$this->model_vendor_questions->addReply($this->request->get['user_question_id'],$this->request->post);
			$json['success'] = $this->language->get('text_success');
			$json['attention'] = $this->language->get('text_wait');
			$json['updated_question'] = $this->model_vendor_questions->getQuestionById($this->request->get['user_question_id']);
			}
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function delete() {
		$this->language->load('vendor/questions');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/questions');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $questions_id) {
				$this->model_vendor_questions->deletequestions($questions_id);
			}
			$this->session->data['success'] = $this->language->get('text_successdelete');
			$url = '';
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
	
	public function approve(){
		$this->language->load('vendor/questions');
		$this->load->model('vendor/questions');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$approves = array();
		if (isset($this->request->post['selected'])){
			$approve = $this->request->post['selected'];
		} 
		elseif (isset($this->request->get['user_question_id'])){
			$approves[] = $this->request->get['user_question_id'];
		}
		if ($approves && $this->validateApprove()){
			foreach($approves as $user_question_id){
				$this->model_vendor_questions->approve($user_question_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList(); 
	}

	public function disapprove(){
		$this->language->load('vendor/questions');
		$this->load->model('vendor/questions');
		$this->document->setTitle($this->language->get('heading_title'));
	
		$approves = array();
		if (isset($this->request->post['selected'])){
			$approve = $this->request->post['selected'];
		} 
		elseif (isset($this->request->get['user_question_id'])){
			$approves[] = $this->request->get['user_question_id'];
		}
		if ($approves && $this->validateDesapprove()){
			foreach($approves as $user_question_id){
				$this->model_vendor_questions->Disapprove($user_question_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$url = '';
			if (isset($this->request->get['sort'])){
				$url .= '&sort=' . $this->request->get['sort'];
			}
			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			if (isset($this->request->get['page'])){
				$url .= '&page=' . $this->request->get['page'];
			}
			$this->response->redirect($this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		$this->getList(); 

	 }	
	 
	
	protected function getList() {

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		if (isset($this->request->get['filter_product'])) {
			$filter_product = $this->request->get['filter_product'];
		} else {
			$filter_product = null;
		}

		if (isset($this->request->get['filter_vendor'])) {
			$filter_vendor = $this->request->get['filter_vendor'];
		} else {
			$filter_vendor = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'uq.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . $this->request->get['filter_product'];
		}

		if (isset($this->request->get['filter_vendor'])) {
				$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}



		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}


	
		
	

		$filter_data = array(
			'filter_name'	=> $filter_name,
			'filter_product'	=> $filter_product,
			'sort'            => $sort,
			'filter_vendor'   => $filter_vendor,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);



		$questions_total = $this->model_vendor_questions->getTotalquestions($filter_data);
		$results = $this->model_vendor_questions->getquestions($filter_data);

		$this->load->model('tool/image');
		$this->load->model('catalog/product');
		$data['questions'] = array();

		foreach ($results as $result) {
			$action = array();
			if($result['answered']==0){
				$action[] = array('text' => $this->language->get('text_answer'));
				$get_answer=$this->language->get('text_waiting');
				$get_answer_on ='';
			}else{
				$action[] = array('text' => $this->language->get('text_answered'));
				$get_answers=$this->model_vendor_questions->getAnswer($result['user_question_id']);
				$get_answer=$get_answers['answer'];
				$get_answer_on = date($this->language->get('date_format_short'), strtotime($get_answers['date_added']));
			}
			
	
			if (!$result['approved']) {
				$approve = $this->url->link('vendor/questions/approve', 'user_token=' . $this->session->data['user_token'] . '&user_question_id=' . $result['user_question_id'] . $url, true);
			} else {
				$approve = '';
			}
			
			if ($result['approved']) {
				$disapproved = $this->url->link('vendor/questions/disapprove', 'user_token=' . $this->session->data['user_token'] . '&user_question_id=' . $result['user_question_id'] . $url, true);
			} else {
				$disapproved = '';
			}

				// new code start 
			$this->load->model('vendor/vendor');
			$productvendorid= $this->model_vendor_questions->getAnswersVendorName($result['product_id']);
			
			if(!empty($productvendorid['vendor_id'])){
				$vendor_idproduct=$productvendorid['vendor_id']; 
			}else{
				$vendor_idproduct='';
			}


			$sellers = $this->model_vendor_vendor->getVendor($vendor_idproduct);
			
			if(!empty($sellers['firstname'])){
				$sellername = $sellers['firstname'];
			} else {
				$sellername ='';
			}

			// new code end 
			
			$data['questions'][] = array(
				'user_question_id' 	=> $result['user_question_id'],
				'name'        		=> $result['username'],
				'product_name'   	=> $result['name'],
				'sellername'     => $sellername,
				'email'        		=> $result['email'],
				'question'        	=> $result['question'],
				'date_added'        => date( $this->language->get('date_format_short'),strtotime( $result['date_added'])),
				'selected'    		=> isset($this->request->post['selected']) && in_array($result['user_question_id'], $this->request->post['selected']),
				'action'      		=> $action,
				'answered'      	=> $result['answered'],
				'get_answer' 		=>  $get_answer,
				'get_answer_on' 	=>  $get_answer_on,
				'showquestion' 	    =>  $result['showquestion'],
				'approve'		    => $approve,
				'disapproved'	    => $disapproved,
                'view'              => $this->url->link('vendor/questions/info', 'user_token=' . $this->session->data['user_token'] . '&user_question_id=' . $result['user_question_id']. $url, true),
				
			);
		}
		
		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . $url, true),
      		'separator' => ' :: '
   		);
		


		$data['delete'] = $this->url->link('vendor/questions/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
        
        $data['setting'] = $this->url->link('vendor/questions/setting', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_wait'] = $this->language->get('text_wait');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['user_token'] =  $this->session->data['user_token'] ;

		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_list'] = $this->language->get('text_list');
		$data['button_reply'] = $this->language->get('button_reply');
		$data['button_view'] = $this->language->get('button_view');
		$data['button_setting'] = $this->language->get('button_setting');
		$data['button_approve'] = $this->language->get('button_approve');
		$data['button_desapprove'] = $this->language->get('button_desapprove');
		$data['column_approve'] = $this->language->get('column_approve');
		$data['entry_vendor'] = $this->language->get('entry_vendor');
		$data['text_none'] = $this->language->get('text_none');

		$data['column_productname'] = $this->language->get('column_productname');
		$data['column_productimage'] = $this->language->get('column_productimage');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_email'] = $this->language->get('column_email');
		$data['column_question'] = $this->language->get('column_question');
		$data['column_date'] = $this->language->get('column_date');

		$data['column_action'] = $this->language->get('column_action');
		$data['column_answered'] = $this->language->get('column_answered');
		$data['column_answered_date'] = $this->language->get('column_answered_date');

		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_delete'] = $this->language->get('button_delete');
 		$data['button_repair'] = $this->language->get('button_repair');
		$data['button_answer'] = $this->language->get('button_answer');
		$data['button_answerd'] = $this->language->get('button_answerd');
		$data['button_back'] = $this->language->get('button_back');

		$data['text_showQuestion'] = $this->language->get('text_showQuestion');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');


			
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}	

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

			if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . $this->request->get['filter_product'];
		}

		if (isset($this->request->get['filter_vendor'])) {
				$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_image'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . '&sort=image' . $url, true);
		$data['sort_productname'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_name'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . '&sort=uq.name' . $url, true);
		$data['sort_email'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . '&sort=uq.email' . $url, true);
		$data['sort_question'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . '&sort=uq.question' . $url, true);
		$data['sort_date_added'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . '&sort=uq.date_added' . $url, true);
	
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_product'])) {
			$url .= '&filter_product=' . $this->request->get['filter_product'];
		}

		if (isset($this->request->get['filter_vendor'])) {
				$url .= '&filter_vendor=' . $this->request->get['filter_vendor'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}


		$pagination = new Pagination();
		$pagination->total = $questions_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($questions_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($questions_total - $this->config->get('config_limit_admin'))) ? $questions_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $questions_total, ceil($questions_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_product'] = $filter_product;
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/questions_list', $data));
	}
    
    public function info() {
        $this->language->load('vendor/questions');
        $this->load->model('vendor/questions');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['heading_title'] = $this->language->get('heading_title');
        $data['entry_question'] = $this->language->get('entry_question');
        $data['user_token'] =  $this->session->data['user_token'] ;
        $data['column_answered'] = $this->language->get('column_answered');
        $data['column_email'] = $this->language->get('column_email');
        $data['column_answered_date'] = $this->language->get('column_answered_date');
        $data['column_answerby'] = $this->language->get('column_answerby');
     	$data['column_action'] = $this->language->get('column_action');
        $url = '';
        
        $data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'] . $url, true),
      		'separator' => ' :: '
   		);

   		if (isset($this->request->get['user_question_id'])) {
			$user_question_id = $this->request->get['user_question_id'];
		} else {
			$user_question_id = 0;
		}
		
		$questiononproducts=$this->model_vendor_questions->getQuestion($user_question_id);		
	
		$data['productlink'] = HTTP_CATALOG.'index.php?route=product/product'. '&product_id=' . $questiononproducts['product_id'];
		
		$this->load->model('tool/image');
		
			if (is_file(DIR_IMAGE . $questiononproducts['image'])) {
				$data['pimage'] = $this->model_tool_image->resize($questiononproducts['image'], 40, 40);
			} else {
				$data['pimage'] = $this->model_tool_image->resize('no_image.png', 40, 40);
			}
			
		$data['productname'] = $questiononproducts['pname'];
		$data['questiononproduct'] =$questiononproducts['question'];
   		$answers_info = $this->model_vendor_questions->getAnswers($user_question_id);
		
		foreach ($answers_info as $answer) {
			
					
			$singaleproduct   = $this->model_vendor_questions->getAnswersSingal($answer['user_question_id']);
			$productvendorid  = $this->model_vendor_questions->getAnswersVendorName($singaleproduct['product_id']);
			if(!empty($productvendorid)){
			$sellers  = $this->model_vendor_questions->getVendor($productvendorid['vendor_id']);		
			}else{
			$sellers = 0;	
			}
		
			if(!empty($answer['name'])){
				$answeusername = $answer['name'];
			} else {
				$answeusername ='';	
			}
			
			if(!empty($answer['email'])){
			 $answeuseremail = $answer['email'];
			} else {
			 $answeuseremail ='';	
			}
			
			
           if(!empty($answer['adminname'])){
				$usename = $answer['adminname'];
			} else {
				$usename =$sellers['firstname'];	
			}
			
			if(!empty($answer['adminemail'])){
			 $usenameemail = $answer['adminemail'];
			} else {
			 $usenameemail =$sellers['email'];	
			}
			
			
			$data['answers'][] = array(
			'user_question_answer_id'   => $answer['user_question_answer_id'],
			'name'  		            => $answeusername,
			'email'        		        => $answeuseremail,
			'usenameemail'        		=> $usenameemail,
			'usename'        		    => $usename,
			'answer'        	        => $answer['answer'],
			'date_added'                => date($this->language->get('date_format_short') ,strtotime($answer['date_added']))		
			);
		}
		
		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		if (isset($this->error['answer'])) {
			$data['error_answer'] = $this->error['answer'];
		} else {
			$data['error_answer'] = '';
		}
        $data['action'] = $this->url->link('vendor/questions/add', 'user_token=' . $this->session->data['user_token'] . '&user_question_id=' . $user_question_id.  $url, true);
		$data['cancel'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'], true);
		
        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/questions_info', $data));
    }
    
	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'vendor/questions')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('vendor/questions');

			$data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);

			$results = $this->model_vendor_questions->getCategories($data);

			foreach ($results as $result) {
				$json[] = array(
					'questions_id' => $result['questions_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->setOutput(json_encode($json));
	}


    public function setting() {
		$this->load->language('vendor/questions');

		$this->document->setTitle($this->language->get('heading_title'));
    
        $this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('questions', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
        
		$data['text_setting'] = $this->language->get('text_setting');
		$data['entry_askbg'] = $this->language->get('entry_askbg');
		$data['entry_askcolor'] = $this->language->get('entry_askcolor');
		$data['entry_btnbg'] = $this->language->get('entry_btnbg');
		$data['entry_btncolor'] = $this->language->get('entry_btncolor');
		$data['entry_qsbg'] = $this->language->get('entry_qsbg');
		$data['entry_qscolor'] = $this->language->get('entry_qscolor');
        $data['entry_ansbg'] = $this->language->get('entry_ansbg');
		$data['entry_anscolor'] = $this->language->get('entry_anscolor');
		$data['entry_anscolor'] = $this->language->get('entry_anscolor');
		$data['entry_captchastatus'] = $this->language->get('entry_captchastatus');
		$data['entry_answertext'] = $this->language->get('entry_answertext');

		$data['help_askbg'] = $this->language->get('help_askbg');
		$data['help_askcolor'] = $this->language->get('help_askcolor');
		$data['help_btnbg'] = $this->language->get('help_btnbg');
		$data['help_btncolor'] = $this->language->get('help_btncolor');
		$data['help_qsbg'] = $this->language->get('help_qsbg');
		$data['help_qscolor'] = $this->language->get('help_qscolor');
		$data['help_ansbg'] = $this->language->get('help_ansbg');
		$data['help_anscolor'] = $this->language->get('help_anscolor');
		$data['help_answertext'] = $this->language->get('help_answertext');
		$data['help_captchastatus'] = $this->language->get('help_captchastatus');
        
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_stay'] = $this->language->get('button_stay');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
        
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('vendor/questions/setting', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('vendor/questions', 'user_token=' . $this->session->data['user_token'], true);
		
        if (isset($this->request->post['questions_askbg'])) {
			$data['questions_askbg'] = $this->request->post['questions_askbg'];
		} else {
			$data['questions_askbg'] = $this->config->get('questions_askbg');
		}
    
        if (isset($this->request->post['questions_ask_color'])) {
			$data['questions_ask_color'] = $this->request->post['questions_ask_color'];
		} else {
			$data['questions_ask_color'] = $this->config->get('questions_ask_color');
		}
    
        if (isset($this->request->post['questions_btnbg'])) {
			$data['questions_btnbg'] = $this->request->post['questions_btnbg'];
		} else {
			$data['questions_btnbg'] = $this->config->get('questions_btnbg');
		}
    
        if (isset($this->request->post['questions_btn_color'])) {
			$data['questions_btn_color'] = $this->request->post['questions_btn_color'];
		} else {
			$data['questions_btn_color'] = $this->config->get('questions_btn_color');
		}
    
        if (isset($this->request->post['questions_qsbg'])) {
			$data['questions_qsbg'] = $this->request->post['questions_qsbg'];
		} else {
			$data['questions_qsbg'] = $this->config->get('questions_qsbg');
		}
    
        if (isset($this->request->post['questions_qscolor'])) {
			$data['questions_qscolor'] = $this->request->post['questions_qscolor'];
		} else {
			$data['questions_qscolor'] = $this->config->get('questions_qscolor');
		}
    
       if (isset($this->request->post['questions_ansbg'])) {
			$data['questions_ansbg'] = $this->request->post['questions_ansbg'];
		} else {
			$data['questions_ansbg'] = $this->config->get('questions_ansbg');
		}
    
        if (isset($this->request->post['questions_anscolor'])) {
			$data['questions_anscolor'] = $this->request->post['questions_anscolor'];
		} else {
			$data['questions_anscolor'] = $this->config->get('questions_anscolor');
		}
    
        if (isset($this->request->post['questions_captchastatus'])) {
			$data['questions_captchastatus'] = $this->request->post['questions_captchastatus'];
		} else {
			$data['questions_captchastatus'] = $this->config->get('questions_captchastatus');
		}
        
        if (isset($this->request->post['questions_answertext'])) {
			$data['questions_answertext'] = $this->request->post['questions_answertext'];
		} else {
			$data['questions_answertext'] = $this->config->get('questions_answertext');
		}

        
        

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('vendor/questions_setting', $data));
	}

	public function add() {
		$this->load->language('vendor/questions');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('vendor/questions');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_vendor_questions->addAnswers($this->request->get['user_question_id'],$this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';


			$this->response->redirect($this->url->link('vendor/questions/info', 'user_token=' . $this->session->data['user_token'] . '&user_question_id=' . $this->request->get['user_question_id']. $url, true));
		}
		$this->info();
	}
	
	public function deleteanswers() {
    	 $json = array();
    	$this->load->model('vendor/questions'); 
    	
		if(!empty($this->request->get['user_question_answer_id'])){
			$user_question_answer_id=$this->request->get['user_question_answer_id'];
		}else{
			$user_question_answer_id='';
		}

  	 	if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
	 		   $this->model_vendor_questions->deleteanswers($user_question_answer_id,$this->request->post);
	 			$json['success'] = $this->language->get('delete');
	 		}
  	       
  	     $this->response->addHeader('Content-Type: application/json');
         $this->response->setOutput(json_encode($json));
     }

     public function validateForm() {
		if (!$this->user->hasPermission('modify', 'vendor/questions')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['answer']) < 10) || (utf8_strlen($this->request->post['answer']) > 1000)) {
			$this->error['answer'] = $this->language->get('error_answer');
		}		

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
	
	protected function validateApprove(){
		if (!$this->user->hasPermission('modify', 'vendor/questions')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	    return !$this->error;
	}
	
	protected function validateDesapprove(){
		if (!$this->user->hasPermission('modify', 'vendor/questions')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
	    return !$this->error;

	}
	
}
?>