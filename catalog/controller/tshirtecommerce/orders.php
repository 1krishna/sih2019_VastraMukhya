<?php 
// Tshirtecommerce
// @since 4.2.0

class ControllerTshirtecommerceOrders extends Controller
{
	public function index()
	{
		$res['error'] = 1;
		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(dirname(dirname(dirname(__FILE__)))).DS.'tshirtecommerce');
		include_once  ROOT .DS.'includes'.DS.'functions.php';
    	$dg = new dg();
		
		/*check api*/
		if(!isset($this->request->get['api']) || $this->request->get['api'] == '')
		{
			$res['data'] = 'Data not found';
			echo json_encode($res); exit;
		}
		
		$api = $this->request->get['api'];
		$api_code = '';
		$path = ROOT .DS.'data'.DS.'settings.json';
		if(file_exists($path))
		{
			$settings = json_decode(file_get_contents($path), true);
			if(isset($settings['store']['enable']) && $settings['store']['enable'] == 1)
			{
				if(isset($settings['store']['api']))
					$api_code = $settings['store']['api'];
			}
		}
		
		if($api != $api_code)
		{
			$res['data'] = 'Data not found';
			echo json_encode($res); exit;
		}
		if($this->config->get('tshirtecommerce_send_order') != 1)
		{
			$res['data'] = 'Data not found';
			echo json_encode($res); exit;
		}
		
		/*order info*/
		if(!isset($this->request->get['order_id']) || $this->request->get['order_id'] < 1)
		{
			$res['data'] = 'Data not found';
			echo json_encode($res); exit;
		}
		$order_id = $this->request->get['order_id'];
		
		$this->load->model('checkout/order');
		$this->load->model('account/order');
		$order = $this->model_checkout_order->getOrder($order_id);
		
		if(count($order) > 1)
		{
			$res['error'] = 0;
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
			$sub_total = 0;
			$shipping = 0;
			$tax = 0;
			$coupon = 0;
			if(count($query->rows))
			{
				foreach($query->rows as $val)
				{
					if($val['code'] == 'sub_total')
						$sub_total = $val['value'];
					if($val['code'] == 'shipping')
						$shipping = $val['value'];
					if($val['code'] == 'tax')
						$tax = $tax + $val['value'];
					if($val['code'] == 'coupon')
						$coupon = $coupon + $val['value'];
				}
			}
			$res['data']['info'] = array(
				"order_id"=>$order_id,
				"order_parent_id"=>"null",
				"order_key"=> $order['invoice_prefix'],
				"status"=> $order['order_status'],
				"currency"=> $order['currency_code'],
				"payment_method"=> $order['payment_code'],
				"payment_method_title"=> $order['payment_method'],
				"date_created"=> $order['date_added'],
				"date_modified"=> $order['payment_method'],
				"subtotal"=> $sub_total,
				"discount_total"=> $coupon,
				"shipping_total"=> $shipping,
				"total_tax"=> $tax,
				"customer_id"=> $order['customer_id'],
				"total"=> $order['total']
			);
			$res['data']['shipping'] = array(
				"first_name"=> $order['shipping_firstname'],
				"last_name"=> $order['shipping_lastname'],
				"company"=> $order['shipping_company'],
				"address_1"=> $order['shipping_address_1'],
				"address_2"=> $order['shipping_address_2'],
				"city"=> $order['shipping_city'],
				"state"=> $order['shipping_zone'],
				"postcode"=> $order['shipping_postcode'],
				"country"=> $order['shipping_country'],
				"customer_note"=> $order['comment'],
				"email"=> $order['email'],
				"phone"=> $order['telephone']
			);
			$res['data']['billing'] = array(
				"first_name"=> $order['shipping_firstname'],
				"last_name"=> $order['shipping_lastname'],
				"company"=> $order['shipping_company'],
				"address_1"=> $order['shipping_address_1'],
				"address_2"=> $order['shipping_address_2'],
				"city"=> $order['shipping_city'],
				"state"=> $order['shipping_zone'],
				"postcode"=> $order['shipping_postcode'],
				"country"=> $order['shipping_country'],
				"email"=> $order['email'],
				"phone"=> $order['telephone']
			);
			$products = $this->model_account_order->getOrderProducts($order_id);
			if(count($products))
			{
				$data = array();
				foreach($products as $product)
				{
					$query = $this->db->query('SELECT `options` FROM `'.DB_PREFIX.'tshirtdesign_order` WHERE `order_product_id`='.(int)$product['order_product_id']);
					$design = array();
					$design_price = 0;
					
					$design_id = '';
					$color_hex = '';
					$color_title = '';
					$teams = array();
					$options = array();
					$this->load->model('catalog/product');
					$product_info = $this->model_catalog_product->getProduct($product['product_id']);
					$images = '{}'; 
					if ($this->request->server['HTTPS']) {
						$site_url = HTTPS_SERVER;
					} else {
						$site_url = HTTP_SERVER;
					}
					$thumb = '';
					if(isset($product_info['image']))
						$thumb = $site_url.'image/'.$product_info['image'];
					
					if ($query->num_rows) {
						$json = json_decode($query->row['options'], true);
						$t_product_id = 0;
						if(isset($json['price_of_print']))
							$design_price = $json['price_of_print'];
						if(isset($json['options']['rowid']))
							$design_id = $json['options']['rowid'];
						if(isset($json['options']['color_hex']))
							$color_hex = $json['options']['color_hex'];
						if(isset($json['options']['color_title']))
							$color_title = $json['options']['color_title'];
						if(isset($json['options']['options']))
							$options = $json['options']['options'];
						if(isset($json['options']['images']))
							$images = $json['options']['images'];
						if(isset($json['options']['teams']))
							$teams = $json['options']['teams'];
						if(isset($json['options']['t_product_id']))
							$t_product_id = $json['options']['t_product_id'];
						
						$cache = $dg->cache('cart');
						if(isset($json['price_of_print']))
							$design = $cache->get($json['options']['rowid']);
						
						if( isset($design['vector']) )
						{
							unset($design['vector']);
						}
						if( isset($design['vectors']) )
						{
							unset($design['vectors']);
						}
						
						$file = ROOT .DS.'data'.DS.'products.json';
						if (is_file($file) && file_exists($file)) {
							$json_products = json_decode($dg->openUrl($file), true);
						}

						$productdesign = array();
						if ($json_products != false && isset($json_products['products']) && count($json_products['products']) > 0) {
							foreach ($json_products['products'] as $row) {
								if ($row['id'] == $t_product_id) {
									$productdesign = $row;
									break;
								}
							}
						}
						if(isset($productdesign['design']))
							$design['product'] = $productdesign['design'];
					}
					
					$this->load->model('catalog/product');
					$result = $this->model_catalog_product->getProduct($product['product_id']);
					
					$data[] = array(
						"id"=> $product['order_product_id'],
						"item_name"=> $product['name'],
						"product_name"=> $product['name'],
						"product_id"=> $product['product_id'],
						"product_model"=> $product['model'],
						"product_sku"=> $result['sku'],
						"product_price"=> $product['price'],
						"stock_quantity"=> $result['quantity'],
						"quantity"=> $product['quantity'],
						"tax_class"=> $result['tax_class_id'],
						"subtotal"=> $product['total'],
						"subtotal_tax"=> $product['tax'],
						"total"=> $product['total'],
						"total_tax"=> $product['tax'],
						"design"=> array(
							"design_price" => $design_price,
							"design_id" => $design_id,
							"color_hex" => $color_hex,
							"color_title" => $color_title,
							"teams" => $teams,
							"options" => $options,
							"images" => $images,
							"design" => $design
						),
						"url"=> $this->url->link('product/product', 'product_id=' . $product['product_id']),
						"thumb"=> $thumb
					);
				}
				$res['data']['products'] = $data;
				$res['data'] = base64_encode(json_encode($res['data']));
				echo json_encode($res);
				exit;
			}
		}else
		{
			$res['data'] = 'Data not found';
			echo json_encode($res); exit;
		}
	}
}