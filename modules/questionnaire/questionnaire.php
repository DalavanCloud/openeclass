<?php
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
        	    Yannis Exidaridis <jexi@noc.uoa.gr> 
      		    Alexandros Diamantidis <adia@noc.uoa.gr> 

        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	questionnaire.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the questionnaire tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights - to create surveys.

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
==============================================================================
*/

$require_login = TRUE;
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Questionnaire';
include '../../include/baseTheme.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_QUESTIONNAIRE');
/**************************************/

$nameTools = $langQuestionnaire;
$tool_content = "";

$head_content = '
<script>
function confirmation ()
{
    if (confirm("'.$langDelConf.'"))
        {return true;}
    else
        {return false;}
}
</script>
';


if (isset($visibility)) {
		switch ($visibility) {

// activate / dectivate surveys
		case 'sactivate':
					$sql = "UPDATE survey SET active='1' WHERE sid='".mysql_real_escape_string($_GET['sid'])."'";
					$result = db_query($sql,$currentCourseID);
					$GLOBALS["tool_content"] .= $GLOBALS["langSurveyActivated"];
					break;
		case 'sdeactivate':
						$sql = "UPDATE survey SET active='0' WHERE sid='".mysql_real_escape_string($_GET['sid'])."'";
						$result = db_query($sql,$currentCourseID);
						$GLOBALS["tool_content"] .= $GLOBALS["langSurveyDeactivated"];
					break;

// activate / dectivate polls
		case 'activate':  
						$sql = "UPDATE poll SET active='1' WHERE pid='".mysql_real_escape_string($_GET['pid'])."'";
						$result = db_query($sql,$currentCourseID);
						$GLOBALS["tool_content"] .= $GLOBALS["langPollActivated"]."<br><br>";
						break;
		case 'deactivate':  
					$sql = "UPDATE poll SET active='0' WHERE pid='".mysql_real_escape_string($_GET['pid'])."'";
					$result = db_query($sql, $currentCourseID);
					$GLOBALS["tool_content"] .= $GLOBALS["langPollDeactivated"]."<br><br>";
					break;
		}
}

// delete polls
if (isset($delete) and $delete='yes')  {
				$pid = intval($_GET['pid']);
				db_query("DELETE FROM poll_question_answer WHERE pqid IN
					(SELECT pqid FROM poll_question WHERE pid=$pid)");
				db_query("DELETE FROM poll WHERE pid=$pid");
				db_query("DELETE FROM poll_question WHERE pid='$pid'");
				db_query("DELETE FROM poll_answer_record WHERE pid='$pid'");	
        $GLOBALS["tool_content"] .= "<p>".$GLOBALS["langPollDeleted"]."</p>";
				draw($tool_content, 2, ' ', $head_content);
				exit();
}
/*
// delete surveys
if (isset($sdelete) and $sdelete='yes') {
				db_query("DELETE FROM survey WHERE sid=".mysql_real_escape_string($_GET['sid']));
        $sd = mysql_fetch_array(db_query("SELECT aid FROM survey_answer
                                        WHERE sid=".mysql_real_escape_string($_GET['sid'])));
        db_query("DELETE FROM survey_answer_record WHERE aid='$sd[aid]'");
        db_query("DELETE FROM survey_answer WHERE sid=".mysql_real_escape_string($_GET['sid']));
        $sd = mysql_fetch_array(db_query("SELECT sqid FROM survey_question
                                        WHERE sid=".mysql_real_escape_string($_GET['sid'])));
        db_query("DELETE FROM survey_question_answer WHERE sqid='$sd[sqid]'");
        db_query("DELETE FROM survey_question WHERE sid=".mysql_real_escape_string($_GET['sid']));

       $GLOBALS["tool_content"] .= "<p>".$GLOBALS["langSurveyDeleted"]."</p>";
       draw($tool_content, 2, ' ', $head_content);
			 exit();
}
*/
//$tool_content .= "<p><b>$langNamesSurvey</b></p><br>";
//printSurveys();

$tool_content .= "
    <table class='Deps' width='99%'>
    <tbody>
    <tr>
      <th><b>$langSurveys</b></th>
      <td>";
	  
      if ($is_adminOfCourse) {
        $tool_content .= "<a href='addpoll.php'>$langCreatePoll</a>";
      } else {
        $tool_content .= "&nbsp";
      }

$tool_content .= "</td>
    </tr>
    </tbody>
    </table>
    <br>";
printPolls();
	
draw($tool_content, 2, ' ', $head_content);


	
 /***************************************************************************************************
 * printSurveys()
 ****************************************************************************************************/
 
/* Apenergopoihsame ta Surveys
	function printSurveys() {
 		global $tool_content, $currentCourse, $langSurveyNone,
 			$langYes, $langCreateSurvey, $langName, $langSurveyCreator, 
 			$langSurveyStart, $langSurveyEnd, $langType, $langCreate,
 			$langSurveyOperations, $is_adminOfCourse, $langSurveysActive, $mysqlMainDb, $langActions, 
 			$langSurveyMC, $langEdit, $langDelete, $langActivate, $langDeactivate, $langSurveysInactive, $langParticipate, 
 			$langHasParticipated, $uid;

		$survey_check = 0;
		$result = mysql_list_tables($currentCourse);
		while ($row = mysql_fetch_row($result)) {
			if ($row[0] == 'survey') {
		 		$result = db_query("select * from survey", $currentCourse);
				$num_rows = mysql_num_rows($result);
				if ($num_rows > 0)
		 			++$survey_check;
			}
		}
		if (!$survey_check) {
			$tool_content .= "<p class='alert1'>".$langSurveyNone . "</p><br>";
			if ($is_adminOfCourse) 
				$tool_content .= '<a href="addsurvey.php?UseCase=0">'.$langCreateSurvey.'</a><br><br>  ';
			}
		else {
			if ($is_adminOfCourse) 
				$tool_content .= '<b><a href="addsurvey.php?UseCase=0">'.$langCreateSurvey.'</a></b><br><br>  ';
			
			// Print active surveys 
			$tool_content .= <<<cData
				<table border="0" width="99%"><thead><tr>
				<th>$langName</th>
				<th>$langSurveyCreator</th>
				<th>$langCreate</th>
				<th>$langSurveyStart</th>
				<th>$langSurveyEnd</th>
				<th>$langType</th>
cData;
				
				if ($is_adminOfCourse) {
					$tool_content .= "<th colspan='2'>$langActions</th>";
				} else {
					$tool_content .= "<th>$langParticipate</th>";
				}
				$tool_content .= "</tr></thead><tbody>";
			
			$active_surveys = db_query("select * from survey", $currentCourse);
				
			while ($theSurvey = mysql_fetch_array($active_surveys)) {	
				
				$visibility = $theSurvey["active"];
				
				if (($visibility)||($is_adminOfCourse)) {
				
					if ($visibility) {
						$visibility_css = " ";
						$visibility_gif = "invisible";
						$visibility_func = "sdeactivate";
					} else {
						$visibility_css = " class=\"invisible\"";
						$visibility_gif = "visible";
						$visibility_func = "sactivate";						
					}
					
					$creator_id = $theSurvey["creator_id"];
					$survey_creator = db_query("SELECT nom,prenom FROM user 
									WHERE user_id='$creator_id'", $mysqlMainDb);
					$theCreator = mysql_fetch_array($survey_creator);
					
					$sid = $theSurvey["sid"];
					$answers = db_query("SELECT * FROM survey_answer WHERE sid='$sid'", $currentCourse);
					$countAnswers = mysql_num_rows($answers);
					
					if ($is_adminOfCourse) { 
						$tool_content .= "\n<tr><td><a href=\"surveyresults.php?sid=". 
						$sid ."&type=" . $theSurvey["type"]."\">" . $theSurvey["name"] .
						"</a></td>";
					} else {
						$tool_content .= "<tr><td>" . $theSurvey["name"] . "</td>";
					}	
						
					$tool_content .= "<td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["creation_date"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["start_date"] . "</td>";
					$tool_content .= "<td>" . $theSurvey["end_date"] . "</td>";
					
					if ($theSurvey["type"] == 1) {
						$tool_content .= "<td>" . $langSurveyMC . "</td>";
					} else {
						$tool_content .= "<td>" . $langSurveyFillText . "</td>";
					}
					if ($is_adminOfCourse)   {
						$tool_content .= "<td align=center>".
						"<a href='$_SERVER[PHP_SELF]?sdelete=yes&sid={$sid}' onClick='return confirmation();'>
						<img src='../../template/classic/img/delete.gif' border='0' title='$langDelete'></a>&nbsp".
						"<a href='$_SERVER[PHP_SELF]?visibility=$visibility_func&sid={$sid}'>
						<img src='../../template/classic/img/".$visibility_gif.".gif' border='0'></a>  ".
							"</td></tr>\n";
					} else {
							$thesid = $theSurvey["sid"];
							$has_participated = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM survey_answer 
										WHERE creator_id='$uid' AND sid='$thesid'"));
						if ($has_participated[0] == 0) {
							$tool_content .= "<td align='center'><a href='surveyparticipate.php?UseCase=1&sid=". $sid ."'>";
							$tool_content .= $langYes;
							$tool_content .= "</a></td></tr>";
						} else {
							$tool_content .= "<td>".$langHasParticipated."</td></tr>";
						}
					}
				}
			}
		$tool_content .= "</table><br>";
		}
	}
*/




	
 /***************************************************************************************************
 * printPolls()
 ****************************************************************************************************/
	function printPolls() {
		global $tool_content, $currentCourse, $langCreatePoll, $langPollsActive, 
			$langYes, $langName, $langPollCreator, $langPollCreation, $langPollStart, 
			$langPollEnd, $langPollOperations, $langPollNone, $is_adminOfCourse, $langSurveys,
			$langNamesSurvey, $mysqlMainDb, $langEdit, $langDelete, $langActions,
			$langDeactivate, $langPollsInactive, $langActivate, $langParticipate, 
			$user_id, $langHasParticipated, $uid;
		
		$poll_check = 0;
		$result = mysql_list_tables($currentCourse);
		while ($row = mysql_fetch_row($result)) {
			if ($row[0] == 'poll') {
		 		$result = db_query("select * from poll", $currentCourse);
				$num_rows = mysql_num_rows($result);
				if ($num_rows > 0)
		 			++$poll_check;
			}
		}
		if (!$poll_check) {
			$tool_content .= "<p class='alert1'>".$langPollNone . "</p><br>";
			}
		else {
		
			// Print active polls 
			$tool_content .= <<<cData

      <!--<b>$langPollsActive</b>-->
      <table border="0" width="99%">
      <tbody>
      <tr>
        <th width='1'>&nbsp</th>
        <th class='left'>$langName</th>
        <th width='150' class='left'>$langPollCreator</th>
        <th width='30'>$langPollCreation</th>
        <th width='30'>$langPollStart</th>
        <th width='30'>$langPollEnd</th>
cData;
				
				if ($is_adminOfCourse) {
 					$tool_content .= "
        <th colspan='2' width='30'>$langActions</th>";
				} else {
					$tool_content .= "
        <th width='30'>$langParticipate</th>";
					}
				
			$tool_content .= "
      </tr>
      ";
			$active_polls = db_query("SELECT * FROM poll", $currentCourse);
			$index_aa = 1;
			while ($thepoll = mysql_fetch_array($active_polls)) {	
				$visibility = $thepoll["active"];
				
		if (($visibility)||($is_adminOfCourse)) {
				if ($visibility) {
					$visibility_css = " ";
					$visibility_gif = "invisible";
					$visibility_func = "deactivate";
				} else {
					$visibility_css = " class=\"invisible\"";
					$visibility_gif = "visible";
					$visibility_func = "activate";
				}
				
				$creator_id = $thepoll["creator_id"];
				
				$poll_creator = db_query("SELECT nom,prenom FROM user 
									WHERE user_id='$creator_id'", $mysqlMainDb);
				$theCreator = mysql_fetch_array($poll_creator);
				
				$pid = $thepoll["pid"];
				$answers = db_query("
				select * from poll_answer_record where pid='$pid'", $currentCourse);
				$countAnswers = mysql_num_rows($answers);
				
				if ($is_adminOfCourse) { 
					$tool_content .= "<tr><td colspan='2'><small>$index_aa</small>&nbsp;<a href='pollresults.php?pid=$pid'>$thepoll[name]</a></td>";
				} else {
					$tool_content .= "
      <tr".$visibility_css.">
        <td colspan='2'><small>$index_aa.</small>&nbsp;" . $thepoll["name"] . "</td>";
				}	
					
				$tool_content .= "
        <td>" . $theCreator["nom"]. " " . $theCreator["prenom"] . "</td>";
				$tool_content .= "
        <td align='center'>" . $thepoll["creation_date"] . "</td>";
				$tool_content .= "
        <td align='center'>" . $thepoll["start_date"] . "</td>";
				$tool_content .= "
        <td align='center'>" . $thepoll["end_date"] . "</td>";
				
				if ($is_adminOfCourse) 
					$tool_content .= "
        <td align='center'>
          <a href='$_SERVER[PHP_SELF]?delete=yes&pid={$pid}' onClick='return confirmation();'><img src='../../template/classic/img/delete.gif' title='$langDelete' border='0'></a>&nbsp;
          <a href='$_SERVER[PHP_SELF]?visibility=$visibility_func&pid={$pid}'><img src='../../template/classic/img/".$visibility_gif.".gif' border='0'></a>
        </td>
      </tr>";
				else {
					$thepid = $thepoll["pid"];
					$has_participated = mysql_fetch_array(
					mysql_query("SELECT COUNT(*) FROM poll_answer_record where user_id='$uid' AND pid='$thepid'"));
					if ( $has_participated[0] == 0) {
						$tool_content .= "
        <td align='center'><a href='pollparticipate.php?UseCase=1&pid=". $pid ."'>";
						$tool_content .= $langYes;
						$tool_content .= "</a></td>
      </tr>";
					} else {
						$tool_content .= "
        <td>".$langHasParticipated."</td>
      </tr>";
						}
					}
				}
			$index_aa ++;
			}
			$tool_content .= "
      </tbody>
      </table>
      <br>";
		}
	}
?>
