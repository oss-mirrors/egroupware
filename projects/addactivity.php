<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * (http://www.phpgroupware.org)                                            *
  * Written by Bettina Gille [ceb@phpgroupware.org]                          *                               
  *          & Jens Lentfoehr <sw@lf.shlink.de>                              *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
/* $Id$ */

    $phpgw_info['flags']['currentapp'] = 'projects';
    include('../header.inc.php');

    $t = CreateObject('phpgwapi.Template',PHPGW_APP_TPL);
    $t->set_file(array('activity_add' => 'formactivity.tpl'));
    $t->set_block('activity_add','add','addhandle');
    $t->set_block('activity_add','edit','edithandle');

    if ($submit)
    {

	if ($choose)
	{
	    $num = create_activityid($year);
	}
	else
	{
	    $num = addslashes($num);
	}

	$errorcount = 0;
	if (!$num)
	{
	    $error[$errorcount++] = lang('Please enter an ID for that activity !');
	}

	$phpgw->db->query("select count(*) from phpgw_p_activities where num='$num'");
	$phpgw->db->next_record();
	if ($phpgw->db->f(0) != 0)
	{
	    $error[$errorcount++] = lang('That Activity ID has been used already !');
	}

	if ((!$billperae) || ($billperae==0))
	{
	    $error[$errorcount++] = lang('Please enter the bill per workunit !');
	}
	if ((!$minperae) || ($minperae==0))
	{
	    $error[$errorcount++] = lang('Please enter the minutes per workunit !');
	}

	if (! $error)
	{
	    $descr = addslashes($descr);
	    $phpgw->db->query("insert into phpgw_p_activities (num,descr,remarkreq,billperae,minperae) "
                . "values ('$num','$descr','$remarkreq','$billperae','$minperae')");
	}
    }
    if ($errorcount) { $t->set_var('message',$phpgw->common->error_list($error)); }
    if (($submit) && (! $error) && (! $errorcount)) { $t->set_var('message',lang('Activity x has been added !',$num)); }
    if ((! $submit) && (! $error) && (! $errorcount)) { $t->set_var('message',''); }


    $t->set_var('actionurl',$phpgw->link('/projects/addactivity.php'));
    $t->set_var('done_url',$phpgw->link('/projects/activities.php'));

    if (isset($phpgw_info['user']['preferences']['common']['currency']))
    {
	$currency = $phpgw_info['user']['preferences']['common']['currency'];
	$t->set_var('error','');
    }
    else
    {
	$t->set_var('error',lang('Please select your currency in preferences !'));
    }  

    $t->set_var('lang_action',lang('Add activity'));
    $hidden_vars = "<input type=\"hidden\" name=\"id\" value=\"$id\">";

    $t->set_var('hidden_vars',$hidden_vars);
    $t->set_var('lang_num',lang('Activity ID'));
    $t->set_var('num',$num);

    if (!$submit)
    {
	$choose = "<input type=\"checkbox\" name=\"choose\" value=\"True\">";
	$t->set_var('lang_choose',lang('Generate Activity ID ?'));
	$t->set_var('choose',$choose);
    }
    else
    {
	$t->set_var('lang_choose','');
	$t->set_var('choose','');
    }

    $t->set_var('lang_descr',lang('Description'));
    $t->set_var('descr',$descr);
    $t->set_var('lang_minperae',lang("Minutes per workunit"));
    $t->set_var('minperae',$minperae);
    $t->set_var('currency',$currency);
    $t->set_var('lang_billperae',lang('Bill per workunit'));
    $t->set_var('billperae',$billperae);

    $t->set_var('lang_remarkreq',lang('Remark required'));

    if ($remarkreq=='N'):
         $stat_sel[0]=' selected';
    elseif ($remarkreq=='Y'):
         $stat_sel[1]=' selected';
    endif;

    $remarkreq_list = '<option value="N"' . $stat_sel[0] . '>' . lang('No') . '</option>' . "\n"
           	    . '<option value="Y"' . $stat_sel[1] . '>' . lang('Yes') . '</option>' . "\n";
    $t->set_var('remarkreq_list',$remarkreq_list);

    $t->set_var('lang_add',lang('Add'));
    $t->set_var('lang_done',lang('Done'));
    $t->set_var('lang_reset',lang('Clear Form'));

    $t->set_var('edithandle','');
    $t->set_var('addhandle','');
    $t->pparse('out','activity_add');
    $t->pparse('addhandle','add');

    $phpgw->common->phpgw_footer();
?>
