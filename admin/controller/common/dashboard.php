<?php
class ControllerCommonDashboard extends Controller {
	public function index() {
		$this->load->language('common/dashboard');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		// Check install directory exists
		if (is_dir(DIR_APPLICATION . 'install')) {
			$data['error_install'] = $this->language->get('error_install');
		} else {
			$data['error_install'] = '';
		}
		
		// Dashboard Extensions
		$dashboards = array();

		$this->load->model('setting/extension');

		// Get a list of installed modules
		$extensions = $this->model_setting_extension->getInstalled('dashboard');
		
		// Add all the modules which have multiple settings for each module
		foreach ($extensions as $code) {
			if ($this->config->get('dashboard_' . $code . '_status') && $this->user->hasPermission('access', 'extension/dashboard/' . $code)) {
				$output = $this->load->controller('extension/dashboard/' . $code . '/dashboard');
				
				if ($output) {
					$dashboards[] = array(
						'code'       => $code,
						'width'      => $this->config->get('dashboard_' . $code . '_width'),
						'sort_order' => $this->config->get('dashboard_' . $code . '_sort_order'),
						'output'     => $output
					);
				}
			}
		}

		$sort_order = array();

		foreach ($dashboards as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $dashboards);
		
		// Split the array so the columns width is not more than 12 on each row.
		$width = 0;
		$column = array();
		$data['rows'] = array();
		
		foreach ($dashboards as $dashboard) {
			$column[] = $dashboard;
			
			$width = ($width + $dashboard['width']);
			
			if ($width >= 12) {
				$data['rows'][] = $column;
				
				$width = 0;
				$column = array();
			}
		}

		if (DIR_STORAGE == DIR_SYSTEM . 'storage/') {
			$data['security'] = $this->load->controller('common/security');
		} else {
			$data['security'] = '';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		// Run currency update
		if ($this->config->get('config_currency_auto')) {
			$this->load->model('localisation/currency');

			$this->model_localisation_currency->refresh();
		}

		$this->response->setOutput($this->load->view('common/dashboard', $data));
	}
}
?>
<?php

$conn=mysqli_connect("localhost", "root", "", "vastra");

$query1="select firstname, lastname, telephone from oc_vendor where vendor_id in (select vendor_id from rated_vendors) order by firstname";

$result=mysqli_query($conn,$query1);

$options="";

while($row=mysqli_fetch_array($result))
{
  $options=$options."<option value='$row[2]'>$row[0] $row[1]</option>\n"; 	
}

?>
<!-- APP FEEDBACK FORM -->
<div style='align:center;'>
<span style='font-size:18px;text-align:center;'><b><U>VIDEO CHAT WITH ARTISAN</U></b><br></span>
<form action="<?php echo $_SERVER['PHP_SELF']; ?> method=post>

<span id="artisan1">
        <label class="control-label" for="artisan">SELECT ARTISAN</label>
        <select class="form-control" name="artisan" id="artisan" style='padding:10px;margin:0px;width:100%;background-color:#ffffff;'>
        <?php echo $options; ?> 
        </select>
</span>

<span id="invite">
  <label class="control-label" for="mobile">Invite Artisan for Video Chat</label>
  <input type='text' name="mobile" id="mobile" value="Dear Artisan, please come online for Live Video Chat @ https://appr.tc/r/VastraMukhi Immediately!%0a-Vastra Mukhi" style='height:50px; width:100%;'><br>
</span>


<span id="button1" style='text-align:center; margin-top:5px;'>
 <br><input type="submit" value="SEND INVITE" style='padding:5px; width:80%;'/>
<br><br><span id='invite_err' style='color:red;font-weight:bold;text-align:center;'></span>
</span>
</form>


<?php
if(isset($_POST['artisan']) && $_POST['mobile'])
{
	$name=$_POST['artisan'];
	$mobile=$_POST['mobile'];
	$msg = substr($mobile, 0, 158);
  file_get_contents("http://smslogin.mobi/spanelv2/api.php?username=smudunuri&password=suresh11&to=$name&from=MCRWEB&message=".urlencode($msg));
  echo "SMS Successfully sent";
  //Store data into URL variable
//file_get_contents($msg1);
}

?>

<?php