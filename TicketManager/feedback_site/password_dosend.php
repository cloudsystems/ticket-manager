<?php
/* Copyright (c) 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: password_dosend.php,v 1.9 2010/07/27 09:24:17 alex Exp $
 *
 */
include("include/header.php");
include("include/email_function.php");

function GetNewPassword()
{
	srand((double)microtime()*1000000);
    $str = md5(rand().md5(time()));
	return substr($str, 0, 5).substr($str, 20, 3);
}

if (!$_POST['email']) {
	ErrorPrintOut("no_empty", "email");
}

if (!IsEmailAddress($_POST['email'])) {
	ErrorPrintOut("wrong_format", "email");
}

if ($_POST['register'] == "y") {
	// check user table
	$sql="select * from ".$GLOBALS['BR_customer_user_table']." where email='".$_POST['email']."'";
	$sql_result = $GLOBALS['connection']->Execute($sql) or DBError(__FILE__.":".__LINE__);
	if ($sql_result->Recordcount() > 0) {
		ErrorPrintOut("have_same", "user", $_POST['email']);
	}
	// check tmp user table
	$sql="select * from ".$GLOBALS['BR_customer_user_tmp_table']." where email='".$_POST['email']."'";
	$sql_result = $GLOBALS['connection']->Execute($sql) or DBError(__FILE__.":".__LINE__);
	if ($sql_result->Recordcount() > 0) {
		ErrorPrintOut("have_same", "user", $_POST['email']);
	}

	$new_password = GetNewPassword();
	$now = $GLOBALS['connection']->DBTimeStamp(time());
	$insert_sql = "insert into ".$GLOBALS['BR_customer_user_tmp_table']."(email, password, created_date)
			values('".$_POST['email']."', '".md5($new_password)."', $now)";
	$GLOBALS['connection']->Execute($insert_sql);

	LoadingTimerShow();
	SendRemindPassowrd($_POST['email'], $new_password, 1);
	LoadingTimerHide();

	FinishPrintOut("index.php", "finish_password_send", "", 0);
	

} else {

	$sql="select * from ".$GLOBALS['BR_customer_user_table']." where email='".$_POST['email']."' and account_disabled!='t'";
	$sql_result = $GLOBALS['connection']->Execute($sql) or DBError(__FILE__.":".__LINE__);

	// ���o $sql_result �����C�ơA0 ��ܤ��X�k�B1 ��ܦX�k
	$line = $sql_result->Recordcount();

	// �ھڦX�k�P�_����ܵ��G
	if ($line == 1) {

		$delete_sql = "delete from ".$GLOBALS['BR_customer_user_tmp_table']." where email='".$_POST['email']."'";
		$GLOBALS['connection']->Execute($delete_sql) or DBError(__FILE__.":".__LINE__);

		$new_password = GetNewPassword();
		$now = $GLOBALS['connection']->DBTimeStamp(time());
		$insert_sql = "insert into ".$GLOBALS['BR_customer_user_tmp_table']."(email, password, created_date)
			values('".$_POST['email']."', '".md5($new_password)."', $now)";
		$GLOBALS['connection']->Execute($insert_sql);

		LoadingTimerShow();
		SendRemindPassowrd($_POST['email'], $new_password, 0);
		LoadingTimerHide();

		FinishPrintOut("index.php", "finish_password_send", "", 0);

		// �b���M�K�X��J���~
	} else {
		ErrorPrintOut("no_such_xxx", "user");
	}
}

?>
