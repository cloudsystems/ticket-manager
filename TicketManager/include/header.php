<?php
/* Copyright 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: header.php,v 1.27 2010/07/27 09:24:17 alex Exp $
 *
 */
session_start();
ini_set('include_path', ".".PATH_SEPARATOR."include".PATH_SEPARATOR."../include".PATH_SEPARATOR.ini_get('include_path'));
include_once("misc.php");
include_once("db.php");
include_once("group_function.php");
include_once("error.php");
include_once("string_function.php");
include_once("auth.php");

if (isset($_SESSION[SESSION_PREFIX.'back_array']) && ($_GET['error_back'] != 1)) {
	unset($_SESSION[SESSION_PREFIX.'back_array']);
}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/alexwang/style.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/css/styles.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/css/global.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	<link href="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/css/bootstrap.css?<?php echo $SYSTEM['version']?>" rel="stylesheet" type="text/css">
	
	<title>
<?php
echo $SYSTEM['program_name'];
if (isset($_SESSION[SESSION_PREFIX.'uid']) && isset($_SESSION[SESSION_PREFIX.'username'])) {
   echo "--".$_SESSION[SESSION_PREFIX.'username'];
}
?>
	</title>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/string_js.php?<?php echo $SYSTEM['version'].$_SESSION[SESSION_PREFIX.'language']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/misc.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/alexwang/alexwang.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/alexwang/ajax.js" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/alexwang/misc.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/alexwang/dialog.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/alexwang/tooltip.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/bootstrap.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/bootstrap.min.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/jquery-1.9.1.min.js" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/DataTables/media/js/jquery.dataTables.min.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	<script type="text/javascript" language="javascript" src="<?php echo $GLOBALS["SYS_URL_ROOT"]?>/javascript/DataTables/media/js/DT_bootstrap.js?<?php echo $SYSTEM['version']?>" type="text/javascript"></script>
	
</head>

<body>

  <div id="content">   
            <div id="header">
                <img id="header-logo" src="/images/bbva/logo_bbva.gif"></img>
                <ul id="header-controls"><li><a href="/logout.php">Cerrar sesión</a></li></ul>
              
                
                <div id="header-subtitle">BaaS Ticket Manager</div>
            </div>


<br/>

<?php
// Function menu for logged in users
if (isset($_SESSION[SESSION_PREFIX.'uid']) && isset($_SESSION[SESSION_PREFIX.'gid']) && isset($_SESSION[SESSION_PREFIX.'username'])) {
	InitGroupPrivilege($_SESSION[SESSION_PREFIX.'gid']);
	
	
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
	}*/

	// Statistic
	echo "<li>";
	echo "<a href=\"".$GLOBALS["SYS_URL_ROOT"]."/system/system.php?page=information\" class=\"toolbar\">";
	echo "<img src=\"".$GLOBALS["SYS_URL_ROOT"]."/images/title_information.png\" align=\"middle\" border=\"0\">&nbsp;";
	echo $STRING['title_information']."</a>";
	echo "&nbsp;</li>";

	// System
	echo "<li>";
	echo "<a href=\"".$GLOBALS["SYS_URL_ROOT"]."/system/system.php\" class=\"toolbar\">";
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

