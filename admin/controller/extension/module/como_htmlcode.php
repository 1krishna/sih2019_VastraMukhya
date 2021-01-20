<?php
class ControllerExtensionModuleComoHtmlCode extends Controller {
	private $error = array(); 
    private $module_info = array();


    public function install() {
        $this->opencartClearCache();
    }

	public function index() {
		$this->load->language('extension/module/como_htmlcode');

		$this->document->setTitle($this->language->get('heading_title_clean'));
        $this->document->addStyle('view/stylesheet/como_htmlcode.css');
        // jquery confirm
        $this->document->addScript('view/javascript/jquery/jquery-confirm/jquery-confirm.min.js');
        $this->document->addStyle('view/javascript/jquery/jquery-confirm/jquery-confirm.min.css');

		$this->load->model('setting/module');
		$this->load->model('tool/image');
		$this->load->model('localisation/language');
		
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$data['httpimagedir'] = HTTPS_CATALOG . 'image/';
		} else {
			$data['httpimagedir'] = HTTP_CATALOG . 'image/';
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->request->post && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('como_htmlcode', $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $this->session->data['success'] = $this->language->get('text_success');

                if (isset($this->request->get['copy_module']) && $this->request->get['copy_module']) {
                    // copy module
                    $this->request->post['name'] = $this->request->post['name'] . ' - COPY';
                    $this->model_setting_module->addModule('como_htmlcode', $this->request->post);
                    $this->request->get['module_id'] = $this->db->getLastId();
                    $this->session->data['success'] = $this->session->data['success'] . '<br /><i class="fa fa-copy"></i>' . $this->request->post['name'];
                } elseif (isset($this->request->get['delete_module']) && $this->request->get['delete_module']) {
                    // delete module
                    $this->model_setting_module->deleteModule($this->request->get['module_id']);
                    $this->request->get['module_id'] = 0;
                    $this->session->data['success'] = $this->session->data['success'] . '<br /><i class="fa fa-trash-o"></i> ' . $this->request->post['name'];
                    $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
                }

                $this->response->redirect($this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true));
			}
		}

        $data['module_id'] = isset($this->request->get['module_id']) ? $this->request->get['module_id'] : 0;

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['no_image'] = $this->model_tool_image->resize('no_image.png', 32, 32);

        // stylesheets in default theme
        $data['stylesheets_default'] = array();
        foreach (glob(DIR_CATALOG . 'view/theme/default/stylesheet/como_htmlcode*.css') as $filename) {
            $data['stylesheets_default'][] = basename($filename);
        }

        // templates in default theme
        $data['templates_img_block_default'] = array();
        foreach (glob(DIR_CATALOG . 'view/theme/default/template/extension/module/como_imgblock_img*.twig') as $filename) {
            $data['templates_img_block_default'][] = basename($filename);
        }

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
   		);

        $modules = $this->model_setting_module->getModulesByCode('como_htmlcode');
        foreach ($modules as $module) {
			$data['breadcrumbs'][] = array(
				'text' => ($module['module_id'] == $data['module_id'] ? '<span class="module_name">' . strtoupper($module['name']) . '</span>' : $module['name']),
				'href' => $this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'], true)
			);
		}
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('button_add'),
            'href' => $this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'], true)
        );
		
		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
			$data['action_copy'] = $this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'] . '&copy_module=1&module_id=' . $this->request->get['module_id'], true);
			$data['action_delete'] = $this->url->link('extension/module/como_htmlcode', 'user_token=' . $this->session->data['user_token'] . '&delete_module=1&module_id=' . $this->request->get['module_id'], true);
		}
		
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$this->module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['name'] = $this->setData('name', '');
		$data['status'] = $this->setData('status', '1');
		$data['border'] = $this->setData('border', '1');
		$data['block_title'] = $this->setData('block_title', array());
		$data['block_link'] = $this->setData('block_link', '');
		$data['title_image'] = $this->setData('title_image', '');
		$data['stylesheet'] = $this->setData('stylesheet', 'como_htmlcode.css');
		$data['html_code_status'] = $this->setData('html_code_status', '');
		$data['html_visualeditor'] = $this->setData('html_visualeditor', '');
		$data['como_htmlcode'] = $this->setData('como_htmlcode', array());
		$data['img_block_status'] = $this->setData('img_block_status', '');
		$data['img_block_template'] = $this->setData('img_block_template', 'como_imgblock_img.twig');
		$data['img_block_background'] = $this->setData('img_block_background', '');
		$data['img_number'] = $this->setData('img_number', 6);
		$data['img_block_images'] = $this->setData('img_block_images', array());
		$data['img_block_images1'] = $this->setData('img_block_images1', array());
		$data['img_block_links'] = $this->setData('img_block_links', array());
		$data['img_block_titles'] = $this->setData('img_block_titles', array());

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/como_htmlcode', $data));
	}

	private function setData($dataname, $defaultValue='') {
		if (isset($this->request->post[$dataname])) {
			return $this->request->post[$dataname];
		} elseif (isset($this->module_info[$dataname])) {
			return $this->module_info[$dataname];
		} else {
			return $defaultValue;
		}
    }

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/como_htmlcode')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}
		
		return !$this->error;
	}

    function opencartClearCache() {
        $directories = glob(DIR_CACHE . '*', GLOB_ONLYDIR);
        if ($directories) {
            foreach ($directories as $directory) {
                $files = glob($directory . '/*');
                foreach ($files as $file) { 
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                if (is_dir($directory)) {
                    rmdir($directory);
                }
            }
        }
	}
}
