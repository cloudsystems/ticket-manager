<?php
/* Copyright c 2003-2004 Wang, Chun-Pin All rights reserved.
 *
 * Version:	$Id: logout.php,v 1.4 2008/11/28 10:37:53 alex Exp $
 *
 */
include("include/header.php");

/// ��s�ϥΪ�Ū���ݪ������(��s project �� last_read ���)
$board_read = $_SESSION[SESSION_PREFIX.'board_read'];
if (sizeof($board_read) != 0){
	$now = $GLOBALS['connection']->DBTimeStamp(time());
	for ($i=0; $i<sizeof($board_read); $i++){
		$lastread_sql="update ".$GLOBALS['BR_proj_access_table']." set last_read=$now
			where user_id='".$_SESSION[SESSION_PREFIX.'uid']."' and project_id='".$board_read[$i]."'";
		$GLOBALS['connection']->Execute($lastread_sql) or DBError(__FILE__.":".__LINE__);
	}
}

unset($_SESSION[SESSION_PREFIX.'uid']);
unset($_SESSION[SESSION_PREFIX.'username']);
unset($_SESSION[SESSION_PREFIX.'gid']);
unset($_SESSION[SESSION_PREFIX.'board_read']);

echo "<h2 align=center><a href=index.php>Back to Index</a></h2>"; 
echo "<script>";
echo "location.href = \"index.php\";";
echo "</script>";
include("include/tail.php");
?>