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


require_once 'modules/document/doc_init.php';

/**
 * helper function to get a file path from get variable
 * @param string $name
 * @global array $_GET
 * @return string
 */
function get_dir_path($name)
{
        if (isset($_GET[$name])) {
                $path = q($_GET[$name]);
                if ($path == '/' or $path == '\\') {
                        $path = '';
                }
        } else {
                $path = '';
        }
        return $path;
}

/**
 * list documents while inserting them in course unit
 * @global type $id
 * @global type $webDir
 * @global type $course_code
 * @global type $tool_content
 * @global type $group_sql
 * @global type $langDirectory
 * @global type $langUp
 * @global type $langName
 * @global type $langSize
 * @global type $langDate
 * @global type $langType
 * @global type $langAddModulesButton
 * @global type $langChoice
 * @global type $langNoDocuments
 * @global type $course_code
 * @global type $themeimg
 */
function list_docs()
{
        global $id, $webDir, $course_code, $tool_content,
               $group_sql, $langDirectory, $langUp, $langName, $langSize,
               $langDate, $langType, $langAddModulesButton, $langChoice,
               $langNoDocuments, $course_code, $themeimg, $langCommonDocs, $nameTools;

        $basedir = $webDir . '/courses/' . $course_code . '/document';
        $path = get_dir_path('path');
        $dir_param = get_dir_path('dir');
        $dir_setter = $dir_param? ('&amp;dir=' . $dir_param): '';
        $dir_html = $dir_param? "<input type='hidden' name='dir' value='$dir_param'>": '';

        if ($id == -1) {
                $common_docs = true;
                $nameTools = $langCommonDocs;
                $group_sql = "course_id = -1 AND subsystem = ".COMMON."";
                $basedir = $webDir . '/courses/commondocs';
                $result = db_query("SELECT * FROM document
                                    WHERE $group_sql AND
                                          visible = 1 AND
                                          path LIKE " . quote("$path/%") . " AND
                                          path NOT LIKE " . quote("$path/%/%"));
        } else {
                $common_docs = false;
                $result = db_query("SELECT * FROM document
                                    WHERE $group_sql AND
                                          path LIKE " . quote("$path/%") . " AND
                                          path NOT LIKE " . quote("$path/%/%"));
        }

        $fileinfo = array();
        $urlbase = $_SERVER['SCRIPT_NAME'] . "?course=$course_code$dir_setter&amp;type=doc&amp;id=$id&amp;path=";

        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                $fileinfo[] = array(
			'id' => $row['id'],
                        'is_dir' => is_dir($basedir . $row['path']),
                        'size' => filesize($basedir . $row['path']),
                        'title' => $row['title'],
                        'name' => htmlspecialchars($row['filename']),
                        'format' => $row['format'],
                        'path' => $row['path'],
                        'visible' => $row['visible'],
                        'comment' => $row['comment'],
                        'copyrighted' => $row['copyrighted'],
                        'date' => $row['date_modified']);
        }
        if (count($fileinfo) == 0) {
                $tool_content .= "\n  <p class='alert1'>$langNoDocuments</p>\n";
        } else {
                if (empty($path)) {
                        $dirname = '';
                        $parenthtml = '';
                        $colspan = 5;
                } else {
                        list($dirname) = mysql_fetch_row(db_query("SELECT filename FROM document
                                                                   WHERE $group_sql AND path = " . quote($path)));
			$parentpath = dirname($path);
                        $dirname = "/".htmlspecialchars($dirname);
                        $parentlink = $urlbase . $parentpath;
                        $parenthtml = "<th class='right'><a href='$parentlink'>$langUp</a> " .
                                      icon('folder_up', $langUp, $parentlink) . "</th>";
                        $colspan = 4;
                }
		$tool_content .= "<form action='insert.php?course=$course_code' method='post'><input type='hidden' name='id' value='$id' />" .
                         "<table class='tbl_alt' width='99%'>" .
                         "<tr>".
                         "<th colspan='$colspan'><div align='left'>$langDirectory: $dirname</div></th>" .
                                   $parenthtml .
                         "</tr>" .
                         "<tr>" .
                         "<th>$langType</th>" .
                         "<th><div align='left'>$langName</div></th>" .
                         "<th width='100'>$langSize</th>" .
                         "<th width='80'>$langDate</th>" .
                         "<th width='80'>$langChoice</th>" .
                         "</tr>\n";
		$counter = 0;
		foreach (array(true, false) as $is_dir) {
			foreach ($fileinfo as $entry) {
				if ($entry['is_dir'] != $is_dir) {
					continue;
				}
				$dir = $entry['path'];
				if ($is_dir) {
					$image = $themeimg.'/folder.png';
					$file_url = $urlbase . $dir;
					$link_extra = '';
					$link_text = $entry['name'];
				} else {
					$image = '../document/img/' . choose_image('.' . $entry['format']);
                                        $file_url = file_url($entry['path'], $entry['name'],
                                                             $common_docs? 'common': $course_code);
					$link_extra = " target='_blank'";
					if (empty($entry['title'])) {
						$link_text = $entry['name'];
					} else {
						$link_text = $entry['title'];
					}
				}
				if ($entry['visible'] == 'i') {
					$vis = 'invisible';
				} else {
					if ($counter % 2 == 0) {
						$vis = 'even';
					} else {
						$vis = 'odd';
					}
				}
				$tool_content .= "\n    <tr class='$vis'>";
				$tool_content .= "\n      <td width='1' class='center'><a href='$file_url'$link_extra><img src='$image' alt=''></a></td>";
				$tool_content .= "\n      <td><a href='$file_url'$link_extra>$link_text</a>";

				/*** comments ***/
				if (!empty($entry['comment'])) {
					$tool_content .= "<br /><div class='comment'>" .
						standard_text_escape($entry['comment']) .
						"</div>\n";
				}
				$tool_content .= "</td>";
				if ($is_dir) {
					// skip display of date and time for directories
					$tool_content .= "\n      <td>&nbsp;</td>\n      <td>&nbsp;</td>";
				} else {
					$size = format_file_size($entry['size']);
                                        $date = nice_format($entry['date'], true, true);
					$tool_content .= "<td class='center'>$size</td><td class='center'>$date</td>";
				}
					$tool_content .= "<td class='center'><input type='checkbox' name='document[]' value='$entry[id]' /></td>";
					$tool_content .= "</tr>";
				$counter++;
			}
		}
		$tool_content .= "<tr><th colspan=$colspan><div align='right'>";
		$tool_content .= "<input type='submit' name='submit_doc' value='$langAddModulesButton' /></div></th>";
                $tool_content .= "</tr></table>$dir_html</form>\n";
        }
}
