/**
 * @copyright	Copyright (C) Punnet. All rights reserved.
 * @license		http://www.gnu.org/licenses/gpl.html GNU/GPL.
*/
<?php
header('Content-type: text/css'); ?>
<?php 
$path = base64_decode( urldecode( $_GET['pie'] ) );
?>

.btn {
	-pie-border-radius: 5px 5px 5px 5px;
	-pie-box-shadow:none;
	behavior: url(<?php echo $path; ?>/PIE/PIE.htc);
}

#messages {
	-pie-border-radius: 0 10px 10px 10px;
	-pie-box-shadow:4px 4px 10px 0px rgba(111, 111, 111, 0.6);
	behavior: url(<?php echo $path; ?>/PIE/PIE.htc);	
}

#frontpage {
	-pie-border-radius: 10px;
	-pie-box-shadow:4px 4px 10px 0px rgba(111, 111, 111, 0.6);
	behavior: url(<?php echo $path; ?>/PIE/PIE.htc);
}