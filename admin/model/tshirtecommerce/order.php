<?php
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		Aug 31, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ModelTshirtecommerceOrder extends Model
{
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

	public function getItem($order_product_id, $product_id = 0)
	{	
		$item = $this->db->query("
			SELECT * 
			FROM `".DB_PREFIX."tshirtdesign_order` 
			WHERE order_product_id = '".(int)$order_product_id."' ORDER BY `id` DESC LIMIT 1
		");
		$design = '';
		if ($item->num_rows) {
			$string = $item->row['options'];
			if (!empty($string)) $design = json_decode($string);
		}
			
		if (empty($design->rowid)) {
			if ($product_id == 0) return false;
			
			$product = $this->db->query("
				SELECT `design_product_id` 
				FROM `".DB_PREFIX."product` 
				WHERE `product_id` = '".(int)$product_id."'
			");
			if ($product->num_rows) {
				$obj = explode(':', $product->row['design_product_id']);
				if (count($obj) == 1) {
					return false;
				} else {
					$obj['idea'] = true;
					if (isset($design->price)) $obj['price'] = $design->price;
				}
				$design = $obj;
			} else {
				return false;
			}
		}		
		return $design;
	}
	
	// get all product design
	public function getProducts()
	{
		$products = array();
		$items = $this->db->query("
			SELECT p.*, pd.`name` 
			FROM `".DB_PREFIX."product` p 
			LEFT JOIN `".DB_PREFIX."product_description` pd ON p.`product_id` = pd.`product_id`
			WHERE p.`status` = 1 AND p.`design_product_id` <> '' AND pd.`language_id` = ".(int)$this->config->get('config_language_id')."
		");
		if ($items->num_rows) {
			foreach ($items->rows as $item) {
				$design_product_id = $item['design_product_id'];
				$n = explode(':', $design_product_id);
				if (count($n) > 0) $products[] = $item;
			}
		}
		return $products;
	}

	public function store_art_update($design_id, $params, $art_prices = array(), $api = '')
	{
		$array = explode(';', $params);
		$arts = array();

		for ($i = 0; $i < count($array); $i++) {
			$art = explode(':', $array[$i]);
			if (count($art) > 1) $arts[$art[0]] = $art[1];
		}
		if (count($arts)) {
			if (defined('DS') == false) define('DS', DIRECTORY_SEPARATOR);
			if (defined('ROOT') == false) define('ROOT', dirname(DIR_SYSTEM).DIRECTORY_SEPARATOR.'tshirtecommerce');
			
			include_once (ROOT.DS.'includes'.DS.'functions.php');
			$dg = new dg();
			
			// update sales
			$file = ROOT.DS.'data'.DS.'store'.DS.'arts_info.json';
			if (file_exists($file)) {
				// call cache
				$cache = $dg->cache('store');
				$sales = ($sales != null) ? $cache->get('sales') : array();
				$time = time();
				$month = date('Y_m', $time);
				$day = date('d', $time);
				
				if (empty($sales[$month])) $sales[$month] = array();
				if (empty($sales[$month][$day])) $sales[$month][$day] = array();
			
				$rows = json_decode(@file_get_contents($file), true);
				foreach ($arts as $clipar_id => $value) {
					if (isset($art_prices[$clipar_id])) continue;
					if (isset($rows[$clipar_id])) {
						if (isset($rows[$clipar_id]['sales'])) {
							$rows[$clipar_id]['sales'] = $rows[$clipar_id]['sales'] + 1;
						} else {
							$rows[$clipar_id]['sales']	= 1;
						}
						
						if (isset($sales[$month][$day][$clipar_id])) {
							$sales[$month][$day][$clipar_id] = $sales[$month][$day][$clipar_id] + 1;
						} else {
							$sales[$month][$day][$clipar_id] = 1;
						}
					}
				}
				$dg->WriteFile($file, json_encode($rows));
				$cache->set('sales', $sales, 933120000);
			}
			
			$cache = $dg->cache('cart');
			$design = $cache->get($design_id);
			
			if (isset($design['vector'])) {
				$vectors = json_decode($design['vector'], true);
				if (count($vectors)) {
					foreach ($vectors as $view => $items) {
						if (count($items)) {
							foreach ($items as $id => $item) {
								if (isset($item['clipar_type']) && empty($item['clipar_paid'])) {
									if (isset($art_prices[$item['clipart_id']])) {
										$items[$id]['price'] = $art_prices[$item['clipart_id']];
										continue;
									}
									if (isset($arts[$item['clipart_id']])) {
										$items[$id]['clipar_paid'] = 1;
										if((isset($item['file']) && is_string($item['file']) && $item['file'] == 'svg') || (isset($item['file']['type']) && $item['file']['type'] == 'svg')) {
											$svg = $this->store_str_svg($item['svg'], $arts[ $item['clipart_id'] ]);
										} else {
											$key_active = str_replace(' ', '+', $arts[ $item['clipart_id'] ]);
											$svg = $item['svg'];
											$key = md5($key_active);
											
											$url = 'http://api.9file.net/api/orderPNG/id/'.$item['clipart_id'].'/key/'.$key.'/api_key/'.$api;
											$result = $this->openURL($url);
											if ($result != false) {
												$data = json_decode($result, true);
												if (isset($data['content'])) {
													$png = $this->encrypt_compress($key_active, base64_decode($data['content']));
													$img = 'data:image/png;base64,'.base64_encode($png);
													$temp1 = explode('xlink:href="', $item['svg']);
													if (count($temp1) > 1) {
														$temp2 = explode('">', $temp1[1]);
														if (count($temp2) > 1) $svg = $temp1[0].'xlink:href="'.$img.'">'.$temp2[1];
													}
												}
											}
										}
										$items[$id]['svg'] = $svg;
									}
								}
							}
							$vectors[$view]	= $items;
						}
					}
				}
				$design['vector'] = json_encode($vectors);
				$cache->set($design_id, $design);
			}
		}
	}

	protected function fnStrtolower($str)
	{
		if (is_array($str)) {
	            return false;
	        }
	        if (function_exists('mb_strtolower')) {
	            return mb_strtolower($str, 'utf-8');
	        }
	        return strtolower($str);
	}

	protected function openURL($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
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
	            if (isset($opts['http']['method']) && $this->fnStrtolower($opts['http']['method']) == 'post') {
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

	protected function store_str_svg($svg, $key)
	{
		$key = str_replace(' ', '+', $key);
		if ($svg == '') return '';
		
		$params = explode('/', $svg);
		$n = count($params);
		
		$str = '';
		for ($i = 0; $i < $n; $i++) {
			$number = $params[$i];
			$s = substr($key, $number, 1);
			$str .= $s;
		}
		
		$output = @base64_decode($str);
		return $output;
	}

	protected function encrypt_compress($key, $str)
	{
		$s = array();
		for ($i = 0; $i < 256; $i++) {
			$s[$i] = $i;
		}
		$j = 0;
		for ($i = 0; $i < 256; $i++) {
			$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
		}
		$i = 0;
		$j = 0;
		$res = '';
		$count = strlen($str);
		for ($y = 0; $y < $count; $y++) {
			$i = ($i + 1) % 256;
			$j = ($j + $s[$i]) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
			$res .= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
		}
		return $res;
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
		if (count($print_types) > 0) {
			foreach ($print_types as $key => $value) {
				if (strtolower($print_type) == strtolower($key)) {
					$string = $value;
					break;
				}
			}
		}

    	return $string;
    }

	public function getOrderHistory($order_product_id, $parent_id)
    {
    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
    	if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
    	include_once ROOT.DS.'includes'.DS.'functions.php';
    	$dg = new dg();

    	if ($this->request->server['HTTPS']) {
			$server = HTTPS_CATALOG;
		} else {
			$server = HTTP_CATALOG;
		}
		if (substr($server, -1) != '/') {
			$server .= '/';
		}

    	$array = array();
    	$this->load->language('extension/module/tshirtecommerce');

    	$this->load->model('tshirtecommerce/seo_url');

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
	    			$temp['type'] = 'text';
	    			$array[] = $temp;
	    		}

	    		// Render teams and numbers
		    	if (isset($json['teams']) && count($json['teams'])) {
		    		$temp['name'] = $this->language->get('text_cart_team_number');
		    		$html = '<br/><table style="width:100%; border: 1px solid #ddd;float: left;"><thead><tr><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_name').'</th><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_number').'</th><th style="border: 1px solid #ddd;">'.$this->language->get('text_cart_team_number_size').'</th></tr></thead><tbody>';
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
	    			$temp['type'] = 'text';
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
    							$temp['type'] = ($row['type'] == 'printing') ? 'text' : $row['type'];
    							break;

    						case 'textlist':
    							if (count($row['value']) > 0) {
    								$temp['value'] = '';
    								$temp['type'] = 'text';
    								foreach ($row['value'] as $k => $r) {
    									$temp['value'] .= $k.' - '.$r.'; ';
    								}
    							}
    							break;

    						case 'checkbox':
    							if (count($row['value']) > 0) {
    								$temp['value'] = '';
    								$temp['type'] = 'checkbox';
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
    						
    							$link_downlnoad = $server.'tshirtecommerce/design.php?key='.$json['rowid'].'&view='.$key;
	    						$temp['value'] .= '</br><center><a href="'.$link_downlnoad.'" target="_blank">'.$this->language->get('text_cart_download').'</a></center>';
	    					
	    					$temp['value'] .= '</div>';
    					}
    					$temp['type'] = 'text';
    					$temp['value'] .= '</div><div style="clear:both"></div>';
    					$array[] = $temp;
    				}
    			}

    			// set edit design link to option
		    	$cache = $dg->cache('cart');
		    	$design = $cache->get($json['rowid']);

		    	$link = $server.'index.php?route=tshirtecommerce/designer&product_id='.$design['item']['product_id'].'&parent_id='.$parent_id.'&cart_id='.$json['rowid'];
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
    				'type' => 'text',
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
									$temp['value'] .= '<span style="width: 18px;height: 18px;float: left;display: inline-block;background-color: #'.$h.'; width: '.(100 / count($jarr)).'%"></span>';
								}
								$temp['value'] .= '</span>'.$product['design']['color_title'][$idx].'</label>';
							} else {
								$temp['value'] .= '<label style="line-height: 20px;"><span style="float: left;display: inline-block;width: 20px;height: 20px;overflow: hidden;border: 1px solid #d4d4d4;margin-right: 5px;background-color:#'.$hex.'"></span>'.$product['design']['color_title'][$idx].'</label>';
							}
							break;
						}
					}
					$temp['type'] = 'text';
					$array[] = $temp;
				}

				// set the printing to options
				if (isset($product['print_type']) && !empty($product['print_type'])) {
					$temp['name'] = $this->language->get('text_cart_print_type');
					$temp['value'] = $this->getPrintingType($product['print_type']);
					$temp['type'] = 'text';
					$array[] = $temp;
				}
				//print_r($product);
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
										$temp['type'] = $type;
										break;

									case 'textlist':
										if (count($value) > 0) {
											$temp['type'] = 'textlist';
											foreach ($value as $k => $v) {
												if (empty($v) || $v == '') $v = 0;
												$temp['value'] .= $attributes['titles'][$key][$k].' - '.$v.'; ';
											}
										}
										break;

									case 'checkbox':
										$temp['type'] = 'checkbox';
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

    			// Set download link to options if enabled
    			if (count($strs) > 1) {
    				$temp['name'] = $this->language->get('text_cart_download');
    				$temp['type'] = 'text';
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
    				$link = $server.'index.php?route=tshirtecommerce/designer&product_id='.$new_design_product_id.'&parent_id='.$parent_id;
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