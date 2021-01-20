<?php
//turn off all warnings and notices for API calls
//error_reporting(E_ERROR);
//set_error_handler(null);

class ControllerExtensionApi2AuthHandler extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->model('account/customer');
		$this->load->language('account/login');
		$this->load->language('api/customer');
		
		$json = array();
		require_once( "hybridauth/hybridauth/Hybrid/Auth.php" );
		require_once( "hybridauth/hybridauth/Hybrid/thirdparty/Facebook/autoload.php");
		
		$base = HTTPS_SERVER . 'catalog/controller/api2/auth/hybridauth/hybridauth/';
		
		$config = array(
			"base_url" => $base,
			"providers" => array(
				"Google" => array(
					"enabled" => true,
					"keys" => array("id" => $this->config->get('i2csmobile_auth_google_id'), "secret" => $this->config->get('i2csmobile_auth_google_secret')),
				),
				"Facebook" => array(
					"enabled" => true,
					"keys" => array("id" => $this->config->get('i2csmobile_auth_facebook_id'), "secret" => $this->config->get('i2csmobile_auth_facebook_secret')),
					"trustForwarded" => false,
					"scope" => "email"
				),
				"Twitter" => array(
					"enabled" => true,
					"keys" => array("key" => $this->config->get('i2csmobile_auth_twitter_key'), "secret" => $this->config->get('i2csmobile_auth_twitter_secret')),
					"includeEmail" => true
				)
			),
			"debug_mode" => false,
			"debug_file" => "",
			);
		
 
		try{
			$hybridauth = new Hybrid_Auth( $config );
			
			if(isset($this->request->get['provider']) && isset($this->request->get['logout'])){
				$provider = $this->request->get['provider'];
				$auth = $hybridauth->authenticate($provider);
				$auth->logout();
			}else if(!isset($this->request->get['provider'])){
				$json['error'] = "No provider selected";
			}else{
				$provider = $this->request->get['provider'];
				$auth = $hybridauth->authenticate($provider);
				$user_profile = $auth->getUserProfile();
				
				#FIX
				$hybridauth_session_data = $hybridauth->getSessionData();

				if($this->validateUser($user_profile)){
					// Trigger customer pre login event
					$this->event->trigger('pre.customer.login');

					// Unset guest
					unset($this->session->data['guest']);

					// Default Shipping Address
					$this->load->model('account/address');

					if ($this->config->get('config_tax_customer') == 'payment') {
						$this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
					}

					if ($this->config->get('config_tax_customer') == 'shipping') {
						$this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
					}

					// Wishlist
					if (isset($this->session->data['wishlist']) && is_array($this->session->data['wishlist'])) {
						$this->load->model('account/wishlist');

						foreach ($this->session->data['wishlist'] as $key => $product_id) {
							$this->model_account_wishlist->addWishlist($product_id);

							unset($this->session->data['wishlist'][$key]);
						}
					}

					// Add to activity log
					$this->load->model('account/activity');

					$activity_data = array(
						'customer_id' => $this->customer->getId(),
						'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName()
					);

					$this->model_account_activity->addActivity('login', $activity_data);

					// Trigger customer post login event
					$this->event->trigger('post.customer.login');

					$json['customer_info'] = $this->model_account_customer->getCustomerByEmail($user_profile->email);
					unset($json['customer_info']['password']);
					unset($json['customer_info']['salt']);
					
					$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 302 Found');
				}else{
					if ($this->customer->isLogged()) {
						return;
					}

					$this->load->language('account/register');

					$register["email"] = $user_profile->email;
					$register["firstname"] = $user_profile->firstName;
					$register["lastname"] = $user_profile->lastName;
					$register["telephone"] = $user_profile->lastName;
					$register["address_1"] = $user_profile->address;
					$register["address_2"] = "";
					$register["postcode"] = $user_profile->zip;
					$register["city"] = $user_profile->city;
					$register["agree"] = true;


					$register["password"] = $register["confirm"] = md5(mt_rand(995481));

					$customer_id = $this->model_account_customer->addCustomer($register);

					// Clear any previous login attempts for unregistered accounts.
					$this->model_account_customer->deleteLoginAttempts($user_profile->email);

					$this->customer->login($user_profile->email, '', true);

					unset($this->session->data['guest']);

					// Add to activity log
					$this->load->model('account/activity');

					$activity_data = array(
						'customer_id' => $customer_id,
						'name'        => $this->request->post['firstname'] . ' ' . $this->request->post['lastname']
					);

					$this->model_account_activity->addActivity('register', $activity_data);

					$json['customer_info'] = $this->model_account_customer->getCustomerByEmail($user_profile->email);
					$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 302 Found');
				}
			}
		}
		catch( Exception $e ){
		   $json['error'] = $e->getMessage();
		}
		
		
		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		
		if(isset($json['error']))
			$this->response->redirect($this->url->link('api2/auth/handler/error', 'error='. $json['error'], 'SSL'));
		else if(isset($json['customer_info']))
			$this->response->redirect($this->url->link('api2/auth/handler/success', 'id='. $json['customer_info']['customer_id'], 'SSL') . '&s=' . $hybridauth_session_data);
	}
	
	public function getUser() {
		$this->load->model('account/customer');
		$this->load->language('account/login');
		$this->load->language('api/customer');
		
		$json = array();
		
		$id = $this->request->post['id'];
		if(!isset($id)){
			$json['error'] = "id parameter missing";
		}else{
			$json['customer_info'] = $this->model_account_customer->getCustomer($id);
		}
		
		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function success() {
		echo "<center><div style=\"font-family: 'Open Sans', sans-serif; font-size: 18px; color: #666666;\">Done</div></center>";
	}
	
	public function error() {
		if($_REQUEST['error'])
			echo "<center><div style=\"font-family: 'Open Sans', sans-serif; font-size: 18px; color: #666666;\">" . $_REQUEST['error'] . "</div></center>";
		else
			echo "<center><div style=\"font-family: 'Open Sans', sans-serif; font-size: 18px; color: #666666;\">Error</div></center>";
	}
	
	function validateUser($user){
		$this->event->trigger('pre.customer.login');

		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($user->email);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($user->email);

		if ($customer_info && !$customer_info['approved']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($user->email, '', true)) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($user->email);
			} else {

				$this->model_account_customer->deleteLoginAttempts($user->email);
			}
		}

		return !$this->error;
	}
}