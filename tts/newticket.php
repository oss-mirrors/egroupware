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

  /*
  ** get chainedSelectors class, require will produce fatal error if chainedSelectors is not found
  ** include produce warning instead of fatal error
  */
  require("inc/chainedSelectors.php");



  $GLOBALS['phpgw']->config->read_repository();

//ACL  //add by Josip
//ACL    if (!$GLOBALS['phpgw']->acl->check('add',1,'tts'))
//ACL    {
//ACL      $GLOBALS['phpgw']->redirect_link('/tts/index.php');
//ACL    }
//ACL    ////

  if($_POST['cancel'])
  {
    $GLOBALS['phpgw']->redirect_link('/tts/index.php');
  }

  if($_POST['submit'] && !empty($_POST['ticket_subject']))
  {
  
    if (get_magic_quotes_gpc())
    {
      foreach(array('subject','details') as $name)
      {
        $_POST['ticket_'.$name] = stripslashes($_POST['ticket_'.$name]);
      }
    }
    $_POST['ticket_details'] = html_activate_urls($_POST['ticket_details']);

    $ticket_billable_hours = str_replace(',','.',$_POST['ticket_billable_hours']);
    $ticket_billable_rate = str_replace(',','.',$_POST['ticket_billable_rate']);

    // changed insert for custom atributes by Josip
    $GLOBALS['phpgw']->db->query("insert into phpgw_tts_tickets (ticket_state,ticket_group,ticket_priority,ticket_owner,"
      . "ticket_caller_name,ticket_caller_telephone,ticket_caller_telephone_2,ticket_caller_email,ticket_caller_ticket_id,ticket_caller_password,"
      . "ticket_caller_address,ticket_caller_address_2, ticket_caller_audio_file,"
      . "ticket_assignedto,ticket_subject,ticket_category,ticket_billable_hours,"
      . "ticket_billable_rate,ticket_status,ticket_details) values ('"
      . intval($_POST['ticket_state']) . "','"
      . intval($_POST['ticket_group']) . "','"
      . intval($_POST['ticket_priority']) . "','"
      . $GLOBALS['phpgw_info']['user']['account_id'] . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_name']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_telephone']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_telephone_2']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_email']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_ticket_id']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_password']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_address']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_address_2']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_caller_audio_file']) . "','"
      . intval($_POST['ticket_assignedto']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_subject']) . "','"
      . intval($_POST['ticket_category']) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($ticket_billable_hours) . "','"
      . $GLOBALS['phpgw']->db->db_addslashes($ticket_billable_rate) . "','O','"
      . $GLOBALS['phpgw']->db->db_addslashes($_POST['ticket_details']) . "')",__LINE__,__FILE__);

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

  //added by Josip
  // chained combo for category, group and user
  $selectorNames = array(
        CS_FORM=>"newTicket",
        CS_FIRST_SELECTOR=>"ticket_category",
        CS_SECOND_SELECTOR=>"ticket_group",
        CS_THIRD_SELECTOR=>"ticket_assignedto");

        /*
  $Query = "SELECT a.cat_id, b.cat_name, a.account_id group_id, c.account_lid group_name, d.account_id user_id, d.account_lid user_name ".
             "FROM phpgw_tts_categories_groups a, phpgw_categories b, phpgw_accounts c, phpgw_accounts d ".
             "WHERE a.cat_id = b.cat_id AND a.account_id = c.account_id AND d.account_primary_group = a.account_id ".
             "ORDER BY b.cat_name, c.account_lid, d.account_lid";
          */

  $Query = "SELECT a.cat_id, b.cat_name, a.account_id group_id, c.account_lid group_name, d.account_id user_id, d.account_lid user_name ".
            "FROM phpgw_tts_categories_groups a, phpgw_categories b, phpgw_accounts c, phpgw_accounts d, phpgw_acl ga, phpgw_acl gu ".
            "WHERE a.cat_id = b.cat_id AND a.account_id = c.account_id ".
            "AND a.account_id = ga.acl_account AND ga.acl_appname = 'tts' AND ga.acl_location='run' ".
            "AND d.account_id = gu.acl_account AND gu.acl_appname = 'phpgw_group' AND gu.acl_location = c.account_id ".
            "ORDER BY b.cat_name, c.account_lid, d.account_lid ";

  $db = $GLOBALS['phpgw']->db;

  $db->query($Query,__LINE__,__FILE__);

  while ($db->next_record())
  {
      $selectorData[] = array(
          CS_SOURCE_ID=>$db->f('cat_id'),
          CS_SOURCE_LABEL=>$db->f('cat_name'),
          CS_TARGET_ID=>$db->f('group_id'),
          CS_TARGET_LABEL=>$db->f('group_name'),
          CS_TARGET2_ID=>$db->f('user_id'),
          CS_TARGET2_LABEL=>$db->f('user_name'));
  }
  //instantiate class
  $categoryGroupUser = new chainedSelectors(
      $selectorNames,
      $selectorData);


  //added by Josip to accesp input param
  $caller_audio_file = reg_var('audiofile','GET');
  $caller_telephone_2 = reg_var('telephone2','GET');

  $ticketid = reg_var('ticketid','GET');
  $ticketidwnt = reg_var('ticketidwnt','GET');

  if ($ticketid <> "")
  {
    // duplicate data from the parameter ticketid
    $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticketid'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();

    $caller_name = $GLOBALS['phpgw']->db->f('ticket_caller_name');
    $caller_email = $GLOBALS['phpgw']->db->f('ticket_caller_email');

    $caller_telephone = $GLOBALS['phpgw']->db->f('ticket_caller_telephone');
    $caller_address = $GLOBALS['phpgw']->db->f('ticket_caller_address');
    $caller_address_2 = $GLOBALS['phpgw']->db->f('ticket_caller_address_2');

    $caller_audio_file = $GLOBALS['phpgw']->db->f('ticket_caller_audio_file');
    $caller_telephone_2 = $GLOBALS['phpgw']->db->f('ticket_caller_telephone_2');


  }
  else
  {
      if ($ticketidwnt <> "")
      {
        // duplicate data from the parameter ticketid
        $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets_wnt where ticket_id='$ticketidwnt'",__LINE__,__FILE__);
        $GLOBALS['phpgw']->db->next_record();

        $caller_name = $GLOBALS['phpgw']->db->f('ticket_caller_name');
        $caller_email = $GLOBALS['phpgw']->db->f('ticket_caller_email');

        $caller_telephone = $GLOBALS['phpgw']->db->f('ticket_caller_telephone');
        $caller_address = $GLOBALS['phpgw']->db->f('ticket_caller_address');
        $caller_address_2 = $GLOBALS['phpgw']->db->f('ticket_caller_address_2');

        $details =  $GLOBALS['phpgw']->db->f('ticket_details');

      }

  }

  // end add





  $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'] . ' - ' . lang('Create new ticket');

  $GLOBALS['phpgw']->common->phpgw_header();



  echo parse_navbar();

//  $temp='<script type="text/javascript" language="JavaScript"> '.$categoryGroupUser->printUpdateFunction().'</script> ';
//  $temp2=$categoryGroupUser->printUpdateFunction();


  $GLOBALS['phpgw']->template->set_file(array(
    'newticket'   => 'newticket.tpl'
  ));

  //$GLOBALS['phpgw']->template->set_var('CatGroupUser','<script type="text/javascript" language="JavaScript"> '.$categoryGroupUser->printUpdateFunction().'</script> ');

  //$GLOBALS['phpgw']->template->set_block('newticket','cgu');
  $GLOBALS['phpgw']->template->set_var('CatGroupUser','<script type="text/javascript" language="JavaScript"> '.$categoryGroupUser->printUpdateFunction2().'</script>');

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
  //added custom atributes by Josip
  $GLOBALS['phpgw']->template->set_var('lang_caller_name',lang('Caller Name'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_telephone',lang('Caller Telephone'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_telephone_2',lang('Caller Telephone ID'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_email',lang('Caller Email'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_ticket_id',lang('Caller Ticket ID'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_password',lang('Caller Password'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_address',lang('Caller Address'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_address_2',lang('Caller Address 2'));
  $GLOBALS['phpgw']->template->set_var('lang_caller_audio_file',lang('Caller Audio File'));
  //
  $GLOBALS['phpgw']->template->set_var('lang_submit',lang('Save'));
  $GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));
  $GLOBALS['phpgw']->template->set_var('lang_no_subject',lang('Please enter the subject of the ticket, otherwise the ticket cannot be stored.'));

  $GLOBALS['phpgw']->template->set_var('lang_billable_hours',lang('Billable hours'));
  $GLOBALS['phpgw']->template->set_var('lang_billable_hours_rate',lang('Billable hours rate'));

  $GLOBALS['phpgw']->template->set_var('row_off', $GLOBALS['phpgw_info']['theme']['row_off']);
  $GLOBALS['phpgw']->template->set_var('row_on', $GLOBALS['phpgw_info']['theme']['row_on']);
  $GLOBALS['phpgw']->template->set_var('th_bg', $GLOBALS['phpgw_info']['theme']['th_bg']);

  //added by Josip
  $GLOBALS['phpgw']->template->set_var('value_caller_telephone_2',$caller_telephone_2);
  $GLOBALS['phpgw']->template->set_var('value_caller_audio_file',$caller_audio_file);
  $GLOBALS['phpgw']->template->set_var('value_caller_name',$caller_name);
  $GLOBALS['phpgw']->template->set_var('value_caller_email',$caller_email);
  $GLOBALS['phpgw']->template->set_var('value_caller_telephone',$caller_telephone);
  $GLOBALS['phpgw']->template->set_var('value_caller_address',$caller_address);
  $GLOBALS['phpgw']->template->set_var('value_caller_address_2',$caller_address_2);

  if ($details == "")
  {
     $details = $_POST['ticket_details'];
  }
  $GLOBALS['phpgw']->template->set_var('value_details',$details);

  //random string for caller password (ticket)
  //$str_rand1 = strtoupper($GLOBALS['phpgw']->common->randomstringnumber(6));
  //$str_rand2 = strtoupper($GLOBALS['phpgw']->common->randomstringnumber(4));
  //$str_rand1 = strtoupper($GLOBALS['phpgw']->common->randomstring(6));
  //$str_rand2 = strtoupper($GLOBALS['phpgw']->common->randomstring(4));
  $str_rand1 = randomstringnumber(6);
  $str_rand2 = randomstringnumber(4);
  $GLOBALS['phpgw']->template->set_var('value_caller_ticket_id',$str_rand1);
  $GLOBALS['phpgw']->template->set_var('value_caller_password',$str_rand2);
  //

//  $GLOBALS['phpgw']->template->set_var('value_details',$_POST['ticket_details']);
  $GLOBALS['phpgw']->template->set_var('value_subject',$_POST['ticket_subject']);
  $GLOBALS['phpgw']->template->set_var('value_billable_hours',($_POST['ticket_billable_hours']?$_POST['ticket_billable_hours']:'0.00'));
  $GLOBALS['phpgw']->template->set_var('value_billable_hours_rate',($_POST['ticket_billable_rate']?$_POST['ticket_billable_rate']:'0.00'));



  //produce the list of categories
  /*$s = '<select name="ticket_category">' . $GLOBALS['phpgw']->categories->formated_list('select','all',$_POST['ticket_category'],True) . '</select>';
  $GLOBALS['phpgw']->template->set_var('value_category',$s);    
  */
  unset($s);
  $s=$categoryGroupUser->printSelector1();
  $GLOBALS['phpgw']->template->set_var('value_category',$s);


  
  //produce the list of groups
  /*unset($s);
  $groups = CreateObject('phpgwapi.accounts');
  $group_list = array();
  $group_list = $GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']);

  while(list($key,$entry) = each($group_list))
  {
    $GLOBALS['phpgw']->template->set_var('optionname', $entry['account_name']);
    $GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
    $GLOBALS['phpgw']->template->set_var('optionselected', $entry['account_id']==$_POST['ticket_group']?' SELECTED ':'');
    $GLOBALS['phpgw']->template->parse('options_group','options_select',true);
  }
  */
  $s=$categoryGroupUser->printSelector2();
  $GLOBALS['phpgw']->template->set_var('value_group',$s);



  

  //produce the list of accounts for assigned to
  /*$s = '<option value="0">' . lang('None') . '</option>';
  $accounts = $groups;
  $accounts->account_id = $group_id;
  $account_list = $accounts->get_list('accounts');
  while(list($key,$entry) = each($account_list))
  {
    $s .= '<option value="' . $entry['account_id'] . '" '
      . ($entry['account_id']==$_POST['ticket_assignedto']?' SELECTED ':'')
      . '>' . $entry['account_lid'] . '</option>';
  }
  $GLOBALS['phpgw']->template->set_var('value_assignedto','<select name="ticket_assignedto">' . $s . '</select>');
  */
  $s=$categoryGroupUser->printSelector3();
  $GLOBALS['phpgw']->template->set_var('value_assignedto',$s);

  // Choose the correct priority to display
  $prority_selected[$ticket_priority] = ' selected';
  $priority_comment[1]  = ' - '.lang('Lowest');
  $priority_comment[5]  = ' - '.lang('Medium');
  $priority_comment[10] = ' - '.lang('Highest');
  for($i=1; $i<=10; $i++)
  {
    $priority_select .= '<option value="' . $i . '"'
      . ($i==$_POST['ticket_priority']?' SELECTED ':'')
      . '>' . $i . $priority_comment[$i] . '</option>';
  }
  $GLOBALS['phpgw']->template->set_var('value_priority','<select name="ticket_priority">' . $priority_select . '</select>');

  // Choose the initial state to display
  $GLOBALS['phpgw']->template->set_var('options_state',
    listid_field('phpgw_tts_states','state_name','state_id',$_POST['ticket_state'], "state_initial=1"));

  $GLOBALS['phpgw']->template->set_var('tts_select_options','');
  $GLOBALS['phpgw']->template->set_var('tts_new_lstcategory','');
  $GLOBALS['phpgw']->template->set_var('tts_new_lstassignto','');


  //add by Josip
  $GLOBALS['phpgw']->template->set_var('initCatGroupUser','<script type="text/javascript" language="JavaScript">'.$categoryGroupUser->initialize2().'</script>');
  //end add

  $GLOBALS['phpgw']->template->pfp('out','form');
  $GLOBALS['phpgw']->common->phpgw_footer();
?>
