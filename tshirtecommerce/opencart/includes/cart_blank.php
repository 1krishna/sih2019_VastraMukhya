<?php 

/* Only 4.1.5+ */

$teattributes['colors'] = isset($this->request->post['design']['color_hex']) ? (array)$this->request->post['design']['color_hex'] : array();
$teattributes['print'] = isset($this->request->post['design']['design_printing']) ? $this->request->post['design']['design_printing'] : '';
$teattributes['cliparts'] = array();
$teattributes['product_id'] = $tproduct_design_id;
$teattributes['artStore'] = array();

$tfile = dirname(DIR_SYSTEM).'/tshirtecommerce/data/products.json';
$tstring = @file_get_contents($tfile);
$eproducts = array();
if ($tstring != false) {
	$tproducts = @json_decode($tstring, true);
	if (count($tproducts) > 0) {
		$eproducts = $tproducts['products'];
	}
}
$eattributes = array();
foreach ($eproducts as $eproduct) {
	if ($eproduct['id'] == $tproduct_design_id) {
		$eattributes = $eproduct['attributes'];
	}
}
$tattributes = array();
foreach ($teoptions as $tkey => $tvalue) {
	switch ($tvalue['type']) {
		case 'checkbox':
			$ttemp = array();
			if (isset($tvalue['value']) && count($tvalue['value']) > 0) {
				foreach ($tvalue['value'] as $ttkey => $ttvalue) {
					$ttemp[$ttkey] = $ttkey;
				}
				$tattributes[$tkey] = $ttemp;
			}
			break;
		case 'selectbox':
		case 'radio':
				if (isset($eattributes['titles'][$tkey]) && count($eattributes['titles'][$tkey]) > 0) {
					foreach ($eattributes['titles'][$tkey] as $tttkey => $tttvalue) {
						if (isset($tvalue['value']) && $tttvalue == $tvalue['value']) {
							$tattributes[$tkey] = $tttkey;
						}
					}
				}
			break;
		case 'textlist':
			if (isset($tvalue['value']) && count($tvalue['value']) > 0) {
				foreach ($tvalue['value'] as $ttkey => $ttvalue) {
					if (count($ttvalue) > 0) {
						if (isset($eattributes['titles'][$tkey]) && count($eattributes['titles'][$tkey]) > 0) {
							foreach ($eattributes['titles'][$tkey] as $tttkey => $tttvalue) {
								if ($tttvalue == $ttkey && $ttvalue > 0) {
									$tattributes[$tkey][$tttkey] = $ttvalue;
								}
							}
						}
					}
				}
			}
			break;
		default:
			break;
	}
}
$teattributes['attribute'] = $tattributes;

?>