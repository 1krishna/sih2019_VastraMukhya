<?php 

// Since Opencart Custom Product Designer 4.1.5

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
	case 'store_payment_art':
		store_payment_art();
		break;

	case 'store_ajax_key':
		store_ajax_key();
		break;
	
	default:
		break;
}

function store_payment_art()
{
	if (isset($_POST['e_order_id']) && $_POST['e_order_id'] != '' && isset($_POST['params']) && $_POST['params'] != '') {
		
		$e_order_id = $_POST['e_order_id'];
		$params 	= $_POST['params'];
		$api 		= $_POST['api'];

		store_art_update($e_order_id, $params, array(), $api);
	}
}

function store_ajax_key()
{
	ini_set('max_execution_time', 3000);

	$data = array(
		'error'		=> 0,
		'msg'		=> '',
		'reload'	=> 0
	);

	if (empty($_GET['api_key']) || empty($_GET['arts']) || empty($_GET['order_id'])) {
		$data['error']	= 1;
		$data['msg']	= 'Data design not found!';
	} else {
		$ids 	= str_replace(':', '-', $_GET['arts']);
		$url 	= 'http://api.9file.net/api/order/ids/'.$ids.'/order_number/'.$_GET['order_id'].'/api_key/'.$_GET['api_key'];
		$result = openURL($url);

		if ($result != false) {
			$arts = json_decode($result, true);

			if (isset($arts['error'])) {
				$data['error']	= 1;
				$data['msg']	= 'Data design not found!';
			} else {
				$params		= '';
				$art_prices	= array();

				foreach ($arts as $id => $art) {
					if ($art['price'] > 0) {
						$art_prices[$id]	= $art['price'];
						$art['key']			= 0;
					}

					if(empty($params)) {
						$params		= $id.':'.$art['key'];
					} else {
						$params		.= ';'.$id.':'.$art['key'];
					}
				}

				if (count($art_prices) > 0) {
					$data['error']	= 1;
					$data['msg']	= 'Please payment before download file design!';
				}

				store_art_update($_GET['order_id'], $params, $art_prices, $_GET['api_key']);
			}
		} else {
			$data['error']	= 1;
			$data['msg']	= 'Data design not found!';
		}		
	}

	echo json_encode($data); exit;
	exit;
}

function store_art_update($design_id, $params, $art_prices = array(), $api = '')
{
	ini_set('max_execution_time', 3000);

	$array 	= explode(';', $params);
	$arts 	= array();

	for ($i = 0; $i < count($array); $i++) {
		$art = explode(':', $array[$i]);

		if (count($art) > 1) $arts[$art[0]] = $art[1];
	}

	if (count($arts)) {
		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(dirname(__FILE__)));
		
		include_once (ROOT.DS.'includes'.DS.'functions.php');
		$dg = new dg();
		
		// update sales
		$file = ROOT.DS.'data'.DS.'store'.DS.'arts_info.json';

		if (file_exists($file)) {
			// call cache
			$cache 	= $dg->cache('store');
			$sales 	= $cache->get('sales');

			if ($sales == null) $sales 	= array();
			
			$time 	= time();
			$month 	= date('Y_m', $time);
			$day 	= date('d', $time);
			
			if (empty($sales[$month])) $sales[$month] = array();
			if (empty($sales[$month][$day])) $sales[$month][$day] = array();
		
			$rows = json_decode(@file_get_contents($file), true);

			foreach ($arts as $clipar_id => $value) {
				if(isset($art_prices[$clipar_id])) continue;
				
				if (isset($rows[$clipar_id])) {
					if(isset($rows[$clipar_id]['sales'])) {
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
		
		$obj = explode(':', $design_id);
		if (count($obj) > 1 && $obj[0] != 'cart') {
			$cache 	= $dg->cache('design');
			$designs = $cache->get($obj[0]);

			if ($designs == null) {
				$cache 		= $dg->cache('admin');
				$designs 	= $cache->get($obj[0]);
			}
			
			if (isset($designs[$obj[1]])) {
				$design	= $designs[$obj[1]];
			} else {
				$design	= array();
			}

			$design_id = $obj[0];
		} else {
			$cache 	= $dg->cache('cart');
			$design = $cache->get($design_id);
		}
		
		if (isset($design['vectors'])) {
			$design['vector'] = $design['vectors'];
			unset($design['vectors']);
		}
		
		if (isset($design['vector'])) {
			if (is_array($design['vector'])) {
				$vectors = $design['vector'];
			} else {
				$vectors = json_decode($design['vector'], true);
			}

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

									if ((isset($item['file']) && is_string($item['file']) && $item['file'] == 'svg') || (isset($item['file']['type']) && $item['file']['type'] == 'svg')) {
										$svg 	= StorestrSVG($item['svg'], $arts[ $item['clipart_id'] ]);
									} else {
										$key_active = str_replace(' ', '+', $arts[ $item['clipart_id'] ]);
										$svg		= $item['svg'];
										$key 		= md5( $key_active );
										$url 		= 'http://api.9file.net/api/orderPNG/id/'.$item['clipart_id'].'/key/'.$key.'/api_key/'.$api;
										$result 	= openURL($url);

										if ($result != false) {
											$data = json_decode($result, true);

											if (isset($data['content'])) {
												$png 	= encrypt_compress($key_active, base64_decode($data['content']));
												$img 	= 'data:image/png;base64,' . base64_encode($png);
												$temp1 	= explode('xlink:href="', $item['svg']);

												if (count($temp1) > 1) {
													$temp2 = explode('">', $temp1[1]);

													if (count($temp2) > 1) {
														$svg = $temp1[0] .'xlink:href="'. $img .'">'. $temp2[1];
													}
													
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
			if (isset($designs) && isset($obj[1]) && isset($designs[$obj[1]])) {
				$designs[$obj[1]] 	= $design;
				$design 			= $designs;
			}

			$cache->set($design_id, $design);
		}
	}
}

function StorestrSVG($svg, $key)
{
	$key = str_replace(' ', '+', $key);

	if (empty($svg)) return '';
	
	$params = explode('/', $svg);
	$n 		= count($params);
	$str 	= '';

	for ($i = 0; $i < $n; $i++) {
		$number = $params[$i];
		$s 		= substr($key, $number, 1);
		$str 	.= $s;
	}
	
	$output = base64_decode($str);
	return $output;
}

function encrypt_compress($key, $str) 
{
	$s = array();

	for ($i = 0; $i < 256; $i++) {
		$s[$i] = $i;
	}

	$j = 0;

	for ($i = 0; $i < 256; $i++) {
		$j 		= ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
		$x 		= $s[$i];
		$s[$i] 	= $s[$j];
		$s[$j] 	= $x;
	}

	$i 		= 0;
	$j 		= 0;
	$res 	= '';
	$count 	= strlen($str);

	for ($y = 0; $y < $count; $y++) {
		$i 		= ($i + 1) % 256;
		$j 		= ($j + $s[$i]) % 256;
		$x 		= $s[$i];
		$s[$i] 	= $s[$j];
		$s[$j] 	= $x;
		$res 	.= $str[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
	}

	return $res;
}

function openURL($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
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

            if (isset($opts['http']['method']) && Tools::strtolower($opts['http']['method']) == 'post') {
                curl_setopt($curl, CURLOPT_POST, true);

                if (isset($opts['http']['content'])) {
                    parse_str($opts['http']['content'], $post_data);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
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

exit;

?>