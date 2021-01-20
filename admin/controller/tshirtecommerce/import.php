<?php 

class ControllerTshirtecommerceImport extends Controller 
{
	private $error = array();

	protected $url_valid_code = 'http://updates.tshirtecommerce.com/verify_purchase.php';
	protected $param_valid_code = '%s?code=%s&platform=opencart';

	// http://dev.9file.net/store/import/index?name=base64_encode($site_name)_$purchased&returnUrl=base64_encode($site_url)
	protected $url_import_products = 'http://dev.9file.net/store/import/index?name=%s_%s&returnUrl=%s&task=product';
	protected $url_import_cliparts = 'http://dev.9file.net/store/import/index?name=%s_%s&returnUrl=%s&task=toolsart';
	protected $url_import_designs = 'http://dev.9file.net/store/import/index?name=%s_%s&returnUrl=%s&task=design';
	protected $url_import_templates = 'http://dev.9file.net/store/import/index?name=%s_%s&returnUrl=%s&task=toolsdesign';
	protected $param_site_url = '%s|%s';

	//http://dev.9file.net/store/download/index/TASK/API/SITE_ID
	protected $url_download = 'http://dev.9file.net/store/download/index/%s/%s/%s';
	protected $task_product = 'products';
	protected $task_design = 'design';
	protected $task_clipart = 'clipart';

	protected $url_image = 'http://dev.9file.net/store/products_data/tshirtecommerce/';
	protected $data_path = '/tshirtecommerce/data/';
	
	protected $download_url 	= 'http://dev.9file.net/store/download/index/';
	protected $product_img 	= 'http://dev.9file.net/store/products_data/tshirtecommerce/';

	public function index()
	{
		$this->data_path = dirname(dirname(dirname(dirname(__FILE__)))) . $this->data_path;
		
		$str_token = (version_compare(VERSION, '3.0.0', '>=')) ? 'user_token='.$this->session->data['user_token'] : 'token='.$this->session->data['token'];
		$str_ssl = (version_compare(VERSION, '2.2.0.0', '>=')) ? true : 'SSL';

		$this->load->language('tshirtecommerce/import');

		$this->load->model('setting/store');
		$config_store_id = $this->config->get('config_store_id');
		if ($config_store_id == null) {
			$config_store_id = 0;
			$store_info = array(
				'store_id' => 0,
				'name' => $this->config->get('config_name'),
				'url' => $this->config->get('config_secure') ? HTTPS_CATALOG : HTTP_CATALOG,
			);
		} else {
			$store_info = $this->model_setting_store->getStore($config_store_id);
		}

		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('tshirtecommerce');
		if (isset($this->request->get['status']) && $this->request->get['status'] == 'success') {
			$data['error_success'] = $this->language->get('import_msg_success');
		}
		
		$html 		= '';
		if(isset($this->request->get['api']) && $this->request->get['api'] != '' && isset($this->request->get['task']) && $this->request->get['task'] != '' && isset($this->request->get['site_id']) && $this->request->get['site_id'] != '')
		{
			$this->set_init($this->request->get['api'], $this->request->get['site_id']);
			
			$task 		= $this->request->get['task'];

			$this->task = $task;
			if($task == 'products')
			{
				if( file_exists($this->product_file) )
				{
					$html = $this->loadProducts();
				}
				else
				{
					$html = $this->download_products();
				}
			}
			elseif($task == 'design')
			{
				$html 	= $this->design();
			}
			elseif($task == 'cliparts')
			{
				$html 	= $this->cliparts();
			}
			elseif($task == 'designidea')
			{
				$html 	= $this->your_design();
			}
			elseif($task == 'customIdea')
			{
				$html 	= $this->your_design('customIdea');
			}
			elseif($task == 'customClipart')
			{
				$html 	= $this->your_design('customClipart');
			}
		}
		$data['import_html'] = $html;

		$this->load->language('extension/module/tshirtecommerce');
		$this->document->setTitle($this->language->get('heading_title'));
		$data['heading_title'] = $this->language->get('heading_title');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $str_token, $str_ssl)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tshirtecommerce/import', $str_token, $str_ssl)
		);

		$data['checkcode'] = $this->validatePurcahseCode();

		$data['url_import_products'] = '';
		$data['url_import_cliparts'] = '';
		$data['url_import_designs'] = '';
		$data['url_import_templates'] = '';

		if ($data['checkcode'] == true) {
			$site_name = base64_encode($store_info['name']);

			$main_url = $store_info['url'];
			$main_url = rtrim($main_url, '/');
			$http_referer = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			if (strpos($main_url, 'http://') === false || strpos($main_url, 'https://') === false) {
				$http_referer = $this->config->get('config_secure') ? 'https://'.$http_referer : 'http://'.$http_referer ;
			}

			if(strpos($main_url, 'https://') !== false)
			{
				$http_referer = str_replace('http://', 'https://', $http_referer);
			}

			$link_of_page_import = str_replace($main_url.'/', '', $http_referer);
			$link_of_page_import = ltrim($link_of_page_import, '/');

			$site_url = base64_encode(sprintf($this->param_site_url, $main_url, $link_of_page_import));

			$purchase_code = $this->config->get('tshirtecommerce_code');

			$data['url_import_products'] = sprintf($this->url_import_products, $site_name, $purchase_code, $site_url);
			$data['url_import_cliparts'] = sprintf($this->url_import_cliparts, $site_name, $purchase_code, $site_url);
			$data['url_import_designs'] = sprintf($this->url_import_designs, $site_name, $purchase_code, $site_url);
			$data['url_import_templates'] = sprintf($this->url_import_templates, $site_name, $purchase_code, $site_url);
		}else
		{
			$data['msg_valid_code'] = $this->language->get('msg_valid_code');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tshirtecommerce/import', $data));
	}
	
	/*
	* Import clipart of customers using tools of store
	 */
	public function cliparts()
	{
		$link 	= $this->getLinkDownload();
		
		$downloaded = false;

		$data 	= $this->openURL($link);
		if ($data != false && strlen($data) > 100)
		{
			$path 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/';
			$file 	= $path . 'cliparts.json';
			if(file_put_contents($file, $data))
			{
				$downloaded = true;
			}
		}

		$folder 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/';
		
		if ($this->request->server['HTTPS'])
			$base_url = HTTPS_CATALOG;
		else
			$base_url = HTTP_CATALOG;
		
		$import_url = $base_url.'tshirtecommerce/admin/index.php?/clipart/import';
		
		$success_url = $base_url.'admin/index.php?route=tshirtecommerce/import&status=success&user_token='.$this->request->get['user_token'];

		ob_start();
		include_once(dirname(__FILE__).'/import/import_cliparts.php');
		$content = ob_get_contents();
   		ob_end_clean();
		return $content;
	}

	protected function getImages(&$data, $row = array(), $view = 'front')
	{
		if (isset($row['design']) && isset($row['design']['images_'.$view]) && !empty($row['design']['images_'.$view])) {
			$img_names = explode("','", $this->getBetween($row['design']['images_'.$view], "['", "']"));
			if (count($img_names)) {
				$index = 1;
				foreach ($img_names as $img_name) {
					if (strpos($img_name, 'http://') !== false || strpos($img_name, 'https://') !== false) {
						$images_right = $img_name;
						//$img_name = basename($img_name);
					} else {
						$images_right = $this->url_image.$img_name;	
					}
					$extension = pathinfo($images_right, PATHINFO_EXTENSION);
					$oc_image = DIR_IMAGE.'/catalog/'.$view.'_'.$row['sku'].'_'.$index.'.'.$extension;
					//$ts_image = dirname(DIR_SYSTEM).'/tshirtecommerce/'.$img_name;
					$this->downloadImg($images_right, $oc_image);
					//$this->downloadImg($images_right, $ts_image);
					$image = 'catalog/'.$view.'_'.$row['sku'].'_'.$index.'.'.$extension;
					$data['product_image'][] = array(
						'image' => $image,
						'sort_order' => 0,
					);
					$index++;
				}
			}
		}
	}

	protected function validatePurcahseCode()
	{
		$return = false;

		$purchase_code = $this->config->get('tshirtecommerce_code');
		if ($purchase_code == false || $purchase_code == null || $purchase_code == '') {
			return $return;
		}

		$array = array();
		$json = $this->openURL(sprintf($this->param_valid_code, $this->url_valid_code, $purchase_code));
		if ($json !== false) {
			$array = json_decode($json, true);
		}
		if (isset($array['error']) && $array['error'] == 0) {
			$return = true;
		}

		return $return;
	}

	protected function removeSpecialChar($value)
	{
		$result  = preg_replace('/[^a-zA-Z0-9_ -]/s', '', html_entity_decode($value));
		$result = str_replace(' ', '-', $result);
		return strtolower($result);
	}

	protected function getBetween($content, $start, $end)
	{
	    $r = explode($start, $content);
	    if (isset($r[1])) {
	        $r = explode($end, $r[1]);
	        return $r[0];
	    }
	    return '';
	}

	protected function openURL($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
	{
	    $user_agent = 'User-Agent: curl/7.39.0';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		}
		
		$data = false;
		
		if (function_exists('curl_exec')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
	      	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			$data = curl_exec($ch);
			curl_close($ch);
		}
		
		if ($data == false && function_exists('file_get_contents')) {
			$arrContextOptions = array(
				"ssl" => array(
					"verify_peer" => false,
					"verify_peer_name" => false,
				),
			);
			$data = @file_get_contents($url, false, stream_context_create($arrContextOptions));
		}
		
		return $data;
	}

	protected function downloadImg($url_to_image, $complete_save_loc)
	{
		if (ini_get('allow_url_fopen')) {
			@file_put_contents($complete_save_loc, @file_get_contents($url_to_image));
		} else {
			$user_agent = 'User-Agent: curl/7.39.0';
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$user_agent = $_SERVER['HTTP_USER_AGENT'];
			}

			$ch = curl_init($url_to_image);
			$fp = @fopen($complete_save_loc, 'w+');

			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
			curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			// curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_exec($ch);
			curl_close($ch);
			@fclose($fp);
		}
	}
	
	function import_product_design() 
	{
		$file_products 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/products.json';
		$added = 0;
		if( isset($_POST['id']) )
		{
			$file_products 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/products.json';
			if( isset($_POST['remove']) && $_POST['remove'] == 1 )
			{
				unlink($file_products);
				echo 1;
				exit();
			}

			$id = $_POST['id'];
			if(file_exists($file_products))
			{
				$content 	= file_get_contents($file_products);
				$products 	= json_decode($content, true);
				if(count($products))
				{
					foreach($products as $product)
					{
						if($id == $product['id'])
						{
							$data = $product;
							break;
						}
					}

					if(isset($data))
					{
						$data = $this->oc_add_product($product);
						$added 	= $data['image'];
					}
				}
			}
		}
		echo $added;
	}
	
	public function add_product($data)
	{
		$file 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/products.json';
		$content 	= file_get_contents($file);

		$design_id 	= 0;
		if( $content != false )
		{
			$row 	= json_decode($content, true);
			if( count($row) && isset($row['products']) )
			{
				$added 	= false;
				if(count($row['products']))
				{
					foreach ($row['products'] as $i => $product)
					{
						if($product['sku'] == $data['sku'])
						{
							$added 	= true;
							$design_id	= $product['id'];
							$data['id'] = $design_id;
							$row['products'][$i] = $data;
							break;
						}
						else
						{
							if($product['id'] > $design_id){
								$design_id = $product['id'];
							}
						}
					}
				}
				if($added === false)
				{
					$design_id 			= $design_id + 1;
					$data['id']			= $design_id;
					$row['products'][] 	= $data;

					$content 			= json_encode($row);
					file_put_contents($file, $content);
				}
			}
		}

		return $design_id;
	}
	
	function download_images($data)
	{
		if( strpos($data['image'], 'http') === false )
		{
			$data['image'] 	= $this->product_img . $data['image'];
		} 
		return $data;
	}

	function oc_add_product($data)
	{
		if(count($data) == 0) return;
		
		$count = 0;
		$query = $this->db->query('SELECT COUNT(*) AS total FROM `'.DB_PREFIX.'product` WHERE `sku` = "'.$this->db->escape($data['sku']).'"');
		$count = $query->row['total'];
		if ($count < 1) 
		{
			$row = $this->download_images($data);
			$design_id 	= $this->add_product($data);
			if($design_id > 0)
			{
				$this->load->model('tool/image');
				$this->load->model('catalog/product');
				$this->load->model('tshirtecommerce/import');
				$this->load->model('localisation/language');
				
				$data = array(
					'model' => $row['title'],
					'sku' => $row['sku'],
					'upc' => '',
					'ean' => '',
					'jan' => '',
					'isbn' => '',
					'mpn' => '',
					'location' => '',
					'quantity' => $row['max_oder'],
					'minimum' => $row['min_order'],
					'subtract' => 1,
					'stock_status_id' => 5, // out of stock
					'date_available' => date("Y-m-d", (time() - 60 * 60 * 24)),
					'manufacturer_id' => 0,
					'shipping' => 1, // require shipping
					'price' => (isset($row['sale_price']) && !empty($row['sale_price']) && $row['sale_price'] > 0) ? $row['sale_price'] : $row['price'],
					'points' => 0,
					'weight' => '1.50000000',
					'weight_class_id' => 1,
					'length' => '1.00000000',
					'width' => '2.00000000',
					'height' => '3.00000000',
					'length_class_id' => 1,
					'status' => 1,
					'tax_class_id' => 0, // no taxes
					'sort_order' => 1,
				);

				$extension = pathinfo($this->url_image.$row['image'], PATHINFO_EXTENSION);
				$image = DIR_IMAGE.'catalog/'.$row['sku'].'.'.$extension;
				$ts_image = dirname(DIR_SYSTEM).'/tshirtecommerce/'.$row['image'];
				if(strpos($row['image'], 'http') != -1)
				{
					$this->downloadImg($row['image'], $image);
					$this->downloadImg($row['image'], $ts_image);
				}else
				{
					$this->downloadImg($this->url_image.$row['image'], $image);
					$this->downloadImg($this->url_image.$row['image'], $ts_image);
				}
				
				$data['image'] = 'catalog/'.$row['sku'].'.'.$extension;

				$languages = $this->model_localisation_language->getLanguages();
				if (count($languages)) {
					foreach ($languages as $language) {
						$data['product_description'][$language['language_id']] = array(
							'name' => $row['title'],
							'description' => $row['description'],
							'tag' => '',
							'meta_title' => '',
							'meta_description' => '',
							'meta_keyword' => '',
						);
					}
				}
				
				$config_store_id = $this->config->get('config_store_id');
				if ($config_store_id == null)
					$config_store_id = 0;
				$data['product_store'] = array($config_store_id);

				$data['product_image'] = array();
				$this->getImages($data, $row, 'front');
				$this->getImages($data, $row, 'back');
				$this->getImages($data, $row, 'left');
				$this->getImages($data, $row, 'right');

				if (count($languages)) {
					$seo_urls = array();
					foreach ($languages as $language) {
						$seo_urls[$language['language_id']] = $this->removeSpecialChar($row['title']);
					}
					if (count($seo_urls)) {
						$data['product_seo_url'][$config_store_id] = $seo_urls;
					}
				}

				$return = $this->model_catalog_product->addProduct($data);
				$this->model_tshirtecommerce_import->import($row, $return);
			}
		}
		return $data;
	}
	
	/*
	* download clipart & design template
	 */
	public function design()
	{
		$link 	= $this->getLinkDownload();

		$folder 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/store/';
		
		if ($this->request->server['HTTPS']) 
			$base_url = HTTPS_CATALOG;
		else 
			$base_url = HTTP_CATALOG;
		
		$import_url = $base_url.'admin/index.php?route=tshirtecommerce/import/download_design&user_token='.$this->request->get['user_token'];
		$success_url = $base_url.'admin/index.php?route=tshirtecommerce/import&status=success&user_token='.$this->request->get['user_token'];

		ob_start();
		include_once(dirname(__FILE__).'/import/import_design.php');
		$content = ob_get_contents();
   		ob_end_clean();
		return $content;
	}
	
	function download_design()
	{
		$result 	= array(
			'error' 	=> 1,
			'mgs'		=> "Your server not support download and unzip file."
		);
		if($_POST['link'])
		{
			$link 	= $_POST['link'];
			$data 	= $this->openURL($link);
			if ($data != false && strlen($data) > 100)
			{
				$path 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/store/';
				$file 	= $path . 'store.zip';
				if(file_put_contents($file, $data))
				{
					$zip = new ZipArchive;
					if ($zip->open($file) === TRUE) 
					{
						$zip->extractTo($path);
						$zip->close();
						$result['error'] = 0;
					}
				}
				unlink($file);
			}
			else
			{
				$result['mgs'] 	= 'Data not found!';
			}
		}
		echo json_encode($result);
		exit();
	}
	
	/* 
	* Download add design template of customers
	*/
	public function your_design($task = '')
	{
		$link 	= $this->getLinkDownload();
		
		$downloaded = false;
		$data 		= $this->openURL($link);
		$file_name 	= 'designs'.$task.'.txt';
		if ($data != false && strlen($data) > 100)
		{
			$path 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/';
			$file 	= $path . $file_name;
			if(file_put_contents($file, $data))
			{
				$downloaded = true;
			}
		}

		$folder 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/';
		
		if ($this->request->server['HTTPS']) 
			$base_url = HTTPS_CATALOG;
		else 
			$base_url = HTTP_CATALOG;
		
		$import_url = $base_url.'admin/index.php?route=tshirtecommerce/import/download_your_design&user_token='.$this->request->get['user_token'];
		$success_url = $base_url.'admin/index.php?route=tshirtecommerce/import&status=success&user_token='.$this->request->get['user_token'];
		$user_id 	= md5($this->user->getId());
		
		if(empty($task) || $task == '') $task = 'importStore';
		$import_design_url = $base_url.'tshirtecommerce/ajax.php?type=addon&task=store&view='.$task.'&user_id='.$user_id;

		ob_start();
		include_once(dirname(__FILE__).'/import/import_your_design.php');
		$content = ob_get_contents();
   		ob_end_clean();
		return $content;
	}

	public function download_your_design()
	{
		if( isset($_POST['site_id']) && isset($_POST['api']) )
		{
			$link 	= $this->download_url.'vectors/'.$_POST['api'].'/'.$_POST['site_id'];
			$data 	= $this->openURL($link);
			
			if ($data != false && strlen($data) > 100)
			{
				$path 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/cache/admin/';
				$file 	= $path . 'designs.zip';
				if(file_put_contents($file, $data))
				{
					$zip = new ZipArchive;
					if ($zip->open($file) === TRUE) 
					{
						$zip->extractTo($path);
						$zip->close();
						
						$folders = glob($path . 'user*' , GLOB_ONLYDIR);
						if(isset($folders[0]))
						{
							$folder 	= $folders[0];
							$files 	= glob($folder . '/*.txt');
							for($i=0; $i<count($files); $i++)
							{
								$file_name 	= str_replace($folder.'/', '', $files[$i]);
								rename($files[$i], $path.$file_name);
							}
							unlink($folder);
						}
					}
				}
				unlink($file);
			}
		}
		exit;
	}
	
	public function getLinkDownload()
	{
		$url = $this->download_url.$this->task.'/'.$this->api.'/'.$this->site_id;

		return $url;
	}
	
	public function loadProducts()
	{
		$content 	= file_get_contents($this->product_file);
		$data 	= json_decode($content, true);
		$html 	= $this->displayProducts($data);

		return $html;
	}
	
	public function set_init($api, $site_id)
	{
		$this->api 			= $api;
		$this->site_id 		= $site_id;
		$this->addAPI();
		$this->product_file 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/import/products.json';
	}
	
	public function addAPI()
	{
		$file 	= dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/data/settings.json';
		if( file_exists($file) )
		{
			$content = file_get_contents($file);
			$settings = json_decode($content, true);

			if( isset($settings['store']) && isset($settings['store']['api']) )
			{
				$settings['store']['api'] = $this->api;
			}
			else
			{
				$settings['store'] = array(
					'enable' => 1,
					'verified' => 1,
					'auto_download' => 1,
					'your_clipart' => 1,
					'exchange_rate' => '0.2',
					'api' => $this->api,
				);
			}
			file_put_contents($file, json_encode($settings));
		}
	}
	
	public function download_products()
	{
		$path_data = dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/';
		$product_file = $path_data.'data/import/products.json';
		if (isset($this->request->get['site_id']) && $this->request->get['site_id'] != '' && isset($this->request->get['api']) && $this->request->get['api'] != '')
		{
			$download_url = 'http://dev.9file.net/store/download/index/products/'.$this->request->get['api'].'/'.$this->request->get['site_id'];
			$content 		= $this->openURL($download_url);
			$html 		= '';
			if($content === false)
			{
				$html = '<div class="notice notice-error">'
						.'<h3>Your server not allow connect to our server and download file. Please do step by step to import</h3>'
						.'<p>1. Please download file <a href="'.$download_url.'" target="_blank">products.json</a></p>'
						.'<p>2. Upload file products.json to folder <strong>'.$path_data.'data/import</strong></p>'
						.'<p>3. Reload this page and try again.</p>'
					.'</div>';
			}
			else
			{
				$data = json_decode($content, true);
				if(isset($data['error']))
				{
					$html 	= '<h3 class="notice notice-error"><strong>Data not found</strong></h3>';
				}
				else
				{
					file_put_contents($product_file, $content);
					$html = $this->displayProducts($data);
				}
			}

			return $html;
		}
	}
	
	function displayProducts($products)
	{
		if ($this->request->server['HTTPS']) 
			$base_url = HTTPS_CATALOG;
		else 
			$base_url = HTTP_CATALOG;
		
		$import_url = $base_url.'admin/index.php?route=tshirtecommerce/import/import_product_design&user_token='.$this->request->get['user_token'];
		$success_url = $base_url.'admin/index.php?route=tshirtecommerce/import&status=success&user_token='.$this->request->get['user_token'];
		
		$product_img 	= $this->product_img;
		ob_start();
		include_once(dirname(__FILE__).'/import/import.php');
		$content = ob_get_contents();
   		ob_end_clean();
		
		return $content;
	}
}