<?php 
// @since version 4.2.1

class ControllerTshirtecommerceMydesign extends Controller 
{
	public function index()
	{
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/account');
		$this->load->language('tshirtecommerce/mydesign');

		if ($this->request->server['HTTPS']) {
			$site_url = $this->config->get('config_ssl');
		} else {
			$site_url = $this->config->get('config_url');
		}

		if (isset($this->request->get['design'])) {
			$search = $this->request->get['design'];
		} else {
			$search = '';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		if (isset($this->request->get['design'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['design'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('design_title'),
			'href' => $this->url->link('tshirtecommerce/mydesign', $url, true)
		);

		$data['button_create_design'] = $this->language->get('button_create_design');
		$data['design_title'] = $this->language->get('design_title');
		$data['design_delete_text'] = $this->language->get('design_delete_text');
		$data['design_view_text'] = $this->language->get('design_view_text');
		$data['design_share_facebook_text'] = $this->language->get('design_share_facebook_text');
		$data['design_share_twitter_text'] = $this->language->get('design_share_twitter_text');
		$data['design_share_pinterest_text'] = $this->language->get('design_share_pinterest_text');
		$data['design_share_instagram_text'] = $this->language->get('design_share_instagram_text');
		$data['tshirtecommerce_design_confirm_delete'] = $this->language->get('tshirtecommerce_design_confirm_delete');
		$data['tshirtecommerce_design_search_holder'] = $this->language->get('tshirtecommerce_design_search_holder');
		$data['tshirtecommerce_design_not_found'] = $this->language->get('tshirtecommerce_design_not_found');

		$data['site_url'] = $site_url;

		$data['tshirtecommerce_design_search_link'] = $this->url->link('tshirtecommerce/mydesign', 'design=', true);

		$tshirtecommerce_parent = $this->config->get('tshirtecommerce_product');
		$this->load->model('tshirtecommerce/mydesign');
		$tshirtecommerce_product = $this->model_tshirtecommerce_mydesign->getDefaultProduct($tshirtecommerce_parent);
		$data['design_your_own'] = $this->url->link('tshirtecommerce/designer', 'product_id='.$tshirtecommerce_product.'&parent_id='.$tshirtecommerce_parent, true);

		$data['mydesign_ajax_del_link'] = $this->url->link('tshirtecommerce/mydesign/delete', '', true);

		$data['search_value'] = $search;

		$limit = 20;
		$filter = array();
		if (isset($this->request->get['design'])) {
			$filter['search'] = $this->request->get['design'];
		}
		$filter['page'] = $page;
		$filter['limit'] = $limit;
		$data['designs'] = $this->model_tshirtecommerce_mydesign->getDesignsPhp($filter);

		$url = '';
		if (isset($this->request->get['design'])) {
			$url .= '&search=' . urlencode(html_entity_decode($this->request->get['design'], ENT_QUOTES, 'UTF-8'));
		}

		// pagination
		$product_total = $this->model_tshirtecommerce_mydesign->getDesignsPhpTotal();
		$this->load->language('product/search');

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('tshirtecommerce/mydesign', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

		//$data['column_left'] = $this->load->controller('common/column_left');
		//$data['column_right'] = $this->load->controller('common/column_right');
		//$data['content_top'] = $this->load->controller('common/content_top');
		//$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('tshirtecommerce/mydesign', $data));
	}

	public function more()
	{
		$page = isset($this->request->post['page']) ? $this->request->post['page'] : 2;

		$segment = 16;
		$start = ($page - 1) * $segment;
		$end = $start + $segment;

		$this->load->language('extension/module/tshirtecommerce');
		$json = array(
			'continue' => 1,
			'html' => '',
		);
		$html = '';

		if ($page < 2) {
			$json['html'] = '';
			return json_encode($json);
		}

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

		if ($start >= count($designs)) {
			$json['continue'] = 0;
			$json['html'] = '';
			return json_encode($json);
		}

		if ($end >= count($designs)) {
			$json['continue'] = 0;
		}

		if (count($designs)) {
			$count = 1;
			foreach ($designs as $key => $design) {
				if ($count >= $start && $count < $end) {
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
			$json['continue'] = 0;
			$html .= '<p>'.$this->language->get('tshirtecommerce_design_not_found').'</p>';
		}

		$json['html'] = $html;

		echo json_encode($json);
		return;
	}

	public function delete()
	{
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/account', '', true);
			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('tshirtecommerce/mydesign');
		
		$this->load->model('tshirtecommerce/mydesign');

		if (isset($this->request->post['id']) && !empty($this->request->post['id'])) {
			$this->model_tshirtecommerce_mydesign->delete($this->request->post['id']);
		}

		echo $this->language->get('tshirtecommerce_design_delete_success');

		return;
	}
}