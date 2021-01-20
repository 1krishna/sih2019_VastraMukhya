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
<form action='video_invite.php' method=post>

<span id="artisan1">
        <label class="control-label" for="artisan">SELECT ARTISAN</label>
        <select class="form-control" name="artisan" id="artisan" style='padding:10px;margin:0px;width:100%;background-color:#ffffff;'>
        <?php echo $options; ?> 
        </select>
</span>

<span id="invite">
  <label class="control-label" for="mobile">Invite Artisan for Video Chat</label>
  <input type='text' name="mobile" id="mobile" value="Dear Artisan, please come online for Live Video Chat @ https://appr.tc/r/VASTRA Immediately!%0a-Vastra Mukhi" style='height:50px; width:100%;'><br>
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