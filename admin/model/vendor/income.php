<?php
class ModelVendorincome extends Model {
	
	public function addAmount($data) {
		$sql="INSERT INTO " . DB_PREFIX . "vendor_amount_pay set vendor_id='".(int)$data['vendor_id']."',payment_method='".$this->db->escape($data['payment_method'])."',amount='".(float)$data['amount']."',comment='".$this->db->escape($data['comment'])."',date_added=now()";
		$this->db->query($sql);
		return $amount;
	}
		
	public function getTotal($data,$vendor_id){
		$sql = "SELECT sum(total) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".$vendor_id. "'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalCommission($data,$vendor_id){
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".$vendor_id. "'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getPay($vendor_id){
		//$sql="SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_amount_pay vap ON (v.vendor_id = vap.vendor_id) WHERE vap.vendor_id='".$vendor_id."'";
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getAmount($vendor_id){
		$sql = "SELECT sum(amount) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
				
 	public function getIncomes($data=array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor WHERE vendor_id<>0";
		//$sql="SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (v.vendor_id = vop.vendor_id) WHERE v.vendor_id<>0";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}
		
		if (!empty($data['filter_date_added_from'])) {
			$sql .= " AND DATE(date_added) >= '" . $data['filter_date_added_from'] . "'";
		}

		if (!empty($data['filter_date_added_to'])) {
			$sql .= " AND DATE(date_added) <= '" . $data['filter_date_added_to'] . "'";
		}
		
		$sort_data = array(
			'vendor_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY vendor_id";
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
				$data['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;	
 	}
 	public function getTotalIncome($data=array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor WHERE vendor_id<>0";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}
		
		if(!empty($data['filter_from']) && !empty($data['filter_to'])){
			$sql .= " and date_added>='".$data['filter_from']."' and  date_added<='".$data['filter_to']."'";
		}
		
		/*if (isset($data['filter_date'])){
		 	$sql .=" and date_added like '".$this->db->escape($data['filter_date'])."%'";
		}*/
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getVendorTotal($vendor_id){
		$sql = "SELECT sum(total) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".$vendor_id. "'";
								
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalAmount($vendor_id){
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".$vendor_id. "'";
								
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

}
?>