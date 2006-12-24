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
    'enable_nextmatchs_class' => True,
    'enable_categories_class' => True,
    'enable_config_class'     => True,
    'currentapp'              => 'tts',
    'noheader'                => True,
    'nonavbar'                => True,
    'enable_config_class'     => !@$_POST['submit'] && !@$_POST['cancel']
  );

  include('../header.inc.php');
  
  $filter = reg_var('filter','GET');
  $start  = reg_var('start','GET','numeric',0);
  $sort   = reg_var('sort','GET');
  $order  = reg_var('order','GET');
  $searchfilter = reg_var('searchfilter','POST');

  if($_POST['cancel'])
  {
    $GLOBALS['phpgw']->redirect_link('/tts/index.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort,'start'=>$start));
  }
  $ticket_id = intval(get_var('ticket_id',array('POST','GET')));

  $GLOBALS['phpgw']->config->read_repository();

  $GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

  $GLOBALS['phpgw']->historylog->types = array(
    'R' => 'Re-opened',
    'X' => 'Closed',
    'O' => 'Opened',
    'A' => 'Re-assigned',
    'P' => 'Priority changed',
    'T' => 'Category changed',
    'S' => 'Subject changed',
    'B' => 'Billing rate',
    'H' => 'Billing hours',
    'G' => 'Group ownership changed',
    'N' => 'State changed',
    'D' => 'Due Date changed'
  );

  if(!$_POST['save'] && !$_POST['apply'])
  {
    // load the necessary css for the tabs
    function css()
    {
      $appCSS =
      'th.activetab
      {
        color:#000000;
        background-color:#D3DCE3;
        border-top-width : 2px;
        border-top-style : solid;
        border-top-color : Black;
        border-left-width : 2px;
        border-left-style : solid;
        border-left-color : Black;
        border-right-width : 2px;
        border-right-style : solid;
        border-right-color : Black;
      }

      th.inactivetab
      {
        color:#000000;
        background-color:#E8F0F0;
        border-width : 1px;
        border-style : solid;
        border-color : Black;
        border-bottom-width : 2px;
        border-bottom-style : solid;
        border-bottom-color : Black;
      }

      table.tabcontent
      {
        border-bottom-width : 2px;
        border-bottom-style : solid;
        border-bottom-color : Black;
        border-left-width : 2px;
        border-left-style : solid;
        border-left-color : Black;
        border-right-width : 2px;
        border-right-style : solid;
        border-right-color : Black;
      }

      .td_left { border-left : 1px solid Gray; border-top : 1px solid Gray; }
      .td_right { border-right : 1px solid Gray; border-top : 1px solid Gray; }

      div.activetab{ display:inline; }
      div.inactivetab{ display:none; }';

      return $appCSS;
    }
    $GLOBALS['phpgw_info']['flags']['css'] = css();

    // load the necessary javascript for the tabs
    if(!@is_object($GLOBALS['phpgw']->js))
    {
      $GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
    }
    $GLOBALS['phpgw']->js->validate_file('tabs','tabs');
    $GLOBALS['phpgw']->js->set_onload('tab.init();');

    $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'] . ' - ' . lang('View Job Detail');

    // The following sets up jsCalendar  -- MSc
    $jscal = CreateObject('phpgwapi.jscalendar');	// before phpgw_header() !!!

    $GLOBALS['phpgw']->common->phpgw_header();
    echo parse_navbar();

    // Did we get the ticket_id? If not, there is some problem
    if ( (! isset($ticket_id)) || $ticket_id == 0) {
       echo 'Error --- no ticket ID';
       $GLOBALS['phpgw']->common->phpgw_footer();
       exit ();
     }
 
    $GLOBALS['phpgw']->template->set_var('datepopup_image', $GLOBALS['phpgw']->link('/phpgwapi/templates/default/images/datepopup.gif'));

    // Have they viewed this ticket before ?
    $GLOBALS['phpgw']->db->query("select count(*) from phpgw_tts_views where view_id='$ticket_id' "
      . "and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();

    if(!$GLOBALS['phpgw']->db->f(0))
    {
      $GLOBALS['phpgw']->db->query("insert into phpgw_tts_views values ('$ticket_id','"
        . $GLOBALS['phpgw_info']['user']['account_id'] . "','" . time() . "')",__LINE__,__FILE__);
    }

    // select the ticket that you selected
    $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();

    $ticket['billable_hours'] = $GLOBALS['phpgw']->db->f('ticket_billable_hours');
    $ticket['billable_rate']  = $GLOBALS['phpgw']->db->f('ticket_billable_rate');
    $ticket['assignedto']     = $GLOBALS['phpgw']->db->f('ticket_assignedto');
    $ticket['category']       = $GLOBALS['phpgw']->db->f('ticket_category');
    $ticket['details']        = $GLOBALS['phpgw']->db->f('ticket_details');
    $ticket['subject']        = $GLOBALS['phpgw']->db->f('ticket_subject');
    $ticket['priority']       = $GLOBALS['phpgw']->db->f('ticket_priority');
    $ticket['owner']          = $GLOBALS['phpgw']->db->f('ticket_owner');
    $ticket['group']          = $GLOBALS['phpgw']->db->f('ticket_group');
    $ticket['state']          = $GLOBALS['phpgw']->db->f('ticket_state');
    $ticket['duedate']	      = substr($GLOBALS['phpgw']->db->f('ticket_due'), 0, 16);
    $ticket['converted']      = $GLOBALS['phpgw']->db->f('ticket_converted');

    if (!$ticket['group']) $ticket['group'] = $GLOBALS['phpgw_info']['user']['account_primary_group'];

    $GLOBALS['phpgw']->template->set_file('viewticket','viewticket_details.tpl');
    $GLOBALS['phpgw']->template->set_block('viewticket','options_select');
    $GLOBALS['phpgw']->template->set_block('viewticket','additional_notes_row');
    $GLOBALS['phpgw']->template->set_block('viewticket','additional_notes_row_empty');
    $GLOBALS['phpgw']->template->set_block('viewticket','row_history');
    $GLOBALS['phpgw']->template->set_block('viewticket','row_history_empty');
    $GLOBALS['phpgw']->template->set_block('viewticket','form');
    $GLOBALS['phpgw']->template->set_block('form','update_state_items','update_state_group');

    // Someone without permission tries to get direct access to the ticket
    if (! check_read_right($ticket['owner'], $ticket['assignedto'], $ticket['group'])) {
       echo lang('ticket is not accessable');
       $GLOBALS['phpgw']->common->phpgw_footer();
       exit ();
    }

    $messages .= rtrim($GLOBALS['phpgw']->session->appsession('messages','tts'),"\0");
    if($messages)
    {
      $GLOBALS['phpgw']->template->set_var('messages',$messages);
      $GLOBALS['phpgw']->session->appsession('messages','tts','');
    }

    // Choose the correct priority to display
    $priority_selected[$ticket['priority']] = ' selected';

    for($i=4; $i>=0; $i--)
    {
      $priority_comment[$i] = ' - '.lang(prioname($i));
      $GLOBALS['phpgw']->template->set_var('optionname', $i.$priority_comment[$i]);
      $GLOBALS['phpgw']->template->set_var('optionvalue', $i);
      $GLOBALS['phpgw']->template->set_var('optionselected', $priority_selected[$i]);
      $GLOBALS['phpgw']->template->parse('options_priority','options_select',true);
    }

  //produce the list of groups
  $group_list = array();
//  $group_list = $GLOBALS['phpgw']->accounts->search (array('type'=>'groups'));
  $group_list = $GLOBALS['phpgw']->accounts->membership($ticket['owner']);

  while(list($key,$entry) = each($group_list))
  {
//      if (check_assign_right(-1, $entry['account_id'], $ticket['group'])) {
	  $GLOBALS['phpgw']->template->set_var('optionname', $entry['account_name']);
	  $GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
	  $GLOBALS['phpgw']->template->set_var('optionselected', $entry['account_id']==$ticket['group']?' SELECTED ':'');
	  $GLOBALS['phpgw']->template->parse('options_group','options_select',true);
//      }
  }
 
  //produce the list of accounts for assigned to
    $account_list = array();
    $account_list = $GLOBALS['egw']->accounts->get_list('accounts');

    $GLOBALS['phpgw']->template->set_var('optionname',lang('None'));
    $GLOBALS['phpgw']->template->set_var('optionvalue','0');
    $GLOBALS['phpgw']->template->set_var('optionselected','');
    $GLOBALS['phpgw']->template->parse('options_assignedto','options_select',true);
    while(list($key,$entry) = @each($account_list))
    {
	// Check ACL for ADD (== assign to)
	if (check_assign_right($entry['account_id'], 1, $ticket['group'])) {
	    if (!array_key_exists ('account_lid', $entry)) {
	    	$entry['account_lid'] = $entry['account_name'];
	    }
	    $GLOBALS['phpgw']->template->set_var('optionname', $GLOBALS['egw']->common->grab_owner_name($entry['account_id']));
	    $GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
	    $GLOBALS['phpgw']->template->set_var('optionselected',
		    ($entry['account_id']==$ticket['assignedto']?'selected="selected"':''));
	    $GLOBALS['phpgw']->template->parse('options_assignedto','options_select',True);
	}
    }

    // Figure out when it was opened and last closed
    $history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('X','O'),'','',$ticket_id);

    while(is_array($history_array) && list(,$value) = each($history_array))
    {
      if($value['status'] == 'O')
      {
        $ticket['opened'] = $GLOBALS['phpgw']->common->show_date($value['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset']));
      }

      if($value['status'] == 'X')
      {
        $ticket['closed'] = $GLOBALS['phpgw']->common->show_date($value['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset']));
      }
    }

    $GLOBALS['phpgw']->template->set_var('options_category',$GLOBALS['phpgw']->categories->formated_list('select','all',$ticket['category'],$ticket['category'],True));

    /**************************************************************\
    * Display additional notes                                     *
    \**************************************************************/
    $history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('C'),'','',$ticket_id);
    $i = 0;
    while(is_array($history_array) && list(,$value) = each($history_array))
    {
      $GLOBALS['phpgw']->template->set_var('row_class',++$i & 1 ? 'row_off' : 'row_on');

      $GLOBALS['phpgw']->template->set_var('lang_date',lang('Date'));
      $GLOBALS['phpgw']->template->set_var('lang_user',lang('User'));

      $GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
      $GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

      if ($ticket['converted'] == 'Y')
      {
        $GLOBALS['phpgw']->template->set_var('ticket_is_in_tracker',lang('ticket is in tracker'));
      }
      else
      {
        $GLOBALS['phpgw']->template->set_var('ticket_is_in_tracker','');
      }

      /* Remove old HTML from the notes -- MSc 060131 */
//      $value['new_value'] = preg_replace('/<a href="[^"]+">([^<]+)<\/a>/', '$1', $value['new_value']);
      $GLOBALS['phpgw']->template->set_var('value_note',html_activate_urls(nl2br(htmlspecialchars(stripslashes($value['new_value'])))));
      $GLOBALS['phpgw']->template->fp('rows_notes','additional_notes_row',True);
    }

    if(!count($history_array))
    {
      $GLOBALS['phpgw']->template->set_var('lang_no_additional_notes',lang('No additional notes'));
      $GLOBALS['phpgw']->template->fp('rows_notes','additional_notes_row_empty',True);
    }

    /**************************************************************\
    * Display record history                                       *
    \**************************************************************/
    $GLOBALS['phpgw']->template->set_var('lang_history',lang('History'));
    $GLOBALS['phpgw']->template->set_var('lang_user',lang('User'));
    $GLOBALS['phpgw']->template->set_var('lang_date',lang('Date'));
    $GLOBALS['phpgw']->template->set_var('lang_action',lang('Action'));
    $GLOBALS['phpgw']->template->set_var('lang_new_value',lang('New Value'));
    $GLOBALS['phpgw']->template->set_var('lang_old_value',lang('Old Value'));

    $i=0;
    $history_array = $GLOBALS['phpgw']->historylog->return_array(array('C'),array(),'','',$ticket_id);
    while(is_array($history_array) && list(,$value) = each($history_array))
    {
      $GLOBALS['phpgw']->template->set_var('row_class',++$i & 1 ? 'row_off' : 'row_on');

      $GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
      $GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

      switch($value['status'])
      {
        case 'R': $type = lang('Re-opened'); break;
        case 'X': $type = lang('Closed');    break;
        case 'O': $type = lang('Opened');    break;
        case 'A': $type = lang('Re-assigned'); break;
        case 'P': $type = lang('Priority changed'); break;
        case 'T': $type = lang('Category changed'); break;
        case 'S': $type = lang('Subject changed'); break;
        case 'H': $type = lang('Billable hours changed'); break;
        case 'B': $type = lang('Billable rate changed'); break;
        case 'G': $type = lang('Group ownership changed'); break;
        case 'N': $type = lang('State changed'); break;
        case 'D': $type = lang('Due Date changed'); break;
        default: break;
      }

      $GLOBALS['phpgw']->template->set_var('value_action',($type?$type:'&nbsp;'));
      unset($type);

      if($value['status'] == 'A')
      {
        if(!$value['new_value'])
        {
          $GLOBALS['phpgw']->template->set_var('value_new_value',lang('None'));
        }
        else
        {
          $GLOBALS['phpgw']->template->set_var('value_new_value',$GLOBALS['phpgw']->accounts->id2name($value['new_value']));
        }

        if(!$value['old_value'])
        {
          $GLOBALS['phpgw']->template->set_var('value_old_value',lang('None'));
        }
        else
        {
          $GLOBALS['phpgw']->template->set_var('value_old_value',$GLOBALS['phpgw']->accounts->id2name($value['old_value']));
        }
      }
      elseif($value['status'] == 'T')
      {
        $GLOBALS['phpgw']->template->set_var('value_new_value',$GLOBALS['phpgw']->categories->id2name($value['new_value']));
        $GLOBALS['phpgw']->template->set_var('value_old_value',$GLOBALS['phpgw']->categories->id2name($value['old_value']));
      }
      elseif($value['status'] == 'G')
      {
        $s = $GLOBALS['phpgw']->accounts->id2name($value['new_value']);
        $s = ($s ? $s : '--');
        $GLOBALS['phpgw']->template->set_var('value_new_value',$s);

        $s = $GLOBALS['phpgw']->accounts->id2name($value['old_value']);
        $s = ($s ? $s : '--');
        $GLOBALS['phpgw']->template->set_var('value_old_value',$s);
      }
      elseif($value['status'] == 'N')
      {
        $s = id2field('phpgw_tts_states','state_name','state_id',$value['new_value']);
        $s = ($s ? $s : '--');
        $GLOBALS['phpgw']->template->set_var('value_new_value',$s);

        $s = id2field('phpgw_tts_states','state_name','state_id',$value['old_value']);
        $s = ($s ? $s : '--');
        $GLOBALS['phpgw']->template->set_var('value_old_value',$s);
      }
      elseif($value['status'] != 'O' && $value['new_value'])
      {
        $GLOBALS['phpgw']->template->set_var('value_new_value',$value['new_value']);
        $GLOBALS['phpgw']->template->set_var('value_old_value',$value['old_value']);
      }
      else
      {
        $GLOBALS['phpgw']->template->set_var('value_new_value','&nbsp;');
        $GLOBALS['phpgw']->template->set_var('value_old_value','&nbsp;');
      }

      $GLOBALS['phpgw']->template->fp('rows_history','row_history',True);
    }

    if(!count($history_array))
    {
      $GLOBALS['phpgw']->template->set_var('lang_no_history',lang('No history for this record'));
      $GLOBALS['phpgw']->template->fp('rows_history','row_history_empty',True);
    }

    $GLOBALS['phpgw']->template->set_var('lang_update',lang('Update'));

//    $phpgw->template->set_var('additonal_details_rows',$s);

    $GLOBALS['phpgw']->template->set_var('viewticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort,'start'=>$start)));
    $GLOBALS['phpgw']->template->set_var('ticket_id', $ticket_id);

    $GLOBALS['phpgw']->template->set_var('lang_assignedfrom', lang('Assigned from'));
    $GLOBALS['phpgw']->template->set_var('value_owner',$GLOBALS['egw']->common->grab_owner_name($ticket['owner']));

    $GLOBALS['phpgw']->template->set_var('lang_opendate', lang('Open Date'));
    $GLOBALS['phpgw']->template->set_var('value_opendate',$ticket['opened']);

    $GLOBALS['phpgw']->template->set_var('lang_duedate', lang('Due Date'));
    $GLOBALS['phpgw']->template->set_var('value_duedate',
	    ($ticket['duedate'] != 'NULL')?$ticket['duedate']:'');
    
    $GLOBALS['phpgw']->template->set_var('lang_priority', lang('Priority'));
    $GLOBALS['phpgw']->template->set_var('value_priority', $ticket['priority'].' - '.lang(prioname($ticket['priority'])));

    $GLOBALS['phpgw']->template->set_var('lang_group', lang('Group'));
    $s = $GLOBALS['phpgw']->accounts->id2name($ticket['group']);
    $s = ($s ? $s : '--');
    $GLOBALS['phpgw']->template->set_var('value_group',$s);

    $GLOBALS['phpgw']->template->set_var('lang_state', lang('State'));
    $s = id2field('phpgw_tts_states','state_name','state_id',$ticket['state']);
    $GLOBALS['phpgw']->template->set_var('value_state',$s ? $s : '--');
    $t = id2field('phpgw_tts_states','state_description','state_id',$ticket['state'],False);
    $GLOBALS['phpgw']->template->set_var('value_state_description',
      $t ? $t : '-- '.lang('Missing description').' --');

    $GLOBALS['phpgw']->template->set_var('lang_billable_hours',lang('Billable hours'));
    $GLOBALS['phpgw']->template->set_var('value_billable_hours',$ticket['billable_hours']);
    $GLOBALS['phpgw']->template->set_var('value_billable_hours_pretty',($ticket['billable_hours']==0)?'--':sprintf('%.2f', $ticket['billable_hours']));

    $GLOBALS['phpgw']->template->set_var('lang_billable_hours_rate',lang('Billable rate'));
    $GLOBALS['phpgw']->template->set_var('value_billable_hours_rate',$ticket['billable_rate']);
    $GLOBALS['phpgw']->template->set_var('value_billable_hours_rate_pretty',($ticket['billable_rate']==0)?'--':sprintf('%.2f', $ticket['billable_rate']));

    $GLOBALS['phpgw']->template->set_var('lang_billable_hours_total',lang('Total billable'));
    $GLOBALS['phpgw']->template->set_var('value_billable_hours_total',
	    ($ticket['billable_hours'] * $ticket['billable_rate'])==0?'--':sprintf('%01.2f',($ticket['billable_hours'] * $ticket['billable_rate'])));

    $GLOBALS['phpgw']->template->set_var('lang_assignedto',lang('Assigned to'));
    if($ticket['assignedto'])
    {
      $assignedto = $GLOBALS['egw']->common->grab_owner_name($ticket['assignedto']);
    }
    else
    {
      $assignedto = lang('None');
    }
    $GLOBALS['phpgw']->template->set_var('value_assignedto',$assignedto);

    $GLOBALS['phpgw']->template->set_var('lang_subject', lang('Subject'));

    $GLOBALS['phpgw']->template->set_var('lang_details', lang('Details'));

    // cope with old, wrongly saved entries, stripslashes would remove single backslashes too
    foreach(array('subject','details') as $name)
    {
      $ticket[$name] = str_replace(array('\\\'','\\"','\\\\'),array("'",'"','\\'),$ticket[$name]);
      $ticket[$name] = preg_replace('/<a href="[^"]+">([^<]+)<\/a>/', '$1', $ticket[$name]);
    }
    $GLOBALS['phpgw']->template->set_var('value_details', html_activate_urls(nl2br(htmlspecialchars($ticket['details']))));

    $GLOBALS['phpgw']->template->set_var('value_subject', $ticket['subject']);

    $GLOBALS['phpgw']->template->set_var(array(
	    'lang_additional_notes'	=> lang('Additional notes'),
	    'lang_save'			=> lang('Save'),
	    'lang_apply'	=> lang('Apply'),
	    'lang_cancel'	=> lang('Cancel')
	    )
	);

    $GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
    $GLOBALS['phpgw']->template->set_var('value_category',$GLOBALS['phpgw']->categories->id2name($ticket['category']));

    $GLOBALS['phpgw']->template->set_var('options_select','');

    
    // Ticket state transitions
    
    $GLOBALS['phpgw']->template->set_var(array(
	    'lang_update_state' 	=> lang('Update ticket state'),
	    'lang_keep_present_state'	=> lang('Keep the present state'),
	    'lang_present_state'	=> try_lang(id2field('phpgw_tts_states','state_name','state_id',$ticket['state']))
	    )
	);

    $db = clone($GLOBALS['phpgw']->db);
    $db->query("select * from phpgw_tts_transitions where transition_source_state='".$ticket['state']."'",__LINE__,__FILE__);

    while($db->next_record())
    {
	$GLOBALS['phpgw']->template->set_var('update_state_value',
		$db->f('transition_email').$db->f('transition_target_state')	# this is ugly, but simple -- MSc
		);
	$GLOBALS['phpgw']->template->set_var(array(
		'lang_state'		=> try_lang(id2field('phpgw_tts_states','state_name','state_id',$db->f('transition_target_state'))),
		'update_state_text'	=> lang($db->f('transition_description'))
		)
	);
	$GLOBALS['phpgw']->template->parse('update_state_group', 'update_state_items', True);
    }


    $GLOBALS['phpgw']->template->pfp('out','form');
    $GLOBALS['phpgw']->common->phpgw_footer();

  }
  else // save or apply
  {
    $ticket = $_POST['ticket'];

    // DB Content is fresher than http posted value.
    $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();

    $oldassigned = $GLOBALS['phpgw']->db->f('ticket_assignedto');
    $oldpriority = $GLOBALS['phpgw']->db->f('ticket_priority');
    $oldcategory = $GLOBALS['phpgw']->db->f('ticket_category');
    $old_status  = $GLOBALS['phpgw']->db->f('ticket_status');
    $old_billable_hours = $GLOBALS['phpgw']->db->f('ticket_billable_hours');
    $old_billable_rate = $GLOBALS['phpgw']->db->f('ticket_billable_rate');
    $old_group   = $GLOBALS['phpgw']->db->f('ticket_group');
    $old_state   = $GLOBALS['phpgw']->db->f('ticket_state');
    $old_duedate = $GLOBALS['phpgw']->db->f('ticket_due');

    $GLOBALS['phpgw']->db->transaction_begin();

    /*
    **  phpgw_tts_append.append_type - Defs
    **  R - Reopen ticket
    ** X - Ticket closed
    ** O - Ticket opened
    ** C - Comment appended
    ** A - Ticket assignment
    ** P - Priority change
    ** T - Category change
    ** S - Subject change
    ** B - Billing rate
    ** H - Billing hours
    ** G - Group
    ** N - Petri Net State change (?)
    ** D - Due Date
    */
  
    $no_error = True;
    $fields_updated = False;
    $send_mail = False;
    
    // split $ticket['state'] in email(Y/N) and new state
    if (strlen($ticket['state']) == 1) {
       $transition_email = 'Y'; // Default 'Y'
    } else {
       $transition_email = substr($ticket['state'],0,1);
       $ticket['state'] = substr($ticket['state'],1);
    }
 
    if($ticket['state'] && $old_state != $ticket['state'])
    {
	$fields_updated = True;

	$GLOBALS['phpgw']->historylog->add('N',$ticket_id,$ticket['state'],$old_state);
	$GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_state='" . $ticket['state']
		. "' where ticket_id='$ticket_id'",__LINE__,__FILE__);

	// now let's find out what's the new status
	$GLOBALS['phpgw']->db->query("select state_open from phpgw_tts_states".
		" where state_id='".$ticket['state']."'", __LINE__, __FILE__);
	$GLOBALS['phpgw']->db->next_record();
	$ticket['status'] = $GLOBALS['phpgw']->db->f('state_open');

	// is the owner to be notified?
	if ($transition_email == 'Y') {
	    $send_mail = True;
	}
	
	// As the user can't change the status directly, we do the following
	// in this very if-block
	if($old_status != $ticket['status'])
	{
	    $send_mail = True;
	    $GLOBALS['phpgw']->historylog->add($ticket['status'],$ticket_id,$ticket['status'],$old_status);
	    $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_status='"
		    . $ticket['status'] . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
	}

    }

    if($old_group != $ticket['group'])
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_group='" . $ticket['group']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('G',$ticket_id,$ticket['group'],$old_group);
    }

    if($oldassigned != $ticket['assignedto'])
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_assignedto='" . $ticket['assignedto']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('A',$ticket_id,$ticket['assignedto'],$oldassigned);
    }

    if($oldpriority != $ticket['priority'])
    {
      $fields_updated = True;
      $ticket['priority']=intval($ticket['priority']);
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_priority='" . $ticket['priority']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('P',$ticket_id,$ticket['priority'],$oldpriority);
    }

    if($oldcategory != $ticket['category'])
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_category='" . $ticket['category']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('T',$ticket_id,$ticket['category'],$oldcategory);
    }

    if($old_billable_hours != $ticket['billable_hours'])
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_billable_hours='" . $ticket['billable_hours']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('H',$ticket_id,$ticket['billable_hours'],$old_billable_hours);
    }

    if($old_billable_rate != $ticket['billable_rate'])
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_billable_rate='" . $ticket['billable_rate']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('B',$ticket_id,$ticket['billable_rate'],$old_billable_rate);
    }

    // Sanitize Due Date
    $td = $ticket['duedate'];	    // save some typing
    if (strlen($td) == 10) {		# 2005-09-07
	$td .= ' 23:59:00';
    } elseif (strlen($td) == 16) {	# 2005-09-07 12:13
	$td .= ':00';
    } else {
	$td = 'NULL';
    }
    if($old_duedate != $td)
    {
      $fields_updated = True;
      $send_mail = True;
      if ($td != 'NULL') $td = "'$td'";
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_due=$td".
	      " where ticket_id='$ticket_id'",__LINE__,__FILE__);
      // we save the given DueDate into the history, on purpose
      $GLOBALS['phpgw']->historylog->add('D',$ticket_id,$ticket['duedate'],$old_duedate);
    }


    if($ticket['note'])
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->historylog->add('C',$ticket_id,$ticket['note'],'');

      // Do this before we go into mail_ticket()
      $GLOBALS['phpgw']->db->transaction_commit();
    }
    else
    {
      // Only do our commit once
      $GLOBALS['phpgw']->db->transaction_commit();
    }

    if($fields_updated)
    {
      $GLOBALS['phpgw']->session->appsession('messages','tts',lang('Ticket has been updated').'<br/>'.$messages);

      if($GLOBALS['phpgw']->config->config_data['mailnotification'])
      {
        mail_ticket($ticket_id, $send_mail);
      }
    }

    if ($_POST['save'] && $no_error)
    {
      $filter = reg_var('filter','GET');
      $sort   = reg_var('sort','GET');
      $order  = reg_var('order','GET');
      $start  = reg_var('start','GET','numeric',0);
      $GLOBALS['phpgw']->redirect_link('/tts/index.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort,'start'=>$start));
    }
    else  // apply
    {
      $GLOBALS['phpgw']->redirect_link('/tts/viewticket_details.php',array('ticket_id'=>$ticket_id,'filter'=>$filter,'order'=>$order,'sort'=>$sort));
    }
  }
?>
