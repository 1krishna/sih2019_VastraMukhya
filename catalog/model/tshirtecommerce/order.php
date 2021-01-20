<?php
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		Aug 29, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ModelTshirtecommerceOrder extends Model
{
	public function getOptions($order_id, $product_id = 0)
	{
		$query = $this->db->query("
			SELECT pt.`order_product_id`,pt.`options`,op.`product_id`,op.`quantity` 
			FROM `".DB_PREFIX."tshirtdesign_order` pt 
			INNER JOIN (
				SELECT `order_product_id`,`product_id`,`quantity` 
				FROM `".DB_PREFIX."order_product` 
				WHERE `order_id` = '".(int)$order_id."' 
					AND `product_id` = '".(int)$product_id."'
			) op ON pt.`order_product_id` = op.`order_product_id` 
			ORDER BY pt.`order_product_id`
		");
		if ($query->num_rows) return $query->rows;

	 	return false;
	}

	public function getCarts($cart_id)
	{
		$query = $this->db->query("
			SELECT * 
			FROM `".DB_PREFIX."cart` 
			WHERE `cart_id`='".(int)$cart_id."'
		");
		if ($query->num_rows) return $query->rows;

		return false;
	}

	public function getPrices($order_id)
	{
		$query = $this->db->query("
			SELECT * 
			FROM `".DB_PREFIX."order_product` 
			WHERE `order_id`='".(int)$order_id."' 
			ORDER BY `order_product_id`
		");
		if ($query->num_rows) return $query->rows;

		return false;
	}

	public function getItemOrder($order_id, $product_id = 0)
	{
	 	$prices = array();
	 	$query = $this->db->query("
	 		SELECT op.`product_id`,op.`quantity`, op.`price`, op.`total` 
	 		FROM `".DB_PREFIX."order_product` op 
	 		WHERE `product_id`='".(int)$product_id."' 
	 			AND `order_id`='".(int)$order_id."'
	 	");
	 	if ($query->num_rows) return $query->rows;

	 	return false;
	}

	public function getItem($order_product_id)
	{		
		$item = $this->db->query("
			SELECT * 
			FROM `".DB_PREFIX."tshirtdesign_order` 
			WHERE `order_product_id` = '".(int)$order_product_id."' ORDER BY `id` DESC LIMIT 1
		");
		if ($item->num_rows) {
			$string = $item->row['options'];
			if ($string == '') {
				return false;
			} else {
				return json_decode($string);
			}
		} else {
			return false;
		}
	}
	
	// get product
	public function getProduct($product_id)
	{		
		$item = $this->db->query("
			SELECT `product_id`, `design_product_id` 
			FROM `".DB_PREFIX."product` 
			WHERE `status` = 1 AND `product_id` = '".$product_id."'
		");
		if ($item->num_rows) {
			$data = array(
				'product_id' => $item->row['product_id'],
				'design_product_id' => $item->row['design_product_id'],
			);
			return $data;
		}
		return false;
	}
	
	// get all product design
	public function getProducts()
	{
		$items = $this->db->query("SELECT * FROM `".DB_PREFIX."product` WHERE `status` = 1 AND `design_product_id` != ''");
		$products = 0;
		if ($items->num_rows) {
			$products = array();
			foreach ($items->rows as $item) {
				$design_product_id = $item['design_product_id'];
				$n = explode(':', $design_product_id);
				if (count($n) == 1) $products[] = $item;
			}
		}
		return $products;
	}

	// Fixed #32805
	public function checkProductDesign($parent_id, $product_id)
	{
		$array = explode(':', $product_id);
		if (count($array) > 1) {
			$product_id = $array[2];
		}

		// Check exist on opencart
		$count = 0;
		$query = $this->db->query('SELECT COUNT(*) AS total FROM `'.DB_PREFIX.'product` WHERE `product_id` ='.(int)$parent_id);
		if ($query->num_rows) $count = $query->row['total'];
		if ($count < 1) return false;
		
		// Check exist on tshirtecommerce
		$products = array();
		$file = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
		if (!is_file($file) || !file_exists($file)) {
			return false;
		}
		$contents = $this->openUrl($file);
		if ($contents !== false) {
			$json = json_decode($contents, true);
			if (!isset($json['products'])) return false;

			if (count($json['products']) > 0) {
				foreach ($json['products'] as $product) {
					if (isset($product['id'])) $products[] = $product['id'];
				}
			}
		}

		if (!in_array($product_id, $products))
			return false;

		return true;
	}

	public function openUrl($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
	{
		if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
            $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)));
        }
        if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
            return @file_get_contents($url, $use_include_path, $stream_context);
        } elseif (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            if ($stream_context != null) {
                $opts = stream_context_get_options($stream_context);
                if (isset($opts['http']['method']) && $this->strtolower($opts['http']['method']) == 'post') {
                    curl_setopt($curl, CURLOPT_POST, true);
                    if (isset($opts['http']['content'])) {
                        parse_str($opts['http']['content']);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, array());
                    }
                }
            }
            $content = curl_exec($curl);
            curl_close($curl);
            return $content;
        } else {
            return false;
        }
	}

	protected static function strtolower($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }
        return strtolower($str);
    }

    public function getPriceOfPrint($design_product_id, $design = array(), $cart = false)
    {
    	if (empty($design_product_id) || $design_product_id < 0) return 0;

    	$price_of_print = 0;
    	$array = explode(':', $design_product_id);

    	$design['product_id'] = $design_product_id;

    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    	if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');

    	include_once ROOT.DS.'includes'.DS.'functions.php';
    	$dg = new dg();

    	$design['print'] = array(
    		'sizes' => '{}',
    		'elements' => '{front: [], back: [], left: [], right: []}',
    		'colors' => '{front:[], back:[], left:[], right:[]}'
    	);

    	if (count($array) > 1) {
    		$design['product_id'] = $array[2];
    		if (!isset($design['colors']) || $design['colors'] == false) {
	    		$design['colors'] = array();
	    		$tmp_colors = explode(';', $array[3]);
	    		foreach ($tmp_colors as $hex) {
	    			$design['colors'][] = $hex;
	    		}
	    	}

	    	$cache = $dg->cache('admin');
	    	$designs = $cache->get($array[0]);
	    	$str_vectors = '';
            if ($designs != false && count($designs) > 0) {
    	    	foreach ($designs as $key => $row) {
    	    		if ($key == $array[1]) {
    	    			$design['print'] = $row['print'];
    	    			$design['teams'] = isset($row['teams']) ? $row['teams'] : array();
    	    			$design['print_type'] = $row['print_type'];
    	    			$design['cliparts'] = isset($row['cliparts']) ? $row['cliparts'] : array();
    	    			
    	    			if (isset($row['vectors'])) $str_vectors = $row['vectors'];
    	    			break;
    	    		}
    	    	}
            }

	    	if (count($design) < 1) {
	    		$cache = $dg->cache('design');
		    	$designs = $cache->get($array[0]);
		    	foreach ($designs as $key => $row) {
		    		if ($key == $array[1]) {
		    			$design['print'] = $row;
		    			break;
		    		}
		    	}
	    	}
	    }

        $design['attribute'] = isset($design['attribute']) ? $design['attribute'] : array();

	    // get clipart on stroe
	    $design['artStore'] = array();
	    if (!empty($str_vectors)) {
	    	$stores = json_decode($str_vectors, true);
    		if (count($stores) > 0) {
    			foreach ($stores as $key => $view) {
    				if (count($view) > 0) {
    					foreach ($view as $item) {
    						if (isset($item['clipar_type']) && $item['clipar_type'] == 'store')
    							$design['artStore'][] = $item['clipart_id'];
    					}
    				}
    			}
    		}
	    }

	    if (isset($design['print']['elements'])) {
    		$design['elements'] = $design['print']['elements'];
    	}
    	
    	if (count($design) > 0) {
    		if (!isset($design['quantity'])) $design['quantity'] = 1;
    		$design['price_taxes'] = 0;
    		
    		$prices = @$dg->prices($design, false, true);
    		
    		//$item = str_replace($prices->price_thousand, '', $prices->item);
    		//$item = str_replace($prices->price_decimal, '.', $item);
    		$item = $prices->item;
            
            /*$price_base = 0;
            $file = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
            if (is_file($file) || file_exists($file)) {
                $contents = $this->openUrl($file);
                if ($contents !== false) {
                    $json = json_decode($contents, true);
                    if (isset($json['products']) && count($json['products']) > 0) {
                        foreach ($json['products'] as $product) {
                            if (isset($product['id']) && $product['id'] == $design['product_id'] && $product['price'] > 0)
                                $price_base = $product['price'];
                        }
                    }
                }
            }
            
            $price_of_print = (count($array) > 1) ? ($item - $price_base) : $item;*/
            $price_of_print = $item;
    	}

    	return $price_of_print;
    }

    public function getCartOption($cart_id = 0)
    {
    	if ($cart_id < 1) return array();

    	$query = $this->db->query('SELECT `option`, `session_id` FROM `'.DB_PREFIX.'cart` WHERE `cart_id` = '.(int)$cart_id);
    	if ($query->num_rows) {
    		$option = $query->row['option'];
    			return json_decode($option, true);
    	}

    	return array();
    }

    public function rtrimString($str, $str_search)
    {
        $length_str = strlen($str_search);
        if (strlen($str) >= $length_str && substr($str, -$length_str) == $str_search) {
            $str = substr($str, 0, -$length_str);
        }
        return $str;
    }

    public function getPrintingType($print_type)
    {
    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    	if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');

    	include_once ROOT.DS.'includes'.DS.'functions.php';
    	include_once ROOT.DS.DS.'includes'.DS.'addons.php';
    	$dg = new dg();
    	$addons = new addons();

    	$print1 = $dg->getPrintingType($print_type);
    	$print2 = array(
			'screen'		=> $addons->__('settings_print_screen'),
			'dtg'			=> $addons->__('settings_print_DTG'),
			'sublimation'	=> $addons->__('settings_print_sublimation'),
			'embroidery'	=> $addons->__('settings_print_embroidery'),
		);
		$print_types = array_merge((array)$print1, $print2);

		$string = '';
		foreach ($print_types as $key => $value) {
			if (strtolower($print_type) == strtolower($key)) {
				$string = $value;
				break;
			}
		}

    	return $string;
    }

    public function getPrice($data, $parent_id, $quantity)
    {
    	$price = 0;

    	if (!isset($data['rowid']) || $data['rowid'] == '') return $price;
    	if (!isset($data['refer']) || $data['refer'] != 'designer') return $price;

    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    	if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');

    	include_once ROOT.DS.'includes'.DS.'functions.php';
    	$dg = new dg();

    	$cache = $dg->cache('cart');
    	$rowid = $data['rowid'];
    	$design = $cache->get($rowid);

    	$params['colors'] = $data['colors'];
    	$params['print'] = $design['print'];
    	$params['cliparts'] = isset($design['cliparts']) ? $design['cliparts'] : array();
    	$params['attribute'] = isset($design['attributes']) ? $design['attributes'] : array();
    	$params['quantity'] = $quantity;
    	$params['product_id'] = $design['item']['product_id'];
    	$params['artStore'] = array();
    	if (isset($design['vector']) && !empty($design['vector'])) {
    		$stores = json_decode($design['vector'], true);
    		if (count($stores) > 0) {
    			foreach ($stores as $key => $view) {
    				if (count($view) > 0) {
    					foreach ($view as $item) {
    						if (isset($item['clipar_type']) && $item['clipar_type'] == 'store')
    							$params['artStore'][] = $item['clipart_id'];
    					}
    				}
    			}
    		}
    	}
    	$params['price_taxes'] = 0;

    	if (isset($design['print']['elements'])) {
    		$params['elements'] = $design['print']['elements'];
    	}

    	$prices = @$dg->prices($params, false, true);

    	//$item = str_replace($prices->price_thousand, '', $prices->item);
    	//$item = str_replace($prices->price_decimal, '.', $item);
    	$price = $prices->item;

    	return $price;
    }

    public function getOrderHistory($order_product_id, $parent_id)
    {
    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    	if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
    	include_once ROOT.DS.'includes'.DS.'functions.php';
    	$dg = new dg();

    	if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		if (substr($server, -1) != '/') {
			$server .= '/';
		}

		if ($server == null || $server == '/') {
        	if ($this->request->server['HTTPS']) {
        		$server = HTTPS_SERVER;
        	} else {
        		$server = HTTP_SERVER;
        	}
        }

    	$array = array();
    	$this->load->language('extension/module/tshirtecommerce');

    	$this->load->model('tshirtecommerce/seo_url');

    	$enable_download = $this->config->get('tshirtecommerce_downloadable');
    	if ($enable_download == null) $enable_download = false;

    	$json = array();
    	$query = $this->db->query('SELECT `options` FROM `'.DB_PREFIX.'tshirtdesign_order` WHERE `order_product_id`='.(int)$order_product_id);

    	if ($query->num_rows) {
    		$json = json_decode($query->row['options'], true);
    	}

    	if (isset($json['options']) && count($json['options']) > 0)
    		$json = $json['options'];

    	if (count($json) > 0) {
    		// for cart
    		if (isset($json['rowid']) && !empty($json['rowid']) && isset($json['refer']) && $json['refer'] == 'designer') {
    			// Set design color for options
	    		if (isset($json['colors']) && count($json['colors']) > 0) {
	    			$temp['name'] = $this->language->get('text_cart_color');
	    			$html = '';
	    			if (count($json['colors']) > 1) {
	    				$html .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;">';
						foreach ($json['colors'] as $h) {
							$html .= '<span style="width: 18px;height: 18px;float: left;display: inline-block;background-color: #'.$h.'; width: '.(100 / count($json['colors'])).'%"></span>';
						}
						$html .= '</span>'.$json['color_title'].'</label>';
	    			} else {
	    				$html .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;background-color:#'.$json['color_hex'].'"></span>'.$json['color_title'].'</label>';	
	    			}
	    			$temp['value'] = $html;
	    			$array[] = $temp;
	    		}

	    		// Render teams and numbers
		    	if (isset($json['teams']) && count($json['teams'])) {
		    		$temp['name'] = $this->language->get('text_cart_team_number');
		    		$html = '<table style="width:100%; border: 1px solid #ddd;float: left;"><thead><tr><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_name').'</th><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_number').'</th><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_size').'</th></tr></thead><tbody>';
		    		foreach ($json['teams']['name'] as $idx => $name) {
		    			$str_size = isset($json['teams']['size'][$idx]) ? $json['teams']['size'][$idx] : '';
		    			$size = '';
		    			if (!empty($str_size)) {
		    				$sizes = explode('::', $str_size);
		    				$size = isset($sizes[0]) ? $sizes[0] : '';
		    			}
		    			$number = isset($json['teams']['number'][$idx]) ? $json['teams']['number'][$idx] : '';
		    			$html .= '<tr><td style="border: 1px solid #ddd;">'.$name.'</td><td style="border: 1px solid #ddd;">'.$number.'</td><td style="border: 1px solid #ddd;">'.$size.'</td></tr>';
		    		}
		    		$html .= '</tbody></table>';
		    		$temp['value'] = $html;
	    			$array[] = $temp;
		    	}

    			// Set design attributes for options
    			if (isset($json['options']) && count($json['options'])) {
    				foreach ($json['options'] as $row) {
    					$temp['name'] = $row['name'];
    					switch ($row['type']) {
    						case 'selectbox':
    						case 'radio':
    						case 'printing':
    							$temp['value'] = $row['value'];
    							break;

    						case 'textlist':
    							if (count($row['value']) > 0) {
    								$temp['value'] = '';
    								foreach ($row['value'] as $k => $r) {
    									$temp['value'] .= $k.' - '.$r.'; ';
    								}
    							}
    							break;

    						case 'checkbox':
    							if (count($row['value']) > 0) {
    								$temp['value'] = '';
    								foreach ($row['value'] as $r) {
    									$temp['value'] .= $r.'; ';
    								}
    							}
    							break;
    						
    						default:
    							if (is_array($row['value'])) {
	                        		$temp['value'] = '';
	                        		foreach ($row['value'] as $kop => $opval) {
	                        			$stropval = explode(':', $opval);
	                        			if (count($stropval) > 1)
	                        				$temp['value'] .= $kop.' - '.$stropval[0].'; ';
	                        			else 
	                        				$temp['value'] .= $kop.' - '.$opval.'; ';
	                        		}
	                        	} else {
	                        		$temp['value'] = $row['value'];
	                        	}
    							break;
    					}
    					$array[] = $temp;
    				}
    			}

    			// Set design images to options
    			if (isset($json['images']) && !empty($json['images'])) {
    				$images = json_decode(str_replace('&quot;', '"', $json['images']), true);
    				if (count($images) > 0) {
    					$temp['name'] = $this->language->get('text_cart_images');
    					$temp['value'] = '<div style="width:100%;display:block;">';
    					foreach ($images as $key => $img) {
    						$image_link = $server.'tshirtecommerce/'.$img;
    						$temp['value'] .= '<div style="padding:5px;float:left;"><a target="_blank" href="'.$image_link.'"><img width="90" height="90" src="'.$image_link.'" class="img-thumbnail" alt="'.$key.'" title="'.$key.'" /></a>';
    						// Set link download if enabled
    						
    						$payment = $this->checkStorePayment($json['rowid'], 'cart');

    						if ($enable_download && $payment == true) {
    							$link_downlnoad = $server.'tshirtecommerce/design.php?key='.$json['rowid'].'&view='.$key;
	    						$temp['value'] .= '</br><center><a href="'.$link_downlnoad.'" target="_blank">'.$this->language->get('text_cart_download').'</a></center>';
	    					}
	    					$temp['value'] .= '</div>';
    					}
    					$temp['value'] .= '</div><div style="clear:both"></div>';
    					$array[] = $temp;
    				}
    			}

    			// set edit design link to option
		    	$cache = $dg->cache('cart');
		    	$design = $cache->get($json['rowid']);
		    	$link = $this->url->link('tshirtecommerce/designer', 'product_id='.$design['item']['product_id'].'&parent_id='.$parent_id.'&cart_id='.$json['rowid'], true);
		    	$slink = '';
		    	if ($this->config->get('config_seo_url') == 1 && $this->config->get('tshirtecommerce_seo_enable') == 1) {
			    	$slink = 'tshirtecommerce/designer&product_id='.$design['item']['product_id'].'&parent_id='.$parent_id.'&cart_id='.$json['rowid'];
			    	$slink = $this->model_tshirtecommerce_seo_url->getKeyword($slink);
			    }
		    	if (empty($slink)) {
		    		$slink = $link;
		    	} else {
		    		$slink = $server.$slink;
		    	}
    			$array[] = array(
    				'name' => $this->language->get('text_cart_view_design'),
    				'value' => '<a href="'.$slink.'" target="_blank">'.$this->language->get('text_cart_view_design_text').'</a>'
    			);
    		}

    		// for admin or design
    		if (!isset($json['rowid']) && isset($json['refer']) && $json['refer'] == 'product' && isset($json['design_product_id']) && !empty($json['design_product_id'])) {
    			$product_id = $json['design_product_id'];

    			$strs = explode(':', $json['design_product_id']);
    			if (count($strs) > 1) {
    				$product_id = $strs['2'];
    			}
    			
    			$product = array();

    			$file = ROOT.'/data/products.json';
				if (is_file($file) && file_exists($file)) {
					$json_products = json_decode($this->openUrl($file), true);
				}

				if ($json_products != false && isset($json_products['products']) && count($json_products['products']) > 0) {
					foreach ($json_products['products'] as $row) {
						if ($row['id'] == $product_id) {
							$product = $row;
							break;
						}
					}
				}

				$str_color = isset($strs['3']) ? $strs['3'] : '';
				if (isset($json['colors'])) {
					$str_color = implode(';', $json['colors']);
				}

				$str_color = $this->rtrimString($str_color, ';');

				$new_design_product_id = $json['design_product_id'];
				if (isset($strs['3']) && !empty($strs['3'])) {
					$new_design_product_id = str_replace($strs['3'], $str_color, $json['design_product_id']);
				}

				// Set color to options
				if (isset($product['design']) && isset($product['design']['color_hex']) && count($product['design']['color_hex']) > 0) {
					$temp['name'] = $this->language->get('text_cart_color');
					$temp['value'] = '';
					foreach ($product['design']['color_hex'] as $idx => $hex) {
						if (strtolower($hex) == strtolower($str_color)) {
							$arr = explode(';', $str_color);
							if (count($arr) > 1) {
								$temp['value'] .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;">';
								foreach ($arr as $h) {
									$temp['value'] .= '<span style="width: 18px;height: 18px;float: left;display: inline-block;background-color: #'.$h.'; width: '.(100 / count($arr)).'%"></span>';
								}
								$temp['value'] .= '</span>'.$product['design']['color_title'][$idx].'</label>';
							} else {
								$temp['value'] .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;background-color:#'.$hex.'"></span>'.$product['design']['color_title'][$idx].'</label>';
							}
							break;
						}
					}
					$array[] = $temp;
				}

				// set the printing to options
				if (isset($product['print_type']) && !empty($product['print_type'])) {
					$temp['name'] = $this->language->get('text_cart_print_type');
					$temp['value'] = $this->getPrintingType($product['print_type']);
					$array[] = $temp;
				}

				// Set attributes to options
				$attributes = isset($product['attributes']) ? $product['attributes'] : array();
				if (isset($json['attribute']) && count($json['attribute']) > 0 && count($attributes) > 0) {
					foreach ($json['attribute'] as $key => $value) {
						if (isset($attributes['type'][$key])) {
							$temp['name'] = $attributes['name'][$key];
							$temp['value'] = '';
							$type = isset($attributes['type'][$key]) ? $attributes['type'][$key] : '';
							if (!empty($type)) {
								switch ($type) {
									case 'selectbox':
									case 'radio':
										$temp['value'] = $attributes['titles'][$key][$value];
										break;

									case 'textlist':
										if (count($value) > 0) {
											foreach ($value as $k => $v) {
												if (empty($v) || $v == '') $v = 0;
												$temp['value'] .= $attributes['titles'][$key][$k].' - '.$v.'; ';
											}
										}
										break;

									case 'checkbox':
										if (count($value) > 0) {
											foreach ($value as $k => $v) {
												$temp['value'] .= $attributes['titles'][$key][$k].'; ';
											}
										}
										break;
									
									default:
										break;
								}
							}
							$array[] = $temp;
						}
					}
				}

				$payment = $this->checkStorePayment($new_design_product_id, 'xcart');

    			// Set download link to options if enabled
    			if ($enable_download && count($strs) > 1 && $payment == true) {
    				$temp['name'] = $this->language->get('text_cart_download');
    				
    				$temp['value'] = '';
    				if (isset($product['design']) && isset($product['design']['params'])) {
    					if ($this->validCampaign($parent_id) == 0) {
	    					if (isset($product['design']['params']['front']) && !empty($product['design']['params']['front'])) {
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?idea=1&key='.$new_design_product_id.'&view=front" target="_blank">'.$this->language->get('text_download_front').'</a>';
							}

							if (isset($product['design']['params']['back']) && !empty($product['design']['params']['back'])) {
								if (isset($product['design']['params']['front']) && !empty($product['design']['params']['front'])) $temp['value'] .= ' - ';
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?idea=1&key='.$new_design_product_id.'&view=back" target="_blank">'.$this->language->get('text_download_back').'</a>';
							}

							if (isset($product['design']['params']['left']) && !empty($product['design']['params']['left'])) {
								if (isset($product['design']['params']['back']) && !empty($product['design']['params']['back'])) $temp['value'] .= ' - ';
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?idea=1&key='.$new_design_product_id.'&view=left" target="_blank">'.$this->language->get('text_download_left').'</a>';
							}
							
							if (isset($product['design']['params']['right']) && !empty($product['design']['params']['right'])) {
								if (isset($product['design']['params']['left']) && !empty($product['design']['params']['left'])) $temp['value'] .= ' - ';
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?idea=1&key='.$new_design_product_id.'&view=right" target="_blank">'.$this->language->get('text_download_right').'</a>';
							}
						} else {
							if (isset($product['design']['params']['front']) && !empty($product['design']['params']['front'])) {
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?&key='.$strs[0].'&view=front" target="_blank">'.$this->language->get('text_download_front').'</a>';
							}

							if (isset($product['design']['params']['back']) && !empty($product['design']['params']['back'])) {
								if (isset($product['design']['params']['front']) && !empty($product['design']['params']['front'])) $temp['value'] .= ' - ';
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?key='.$strs[0].'&view=back" target="_blank">'.$this->language->get('text_download_back').'</a>';
							}

							if (isset($product['design']['params']['left']) && !empty($product['design']['params']['left'])) {
								if (isset($product['design']['params']['back']) && !empty($product['design']['params']['back'])) $temp['value'] .= ' - ';
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?key='.$strs[0].'&view=left" target="_blank">'.$this->language->get('text_download_left').'</a>';
							}
							
							if (isset($product['design']['params']['right']) && !empty($product['design']['params']['right'])) {
								if (isset($product['design']['params']['left']) && !empty($product['design']['params']['left'])) $temp['value'] .= ' - ';
								$temp['value'] .= '<a href="'.$server.'tshirtecommerce/design.php?key='.$strs[0].'&view=right" target="_blank">'.$this->language->get('text_download_right').'</a>';
							}
						}
					}
    				$array[] = $temp;
    			}

    			if (count($strs) > 1 && $this->validCampaign($parent_id) == 0) {
    				$link = $this->url->link('tshirtecommerce/designer', 'product_id='.$new_design_product_id.'&parent_id='.$parent_id, true);
    				$slink = '';
    				if ($this->config->get('config_seo_url') == 1 && $this->config->get('tshirtecommerce_seo_enable') == 1) {
	    				$slink = 'tshirtecommerce/designer&product_id='.$new_design_product_id.'&parent_id='.$parent_id;
				    	$slink = $this->model_tshirtecommerce_seo_url->getKeyword($slink);
				    }
			    	if (empty($slink)) {
			    		$slink = $link;
			    	} else {
			    		$slink = $server.$slink;
			    	}

	    			$array[] = array(
	    				'name' => $this->language->get('text_cart_view_design'),
	    				'type' => 'text',
	    				'value' => '<a href="'.$slink.'" target="_blank">'.$this->language->get('text_cart_view_design_text').'</a>'
	    			);
    			}
    		}
    	}

    	return $array;
    }

    protected function checkStorePayment($rowid, $type = 'cart')
    {
    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    	if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');

    	include_once ROOT.DS.'includes'.DS.'functions.php';
    	$dg = new dg();

    	$settings = $dg->getSetting();

    	if ($type != 'cart') {
    		$design_product_id = $rowid;
    		$array = explode(':', $design_product_id);
    		if (count($array) < 1) return true;
    		
    		$cache = $dg->cache('admin');
    		$design = $cache->get($array[0]);
    		if ($design == null || count($design) == 0) {
    			$cache = $dg->cache();
    			$design = $cache->get($array[0]);
    		}
    		if ($design == null || count($design) == 0)  return true;
    		
    		$str_vectors = '';
    		foreach ($design as $key => $row) {
    			if ($key == $array[1]) {
    				if (isset($row['vectors']) && $row['vectors'] != '') {
    					$str_vectors = $row['vectors'];
    				}
    			}
    		}
    		if (isset($str_vectors) && !empty($str_vectors)) {
    			$vectors = json_decode($str_vectors, true);
    			if (count($vectors) > 0) {
    				$need_pay = false;
			    	foreach ($vectors as $view) {
			    		if (count($view) > 0) {
			    			foreach ($view as $item) {
			    				if (isset($item['price']) && $item['price'] > 0 && isset($item['clipar_type']) && $item['clipar_type'] == 'store' && (!isset($item['clipar_paid']) || $item['clipar_paid'] != 1)) {
                                    if (empty($settings->store->api) || (isset($settings->store->api) && $settings->store->api == '') 
                                        || (isset($settings->store->verified) && $settings->store->verified ==0))
                                        return false;
			    					$need_pay = true;
			    				}
			    			}
			    		}
			    	}
			    	if ($need_pay == true)
			    		return false;
    			}
    		}

    		return true;
    	}

    	$cache = $dg->cache('cart');
    	$design = $cache->get($rowid);

    	if (!$design['vector'] || $design['vector'] == '') {
    		return true;
    	}

    	$vectors = json_decode($design['vector'], true);
    	if (count($vectors) < 1) return true;

		$need_pay = false;
    	foreach ($vectors as $view) {
    		if (count($view) > 0) {
    			foreach ($view as $item) {
    				if (isset($item['price']) && $item['price'] > 0 && $item['clipar_type'] == 'store' && (!isset($item['clipar_paid']) || $item['clipar_paid'] != 1)) {
                        if (empty($settings->store->api) || (isset($settings->store->api) && $settings->store->api == '') 
                            || (isset($settings->store->verified) && $settings->store->verified ==0))
                            return false;
    					$need_pay = true;
    				}
    			}
    		}
    	}
    	if ($need_pay == true)
    		return false;

    	return true;
    }

    public function extendOptions($data = array(), $array = array())
    {
        if ($this->request->server['HTTPS']) {
            $server = $this->config->get('config_ssl');
        } else {
            $server = $this->config->get('config_url');
        }
        if (substr($server, -1) != '/') {
            $server .= '/';
        }

        if ($server == null || $server == '/') {
        	if ($this->request->server['HTTPS']) {
        		$server = HTTPS_SERVER;
        	} else {
        		$server = HTTP_SERVER;
        	}
        }

        $option_data = $array;

        if (count($data) < 1) return $option_data;

        $this->load->language('extension/module/tshirtecommerce');

        $this->load->model('tshirtecommerce/seo_url');

        if (isset($data['options'])) {
            if (isset($data['options']['design_product_id'])) {
                if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
                if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');

                $design_product_id = $data['options']['design_product_id'];
                $str_design_product_id = explode(':', $data['options']['design_product_id']);
                if (count($str_design_product_id) > 1) {
                    $design_product_id = $str_design_product_id[2];
                }

                $product = array();

                $file = ROOT.'/data/products.json';
                if (is_file($file) && file_exists($file)) {
                    $json_products = json_decode($this->openUrl($file), true);
                }

                if ($json_products != false && isset($json_products['products']) && count($json_products['products']) > 0) {
                    foreach ($json_products['products'] as $row) {
                        if ($row['id'] == $design_product_id) {
                            $product = $row;
                            break;
                        }
                    }
                }

                $str_color = isset($str_design_product_id['3']) ? $str_design_product_id['3'] : '';
                if (isset($data['options']['colors'])) {
                    $str_color = implode(';', $data['options']['colors']);
                }

                $str_color = $this->rtrimString($str_color, ';');

                // Set color to options
                if (isset($product['design']) && isset($product['design']['color_hex']) && count($product['design']['color_hex']) > 0) {
                    $temp['name'] = $this->language->get('text_cart_color');
                    $temp['value'] = '';
                    foreach ($product['design']['color_hex'] as $idx => $hex) {
                        if (strtolower($hex) == strtolower($str_color)) {
                            $arr = explode(';', $str_color);
                            if (count($arr) > 1) {
                                $temp['value'] .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;">';
                                foreach ($arr as $h) {
                                    $temp['value'] .= '<span style="width: 18px;height: 18px;float: left;display: inline-block;background-color: #'.$h.'; width: '.(100 / count($arr)).'%"></span>';
                                }
                                $temp['value'] .= '</span>'.$product['design']['color_title'][$idx].'</label>';
                            } else {
                                $temp['value'] .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;background-color:#'.$hex.'"></span>'.$product['design']['color_title'][$idx].'</label>';
                            }
                            break;
                        }
                    }
                    $option_data[] = $temp;
                }

                // set the printing to options
                if (isset($product['print_type']) && !empty($product['print_type'])) {
                    $temp['name'] = $this->language->get('text_cart_print_type');
                    $temp['value'] = $this->getPrintingType($product['print_type']);
                    $option_data[] = $temp;
                }

                // Set attributes to options
                $attributes = isset($product['attributes']) ? $product['attributes'] : array();
                if (isset($data['options']['attribute']) && count($data['options']['attribute']) > 0 && count($attributes) > 0) {
                    foreach ($data['options']['attribute'] as $key => $value) {
                        if (isset($attributes['type'][$key])) {
                            $temp['name'] = $attributes['name'][$key];
                            $temp['value'] = '';
                            $type = isset($attributes['type'][$key]) ? $attributes['type'][$key] : '';
                            if (!empty($type)) {
                                switch ($type) {
                                    case 'selectbox':
                                    case 'radio':
                                        $temp['value'] = $attributes['titles'][$key][$value];
                                        break;

                                    case 'textlist':
                                        if (count($value) > 0) {
                                            foreach ($value as $k => $v) {
                                                if (empty($v) || $v == '') $v = 0;
                                                $temp['value'] .= '<span class="tshirtecommerce-textlist">'.$attributes['titles'][$key][$k].' - '.$v.'; '.'<span>';
                                            }
                                            $temp['value'] .= '
                                            	<script>
                                            		$(document).ready(function() {
                                            			$(\'.tshirtecommerce-textlist\').parent().parent().parent().find(\'button[type="submit"]\').css(\'display\', \'none\');
                                            		});
                                            	</script>';
                                        }
                                        break;

                                    case 'checkbox':
                                        if (count($value) > 0) {
                                            foreach ($value as $k => $v) {
                                                $temp['value'] .= $attributes['titles'][$key][$k].'; ';
                                            }
                                        }
                                        break;
                                    
                                    default:
                                        break;
                                }
                            }
                            $option_data[] = $temp;
                        }
                    }
                }
                
                if ($this->validCampaign($data['parent_id']) == 0) {
	                // add link edit design
	                if (count($str_design_product_id) > 1) {
	                	$param_colors = implode(';', $data['options']['colors']);
	                	$new_design_product_id = str_replace($str_design_product_id['3'], $param_colors, $data['options']['design_product_id']);
	                	$temp = array();
	                	$temp['name'] = $this->language->get('text_cart_link_edit_design');
	                	$slink = $this->url->link('tshirtecommerce/designer', 'design='.$new_design_product_id.'&parent_id='.$data['parent_id'], true);
	                	//if ($this->config->get('config_seo_url') == 1 && $this->config->get('tshirtecommerce_seo_enable') == 1) {
	                	//	$slink = $this->model_tshirtecommerce_seo_url->add($data['parent_id'] , $slink, 1);
	                	//}
	                	$temp['value'] = '<a href="'.$slink.'" target="_blank">'.$this->language->get('text_cart_view_design_text').'</a>';
	                	$option_data[] = $temp;
	                }
	            }

                return $option_data;
            }

            // for cart
            if (isset($data['options']['color_hex'])) {
                $colors = explode(';', $data['options']['color_hex']);
                $temp = array(
                    'name' => $this->language->get('text_cart_color'), 
                    'type' => 'text',
                    'value' => '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;">'
                );
                if (count($colors) > 1) {
                    foreach ($colors as $hex) {
                        $temp['value'] .= '<span style="width: 18px;height: 18px;float: left;display: inline-block;background-color: #'.$hex.'; width: '.(100 / count($colors)).'%"></span>';
                    }
                    $temp['value'] .= '</span>'.$data['options']['color_title'].'</label>';
                } else {
                    $temp['value'] = '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;background-color:#'.$data['options']['color_hex'].'"></span>'.$data['options']['color_title'].'</label>';
                }
                $option_data[] = $temp;
            }

            if (isset($data['options']['options']) && count($data['options']['options']) > 0) {
                foreach ($data['options']['options'] as $option) {
                    $temp = array();
                    $temp['value'] = '';
                    $temp['type'] = 'text';
                    $temp['name'] = $option['name'];
                    switch ($option['type']) {
                        case 'selectbox':
                        case 'radio':
                            $temp['value'] .= $option['value'];
                            break;

                        case 'printing':
                            $temp['name'] = $this->language->get('text_cart_print_type');
                            $temp['value'] .= $option['value'];
                            break;
                        
                        case 'textlist':
                            if (isset($option['value']) && count($option['value']) > 0) {
                                foreach ($option['value'] as $k => $val) {
                                    $temp['value'] .= '<span class="tshirtecommerce-textlist">'.$k.' : '.$val.'; '.'</span>';
                                }
                                $temp['value'] .= '
                                	<script>
                                		$(document).ready(function() {
                                			$(\'.tshirtecommerce-textlist\').parent().parent().parent().find(\'button[type="submit"]\').css(\'display\', \'none\');
                                		});
                                	</script>';
                            }
                            break;

                        case 'checkbox':
                            if (isset($option['value']) && count($option['value']) > 0) {
                                foreach ($option['value'] as $val) {
                                    $temp['value'] .= $val.'; ';
                                }
                            }
                            break;

                        default:
                        	if (is_array($option['value'])) {
                        		$temp['value'] = '';
                        		foreach ($option['value'] as $kop => $opval) {
                        			$stropval = explode(':', $opval);
                        			if (count($stropval) > 1)
                        				$temp['value'] .= $kop.' - '.$stropval[0].'; ';
                        			else 
                        				$temp['value'] .= $kop.' - '.$opval.'; ';
                        		}
                        	} else {
                        		$temp['value'] = $option['value'];
                        	}
                            break;
                    }
                    $option_data[] = $temp;
                }
            }

            if (isset($data['options']['teams']) && count($data['options']['teams']) > 0 && isset($data['options']['teams']['name']) && count($data['options']['teams']['name']) > 0) {
                $temp = array();
                $temp['name'] = $this->language->get('text_cart_team_number');
                $temp['value'] = '';
                $temp['type'] = 'text';
                $html = '<table style="width:100%; border: 1px solid #ddd;float: left;"><thead><tr><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_name').'</th><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_number').'</th><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_size').'</th></tr></thead><tbody>';
                foreach ($data['options']['teams']['name'] as $idx => $name) {
                    $str_size = isset($data['options']['teams']['size'][$idx]) ? $data['options']['teams']['size'][$idx] : '';
                    $size = '';
                    if (!empty($str_size)) {
                        $sizes = explode('::', $str_size);
                        $size = isset($sizes[0]) ? $sizes[0] : '';
                    }
                    $number = isset($data['options']['teams']['number'][$idx]) ? $data['options']['teams']['number'][$idx] : '';
                    $html .= '<tr><td style="border: 1px solid #ddd;">'.$name.'</td><td style="border: 1px solid #ddd;">'.$number.'</td><td style="border: 1px solid #ddd;">'.$size.'</td></tr>';
                }
                $html .= '</tbody></table>';
                $temp['value'] = $html;
                $option_data[] = $temp;
            }

            $new_design_product_id = isset($data['options']['rowid']) ? $data['options']['rowid'] : '';

            if (isset($data['options']['images']) && !empty($data['options']['images'])) {
                $images = json_decode(str_replace('&quot;', '"', $data['options']['images']), true);
                if (count($images) > 0) {
                    $temp = array();
                    $temp['name'] = $this->language->get('text_cart_images');
                    $temp['type'] = 'text';
                    $temp['value'] = '';
                    
                    $html = '<div class="tshirtecommerce-design-image-cart" style="padding-top:5px;width:100%;display:flex;">';
                    foreach ($images as $key => $image) {
                        $image_link = $server.'/tshirtecommerce/'.$image;
                        $html .= '<a style="float:left;" target="_blank" href="'.$image_link.'"><img width="90" height="90" src="'.$image_link.'" class="img-thumbnail" alt="'.$key.'" title="'.$key.'" /></a>';
                    }
                    $html .= '</div><div style="clear: both;"></div>';
                    $temp['value'] = $html;

                    $option_data[] = $temp;
                }
            }

            if ($this->validCampaign($data['parent_id']) == 0) {
	            if (!empty($new_design_product_id) && isset($data['options']) && isset($data['options']['t_product_id'])) {
		            $temp = array();
		        	$temp['name'] = $this->language->get('text_cart_link_edit_design');
		        	$slink = $this->url->link('tshirtecommerce/designer', 'product_id='.$data['options']['t_product_id'].'&parent_id='.$data['parent_id'].'&cart_id='.$new_design_product_id, true);
		        	//if ($this->config->get('config_seo_url') == 1 && $this->config->get('tshirtecommerce_seo_enable') == 1) {
		        	//	$slink = $this->model_tshirtecommerce_seo_url->add($data['parent_id'] , $slink, 1);
		        	//}
		        	$temp['value'] = '<a href="'.$slink.'" target="_blank">'.$this->language->get('text_cart_view_design_text').'</a>';
		        	$option_data[] = $temp;
		        }
		    }
        }

        return $option_data;
    }

    public function editCart($cart_id)
    {
    	$this->load->language('extension/module/tshirtecommerce');
    	$json = array(
    		'error' => 0,
    		'msg' => ''
    	);
    	$cart = $this->db->query("SELECT * FROM `".DB_PREFIX."cart` WHERE cart_id = '".(int)$cart_id."' AND api_id = '".(isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0)."' AND customer_id = '".(int)$this->customer->getId()."' AND session_id = '".$this->db->escape($this->session->getId())."'");

    	if ($cart->num_rows && isset($cart->row['option'])) {
    		$tshirtecommerce = json_decode($cart->row['option'], true);
    		if (isset($tshirtecommerce['tshirtecommerce'])) {
	    		$tshirtecommerce = $tshirtecommerce['tshirtecommerce'];

	    		if (isset($tshirtecommerce['options'])) {
	    			if (isset($tshirtecommerce['options']['options']) && count($tshirtecommerce['options']['options']) > 0) { // rowid
		    			foreach ($tshirtecommerce['options']['options'] as $index => $attribute) {
		    				if ($attribute['type'] == 'textlist') {
		    					$json['error'] = 1;
		    					$json['msg'] = sprintf($this->language->get('text_cart_update_textlist_warning'), $attribute['name']);
		    					break;
		    				}
		    			}
		    		} elseif (isset($tshirtecommerce['options']) && isset($tshirtecommerce['options']['design_product_id'])) { // design_product_id
		    			$design_product_id = $tshirtecommerce['options']['design_product_id'];
		    			$array = explode(':', $design_product_id);
		    			if (count($array) > 1) {
		    				$design_product_id = $array[2];
		    			}

		    			$xproduct = array();
		    			$product_json = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
		    			if (file_exists($product_json)) {
		    				$products = json_decode($this->openUrl($product_json), true);
		    				if (isset($products['products']) && count($products['products']) > 0) {
		    					foreach ($products['products'] as $product) {
		    						if ($product['id'] == $design_product_id) {
		    							$xproduct = $product;
		    							break;
		    						}
		    					}
		    				}
		    			}
		    			
		    			if (count($xproduct) > 0 && isset($xproduct['attributes']) && isset($xproduct['attributes']['type']) && count($xproduct['attributes']['type']) > 0 ) {
		    				foreach ($xproduct['attributes']['type'] as $idx => $type) {
		    					if ($type == 'textlist') {
		    						$json['error'] = 1;
		    						$json['msg'] = sprintf($this->language->get('text_cart_update_textlist_warning'), $attribute['name']);
		    						break;
		    					}
		    				}
		    			}
		    		}
	    		}
	    	}
    	}
    	
    	return $json;
    }

    public function validCampaign($product_id)
	{
		// Check install campaign
		$query = $this->db->query('DESCRIBE `'.DB_PREFIX.'product`');
		$is_campaign = 0;
		foreach ($query->rows as $column) {
			if ($column['Field'] == 'author') {
				$is_campaign = 1;
			}
		}

		if ($is_campaign == 0) return 0;

		$query = $this->db->query('SELECT `author` FROM `'.DB_PREFIX.'product` WHERE `product_id` = '.(int)$product_id);
		if ($query->num_rows) {
			if ($query->row['author'] > 0) return 1;
		}

		return 0;
	}
	
	public function getProductCategories()
	{
		$pr_categories = $this->db->query("
			SELECT *
			FROM `".DB_PREFIX."product_to_category`
		");
		
		$categories = array();
		if ($pr_categories->num_rows) {
			$categories = $pr_categories->rows;
		}
			
		return $categories;
	}
	
	public function getParentCategory($categories, $parent_id, $results = array())
	{
		foreach($categories as $category)
		{
			if($category['category_id'] == $parent_id)
			{
				if($category['parent_id'] == 0)
					$results = $category;
				
				if($category['parent_id'] > 0)
				{
					$results = $this->getParentCategory($categories, $category['parent_id'], $results);
				}
			}
		}
		
		return $results;
	}
}