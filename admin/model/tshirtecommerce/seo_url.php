<?php 
// @since 4.2.1
class ModelTshirtecommerceSeoUrl extends Model 
{
	public function install()
	{
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('tshirtecommerce');
		$settings['tshirtecommerce_seo_enable'] = 0;
		$this->model_setting_setting->editSetting('tshirtecommerce', $settings);

		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."tshirtecommerce_seo_url` (
			`seo_url_id` int(11) NOT NULL AUTO_INCREMENT,
			`store_id` int(11) NOT NULL,
			`language_id` int(11) NOT NULL,  
			`query` varchar(255) NOT NULL,
			`keyword` varchar(255) NOT NULL,
			`is_cart` tinyint(1) NOT NULL DEFAULT '0',
			PRIMARY KEY (`seo_url_id`),
			KEY `query` (`query`),
			KEY `keyword` (`keyword`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
	}

	public function getSeoUrls($store_id = 0, $language_id = 1)
	{
		$query = $this->db->query('
			SELECT * 
			FROM `'.DB_PREFIX.'tshirtecommerce_seo_url` 
			ORDER BY `seo_url_id` DESC
		');

		if ($query->num_rows) {
			return $query->rows;
		}

		return array();
	}

	public function genUrls()
	{
		// Delete old data
		$this->db->query('DELETE FROM `'.DB_PREFIX.'tshirtecommerce_seo_url` WHERE `is_cart` = 0');

		// Make new data
		$products = array();
		$query = $this->db->query('
			SELECT `product_id`,`design_product_id` 
			FROM `'.DB_PREFIX.'product` 
			WHERE `design_product_id` <> "" AND `design_product_id` IS NOT NULL AND `design_product_id` <> "0"
		');
		if ($query->num_rows) {
			$products = $query->rows;
		}

		// get languages
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		// get stores
		$stores = array();
		if (count($languages)) {
			foreach ($languages as $language) {
				$query = $this->db->query('
					SELECT DISTINCT `store_id` 
					FROM `'.DB_PREFIX.'setting` 
					WHERE `key` = "config_language" AND `value` = "'.$this->db->escape($language['code']).'"
				');
				$str = $query->row['store_id'].'_'.$language['language_id'];
				if ($query->num_rows && !in_array($str, $stores)) {
					$stores[] = $str;
				}
			}
		}


		// Get keyword default in config
		$custom_keyword_default = $this->config->get('tshirtecommerce_seo_default_keyword');
		if ($custom_keyword_default === null || empty($custom_keyword_default)) $custom_keyword_default = 'custom-your-design';

		// make keyword
		if (count($stores)) {
			foreach ($stores as $store) {
				$str = explode('_', $store);

				// Create a link
				foreach ($products as $product) {
					$keywords = $this->getProductKeyword($product['product_id']);

					$str_query = 'tshirtecommerce/designer&parent_id='.$product['product_id'].'&product_id='.$product['design_product_id'];					
					if (is_array($keywords)) {
						$str_keyword = isset($keywords[$str[0]][$str[1]]) ? $keywords[$str[0]][$str[1]] : $this->getProductName($product['product_id']);
					} else {
						$str_keyword = $keywords;
					}
					$str_keyword = $custom_keyword_default.'-'.$str_keyword;

					$this->db->query('
						INSERT INTO `'.DB_PREFIX.'tshirtecommerce_seo_url`(`store_id`,`language_id`,`query`,`keyword`) 
						VALUES('.(int)$str[0].','.(int)$str[1].',"'.$this->db->escape($str_query).'","'.$this->db->escape($str_keyword).'")
					');
				}
			}
		}
		/* end function */
	}

	protected function removeSpecialChar($value)
	{
		$result  = preg_replace('/[^a-zA-Z0-9_ -]/s', '', html_entity_decode($value));
		return $result;
	}

	public function getProductKeyword($product_id)
	{
		$this->load->model('catalog/product');
		$keywords = $this->model_catalog_product->getProductSeoUrls($product_id);

		if (count($keywords) < 1) {
			$keywords = $this->getProductName($product_id);
		}

		return $keywords;
	}

	protected function getProductName($product_id)
	{
		$this->load->model('catalog/product');
		$product = $this->model_catalog_product->getProduct($product_id);

		$keywords = strtolower(str_replace(' ', '-', $product['name']));

		return $this->removeSpecialChar($keywords);
	}

	public function getKeyword($str_query) 
	{
		$query = $this->db->query('
			SELECT `keyword`
			FROM `'.DB_PREFIX.'tshirtecommerce_seo_url` 
			WHERE `store_id` = '.(int)$this->config->get('config_store_id').' AND `language_id` = '.(int)$this->config->get('config_language_id').' AND `query` = "'.$this->db->escape($str_query).'" 
			ORDER BY `seo_url_id` DESC
			LIMIT 1
		');

		if ($query->num_rows) {
			return $query->row['keyword'];
		}

		return '';
	}
}