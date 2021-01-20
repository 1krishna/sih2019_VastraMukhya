<?php
use Cardinity\Method\Error;

//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Featured extends Controller {
	public function index() {
		try{
		$this->load->language('module/latest');
		$json = array();
		$json['products'] = array();
		
		$this->load->model('extension/module');
		
		$this->load->model('setting/setting');
		$module_id = $this->config->get('i2csmobile_featured_module');
		
		if(isset($module_id) && !empty($module_id)){

			$setting = $this->model_extension_module->getModule($module_id);
			
			$this->load->language('module/featured');
			$json['heading_title'] = $this->language->get('heading_title');
			$this->load->model('catalog/product');
			$this->load->model('tool/image');
			
			if (!isset($setting['limit'])) {
				$setting['limit'] = 4;
			}
			
			if(!isset($setting['width'])){
				$setting['width'] = 200;
			}
			
			if(!isset($setting['height'])){
				$setting['height'] = 200;
			}
			
			$width = !isset($this->request->post['width']) ? $setting['width'] : $this->request->post['width'];
			$height = !isset($this->request->post['height']) ? $setting['height'] : $this->request->post['height'];
			
			if (!empty($setting['product'])) {
				$products = array_slice($setting['product'], 0, (int)$setting['limit']);
				foreach ($products as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);
					if ($product_info) {
						if ($product_info['image']) {
							$image = $this->model_tool_image->resize($product_info['image'], $width, $height);
						} else {
							$image = $this->model_tool_image->resize('placeholder.png', $width, $height);
						}
						$images = array();

						$results = $this->model_catalog_product->getProductImages($product_id);

						for ($i = 1 ; $i <= 3 ; $i++){
							if(isset($results[$i]))
								$images[] = $this->model_tool_image->resize($results[$i]['image'], 100, 100);
						}
						if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
							$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$price = false;
						}
						if (isset($product_info['special']) && (float)$product_info['special']) {
							$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$special = false;
						}
						if (isset($product_info['mobile_special']) && (float)$product_info['mobile_special']) {
							$mobile_special = $this->currency->format($this->tax->calculate($product_info['mobile_special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						} else {
							$mobile_special = false;
						}
						if ($this->config->get('config_tax')) {
							$tax = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
						} else {
							$tax = false;
						}
						if ($this->config->get('config_review_status')) {
							$rating = $product_info['rating'];
						} else {
							$rating = false;
						}
						$json['products'][] = array(
							'product_id'  => $product_info['product_id'],
							'thumb'       => $image,
							'name'        => $product_info['name'],
							'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
							'price'       => $price,
							'price_clear' => $product_info['price'],
							'special'     => $special,
							'special_clear'     => $product_info['special'],
							'mobile_special'     => $mobile_special,
							'mobile_special_clear' => $product_info['mobile_special'],
							'tax'         => $tax,
							'rating'      => $rating,
							'images'      => $images,
							'reward'      => $product_info['reward']
						);
					}
				}
			}
		}
	}catch(Exception $e){
echo $e;exit;
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
}