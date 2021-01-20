<?php 
// @since version 4.2.1

class ModelTshirtecommerceMydesign extends Model
{
	public function getDefaultProduct($parent_id = 0)
    {
    	$product_id = 0;
    	$query = $this->db->query('SELECT `design_product_id` FROM `'.DB_PREFIX.'product` WHERE `product_id` = '.(int)$parent_id);

    	if ($query->num_rows) {
    		$product_id = $query->row['design_product_id'];
    	}

    	$pos = explode(':', $product_id);
    	if (count($pos) > 1 && isset($pos[2])) {
    		$product_id = $pos[2];
    	}

    	return $product_id;
    }

    public function getDesignsPhpTotal()
    {
    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
		include_once ROOT.DS.'includes'.DS.'functions.php';
		$dg = new dg();
		$cache = $dg->cache();
		$user_id = md5($this->customer->getId());
		$designs = $cache->get($user_id);

		if ($designs == null || $designs == false) {
			$designs = array();
		}

		return count($designs);
    }

    public function getDesignsPhp($filter = array())
    {
    	$array = array();

    	if ($this->request->server['HTTPS']) {
			$site_url = $this->config->get('config_ssl');
		} else {
			$site_url = $this->config->get('config_url');
		}

    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
		include_once ROOT.DS.'includes'.DS.'functions.php';
		$dg = new dg();
		$cache = $dg->cache();
		$user_id = md5($this->customer->getId());
		$designs = $cache->get($user_id);

		if ($designs == null || $designs == false) {
			$designs = array();
		}

		$limit = isset($filter['limit']) ? $filter['limit'] : 20;
		$page = isset($filter['page']) ? $filter['page'] : 1;

		if ($page < 1) $page = 1;

		$start = ($page - 1) * $limit;
		$end = $start + $limit;

		$search = isset($filter['search']) ? $filter['search'] : '';
		$search_designs = array();
		if (!empty($search) && count($designs)) {
			foreach ($designs as $key => $design) {
				if (strpos($design['title'], $search) !== false || strpos($design['description'], $search) !== false) {
					$search_designs[$key] = $design;
				}
			}

			$designs = $search_designs;
		}

		$count = 1;
		foreach ($designs as $key => &$design) {
			if ($count > $start && $count <= $end) {
				//product_url/user_design::ID_of_design
				$id_of_design = $user_id.':'.$key.':'.$design['product_id'].':'.$design['product_options'];
				$url = 'product_id='.$design['parent_id'];
				$url .= '&user_design='.$id_of_design;
				//$url = '&parent_id='.$design['parent_id'];
				$design['view_link'] = $this->url->link('product/product', $url, true);
				$design['id'] = $key;

				$design['fblink'] = sprintf('https://www.facebook.com/sharer/sharer.php?u=%s', $design['view_link']);
				$design['twlink'] = sprintf('https://twitter.com/intent/tweet?text=%s&url=%s', urlencode($design['title']), $design['view_link']);
				$design['prlink'] = sprintf('https://www.pinterest.com/pin/create/button/?url=%s&media=%s&description=%s', $design['view_link'], $site_url.'/tshirtecommerce/'.$design['image'], urlencode($design['title']));
				//$design['iglink'] = sprintf('%s', $design['view_link']);

				$array[$key] = $design;
			}
			$count++;
		}

		return $array;
    }

    public function delete($id)
    {
    	if (empty($id)) return true;

    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
		include_once ROOT.DS.'includes'.DS.'functions.php';
		$dg = new dg();
		$cache = $dg->cache();
		$user_id = md5($this->customer->getId());
		$designs = $cache->get($user_id);

		if ($designs == null || $designs == false) {
			$designs = array();
		}

		if (count($designs)) {
			foreach ($designs as $key => $design) {
				if ($key == $id) {
					unset($designs[$key]);
				}
			}
		}

		$cache->set($user_id, $designs);

    	return true;
    }

    public function getDesigns()
    {
    	$segment = 16;
    	$json = array(
    		'continue' => 1,
    		'html' => ''
    	);

    	$this->load->language('extension/module/tshirtecommerce');
    	$html = '';

    	if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if (!defined('ROOT')) define('ROOT', dirname(DIR_SYSTEM).DS.'tshirtecommerce');
		include_once ROOT.DS.'includes'.DS.'functions.php';
		$dg = new dg();
		$cache = $dg->cache();
		$user_id = md5($this->customer->getId());
		$designs = $cache->get($user_id);

		if ($this->request->server['HTTPS']) {
			$site_url = HTTPS_SERVER;
		} else {
			$site_url = HTTP_SERVER;
		}

		if (count($designs)) {
			$count = 1;
			foreach ($designs as $key => $design) {
				if ($count <= $segment) {
					$params = sprintf('product_id=%s:%s:%s:%s&parent_id=%s', $user_id, $key, $design['product_id'], $design['product_options'], $design['parent_id']);
					$link_edit = $this->url->link('tshirtecommerce/designer', $params, true);

					$html .= '<div id="mydesign-item-'.$key.'" class="mydesign-item">';
					$html .= 	'<span class="iconclear" title="'.$this->language->get('label_delete').'" onclick="removemydesign(\''.$key.'\')"><i class="material-icons">clear</i></span>';
					$html .= 	'<a target="_blank" href="'.$link_edit.'" title="'.$design['title'].'">';
					$html .= 		'<img src="'.$site_url.'/tshirtecommerce/'.$design['image'].'" alt="'.$design['title'].'" />';
					$html .= 	'</a>';
					$html .= '</div>';
				}
				$count++;
			}
		} else {
			$html .= '<p>'.$this->language->get('tshirtecommerce_design_not_found').'</p>';
		}

		if (count($designs) <= $segment) {
			$json['continue'] = 0;
		}

		$json['html'] = $html;

    	return $json;
    }
}