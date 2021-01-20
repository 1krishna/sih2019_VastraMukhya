<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2UserLogin extends Controller {
	private $error = array();

	public function index() {
		$json = array();
		
		$this->load->model('account/customer');

		$this->load->language('account/login');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
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
			
			$json['customer_info'] = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);
			unset($json['customer_info']['password']);
			unset($json['customer_info']['salt']);
		}

		if (isset($this->error['warning'])) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 406 Not Acceptable');
			$json['error_warning'] = $this->error['warning'];
		} else {
			$json['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$json['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$json['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$json['email'] = $this->request->post['email'];
		} else {
			$json['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$json['password'] = $this->request->post['password'];
		} else {
			$json['password'] = '';
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Credentials: true');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		$this->event->trigger('pre.customer.login');

		// Check how many login attempts have been made.
		$login_info = $this->model_account_customer->getLoginAttempts($this->request->post['email']);

		if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
			$this->error['warning'] = $this->language->get('error_attempts');
		}

		// Check if customer has been approved.
		$customer_info = $this->model_account_customer->getCustomerByEmail($this->request->post['email']);

		if ($customer_info && !$customer_info['approved']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}

		if (!$this->error) {
			if (!$this->customer->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->language->get('error_login');

				$this->model_account_customer->addLoginAttempt($this->request->post['email']);
			} else {
				$this->model_account_customer->deleteLoginAttempts($this->request->post['email']);
			}
		}

		return !$this->error;
	}
}
