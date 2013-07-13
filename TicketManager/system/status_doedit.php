<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: status_doedit.php,v 1.5 2008/11/28 10:36:31 alex Exp $
 *
 */
include("../include/header.php");

AuthCheckAndLogin();

if (!($GLOBALS['Privilege'] & $GLOBALS['can_admin_status'])) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_privilege");
}

if (!trim($_POST['status_name'])) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST, 
						  "no_empty", "status_name");
}
if (!trim($_POST['status_color'])) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST, 
						  "no_empty", "color");
}
if (!trim($_POST['status_type'])) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST, 
						  "no_empty", "type");
}

if (utf8_strlen($_POST['status_name']) > 60) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST,
						  "too_long", "status_name", "60");
}
if (utf8_strlen($_POST['status_color']) > 20) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST,
						  "too_long", "color", "20");
}
if (($_POST['status_type'] != "active") && ($_POST['status_type'] != "closed")) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST,
						  "wrong_format", "type");
}
	
$check_sql = "select * from ".$GLOBALS['BR_status_table']." 
				where status_name='".$_POST['status_name']."' and
				status_id !='".$_POST['status_id']."'";

$check_result = $GLOBALS['connection']->Execute($check_sql) or 
		DBError(__FILE__.":".__LINE__);

$line = $check_result->Recordcount();
if ($line > 0) {
	ErrorPrintBackFormOut("GET", "status_edit.php", $_POST,
						  "have_same", "status_name", $_POST['status_name']);
}

$sql = "update ".$GLOBALS['BR_status_table']." set status_name='".$_POST['status_name']."', 
		status_color = '".$_POST['status_color']."',
		status_type = '".$_POST['status_type']."' where
		status_id='".$_POST['status_id']."'";

$GLOBALS['connection']->Execute($sql) or DBError(__FILE__.":".__LINE__);

WriteSyslog("info", "syslog_edit_xxx", "status", $_POST['status_name']);
FinishPrintOut("status_admin.php", "finish_update", "status");

?>
