<?php
  /**************************************************************************\
  * eGroupWare - TTS                                                         *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	$GLOBALS['phpgw_info']['flags'] = array(
		'currentapp'  => 'tts', 
		'noheader'    => True, 
		'nonavbar'    => True, 
		'noappheader' => True,
		'noappfooter' => True,
		'enable_config_class'     => True,
		'enable_nextmatchs_class' => True
	);

	include('../header.inc.php');

 	if ($_POST['cancel'])
 	{
 		$GLOBALS['phpgw']->redirect_link('/tts/index.php');
 	}

	$option_names = array(lang('Disabled'), lang('Users choice'), lang('Force'));
	$owner_selected = array ();
	$group_selected = array ();
	$assigned_selected = array ();

	$GLOBALS['phpgw']->config->read_repository();

	if ($_POST['submit'])
	{
		if ($_POST['ownernotification'])
		{
			$GLOBALS['phpgw']->config->config_data['ownernotification'] = True;
		}
		else
		{
			unset($GLOBALS['phpgw']->config->config_data['ownernotification']);
		}

		if ($_POST['groupnotification'])
		{
			$GLOBALS['phpgw']->config->config_data['groupnotification'] = True;
		}
		else
		{
			unset($GLOBALS['phpgw']->config->config_data['groupnotification']);
		}

		if ($_POST['assignednotification'])
		{
			$GLOBALS['phpgw']->config->config_data['assignednotification'] = True;
		}
		else
		{
			unset($GLOBALS['phpgw']->config->config_data['assignednotification']);
		}

        // add by Josip
        if ($_POST['assignmentnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['assignmentnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['assignmentnotification']);
        }

         if ($_POST['assignmentgroupnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['assignmentgroupnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['assignmentgroupnotification']);
        }

        if ($_POST['email2assignednotification'])
        {
            $GLOBALS['phpgw']->config->config_data['email2assignednotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['email2assignednotification']);
        }


        if ($_POST['email2assignmentnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['email2assignmentnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['email2assignmentnotification']);
        }

        if ($_POST['email2assignmentgroupnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['email2assignmentgroupnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['email2assignmentgroupnotification']);
        }

        if ($_POST['email2highpriorityassignmentnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification']);
        }


        if ($_POST['email2highpriorityassignmentnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification']);
        }

        if ($_POST['email2highpriorityassignmentgroupnotification'])
        {
            $GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentgroupnotification'] = True;
        }
        else
        {
            unset($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentgroupnotification']);
        }

        // end add

		if( $GLOBALS['phpgw']->config->config_data['ownernotification'] ||
			$GLOBALS['phpgw']->config->config_data['groupnotification'] ||
            $GLOBALS['phpgw']->config->config_data['assignednotification'] ||
            $GLOBALS['phpgw']->config->config_data['assignmentnotification'] ||
            $GLOBALS['phpgw']->config->config_data['assignmentgroupnotification'] ||
            $GLOBALS['phpgw']->config->config_data['email2assignednotification'] ||
            $GLOBALS['phpgw']->config->config_data['email2assignmentnotification'] ||
            $GLOBALS['phpgw']->config->config_data['email2assignmentgroupnotification'] ||
            $GLOBALS['phpgw']->config->config_data['email2highpriorityassignednotification'] ||
            $GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification'] ||
            $GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentgroupnotification'] ) {
        	$GLOBALS['phpgw']->config->config_data['mailnotification'] = True;
		} else {
			unset($GLOBALS['phpgw']->config->config_data['mailnotification']);
		}
		$GLOBALS['phpgw']->config->save_repository(True);
		$GLOBALS['phpgw']->redirect_link('/tts/index.php');
	}

	$GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'] . ' - ' . lang('Administration');
	$GLOBALS['phpgw']->common->phpgw_header();
	echo parse_navbar();

	$GLOBALS['phpgw']->template->set_file(array('admin' => 'admin.tpl'));

	$GLOBALS['phpgw']->template->set_var('action_url',$GLOBALS['phpgw']->link('/tts/admin.php'));

	$tr_color = $GLOBALS['phpgw']->nextmatchs->alternate_row_color($tr_color);
	$GLOBALS['phpgw']->template->set_var('tr_color',$tr_color);

	$GLOBALS['phpgw']->template->set_var('lang_ownernotification',lang('notify changes to ticket owner by e-mail'));
	if ($GLOBALS['phpgw']->config->config_data['ownernotification'])
	{
		$GLOBALS['phpgw']->template->set_var('ownernotification',' checked');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('ownernotification','');
	}

	$GLOBALS['phpgw']->template->set_var('lang_groupnotification',lang('notify changes to ticket group by e-mail'));
	if ($GLOBALS['phpgw']->config->config_data['groupnotification'])
	{
		$GLOBALS['phpgw']->template->set_var('groupnotification',' checked');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('groupnotification','');
	}

	$GLOBALS['phpgw']->template->set_var('lang_assignednotification',lang('notify changes to ticket assignee by e-mail'));
	if ($GLOBALS['phpgw']->config->config_data['assignednotification'])
	{
		$GLOBALS['phpgw']->template->set_var('assignednotification',' checked');
	}
	else
	{
		$GLOBALS['phpgw']->template->set_var('assignednotification','');
	}


    // add by Josip
    $GLOBALS['phpgw']->template->set_var('lang_assignmentnotification',lang('notify assignment of the ticket to ticket assignee by e-mail'));
    if ($GLOBALS['phpgw']->config->config_data['assignmentnotification'])
    {
        $GLOBALS['phpgw']->template->set_var('assignmentnotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('assignmentnotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_assignmentgroupnotification',lang('notify assignment of the ticket to ticket group by e-mail'));
    if ($GLOBALS['phpgw']->config->config_data['assignmentgroupnotification'])
    {
        $GLOBALS['phpgw']->template->set_var('assignmentgroupnotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('assignmentgroupnotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_email2assignednotification',lang('notify changes to ticket assignee by e-mail 2'));
    if ($GLOBALS['phpgw']->config->config_data['email2assignednotification'])
    {
        $GLOBALS['phpgw']->template->set_var('email2assignednotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('email2assignednotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_email2assignmentnotification',lang('notify assignment of the ticket to ticket assignee by e-mail 2'));
    if ($GLOBALS['phpgw']->config->config_data['email2assignmentnotification'])
    {
        $GLOBALS['phpgw']->template->set_var('email2assignmentnotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('email2assignmentnotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_email2assignmentgroupnotification',lang('notify assignment of the ticket to ticket group by e-mail 2'));
    if ($GLOBALS['phpgw']->config->config_data['email2assignmentgroupnotification'])
    {
        $GLOBALS['phpgw']->template->set_var('email2assignmentgroupnotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('email2assignmentgroupnotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_email2highpriorityassignednotification',lang('notify changes of the high priority ticket to ticket assignee by e-mail 2'));
    if ($GLOBALS['phpgw']->config->config_data['email2highpriorityassignednotification'])
    {
        $GLOBALS['phpgw']->template->set_var('email2highpriorityassignednotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('email2highpriorityassignednotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_email2highpriorityassignmentnotification',lang('notify assignment of the high priority ticket to ticket assignee by e-mail 2'));
    if ($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification'])
    {
        $GLOBALS['phpgw']->template->set_var('email2highpriorityassignmentnotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('email2highpriorityassignmentnotification','');
    }

    $GLOBALS['phpgw']->template->set_var('lang_email2highpriorityassignmentgroupnotification',lang('notify assignment of the high priority ticket to ticket group by e-mail 2'));
    if ($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentgroupnotification'])
    {
        $GLOBALS['phpgw']->template->set_var('email2highpriorityassignmentgroupnotification',' checked');
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('email2highpriorityassignmentgroupnotification','');
    }

    // end add


 	$GLOBALS['phpgw']->template->set_var('lang_submit',lang('Save'));
 	$GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

	$GLOBALS['phpgw']->template->pparse('out','admin');
	$GLOBALS['phpgw']->common->phpgw_footer();
?>
