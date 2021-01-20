<?php 
// @since 4.2.1

class ModelTshirtecommerceSeoUrl extends Model 
{
	public function getSeoUrls()
	{
		$query = $this->db->query('
			SELECT * 
			FROM `'.DB_PREFIX.'tshirtecommerce_seo_url` 
			WHERE `store_id` = '.(int)$this->config->get('config_store_id').' AND `language_id` = '.(int)$this->config->get('config_language_id').'
		');

		if ($query->num_rows) {
			return $query->rows;
		}

		return array();
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

	public function add($product_id, $link = '', $is_cart = 0)
	{
		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		$slink = $link;
		$query = '';

		$queries = explode('tshirtecommerce/designer', htmlspecialchars_decode($link));
		if (count($queries) > 1 && isset($queries[1])) {
			$query = sprintf('tshirtecommerce/designer%s', $queries[1]);
		}

		$keyword_default = $this->config->get('tshirtecommerce_seo_default_keyword');
		if ($keyword_default == null || empty($keyword_default)) {
			$keyword_default = 'custom-your-design';
		}
		$time = uniqid(time());
		$keyword = sprintf('cart-%s-%s-%s', $keyword_default, $this->getProductKeyword($product_id), $time);

		if (!empty($query)) {
			$valid = $this->getKeyword($query);
			if (!empty($valid)) {
				$slink = $server.$valid;
			} else {
				$this->db->query('
					INSERT INTO `'.DB_PREFIX.'tshirtecommerce_seo_url`(`store_id`, `language_id`, `query`, `keyword`, `is_cart`)
					VALUES('.(int)$this->config->get('config_store_id').', '.(int)$this->config->get('config_language_id').', "'.$this->db->escape($query).'", "'.$this->db->escape($keyword).'", 1)
				');

				$slink = $server.$keyword;
			}
		}

		return $slink;
	}

	protected function getProductKeyword($product_id)
	{
		$squery = sprintf('product_id=%s', $product_id);

		$query = $this->db->query('
			SELECT `keyword` 
			FROM `'.DB_PREFIX.'seo_url` 
			WHERE `query`='.$this->db->escape($squery).' AND `store_id` = '.(int)$this->config->get('config_store_id').' AND `language_id` = '.(int)$this->config->get('config_language_id')
		);
		if ($query->num_rows) {
			return $query->row['keyword'];
		}

		$query = $this->db->query('
			SELECT `name`
			FROM `'.DB_PREFIX.'product_description`
			WHERE `product_id`='.(int)$product_id.' AND `language_id` = '.(int)$this->config->get('config_language_id')
		);
		if ($query->num_rows) {
			$keyword = $query->row['name'];
			$keyword = $this->removeSpecialChar($keyword);
			$keyword = str_replace(' ', '-', $keyword);
			return $keyword;
		}

		return '';
	}

	protected function removeSpecialChar($value)
	{
		$result  = preg_replace('/[^a-zA-Z0-9_ -]/s', '', html_entity_decode($value));
		return $result;
	}
}