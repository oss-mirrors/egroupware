<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille  [ceb@phpgroupware.org]                         * 
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * ------------------------------------------------                         *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

    $t = CreateObject('phpgwapi.Template',$phpgw_info["server"]["app_tpl"]);
    $t->set_file(array('projects_header' => 'header.tpl'));

    $admin_info = lang('Projects');
    $t->set_var('admin_info', "<td bgcolor=\"" . $phpgw_info["theme"]["th_bg"] 
                        . "\" align=\"left\"><b>" . $admin_info);

    $t->set_var("link_activities","<a href=\"" . $phpgw->link("/projects/activities.php") . "\">" . lang("Activities") ."</a>");
    $t->set_var("link_billing","<a href=\"" . $phpgw->link("/projects/bill_index.php") . "\">" . lang("Project billing") ."</a>");
    $t->set_var("link_hours","<a href=\"" . $phpgw->link("/projects/hours_index.php") . "\">" . lang("Project hours") ."</a>");
    $t->set_var("link_statistics","<a href=\"" . $phpgw->link("/projects/stats_projectlist.php") . "\">" . lang("Project statistics") ."</a>");
    $t->set_var("link_delivery","<a href=\"" . $phpgw->link("/projects/del_index.php") . "\">" . lang("Project delivery") ."</a>");
    $t->set_var("link_return_projects","<a href=\"" . $phpgw->link("/projects/index.php") . "\">" . lang("Return to projects") ."</a>");
    $t->pparse("out","projects_header");
 
?>