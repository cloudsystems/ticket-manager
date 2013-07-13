<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: report_delete.php,v 1.10 2009/04/18 16:16:08 alex Exp $
 *
 */
include("../include/header.php");
include("../include/project_function.php");

AuthCheckAndLogin();

if (!($GLOBALS['Privilege'] & $GLOBALS['can_delete_report'])) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_privilege");
}
if (!$_GET['project_id']) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "project_id");
}
if (!$_GET['report_id']) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "report_id");
}
if (CheckProjectAccessable($_GET['project_id'], $_SESSION[SESSION_PREFIX.'uid']) == FALSE) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "project");
}
$sql = "select summary from proj".$_GET['project_id']."_report_table where report_id='".$_GET['report_id']."'";
$result = $GLOBALS['connection']->Execute($sql) or DBError(__FILE__.":".__LINE__);
if ($result->Recordcount() != 1) {
	WriteSyslog("error", "syslog_not_found", "report", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "report");
}
$summary = $result->fields["summary"];

$GLOBALS['connection']->StartTrans();

if ($GLOBALS['SYS_FILE_IN_DB'] != 1) {
	// �R���W�Ǫ��ɮ�
	// ��X�Ҧ��W���ɪ����
	$get_log_sql = "select * from proj".$_GET['project_id']."_report_log_table 
				where report_id='".$_GET['report_id']."' and filename!=''";
	$log_result = $GLOBALS['connection']->Execute($get_log_sql) or DBError(__FILE__.":".__LINE__);
	while ($row = $log_result->FetchRow()) {
		$filename = $row["filename"];
		// �R���W�Ǫ��ɮ�
		if ($filename != "") {
			if (!unlink($filename )) { echo "Failed to remove old file."; }
		}
	}// end of while
}

// ���R�����檺 also see,�]�n�h�ӷ��R
$other_seealso_table = "select see_also_project from proj".$_GET['project_id']."_seealso_table where report_id='".$_GET['report_id']."'
		and see_also_project!='".$_GET['project_id']."' group by see_also_project";
$other_seealso_result = $GLOBALS['connection']->Execute($other_seealso_table) or DBError(__FILE__.":".__LINE__);
$line = $other_seealso_result->Recordcount();

// �b�W����X����A�A��X�Ӫ��{���W�� table name
// �A�H�{�������h�o�ǵ{���� table �R���M���������� also see
if ($line > 0) {
	while($row = $other_seealso_result->FetchRow()) {
		$other_project_id = $row["see_also_project"];
		$delete_other_alsosee="delete from proj".$other_project_id."_seealso_table 
				where see_also_id='".$_GET['report_id']."' and see_also_project='".$_GET['project_id']."'";
		$GLOBALS['connection']->Execute($delete_other_alsosee) or DBError(__FILE__.":".__LINE__);
	}
}
$seealso_sql = "delete from proj".$_GET['project_id']."_seealso_table 
		where report_id='".$_GET['report_id']."'";
$co_alsosee_sql = "delete from proj".$_GET['project_id']."_seealso_table 
		where report_id='".$_GET['report_id']."' and see_also_project='".$_GET['project_id']."'";
$report_delete_sql = "delete from proj".$_GET['project_id']."_report_table where report_id='".$_GET['report_id']."'";
$log_delete_sql = "delete from proj".$_GET['project_id']."_report_log_table where report_id='".$_GET['report_id']."'";
$map_delete_sql = "delete from proj".$_GET['project_id']."_feedback_map_table where internal_report_id='".$_GET['report_id']."'";
	 
$GLOBALS['connection']->Execute($seealso_sql) or DBError(__FILE__.":".__LINE__);
$GLOBALS['connection']->Execute($co_alsosee_sql) or DBError(__FILE__.":".__LINE__);
$GLOBALS['connection']->Execute($report_delete_sql) or DBError(__FILE__.":".__LINE__);
$GLOBALS['connection']->Execute($log_delete_sql) or DBError(__FILE__.":".__LINE__);
$GLOBALS['connection']->Execute($map_delete_sql) or DBError(__FILE__.":".__LINE__);
	 
$GLOBALS['connection']->CompleteTrans();
	 
WriteSyslog("info", "syslog_delete_xxx", "report", $summary);

$extra_params = GetExtraParams($_GET, "search_key,search_type,choice_filter,sort_by,sort_method,page,assign_to");

FinishPrintOut("project_list.php?project_id=".$_GET['project_id'].$extra_params, "finish_delete", "report");

?>
