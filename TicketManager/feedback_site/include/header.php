<?php
/* Copyright 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: header.php,v 1.21 2009/07/07 15:13:52 alex Exp $
 *
 */
session_start();
ini_set('include_path', ".".PATH_SEPARATOR."include".PATH_SEPARATOR."../include".PATH_SEPARATOR.ini_get('include_path'));
include("db.php");
include("misc.php");
include("string_function.php");
include("error.php");
include("auth.php");

if (isset($_SESSION[SESSION_PREFIX.'feedback_back_array']) && ($_GET['error_back'] != 1)) {
	unset($_SESSION[SESSION_PREFIX.'feedback_back_array']);
}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="javascript/alexwang/style.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/css/styles.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/css/global.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/css/bootstrap.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<title>
<?php
echo $FEEDBACK_SYSTEM['feedback_system_name'];
if (isset($_SESSION[SESSION_PREFIX.'feedback_uid']) && isset($_SESSION[SESSION_PREFIX.'feedback_email'])) {
   echo "--".$_SESSION[SESSION_PREFIX.'feedback_email'];
}
?>
	</title>
	<script language="JavaScript" src="javascript/string_js.php?<?php echo $SYSTEM['version'].$_SESSION[SESSION_PREFIX.'language']?>" type="text/javascript"></script>
	<script language="JavaScript" src="javascript/misc.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script language="JavaScript" src="javascript/alexwang/alexwang.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script language="JavaScript" src="javascript/alexwang/ajax.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script language="JavaScript" src="javascript/alexwang/misc.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script language="JavaScript" src="javascript/alexwang/dialog.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script language="JavaScript" src="javascript/alexwang/tooltip.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/bootstrap.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/bootstrap.min.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/DataTables/media/js/jquery.dataTables.min.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/DataTables/media/js/DT_bootstrap.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
</head>

<body>

<?php
if (strstr($_SERVER['PHP_SELF'], "project_list.php") || 
	strstr($_SERVER['PHP_SELF'], "faq.php")) {
?>
<script language="JavaScript">
<!--
function onLocalSubmit1(form) {
	if (document.form1 && document.form1.project_id) {
		var project_idx = document.form1.project_id.selectedIndex;
		document.search_form.project_id.value = document.form1.project_id.options[project_idx].value;
	}
	return OnSubmit(form);
}
//-->
</script>
<?php
}
?>
<div id="content">   
            <div id="header">
                <img id="header-logo" src="/images/bbva/logo_bbva.gif"></img>
                <ul id="header-controls"><li><a href="/logout.php">Cerrar sesión</a></li></ul>
              
                
                <div id="header-subtitle">BaaS Ticket</div>
            </div>


<br/>
		
<?php
// Function menu for logged in users
if (isset($_SESSION[SESSION_PREFIX.'feedback_uid']) && isset($_SESSION[SESSION_PREFIX.'feedback_email'])) {


	echo "<ul class='nav nav-tabs'>";
	echo "<li><a href=\"".$GLOBALS["SYS_URL_ROOT"]."/index.php\" class=\"toolbar\">";
	echo "<img src=\"".$GLOBALS["SYS_URL_ROOT"]."/images/title_project.png\" align=\"middle\" border=\"0\">&nbsp;";
	echo $STRING['title_project_list'];
	echo "</a></li>";
	
	/*	if ( ($_SESSION[SESSION_PREFIX.'uid'] == 0) ||
	 ($GLOBALS['Privilege'] & $GLOBALS['can_see_document']) ){
	echo "<li>";
	echo "<a href=\"".$GLOBALS["SYS_URL_ROOT"]."/document/document.php\" class=\"toolbar\">\n";
	echo "<img src=\"".$GLOBALS["SYS_URL_ROOT"]."/images/title_document.png\" align=\"middle\" border=\"0\">&nbsp;";
	echo $STRING['title_document']."</a>";
	echo "&nbsp;</li>";
	}
	
	if ( ($_SESSION[SESSION_PREFIX.'uid'] == 0) ||
			($GLOBALS['Privilege'] & $GLOBALS['can_see_schedule']) ){
	echo "<li>";
	echo "<a href=\"".$GLOBALS["SYS_URL_ROOT"]."/schedule/schedule.php?init=y\" class=\"toolbar\">\n";
	echo "<img src=\"".$GLOBALS["SYS_URL_ROOT"]."/images/title_schedule.png\" align=\"middle\" border=\"0\">&nbsp;";
	echo $STRING['title_schedule']."</a>";
	echo "&nbsp;</li>";
	}
	
	// Statistic
	echo "<li>";
	echo "<a href=\"".$GLOBALS["SYS_URL_ROOT"]."/system/system.php?page=information\" class=\"toolbar\">";
	echo "<img src=\"".$GLOBALS["SYS_URL_ROOT"]."/images/title_information.png\" align=\"middle\" border=\"0\">&nbsp;";
	echo $STRING['title_information']."</a>";
	echo "&nbsp;</li>";
	*/
	// System
	echo "<li>";
	echo "<a href=\"".$GLOBALS["SYS_URL_ROOT"]."/system.php\" class=\"toolbar\">";
	echo "<img src=\"".$GLOBALS["SYS_URL_ROOT"]."/images/title_system.png\" align=\"middle\" border=\"0\">&nbsp;";
	echo $STRING['title_system']."</a>";
	echo "&nbsp;</li>";
	
}
?>
 </ul>
	<table  border="0" width="100%" cellpadding="0" cellspacing="0" >
	<tr>
	
		<td width="100%" align="center">
			<span id="search_container"></span>
		</td>
	
	</tr>
	</table>
