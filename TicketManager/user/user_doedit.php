<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: user_doedit.php,v 1.15 2010/07/27 09:24:17 alex Exp $
 *
 */
include("../include/header.php");
include("../include/project_function.php");
include("../include/email_function.php");

AuthCheckAndLogin();

// ��J�@�ӨϥΪ̥h��X�쥻�ϥΪ̪����
// �p�G�����ϥΪ̡A�h�Ǧ^���
// �_�h�Ǧ^ "1999-01-01"
function FindLastRead($project, $orig_projects) {
	for($i=0;$i<sizeof($orig_projects);$i++) {
		if ($project==$orig_projects[$i]->getprojectid()) {
			if ($orig_projects[$i]->getlastread()!="") {
				return $orig_projects[$i]->getlastread();
			}else{
				return "1999-01-01";
			}
		}else{
			continue;
		}
	}
	// �S���b�}�C�����A�ҥH�O�s���{���A�Ǧ^1999-01-01
	return "1999-01-01";
}

if (!isset($_POST['user_id'])) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "user_id");
}

if ($_SESSION[SESSION_PREFIX.'uid'] != $_POST['user_id']) {
	if (!($GLOBALS['Privilege'] & $GLOBALS['can_admin_user'])) {
		WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
		ErrorPrintOut("no_privilege");
	}
} else {
	if (!($GLOBALS['Privilege'] & $GLOBALS['can_edit_selfdata'])) {
		WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
		ErrorPrintOut("no_privilege");
	}
}

$change_password = 0;
if (isset($_POST['password1'])) {
	if (($_POST['password1'] != "12345678") || ($_POST['password2'] != "AlexWang")) {
		$change_password = 1;
	}
}

if (($change_password) && 
	($_POST['password1'] != $_POST['password2']) ) {
	ErrorPrintOut("password_not_match");
}

if ($_SESSION[SESSION_PREFIX.'uid'] != 0) {
	if ($_POST['email'] && !IsEmailAddress($_POST['email'])) {
		ErrorPrintOut("wrong_format", "email");
	}
} else {
	if (!IsEmailAddress($_POST['email'])) {
		ErrorPrintOut("wrong_format", "email");
	}
}

if (isset($_POST['enable_login'])) {
	if ($_POST['enable_login'] == 1) {
		$account_disabled = "account_disabled='f',";
	} else {
		$account_disabled = "account_disabled='t',";
	}
} else {
	$account_disabled="";
}

$send_email = "";

// �p�G���]�K�X�~�n�ק�K�X
if ($change_password == 0) {
	$update_user_sql = "update ".$GLOBALS['BR_user_table']." set email='".$_POST['email']."',
		realname='".$_POST['realname']."', language='".$_POST['language']."', $account_disabled
		group_id='".$_POST['group_id']."' where user_id='".$_POST['user_id']."'";
} else {
	$update_user_sql = "update ".$GLOBALS['BR_user_table']." set email='".$_POST['email']."', password='".md5($_POST['password1'])."',
		realname='".$_POST['realname']."', language='".$_POST['language']."', $account_disabled
		group_id='".$_POST['group_id']."' where user_id='".$_POST['user_id']."'";
	
	if (($_POST['enable_login'] == 1) || ($_POST['user_id'] == 0)) {
		$send_email = "password";
	}		
}

// Get original username in order to send email.
$get_user_sql = "select username, account_disabled from ".$GLOBALS['BR_user_table']." where user_id='".$_POST['user_id']."'";
$user_result = $GLOBALS['connection']->Execute($get_user_sql);
if ($user_result->Recordcount() != 1) {
	ErrorPrintOut("no_such_xxx", "user");
}
$username = $user_result->fields["username"];

// �}�l"���"
$GLOBALS['connection']->StartTrans();
$GLOBALS['connection']->Execute($update_user_sql) or DBError(__FILE__.":".__LINE__);

if (($_POST['user_id'] != 0) && 
	( ($_SESSION[SESSION_PREFIX.'uid'] == 0) || 
	  ($GLOBALS['Privilege'] & $GLOBALS['can_admin_user']) ) ) {
	// �O���e�i�HŪ�������ǵ{���A�Ψ�̫�ɶ�
	$all_access_sql = "select * from ".$GLOBALS['BR_proj_access_table']." where user_id='".$_POST['user_id']."'";
	$all_access_result = $GLOBALS['connection']->Execute($all_access_sql) or DBError(__FILE__.":".__LINE__);

	$orig_projects=array();
	while ($row = $all_access_result->FetchRow()) {
		$project_id = $row["project_id"];
		$last_read = $row["last_read"];
		$new_project = new projectclass;
		$new_project->setprojectid($project_id);
		$new_project->setlastread($last_read);;
		array_unshift($orig_projects, $new_project);
	}

	// ���o�{�����{���Q�װϼƶq
	$project_sql = "select * from ".$GLOBALS['BR_project_table'];
	$project_result = $GLOBALS['connection']->Execute($project_sql) or DBError(__FILE__.":".__LINE__);
	$count_project = $project_result->Recordcount();
	
	$delete_access_sql = "delete from ".$GLOBALS['BR_proj_access_table']." where user_id='".$_POST['user_id']."'";
	$GLOBALS['connection']->Execute($delete_access_sql) or DBError(__FILE__.":".__LINE__);
	
	// �p�G���]�w�ϥΪ̥i�HŪ�Ӫ��A�h�s�W�ϥΪ�
	for ($i=0; $i<$count_project; $i++) {
		$project_arg = "project".$i;
		if (isset($_POST[$project_arg])){
			
			$access_sql="insert into ".$GLOBALS['BR_proj_access_table']."(user_id, project_id, last_read) 
				values('".$_POST['user_id']."', ".$_POST[$project_arg].", '".FindLastRead($_POST[$project_arg], $orig_projects)."')";

			$GLOBALS['connection']->Execute($access_sql) or DBError(__FILE__.":".__LINE__);
		}
	}
} 

$GLOBALS['connection']->CompleteTrans();

if ($_SESSION[SESSION_PREFIX.'uid'] == $_POST['user_id']) {
	unset($_SESSION[SESSION_PREFIX.'language']);
}

if ($send_email) {
	LoadingTimerShow();
	SendUpdateUserEamil($username, $_POST['password1'], $send_email);
	LoadingTimerHide();
}

if (($_SESSION[SESSION_PREFIX.'uid'] == 0) || ($GLOBALS['Privilege'] & $GLOBALS['can_admin_user'])) {
	WriteSyslog("info", "syslog_edit_xxx", "user", $username);
	FinishPrintOut("user_admin.php?user_type=".$_POST['user_type'], "finish_update", "user", 0);
} else {
	FinishPrintOut("../system/system.php", "finish_update", "user", 0);
}
?>

