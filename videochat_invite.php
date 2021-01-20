<?php

  $artisan = addslashes($_GET['artisan']);
  $mobile = addslashes($_GET['mobile']);
  
  echo "<span style='color:green;'>Your Invitation SMS has been Sent successfully!</span>";

  $msg = substr($mobile, 0, 158);
  $msg1 = "http://smslogin.mobi/spanelv2/api.php?username=smudunuri&password=suresh11&to=$artisan&from=MCRWEB&message=".urlencode($msg);    //Store data into URL variable


  $ret1 = file_get_contents($msg1);    //Call Url variable by using file() function

  
?>