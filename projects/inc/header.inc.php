<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Jens Lentfoehr <sw@lf.shlink.de>                              *
  *            Bettina Gille  [bettina@lisa.de]                              * 
  *                           [http://bananenkeller.lisa.de]                 *
  * --------------------------------------------------------                 *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

       $phpgw->db->query("select app_name from applications where (app_enabled > 0  and app_name='projects')");
       $lid = $phpgw_info["user"]["userid"];
       
       while ($phpgw->db->next_record()); 
       
       if (gettype($id) == "integer") {
          $phpgw->db->query("select account_permissions from accounts where account_id='$lid'");
       } else {
          $phpgw->db->query("select account_permissions from accounts where account_lid='$lid'");
       }
       $phpgw->db->next_record();

       $pl = explode(":",$phpgw->db->f("account_permissions"));

       for ($i=0; $i<count($pl); $i++) {
          if ($enabled_apps[$pl[$i]]) {
             $enabled_apps[$pl[$i]] = 2;
          }
       }
       
       // This is to prevent things from being loaded twice
       if ($phpgw_info["user"]["userid"] == $lid) {
          $group_list = $this->groups;
       } else {
          $group_list = $this->read_groups($lid);
       }

       for ($k=0; $k<count($group_list); $k++) {
          $phpgw->db->query("select group_apps from groups where group_id='"
				. "$group_list[$i]'");
          $phpgw->db->next_record();

          $gp = explode(",",$phpgw->db->f("group_apps"));
          for ($i=1,$j=0;$i<count($gp)-1;$i++,$j++) {
             $apps[$gp[$i]] = 2;
          }
       }
      $t = new Template($phpgw_info["server"]["app_tpl"]);
      $t->set_file(array("projects_header" => "header.tpl"));
      $phpgw->db->query("select group_id from groups where group_name = 'projectAdmin'");

      $admin_info = lang("Projects");
      if ($phpgw->db->next_record()) {
         $group_id = $phpgw->db->f("group_id");
	 $phpgw->db->query("select account_lid from accounts where account_groups like '%,$group_id%' and account_lid='$lid'");
         $phpgw->db->next_record();
	 if($phpgw->db->f("account_lid") == $lid)
            $admin_info = lang("Projects")."&nbsp;&nbsp;&nbsp;".lang("Admin");
	 }
      $t->set_var("admin_info", "<td bgcolor=\"" . $phpgw_info["theme"]["th_bg"] 
                        . "\" align=\"left\"><b>" . $admin_info . "</b>");

 /*  if($apps["projectbilling"]==True)
     $t->set_var("link_billing","<a href=\"" . $phpgw->link("../projectbilling/") . "\">" . lang("Projectbilling") ."</a>");
     else
     $t->set_var("link_billing","");

     if($apps["projecthours"]==True)
     $t->set_var("link_hours","<a href=\"" . $phpgw->link("../projecthours/") . "\">" . lang("Projecthours") ."</a>");
     else
    $t->set_var("link_hours","");

    if($apps["projectstatistics"]==True)
    $t->set_var("link_statistics","<a href=\"" . $phpgw->link("../projectstatistics/") . "\">" . lang("Projectstatistics") ."</a>");
    else
    $t->set_var("link_statistics","");

    if($apps["projects"]==True)
    $t->set_var("link_projects","<a href=\"" . $phpgw->link("../projects/") . "\">" . lang("Projects") ."</a>");
    else
    $t->set_var("link_projects","");
 */

$t->pparse("out","projects_header");
 
?>