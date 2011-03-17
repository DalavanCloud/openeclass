<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/**
 * Index
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract Password change component
 *
 */
$require_login = true;
$helpTopic = 'Profile';
$require_valid_uid = TRUE;

include '../../include/baseTheme.php';

$nameTools = $langChangePass;
$navigation[]= array ("url"=>"../profile/profile.php", "name"=> $langModifProfile);

check_uid();
$tool_content = "";
$passurl = $urlSecure.'modules/profile/password.php';

if (isset($_POST['submit'])) {
	if (empty($_REQUEST['password_form']) || empty($_REQUEST['password_form1']) || empty($_REQUEST['old_pass'])) {
		header("location:". $passurl."?msg=2");
		exit();
	}
	if ($_REQUEST['password_form1'] !== $_REQUEST['password_form']) {
		header("location:". $passurl."?msg=1");
		exit();
	}

	//all checks ok. Change password!
	$sql = "SELECT `password` FROM `user` WHERE `user_id`=".$_SESSION["uid"]." ";
	$result = db_query($sql, $mysqlMainDb);
	$myrow = mysql_fetch_array($result);

	$old_pass = md5($_REQUEST['old_pass']) ;
	$old_pass_db = $myrow['password'];
	$new_pass = md5($_REQUEST['password_form']);

	if($old_pass == $old_pass_db) {
		$sql = "UPDATE `user` SET `password` = '$new_pass' WHERE `user_id` = ".$_SESSION["uid"]."";
		db_query($sql, $mysqlMainDb);
		header("location:". $passurl."?msg=4");
		exit();
	} else {
		header("location:". $passurl."?msg=3");
		exit();
	}

}

//Show message if exists
if(isset($_GET['msg'])) {
	$msg = $_GET['msg'];
	switch ($msg){

		case 1: {//passwords do not match
			$message = $langPassTwo;
			$urlText = "";
			$type = "caution";
			break;
		}

		case 2: { // admin tools
			$message = $langFields;
			$urlText = "";
			$type = "caution";
			break;
		}
		case 3: {//wrong old password entered
			$message = $langPassOldWrong;
			$urlText = "";
			$type = "caution";
			break;
		}

		case 4: {//password successfully changed
			$message = $langPassChanged;
			$urlText = $langHome;
			$type = "success";
			break;
		}
		default:die("invalid message id");
	}
	$tool_content .=  "\n         <p class=\"$type\">$message<br /><a href=\"$urlServer\">$urlText</a></p>";
}

if (!isset($_POST['changePass'])) {
	$tool_content .= "
	<form method=\"post\" action=\"$passurl\">
        <fieldset>
        <legend>$langPassword</legend>
	<table class=\"tbl\">
	<tr>
	   <th>$langOldPass</th>
	   <td><input type=\"password\" size=\"40\" name=\"old_pass\" value=\"\"></td>
	</tr>
	<tr>
	   <th>$langNewPass1</th>
	   <td>";
	$tool_content .= "<input type=\"password\" size=\"40\" name=\"password_form\" value=\"\"></td>
	</tr>
	<tr>
	   <th>$langNewPass2</th>
	   <td><input type=\"password\" size=\"40\" name=\"password_form1\" value=\"\"></td>
	</tr>
	<tr>
	   <th>&nbsp;</th>
	   <td><input type=\"submit\" name=\"submit\" value=\"$langModify\"></td>
	</tr>
	</table>
        </fieldset>
        </form>";
}

draw($tool_content, 1);
?>
