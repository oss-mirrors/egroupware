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
//  $start  = reg_var('start','GET','numeric',15);
//  $start  = (int) get_var('start',array('POST','GET'));
  $start  = reg_var('start','GET');
  $f_status = reg_var('f_status','GET');
  $sort   = reg_var('sort','GET');
  $order  = reg_var('order','GET');
  $searchfilter = reg_var('searchfilter','POST');

  if($_POST['cancel'])
  {
    $GLOBALS['phpgw']->redirect_link('/tts/wnt_index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort));
  }
   //add by Josip
  $can_add=False;
  $can_close=False;
  $can_mon=False;
  $can_vip=False;
  $can_view_all=False;

  $def_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];
  // if user is admin, or VIP user, or HD_OPER user then ...
  if (($def_group == '16') || ($def_group == '6') || $GLOBALS['phpgw']->acl->check('add',1,'tts'))
  {
           $can_view_all=True;
           $can_close=True;
  }
  if ($GLOBALS['phpgw']->acl->check('mon',1,'tts'))
  {
           $can_mon=True;
           $can_view_all=True;
  }
  if ($GLOBALS['phpgw']->acl->check('vip',1,'tts'))
  {
           $can_vip=True;
           $can_view_all=True;
  }
  if ($GLOBALS['phpgw']->acl->check('add',1,'tts'))
  {
           $can_add=True;
           $can_close=True;
  }
  //


  $ticket_id = intval(get_var('ticket_id',array('POST','GET')));

  $GLOBALS['phpgw']->config->read_repository();



  if(!$_POST['save'] && !$_POST['close'])
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


    $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'] . ' - ' . lang('WWW request');
    $GLOBALS['phpgw']->common->phpgw_header();
    echo parse_navbar();

    // Have they viewed this ticket before ?
    $GLOBALS['phpgw']->db->query("select count(*) from phpgw_tts_views_wnt where view_id='$ticket_id' "
      . "and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();

    if(!$GLOBALS['phpgw']->db->f(0))
    {
      $GLOBALS['phpgw']->db->query("insert into phpgw_tts_views_wnt values ('$ticket_id','"
        . $GLOBALS['phpgw_info']['user']['account_id'] . "','" . time() . "')",__LINE__,__FILE__);
    }

    // select the ticket that you selected
    $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets_wnt where ticket_id='$ticket_id'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();

    $ticket['caller_name']      = $GLOBALS['phpgw']->db->f('ticket_caller_name');
    $ticket['caller_telephone'] = $GLOBALS['phpgw']->db->f('ticket_caller_telephone');
    $ticket['caller_email']     = $GLOBALS['phpgw']->db->f('ticket_caller_email');
    $ticket['caller_address']   = $GLOBALS['phpgw']->db->f('ticket_caller_address');
    $ticket['caller_address_2']   = $GLOBALS['phpgw']->db->f('ticket_caller_address_2');

    $ticket['details']        = $GLOBALS['phpgw']->db->f('ticket_details');
    $ticket['subject']        = $GLOBALS['phpgw']->db->f('ticket_subject');
    $ticket['status']         = $GLOBALS['phpgw']->db->f('ticket_status');
    $ticket['opened']         = $GLOBALS['phpgw']->db->f('creation_date');
    $ticket['closed']         = $GLOBALS['phpgw']->db->f('finish_date');


    $GLOBALS['phpgw']->template->set_file('viewticket','wnt_viewticket_details.tpl');
    $GLOBALS['phpgw']->template->set_block('viewticket','form');

    if ($can_add)
    {
        $GLOBALS['phpgw']->template->set_var('duplicate_ticket', '<tr class="th">'.
            '<td colspan="4" align="right"><a href="'.
            $GLOBALS['phpgw']->link('/tts/newticket.php',array('ticketidwnt'=>$ticket_id)).
            '"><b>'.lang('Duplicate ticket').'</b></a></td>'.
        '</tr>');
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



    if ($ticket['status'] == 'X')
    {
     	$GLOBALS['phpgw']->template->set_var('value_status',lang('Closed'));
    }
    else
    {
        $GLOBALS['phpgw']->template->set_var('value_status',lang('Open'));
    }

    $GLOBALS['phpgw']->template->set_var('lang_status',lang('Status'));



    /**************************************************************\
    * Display additional notes                                     *
    \**************************************************************/



    $GLOBALS['phpgw']->template->set_var('viewticketdetails_link', $GLOBALS['phpgw']->link('/tts/wnt_viewticket_details.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));
    $GLOBALS['phpgw']->template->set_var('ticket_id', $ticket_id);
    //added custom atributes by josip
    $GLOBALS['phpgw']->template->set_var('lang_caller_name',lang('Caller Name'));
    $GLOBALS['phpgw']->template->set_var('value_caller_name',$ticket['caller_name']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_telephone',lang('Caller Telephone'));
    $GLOBALS['phpgw']->template->set_var('value_caller_telephone',$ticket['caller_telephone']);

    $GLOBALS['phpgw']->template->set_var('lang_caller_email',lang('Caller Email'));
    $GLOBALS['phpgw']->template->set_var('value_caller_email',$ticket['caller_email']);

    $GLOBALS['phpgw']->template->set_var('lang_caller_address',lang('Caller Address'));
    $GLOBALS['phpgw']->template->set_var('value_caller_address',$ticket['caller_address']);
    $GLOBALS['phpgw']->template->set_var('lang_caller_address_2',lang('Caller Address 2'));
    $GLOBALS['phpgw']->template->set_var('value_caller_address_2',$ticket['caller_address_2']);

    $GLOBALS['phpgw']->template->set_var('lang_opendate', lang('Open Date'));
    $GLOBALS['phpgw']->template->set_var('value_opendate',$ticket['opened']);

    $GLOBALS['phpgw']->template->set_var('lang_finishdate', lang('Date closed'));
    $GLOBALS['phpgw']->template->set_var('value_finishdate',$ticket['closed']);


    $GLOBALS['phpgw']->template->set_var('lang_subject', lang('Subject'));

    $GLOBALS['phpgw']->template->set_var('lang_details', lang('Details'));

    // cope with old, wrongly saved entries, stripslashes would remove single backslashes too
    foreach(array('subject','details') as $name)
    {
      $ticket[$name] = str_replace(array('\\\'','\\"','\\\\'),array("'",'"','\\'),$ticket[$name]);
    }
    $GLOBALS['phpgw']->template->set_var('value_details', nl2br($ticket['details']));

    $GLOBALS['phpgw']->template->set_var('value_subject', $ticket['subject']);

    $GLOBALS['phpgw']->template->set_var('lang_close_with_new', lang('Close with duplicate'));
    $GLOBALS['phpgw']->template->set_var('lang_close', lang('Close'));
    $GLOBALS['phpgw']->template->set_var('lang_cancel', lang('Cancel'));


    $GLOBALS['phpgw']->template->pfp('out','form');
    $GLOBALS['phpgw']->common->phpgw_footer();

  }
  else // save or apply
  {
    $ticket = $_POST['ticket'];

    // DB Content is fresher than http posted value.
    $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets_wnt where ticket_id='$ticket_id'",__LINE__,__FILE__);
    $GLOBALS['phpgw']->db->next_record();
    //added custom atributes by josip

    /*
    $old_subject             = $GLOBALS['phpgw']->db->f('ticket_subject');
    $old_caller_name  	     = $GLOBALS['phpgw']->db->f('ticket_caller_name');
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
    */

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
    if( $ticket['status'] <> "X")
    {
      //allow close to users with close privilege (in fact with add priviledge)
      //if(($GLOBALS['phpgw_info']['user']['account_id'] == $oldassigned) ||

      if(($can_add))
      {
        $fields_updated = True;

        $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets_wnt set ticket_status='X' where ticket_id='$ticket_id'",__LINE__,__FILE__);

        $finish_date = date("d.m.Y G:i:s");

        $GLOBALS['phpgw']->db->query("update phpgw_tts_tickets_wnt set finish_date='".$finish_date."' where ticket_id='$ticket_id'",__LINE__,__FILE__);

        // Only do our commit once
        $GLOBALS['phpgw']->db->transaction_commit();
      }

    }


    if($fields_updated)
    {
      $GLOBALS['phpgw']->session->appsession('messages','tts',lang('Ticket has been updated').'<br/>'.$messages);

      //add by Josip - delete from tts_views so unread flag can be shown again if ticket is changed
      //$GLOBALS['phpgw']->db->query("delete from phpgw_tts_views_wnt where view_id = '$ticket_id' "
      //  . "and view_account_id <> '" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);

    }

    if ($_POST['save'] && $no_error)
    {

//      $GLOBALS['phpgw']->redirect_link('/tts/wnt_index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort));
      $GLOBALS['phpgw']->redirect_link('/tts/newticket.php',array('ticketidwnt'=>$ticket_id));

    }
    else
    {
      $GLOBALS['phpgw']->redirect_link('/tts/wnt_index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort));
    }
  }
?>
