<?php 
// @since 4.2.0

class ControllerTshirtecommerceAjax extends Controller
{
	public function index() { }

	public function renderAttributes()
	{
		$html = '';

		// Get allow taxes setting
		$tshirtecommerce_allow_taxes = $this->config->get('tshirtecommerce_allow_taxes');
		if ($tshirtecommerce_allow_taxes == null) {
			$tshirtecommerce_allow_taxes = 1;
		}

		// Check enable taxes setting
		if ($tshirtecommerce_allow_taxes == 0) {
			echo '';
			return;
		}

		// Check exits params
		if (!isset($this->request->post['parent']) || !isset($this->request->post['product'])) {
			echo '';
			return;
		}

		// Get post params
		$parent_id = $this->request->post['parent'];
		$product_id = $this->request->post['product'];

		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DIRECTORY_SEPARATOR.'tshirtecommerce');
		include_once ROOT .DS. 'includes' .DS. 'functions.php';
		$dg = new dg();

		$products = $dg->getProducts();
		$p = new stdClass();
		if ($products !== false && count($products)) {
			foreach ($products as $product) {
				if ($product->id == $product_id) {
					$p = $product;
					break;
				}
			}
		}

		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($parent_id);

		if ($product_info == false || !isset($product_info['tax_class_id'])) {
			echo '';
			return;
		}

		if (isset($p->attributes->name)) {
			$html = $this->getAttributes($p->attributes, $product_info['tax_class_id']);
			if (isset($product_info['minimum'])) {
				$html .= $dg->quantity($product_info['minimum']);
			} else {
				$html .= $dg->quantity(1);
			}
		}

		echo $html;
		return;
	}

	protected function getAttributes($attribute, $tax = 0)
	{
		if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DIRECTORY_SEPARATOR.'tshirtecommerce');
		include_once ROOT .DS. 'includes' .DS. 'functions.php';
		$dg = new dg();

		if (isset($attribute->name) && $attribute->name != '') {
			$attrs = new stdClass();
			
			if (is_string($attribute->name)) {
				$attrs->name = json_decode($attribute->name);
			} else {
				$attrs->name = $attribute->name;
			}
			
			if (is_string($attribute->titles)) {
				$attrs->titles = json_decode($attribute->titles);
			} else {
				$attrs->titles = $attribute->titles;
			}
			
			if (is_string($attribute->prices)) {
				$attrs->prices = json_decode($attribute->prices);
			} else {
				$attrs->prices = $attribute->prices;
			}
			
			if (is_string($attribute->type)) {
				$attrs->type = json_decode($attribute->type);
			} else {
				$attrs->type = $attribute->type;
			}

			$html = '';
			$setttings = $dg->getSetting();

			for ($i = 0; $i < count($attrs->name); $i++) {
				$html .= '<div class="form-group product-fields">';
				$html .= '<label for="fields">'.$attrs->name[$i].'</label>';
				$id = 'attribute['.$i.']';
				$html .= $this->field(
					$attrs->name[$i], 
					$attrs->titles[$i], 
					$attrs->prices[$i], 
					$attrs->type[$i], 
					$id, 
					$setttings,				// the settings of tshirtecommerce
					$tax 					// tax of opencart
				);
				$html .= '</div>';
			}

			return $html;
		} else {
			return '';
		}
	}

	protected function field($name, $title, $price, $type, $id, $setttings, $tax = 0)
	{
		$html = '<div class="dg-poduct-fields">';

		switch ($type) {

			case 'checkbox':
				for ($i = 0; $i < count($title); $i++) {
					$html .= '<label class="checkbox-inline">';
					$html .= 	'<input type="checkbox" name="'.$id.'['.$i.']" value="'.$i.'"> '.$title[$i];
					$html .= $this->attributePrice($price[$i], $setttings, $tax);
					$html .= '</label>';
				}
				break;
			
			case 'selectbox':
				$html .= '<select class="form-control input-sm" name="'.$id.'">';
				for ($i = 0; $i < count($title); $i++) {
					if ($price[$i] != '0') {
						$html_price = $this->attributePrice($price[$i], $setttings, $tax);
					} else {
						$html_price = '';
					}
					$html .= '<option value="'.$i.'">'.$title[$i].$html_price.'</option>';
				}
				$html .= '</select>';
				break;
			
			case 'radio':
				for ($i = 0; $i < count($title); $i++) {
					$html .= '<label class="radio-inline">';
					$html .= 	'<input type="radio" name="'.$id.'" value="'.$i.'"> '.$title[$i];
					$html .= $this->attributePrice($price[$i], $setttings, $tax);
					$html .= '</label>';
				}
				break;
			
			case 'textlist':
				$html .= '<style>.product-quantity{display:none;}</style><ul class="p-color-sizes list-number col-md-12">';
				for ($i = 0; $i < count($title); $i++) {
					$html .= '<li>';
					if ($price[$i] != '0') {
						$html_price = '<small>'.$this->attributePrice($price[$i], $setttings, $tax).'</small>';
					} else {
						$html_price = '';
					}
					$html .= 	'<label data-id="'.$title[$i].'">'.$title[$i].$html_price.'</label>';
					$html .= 	'<input type="text" class="form-control input-sm size-number" name="'.$id.'['.$i.']"';
					if ($i == 0) {
						$html .= ' value="1"';
					}
					$html .= 	'/>';
					$html .= '</li>';
				}
				$html .= '</ul>';
				break;

			default:
				break;
		}
		$html .= '</div>';
		
		return $html;
	}

	protected function attributePrice($price, $setttings, $tax = 0)
	{
		$html = '';
		
		if ($price != '' && $price != '0') {
			if (isset($setttings->currency_symbol)) {
				$currency = $setttings->currency_symbol;
			} else {
				$currency = '$';
			}
			
			if (strpos($price, '-') !== false) {
				$price = str_replace('-', '', $price);
				$add = '-';
			} elseif (strpos($price, '+') !== false) {
				$price = str_replace('+', '', $price);
				$add = '+';
			} else {
				$price = $price;
				$add = '+';
			}
			
			if ($tax > 0) {
				//$price += $price * $tax;
				$price = $this->currency->format(
					$this->tax->calculate((float)$price, $tax, $this->config->get('config_tax') ? 'P' : false), 
					$this->session->data['currency']
				);
			}

			/*
			if (isset($setttings->currency_postion) && $setttings->currency_postion == 'right') {
				$html = ' ('.$add.$price.$currency.')';
			} else {
				$html = ' ('.$add.$currency.$price.')';
			}*/
			$html = ' ('.$add.$price.')';
		}

		return $html;
	}
}

?>