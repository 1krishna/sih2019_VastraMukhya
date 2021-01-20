<?php
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date  		September 13, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */
 
class ControllerTshirtecommerceDesignerApi extends Controller
{
	public function login()
	{
		$data = array('error' => 0, 'id' => 0);
		$this->load->model('account/customer');
		$id = 0;
		$email = isset($this->request->post['username']) ? $this->request->post['username'] : '';
		$password = isset($this->request->post['password']) ? $this->request->post['password'] : '';
		$is_login = $this->customer->login($email, $password) ? 1 : 0;
		
		if ($this->customer->isLogged()) {
			$logged = array(
				'login' => true, 
				'email' => $email, 
				'id' => $this->customer->isLogged()
			);
			$_SESSION['is_logged'] = $logged;
			$this->session->data['is_logged'] = $logged;
			$is_login = 1;
			$id = md5($this->customer->isLogged());
		}
		
		$data['error'] = $is_login;
		$data['id'] = $id;
		
		echo json_encode($data);
		return;
	}
	
	// For customers
	public function valid_customer()
	{
		$id = 0;
		$logged = (isset($this->session->data['is_logged'])) ? $this->session->data['is_logged'] : array();
		$logged = $_SESSION['is_logged'];
		
		if ($logged['id'] > 0 && $this->customer->isLogged()) {
			$id = $this->customer->isLogged();
			if ($id > 0) $id = md5($id);
		}
		
		echo $id;
		return;
	}
}
