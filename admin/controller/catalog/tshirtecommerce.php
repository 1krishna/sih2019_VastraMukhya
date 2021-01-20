<?php
/**
 * @author 		tshirtecommerce - https://tshirtecommerce.com
 * @date 		September 13, 2017
 * 
 * API 			4.2.0
 * 
 * @copyright  	Copyright (C) 2015 https://tshirtecommerce.com. All rights reserved.
 * @license    	GNU General Public License version 2 or later; see LICENSE
 *
 */

class ControllerCatalogTshirtecommerce extends Controller {
	private $error = array();
	
    public function index()
    {
		$this->load->language('extension/module/tshirtecommerce');
		$this->document->setTitle($this->language->get('heading_title'));
		
		// get language
		$data['product_build'] = $this->language->get('product_build');
		$data['heading_title'] = $this->language->get('heading_title');
		$data['breadcrumbs']   = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token='.$this->session->data['user_token'], true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/tshirtecommerce/index', 'user_token='.$this->session->data['user_token'], true)
		);
	
		if ($this->request->server['HTTPS']) {
			$url = HTTPS_CATALOG.'/tshirtecommerce/admin/index.php';
		} else {
			$url = HTTP_CATALOG.'/tshirtecommerce/admin/index.php';
		}

		$data['url'] = str_replace('//tshirtecommerce', '/tshirtecommerce', $url).'?session_id='.$this->session->getId();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
	
		$this->load->model('user/user');
		$user = $this->model_user_user->getUser($this->session->data['user_id']);
		
		$is_admin = array(
			'login' => true,
			'email' => $user['email'],
			'id' => $this->session->data['user_id'],
		);

		$this->session->data['is_admin'] = $is_admin;
		if (!isset($_SESSION['is_admin']))
		{
			$session = dirname(dirname(dirname(dirname(__FILE__)))).'/tshirtecommerce/admin/data/session.txt';
			file_put_contents($session, json_encode($is_admin));
		}
		
		$this->response->setOutput($this->load->view('catalog/tshirtecommerce', $data));
    }
	
	// check update
	public function update()
	{
		$this->load->language('extension/module/tshirtecommerce');
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		$code = (null !== $this->config->get('tshirtecommerce_code')) ? $this->config->get('tshirtecommerce_code') : '';
		$oc_version = str_replace('.', '', VERSION);
		
		// auto update
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (empty($code) || str_replace(' ', '', $code) == '') {
				$data['error_warning'] = 'Please enter your Purchase codes before update or download new version. Click <a style="font-weight:bold" href="'.$this->url->link('module/tshirtecommerce', 'token='.$this->session->data['token'], true).'">here</a> to setting';

				$data['permission_warning'] = 'Before you update new version, please <span style="font-weight:bold;"> backup the files and the data of your current installation!</span>';
			} else {
				$ts_version = str_replace('.','',$this->request->post['update']);
				$file = 'http://updates.tshirtecommerce.com/api.php?code='.$code.'&version='.$ts_version.'&platform=opencart&opencart='.$oc_version;
				$content = $this->openURL($file);
				if ($content !== false) {
					$path = dirname(DIR_SYSTEM).'/opencart_'.$ts_version;
					if (@file_put_contents($path, $content)) {
						$zip = new ZipArchive();
						if ($zip->open($path)) {
							$zip->extractTo(dirname(DIR_SYSTEM));
							$zip->close();
							$data['success'] = '<strong>Update complete:</strong> Your version has been successfully updated.';
						} else {
							$data['error_warning'] = '<strong>Update failed:</strong> Can not open file downloaded on your server. Please try to download and upload to server by FTP account';
						}						
					}
				} else {
					$data['error_warning'] = $this->language->get('updata_file_found');
				}
			}
		} else {
			$data['permission_warning'] = 'Before you update new version, please <span style="font-weight:bold;">always backup the files and the data of your current installation!</span>';
		}
		

		if (isset($this->error['warning'])) $data['error_warning'] = $this->error['warning'];
		if (isset($this->error['code'])) $data['error_warning'] = $this->error['code'];
		
		// load language
		$data['updata_head'] = $this->language->get('updata_head');
		$data['updata_version'] = $this->language->get('updata_version');
		$data['updata_fontd'] = $this->language->get('updata_fontd');
		
		// check version
		$file = dirname(DIR_SYSTEM).'/tshirtecommerce/version.json';
		$version = '';
		if (file_exists($file)) {
			$str = @file_get_contents($file);
			if ($str !== false) {
				$content = json_decode($str);
				$version = $content->version;
			}
		}
		$data['version'] = $version;
		
		// load update content
		$file = 'http://updates.tshirtecommerce.com/opencart/updates.json';
		$content = $this->openURL($file);
		if ($content !== false) {
			$versions = json_decode($content);
			if (count($versions)) $data['versions'] = $versions;
		}

		// load update content
		$file = 'http://updates.tshirtecommerce.com/opencart/updates.json';
		$content = $this->openURL($file);
		if ($content !== false) {
			$versions = json_decode($content);
			if (count($versions)) {
				foreach ($versions as &$v) {
					$v->info = $this->openURL('http://updates.tshirtecommerce.com/opencart/'.$v->info);
				}
				$data['versions'] = $versions;
			}
		}
		
		$data['code'] = $code;
		$data['oc_version'] = $oc_version;
		$data['link'] = $this->url->link('catalog/tshirtecommerce/update', 'user_token='.$this->session->data['user_token'], true);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $data['link']
		);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		// Verify code
		$data['tshirtecommerce_verify'] = array();
		if (!empty($code)) {
			$json_str = $this->openURL('http://updates.tshirtecommerce.com/verify_purchase.php?code='.$code.'&platform=opencart');
			if ($json_str !== false) $data['tshirtecommerce_verify'] = json_decode($json_str, true);
		}

		if (empty($code) || str_replace(' ', '', $code) == '' || count($data['tshirtecommerce_verify']) == 0 || $data['tshirtecommerce_verify']['error'] == 1) {
			$data['error_warning'] = 'Please enter your Purchase codes before update or download new version. Click <a style="font-weight:bold;" href="'.$this->url->link('extension/module/tshirtecommerce', 'user_token='.$this->session->data['user_token'], true).'">here</a> to setting';
		}
		
		$this->response->setOutput($this->load->view('catalog/tshirtecommerce_update', $data));		
	}
	
	protected function fnStrtolower($str)
	{
		if (is_array($str)) {
	            return false;
	        }
	        if (function_exists('mb_strtolower')) {
	            return mb_strtolower($str, 'utf-8');
	        }
	        return strtolower($str);
	}

	protected function openURL($url, $use_include_path = false, $stream_context = null, $curl_timeout = 5)
	{
	    if ($stream_context == null && preg_match('/^https?:\/\//', $url)) {
	        $stream_context = @stream_context_create(array('http' => array('timeout' => $curl_timeout)));
	    }
	    if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1')) || !preg_match('/^https?:\/\//', $url)) {
	        return @file_get_contents($url, $use_include_path, $stream_context);
	    } elseif (function_exists('curl_init')) {
	        $curl = curl_init();
	        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($curl, CURLOPT_URL, $url);
	        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
	        curl_setopt($curl, CURLOPT_TIMEOUT, $curl_timeout);
	        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	        if ($stream_context != null) {
	            $opts = stream_context_get_options($stream_context);
	            if (isset($opts['http']['method']) && $this->fnStrtolower($opts['http']['method']) == 'post') {
	                curl_setopt($curl, CURLOPT_POST, true);
	                if (isset($opts['http']['content'])) {
	                    parse_str($opts['http']['content']);
	                    curl_setopt($curl, CURLOPT_POSTFIELDS, array());
	                }
	            }
	        }
	        $content = curl_exec($curl);
	        curl_close($curl);
	        return $content;
	    } else {
	        return false;
	    }
	}
	
	public function ajax()
	{
		if ($this->request->server['HTTPS']) {
			$url = HTTPS_CATALOG.'/tshirtecommerce/admin.php?key=123';
		} else {
			$url = HTTP_CATALOG.'/tshirtecommerce/admin.php?key=123';
		}

		echo $this->openURL($url);		
		exit;
	}
	
	
	protected function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'catalog/tshirtecommerce')) $this->error['warning'] = $this->language->get('error_permission');
		if ($_FILES["import_design"]["name"] == '') $this->error['code'] = $this->language->get('error_name');
		
		if (!$this->error) {
			return true;
		} else {
			return $this->error;
		}	
	}
	
	protected function validate()
	{
		if (!$this->user->hasPermission('modify', 'catalog/tshirtecommerce')) $this->error['warning'] = $this->language->get('error_permission');
		if (!$this->request->post['designer_code']) $this->error['code'] = $this->language->get('error_code');

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
	
	protected function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'catalog/tshirtecommerce')) $this->error['warning'] = $this->language->get('error_permission');

		if (!$this->error) {
			return true; 
		} else {
			return false;
		}
	}

	protected function validateRepair()
	{
		if (!$this->user->hasPermission('modify', 'catalog/tshirtecommerce')) $this->error['warning'] = $this->language->get('error_permission');

		if (!$this->error) {
			return true; 
		} else {
			return false;
		}
	}

	protected function is_session_started()
	{
	    if ( php_sapi_name() !== 'cli' ) {
	        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
	            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
	        } else {
	            return session_id() === '' ? FALSE : TRUE;
	        }
	    }
	    return FALSE;
	}
	
	public function tshirt_user_is_login()
	{
		$this->load->model('user/user');
		$user = $this->model_user_user->getUser($this->session->data['user_id']);
		$data = array();
		if ( isset($user['email']) && $user['email'] != '')
		{
			$data['logged'] = true;
			$data['user'] 	= array(
				'id' 		=> $this->session->data['user_id'],
				'is_admin' 	=> false,
				'username' => $user['username'],
				'email' => $user['email'],
				'key' => md5($this->session->data['user_id'])
			);
			
			if (!session_id())
				session_start();
		
			$logged = array(
				'login' => true,
				'email' => $user['email'],
				'id' => $this->session->data['user_id'],
				'is_admin' => false,
			);
			$logged['is_admin'] = true;
			$data['user']['is_admin'] = true;

			/* fix server not allow write session */
			if( isset($_POST['path']) )
			{
				$file = $_POST['path'].'ajax.php';
				include_once($file);
				$reload = tshirt_session_file($data);

				if($reload == true)
				{
					$data['reload'] = 1;
				}
			}
			$_SESSION['is_logged'] = $logged;

			/* check file session */
			$path = dirname(dirname(dirname(dirname(__FILE__)))). '/tshirtecommerce/admin/';
			if( file_exists($path.'session.php') )
			{
				$session_id = session_id();
				$content 	= session_encode();
				$file 	= $path.'/temp/sess_'.$session_id;
				write_to_file($content, $file);
			}
			
		}
		else
		{
			$data['logged'] = false;
		}
		
		echo json_encode($data);
		return;
	}
}
?>