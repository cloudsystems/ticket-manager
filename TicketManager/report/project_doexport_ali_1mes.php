<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: project_doexport.php,v 1.13 2009/07/07 15:13:52 alex Exp $
 *
 */
session_start();
ini_set('include_path', ".".PATH_SEPARATOR."include".PATH_SEPARATOR."../PEAR".PATH_SEPARATOR."../include".PATH_SEPARATOR.ini_get('include_path'));
include_once("../include/db.php");
include_once("../include/group_function.php");
include_once("../include/misc.php");
include_once("../include/error.php");
include_once("../include/string_function.php");
include("../include/auth.php");
include("../include/user_function.php");
include("../include/status_function.php");
include("../include/customer_function.php");
include("../include/project_function.php");
include("../include/datetime_function.php");

require_once 'Spreadsheet/Excel/Writer.php';
AuthCheckAndLogin();

$selected_project=$_POST['project_id'];

if (!isset($selected_project) || ($selected_project == "")) {
	WriteSyslog("error", "syslog_miss_arg", "", __FILE__.":".__LINE__);
	include("../include/header.php");
	ErrorPrintOut("miss_parameter", "project_id");
}

// Get project data
$project_sql = "select * from ".$GLOBALS['BR_project_table']." where project_id='".$selected_project."'";
$project_result = $GLOBALS['connection']->Execute($project_sql) or DBError(__FILE__.":".__LINE__);
$project_line = $project_result->Recordcount();
if ($project_line == 1) {
	$project_name = $project_result->fields["project_name"];
}else{
	WriteSyslog("error", "syslog_not_found", "project", __FILE__.":".__LINE__);
	ErrorPrintOut("no_such_xxx", "project");
}

if (CheckProjectAccessable($selected_project, $_SESSION[SESSION_PREFIX.'uid']) == FALSE) {
	WriteSyslog("warn", "syslog_permission_denied", "", __FILE__.":".__LINE__);
	include("../include/header.php");
	ErrorPrintOut("no_such_xxx", "project");
}

// Initial parameters
if (!$_POST['sort_by']) {
	$sort_by = "report_id";
} else {
	if (false === strpos($_GET['sort_by'], ';') && false === strpos($_GET['sort_by'], ' ')) {
		$sort_by = $_GET['sort_by'];
	}
}

if (!$_POST['sort_method']) {
	$sort_method = "DESC";
} else {
	$sort_method = $_POST['sort_method'];
}
if ($sort_method != "DESC") {
	$sort_method = "ASC";
}

$_POST['search_key'] = trim($_POST['search_key']);
if ($_POST['search_key'] == "") {
	unset($_POST['search_key']);
}

if ($_POST['choice_filter'] == "") {
	$_POST['choice_filter'] = 0;
}

for ($i=0; $i<sizeof($show_column_array); $i++) {
	if (isset($_POST[$show_column_array[$i]])) {
		if ($quick_filter != "") {
			$quick_filter .= " and ";
		}
		$quick_filter .= $show_column_array[$i]."='".$_POST[$show_column_array[$i]]."'";
	}
}

$condition = ConditionByFilterSearch($_POST['choice_filter'], $_POST['label'], $_POST['search_key'], $_POST['search_type']);
//if ($condition != "") {
//	if ($quick_filter != "") {
		//$condition = "where (".$condition.") and (created_date>ADDDATE(NOW(),-15) and (".$quick_filter.")";
//	} else {
//		$condition = "where (".$condition.") and (created_date>ADDDATE(NOW(),-15)";
//	}
//} else if ($quick_filter != "") {
//	$condition = "where (".$quick_filter.") and (created_date>ADDDATE(NOW(),-15)";
//}

if ($condition != "") {
        if ($quick_filter != "") {
                $condition = "(".$condition.") and (".$quick_filter.")";
        } else {
                $condition = $condition;
        }
} else if ($quick_filter != "") {
        $condition = $quick_filter;
}
echo($condition);
$condition_dias="and created_date>ADDDATE(NOW(),INTERVAL -1 MONTH)";

// Creamos un libro de excel que sirve como nuestro espacio de trabajo.
$libro = new Spreadsheet_Excel_Writer();
$libro->setVersion(8);// Allow UTF-8
$infor_project = array();
$hoja = array();
$titulo=array();

//Formato hoja excel
$negrita =& $libro->addFormat();
$negrita->setBold();

$projects = $GLOBALS['connection']->Execute("SELECT project_id, project_name FROM project_table where project_id=".$selected_project) or DBError(__FILE__.":".__LINE__);
$num_projects = $projects->Recordcount(); 
//echo ($num_projects);
for ($j = 1; $j <= $num_projects; $j++) {
   while ($infor_project= $projects->FetchRow()){

      $project_id = $infor_project["project_id"];
      $project_name = $infor_project["project_name"];
      //echo ($project_id);
      //echo($project_name);

      if ($project_id !=1){ //Proyecto de Listado de Incidencias es el 1
	  $allsqlproy="SELECT * FROM proj".$project_id."_report_table WHERE created_date>ADDDATE(NOW(),INTERVAL -1 MONTH) $condition ORDER BY $sort_by $sort_method";
          $allposts = $GLOBALS['connection']->Execute($allsqlproy) or DBError(__FILE__.":".__LINE__);
          $num_incidencias = $allposts->Recordcount();
          if ($num_incidencias >0){
	      $userarray = GetAllUsers(1, 1);
	      $status_array = GetStatusArray();
	      $customer_array = GetAllCustomers();

	      $columns = array();
	      array_push($columns, "summary");
	      for ($i = 0; $i < sizeof($show_column_array); $i++) {
		      array_push($columns, $show_column_array[$i]);
	      }
	      $project_name= str_replace('á', 'a', $project_name);
	      $project_name= str_replace('é', 'e', $project_name);
	      $project_name= str_replace('í', 'i', $project_name);
	      $project_name= str_replace('ó', 'o', $project_name);
	      $project_name= str_replace('ú', 'u', $project_name);
	      $project_name= str_replace('Á', 'A', $project_name);
	      $project_name= str_replace('É', 'e', $project_name);
	      $project_name= str_replace('Í', 'i', $project_name);
	      $project_name= str_replace('Ó', 'o', $project_name);
	      $project_name= str_replace('Ú', 'u', $project_name);
	      $project_name= str_replace('Ñ', 'N', $project_name);
	      $project_name= str_replace('ñ', 'n', $project_name);

	      $hoja[$j]=& $libro->addWorksheet($project_name);
	      
		// Verificamos que la hoja se haya generado correctamente
	      if (PEAR::isError($hoja[$j])) {
		    die($hoja[$j]->getMessage());
	      }
		$hoja[$j]->setInputEncoding('UTF-8');

		// Creating the format
		$format_project =& $libro->addFormat();
		$format_project->setBold();
		$format_project->setSize(16);

		$format_title =& $libro->addFormat();
		$format_title->setBold();
		$format_date =& $libro->addFormat();
		$format_date->setNumFormat('YYYY/MM/DD hh:mm:ss');


		$worksheet_row = 0;
		$hoja[$j]->write($worksheet_row, 0, $project_name, $format_project);

		$worksheet_row = 2;
		$worksheet_column = 0;
		if ($_POST['show_id'] == 'Y') {
		  $hoja[$j]->write($worksheet_row, $worksheet_column,$STRING[id], $format_title);
		  $worksheet_column++;
		}
		for ($i = 0; $i < sizeof($columns); $i++) {
		    $show_column = "show_".$columns[$i];
		    if ($_POST[$show_column] == 'Y') {
		      $hoja[$j]->write($worksheet_row, $worksheet_column, $STRING[$columns[$i]], $format_title);
		      $worksheet_column++;
		    }
		  }
		$worksheet_row++;
		while ($row = $allposts->FetchRow()) {
		  $worksheet_row++;
		  $worksheet_column = 0;
		  $status = GetStatusClassByID($status_array, $row['status']);
		  if ($_POST['show_id'] == 'Y') {
			  $hoja[$j]->write($worksheet_row, $worksheet_column, $row['report_id']);
			  $worksheet_column++;
		  }
		  for ($i = 0; $i < sizeof($columns); $i++) {
			  $show_column = "show_".$columns[$i];
			  if ($_POST[$show_column] == 'Y') {
				  $column_value = $row[$columns[$i]];

				  if ($columns[$i] == "summary") {
					  $value = $column_value;
					  $value = str_replace("&lt;", '<', $value);
					  $value = str_replace("&gt;", '>', $value);
					  $value = str_replace("&quot;", '"', $value);
					  $value = str_replace("&amp;", '&', $value);

				  } elseif ($columns[$i] == "priority") {
					  $value = $STRING[$GLOBALS['priority_array'][$column_value]];
				  } elseif ($columns[$i] == "type") {
					  $value = $STRING[$GLOBALS['type_array'][$column_value]];
				  } elseif ($columns[$i] == "status") {
					  $status = GetStatusClassByID($status_array, $column_value);
					  if ($status) {
						  $value = $status->getstatusname();
					  } else {
						  $value = "";
					  }
					  
				  } elseif ($columns[$i] == "reported_by_customer") {
					  $value = GetCustomerNameFromID($customer_array, $column_value);
					  $titulo[$n]='Reportado por el cliente';
					  $n++;
				  } elseif (($columns[$i] == "reported_by") || ($columns[$i] == "assign_to") || 
						    ($columns[$i] == "fixed_by") || ($columns[$i] == "verified_by") ) {
					    $value = UidToUsername($userarray, $column_value);
				  } elseif (($columns[$i] == "created_date") || ($columns[$i] == "fixed_date") ||
						    ($columns[$i] == "verified_date")|| ($columns[$i] == "estimated_time")) {
    
					  if ($column_value != "") {
						  $value = $allposts->UserTimeStamp($column_value, "U"); // seconds since January 1 1970 00:00:00 GMT
						  $value = $value + $allposts->UserTimeStamp($column_value, "Z"); // Add timezone
						  // Calculate the number of days since December 30 1899 (Excel's day zero)
						  $value = ($value/86400) + 25569; // 25569 is Jan. 1, 1970
					  }
					  $format = $format_date;
				  } else {
					  $value = $column_value;
				  }
				  if (isset($format)) {
					  $hoja[$j]->write($worksheet_row, $worksheet_column, $value, $format);
				  } else {
					  $hoja[$j]->write($worksheet_row, $worksheet_column, $value);
				  }
				  unset($format);
				  $worksheet_column++;
			    } /* end of show column */
			} /* for each column */

		    }// end of for each report
		    }
            }//Si está vaía el nº de incidencias
   }//for proyecto
   $libro->send('Informe_Ultimo_Mes.xls');
   $libro->close();
}

?>

