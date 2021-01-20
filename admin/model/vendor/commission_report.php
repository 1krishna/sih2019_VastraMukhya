<?php
class ModelVendorCommissionreport extends Model {
 	public function deleteCommissionReport($order_product_id){		
		$sql="delete  from " . DB_PREFIX . "vendor_order_product where order_product_id='".$order_product_id."'";
		$query=$this->db->query($sql);
 	}
	
	public function getTotaCommission($data,$vendor_id){
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
		
		if(!empty($data['filter_from']) && !empty($data['filter_to'])){
			$sql .= " and date_added>='".$data['filter_from']."' and  date_added<='".$data['filter_to']."'";
		}
		
		$sql .= " AND vendor_id ='".$vendor_id. "'";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getOrderCurrency($order_id){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id='".$order_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}	
				
 	public function getCommissionReports($data=array()){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
				
		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and date_added like '".$this->db->escape($data['filter_date'])."%'";
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
 	public function getTotalCommissionReport($data=array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
}
?>