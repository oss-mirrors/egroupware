<?php
  /**************************************************************************\
  * phpGroupWare - projects                                                  *
  * http://www.phpgroupware.org                                              *
  * Written by Bettina Gille [ceb@phpgroupware.org]                          *
  * -----------------------------------------------                          *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/
  /* $Id$ */

    if ($submit)
    {
        $phpgw_info['flags'] = array('noheader' => True,
                                     'nonavbar' => True);
    }

    $phpgw_info['flags']['currentapp'] = 'projects';
    $phpgw_info['flags']['noappheader'] = True;

    include('../header.inc.php');

    if($submit)
    {
	$phpgw->db->query("DELETE from phpgw_p_projectmembers WHERE type='aa' OR type='ag'",__LINE__,__FILE__);

	if (count($users) != 0)
	{
    	    while($activ=each($users))
	    {
        	$phpgw->db->query("insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,'$activ[1]','aa')",__LINE__,__FILE__);
            }
	}
	if (count($groups) != 0)
	{
    	    while($activ=each($groups))
	    {
        	$phpgw->db->query("insert into phpgw_p_projectmembers (project_id, account_id,type) values (0,'$activ[1]','ag')",__LINE__,__FILE__);
            }
	}
	Header('Location: ' .$phpgw->link('/projects/admin.php'));
//    $phpgw->common->phpgw_exit;
    }

    $t = new Template(PHPGW_APP_TPL);
    $t->set_file(array('admin_add' => 'form_admin.tpl'));

    $t->set_var('lang_action',lang('Edit project administrator list'));
    $t->set_var('actionurl',$phpgw->link('/projects/add_admin.php'));
    $t->set_var('lang_edit',lang('Edit'));
    $t->set_var('th_bg',$phpgw_info['theme']['th_bg']);


    $db2 = $phpgw->db;
    $db2->query("SELECT account_id from phpgw_p_projectmembers WHERE type='aa'",__LINE__,__FILE__); 
	$i = 0;
	while ($db2->next_record())
	{
	    $is_admin[$i]['account_id'] = $db2->f('account_id'); 
	    $i++;
	}

    if (is_array($is_admin))
    {
	for($i=0;$i<count($is_admin);$i++)
	{

	    $allusers = $phpgw->accounts->get_list('accounts', $start, $sort, $order, $query);
    	    while (list($null,$account) = each($allusers))
	    {
        	$users_list .= '<option value="' . $account['account_id'] . '"';
        	if($account['account_id']==$is_admin[$i]['account_id'])
        	$users_list .= ' selected';
        	$users_list .= '>'
        		. $account['account_firstname'] . ' ' . $account['account_lastname'] . ' [ ' . $account['account_lid'] . ' ]' . '</option>';
	    }
	}
    }
    else 
    {
        $allusers = $phpgw->accounts->get_list('accounts', $start, $sort, $order, $query);
        while (list($null,$account) = each($allusers))
	{
            $users_list .= '<option value="' . $account['account_id'] . '"';
            $users_list .= '>'
                    . $account['account_firstname'] . ' ' . $account['account_lastname'] . ' [ ' . $account['account_lid'] . ' ]' . '</option>';
        }
    }

    $t->set_var('users_list',$users_list);

    $db2->query("SELECT account_id from phpgw_p_projectmembers WHERE type='ag'",__LINE__,__FILE__);
        $i = 0;
        while ($db2->next_record())
	{
            $is_admin[$i]['account_id'] = $db2->f('account_id');
            $i++;
        }

    if (is_array($is_admin))
    {
	for($i=0;$i<count($is_admin);$i++)
	{

	    $allgroups = $phpgw->accounts->get_list('groups', $start, $sort, $order, $query);
    	    while (list($null,$account) = each($allgroups))
	    {
        	$groups_list .= '<option value="' . $account['account_id'] . '"';
        	if($account['account_id']==$is_admin[$i]['account_id'])
        	$groups_list .= ' selected';
        	$groups_list .= '>'
        		. $account['account_lid'] . '</option>';
	    }
	}
    }
    else
    {
        $allgroups = $phpgw->accounts->get_list('groups', $start, $sort, $order, $query);
        while (list($null,$account) = each($allgroups))
	{
            $groups_list .= '<option value="' . $account['account_id'] . '"';
            $groups_list .= '>'
                    . $account['account_lid'] . '</option>';
        }
    }

    $t->set_var('groups_list',$groups_list);

    $t->set_var('lang_users_list',lang('Select users'));
    $t->set_var('lang_groups_list',lang('Select groups'));

    $t->pparse('out','admin_add');
    $phpgw->common->phpgw_footer();
?>
