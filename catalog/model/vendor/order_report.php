<?php
class ModelVendorOrderReport extends Model {
	
	public function getTotalReport($data) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id) WHERE vop.vendor_id='".$this->vendor->getId()."'";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vop.vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" and o.customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" and vop.order_status_id like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" and o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getReports($data){
		$sql = "SELECT *,vop. order_status_id FROM " . DB_PREFIX . "vendor_order_product vop LEFT JOIN " . DB_PREFIX . "order o ON (vop.order_id = o.order_id) WHERE vop.vendor_id='".$this->vendor->getId()."'";
		
		if (isset($data['filter_seller'])){
		 	$sql .=" and vop.vendor_id like '".$this->db->escape($data['filter_seller'])."%'";
		}

		if (isset($data['filter_customer'])){
		 	$sql .=" and o.customer_id like '".$this->db->escape($data['filter_customer'])."%'";
		}
		
		if (isset($data['filter_status'])){
		 	$sql .=" and vop.order_status_id like '".$this->db->escape($data['filter_status'])."%'";
		}
		
		if (isset($data['filter_order_id'])){
		 	$sql .=" and o.order_id like '".$this->db->escape($data['filter_order_id'])."%'";
		}
		
		if (isset($data['filter_date'])){
		 	$sql .=" and vop.date_added like '".$this->db->escape($data['filter_date'])."%'";
		}
		
		$sort_data = array(
			'vop.order_product_id',
			'vop.name',
			'vop.date_added',
			'o.order_id',
			'o.firstname'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY o.order_id";
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
	
	public function getOrderStatus($order_status_id){
		$sql="select * from " . DB_PREFIX . "order_status where order_status_id='".$order_status_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
}	