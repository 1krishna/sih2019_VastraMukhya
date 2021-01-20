<?php 

// @Since Tshirtecommerce version 4.1.5

$product = $params['product'];
$data = $params['data'];

$price_option_oc = isset($data['price_option_oc']) ? $data['price_option_oc'] : 0;

$product->price = (isset($data['price_old_oc']) ? $data['price_old_oc'] : 0) + (float)$price_option_oc;
$product->sale_price = (isset($data['price_sale_oc']) ? $data['price_sale_oc'] : 0) + (float)$price_option_oc;
$product->tax = isset($data['price_taxes']) ? $data['price_taxes'] : 0;

// Fixed #32165
$post = $params['post'];
if (isset($product->design) && isset($product->design->color_hex) && isset($post['colors']) && count($post['colors']) > 0) {
	$colors_hex = $product->design->color_hex;
	$temp = array();
	foreach ($colors_hex as $key => $row) {
		foreach ($post['colors'] as $color) {
			if ($row == $color) $temp[$key] = $color;
		}
	}
	if (count($temp) > 0) $post['colors'] = $temp;
}
$GLOBALS['post'] = $post;

$GLOBALS['product'] = $product;

?>