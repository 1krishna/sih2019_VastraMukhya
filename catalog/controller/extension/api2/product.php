<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Product extends Controller {
	public function index() {
		$json = array();
		$this->load->language('product/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');

		if (isset($this->request->request['product_id'])) {
			$product_id = (int)$this->request->request['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			
			$json['heading_title'] = $product_info['name'];

			$this->load->model('catalog/review');

			$json['product_id'] = (int)$this->request->request['product_id'];
			$json['manufacturer'] = $product_info['manufacturer'];
			$json['manufacturers'] = $product_info['manufacturer_id'];
			$json['model'] = $product_info['model'];
			$json['reward'] = (int)$product_info['reward'];
			$json['points'] = $product_info['points'];
			$json['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			if ($product_info['quantity'] <= 0) {
				$json['stock'] = $product_info['stock_status'];
			} elseif ($this->config->get('config_stock_display')) {
				$json['stock'] = $product_info['quantity'];
			} else {
				$json['stock'] = $this->language->get('text_instock');
			}

			$this->load->model('tool/image');

			if ($product_info['image']) {
				$json['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_popup_width') ?: 500, $this->config->get('config_image_popup_height') ?: 500);
			} else {
				$json['popup'] = '';
			}

			if ($product_info['image']) {
				$json['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_thumb_width') ?: 228, $this->config->get('config_image_thumb_height') ?: 228);
			} else {
				$json['thumb'] = '';
			}

			$json['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->request['product_id']);

			foreach ($results as $result) {
				$json['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_popup_width') ?: 500, $this->config->get('config_image_popup_height') ?: 500),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_additional_width') ?: 74, $this->config->get('config_image_additional_height') ?: 74),
					'preview' => $this->model_tool_image->resize($result['image'], $this->config->get('config_image_thumb_width') ?: 228, $this->config->get('config_image_thumb_height') ?: 228)
				);
			}

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$calculated_price = $this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$json['price'] = $this->currency->format($calculated_price, $this->session->data['currency']);
				$json['price_clear'] = (float)$this->currency->format($calculated_price, $this->session->data['currency'], '', false);
			} else {
				$json['price'] = false;
				$json['price_clear'] = false;
			}
			
			$formatted_currency_for_0 = $this->currency->format(0, $this->session->data['currency']);
			$number_without_currency = number_format(0, (int)$this->currency->getDecimalPlace($this->session->data['currency']), '.', ',');
			
			$json['currency_format'] = str_replace((string)$number_without_currency, "{value}", $formatted_currency_for_0);
			$json['decimal_place'] = (int)$this->currency->getDecimalPlace($this->session->data['currency']);

			if ((float)$product_info['special']) {
				$calculated_price = $this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax'));
				$json['special'] = $this->currency->format($calculated_price, $this->session->data['currency']);
				$json['special_clear'] = (float)$this->currency->format($calculated_price, $this->session->data['currency'], '', false);;
			} else {
				$json['special'] = false;
				$json['special_clear'] = false;
			}
			
			if ((float)$product_info['mobile_special']) {
				$json['mobile_special'] = $this->currency->format($this->tax->calculate($product_info['mobile_special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$json['mobile_special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$json['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$json['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->request['product_id']);

			$json['discounts'] = array();

			foreach ($discounts as $discount) {
				$json['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$json['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->request['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price_clear = $this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false);
							$price = $this->currency->format($price_clear, $this->session->data['currency']);
							$price_clear = (float)$this->currency->format($price_clear, $this->session->data['currency'], '', false);
						} else {
							$price = false;
							$price_clear = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_clear'             => (float)$price_clear,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$json['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => (boolean)$option['required']
				);
			}

			if ($product_info['minimum']) {
				$json['minimum'] = $product_info['minimum'];
			} else {
				$json['minimum'] = 1;
			}

			$json['review_status'] = $this->config->get('config_review_status');

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$json['review_guest'] = true;
			} else {
				$json['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$json['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$json['customer_name'] = '';
			}

			$json['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$json['rating'] = (int)$product_info['rating'];
			$json['entry_name'] = $this->language->get('entry_name');
			$json['entry_review'] = $this->language->get('entry_review');

			// Captcha
			if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$json['captcha'] = $this->load->controller('captcha/' . $this->config->get('config_captcha'));
			} else {
				$json['captcha'] = '';
			}

			$json['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->request['product_id']);

			$json['products'] = array();

			$results = $this->model_catalog_product->getProductRelated($this->request->request['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_related_width') ?: 80, $this->config->get('config_image_related_height') ?: 80);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_related_width') ?: 80, $this->config->get('config_image_related_height') ?: 80);
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}
				
				if ((float)$result['mobile_special']) {
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
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
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
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			$json['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$json['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}

			$json['recurrings'] = $this->model_catalog_product->getProfiles($this->request->request['product_id']);

			$this->model_catalog_product->updateViewed($this->request->request['product_id']);

		} else {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
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

	public function search() {
		$json = array();
		$this->load->language('product/search');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		
		$json['text_empty'] = $this->language->get('text_empty');
		
		if (isset($this->request->post['search'])) {
			$search = $this->request->post['search'];
			$description = $this->request->post['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->post['tag'])) {
			$tag = $this->request->post['tag'];
		} elseif (isset($this->request->post['search'])) {
			$tag = $this->request->post['search'];
		} else {
			$tag = '';
		}

		if (isset($this->request->post['category_id'])) {
			$category_id = $this->request->post['category_id'];
		} else {
			$category_id = 0;
		}

		if (isset($this->request->post['sub_category'])) {
			$sub_category = $this->request->post['sub_category'];
		} else {
			$sub_category = '';
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

		$json['products'] = array();

		if (isset($this->request->post['search']) || isset($this->request->post['tag'])) {
			$filter_data = array(
				'filter_name'         => $search,
				'filter_tag'          => $tag,
				'filter_description'  => $description,
				'filter_category_id'  => $category_id,
				'filter_sub_category' => $sub_category,
				'sort'                => $sort,
				'order'               => $order,
				'start'               => ($page - 1) * $limit,
				'limit'               => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('config_image_product_width') ?: 228, $this->config->get('config_image_product_height') ?: 228);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('config_image_product_width') ?: 228, $this->config->get('config_image_product_height') ?: 228);
				}

				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}
				
				if ((float)$result['mobile_special']) {
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
		}

		$json['search'] = $search;
		$json['description'] = $description;
		$json['category_id'] = $category_id;
		$json['sub_category'] = $sub_category;

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
	}
	
	public function getReviews() {
		$json = array();
		$json['reviews'] = array();
		
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		$json['text_no_reviews'] = $this->language->get('text_no_reviews');

		if (isset($this->request->post['page'])) {
			$page = $this->request->post['page'];
		} else {
			$page = 1;
		}

		$json['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->post['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->post['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$json['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;

		$json['pagination'] = $pagination->render();

		$json['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

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

	public function addReview(){
		$this->load->language('product/product');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}

			if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
				$json['error'] = $this->language->get('error_text');
			}

			if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
				$json['error'] = $this->language->get('error_rating');
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review');

				$this->model_catalog_review->addReview($this->request->post['product_id'], $this->request->post);

				$json['success'] = $this->language->get('text_success');
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