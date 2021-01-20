<?php
require_once('stripe/init.php');
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2PaymentHandlersAdvertikonStripeCheckout extends Controller {

	public function index() {
		$json = array();
		$this->load->model('checkout/order');
		
		$adk_stripe_test_mode = $this->config->get('adk_stripe_test_mode');
		$adk_stripe_test_secret_key = $this->config->get('adk_stripe_test_secret_key');
		$adk_stripe_live_secret_key = $this->config->get('adk_stripe_live_secret_key');
		$adk_stripe_test_public_key = $this->config->get('adk_stripe_test_public_key');
		$adk_stripe_live_public_key = $this->config->get('adk_stripe_live_public_key');
		
		if($adk_stripe_test_mode)
			$secret = $adk_stripe_test_secret_key;
		else
			$secret = $adk_stripe_live_secret_key;
		
		if (isset($this->request->post['order_id'])) {
			$order_id = $this->request->post['order_id'];
			$order_info = $this->model_checkout_order->getOrder($order_id);
		} else {
			$json['error'] = "Order id is not set";
		}
		
		if(isset($secret) && !$json['error']){
			\Stripe\Stripe::setApiKey($secret);
			
			if(!isset($this->request->post['stripe_token'])){
				$json['error'] = "token is not set";
			}else{
				$token = $this->request->post['stripe_token'];
			}
			
			$amount = str_replace(".", "", $order_info['total']);
			
			if(!isset($this->request->post['currency'])){
				$json['error'] = "currency is not set";
			}else{
				$currency = $this->request->post['currency'];
			}
			
			if(!isset($this->request->post['last4'])){
				$json['error'] = "last4 is not set";
			}else{
				$last4 = $this->request->post['last4'];
			}

			if(!isset($json['error'])){
				try {
					// Charge the Customer instead of the card
					$charge = \Stripe\Charge::create(array(
					  "amount" => $amount,
					  "currency" => $currency,
					  "source" => $token)
					);
					
					$this->load->language('api/order');
					$this->load->model('checkout/order');
					
					if ($order_info) {
						$this->model_checkout_order->addOrderHistory($order_id, 2, "Online payment completed with card/alipay. $last4. $token", false, false);
					} else {
						$json['error'] = $this->language->get('error_not_found');
					}
				} catch(Exception $e) {
					$json['error'] = "Your card is not charged." . $e->getMessage();
				}
			}
		}else if(!isset($secret)){
			$json['error'] = "API key is not set in payment module";
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function publicKey(){
		$json = array();
		
		$adk_stripe_test_mode = $this->config->get('adk_stripe_test_mode');
		$adk_stripe_test_public_key = $this->config->get('adk_stripe_test_public_key');
		$adk_stripe_live_public_key = $this->config->get('adk_stripe_live_public_key');
		
		if($adk_stripe_test_mode)
			$publickey = $adk_stripe_test_public_key;
		else
			$publickey = $adk_stripe_live_public_key;
		
		if(isset($publickey)){
			$json['public_key'] = $publickey;
		}else{
			$json['error'] = "public key is not set in payment module";
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
