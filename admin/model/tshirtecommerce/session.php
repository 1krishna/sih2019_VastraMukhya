<?php 

class ModelTshirtecommerceSession extends Model 
{
	public function init()
	{
		if (version_compare(VERSION, '3.0.0', '<')) {
			$this->db->query('
				CREATE TABLE IF NOT EXISTS `'.DB_PREFIX.'tshirtecommerce_session` (
			  		`session_id` varchar(32) NOT NULL,
			  		`data` text NOT NULL,
			  		`expire` datetime NOT NULL,
			  	PRIMARY KEY (`session_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
			');

			$this->write($this->session->getId(), $this->session->data);
		}
	}

	public function read($session_id) {
		if (version_compare(VERSION, '3.0.0', '<')) {
			$query = $this->db->query("
				SELECT `data` 
				FROM `" . DB_PREFIX . "tshirtecommerce_session` 
				WHERE session_id = '" . $this->db->escape($session_id) . "' AND expire > " . (int)time()
			);
		} else {
			$query = $this->db->query("SELECT `data` FROM `" . DB_PREFIX . "session` WHERE session_id = '" . $this->db->escape($session_id) . "' AND expire > " . (int)time());
		}
		
		if ($query->num_rows) {
			return json_decode($query->row['data'], true);
		} else {
			return false;
		}
	}
	
	public function write($session_id, $data) {
		if ($session_id && version_compare(VERSION, '3.0.0', '<')) {
			$expire = ini_get('session.gc_maxlifetime');

			$this->db->query("
				REPLACE INTO `" . DB_PREFIX . "tshirtecommerce_session` 
				SET 
					session_id = '" . $this->db->escape($session_id) . "', 
					`data` = '" . $this->db->escape(json_encode($data)) . "', 
					expire = '" . $this->db->escape(date('Y-m-d H:i:s', time() + $expire)) . "'
			");
		}
		
		return true;
	}
	
	public function destroy($session_id) {
		if (version_compare(VERSION, '3.0.0', '<')) {
			$this->db->query("
				DELETE 
				FROM `" . DB_PREFIX . "tshirtecommerce_session` 
				WHERE session_id = '" . $this->db->escape($session_id) . "'
			");
		}
		
		return true;
	}
	
	public function gc($expire) {
		if (version_compare(VERSION, '3.0.0', '<')) {
			$this->db->query("
				DELETE 
				FROM `" . DB_PREFIX . "tshirtecommerce_session` 
				WHERE expire < " . ((int)time() + $expire)
			);
		}
		
		return true;
	}
}