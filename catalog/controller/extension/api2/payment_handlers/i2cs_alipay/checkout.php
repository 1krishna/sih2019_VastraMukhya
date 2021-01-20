<?php
require_once("Alipay/Alipay.php");
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2PaymentHandlersI2csAlipayCheckout extends Controller {

	private $json = array();
	
	public function index() {
		$this->load->model('checkout/order');
		
		$i2cs_alipay_endpoint = $this->config->get('i2cs_alipay_endpoint');
		$i2cs_alipay_partner_id = $this->config->get('i2cs_alipay_partner_id');
		$i2cs_alipay_secret = $this->config->get('i2cs_alipay_secret');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
			$order_info = $this->model_checkout_order->getOrder($order_id);
		} else {
			$json['error'] = "Order id is not set";
		}
		
		$sale_id = $order_info['order_id'];
		$amount = $order_info['total'];
		$currency = $order_info['currency'];
		$description = "Order #" . $order_info['order_id'] . " for ". $order_info['firstname'] . " " . $order_info['lastname'];
		$uuid = $order_info['order_id'];
		// Associate the sale id with uuid in your database for a look up once Alipay
		// pings your notify_url
		$return_url = HTTPS_SERVER . "?route=api2/payment_handlers/i2cs_alipay/callback&action=return&sale_id=$sale_id";
		$notify_url = HTTPS_SERVER . "?route=api2/payment_handlers/i2cs_alipay/callback&action=notify&id=$uuid";

		
		$config = new Alipay\Config();
		
		/*
        The HTTPS API URL is https://mapi.alipay.com/gateway.do
		*/
		$config->setEndpoint($i2cs_alipay_endpoint); //https://mapi.alipay.com/gateway.do?
		
		/*
        This unique number is essentially your account number that is referred
        to as the Partner ID. It begins with '2088' and is 16 numbers.
		*/
		$config->setPartner_id($i2cs_alipay_partner_id); //2088101122136241;
		
		/*
        This is your API secret that only you and Alipay need to know.
        It's used for creating the MD5 hash. Don't make it public.
		*/
		$config->setSecret($i2cs_alipay_secret); //760bdzec6y9goq7ctyx96ezkz78287de
		
		/*
        Currency to trade in. Default is RMB.
        Valid values are: GBP, HKD, USD, CHF, SGD, SEK, DKK, NOK, JPY, CAD, AUD
        EUR, NZD, RUB, MOP
		*/
		$config->setCurrency($currency);
		
		/*
        You need to reference this certificate when using cURL.
		*/
		$config->setSsl_cert("alipay_cert.pem");
		
		$alipay = new Alipay\Alipay($config);
		// Generates a one-time URL to redirect the Buyer to
		$approve = $alipay->createPayment($sale_id, $amount, $description, $return_url, $notify_url);
		
		$this->response->redirect($approve);
	}
	
	public function callback(){
		$config = new Alipay\Config();
		
		$i2cs_alipay_endpoint = $this->config->get('i2cs_alipay_endpoint');
		$i2cs_alipay_partner_id = $this->config->get('i2cs_alipay_partner_id');
		$i2cs_alipay_secret = $this->config->get('i2cs_alipay_secret');
		
		/*
        The HTTPS API URL is https://mapi.alipay.com/gateway.do
		*/
		$config->setEndpoint($i2cs_alipay_endpoint);
		
		/*
        This unique number is essentially your account number that is referred
        to as the Partner ID. It begins with '2088' and is 16 numbers.
		*/
		$config->setPartner_id($i2cs_alipay_partner_id);
		
		/*
        This is your API secret that only you and Alipay need to know.
        It's used for creating the MD5 hash. Don't make it public.
		*/
		$config->setSecret($i2cs_alipay_secret);
		
		
		/*
        You need to reference this certificate when using cURL.
		*/
		$config->setSsl_cert("alipay_cert.pem");
		
		$alipay = new Alipay\Alipay($config);
		
		$success = $alipay->verifyPayment($_GET);
		if($success){
			$this->load->language('api/order');
			$this->load->model('checkout/order');
			
			if (isset($this->request->get['sale_id'])) {
				$order_id = $this->request->get['sale_id'];
				$order_info = $this->model_checkout_order->getOrder($order_id);
			} else {
				$json['error'] = "Order id is not set";
			}
			
			if ($order_info) {
				$this->model_checkout_order->addOrderHistory($order_id, 2, "Online payment completed with Alipay", false, false);
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		}else{
			$json['error'] = "Payment error";
		}
		
		if(isset($json['error']))
			$this->response->redirect($this->url->link('api2/payment_handlers/i2cs_alipay/checkout/error', 'error='. $json['error'], 'SSL'));
		else if(isset($json['customer_info']))
			$this->response->redirect($this->url->link('api2/payment_handlers/i2cs_alipay/success', '', 'SSL'));
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
}