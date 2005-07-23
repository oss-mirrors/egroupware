<?php
   /**************************************************************************\
   * eGroupWare TTS                                                           *
   * http://www.egroupware.org                                                *
   * Written by Joseph Engo <jengo@phpgroupware.org>                          *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   // $Id$
   // $Source$

	$menu_title = $GLOBALS['egw_info']['apps'][$appname]['title'] . ' '. lang('Menu');

	// added by Josip
	/*
	$group_list = array();
	$group_list = $GLOBALS['egw']->accounts->membership($GLOBALS['egw_info']['user']['account_id']);

	while(list($key,$entry) = each($group_list))
	{
		$tag = '';
		if($entry['account_id'] == $ticket['group'])
		{
			$tag = 'selected';
		}
		$GLOBALS['egw']->template->set_var('optionname', $entry['account_name']);
		$GLOBALS['egw']->template->set_var('optionvalue', $entry['account_id']);
		$GLOBALS['egw']->template->set_var('optionselected', $tag);
		$GLOBALS['egw']->template->parse('options_group','options_select',true);
	}
	*/
//ACL	if($GLOBALS['egw']->acl->check('add',1,'tts'))
//ACL	{
	$file = Array(
		'New ticket'        => $GLOBALS['egw']->link('/tts/newticket.php'),
		'View all tickets' => $GLOBALS['egw']->link('/tts/index.php','filter=view&f_status=A'),
		'View only open tickets' => $GLOBALS['egw']->link('/tts/index.php','filter=view&f_status=O') ,
		'View only my open tickets' => $GLOBALS['egw']->link('/tts/index.php','filter=viewmy&f_status=O'),
		'View only open tickets created by me' => $GLOBALS['egw']->link('/tts/index.php','filter=viewownedbyme&f_status=O'),
		'View only open WWW requests' => $GLOBALS['egw']->link('/tts/wnt_index.php','filter=view&f_status=O'),
		'View reports' => $GLOBALS['egw']->link('/tts/view_report.php','filter=viewhighcritical')
	);
//ACL        }
//ACL        else
//ACL        {
//ACL          $file = Array(
//ACL                'View all tickets' => $GLOBALS['egw']->link('/tts/index.php','filter=view&f_status=A'),
//ACL                'View only open tickets' => $GLOBALS['egw']->link('/tts/index.php','filter=view&f_status=O') ,
//ACL                'View only my open tickets' => $GLOBALS['egw']->link('/tts/index.php','filter=viewmy&f_status=O'),
//ACL                'View reports' => $GLOBALS['egw']->link('/tts/view_report.php','filter=view_report')
//ACL         );
//ACL        }
	display_sidebox($appname,$menu_title,$file);

	if($GLOBALS['egw_info']['user']['apps']['preferences'])
	{
		$menu_title = lang('Preferences');
		$file = Array(
			'Preferences'     => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uisettings.index&appname=tts'),
			'Edit Categories' => $GLOBALS['egw']->link('/index.php','menuaction=preferences.uicategories.index&cats_app='.$appname.'&cats_level=True&global_cats=True')
		);
		display_sidebox($appname,$menu_title,$file);
	}

	if($GLOBALS['egw_info']['user']['apps']['admin'])
	{
		$menu_title = lang('Administration');
		$file = Array(
			'Admin options'     => $GLOBALS['egw']->link('/tts/admin.php'),
			'Global Categories' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uicategories.index&appname=tts'),
			'Configure the states'        => $GLOBALS['egw']->link('/tts/states.php'),
			'Configure the transitions'   => $GLOBALS['egw']->link('/tts/transitions.php'),
			'Configure the Cat-Groups'    => $GLOBALS['egw']->link('/tts/cat_group.php'),
			'Configure the Escalation'    => $GLOBALS['egw']->link('/tts/escalation.php')
		);
//                $file = Array(
//                        'Admin options'     => $GLOBALS['egw']->link('/tts/admin.php'),
//                        'Global Categories' => $GLOBALS['egw']->link('/index.php','menuaction=admin.uicategories.index&appname=tts'),
//                        'Configure the states'             => $GLOBALS['egw']->link('/tts/states.php'),
//                        'Configure the transitions'   => $GLOBALS['egw']->link('/tts/transitions.php'),
//                        'Configure the Cat-Groups'    => $GLOBALS['egw']->link('/tts/cat_group.php'),
//                        'Configure the Escalation'    => $GLOBALS['egw']->link('/tts/escalation.php'),
//                        'Configure the VIP Groups'    => $GLOBALS['egw']->link('/tts/vip_group.php')
//                );

		display_sidebox($appname,$menu_title,$file);
	}
