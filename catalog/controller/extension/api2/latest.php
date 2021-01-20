<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Latest extends Controller {
	public function index() {
		$this->load->language('module/latest');
		$json = array();
		
		$json['heading_title'] = $this->language->get('heading_title');

		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		$json['products'] = array();
	
		$start = !isset($this->request->post['start']) ? 0 : $this->request->post['start'];
		$limit = !isset($this->request->post['limit']) ? 10 : $this->request->post['limit'];
		
		$filter_data = array(
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => $start,
			'limit' => $limit
		);
		
		$results = $this->model_catalog_product->getProducts($filter_data);
		
		$width = !isset($this->request->post['width']) ? 200 : $this->request->post['width'];
		$height = !isset($this->request->post['height']) ? 200 : $this->request->post['height'];
		
		
		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $width , $height);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $width , $height);
				}
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}
				if (isset($result['mobile_special']) && (float)$result['mobile_special']) {
					$mobile_special = $this->currency->format($this->tax->calculate($result['mobile_special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$mobile_special = false;
				}
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}
				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}
				$images = array();

				$results = $this->model_catalog_product->getProductImages($result['product_id']);

				for ($i = 1 ; $i <= 3 ; $i++){
					if(isset($results[$i]))
						$images[] = $this->model_tool_image->resize($results[$i]['image'], 100, 100);
				}
				$json['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'price_clear' => $result['price'],
					'special'     => $special,
					'special_clear'     => $result['special'],
					'mobile_special'     => $mobile_special,
					'mobile_special_clear' => $result['mobile_special'],
					'tax'         => $tax,
					'rating'      => $rating,
					'images'      => $images,
					'reward'      => $result['reward']
				);
			}
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