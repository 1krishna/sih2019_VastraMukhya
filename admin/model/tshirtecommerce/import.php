<?php 

class ModelTshirtecommerceImport extends Model 
{
	public function import($new_product, $oc_product_id)
	{
		if ($oc_product_id > 0) {
			$file = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
			$jsons = json_decode(file_get_contents($file), true);

			if ($jsons !== false && count($jsons) && isset($jsons['products']) && count($jsons['products'])) {
				$is_new = false;
				foreach ($jsons['products'] as $product) {
					if ($product['sku'] == $new_product['sku']) {
						$is_new = false;
						break;
					} else {
						$is_new = true;
					}
				}

				if ($is_new === true) {
					$new_product['id'] = $this->getNewId($jsons['products']);

					// Download and re-update images url of new product
					

					// update design for opencart product
					$this->db->query("UPDATE `".DB_PREFIX."product` SET `design_product_id` = '".$this->db->escape($new_product['id'])."' WHERE `product_id` = '".(int)$oc_product_id."'");

					// write file
					$jsons['products'][] = $new_product;
					file_put_contents($file, json_encode($jsons));
				} else {
					$this->db->query("UPDATE `".DB_PREFIX."product` SET `design_product_id` = '".$this->db->escape($product['id'])."' WHERE `product_id` = '".(int)$oc_product_id."'");
				}
			}
		}
	}

	protected function getNewId($arary)
	{
		return max(array_column($arary, 'id')) + 1;
	}

	protected function downloadImg($url_to_image, $complete_save_loc)
	{
		if (ini_get('allow_url_fopen')) {
			file_put_contents($complete_save_loc, file_get_contents($url_to_image));
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
			fclose($fp);
		}
	}
}