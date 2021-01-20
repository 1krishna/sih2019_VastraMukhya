<?php
class ModelVendorAllseller extends Model {
	
	public function getVendors($data=array()){
		/* 01-02-2019 approved code  23-02-2019 update */ 
		$sql="select * from " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_description vd on(v.vendor_id = vd.vendor_id) where v.vendor_id<>0  AND v.approved!=0  AND vd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
			
		$sort_data = array(
			'v.vendor_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY v.vendor_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 4;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		return $query->rows;	
 	}

 	public function getTotalVendors($data) {
		/* 01-02-2019 approved code */
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE vendor_id<>0 AND approved!=0";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalProduct($vendor_id) {		
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_to_product WHERE vendor_id='".$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getVendordescription($vendor_id) {
		/* 23-02-2019 update */
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_description WHERE vendor_id='".$vendor_id."' AND language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$query = $this->db->query($sql);
		return $query->row;
		/* 23-02-2019 update */
	}
}
?>