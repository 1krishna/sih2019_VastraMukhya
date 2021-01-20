<?php
class ControllerVendorallseller extends Controller {
	public function index() {

		$this->load->language('vendor/allseller');
		$this->load->model('vendor/allseller');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'vendor_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = 8;
		}
		
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('vendor/allseller', '', true)
		);
		
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}


		$this->document->setTitle($this->language->get('heading_title'));


		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['vendors'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		);

		$this->load->model('tool/image');
		$this->load->model('vendor/vendor');

		$seller_total = $this->model_vendor_allseller->getTotalVendors($filter_data);
		$results = $this->model_vendor_allseller->getVendors($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['banner'])) {
				$image = $this->model_tool_image->resize($result['banner'], 600, 200);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 600, 200);
			}
			
			if (is_file(DIR_IMAGE . $result['image'])) {
				$smallimage = $this->model_tool_image->resize($result['image'], 70, 70);
			} else {
				$smallimage = $this->model_tool_image->resize('no_image.png', 70, 70);
			}

			if(isset($result['vendor_id'])){
				$totalproduct = $this->model_vendor_allseller->getTotalProduct($result['vendor_id']);
			}

			$store_info = $this->model_vendor_allseller->getVendordescription($result['vendor_id']);
			if(isset($store_info['name'])){
				$storename = $store_info['name'];
			} else {
				$storename = '';
			}

			$data['vendors'][] = array(
				'vendor_id'   => $result['vendor_id'],
				'thumb'       => $image,
				'smallthumb'  => $smallimage,
				'storename'   => $storename,
				'firstname'   => $result['firstname'].' '.$result['lastname'],
				'email'   	  => $result['email'],
				'telephone'   => $result['telephone'],
				'city'   	  => $result['city'],
				'facebookurl' => $result['facebook_url'],
				'googleurl'   => $result['google_url'],
				'totalproduct'=> $totalproduct ,
				'href'        => $this->url->link('vendor/vendor_profile', 'vendor_id=' . $result['vendor_id']),
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$url = '';


		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
			
		$pagination = new Pagination();
		$pagination->total = $seller_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('vendor/allseller','&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($seller_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($seller_total - $limit)) ? $seller_total : ((($page - 1) * $limit) + $limit), $seller_total, ceil($seller_total / $limit));
			
			
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['limit'] = $limit;
		
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('vendor/allseller', $data));
	}
}