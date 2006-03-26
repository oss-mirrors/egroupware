<?php
  /**************************************************************************\
  * eGroupWare - Trouble Ticket System                                       *
  * http://www.egroupware.org                                                *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  // $Id$
  // $Source$

  $GLOBALS['phpgw_info']['flags'] = array(
    'noheader'             => True,
    'nonavbar'             => True,
    'currentapp'           => 'tts',
    'enable_send_class'    => True,
    'enable_config_class'  => True,
    'enable_categories_class' => True
  );

  include('../header.inc.php');
  require_once (EGW_INCLUDE_ROOT.'/tts/inc/acl_funcs.inc.php');
  require_once (EGW_INCLUDE_ROOT.'/tts/inc/prio.inc.php');

  $GLOBALS['phpgw']->config->read_repository();

  if($_POST['cancel'])
  {
    $GLOBALS['phpgw']->redirect_link('/tts/index.php');
  }

  if($_POST['submit'] && !empty($_POST['ticket_subject']))
  {
  
    if (get_magic_quotes_gpc())
    {
      foreach(array('subject','details','due') as $name)
      {
        $_POST['ticket_'.$name] = stripslashes($_POST['ticket_'.$name]);
      }
    }
    /* This is the wrong place for doing this
     * MSc 060131
     */
    // $_POST['ticket_details'] = html_activate_urls($_POST['ticket_details']);

    $ticket_billable_hours = str_replace(',','.',$_POST['ticket_billable_hours']);
    $ticket_billable_rate = str_replace(',','.',$_POST['ticket_billable_rate']);

	$td = $_POST['ticket_due'];
	if (strlen($td) == 10) {		# 2005-09-07
		$td .= ' 23:59:00';
		$td = "'$td'";
	} elseif (strlen($td) == 16) {	# 2005-09-07 12:13
		$td .= ':00';
		$td = "'$td'";
	} else {
		$td = 'NULL';
	}

    $GLOBALS['phpgw']->db->query("insert into phpgw_tts_tickets (ticket_state,ticket_group,ticket_priority,ticket_owner,"
      . "ticket_assignedto,ticket_subject,ticket_category,ticket_billable_hours,"
      . "ticket_billable_rate,ticket_status,ticket_details,ticket_due) values ('"
      . intval($_POST['ticket_state']) . "','"
      . intval($_POST['ticket_group']) . "','"
      . intval($_POST['ticket_priority']) . "','"
      . $GLOBALS['phpgw_info']['user']['account_id'] . "','"
      . intval($_POST['ticket_assignedto']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_subject']) . "','"
      . intval($_POST['ticket_category']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($ticket_billable_hours) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($ticket_billable_rate) . "','O','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_details']) . "',"
      . $td
      . ")",__LINE__,__FILE__);

    $ticket_id = $GLOBALS['phpgw']->db->get_last_insert_id('phpgw_tts_tickets','ticket_id');

    $historylog = createobject('phpgwapi.historylog','tts');
    $historylog->add('O',$ticket_id,' ','');

    if($GLOBALS['phpgw']->config->config_data['mailnotification'])
    {
      mail_ticket($ticket_id);
    }

    $GLOBALS['phpgw']->redirect_link('/tts/viewticket_details.php','&ticket_id=' . $ticket_id);
  }
  else if ($_POST['submit']) {
    //there is an error:
    $GLOBALS['phpgw']->template->set_var('messages',lang('ERROR: The subject of the ticket is not specified.'));
  }
  else { //the form was not yet submitted, grab the defaults
    $GLOBALS['phpgw']->preferences->read_repository();
    if($GLOBALS['phpgw_info']['user']['preferences']['tts']['groupdefault']) 
      $_POST['ticket_group']=$GLOBALS['phpgw_info']['user']['preferences']['tts']['groupdefault'];
    if($GLOBALS['phpgw_info']['user']['preferences']['tts']['assigntodefault'])
      $_POST['ticket_assignedto']=$GLOBALS['phpgw_info']['user']['preferences']['tts']['assigntodefault'];
    if($GLOBALS['phpgw_info']['user']['preferences']['tts']['prioritydefault'])
      $_POST['ticket_priority']=$GLOBALS['phpgw_info']['user']['preferences']['tts']['prioritydefault'];
  }

  # The following sets up jsCalendar  -- MSc
  $jscal = CreateObject('phpgwapi.jscalendar');	// before phpgw_header() !!!

  $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'] . ' - ' . lang('Create new ticket');
  $GLOBALS['phpgw']->common->phpgw_header();
  echo parse_navbar();

  /* Let's define the warnings for too high prios now
   * MSc 060130
   */
  generate_priowarn();
  
  $GLOBALS['phpgw']->template->set_file(array(
    'newticket'   => 'newticket.tpl'
  ));
  $GLOBALS['phpgw']->template->set_block('newticket','options_select');
  $GLOBALS['phpgw']->template->set_block('newticket','form');

  $GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/tts/newticket.php'));

  $GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
  $GLOBALS['phpgw']->template->set_var('lang_group', lang('Group'));
  $GLOBALS['phpgw']->template->set_var('lang_subject', lang('Subject') );
  $GLOBALS['phpgw']->template->set_var('lang_nosubject', lang('No subject'));
  $GLOBALS['phpgw']->template->set_var('lang_details', lang('Details'));
  $GLOBALS['phpgw']->template->set_var('lang_priority', lang('Priority'));
  $GLOBALS['phpgw']->template->set_var('lang_lowest', lang('Lowest'));
  $GLOBALS['phpgw']->template->set_var('lang_medium', lang('Medium'));
  $GLOBALS['phpgw']->template->set_var('lang_highest', lang('Highest'));
  $GLOBALS['phpgw']->template->set_var('lang_addticket', lang('Add Ticket'));
  $GLOBALS['phpgw']->template->set_var('lang_clearform', lang('Clear Form'));
  $GLOBALS['phpgw']->template->set_var('lang_initialstate', lang('Initial State'));
  $GLOBALS['phpgw']->template->set_var('lang_assignedto',lang('Assign to'));
  $GLOBALS['phpgw']->template->set_var('lang_submit',lang('Save'));
  $GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
  $GLOBALS['phpgw']->template->set_var('lang_no_subject',lang('Please enter the subject of the ticket, otherwise the ticket cannot be stored.'));

  $GLOBALS['phpgw']->template->set_var('lang_billable_hours',lang('Billable hours'));
  $GLOBALS['phpgw']->template->set_var('lang_billable_hours_rate',lang('Billable hours rate'));

  $GLOBALS['phpgw']->template->set_var('row_off', $GLOBALS['phpgw_info']['theme']['row_off']);
  $GLOBALS['phpgw']->template->set_var('row_on', $GLOBALS['phpgw_info']['theme']['row_on']);
  $GLOBALS['phpgw']->template->set_var('th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);

  $GLOBALS['phpgw']->template->set_var('value_details',$_POST['ticket_details']);
  $GLOBALS['phpgw']->template->set_var('value_subject',$_POST['ticket_subject']);
  $GLOBALS['phpgw']->template->set_var('value_billable_hours',($_POST['ticket_billable_hours']?$_POST['ticket_billable_hours']:'0.00'));
  $GLOBALS['phpgw']->template->set_var('value_billable_hours_rate',($_POST['ticket_billable_rate']?$_POST['ticket_billable_rate']:'0.00'));


# Msc: due date
  $GLOBALS['phpgw']->template->set_var('lang_duedate', lang('Due Date'));
  if ($_POST['ticket_duedate']) {
      $GLOBALS['phpgw']->template->set_var('value_duedate',$_POST['ticket_duedate']);
  } else {
      $GLOBALS['phpgw']->template->set_var('value_duedate', date('Y-'));
  }

  
  //produce the list of groups	-- MSc 050824
  // This used to be a list of all groups the user is a member of
  // but now we want a list of all groups the user can assign tickets to

  $group_list = array();
  $group_list = $GLOBALS['phpgw']->accounts->search (array('type'=>'groups'));
//  $group_list = $GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']);

  while(list($key,$entry) = each($group_list))
  {
      if (check_ticket_right(-1, -1, $entry['account_id'], PHPGW_ACL_ADD)) {
	  $GLOBALS['phpgw']->template->set_var('optionname', $entry['account_lid']);
	  $GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
	  $GLOBALS['phpgw']->template->set_var('optionselected', $entry['account_id']==$_POST['ticket_group']?' SELECTED ':'');
	  $GLOBALS['phpgw']->template->parse('options_group','options_select',true);
      }
  }


  //produce the list of categories
  unset($s);
  $s = '<select name="ticket_category">' . $GLOBALS['phpgw']->categories->formated_list('select','all',$_POST['ticket_category'],True) . '</select>';
  $GLOBALS['phpgw']->template->set_var('value_category',$s);

  
  
  //produce the list of accounts for assigned to   -- MSc 050824
  // This used to be a list of all users (it used a undefined variable, though, so
  //   maybe it was broken anyways)
  // Now we want a list of all users that the current user can assign tickets to
  $s = '<option value="0">' . lang('None') . '</option>';
  $account_list = array();
  $account_list = $GLOBALS['phpgw']->accounts->search (array('type'=>'accounts'));
  while(list($key,$entry) = each($account_list))
  {
      if (check_ticket_right($entry['account_id'], -1, -1, PHPGW_ACL_ADD)) {
	  $s .= '<option value="' . $entry['account_id'] . '"' 
	      . ($entry['account_id']==$_POST['ticket_assignedto']?' SELECTED ':'')
	      . '>' . $entry['account_lid'] . '</option>';
      }
  }
  $GLOBALS['phpgw']->template->set_var('value_assignedto','<select name="ticket_assignedto">' . $s . '</select>');

  // Choose the correct priority to display
  $prority_selected[$ticket_priority] = ' selected';
  
  for($i=4; $i>=0; $i--)
  {
    $priority_comment[$i] = ' - '.lang(prioname($i));
    $priority_select .= '<option value="' . $i . '"' 
      . ($i==$_POST['ticket_priority']?' SELECTED ':'') 
      . '>' . $i . $priority_comment[$i] . '</option>';
  }
  $GLOBALS['phpgw']->template->set_var('value_priority','<select name="ticket_priority" onChange="generate_priowarn(this.value);">' . $priority_select . '</select>');

  // Choose the initial state to display
  $GLOBALS['phpgw']->template->set_var('options_state',
    listid_field('phpgw_tts_states','state_name','state_id',$_POST['ticket_state'], "state_initial=1"));

  $GLOBALS['phpgw']->template->set_var('tts_select_options','');
  $GLOBALS['phpgw']->template->set_var('tts_new_lstcategory','');
  $GLOBALS['phpgw']->template->set_var('tts_new_lstassignto','');

  $GLOBALS['phpgw']->template->pfp('out','form');
  $GLOBALS['phpgw']->common->phpgw_footer();
?>
