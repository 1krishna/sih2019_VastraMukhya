<?php 
// @since version 4.2.0

class ModelTshirtecommerceSproduct extends Model
{
	public function getSetting()
	{
		$setting = array();

		$setting['tshirtecommerce_allow_taxes'] = $this->config->get('tshirtecommerce_allow_taxes');
		$setting['tshirtecommerce_hide_addtocart'] = $this->config->get('tshirtecommerce_hide_addtocart');
		$setting['tshirtecommerce_customize_design_btn'] = $this->config->get('tshirtecommerce_customize_design_btn');
		//$setting['tshirtecommerce_customize_design_text'] = $this->config->get('tshirtecommerce_customize_design_text')[$this-];

		return $setting;
	}

	public function getInfo($parent_id = 0, $is_category = false)
	{
		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).'/tshirtecommerce');
		include_once ROOT.'/includes/functions.php';
		$dg = new dg();

		$this->load->language('extension/module/tshirtecommerce');
		$this->load->model('tshirtecommerce/order');
		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($parent_id);

		$infos = array();
		$min_order = 1;
		$design_product_id = '';
		$query = $this->db->query('SELECT `design_product_id`, `minimum` FROM `'.DB_PREFIX.'product` WHERE `product_id` = '.(int)$parent_id);
		if ($query->num_rows) {
			$design_product_id = $query->row['design_product_id'];
			$min_order = $query->row['minimum'];
		}

		$product_id = $design_product_id;

		$link = $this->url->link('tshirtecommerce/designer', 'parent_id='.$parent_id.'&product_id='.$product_id, true);

		if (!empty($design_product_id))
			$infos['design_product_id'] = $design_product_id;

		$test = explode(':', $design_product_id);
		$style = $this->config->get('tshirtecommerce_customize_design_btn');

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}
		$infos['design_custom_button'] = '';
		if ($is_category === false) {
			$infos['design_custom_button'] = '<link rel="stylesheet" href="'.$server.'/catalog/view/javascript/tshirtecommerce.css?v='.time().'">';
		}

		if (count($test) > 1) {
			$product_id = $test[2];

			if ($this->model_tshirtecommerce_order->checkProductDesign($parent_id, $product_id) == true) {
				$infos['design_custom_button'] .= '<div class="form-group tshirtecommerce-btn-group"><a id="button-design" style="'.$style.'" class="btn-design" href="'.$link.'">'.$this->language->get('tshirt_text_button_custom').'</a></div>';
			}

			if ($is_category === false) {
				// get price of print for design templates
				//$price_of_print = $this->model_tshirtecommerce_order->getPriceOfPrint($design_product_id);
				$price_of_print = $this->getPriceOfPrint($design_product_id);
				
				if ($price_of_print != 0) {
					if ($product_info && isset($product_info['tax_class_id'])) {
						if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
							$price_of_print = $this->tax->calculate($price_of_print, $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false);
						}
					}
					$infos['price_of_print'] = sprintf('%s %s', $this->language->get('tshirt_text_price_of_print'), $this->currency->format($price_of_print, $this->session->data['currency']));
				}
			}
		} else {
			if ($this->model_tshirtecommerce_order->checkProductDesign($parent_id, $product_id) == true) {
				$infos['design_custom_button'] .= '<div class="form-group tshirtecommerce-btn-group"><a id="button-design" style="'.$style.'" class="btn-design" href="'.$link.'">'.$this->language->get('tshirt_text_button_custom').'</a></div>';
			}
		}

		if (!empty($design_product_id) && $is_category === false) {
			$product = array();
			$json = false;
			$file = ROOT.'/data/products.json';
			if (is_file($file) && file_exists($file)) {
				$json = json_decode($this->model_tshirtecommerce_order->openUrl($file), true);
			}

			if ($json != false && isset($json['products']) && count($json['products']) > 0) {
				foreach ($json['products'] as $row) {
					if ($row['id'] == $product_id) {
						$product = $row;
						break;
					}
				}
			}
			if (count($product) > 0) {
				$hide_add2cart = $this->config->get('tshirtecommerce_hide_addtocart');
				if ($hide_add2cart == null) $hide_add2cart = 0;
				
				$infos['hide_add2cart'] = 0;
				if ($hide_add2cart > 0)
				{
					if ($hide_add2cart == 1 && count($test) < 2)
					{
						$infos['hide_add2cart'] = 1;
					}elseif($hide_add2cart == 2)
					{
						$infos['hide_add2cart'] = 1;
					}
				}
				$infos['product_id'] = $product_id;
				$infos['print_type'] = $product['print_type'];
				$infos['design'] = $product['design'];
				$infos['sizes'] = array();
				$infos['product_attributes'] = $product['attributes'];
				if (isset($product['attributes']) && $infos['hide_add2cart'] == 0) {
					$infos['attributes'] = $this->renderAttributes(($product_info && isset($product_info['tax_class_id'])) ? $product_info['tax_class_id'] : false,$product['design'], $product['attributes'], $product['print_type'], $infos['sizes'], $min_order, $design_product_id);
					$infos['sizes'] = array_unique($infos['sizes']);
				}
				$infos['show_attribute'] = isset($product['show_attribute']) ? $product['show_attribute'] : 0;

				// For product campaign
				$query = $this->db->query('DESCRIBE `'.DB_PREFIX.'product`');
				$have_author = false;
				if ($query->num_rows) {
	                foreach ($query->rows as $row) {
	                    if ($row['Field'] == 'author') {
	                        $have_author = true;
	                        break;
	                    }
	                }
	            }
	            if ($have_author == true) {
					$query = $this->db->query('SELECT `author` FROM `'.DB_PREFIX.'product` WHERE `product_id` = '.(int)$parent_id);
					$author = 0;
					if ($query->num_rows) $author = $query->row['author'];
					if ($author > 0) {
						$infos['is_campaign'] = 1;
					}
				}
			}
		}

		return $infos;
	}

	public function renderAttributes($tax_class_id = false, $design = array(), $attributes = array(), $print_type = 'screen', &$sizes = array(), $min_order = 1, $design_product_id = '')
	{
		$html = '';
		
		$this->load->language('extension/module/tshirtecommerce');

		if (count($design) > 0 && isset($design['color_hex']) && count($design['color_hex']) > 0) {
			$html .= '<input type="hidden" name="tshirtecommerce[refer]" value="product" />';
			$html .= '<input type="hidden" name="tshirtecommerce[design_product_id]" value="'.$design_product_id.'" />';
			$html .= '<style>
						.tshirtecommerce-colors-radio { 
							height:30px; width: 30px; 
							vertical-align: middle; 
							margin: 0 5px 0 0; 
							float:left; 
							border: 1px solid #d4d4d4;
							overflow: hidden;
						}
						.tshirtecommerce-colors-radio > span {
							float: left;
							height: 28px; 
						}
						input[type="radio"]:checked + .tshirtecommerce-colors-radio {
							border: 1px solid #3f51b5;
							-webkit-border-radius: 50%; -moz-border-radius: 50%; border-radius: 50%;
						}
					</style>';
			$html .= '<div class="form-group" style="width: 100%;">';
			$html .= '<label class="control-label">'.$this->language->get('text_colors').'</label><span id="tshirtecommerce_color_bonus"></span><br/>';
			foreach ($design['color_hex'] as $key => $hex) {
				$bonus = '';
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					if (isset($design['price'][$key]) && !empty($design['price'][$key]) && $design['price'][$key] != 0) {
						$is_negative = ($design['price'][$key] < 0) ? true : false;
						if ($tax_class_id != false) {
							$design['price'][$key] = $this->tax->calculate(abs($design['price'][$key]), $tax_class_id, $this->config->get('config_tax') ? 'P' : false);
						}
						$bonus .= ' (';
						if ($is_negative === false) {
							$bonus .= '+'.$this->currency->format($design['price'][$key], $this->session->data['currency']);
						} else {
							$bonus .= '-'.$this->currency->format(abs($design['price'][$key]), $this->session->data['currency']);
						}
						$bonus .= ')';
					}
				}
				$html .= '<div class="radio" onclick="fnTshirtecommerceColorChange(\''.$bonus.'\')" title="'.(isset($design['color_title'][$key]) ? $design['color_title'][$key] : '').$bonus.'" style="display: inline-block;"><label style="line-height:30px">';
				if ($key == 0) 
					$html .= '<input style="display:none" type="radio" name="tshirtecommerce[colors]" value="'.$hex.'" checked />';
				else 
					$html .= '<input style="display:none" type="radio" name="tshirtecommerce[colors]" value="'.$hex.'" />';
				$check_hex = explode(';', $hex);
				if (count($check_hex) > 1) {
					$html .= '<span class="tshirtecommerce-colors-radio">';
					foreach ($check_hex as $h) {
						$html .= '<span style="width: '.(100 / count($check_hex)).'%; background-color:#'.$h.';"></span>';
					}
					$html .= '</span>';
				} else {
					$html .= '<span class="tshirtecommerce-colors-radio" style="background-color:#'.$hex.'"></span>';
				}
				//$html .= (isset($design['color_title'][$key]) ? $design['color_title'][$key] : '').$bonus;
				$html .= '</label></div>';
			}
			$html .= '</div>';
			$html .= '<script>function fnTshirtecommerceColorChange(str) { document.getElementById("tshirtecommerce_color_bonus").innerHTML = str; }</script>';
		}

		if (count($attributes) < 1) return $html;

		if (isset($attributes['type']) && count($attributes['type']) > 0) {
			$html .= '<input type="hidden" name="tshirtecommerce[type]" value="user" />';
			foreach ($attributes['type'] as $key => $type) {
				$html .= '<div class="form-group" style="width: 100%;">';
				$html .= '<label class="control-label">'.$attributes['name'][$key].'</label>';
				switch ($type) {
					case 'selectbox':
						if (count($attributes['titles'][$key])) {
							$html .= '<select name="tshirtecommerce[attribute]['.$key.']" class="form-control">';
							//$html .= 	'<option value="-1"> '.$this->language->get('text_select').' </option>';
							foreach ($attributes['titles'][$key] as $idx => $title) {
								$bonus = '';
								if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
									if (!empty($attributes['prices'][$key][$idx]) && $attributes['prices'][$key][$idx] != 0) {
										$is_negative = ($attributes['prices'][$key][$idx] < 0) ? true : false;
										if ($tax_class_id != false) {
											$attributes['prices'][$key][$idx] = $this->tax->calculate(abs($attributes['prices'][$key][$idx]), $tax_class_id, $this->config->get('config_tax') ? 'P' : false);
										}
										$bonus .= ' (';
										if ($is_negative === false)
											$bonus .= '+'.$this->currency->format($attributes['prices'][$key][$idx], $this->session->data['currency']);
										else
											$bonus .= '-'.$this->currency->format(abs($attributes['prices'][$key][$idx]), $this->session->data['currency']);
										$bonus .= ')';
									}
								}
								$html .= '<option value="'.$idx.'">'.$title.$bonus.'</option>';
							}
							$html .= '</select>';
						}
						break;

					case 'textlist':
						if (count($attributes['titles'][$key])) {
							$html .= '<style>
										#input-quantity, label[for="input-quantity"],input[name="quantity"],.qty { display:none !important; }
									</style>
									<script>
										function tshirtecommerceChangeQty() {
											var tshirtecommerceQty = 0;
											$("input.tshirtecommerce-qty").each(function() {
												tshirtecommerceQty += parseInt(this.value);
											});
											$("#input-quantity").val(tshirtecommerceQty);
										}
									</script>';
							$html .= '<ul id="design-input'.$key.'" style="list-style: none outside none;float:left;padding:0;width:100%;">';
							foreach ($attributes['titles'][$key] as $idx => $title) {
								$sizes[] = $key;
								$bonus = '';
								if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
									if (!empty($attributes['prices'][$key][$idx]) && $attributes['prices'][$key][$idx] != 0) {
										$is_negative = ($attributes['prices'][$key][$idx] < 0) ? true : false;
										if ($tax_class_id != false) {
											$attributes['prices'][$key][$idx] = $this->tax->calculate(abs($attributes['prices'][$key][$idx]), $tax_class_id, $this->config->get('config_tax') ? 'P' : false);
										}
										$bonus .= ' (';
										if ($is_negative == false)
											$bonus .= '+'.$this->currency->format($attributes['prices'][$key][$idx], $this->session->data['currency']);
										else
											$bonus .= '-'.$this->currency->format(abs($attributes['prices'][$key][$idx]), $this->session->data['currency']);
										$bonus .= ')';
									}
								}
								$html .= '<li style="float:left;text-align:center;margin:2px 0;">';
								$html .= '<label>'.$title.$bonus.'</label><br/>';
								if ($idx == 0) {
									$html .= '<input style="width:50px;height: 26px;text-align: center;" class="tshirtecommerce-qty form-control" type="text" name="tshirtecommerce[attribute]['.$key.']['.$idx.']" value="'.$min_order.'" onchange="tshirtecommerceChangeQty()" />';
								} else {
									$html .= '<input style="width:50px;height: 26px;text-align: center;" class="tshirtecommerce-qty form-control" type="text" name="tshirtecommerce[attribute]['.$key.']['.$idx.']" value="0" onchange="tshirtecommerceChangeQty()" />';
								}
								$html .= '</li>';
							}
							$html .= '</ul>';
						}
						break;

					case 'checkbox':
						if (count($attributes['titles'][$key])) {
							$html .= '<div id="design-input'.$key.'">';
							foreach ($attributes['titles'][$key] as $idx => $title) {
								$html .= '<div class="checkbox"><label>';
								$html .= '<input type="checkbox" name="tshirtecommerce[attribute]['.$key.'][]" value="'.$idx.'">';
								$bonus = '';
								if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
									if (!empty($attributes['prices'][$key][$idx]) && $attributes['prices'][$key][$idx] != 0) {
										$is_negative = ($attributes['prices'][$key][$idx] < 0) ? true : false;
										if ($tax_class_id != false) {
											$attributes['prices'][$key][$idx] = $this->tax->calculate(abs($attributes['prices'][$key][$idx]), $tax_class_id, $this->config->get('config_tax') ? 'P' : false);
										}
										$bonus .= ' (';
										if ($is_negative === false)
											$bonus .= '+'.$this->currency->format($attributes['prices'][$key][$idx], $this->session->data['currency']);
										else
											$bonus .= '-'.$this->currency->format(abs($attributes['prices'][$key][$idx]), $this->session->data['currency']);
										$bonus .= ')';
									}
								}
								$html .= $title.$bonus;
								$html .= '</label></div>';
							}
							$html .= '</div>';
						}
						break;

					case 'radio':
						if (count($attributes['titles'][$key])) {
							$html .= '<div id="design-input'.$key.'">';
							foreach ($attributes['titles'][$key] as $idx => $title) {
								$html .= '<div class="radio"><label>';
								$html .= '<input type="radio" name="tshirtecommerce[attribute]['.$key.']" value="'.$idx.'">';
								$bonus = '';
								if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
									if (!empty($attributes['prices'][$key][$idx]) && $attributes['prices'][$key][$idx] != 0) {
										$is_negative = ($attributes['prices'][$key][$idx] < 0) ? true : false;
										if ($tax_class_id != false) {
											$attributes['prices'][$key][$idx] = $this->tax->calculate(abs($attributes['prices'][$key][$idx]), $tax_class_id, $this->config->get('config_tax') ? 'P' : false);
										}
										$bonus .= ' (';
										if ($is_negative === false)
											$bonus .= '+'.$this->currency->format($attributes['prices'][$key][$idx], $this->session->data['currency']);
										else
											$bonus .= '-'.$this->currency->format(abs($attributes['prices'][$key][$idx]), $this->session->data['currency']);
										$bonus .= ')';
									}
								}
								$html .= $title.$bonus;
								$html .= '</label></div>';
							}
							$html .= '</div>';
						}
						break;
					
					default:
						break;
				}
				$html .= '</div>';
				$html .= '<div style="clear:both;"></div>';
			}
		}

		return $html;
	}

	public function getQtyAndPrice($tshirtecommerces, $parent_id, $quantity, $cart = false)
	{
		$array = array(
			'price_of_print' => 0,
			'quantity' => $quantity,
			'type' => 'cart'
		);
		if (count($tshirtecommerces) < 1) {
			return $array;
		}

		if (isset($tshirtecommerces['attribute']) && count($tshirtecommerces['attribute']) > 0) {
			foreach ($tshirtecommerces['attribute'] as $key => $row) {
				if (!is_array($row) && $row < 0) 
					unset($tshirtecommerces['attribute'][$key]);
			}
		}
		
		$this->load->model('tshirtecommerce/order');

		if (isset($tshirtecommerces['type'])) {
			$array['type'] = $tshirtecommerces['type'];
		}

		if (empty($array['type']) || $array['type'] == '') {
			return $array;
		}

		if ($array['type'] == 'cart') {
			$array['price_of_print'] = $this->model_tshirtecommerce_order->getPrice($tshirtecommerces, $parent_id, $quantity);
			// re-update quantity
			// TODO
			return $array;
		}

		/// for $type = { admin. design }
		/// -------------------------------
		$info = $this->getInfo($parent_id);

		// get qty
		$array['quantity'] = $quantity;
		/*if (isset($info['sizes']) && count($info['sizes']) > 0) {
			foreach ($info['sizes'] as $size) {
				if (isset($tshirtecommerces['attribute']) && isset($tshirtecommerces['attribute'][$size]) && count($tshirtecommerces['attribute'][$size]) > 0)
					$array['quantity'] = array_sum($tshirtecommerces['attribute'][$size]);
			}
		}*/

		// get price
		$design_product_id = isset($info['design_product_id']) ? $info['design_product_id'] : '';
		$data['print_type'] = $info['print_type'];
		$tshirtecommerces['product_id'] = $info['product_id'];
		$tshirtecommerces['quantity'] = $array['quantity'];
		$tshirtecommerces['parent_id'] = $parent_id;

		if (!isset($tshirtecommerces['colors'])) $tshirtecommerces['colors'] = false;
		
		if (strpos($design_product_id, ':') !== false) {
			$array['price_of_print'] = $this->model_tshirtecommerce_order->getPriceOfPrint($design_product_id, $tshirtecommerces, $cart);
		}

		return $array;
	}

	protected function getPriceOfPrint($design_product_id, $design = array(), $cart = false)
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
    	    			//$design['print_type'] = $row['print_type'];
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
            if (isset($design['parent_id'])) {
    	   	   $query = $this->db->query('SELECT `price` FROM `'.DB_PREFIX.'product` WHERE `product_id` = '.(int)$design['parent_id']);
    			if ($query->num_rows)
    				$price_base = $query->row['price'];
    		}
            
            $price_of_print = $item - $price_base;*/
            $price_of_print = $item;
    	}

    	return $price_of_print;
    }

    // @since 4.2.2: Fixed add to cart design template
    protected function isDesignTemplate($product_id)
    {
    	$query = $this->db->query('SELECT `design_product_id` FROM `'.DB_PREFIX.'product` WHERE `product_id` = '.(int)$product_id);

    	if ($query->num_rows && isset($query->row['design_product_id'])) {
    		$strs = explode(':', $query->row['design_product_id']);

    		if (count($strs) > 1) {
    			return $strs;
    		}
    	}

    	return array();
    }

    // @since 4.2.2: Fixed add to cart design template
    public function addDesignTemplate2Cart($product_id)
    {
    	$array = array();

    	$strs = $this->isDesignTemplate($product_id);
    	if (count($strs)) {
    		// e.g. d97b4ecdd28867379aa499d5479ebfdd:1511944442:2:FFFFFF

    		// set colors
    		$colors = $strs[3];
    		$str_colors = explode(';', $colors);
    		$temp_colors = array();
    		
    		$design_product_id = implode(':', $strs);

    		if (count($str_colors) > 1) {
    			foreach ($str_colors as $color) {
    				$temp_colors[] = $color;
    			}
    		} else {
    			$temp_colors = array($colors);
    		}
    		$array['options']['colors'] = $temp_colors;
    		$array['options']['color_hex'] = $colors;

    		// only for design template
    		$array['options']['refer'] = 'product';

    		// set parent id
    		$array['parent_id'] = $product_id;

    		// set design product id
    		$array['options']['design_product_id'] = $design_product_id;

    		// set price of print
    		$array['price_of_print'] = $this->getPriceOfPrint($design_product_id);
    	}

    	return $array;
    }
}