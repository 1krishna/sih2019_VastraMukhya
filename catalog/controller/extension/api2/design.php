<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2Design extends Controller {
	public function banners() {
		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$json = array();
		$json['banners'] = array();
		
		$setting['width'] = isset($this->request->post['banner_width']) ? $this->request->post['banner_width'] : 1140;
		$setting['height'] = isset($this->request->post['banner_height']) ? $this->request->post['banner_height'] :380;
		
		if (isset($this->request->post['banner_id'])) {
			$results = $this->model_design_banner->getBanner($this->request->post['banner_id']);

			foreach ($results as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$json['banners'][] = array(
						'title' => $result['title'],
						'link'  => $result['link'],
						'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
					);
				}
			}
		}else{
			$this->load->model('setting/setting');
			$main_banners = $this->config->get('i2csmobile_main_banner');
			$offer_banner = $this->config->get('i2csmobile_offer_banner');
			
			$results = $this->model_design_banner->getBanner($main_banners);

			foreach ($results as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$json['main_banners'][] = array(
						'title' => $result['title'],
						'link'  => $result['link'],
						'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
					);
				}
			}
			
			$results = $this->model_design_banner->getBanner($offer_banner);

			foreach ($results as $result) {
				if (is_file(DIR_IMAGE . $result['image'])) {
					$json['offer_banner'][] = array(
						'title' => $result['title'],
						'link'  => $result['link'],
						'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
					);
				}
			}
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Credentials: true');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function offers() {
		$this->load->model('design/banner');
		$this->load->model('tool/image');
		$json = array();
		$json['banners'] = array();
		
		$setting['width'] = isset($this->request->post['banner_width']) ? $this->request->post['banner_width'] : 1140;
		$setting['height'] = isset($this->request->post['banner_height']) ? $this->request->post['banner_height'] :380;
		
		
		$this->load->model('setting/setting');
		$offer_banner = $this->config->get('i2csmobile_offers_module_banner');
			
		$results = $this->model_design_banner->getBanner($offer_banner);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$json['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Credentials: true');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}