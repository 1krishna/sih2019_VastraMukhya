<?php
class ModelVendorMybalance extends Model {
		
	public function getAmount($vendor_id){
		$sql = "SELECT sum(amount) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".$this->vendor->getId()."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
				
 	public function getVendorTotal($vendor_id){
		$sql = "SELECT sum(total) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".$this->vendor->getId(). "'";
								
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalAmount($vendor_id){
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".$this->vendor->getId(). "'";
								
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
}
?>