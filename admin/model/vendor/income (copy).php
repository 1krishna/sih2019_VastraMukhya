<?php
class ModelVendorincome extends Model {
	
	public function addAmount($data) {
		//print_r($data);die();
		$sql="INSERT INTO " . DB_PREFIX . "vendor_amount_pay set vendor_id='".(int)$data['vendor_id']."',payment_method='".$this->db->escape($data['payment_method'])."',amount='".(float)$data['amount']."',comment='".$this->db->escape($data['comment'])."',date_added=now()";
		$this->db->query($sql);
	}
	
 	public function deleteIncome($order_product_id){		
		$sql="delete  from " . DB_PREFIX . "vendor_order_product where order_product_id='".$order_product_id."'";
		$query=$this->db->query($sql);
 	}
	
	public function getTotaIncome($data,$vendor_id){
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
		
		/*if(!empty($data['filter_from']) && !empty($data['filter_to'])){
			$sql .= " and date_added>='".$data['filter_from']."' and  date_added<='".$data['filter_to']."'";
		}*/
		
		$sql .= " AND vendor_id ='".$vendor_id. "'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getOrder($order_id){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getAmount($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
				
 	public function getIncomes($data=array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
				
		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}
		
		if(!empty($data['filter_from']) && !empty($data['filter_to'])){
			$sql .= " and date_added>='".$data['filter_from']."' and  date_added<='".$data['filter_to']."'";
		}
		
		$sort_data = array(
			'order_product_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY order_product_id";
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
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
		
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
}
?>