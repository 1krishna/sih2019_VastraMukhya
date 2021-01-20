<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Category extends Controller {
	public function index() {
		$json = array();
		
		$this->load->language('product/category');
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		if (isset($this->request->post['search'])) {
			$search = $this->request->post['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->post['sort'])) {
			$sort = $this->request->post['sort'];
		} else {
			$sort = 'p.sort_order';
		}

		if (isset($this->request->post['order'])) {
			$order = $this->request->post['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->post['page'])) {
			$page = $this->request->post['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->post['limit'])) {
			$limit = (int)$this->request->post['limit'];
		} else {
			$limit = $this->config->get('config_product_limit');
		}

		if (isset($this->request->post['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->post['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {

			$json['heading_title'] = $category_info['name'];
			$json['text_empty'] = $this->language->get('text_empty');

			if ($category_info['image']) {
				$json['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('config_image_category_width') ?: 80, $this->config->get('config_image_category_height') ?: 80);
			} else {
				$json['thumb'] = '';
			}

			$json['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');

			$json['categories'] = array();

			$results = $this->model_catalog_category->getCategories($category_id);

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$json['categories'][] = array(
					'name' => $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
					'id' => $result['category_id']
				);
			}

			$json['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_name'      => $search,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width') ?: 228, $this->config->get('config_image_product_height') ?: 228);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width') ?: 228, $this->config->get('config_image_product_height')) ?: 228;
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}
				if ((float)$result['mobile_special']) {
					$mobile_special = $this->currency->format($this->tax->calculate($result['mobile_special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$mobile_special = false;
				}
				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
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
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '..',
					'price'       => $price,
					'price_clear' => $result['price'],
					'special'     => $special,
					'special_clear'     => $result['special'],
					'mobile_special'     => $mobile_special,
					'mobile_special_clear' => $result['mobile_special'],
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'images'      => $images,
					'reward'      => $result['reward']
				);
			}

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;

			$json['sort'] = $sort;
			$json['order'] = $order;
			$json['limit'] = $limit;

			if (isset($this->request->server['HTTP_ORIGIN'])) {
				$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
				$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
				$this->response->addHeader('Access-Control-Max-Age: 1000');
				$this->response->addHeader('Access-Control-Allow-Credentials: true');
				$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			
			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['button_continue'] = $this->language->get('button_continue');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

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
	
	public function all() {
		$json = array();
		$this->load->model('tool/image');
		$this->load->model('setting/setting');
		$this->load->model('catalog/category');
		if (isset($this->request->post['path'])) {
			$parts = explode('_', (string)$this->request->post['path']);
		} else {
			$parts = array();
			
			$settings = array();
			$settings['i2csmobile_product_category'] = array();
			$settings = $this->model_setting_setting->getSetting('i2csmobile');
			$settings_cats = isset($settings['i2csmobile_product_category']) ? $settings['i2csmobile_product_category'] : "";
			if (isset($settings_cats) && $settings_cats != null&& !empty($settings_cats)&& $settings_cats != "null") {
				$categories_j = json_decode($settings_cats);
				if(isset($categories_j) && is_array($categories_j)){
					$categories = array();
					foreach ($categories_j as $category_id) {
						$categories[] = $this->model_catalog_category->getCategory($category_id);
					}
				}
			}
		}

		if (isset($parts[0])) {
			$json['category_id'] = $parts[0];
		} else {
			$json['category_id'] = 0;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$json['categories'] = array();

		if(!isset($categories))
			$json['categories'] = $this->getCategoryChildrenTree($json['category_id']);
		else{	
			foreach ($categories as $category) {
				$item = array();
				$item['category_id'] = $category['category_id'];
				
				if ($category['image']) {
					$thumb = $this->model_tool_image->resize($category['image'], 100, 100);
				} else {
					$thumb = '';
				}
			
				$item['image'] = $thumb;
				$item['name'] = $category['name'];
				$item['categories'] = $this->getCategoryChildrenTree((string)$category['category_id']);
				
				$json['categories'][] = $item;
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
	
	private function getCategoryChildrenTree($id){
		$categories = $this->model_catalog_category->getCategories($id);
		$response = array();

		foreach ($categories as $category) {
			
			$filter_data = array(
				'filter_category_id'  => $category['category_id'],
				'filter_sub_category' => true
			);

			if ($category['image']) {
				$thumb = $this->model_tool_image->resize($category['image'], 100, 100);
			} else {
				$thumb = '';
			}

			$response[] = array(
				'category_id' => $category['category_id'],
				'name'        => $category['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
				'image'    => $thumb,
				'categories' => $this->getCategoryChildrenTree($category['category_id'])
			);
		}
		
		return $response;
	}
}