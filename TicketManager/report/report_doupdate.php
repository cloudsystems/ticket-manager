<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: report_doupdate.php,v 1.24 2010/07/27 09:24:17 alex Exp $
 *
 */
include("../include/header.php");
include("../include/project_function.php");
include("../include/user_function.php");
include("../include/status_function.php");
include("../include/customer_function.php");
include("../include/report_function.php");
include("../include/email_function.php");
include("../include/feedback_email_function.php");

AuthCheckAndLogin();

if (!($GLOBALS['Privilege'] & $GLOBALS['can_update_report'])) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_privilege");
}
$return_page = "report_update.php?project_id=".$_POST['project_id']."&report_id=".$_POST['report_id'];

if (!$_POST['project_id']) {
	ErrorPrintOut("miss_parameter", "project_id");
}
if (!$_POST['report_id']) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	ErrorPrintOut("miss_parameter", "report_id");
}
if (CheckProjectAccessable($_POST['project_id'], $_SESSION[SESSION_PREFIX.'uid']) == FALSE) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "project");
}

if (!trim($_POST['summary'])) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "no_empty", "summary");
}
if (!$_POST['status']) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "no_empty", "status");
}
if (utf8_strlen($_POST['area']) > 40) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "too_long", "area", "40");
}
if (utf8_strlen($_POST['minor_area']) > 40) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "too_long", "minor_area", "40");
}
if (utf8_strlen($_POST['version']) > 40) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "too_long", "version", "40");
}
if (utf8_strlen($_POST['fixed_in_version']) > 40) {
	ErrorPrintBackFormOut("GET", $return_page, $_POST, 
						  "too_long", "fixed_in_version", "40");
}

if (($_POST['estimated_time_year'] != 0) && 
	($_POST['estimated_time_month'] != 0) && 
	($_POST['estimated_time_day'] != 0) ) {

	$isvalid = checkdate($_POST['estimated_time_month'], $_POST['estimated_time_day'], 
						 $_POST['estimated_time_year']);
	if (!$isvalid){
		ErrorPrintBackFormOut("GET", $return_page, $_POST, 
							  "wrong_format", "estimated_time");
	}
  
	$estimated_time = $_POST['estimated_time_year']."-".$_POST['estimated_time_month']."-".$_POST['estimated_time_day'];
	$estimated_time = $GLOBALS['connection']->DBTimeStamp($estimated_time);
} else {
	$estimated_time = "";
}

for ($k=1; $k<=$_POST['count_seealso_project']; $k++) {
	$seealso = $_POST['seealso_project_id'.$k];
	
	if ( (trim($_POST['seealso'.$k]) != "") && (trim($seealso) != "")) {
		if (!preg_match('/^[0-9]*$/', $seealso)) {
			ErrorPrintBackFormOut("GET", $return_page, $_POST, 
								  "wrong_format", "see_also");
		}
	}
}

// Get the project data
$project_sql = "select * from ".$GLOBALS['BR_project_table']." where project_id='".$_POST['project_id']."'";
$project_result = $GLOBALS['connection']->Execute($project_sql) or DBError(__FILE__.":".__LINE__);
$line = $project_result->Recordcount();
if ($line != 1) {
	ErrorPrintOut("no_such_xxx", "project");
}
$project_name = $project_result->fields["project_name"];
$version_pattern = $project_result->fields["version_pattern"];

// �B�z version
if (!isset($_POST['version'])) {
	$version = "";
	$version_changed = 0;
	for ($i = 0; $i <= strlen($version_pattern); $i++) {
		if (($version_pattern{$i} == '%') || ($version_pattern{$i} == '@')) {
			if ($_POST['version'.$i] != -1) {
				$version .= $_POST['version'.$i];
				$version_changed = 1;
			}
		} else {
			$version .= $version_pattern{$i};
		}
	}
	if ($version_changed == 0) {
		$version = "";
	}
} else {
	$version = $_POST['version'];
}

// Deal with fixed in version
if (!isset($_POST['fixed_in_version'])) {
	$fixed_in_version = "";
	$version_changed = 0;
	for ($i = 0; $i <= strlen($version_pattern); $i++) {
		if (($version_pattern{$i} == '%') || ($version_pattern{$i} == '@')) {
			if ($_POST['fixed_in_version'.$i] != -1) {
				$fixed_in_version .= $_POST['fixed_in_version'.$i];
				$version_changed = 1;
			}
		} else {
			$fixed_in_version .= $version_pattern{$i};
		}
	}
	if ($version_changed == 0) {
		$fixed_in_version = "";
	}
} else {
	$fixed_in_version = $_POST['fixed_in_version'];
}

$status_array = GetStatusArray();
$userarray = GetAllUsers(1, 1);

// ���o�t�d�� Area �� minor area �� owner
$owner_sql = "select owner from ".$GLOBALS['BR_proj_area_table']." 
	where project_id='".$_POST['project_id']."' and area_name='".$_POST['area']."'";
$owner_result = $GLOBALS['connection']->Execute($owner_sql) or DBError(__FILE__.":".__LINE__);
$line = $owner_result->Recordcount();
if ($line == 1) {
	$owner = $owner_result->fields["owner"];
} else {
	$owner = -1;
}
$minor_owner_sql = "select owner from ".$GLOBALS['BR_proj_area_table']."
		where project_id='".$_POST['project_id']."' and area_name='".$_POST['minor_area']."'";
$minor_owner_result = $GLOBALS['connection']->Execute($minor_owner_sql) or DBError(__FILE__.":".__LINE__);
$line = $minor_owner_result->Recordcount();
if ($line == 1) {
	$minor_owner = $minor_owner_result->fields["owner"];
} else {
	$minor_owner = -1;
}

$statusclass = GetStatusClassByID($status_array, $_POST['status']);
if ($statusclass->getstatustype() == "closed") {
	$_POST['assign_to'] = -1;
} else {
	// �p�G�S���� assing_to �h�N assign_to �]�� area/minor owner
	if ($_POST['assign_to'] == -1) {
		if ($minor_owner) {
			$_POST['assign_to'] = $minor_owner;
		} elseif ($owner) {
			$_POST['assign_to'] = $owner;
		}
	}
}

// �h��html������,�����Ҧb�������L�@��
$_POST['type'] = htmlspecialchars($_POST['type']);
$version = htmlspecialchars($version);
$fixed_in_version = htmlspecialchars($fixed_in_version);
$_POST['area'] = htmlspecialchars($_POST['area']);
$_POST['minor_area'] = htmlspecialchars($_POST['minor_area']);
$_POST['summary'] = htmlspecialchars($_POST['summary']);

$old_report_sql = "select * from proj".$_POST['project_id']."_report_table 
		where report_id='".$_POST['report_id']."'";
$old_report_result = $GLOBALS['connection']->Execute($old_report_sql) or DBError(__FILE__.":".__LINE__);

$old_report_row = $old_report_result->FetchRow();

$log = "<p>Set Priority:";
if ($_POST['priority'] == $old_report_row['priority']) {
	$log .= $STRING[$GLOBALS['priority_array'][$_POST['priority']]].", ";
} else {
	$log .= "<font color=red>".$STRING[$GLOBALS['priority_array'][$_POST['priority']]]."</font>, ";
}
if ($_POST['status'] == $old_report_row['status']) {
	$log .= "Status:".addslashes($statusclass->getstatusname())."(".$statusclass->getstatustype()."), Assign To:";
} else {
	$log .= "Status:<font color=red>".addslashes($statusclass->getstatusname())."</font>, Assign To:";
}
if ($_POST['assign_to'] == $old_report_row['assign_to']) {
	$log .= UidToUsername($userarray, $_POST['assign_to']);
} else {
	$log .= "<font color=red>".UidToUsername($userarray, $_POST['assign_to'])."</font>";
}
if ($_POST['type'] != $old_report_row['type']) {
	$log .= "<br>Old Type: ".$STRING[$GLOBALS['type_array'][$old_report_row['type']]].", New Type: <font color=\"red\">".$STRING[$GLOBALS['type_array'][$_POST['type']]]."</font>";
}
if ($_POST['area'] != $old_report_row['area']) {
	$log .= "<br>Old Area:".$old_report_row['area'].", New Area:<font color=\"red\">".$_POST['area']."</font>";
}
if ($_POST['minor_area'] != $old_report_row['minor_area']) {
	$log .= "<br>Old minor area:".$old_report_row['minor_area'].", New minor area:<font color=\"red\">".$_POST['minor_area']."</font>";
}
if ($_POST['reported_by_customer'] != $old_report_row['reported_by_customer']) {
	$customer_array = GetAllCustomers();
	$log .= "<br>Old reported by customer:";
	$log .= GetCustomerNameFromID($customer_array, $old_report_row['reported_by_customer']);
	$log .= ", New reported by customer:<font color=\"red\">";
	$log .= GetCustomerNameFromID($customer_array, $_POST['reported_by_customer'])."</font>";
}

if (($fixed_in_version != $old_report_row['fixed_in_version']) && 
	($old_report_row['fixed_in_version'])) {
	$log .= "<br>Fixed in version changed, original fixed in version:<font color=red>".$old_report_row['fixed_in_version']."</font></p>";
}

if (trim(stripslashes($_POST['summary'])) != trim($old_report_row['summary'])){
	$log .= "<br>updated <font color=red>summary</font><br>old: \"".addslashes($old_report_row['summary'])."\"";
	$log .= "<br>new:<font color=blue>\"".$_POST['summary']."\"</font>";
}

$log=$log."</p><p>Description:</p><p>".$_POST['description']."</p>";

$GLOBALS['connection']->StartTrans();

// �B�z Also See
// ���R���Ҧ��ª� Also See�A���檺 Also See �٭n�h�O�����R
$project_array = GetAllProjects();
for ($i=0; $i<sizeof($project_array); $i++) {
	$the_project_id = $project_array[$i]->getprojectid();
	$delete_co_seealso = "delete from proj".$the_project_id."_seealso_table 
		where see_also_id='".$_POST['report_id']."' and see_also_project='".$_POST['project_id']."'";
	$GLOBALS['connection']->Execute($delete_co_seealso) or 
		DBError(__FILE__.":".__LINE__);
}
$delete_seealso = "delete from proj".$_POST['project_id']."_seealso_table
	where report_id='".$_POST['report_id']."'";

$GLOBALS['connection']->Execute($delete_seealso) or DBError(__FILE__.":".__LINE__);

// ����s�W���u�@
for ($k=1; $k<=$_POST['count_seealso_project']; $k++) {
	$seealso_project_id = $_POST['seealso_project_id'.$k];
	$seealso = $_POST['seealso'.$k];

	$seealso = str_replace(" ","",$seealso);
	$seealso = str_replace("	","",$seealso);
	$seealso_array = explode(",",$seealso);

	for($i = 0; $i<sizeof($seealso_array); $i++) {
		$seealso_array[$i] = trim($seealso_array[$i]);
		if ($seealso_array[$i] != "") {
			// ���T�{���ҿ�J�� ID
			if (is_numeric($seealso_array[$i]) == FALSE) {
				ErrorPrintBackFormOut("GET", $return_page, $_POST, 
									  "no_seealso_id", "", $seealso_array[$i]);
			}
			$is_there_id = "select * from proj".$seealso_project_id."_report_table 
				where report_id='".$seealso_array[$i]."'";

			$is_there_result = $GLOBALS['connection']->Execute($is_there_id) or 
				DBError(__FILE__.":".__LINE__);
			$is_there_line = $is_there_result->Recordcount();
			// �䤣��� ID
			if ($is_there_line != 1) {
				ErrorPrintBackFormOut("GET", $return_page, $_POST, 
									  "no_seealso_id", "", $seealso_array[$i]);
			}
			if (($seealso_project_id == $_POST['project_id']) && 
				($seealso_array[$i] == $_POST['report_id'])) {
				// No need to reference myself.
				continue;
			}

			$add_seealso = "insert into proj".$_POST['project_id']."_seealso_table(report_id, see_also_project, see_also_id)
				values('".$_POST['report_id']."','$seealso_project_id','".$seealso_array[$i]."')";

			$GLOBALS['connection']->Execute($add_seealso) or DBError(__FILE__.":".__LINE__);
		   
		   
			// ���o�ҹ����� alsosee�U�@�� ref_id
			$add_seealso="insert into proj".$seealso_project_id."_seealso_table(report_id, see_also_project, see_also_id)
				values('".$seealso_array[$i]."','".$_POST['project_id']."','".$_POST['report_id']."')";

			$GLOBALS['connection']->Execute($add_seealso) or DBError(__FILE__.":".__LINE__);

		} // end of if
	} // end of for each seealso array
} // end of for

if ($old_report_row['fixed_by'] == -1 && ($_POST['fixed_by'] != -1) && (trim($_POST['fixed_by']) != "")) {
	$fixed_date = $GLOBALS['connection']->DBTimeStamp(time());
} else if ($old_report_row['fixed_date'] != "") {
	if (($_POST['fixed_by'] == -1) || (trim($_POST['fixed_by']) == "")) {
		$fixed_date = "null";
	} else {
		$fixed_date = "'".$old_report_row['fixed_date']."'";
	}
} else {
	$fixed_date = "null";
}

if ($old_report_row['verified_by'] == -1 && ($_POST['verified_by'] != -1) && (trim($_POST['verified_by']) != "")) {
	$verified_date = $GLOBALS['connection']->DBTimeStamp(time());
} else if ($old_report_row['verified_date'] != "") {
	if (($_POST['verified_by'] == -1) || (trim($_POST['verified_by']) == "")) {
		$verified_date = "null";
	} else {
		$verified_date = "'".$old_report_row['verified_date']."'";
	}
} else {
	$verified_date = "null";
}

if ($estimated_time == "") {
	$estimated_time = "null";
}
$now = $GLOBALS['connection']->DBTimeStamp(time());
$update_sql = "update proj".$_POST['project_id']."_report_table set summary='".$_POST['summary']."',
			type=".$_POST['type'].", assign_to='".$_POST['assign_to']."', 
			priority='".$_POST['priority']."', status='".$_POST['status']."',
			fixed_by='".$_POST['fixed_by']."', fixed_date=$fixed_date,
			verified_by='".$_POST['verified_by']."', verified_date=$verified_date,
			version='$version', fixed_in_version='$fixed_in_version',
			area='".$_POST['area']."', minor_area='".$_POST['minor_area']."',
			estimated_time=$estimated_time, last_update=$now, 
			reproducibility=".$_POST['reproducibility'].",
			reported_by_customer=".$_POST['reported_by_customer']."
			where report_id='".$_POST['report_id']."'";

$GLOBALS['connection']->Execute($update_sql) or DBError(__FILE__.":".__LINE__);

// �W�Ǫ��[�ɮ׸��
if(!$_FILES['file']['tmp_name']) {
	$filename = "";
} else {
	if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
		ErrorPrintBackFormOut("GET", $return_page, $_POST, 
							  "wrong_format", "file_upload");
	}

	$org_filename = $_FILES['file']['name'];
	if (utf8_strlen($org_filename) > 252) { /* 100_filename  256-strlen("100_")=252 */
		$subname = strrchr($org_filename, ".");
		if (utf8_strlen($subname) > 251) {
			$filename = utf8_substr($org_filename, 0, 251);
		} else {
			$filename = utf8_substr($org_filename, 0, (251 - utf8_strlen($subname)) ).$subname;
		}
	} else {
		$filename = $org_filename;
	}
	if ($GLOBALS['SYS_FILE_IN_DB'] == 1) {
		$filedata = $GLOBALS['connection']->BlobEncode(fread(fopen($_FILES['file']['tmp_name'], "r"), $_FILES['file']['size']));
	} else {
		$org_filename = $filename;
		$dest_file = "upload/project".$_POST['project_id']."/".$filename;

		$num=1;
		while (file_exists($dest_file)) {
			if ($num > 100) {
				$subname = strrchr($filename, ".");
				if (utf8_strlen($subname) > 250) {
					$filename = date("U");
					$dest_file = "upload/project".$_POST['project_id']."/".$filename;
				} else {
					$filename = date("U").$subname;
					$dest_file = "upload/project".$_POST['project_id']."/".$filename;
				}				
			} else {
				$filename = $num."_".$org_filename;
				$dest_file = "upload/project".$_POST['project_id']."/".$filename;
			}
			$num++; // if previous file name existed then thy another number+_+filename
		}
		move_uploaded_file($_FILES['file']['tmp_name'], $dest_file );
	} // End of file in db
}

$now = $GLOBALS['connection']->DBTimeStamp(time());
$new_log_sql = "insert into proj".$_POST['project_id']."_report_log_table (
	report_id, user_id, post_time, description, filename, filedata) values(
	".$_POST['report_id'].", ".$_SESSION[SESSION_PREFIX.'uid'].", $now, '$log', '$filename', '$filedata');";

$GLOBALS['connection']->Execute($new_log_sql) or DBError(__FILE__.":".__LINE__);

// If status is closed and this bug is reported by customer, update feedback system
if ($statusclass->getstatustype() == "closed") {
	$closed_status = 0;
	for ($i = 0; $i < sizeof($GLOBALS['feedback_status']); $i++) {
		if (stristr($GLOBALS['feedback_status'][$i], "Closed")) {
			$closed_status = $i;
			break;
		}
	}

	$check_sql = "select feedback_report_id from proj".$_POST['project_id']."_feedback_map_table
			where internal_report_id='".$_POST['report_id']."'";
	$check_result = $GLOBALS['connection']->Execute($check_sql) or DBError(__FILE__.":".__LINE__);
	while ($row = $check_result->FetchRow()){
		$feedback_report_id = $row['feedback_report_id'];
		$feedback_sql = "select status from proj".$_POST['project_id']."_feedback_table where report_id=".$feedback_report_id;
		$feedback_result = $GLOBALS['connection']->Execute($feedback_sql) or DBError(__FILE__.":".__LINE__);
		if ($feedback_result->Recordcount() > 0) {
			if ($closed_status != $feedback_result->fields["status"]) {
				// Set status of feedback report to Closed
				$update_sql = "update proj".$_POST['project_id']."_feedback_table set
					status=".$closed_status." where report_id=".$feedback_report_id;
				$GLOBALS['connection']->Execute($update_sql) or DBError(__FILE__.":".__LINE__);

				// Get the auto message
				$mesg_sql = "select closed_description from ".$GLOBALS['BR_feedback_config_table'];
				$mesg_sql = $GLOBALS['connection']->Execute($mesg_sql) or DBError(__FILE__.":".__LINE__);
				$message = $mesg_sql->fields["closed_description"];
				if ($message == "") {
					$message = 'Your report has been closed because the report is __STATUS__.';
				}
				$message = str_replace("__STATUS__", $statusclass->getstatusname(), $message);

				$now = $GLOBALS['connection']->DBTimeStamp(time());
				$new_content_sql = "insert into proj".$_POST['project_id']."_feedback_content_table (
					report_id, internal_user_id, post_time, description) values(
					".$feedback_report_id.", 0, $now, '".$message."')";
				$GLOBALS['connection']->Execute($new_content_sql) or DBError(__FILE__.":".__LINE__);

				// Send email
				SendFeedbackReportEmail($_POST['project_id'], $feedback_report_id);
			}
		}

		
	}
}
$GLOBALS['connection']->CompleteTrans();

LoadingTimerShow();
SendReportEmail($_POST['project_id'], $_POST['report_id'], $old_report_row['assign_to']);
LoadingTimerHide();

if ($_POST['search_key']) {
	$_POST['search_key'] = stripslashes($_POST['search_key']);
}
$extra_params = GetExtraParams($_POST, "search_key, search_type, choice_filter, sort_by,sort_method,page,label");
FinishPrintOut("project_list.php?project_id=".$_POST['project_id'].$extra_params, 
			   "finish_update", "report", 0);

?>
