<?php
class Controllervendorvendorprofile extends Controller {
	private $error = array();
	public function index() {
		
		$this->load->language('vendor/vendor_profile');
			
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/dashboard', '', 'SSL')
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
						
		$data['heading_title'] 		= $this->language->get('heading_title');
		$data['text_select'] 		= $this->language->get('text_select');
		$data['text_none'] 			= $this->language->get('text_none');
		$data['text_loading'] 		= $this->language->get('text_loading');
		$data['text_connect'] 		= $this->language->get('text_connect');
		$data['text_profile'] 		= $this->language->get('text_profile');
		$data['text_about'] 		= $this->language->get('text_about');
		$data['text_products'] 		= $this->language->get('text_products');
		$data['text_reviews'] 		= $this->language->get('text_reviews');
		$data['text_pro_reviews'] 	= $this->language->get('text_pro_reviews');
		$data['text_write'] 		= $this->language->get('text_write');
		$data['text_name'] 			= $this->language->get('text_name');
		$data['text_comment'] 		= $this->language->get('text_comment');
		$data['text_findme'] 		= $this->language->get('text_findme');
        
        /* update code 10-1-2019 */
		if (!$this->customer->isLogged()) {	
			$data['text_loginplz'] 		= $this->language->get('text_loginplz');
				$this->session->data['redirect'] = $this->url->link('vendor/vendor_profile','&vendor_id=' .$this->request->get['vendor_id']);
			} else {
				$data['text_loginplz'] 		='';
		}
		$data['text_proreview'] 		= $this->language->get('text_proreview');
		/* update code 10-1-2019 */

		/* update code 29-1-2019 */
		$data['text_aboutstore'] 		= $this->language->get('text_aboutstore');
		$data['text_storedescrption'] 	= $this->language->get('text_storedescrption');
		$data['text_storeshipingpolicy']= $this->language->get('text_storeshipingpolicy');
		$data['text_storereturnpolicy'] = $this->language->get('text_storereturnpolicy');
		/* update code 29-1-2019 */
		
		$data['button_followers'] 	= $this->language->get('button_followers');
		$data['button_follow'] 		= $this->language->get('button_follow');
		$data['button_following'] 	= $this->language->get('button_following');
		$data['button_close'] 		= $this->language->get('button_close');
		$data['button_send'] 		= $this->language->get('button_send');
		$data['button_cart'] 		= $this->language->get('button_cart');
		$data['button_wishlist'] 	= $this->language->get('button_wishlist');
		$data['button_compare'] 	= $this->language->get('button_compare');
		$data['text_rating'] 	  	= $this->language->get('text_rating');
		
		$this->load->model('vendor/vendor');
		$this->load->model('tool/image');

		

		if($this->customer->getId()) {	
			$customer_info = $this->model_vendor_vendor->getCustomerlog($this->customer->getId());
		}

		if(!empty($customer_info['firstname'])){
			$data['customername'] = $customer_info['firstname']. ' ' .$customer_info['lastname'];
		} else {
			$data['customername'] = '';
		}

		if($this->customer->getId()){
			$data['customer_id'] = $this->customer->getId();
		} else {
			$data['customer_id'] = '';
		}

		/* 01-02-2019 */
		if (isset($this->request->get['vendor_id'])) {
			$vendor_id = (int)$this->request->get['vendor_id'];
		} else {
			$vendor_id = 0;
		}
		/* 01-02-2019 */
		if(isset($vendor_id)) {	
			 $vendor_info = $this->model_vendor_vendor->getVendor($vendor_id);
		}
		
		/* 01-02-2019 */
		if ($vendor_info) {
		/* 01-02-2019 */
		
		if(isset($vendor_info['display_name'])){
			$this->document->setTitle($vendor_info['display_name']);
		} 
		/* new code */

		if(!empty($vendor_info['facebook_url'])){
			$facebookurl = $vendor_info['facebook_url'];
		} else {
			$facebookurl = '';
		}

		if(!empty($vendor_info['google_url'])){
			$googleurl = $vendor_info['google_url'];
		} else {
			$googleurl = '';
		}
		$data['facebookurl'] = $facebookurl;
		$data['googleurl'] = $googleurl;
		$data['vendorfindme'] =  $this->url->link('vendor/findme','&vendor_id=' .$vendor_info['vendor_id']);

		//$this->url->link('social/facebook',facebook_url=' . $vendor_info['facebook_url'] . true');
				
		if(!empty($vendor_info['store_logowidth'])){
			$store_logowidth = $vendor_info['store_logowidth'];
		} else {
			$store_logowidth = 75;
		}
		
		if(!empty($vendor_info['store_logoheight'])){
			$store_logoheight = $vendor_info['store_logoheight'];
		} else {
			$store_logoheight = 75;
		}
		
		if(!empty($vendor_info['store_bannerwidth'])){
			$store_bannerwidth = $vendor_info['store_bannerwidth'];
		} else {
			$store_bannerwidth = 1140;
		}
		
		if(!empty($vendor_info['store_bannerheight'])){
			$store_bannerheight = $vendor_info['store_bannerheight'];
		} else {
			$store_bannerheight = 346;
		}
		
		if(!empty($vendor_info['image'])){
			$images = $this->model_tool_image->resize($vendor_info['image'],150,150);
		} else {
			$images = $this->model_tool_image->resize('placeholder.png',150,150);
		}

		if(!empty($vendor_info['banner'])){
			$banners = $this->model_tool_image->resize($vendor_info['banner'],$store_bannerwidth,$store_bannerheight);
		} else {
			$banners = $this->model_tool_image->resize('placeholder.png',$store_bannerwidth,$store_bannerheight);
		}
		
		if(!empty($vendor_info['logo'])){
			$logos = $this->model_tool_image->resize($vendor_info['logo'],$store_logowidth, $store_logoheight);
		} else {
			$logos = $this->model_tool_image->resize('placeholder.png',$store_logowidth,$store_logoheight);
		}
		
		if(!empty($vendor_info['store_about'])){
			$store_about = $vendor_info['store_about'];
		} else {
			$store_about = '';
		}
        
        /* update code 29-1-2019 */
		
		if(!empty($vendor_info['description'])){
			$data['storedescription'] = utf8_substr(trim(strip_tags(html_entity_decode($vendor_info['description'], ENT_QUOTES, 'UTF-8'))), 0, 500);
		} else {
			$data['storedescription'] = '';
		}
		
		if(!empty($vendor_info['shipping_policy'])){
			$data['shipping_policy'] =  utf8_substr(trim(strip_tags(html_entity_decode($vendor_info['shipping_policy'], ENT_QUOTES, 'UTF-8'))), 0, 500);
		} else {
			$data['shipping_policy']  = '';
		}
		if(!empty($vendor_info['return_policy'])){
			$data['return_policy']  =  utf8_substr(trim(strip_tags(html_entity_decode($vendor_info['return_policy'], ENT_QUOTES, 'UTF-8'))), 0, 500);
		} else {
			$data['return_policy']  = '';
		}
		
		/* update code 29-1-2019 */
        
		if(!empty($vendor_info['display_name'])){
			$display_name = $vendor_info['display_name'];
		} else {
			$display_name = '';
		}
		if(!empty($vendor_info['vendor_id'])){
			$vendor_id = $vendor_info['vendor_id'];
		} else {
			$vendor_id = '';
		}
	//print_r($vendor_id);
		
		if(!empty($vendor_info['telephone'])){
			$vendortelephone = $vendor_info['telephone'];
		} else {
			$vendortelephone = '';
		}
		if(!empty($vendor_info['name'])){
			$vendorname = $vendor_info['name'];
		} else {
			$vendorname = '';
		}
		if(!empty($vendor_info['map_url'])){
			$map_url = $vendor_info['map_url'];
		} else {
			$map_url = '';
		}

		if(!empty($vendor_info['about'])){
			$aboutvendor = $vendor_info['about'];
		} else {
			$aboutvendor = '';
		}
		
		if(!empty($vendor_info['text'])) {
			$ratingtext = $vendor_info['text'];
		} else {
			$ratingtext='';
		}		
				
		if(!empty($vendor_info['email'])){
			$vendoremail = (substr($vendor_info['email'], 0, 25));
		} else {
			$vendoremail = '';
		}

		$data['banners'] 		= $banners;
		$data['logos'] 		    = $logos;
		$data['store_about'] 	= $store_about;
		$data['name'] 			= $vendorname;
		$data['map_url'] 		= $map_url;

		$data['images'] 		= $images;
		$data['display_name'] 	= $display_name;
		$data['vendor_id'] 	    = $vendor_id; 
		$data['catevendor_id'] 	= $vendor_id; 
		$data['email'] 			= $vendoremail;
		$data['telephone'] 		= $vendortelephone;
		$data['about'] 			= $aboutvendor;
		$data['ratingtext'] 	= $ratingtext;
		
		
// Get Vendor Id More than One Time Insert Start // 		
		$data['customerloggin'] = $this->customer->isLogged();
		$write_infos = $this->model_vendor_vendor->getWriteReview($this->request->get['vendor_id']);
		
		if(isset($vendor_info['vendor_id'])){
			$vids = $vendor_info['vendor_id'];
		} else {
			$vids='';
		}

		if(isset($write_infos['customer_id'])){
			$ids = $write_infos['customer_id'];
		} else {
			$ids='';
		}

		if(isset($write_infos['vendor_id'])){
			$vendorids = $write_infos['vendor_id'];
		} else {
			$vendorids='';
		}
		$data['ids'] = $ids;
		$data['vids'] = $vids;
		$data['vendorids'] = $vendorids;

		$data['totals'] = $this->model_vendor_vendor->getTotalCollections($vendor_id);
		$data['sellertotal'] = $this->model_vendor_vendor->getTotalSellerReview($vendor_id);
		$data['producttotal'] = $this->model_vendor_vendor->getTotalProductReview($vendor_id);
		
/// Follow And Following ////
		$data['loggin'] = $this->vendor->isLogged();
		$data['custloggin'] = $this->customer->isLogged();

		if(isset($this->request->get['vendor_id'])) {
			$data['requets']=$this->model_vendor_vendor->getFollow($this->request->get['vendor_id']);
		} else if(isset($this->request->get['vendor_id'])) {
			$data['requets']=$this->model_vendor_vendor->getDelete($this->request->get['vendor_id']);
		}else {
			$data['requets']='';
		}

		$data['followerstotal'] = $this->model_vendor_vendor->getTotalFollowers($vendor_id);

/// Follow And Following ////


// Get Vendor Id More than One Time Insert End //		

// Get Review Start //
		$data['reviews']=array();
		$rating_avg = $this->model_vendor_vendor->getVendorSumValue($vendor_id);
		

		$data['reviews'][]=array(
			'rating_avg'   => $rating_avg
		);

// Get Review End //
		// Seller Review Start //
		$data['field_infos']=array();
		
		
		$field_infos = $this->model_vendor_vendor->getFieldReviews($vendor_id);
	//	print_r($field_info); die();
			foreach($field_infos as $field_info){
				
				$ven_info = $this->model_vendor_vendor->getVendor($field_info['vendor_id']);
				if(isset($ven_info['display_name'])) {
					$fnames = $ven_info['display_name'];
				} else {
					$fnames='';
				}

				$cus_info = $this->model_vendor_vendor->getCustomer($field_info['customer_id']);
				if(isset($cus_info['firstname'])) {
					$cnames = $cus_info['firstname']. ' ' .$cus_info['lastname'];
				} else {
					$cnames='';
				}


				if(isset($ven_info['about'])) {
					$abouts = $ven_info['about'];
				} else {
					$abouts='';
				}
				
				$ratings=array();
				$rating_infos = $this->model_vendor_vendor->getField($field_info['review_id'],$field_info['vendor_id']);
				
				foreach($rating_infos as $rating_info){
					$ratings[]=array(
						'field_name'=> $rating_info['field_name'],
						'value' 	=> $rating_info['value']
						
					);
				}
				
				$data['field_infos'][]=array(
					'review_id' => $field_info['review_id'],
					'reviewtext' => $field_info['text'],
					//'fnames' 	=> $fnames,
					'cnames' 	=> $cnames,
					'abouts' 	=> $abouts,
					'ratings' 	=> $ratings,
					'date_added' => $field_info['date_added']
				);
		
			}
// Seller Review End //



// Product With Seller Review Start //

		$data['sellerreviews']=array();
		if(!empty($vendor_info)){
			$vendor_info= $vendor_info;
		} else{
			$vendor_info='';
		}
		$proreviews = $this->model_vendor_vendor->getProReview($vendor_id);
		foreach($proreviews as $proreview){
			$vendorinfo = $this->model_vendor_vendor->getVendor($proreview['vendor_id']);
			
									
			if(isset($vendorinfo['date_added'])) {
				$date_added = $vendorinfo['date_added'];
			} else {
				$date_added='';
			}
			$products = $this->model_vendor_vendor->getProduct($proreview['product_id']);
		
			if(isset($products['name'])) {
				$names = $products['name'];
			} else {
				$names='';
			}
			if(isset($products['product_id'])) {
				$product_ids = $products['product_id'];
			} else {
				$product_ids='';
			}
			$data['sellerreviews'][]=array(
				'rating' 		=> $proreview['rating'],
				'text' 			=> $proreview['text'],
				'author' 		=> $proreview['author'],
				'names' 		=> $names,
				'date_added' 	=> $date_added,
				'href'  => $this->url->link('product/product','&product_id=' .$product_ids)
			);
		}
		
// Product With Seller Review End //
		
		$this->load->model('vendor/vendor');
		$data['review_fields'] = $this->model_vendor_vendor->getReviewFields($data);
		
		if (isset($this->request->post['reviewfield'])) {
			$data['reviewfield'] = $this->request->post['reviewfield'];
		} elseif (isset($review_info['reviewfield'])) {
			$data['reviewfield'] = $this->model_vendor_vendor->getFieldSubmits($this->request->get['review_id']);
		} else {
			$data['reviewfield'] = array();
		}
		
/// Collection Product Start ///

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = '';
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

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get($this->config->get('config_theme') . '_product_limit');
		}
		
		if (isset($this->request->get['path'])) {
			
		$url = '';
		
		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
		
			
		$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		
		
		$data['products']= array();
		$filter2=array(
			/* new code */
			'filter_category_id' => $category_id,
			/* new code */
			'vendor_id'     => $vendor_id,
			'filter_filter' => $filter,
			'sort'          => $sort,
			'order'         => $order,
			'start'         => ($page - 1) * $limit,
			'limit'         => $limit
		);

		$this->load->model('vendor/store');
		
		$product_total = $this->model_vendor_store->getTotalProducts($filter2);
		$products = $this->model_vendor_store->getProducts($filter2);
		
		foreach($products as $product) {
			if (is_file(DIR_IMAGE . $product['image'])){
				$pimage = $this->model_tool_image->resize($product['image'],   $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			}else{
				$pimage = $this->model_tool_image->resize('no_image.png',  $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
			}
			
			$pros_info = $this->model_vendor_vendor->getProRev($product['product_id']);
            
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}
			
			if(isset($pros_info['rating'])) {
				$ratings = $pros_info['rating'];
			} else {
				$ratings='';
			}
			
			$data['products'][] = array(
				'product_id' 	=> $product['product_id'],
				'name'   		=> $product['name'],
				'description'	=>html_entity_decode(substr($product['description'], 0, 120)),
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'pimage' 		=> $pimage,
				'ratings' 		=> $ratings,
				'minimum'       => $product['minimum'] > 0 ? $product['minimum'] : 1,
				'href'   		=> $this->url->link('product/product','&product_id=' .$product['product_id'])
			);
		}
		$url ='';		
		$data['sorts'] = array();

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_default'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=p.sort_order&order=ASC' . $url,
			
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_asc'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=pd.name&order=ASC' . $url,
			
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_name_desc'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=pd.name&order=DESC' . $url,
			
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_price_asc'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=p.price&order=ASC' . $url,
			
			
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_price_desc'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=p.price&order=DESC' . $url,
			
		);

		if ($this->config->get('config_review_status')) {
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_desc'),
				'value'  => 'vendor_id=' . $vendor_id .'&sort=rating&order=DESC' . $url,
				
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_rating_asc'),
				'value'  =>'vendor_id=' . $vendor_id .'&sort=rating&order=ASC' . $url,
				
			);
		}

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_model_asc'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=p.model&order=ASC' . $url,
			
		);

		$data['sorts'][] = array(
			'text'  => $this->language->get('text_model_desc'),
			'value'  => 'vendor_id=' . $vendor_id .'&sort=p.model&order=DESC' . $url,
			
		);
		
		$url = '';

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$data['limits'] = array();

		$limits = array_unique(array($this->config->get($this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

		sort($limits);

		foreach($limits as $value) {
			$data['limits'][] = array(
				'text'  => $value,
				
				'value'  => 'vendor_id=' . $vendor_id .'&limit=' . $value
			);
		}

		$url = '';

		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('vendor/vendor_profile','vendor_id=' . $vendor_id .'&page={page}');

		$data['pagination'] = $pagination->render();

		

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;
		
		/// Collection Product End ///

		/// Product Category Start ///
			if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
			} else {
				$data['category_id'] = 0;
			}
			
			if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
			} else {
				$data['child_id'] = 0;
			}
		
		$this->load->model('catalog/category');
		$this->load->model('catalog/product');
		
		$data['categories']=array();
		$categories = $this->model_catalog_category->getCategories(0);		
		
		foreach ($categories as $category) {
			/* sub cate */			
			
			$children_data = array();
			
				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach($children as $child) {
					$filter_productdata = array('filter_category_id' => $child['category_id'],'vendor_id'     => $vendor_id,  'filter_sub_category' => true);
				
					$children_data[] = array(
						'category_id' => $child['category_id'],
						'name' => $child['name'] . (' (' . $this->model_vendor_store->getTotalProducts($filter_productdata) . ')'),
						
					);
				}
			
			/* sub cate */
			
			$category_infos = $this->model_catalog_category->getCategory($category['category_id']);
			
			if(isset($category_infos['name'])){
				$categoryname = $category_infos['name'];
			} else {
				$categoryname='';
			}
			
			$filter_productdata = array(
				'filter_category_id'  => $category['category_id'],
				'vendor_id'     => $vendor_id,
				'filter_sub_category' => true
			);
			
			
			$data['categories'][]=array(
				'category_id' => $category['category_id'],				
				'categoryname' => $categoryname . (' (' . $this->model_vendor_store->getTotalProducts($filter_productdata) . ')'),				
				'children'    => $children_data,

			);
			
		}
	/// Product Category End ///

		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		
		$this->response->setOutput($this->load->view('vendor/vendor_profile', $data));
            
        /* 01-02-2019 */
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('vendor/vendor_profile', $url . '&vendor_id=' . $vendor_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			
			$data['continue'] = $this->url->link('vendor/allseller');
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
		/* 01-02-2019 */

	}
	
	function review(){
		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/vendor_profile');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			if(empty($this->request->post['text'])){
				$json['error']='Please add Comment here';
			} else {	
			
			 $this->model_vendor_vendor->addReview($this->request->post,$this->request->get['vendor_id']);			 
			
			$json['success'] = $this->language->get('text_success');
			}
		}
		$this->index();
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function loadcategory() {
        
        $this->load->language('vendor/vendor_profile');
		
		$this->load->model('vendor/vendor');
		$this->load->model('tool/image');
		
		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = '';
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
		
		
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get($this->config->get('config_theme') . '_product_limit');
		}
		
		if(isset($this->request->get['vendor_id'])) {	
			 $vendor_info = $this->model_vendor_vendor->getVendor($this->request->get['vendor_id']);
		
		}
		
		if(!empty($vendor_info['vendor_id'])){
			$vendor_id = $vendor_info['vendor_id'];
		} else {
			$vendor_id = '';
		}
		
		 $data['vendor_id'] 	    = $vendor_id;
		
		
		
		if (isset($this->request->get['path'])) {
			
		$url = '';
		
		if (isset($this->request->get['filter'])) {
			$url .= '&filter=' . $this->request->get['filter'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['limit'])) {
			$url .= '&limit=' . $this->request->get['limit'];
		}			
			
		$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}
		
		
		$data['products']= array();
		
		$filter2=array(
			
			'filter_category_id' => $category_id,
			'vendor_id'     => $vendor_id,
			'filter_filter' => $filter,
			'sort'          => $sort,
			'order'         => $order,
			'start'         => ($page - 1) * $limit,
			'limit'         => $limit
		);

		$this->load->model('vendor/store');
		
		$product_total = $this->model_vendor_store->getTotalProducts($filter2);
		$products = $this->model_vendor_store->getProducts($filter2);
	
		foreach($products as $product) {
			if (is_file(DIR_IMAGE . $product['image'])){
				$pimage = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height')); 
			}else{
				$pimage = $this->model_tool_image->resize('no_image.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height')); 
			}
			
			$pros_info = $this->model_vendor_vendor->getProRev($product['product_id']);
            
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$product['special']) {
					$special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$product['special'] ? $product['special'] : $product['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}
			
			if(isset($pros_info['rating'])) {
				$ratings = $pros_info['rating'];
			} else {
				$ratings='';
			}
			
			$data['products'][] = array(
				'product_id' 	=> $product['product_id'],
				'name'   		=> $product['name'],
				'description'	=>html_entity_decode(substr($product['description'], 0, 120)),
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'pimage' 		=> $pimage,
				'ratings' 		=> $ratings,
				'minimum'       => $product['minimum'] > 0 ? $product['minimum'] : 1,
				'href'   		=> $this->url->link('product/product','&product_id=' .$product['product_id'])
			);
		}
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('vendor/vendor_profile','vendor_id=' . $vendor_id .'&page={page}');

		$data['pagination'] = $pagination->render();

		$data['button_cart'] = $this->language->get('button_cart');
		$data['button_wishlist'] = $this->language->get('button_wishlist');
		$data['button_compare'] = $this->language->get('button_compare');

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;
		
		
		$this->response->setOutput($this->load->view('vendor/loadcategory', $data));
		
	}

	function follow(){
		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/vendor_profile');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			$this->model_vendor_vendor->addFollow($this->request->post['vendor_id'],$this->request->post);			 
			$json['success'] = $this->language->get('text_success');
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	function delfollow(){
		$json = array();
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/vendor_profile');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {

			$this->model_vendor_vendor->getDelete($this->request->get['vendor_id']);			 
			$json['success'] = $this->language->get('text_success');
			
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}


}