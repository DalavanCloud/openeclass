<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

/*
 * Open eClass 3.x standard stuff
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = FALSE;
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
include '../group/group_functions.php';

include_once("./config.php");
include("functions.php"); 

if (isset($_GET['forum'])) {
	$forum = intval($_GET['forum']);
}
if (isset($_GET['topic'])) {
	$topic = intval($_GET['topic']);
} else {
	$topic = '';
}

$result = db_query("SELECT id, name FROM forum WHERE id = $forum AND course_id = $cours_id");
$myrow = mysql_fetch_array($result);

$forum_name = $myrow["name"];
$forum_id = $myrow["id"];

$is_member = false;
$group_id = init_forum_group_info($forum_id);

$nameTools = $langNewTopic;
$navigation[]= array('url' => "index.php?course=$code_cours", 'name' => $langForums);
$navigation[]= array('url' => "viewforum.php?course=$code_cours&amp;forum=$forum_id", 'name' => $forum_name);

if (!does_exists($forum_id, "forum")) {
	$tool_content .= "<div class='caution'>$langErrorPost</div>";
	draw($tool_content, 2);
	exit;
}

if (isset($_POST['submit'])) {
	$subject = trim($_POST['subject']);
	$message = trim($_POST['message']);
	if (empty($message) or empty($subject)) {
		header("Location: viewforum.php?course=$code_cours&forum=$forum_id&empty=true");
		exit;
	}	
	$message = purify($message);        
	$poster_ip = $_SERVER['REMOTE_ADDR'];
	$time = date("Y-m-d H:i");
	
	$sql = "INSERT INTO forum_topics (title, poster_id, forum_id, topic_time)
			VALUES (" . autoquote($subject) . ", $uid, $forum_id, '$time')";
	$result = db_query($sql);

	$topic_id = mysql_insert_id();
	$sql = "INSERT INTO forum_posts (topic_id, forum_id, post_text, poster_id, post_time, poster_ip)
			VALUES ($topic_id, $forum_id, ".autoquote($message).", $uid, '$time', '$poster_ip')";
	$result = db_query($sql);
	
        $post_id = mysql_insert_id();
        db_query("UPDATE forum_topics
                    SET last_post_id = $post_id
                WHERE id = $topic_id 
                AND forum_id = $forum_id");
                        
	db_query("UPDATE forum
                    SET num_topics = num_topics+1, 
                    num_posts = num_posts+1,                    
                    last_post_id = $post_id
		WHERE id = $forum_id");
        
	$topic = $topic_id;
	$total_forum = get_total_topics($forum_id);
	$total_topic = get_total_posts($topic, "topic")-1;  
	// subtract 1 because we want the number of replies, not the number of posts.	

	// --------------------------------
	// notify users 
	// --------------------------------
	$subject_notify = "$logo - $langNewForumNotify";
	$category_id = forum_category($forum_id);
	$cat_name = category_name($category_id);
	$sql = db_query("SELECT DISTINCT user_id FROM forum_notify 
			WHERE (forum_id = $forum_id OR cat_id = $category_id) 
			AND notify_sent = 1 AND course_id = $cours_id AND user_id != $uid", $mysqlMainDb);
	$c = course_code_to_title($currentCourseID);
        $name = uid_to_name($uid);
	$forum_message = "-------- $langBodyMessage ($langSender: $name)\n$message--------";
	$plain_forum_message = html2text($forum_message);
	$body_topic_notify = "$langBodyForumNotify $langInForums '$forum_name' $langInCat '$cat_name' $langTo $langCourseS '$c' <br /><br />$forum_message<br /><br />$gunet<br /><a href='{$urlServer}courses/$currentCourseID'>{$urlServer}courses/$currentCourseID</a>";
	$plain_body_topic_notify = "$langBodyForumNotify $langInForums '$forum_name' $langInCat '$cat_name' $langTo $langCourseS '$c' \n\n$plain_forum_message \n\n$gunet\n<a href='{$urlServer}courses/$currentCourseID'>{$urlServer}courses/$currentCourseID</a>";
	while ($r = mysql_fetch_array($sql)) {
                if (get_user_email_notification($r['user_id'], $cours_id)) {
                        $linkhere = "&nbsp;<a href='${urlServer}modules/profile/emailunsubscribe.php?cid=$cours_id'>$langHere</a>.";
                        $unsubscribe = "<br /><br />".sprintf($langLinkUnsubscribe, $title);            
                        $plain_body_topic_notify .= $unsubscribe.$linkhere;
                        $body_topic_notify .= $unsubscribe.$linkhere;
                        $emailaddr = uid_to_email($r['user_id']);
                        send_mail_multipart('', '', '', $emailaddr, $subject_notify, $plain_body_topic_notify, $body_topic_notify, $charset);
                }
	}
	// end of notification
	
	$tool_content .= "<p class='success'>$langStored</p>
		<p class='back'>&laquo; <a href='viewtopic.php?course=$code_cours&amp;topic=$topic_id&amp;forum=$forum_id&amp;$total_topic'>$langReturnMessages</a></p>
		<p class='back'>&laquo; <a href='viewforum.php?course=$code_cours&amp;forum=$forum_id'>$langReturnTopic</a></p>"; 
} elseif (isset($_POST['cancel'])) {
	header("Location: viewforum.php?course=$code_cours&forum=$forum_id");
	exit;
} else {
	$tool_content .= "
        <form action='$_SERVER[PHP_SELF]?course=$code_cours&amp;topic=$topic&forum=$forum_id' method='post'>
        <fieldset>
          <legend>$langTopicData</legend>
	  <table class='tbl' width='100%'>
	  <tr>
	    <th>$langSubject:</th>
	    <td><input type='text' name='subject' size='53' maxlength='100' /></td>
	  </tr>
	  <tr>
            <th valign='top'>$langBodyMessage:</th>
            <td>".  rich_text_editor('message', 14, 50, '', "") ."</td>
          </tr>
	  <tr>
            <th>&nbsp;</th>
	    <td class='right'>
	       <input class='Login' type='submit' name='submit' value='$langSubmit' />&nbsp;
	       <input class='Login' type='submit' name='cancel' value='$langCancelPost' />
	    </td>
          </tr>
	  </table>
	</fieldset>
	</form>";
}
draw($tool_content, 2, null, $head_content);