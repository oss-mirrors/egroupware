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
  /*
  ** get chainedSelectors class, require will produce fatal error if chainedSelectors is not found
  ** include produce warning instead of fatal error
  */
  require("inc/chainedSelectors.php");

  $filter = reg_var('filter','GET');
//  $start  = reg_var('start','GET','numeric',15);
//  $start  = (int) get_var('start',array('POST','GET'));
  $start  = reg_var('start','GET');
  $f_status = reg_var('f_status','GET');
  $sort   = reg_var('sort','GET');
  $order  = reg_var('order','GET');
  $searchfilter = reg_var('searchfilter','POST');

  if($_POST['cancel'])
  {
    $GLOBALS['phpgw']->redirect_link('/tts/index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort));
  }
   //add by Josip
  $can_add=False;
  $can_close=False;
  $can_mon=False;
  $can_vip=False;
  $can_view_all=False;

  $def_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];
  // if user is admin, or VIP user, or HD_OPER user then ...
//ACL  if (($def_group == '16') || ($def_group == '6') || $GLOBALS['phpgw']->acl->check('add',1,'tts'))
//ACL  {
           $can_view_all=True;
           $can_close=True;
//ACL  }
//ACL  if ($GLOBALS['phpgw']->acl->check('mon',1,'tts'))
//ACL  {
           $can_mon=True;
           $can_view_all=True;
//ACL  }
//ACL  if ($GLOBALS['phpgw']->acl->check('vip',1,'tts'))
//ACL  {
           $can_vip=True;
           $can_view_all=True;
//ACL  }
//ACL  if ($GLOBALS['phpgw']->acl->check('add',1,'tts'))
//ACL  {
           $can_add=True;
           $can_close=True;
//ACL  }
  //


  $ticket_id = intval(get_var('ticket_id',array('POST','GET')));

  $GLOBALS['phpgw']->config->read_repository();

  $GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

  //added custom Caller ... by Josip
  $GLOBALS['phpgw']->historylog->types = array(
    'K1' => 'Caller Name',
    'K2' => 'Caller Telephone',
    'K3' => 'Caller Telephone 2',
    'K4' => 'Caller Address',
    'K5' => 'Caller Address 2',
    'K6' => 'Caller Email',
    'K7' => 'Caller Password',
    'M7' => 'Caller Ticket ID',
    'K8' => 'Caller Audio File',
    'K9' => 'Caller Satisfaction',
    'K0' => 'Caller Solution',
    'E' => 'Escalation',
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
    'N' => 'State changed'
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


     //added by Josip
  // chained combo for category, group and user
  $selectorNames = array(
        CS_FORM=>"viewTicketDetails",
        CS_FIRST_SELECTOR=>"field_category",
        CS_SECOND_SELECTOR=>"field_group",
        CS_THIRD_SELECTOR=>"field_assignedto");


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


  //added by Josip to accept input param
  $caller_audio_file = reg_var('audiofile','GET');
  $caller_telephone_2 = reg_var('telephone2','GET');

  $GLOBALS['phpgw']->template->set_var('CatGroupUser','<script type="text/javascript" language="JavaScript"> '.$categoryGroupUser->printUpdateFunction2().'</script>');


  // end add


    $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'] . ' - ' . lang('View Job Detail');
    $GLOBALS['phpgw']->common->phpgw_header();
    echo parse_navbar();

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
    //added custom atributes by Josip
    $ticket['caller_name']      = $GLOBALS['phpgw']->db->f('ticket_caller_name');
    $ticket['caller_telephone'] = $GLOBALS['phpgw']->db->f('ticket_caller_telephone');
    $ticket['caller_telephone_2'] = $GLOBALS['phpgw']->db->f('ticket_caller_telephone_2');
    $ticket['caller_email']     = $GLOBALS['phpgw']->db->f('ticket_caller_email');
    $ticket['caller_ticket_id']  = $GLOBALS['phpgw']->db->f('ticket_caller_ticket_id');
    $ticket['caller_password']  = $GLOBALS['phpgw']->db->f('ticket_caller_password');
    $ticket['caller_address']   = $GLOBALS['phpgw']->db->f('ticket_caller_address');
    $ticket['caller_address_2']   = $GLOBALS['phpgw']->db->f('ticket_caller_address_2');
    $ticket['caller_audio_file']= $GLOBALS['phpgw']->db->f('ticket_caller_audio_file');
    $ticket['caller_satisfaction']          = $GLOBALS['phpgw']->db->f('ticket_caller_satisfaction');
    $ticket['caller_solution']          = $GLOBALS['phpgw']->db->f('ticket_caller_solution');
    $ticket['escalation']          = $GLOBALS['phpgw']->db->f('ticket_escalation');
    $ticket['escalation_time']          = $GLOBALS['phpgw']->db->f('ticket_escalation_time');
    // end custom atributes
    $ticket['assignedto']     = $GLOBALS['phpgw']->db->f('ticket_assignedto');
    //$ticket[category]       = $GLOBALS['phpgw']->db->f('ticket_category');
    $ticket[category]    = $GLOBALS['phpgw']->db->f('ticket_category');


    $ticket['details']        = $GLOBALS['phpgw']->db->f('ticket_details');
    $ticket['subject']        = $GLOBALS['phpgw']->db->f('ticket_subject');
    $ticket['priority']       = $GLOBALS['phpgw']->db->f('ticket_priority');
    $ticket['owner']          = $GLOBALS['phpgw']->db->f('ticket_owner');
    $ticket['group']          = $GLOBALS['phpgw']->db->f('ticket_group');
    $ticket['status']         = $GLOBALS['phpgw']->db->f('ticket_status');
    $ticket['state']          = $GLOBALS['phpgw']->db->f('ticket_state');


    $GLOBALS['phpgw']->template->set_file('viewticket','viewticket_details.tpl');
    $GLOBALS['phpgw']->template->set_block('viewticket','options_select');
    $GLOBALS['phpgw']->template->set_block('viewticket','options_select2');
    $GLOBALS['phpgw']->template->set_block('viewticket','additional_notes_row');
    $GLOBALS['phpgw']->template->set_block('viewticket','additional_notes_row_empty');
    $GLOBALS['phpgw']->template->set_block('viewticket','row_history');
    $GLOBALS['phpgw']->template->set_block('viewticket','row_history_empty');
    $GLOBALS['phpgw']->template->set_block('viewticket','form');
    $GLOBALS['phpgw']->template->set_block('form','update_state_items','update_state_group');

    if ($can_add)
    {
        $GLOBALS['phpgw']->template->set_var('duplicate_ticket', '<tr class="th">'.
            '<td colspan="4" align="right"><a href="'.
            $GLOBALS['phpgw']->link('/tts/newticket.php',array('ticketid'=>$ticket_id)).
            '"><b>'.lang('Duplicate ticket').'</b></a></td>'.
        '</tr>');


/*        $GLOBALS['phpgw']->template->set_var('duplicate_ticket', '<tr class="th">'.
            '<td colspan="4" align="right"><a href="'.
            $GLOBALS['phpgw']->link('/tts/newticket.php',array('audiofile'=>$ticket['caller_audio_file'],'telephone2'=>$ticket['caller_telephone_2'],'name'=>$ticket['caller_name'],'telephone'=>$ticket['caller_telephone'],'address'=>$ticket['caller_address'],'address2'=>$ticket['caller_address_2'],'email'=>$ticket['caller_email'])).
            '"><b>'.lang('Duplicate ticket').'</b></a></td>'.
        '</tr>');
*/
      // if user can add and if ticket is open and ticket state is NEW then subject can be changed
      if ($ticket['status'] <> 'X') {
             if ($ticket['state'] == '1')
             {
                 $GLOBALS['phpgw']->template->set_var('modify_subject', '<tr class="th">'.
                 '<td colspan="4" align="right"><a href="'.
                 $GLOBALS['phpgw']->link('/tts/newticket.php',array('ticketid'=>$ticket_id)).
                 '"><b>'.lang('Duplicate ticket').'</b></a></td>'.
                 '</tr>');

                 $GLOBALS['phpgw']->template->set_var('modify_subject', '<tr class="row_off">'.
                        '<td>{lang_subject}:</td>'.
                        '<td colspan="3" ><input name="ticket[subject]" value="{value_subject}" size="110"></td>'.
                    '</tr>');
             }
        }

    }

    $messages .= rtrim($GLOBALS['phpgw']->session->appsession('messages','tts'),"\0");
    if($messages)
    {
      $GLOBALS['phpgw']->template->set_var('messages',$messages);
      $GLOBALS['phpgw']->session->appsession('messages','tts','');
    }

    if($GLOBALS['phpgw']->db->f('ticket_status') == 'C')
    {
      $GLOBALS['phpgw']->template->set_var('t_status','FIX ME! time closed ' . __LINE__); // $GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed')));
    }
    else
    {
      $GLOBALS['phpgw']->template->set_var('t_status', lang('In progress'));
    }

    // Choose the correct priority to display
    $priority_selected[$ticket['priority']] = ' selected';
    $priority_comment[1]  = ' - '.lang('Lowest');
    $priority_comment[5]  = ' - '.lang('Medium');
    $priority_comment[10] = ' - '.lang('Highest');

    for($i=1; $i<=10; $i++)
    {
      $GLOBALS['phpgw']->template->set_var('optionname', $i.$priority_comment[$i]);
      $GLOBALS['phpgw']->template->set_var('optionvalue', $i);
      $GLOBALS['phpgw']->template->set_var('optionselected', $priority_selected[$i]);
      $GLOBALS['phpgw']->template->parse('options_priority','options_select',true);
    }

    //disabled_status --> disable fields for normal user who don't have admin od add privilege
    //disabled_status_status --> disable field for ticket status if user don't have admin or add privilege
    //disabled_status_closed --> disable fields for every type of user if ticket is closed

    $disabled_for_standard_user = "";
    $disabled_field_status = "";
    $disabled_for_admin_user = "";

    if ($ticket['status'] == 'X')
    {
         //add by Josip
        //if ticket is closed then changes are not allowed
//ACL        $disabled_for_standard_user = "DISABLED";
//ACL        $disabled_for_admin_user = "DISABLED";

//ACL        if ($can_add)
//ACL        {
            $disabled_field_status = "";
//ACL        }
//ACL        else
//ACL        {
//ACL            $disabled_field_status = "DISABLED";
//ACL        }
//ACL    }
//ACL    else
//ACL    {
        //add by Josip
        // if user doesn't have appropiate rights the certain fields are disabled or enabled for input
        $disabled_for_admin_user = "";

//ACL        if ($can_add)
//ACL        {
            $disabled_for_standard_user = "";
            $disabled_field_status = "";
//ACL        }
//ACL        else
//ACL        {
//ACL            $disabled_for_standard_user = "DISABLED";
//ACL            $disabled_field_status = "DISABLED";
//ACL        }
    }

    $GLOBALS['phpgw']->template->set_var('disabled_for_standard_user',$disabled_for_standard_user);
    $GLOBALS['phpgw']->template->set_var('disabled_field_status',$disabled_field_status);
    $GLOBALS['phpgw']->template->set_var('disabled_for_admin_user',$disabled_for_admin_user);

    // category
    //$GLOBALS['phpgw']->template->set_var('options_category',$GLOBALS['phpgw']->categories->formated_list('select','all',$ticket[category],$ticket[category],True));
      $s=$categoryGroupUser->printSelector1($ticket['category'],$disabled_for_standard_user);
      $GLOBALS['phpgw']->template->set_var('value_category2',$s);

    // group
    /*
    1. original
    $group_list = array();
    $group_list = $GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']);

    while(list($key,$entry) = each($group_list))
    {
      $tag = '';
      if($entry['account_id'] == $ticket['group'])
      {
        $tag = 'selected';
      }
      $GLOBALS['phpgw']->template->set_var('optionname', $entry['account_name']);
      $GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
      $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
      $GLOBALS['phpgw']->template->parse('options_group','options_select',true);
    }
    */
    $s=$categoryGroupUser->printSelector2($ticket['group'], $GLOBALS['phpgw']->accounts->id2name($ticket['group']),$disabled_for_standard_user);
    $GLOBALS['phpgw']->template->set_var('value_group2',$s);

    /*
    //3. show only name of the group
    // to see all other group which is possible to choose user must change first combo (categories)
    $GLOBALS['phpgw']->template->set_var('optionname', $GLOBALS['phpgw']->accounts->id2name($ticket['group']));
    $GLOBALS['phpgw']->template->set_var('optionvalue', $ticket['group']);
    $GLOBALS['phpgw']->template->set_var('optionselected', ' selected');
    $GLOBALS['phpgw']->template->parse('options_group','options_select',true);
    */


   // assigned to
   /* $accounts = CreateObject('phpgwapi.accounts');
    $account_list = $accounts->get_list('accounts');
    $GLOBALS['phpgw']->template->set_var('optionname',lang('None'));
    $GLOBALS['phpgw']->template->set_var('optionvalue','0');
    $GLOBALS['phpgw']->template->set_var('optionselected','');
    $GLOBALS['phpgw']->template->parse('options_assignedto','options_select',true);
    while(list($key,$entry) = each($account_list))
    {
      $tag = '';
      if($entry['account_id'] == $ticket['assignedto'])
      {
        $tag = 'selected';
      }
      $GLOBALS['phpgw']->template->set_var('optionname', $entry['account_lid']);
      $GLOBALS['phpgw']->template->set_var('optionvalue', $entry['account_id']);
      $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
      $GLOBALS['phpgw']->template->parse('options_assignedto','options_select',True);
    }
    */

    $s=$categoryGroupUser->printSelector3($ticket['assignedto'], $GLOBALS['phpgw']->accounts->id2name($ticket['assignedto']),$disabled_for_standard_user);
    $GLOBALS['phpgw']->template->set_var('value_assignedto2',$s);
    /*
    $GLOBALS['phpgw']->template->set_var('optionname', $GLOBALS['phpgw']->accounts->id2name($ticket['assignedto']));
    $GLOBALS['phpgw']->template->set_var('optionvalue', $ticket['assignedto']);
    $GLOBALS['phpgw']->template->set_var('optionselected', ' selected');
    $GLOBALS['phpgw']->template->parse('options_assignedto','options_select',true);
    */

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




    // Choose the correct caller satisfaction to display
    /*csat_id2name
                case '0':        $value = 'None'; break;
                case '1':        $value = 'No Comment'; break;
                case '2':        $value = 'Not Satisfied'; break;
                case '3':        $value = 'Partitialy Satisfied'; break;
                case '5':        $value = 'Not Satisfied'; break;
                default:         $value = 'None'; break;
                */
    $caller_satisfaction_selected[$ticket['caller_satisfaction']] = ' selected';
    $caller_satisfaction_comment[0]  = lang(csat_id2name("0"));
    $caller_satisfaction_comment[1]  = lang(csat_id2name("1"));
    $caller_satisfaction_comment[2]  = lang(csat_id2name("2"));
    $caller_satisfaction_comment[3]  = lang(csat_id2name("3"));
    $caller_satisfaction_comment[5]  = lang(csat_id2name("5"));



    for($i=0; $i<=7; $i++)
    {
      if  ($i == 0 OR $i==1 OR $i==2 OR $i==3 OR $i==5)
      {
      $GLOBALS['phpgw']->template->set_var('optionname2', $caller_satisfaction_comment[$i]);
      $GLOBALS['phpgw']->template->set_var('optionvalue2', $i);
      $GLOBALS['phpgw']->template->set_var('optionselected2', $caller_satisfaction_selected[$i]);
      $GLOBALS['phpgw']->template->parse('options_caller_satisfaction','options_select2',true);
      }
    }
    // end add//

    $ticket_status[$ticket['status']] = ' selected';

    $s = '<option value="O"' . $ticket_status['O'] . '>' . lang('Open') . '</option>';
    $s .= '<option value="X"' . $ticket_status['X'] . '>' . lang('Closed') . '</option>';

    $GLOBALS['phpgw']->template->set_var('options_status',$s);
    $GLOBALS['phpgw']->template->set_var('lang_status',lang('Open / Closed'));

    $GLOBALS['phpgw']->template->set_var('lang_escalation',lang('Escalation'));

    $GLOBALS['phpgw']->template->set_var('value_escalation',$ticket['escalation'].' '.$ticket['escalation_time']);




    /**************************************************************\
    * Display additional notes                                     *
    \**************************************************************/

    // add by Josip
    // changed SORT for history - history_timestamp DESC
    //$history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('C'),'','',$ticket_id);

    $history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('C'),'history_timestamp','DESC',$ticket_id);

    $i = 0;
    while(is_array($history_array) && list(,$value) = each($history_array))
    {
      $GLOBALS['phpgw']->template->set_var('row_class',++$i & 1 ? 'row_off' : 'row_on');

      $GLOBALS['phpgw']->template->set_var('lang_date',lang('Date'));
      $GLOBALS['phpgw']->template->set_var('lang_user',lang('User'));

      $GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
      $GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

      $GLOBALS['phpgw']->template->set_var('value_note',nl2br(stripslashes($value['new_value'])));
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
    // add by Josip
    // changed SORT for history - history_timestamp DESC
    //$history_array = $GLOBALS['phpgw']->historylog->return_array(array('C'),array(),'','',$ticket_id);
    $history_array = $GLOBALS['phpgw']->historylog->return_array(array('C'),array(),'history_timestamp','DESC',$ticket_id);
    while(is_array($history_array) && list(,$value) = each($history_array))
    {
      $GLOBALS['phpgw']->template->set_var('row_class',++$i & 1 ? 'row_off' : 'row_on');

      $GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
      $GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

      switch($value['status'])
      {
         //added custom atributes by josip
         case 'K1': $type = lang('Caller Name'); break;
        case 'K2': $type = lang('Caller Telephone'); break;
        case 'K3': $type = lang('Caller Telephone ID'); break;
        case 'K4': $type = lang('Caller Address'); break;
        case 'K5': $type = lang('Caller Address 2'); break;
        case 'K6': $type = lang('Caller Email'); break;
        case 'K7': $type = lang('Caller Password'); break;
        case 'M7': $type = lang('Caller Ticket ID'); break;
        case 'K8': $type = lang('Caller Audio File'); break;
        case 'K9': $type = lang('Caller Satisfaction'); break;
        case 'K0': $type = lang('Caller Solution'); break;

        //end custom atributes


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
      elseif($value['status'] == 'K9' && $value['new_value'])
      {
        $GLOBALS['phpgw']->template->set_var('value_new_value',csat_id2name($value['new_value']));
        $GLOBALS['phpgw']->template->set_var('value_old_value',csat_id2name($value['old_value']));
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

    $GLOBALS['phpgw']->template->set_var('viewticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));
    $GLOBALS['phpgw']->template->set_var('ticket_id', $ticket_id);
    //added custom atributes by josip
    $GLOBALS['phpgw']->template->set_var('lang_caller_name',lang('Caller Name'));
    $GLOBALS['phpgw']->template->set_var('value_caller_name',$ticket['caller_name']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_telephone',lang('Caller Telephone'));
    $GLOBALS['phpgw']->template->set_var('value_caller_telephone',$ticket['caller_telephone']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_telephone_2',lang('Caller Telephone ID'));
    $GLOBALS['phpgw']->template->set_var('value_caller_telephone_2',$ticket['caller_telephone_2']);

    $GLOBALS['phpgw']->template->set_var('lang_caller_email',lang('Caller Email'));
    $GLOBALS['phpgw']->template->set_var('value_caller_email',$ticket['caller_email']);

    $GLOBALS['phpgw']->template->set_var('lang_caller_ticket_id',lang('Caller Ticket ID'));
    $GLOBALS['phpgw']->template->set_var('value_caller_ticket_id',$ticket['caller_ticket_id']);

    $GLOBALS['phpgw']->template->set_var('lang_caller_password',lang('Caller Password'));
    $GLOBALS['phpgw']->template->set_var('value_caller_password',$ticket['caller_password']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_address',lang('Caller Address'));
    $GLOBALS['phpgw']->template->set_var('value_caller_address',$ticket['caller_address']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_address_2',lang('Caller Address 2'));
    $GLOBALS['phpgw']->template->set_var('value_caller_address_2',$ticket['caller_address_2']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_audio_file',lang('Caller Audio File'));
    $GLOBALS['phpgw']->template->set_var('value_caller_audio_file',$ticket['caller_audio_file']);

    $GLOBALS['phpgw']->template->set_var('lang_caller_satisfaction', lang('Caller Satisfaction'));
    $GLOBALS['phpgw']->template->set_var('value_caller_satisfaction',lang(csat_id2name($ticket['caller_satisfaction'])));

    $GLOBALS['phpgw']->template->set_var('lang_caller_solution', lang('Solution for Caller'));
    $GLOBALS['phpgw']->template->set_var('value_caller_solution',$ticket['caller_solution']);

    //end custom atributes
    $GLOBALS['phpgw']->template->set_var('lang_assignedfrom', lang('Assigned from'));
    $GLOBALS['phpgw']->template->set_var('value_owner',$GLOBALS['phpgw']->accounts->id2name($ticket['owner']));

    $GLOBALS['phpgw']->template->set_var('lang_opendate', lang('Open Date'));
    $GLOBALS['phpgw']->template->set_var('value_opendate',$ticket['opened']);

    $GLOBALS['phpgw']->template->set_var('lang_priority', lang('Priority'));
    $GLOBALS['phpgw']->template->set_var('value_priority',$ticket['priority']);



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

    $GLOBALS['phpgw']->template->set_var('lang_billable_hours_rate',lang('Billable rate'));
    $GLOBALS['phpgw']->template->set_var('value_billable_hours_rate',$ticket['billable_rate']);

    $GLOBALS['phpgw']->template->set_var('lang_billable_hours_total',lang('Total billable'));
    $GLOBALS['phpgw']->template->set_var('value_billable_hours_total',sprintf('%01.2f',($ticket['billable_hours'] * $ticket['billable_rate'])));

    $GLOBALS['phpgw']->template->set_var('lang_assignedto',lang('Assigned to'));
    if($ticket['assignedto'])
    {
      $assignedto = $GLOBALS['phpgw']->accounts->id2name($ticket['assignedto']);
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
    }
    $GLOBALS['phpgw']->template->set_var('value_details', nl2br($ticket['details']));

    $GLOBALS['phpgw']->template->set_var('value_subject', $ticket['subject']);

    $GLOBALS['phpgw']->template->set_var('lang_additional_notes',lang('Additional notes'));
    $GLOBALS['phpgw']->template->set_var('lang_save', lang('Save'));
    $GLOBALS['phpgw']->template->set_var('lang_apply', lang('Apply'));
    $GLOBALS['phpgw']->template->set_var('lang_cancel', lang('Cancel'));

    $GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));
    $GLOBALS['phpgw']->template->set_var('value_category',$GLOBALS['phpgw']->categories->id2name($ticket[category]));

    $GLOBALS['phpgw']->template->set_var('options_select','');

    $GLOBALS['phpgw']->template->set_var('lang_update_state',lang('Update ticket state'));
    $GLOBALS['phpgw']->template->set_var('lang_keep_present_state',
      lang('Keep the present state [%1].',id2field('phpgw_tts_states','state_name','state_id',$ticket['state'])));

    $db = $GLOBALS['phpgw']->db;
    $db->query("select * from phpgw_tts_transitions where transition_source_state=".$ticket['state'],__LINE__,__FILE__);

    while($db->next_record())
    {
      $GLOBALS['phpgw']->template->set_var('update_state_value',$db->f('transition_target_state'));
      $GLOBALS['phpgw']->template->set_var('update_state_text',
        try_lang($db->f('transition_description'),
        id2field('phpgw_tts_states','state_name','state_id',$db->f('transition_target_state'))));
      $GLOBALS['phpgw']->template->parse('update_state_group', 'update_state_items', True);
    }

    //add by Josip
    //////////abc//$GLOBALS['phpgw']->template->set_var('initCatGroupUser','<script type="text/javascript" language="JavaScript">'.$categoryGroupUser->initialize2().'</script>');
    //end add

    $GLOBALS['phpgw']->template->pfp('out','form');
    $GLOBALS['phpgw']->common->phpgw_footer();

  }
  else // save or apply
  {
    $ticket = $_POST['ticket'];

    $ticket['category'] = $_POST['field_category'];
    $ticket['group'] = $_POST['field_group'];
    $ticket['assignedto'] = $_POST['field_assignedto'];

    // DB Content is fresher than http posted value.
    $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();
    //added custom atributes by josip
    $old_subject             = $GLOBALS['phpgw']->db->f('ticket_subject');
    $old_caller_name         = $GLOBALS['phpgw']->db->f('ticket_caller_name');
    $old_caller_telephone    = $GLOBALS['phpgw']->db->f('ticket_caller_telephone');
    $old_caller_telephone_2  = $GLOBALS['phpgw']->db->f('ticket_caller_telephone_2');
    $old_caller_email        = $GLOBALS['phpgw']->db->f('ticket_caller_email');
    $old_caller_ticket_id    = $GLOBALS['phpgw']->db->f('ticket_caller_ticket_id');
    $old_caller_password     = $GLOBALS['phpgw']->db->f('ticket_caller_password');
    $old_caller_address      = $GLOBALS['phpgw']->db->f('ticket_caller_address');
    $old_caller_address_2    = $GLOBALS['phpgw']->db->f('ticket_caller_address_2');
    $old_caller_audio_file   = $GLOBALS['phpgw']->db->f('ticket_caller_audio_file');
    $old_caller_satisfaction = $GLOBALS['phpgw']->db->f('ticket_caller_satisfaction');
    $old_caller_solution     = $GLOBALS['phpgw']->db->f('ticket_caller_solution');
    //end custom atributes
    $oldassigned = $GLOBALS['phpgw']->db->f('ticket_assignedto');
    $oldpriority = $GLOBALS['phpgw']->db->f('ticket_priority');
    $oldcategory = $GLOBALS['phpgw']->db->f('ticket_category');
    $old_status  = $GLOBALS['phpgw']->db->f('ticket_status');
    $old_billable_hours = $GLOBALS['phpgw']->db->f('ticket_billable_hours');
    $old_billable_rate = $GLOBALS['phpgw']->db->f('ticket_billable_rate');
    $old_group   = $GLOBALS['phpgw']->db->f('ticket_group');
    $old_state   = $GLOBALS['phpgw']->db->f('ticket_state');

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
    ** N - Petri Net State change
    */

    $no_error=True;
    if($old_status != $ticket['status'] && $ticket['status'] <> "")
    {
      //allow close to users with close privilege (in fact with add priviledge)
      //if(($GLOBALS['phpgw_info']['user']['account_id'] == $oldassigned) ||
      if(($can_add))
      {
        $fields_updated = True;
        $GLOBALS['phpgw']->historylog->add($ticket['status'],$ticket_id,$ticket['status'],$old_status);

        $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_status='"
          . $ticket['status'] . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      }
      else
      {
        $messages .= '<br>'.lang('You can only close a ticket if it is assigned to you.');
        $GLOBALS['phpgw']->session->appsession('messages','tts',$messages);
        $no_error=False;
      }
    }
    //add by Josip
    // if ticket state is changed
    $ticket_state_is_assigned = false;
    if($ticket['state'] && $old_state != $ticket['state'])
    {
      //and if new ticket state is rejected then
      if ($ticket['state'] == 6)
        {
           // check if user write some note, if note is not changed and ticket state is rejected then write error message
           if (!$ticket['note'])
             {
                  $ticket['state'] = $old_state;
                  $messages .= '<br>'.lang('You did not write any note! Ticket can not be rejected.');
                    $GLOBALS['phpgw']->session->appsession('messages','tts',$messages);
                    $no_error=False;
             }
        }
        //if ticket state is assigned then set boolean variable to true for sending email notification for assignment of the ticket
        if ($ticket['state'] == 2)
        {
             $ticket_state_is_assigned = true;
        }
    }
    //end add

    if($old_group != $ticket['group'] && $ticket['group'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_group='" . $ticket['group']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('G',$ticket_id,$ticket['group'],$old_group);
    }

    //added custom attributes by josip
    /*
    'K1' => 'Caller Name',
    'K2' => 'Caller Telephone',
    'K3' => 'Caller Telephone 2',
    'K4' => 'Caller Address',
    'K5' => 'Caller Address 2',
    'K6' => 'Caller Email',
    'K7' => 'Caller Password',
    'M7' => 'Caller Ticket ID',
    'K8' => 'Caller Audio File'
    'K9' => 'Caller Satisfaction'
    'K0' => 'Caller Solution'
    */

    if($old_subject != $ticket['subject'] && $ticket['subject'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_subject='" . $ticket['subject']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('S',$ticket_id,$ticket['subject'],$old_subject);
    }

    if($old_caller_name != $ticket['caller_name'] && $ticket['caller_name'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_name='" . $ticket['caller_name']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K1',$ticket_id,$ticket['caller_name'],$old_caller_name);
    }

    if($old_caller_telephone != $ticket['caller_telephone'] && $ticket['caller_telephone'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_telephone='" . $ticket['caller_telephone']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K2',$ticket_id,$ticket['caller_telephone'],$old_caller_telephone);
    }

    if($old_caller_telephone_2 != $ticket['caller_telephone_2'] && $ticket['caller_telephone_2'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_telephone_2='" . $ticket['caller_telephone_2']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K3',$ticket_id,$ticket['caller_telephone_2'],$old_caller_telephone_2);
    }

    if($old_caller_address != $ticket['caller_address'] && $ticket['caller_address'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_address='" . $ticket['caller_address']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K4',$ticket_id,$ticket['caller_address'],$old_caller_address);
    }

    if($old_caller_address_2 != $ticket['caller_address_2'] && $ticket['caller_address_2'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_address_2='" . $ticket['caller_address_2']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K5',$ticket_id,$ticket['caller_address_2'],$old_caller_address);
    }

    if($old_caller_email != $ticket['caller_email'] && $ticket['caller_email'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_email='" . $ticket['caller_email']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K6',$ticket_id,$ticket['caller_email'],$old_caller_email);
    }

    if($old_caller_ticket_id != $ticket['caller_ticket_id'] && $ticket['caller_ticket_id'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_ticket_id='" . $ticket['caller_ticket_id']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('M7',$ticket_id,$ticket['caller_ticket_id'],$old_caller_ticket_id);
    }

    if($old_caller_password != $ticket['caller_password'] && $ticket['caller_password'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_password='" . $ticket['caller_password']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K7',$ticket_id,$ticket['caller_password'],$old_caller_password);
    }


    if($old_caller_audio_file != $ticket['caller_audio_file'] && $ticket['caller_audio_file'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_audio_file='" . $ticket['caller_audio_file']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K8',$ticket_id,$ticket['caller_audio_file'],$old_caller_audio_file);
    }

    if($old_caller_satisfaction != $ticket['caller_satisfaction'] && $ticket['caller_satisfaction'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_satisfaction='" . $ticket['caller_satisfaction']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K9',$ticket_id,$ticket['caller_satisfaction'],$old_caller_satisfaction);
    }

    if($old_caller_solution != $ticket['caller_solution'] && $ticket['caller_solution'] <> "" )
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_caller_solution='" . $ticket['caller_solution']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('K0',$ticket_id,$ticket['caller_solution'],$old_caller_solution);
    }

    //end custom atributes

    if($oldassigned != $ticket['assignedto'] && $ticket['assignedto'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_assignedto='" . $ticket['assignedto']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('A',$ticket_id,$ticket['assignedto'],$oldassigned);
    }

    if($oldpriority != $ticket['priority'] && $ticket['priority'] <> "")
    {
      $fields_updated = True;
      $ticket['priority']=intval($ticket['priority']);
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_priority='" . $ticket['priority']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('P',$ticket_id,$ticket['priority'],$oldpriority);
    }

    if($oldcategory != $ticket['category'] && $ticket['category'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_category='" . $ticket[category]
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('T',$ticket_id,$ticket[category],$oldcategory);
    }

    if($old_billable_hours != $ticket['billable_hours'] && $ticket['billable_hours'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_billable_hours='" . $ticket['billable_hours']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('H',$ticket_id,$ticket['billable_hours'],$old_billable_hours);
    }

    if($old_billable_rate != $ticket['billable_rate'] && $ticket['billable_rate'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_billable_rate='" . $ticket['billable_rate']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('B',$ticket_id,$ticket['billable_rate'],$old_billable_rate);
    }

    if($ticket['state'] && $old_state != $ticket['state'] && $ticket['state'] <> "")
    {
      $fields_updated = True;
      $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets set ticket_state='" . $ticket['state']
        . "' where ticket_id='$ticket_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->historylog->add('N',$ticket_id,$ticket['state'],$old_state);
    }


    if($ticket['note'])
    {
      $fields_updated = True;
      $ticket['note'] = html_activate_urls($ticket['note']);

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

      //add by Josip - delete from tts_views so unread flag can be shown again if ticket is changed
      $GLOBALS['phpgw']->db->query("delete from phpgw_tts_views where view_id = '$ticket_id' "
        . "and view_account_id <> '" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);


      if($GLOBALS['phpgw']->config->config_data['mailnotification'])
      {
        //add by Josip - $ticket_state_is_assigned
        mail_ticket($ticket_id,$ticket_state_is_assigned);
      }
    }

    if ($_POST['save'] && $no_error)
    {
      $GLOBALS['phpgw']->redirect_link('/tts/index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort));
    }
    else  // apply
    {
      $GLOBALS['phpgw']->redirect_link('/tts/viewticket_details.php',array('ticket_id'=>$ticket_id,'filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort));
    }
  }
?>
