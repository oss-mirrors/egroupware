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
  
    $phpgw_info["flags"]["currentapp"] = "projects";
    include("../header.inc.php");

    if (! $id) {
     Header("Location: " . $phpgw->link('/projects/index.php',"sort=$sort&order=$order&query=$query&start=$start"
	  . "&filter=$filter"));
    }

    $t = CreateObject('phpgwapi.Template',$phpgw->common->get_tpl_dir('projects'));
    $t->set_file(array('activity_edit' => 'formactivity.tpl'));
    $t->set_file(array('activity_edit' => 'formactivity.tpl'));
    $t->set_block('activity_edit','add','addhandle');
    $t->set_block('activity_edit','edit','edithandle');

    $hidden_vars = "<input type=\"hidden\" name=\"sort\" value=\"$sort\">\n"
			. "<input type=\"hidden\" name=\"order\" value=\"$order\">\n"
			. "<input type=\"hidden\" name=\"query\" value=\"$query\">\n"
			. "<input type=\"hidden\" name=\"start\" value=\"$start\">\n"
			. "<input type=\"hidden\" name=\"filter\" value=\"$filter\">\n"
			. "<input type=\"hidden\" name=\"id\" value=\"$id\">\n";

    if ($submit) {
    $errorcount = 0;
    $phpgw->db->query("select count(*) from phpgw_p_activities where num='$num'");
    $phpgw->db->next_record();
    if ($phpgw->db->f(0) != 0) { $error[$errorcount++] = lang('That Activity ID has been used already !'); }

    if (!$num) { $error[$errorcount++] = lang('Please enter an ID for that Activity !'); }

    if (! $error) {
    $num = addslashes($num);
    $descr = addslashes($descr);
    $phpgw->db->query("update p_activities set num='$num',remarkreq='$remarkreq',descr='$descr',billperae='$billperae',"
                    . "minperae='$minperae' where id='$id'");
      }
    } 

    if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang("Activity $num has been updated !")); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }

    $phpgw->db->query("select * from phpgw_p_activities WHERE id='$id'");
     $phpgw->db->next_record();

    if (isset($phpgw_info["user"]["preferences"]["common"]["currency"])) {
    $currency = $phpgw_info["user"]["preferences"]["common"]["currency"];
    $t->set_var('error','');
    }
    else {
    $t->set_var('error',lang('Please select your currency in preferences !'));
    }

    $t->set_var('lang_choose','');
    $t->set_var('choose','');
    $t->set_var('currency',$currency);
    $t->set_var('actionurl',$phpgw->link("/projects/editactivity.php"));
    $t->set_var('deleteurl',$phpgw->link("/projects/deleteactivity.php"));
    $t->set_var('lang_action',lang('Edit activity'));
    $t->set_var('hidden_vars',$hidden_vars);
    $t->set_var('lang_num',lang('Activity ID'));
    $t->set_var('num',$phpgw->strip_html($phpgw->db->f("num")));
    $t->set_var('lang_descr',lang('Description'));
    $descr  = $phpgw->strip_html($phpgw->db->f("descr"));                                                                                                                                
    if (! $descr)  $descr  = "&nbsp;";
    $t->set_var("descr",$descr);
    $t->set_var('lang_remarkreq',lang('Remark required'));
    if ($phpgw->db->f("remarkreq")=="N"):
         $stat_sel[0]=" selected";
    elseif ($phpgw->db->f("remarkreq")=="Y"):
         $stat_sel[1]=" selected";
    endif;

    $remarkreq_list = "<option value=\"N\"".$stat_sel[0].">" . lang("No") . "</option>\n"
                  . "<option value=\"Y\"".$stat_sel[1].">" . lang("Yes") . "</option>\n";

    $t->set_var('remarkreq_list',$remarkreq_list);
    $t->set_var('lang_billperae',lang('Bill per workunit'));
    $t->set_var('billperae',$phpgw->db->f("billperae"));
    $t->set_var('lang_minperae',lang('Minutes per workunit'));
    $t->set_var('minperae',$phpgw->db->f("minperae"));

    $t->set_var('lang_edit',lang('Edit'));
    $t->set_var('lang_delete',lang('Delete'));
    
    $t->set_var('edithandle','');
    $t->set_var('addhandle','');
    $t->pparse('out','activity_edit');
    $t->pparse('edithandle','edit');

    $phpgw->common->phpgw_footer();
?>
