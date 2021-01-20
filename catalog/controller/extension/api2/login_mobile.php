<?php
//turn off all warnings and notices for API calls
error_reporting(E_ERROR);
set_error_handler(null);

class ControllerExtensionApi2LoginMobile extends Controller {
	public function index() {
		/* deprecated method from i2CSMobile v1.1.0  */
		
		$json = array();
		
		/*$session_name = 'temp_session_' . uniqid();
		$session = new Session();
		@$session->start($this->session->getId(), $session_name);*/

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