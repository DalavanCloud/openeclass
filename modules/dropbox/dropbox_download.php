<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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
 * ======================================================================== */

$require_login = TRUE;
$require_current_course = TRUE;

include 'functions.php';
include 'dropbox_class.inc.php';
include 'include/lib/forcedownload.php';

if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
} else {
        header("Location: $urlServer");
}

$work = new Dropbox_work($id);

$path = $dropbox_cnf["sysPath"] . "/" . $work -> filename; //path to file as stored on server
$file = $work->title;

send_file_to_client($path, $file, null, true);
exit;
