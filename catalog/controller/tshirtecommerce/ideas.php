<?php
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		September 07, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ControllerTshirtecommerceIdeas extends Controller {

	private $error = array();

	public function index()
	{
		$parent_id = $this->config->get('tshirtecommerce_category');
		$category_id = 0;
		
		$check_seo = 0;
		if ($this->config->get('config_seo_url') == 1 && $this->config->get('tshirtecommerce_seo_enable') == 1)
			$check_seo = 1;
		
		/*get seo id*/
		if ($check_seo)
		{
			if (isset($this->request->get['_route_'])) 
			{
				$keyword_default = $this->config->get('tshirtecommerce_seo_ideas_default_keyword');
				if ($keyword_default == null || empty($keyword_default))
					$keyword_default = 'design-ideas';
				$route = str_replace($keyword_default, '', $this->request->get['_route_']);
				$parts = explode('/', $route);
				if (utf8_strlen(end($parts)) == 0) {
					array_pop($parts);
				}
				
				if(isset($parts[1]) && $parts[1] != '')
				{
					$cates = explode('__', $parts[1]);
					if(isset($cates[0]) && $cates[0] != '')
						$parent_id = (int) $cates[0];
					if(isset($cates[1]) && $cates[1] != '')
						$category_id = (int) $cates[1];
				}
			}
		}else
		{
			if (isset($this->request->get['parent_id'])) {
				$parent_id = $this->request->get['parent_id'];
			}
			if (isset($this->request->get['category_id'])) {
				$category_id = $this->request->get['category_id'];
			}
		}
		
		$TSHIRTECOMMERCE_ROOT = dirname(dirname(dirname(dirname(__FILE__)))). DIRECTORY_SEPARATOR . 'tshirtecommerce'. DIRECTORY_SEPARATOR;
		if (defined('ROOT') == false)
			define('ROOT', $TSHIRTECOMMERCE_ROOT);
		
		if (defined('DS') == false)
			define('DS', DIRECTORY_SEPARATOR);
		
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		
		$data = array();
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$site_url 	=  $this->config->get('config_ssl');
		} else {
			$site_url 	=  $this->config->get('config_url');
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 0;
		}
		
		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = 24;
		}
		
		/*get product*/
		$this->load->model('tshirtecommerce/order');
		$results = $this->model_tshirtecommerce_order->getProducts();
		//echo '<pre>'; print_r($results); exit;
		
		$product_ids 	= array();
		$post_ids 		= array();
		$products 		= array();
		$prresults = $this->model_catalog_product->getProducts();
		
		foreach ($results as $product)
		{
			if(isset($prresults[$product['product_id']]))
			{
				$ids = $product['design_product_id'];
				if ($ids != '')
				{
					$temp = explode(':', $ids);
					if (count($temp) == 1)
					{
						$product['design_product_id'] 		= $temp[0];
						$products[$product['product_id']] 	= array_merge($product, $prresults[$product['product_id']]);				
						$product_ids[]						= $product['design_product_id'];
						$post_ids[$product['design_product_id']] = $product['product_id'];
					}
				}
			}
		}
		
		/*get product category*/
		$pr_categories = $this->model_tshirtecommerce_order->getProductCategories();
		
		/*filter list parent categories design*/
		$list_pr_categories = array();
		foreach($products as $value)
		{
			foreach($pr_categories as $pr_category)
			{
				if($pr_category['product_id'] == $value['product_id'])
					$list_pr_categories[$pr_category['category_id']] = $pr_category['product_id'];
			}
		}
		
		/*get categories*/
		$list_categories = $this->model_tshirtecommerce_order->getCategories();
		$result_cate = array();
		
		foreach($list_pr_categories as $key=>$prcate)
		{
			foreach($list_categories as $category)
			{
				if($category['category_id'] == $key)
				{
					if($category['parent_id'] == 0)
					{
						$result_cate[$category['category_id']] = $category;
					}else
					{
						$cate = $this->model_tshirtecommerce_order->getParentCategory($list_categories, $category['parent_id']);
						if(isset($cate['category_id']))
							$result_cate[$cate['category_id']] = $cate;
					}
				}
			}
		}
		
		$categories = array();
		$this->load->model('tshirtecommerce/seo_url');
		foreach ($result_cate as $cate) {
			if ($check_seo)
				$cate_url = $this->model_tshirtecommerce_seo_url->getLink($cate['category_id'], 0, 'tshirtecommerce/ideas');
			else
				$cate_url = $this->url->link('tshirtecommerce/ideas', 'parent_id='.$cate['category_id']);
			
			$categories[] = array(
				'name' => $cate['name'],
				'category_id' => $cate['category_id'],
				'href' => $cate_url
			);
		}
		
		/*get product categories*/
		$product_categories = $this->model_tshirtecommerce_order->getChildCategory($list_categories, $parent_id);
		foreach($list_categories as $cval)
		{
			if($cval['category_id'] == $parent_id)
				$product_categories[] = $cval;
		}
		
		/*filter list product categories design*/
		$list_product_categories = array();
		foreach($product_categories as $value)
		{
			foreach($list_pr_categories as $pr_key=>$pr_category)
			{
				if($pr_key == $value['category_id'])
					$list_product_categories[$pr_key] = $value;
			}
		}
		
		$ideas_categories = array();
		foreach($list_product_categories as $pr_cate) 
		{
			if($pr_cate['category_id'] == $parent_id)
				continue;
			if ($check_seo)
				$prd_url = $this->model_tshirtecommerce_seo_url->getLink($parent_id, $pr_cate['category_id'], 'tshirtecommerce/ideas');
			else
				$prd_url = $this->url->link('tshirtecommerce/ideas', 'parent_id='.$parent_id.'&category_id='.$pr_cate['category_id']);
			
			$ideas_categories[] = array(
				'name' => $pr_cate['name'],
				'category_id' => $pr_cate['category_id'],
				'href' => $prd_url
			);
		}
		
		/*get all product from category*/
		$list_products_from_category = array();
		if($category_id == 0)
		{
			/*get all product from parent category*/
			foreach($list_product_categories as $value)
			{
				foreach($pr_categories as $pr_category)
				{
					if($pr_category['category_id'] == $value['category_id'])
					{
						if(isset($products[$pr_category['product_id']]))
							$list_products_from_category[$pr_category['product_id']] = $products[$pr_category['product_id']];
					}
				}
			}
			if(isset($products[$parent_id]))
				$list_products_from_category[$parent_id] = $products[$parent_id];
		}else
		{
			$list_product_categories = $this->model_tshirtecommerce_order->getChildCategory($list_categories, $category_id);
			foreach($list_categories as $cval)
			{
				if($cval['category_id'] == $category_id)
					$list_product_categories[] = $cval;
			}
			
			/*get all product from category*/
			foreach($list_product_categories as $value)
			{
				foreach($pr_categories as $pr_category)
				{
					if($pr_category['category_id'] == $value['category_id'])
					{
						if(isset($products[$pr_category['product_id']]))
							$list_products_from_category[$pr_category['product_id']] = $products[$pr_category['product_id']];
					}
				}
			}
			if(isset($products[$category_id]))
				$list_products_from_category[$category_id] = $products[$category_id];
		}
		
		/*sort random*/
		shuffle($list_products_from_category);
		$pr_count = 0;
		$products = array();
		$design_id = 0;
		foreach($list_products_from_category as $product)
		{
			$productdata['product_id'] = $product['product_id'];
			$productdata['design_product_id'] = $product['design_product_id'];
			$productdata['image'] = $product['image'];
			$productdata['name'] = $product['name'];
			$productdata['href'] = $this->url->link('product/product', '&product_id=' . $product['product_id']);
			if ($check_seo)
				$productdata['href'] .= '';
			else
				$productdata['href'] .= '';
			
			if(isset($product['design_product_id']) && $product['design_product_id'] > 0)
				$design_id = $product['design_product_id'];
			
			$products[$product['product_id']] = $productdata;
			$pr_count++;
			if($pr_count > 20)
				break;
		}
	
		include_once (ROOT .DS. 'includes' .DS. 'functions.php');
		$dg = new dg();
	
		$settings 	= $dg->getSetting();
	
		$is_store = false;
		if(
			isset($settings->store) 
			&& isset($settings->store->enable) 
			&& $settings->store->enable == 1
			&& isset($settings->store->verified) 
			&& $settings->store->verified == 1
			&& isset($settings->store->api) 	
			&& $settings->store->api != ''		
		)
		{
			$is_store = true;
		}
	
		if($is_store == false)
			return 'Please active store in admin page!';

		$lang = $dg->lang('lang.ini', false); 
	
		ob_start();
	
		/* get ideas */
		include_once (ROOT .DS. 'includes' .DS. 'store.php');
		$store		= new store($settings);
		$store->dg 		= $dg;

		if(isset($design_id))
		{
			$ideas		= $store->getIdeas($design_id);
		}
		else
		{
			$ideas		= $store->getIdeas(0);
		}

		/*search*/
		$options = array();
		if (isset($this->request->get['cate_id'])) {
			$options['cate_id'] = $this->request->get['cate_id'];
		}
		if (isset($this->request->get['keyword'])) {
			$options['keyword'] = $this->request->get['keyword'];
		}
		if(count($options))
		{
			$ideas		= $store->ideas($ideas, $options);	
		}
	
		/* get product design data */
		$products_design 	= $dg->getProducts();
		$product_design_datas = array();
		foreach($products as $product)
		{
			foreach($products_design as $design)
			{
				if($product['design_product_id'] == $design->id)
				{
					$design->product_id = $product['product_id'];
					$design->thumb = $product['image'];
					$design->href = $product['href'];
					$product_design_datas[] = $design;
					break;
				}
			}
		}
		
		$products = array();
		foreach($product_design_datas as $product)
		{
			$prdata = array();
			$box_width = 500;
			if(isset($product->box_width))
			{
				$box_width = (int) $product->box_width;
			}
			if( isset($product->design) && isset($product->design->front) )
			{
				$zoom 	= 220/$box_width;
				
				$front = $product->design->front;
				
				// get area design
				$area 			= json_decode(str_replace("'", '"', $product->design->area->front));
				$width 			= str_replace('px', '', $area->width);
				$area->width 	= $width * $zoom;
				
				$height 		= str_replace('px', '', $area->height);
				$area->height 	= $height * $zoom;
				
				$top 			= str_replace('px', '', $area->top);
				$area->top 		= $top * $zoom;
				
				$left 			= str_replace('px', '', $area->left);
				$area->left 	= $left * $zoom;
				
				if($area->zIndex < 1)
				{
					$area->zIndex = 100;
				}
				$prdata['zoom'] = $zoom;
				$prdata['front'] = $front;
				$prdata['area'] = $area;
				$prdata['width'] = $width;
				$prdata['height'] = $height;
				$prdata['top'] = $top;
				$prdata['left'] = $left;
			}
			$prdata['box_width'] = $box_width;
			$prdata['product_id'] = $product->product_id;
			$prdata['href'] = $product->href;
			$prdata['thumb'] = $product->thumb;
			$products[] = $prdata;
		}
		//echo '<pre>'; print_r($products); exit;
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$site_url 	=  $this->config->get('config_ssl');
		} else {
			$site_url 	=  $this->config->get('config_url');
		}
		
		$params = '';
		if (isset($this->request->get['color'])) {
			$params .= '&color=' . $this->request->get['color'];
		}

		if (isset($this->request->get['keyword'])) {
			$params .= '&keyword=' . $this->request->get['keyword'];
		}

		if (isset($this->request->get['cate_id'])) {
			$params .= '&cate_id=' . $this->request->get['cate_id'];
		}
		
		$data['TSHIRTECOMMERCE_ROOT'] = $TSHIRTECOMMERCE_ROOT;
		$data['site_url'] = $site_url;
		$data['lang'] = $lang;
		$data['page'] = $page;
		
		include_once($TSHIRTECOMMERCE_ROOT.'opencart'. DS .'store'. DS .'store.php');
		$data['store_ideas'] = ob_get_clean();
		/*end ideas*/

		$pagination = new Pagination();
		$pagination->total = $ideas['count'];
		$pagination->page = $page;
		$pagination->limit = $limit;
		
		if ($check_seo && isset($this->request->get['_route_']))
		{
			$pagination->url = $site_url.$this->request->get['_route_'].$params.'&page={page}';
		}else
		{
			$pagination->url = $this->url->link('tshirtecommerce/ideas'.$params, 'page={page}');
		}

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($ideas['count']) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($ideas['count'] - $limit)) ? $ideas['count'] : ((($page - 1) * $limit) + $limit), $ideas['count'], ceil($ideas['count'] / $limit));

		if ($limit && ceil($ideas['count'] / $limit) > $page) {
			$this->document->addLink($this->url->link('product/category', 'page='. ($page + 1), true), 'next');
		}
		
		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('tshirtecommerce/ideas', $data));
	}
}

?>