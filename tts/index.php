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

  /* $Id$ */

  /* Note to self:
  ** Self ... heres the query to use when limiting access to entrys within a group
  ** The acl class *might* handle this instead .... not sure
  ** select distinct group_ticket_id, phpgw_tts_groups.group_ticket_id, phpgw_tts_tickets.*
  ** from phpgw_tts_tickets, phpgw_tts_groups where ticket_id = group_ticket_id and group_id in (14,15);
  */

  /* ACL levels
  ** 1 - Read ticket within your group only
  ** 2 - Close ticket
  ** 4 - Allow to make changes to priority, billing hours, billing rate, category, and assigned to
  */

  $GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
  $GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
  include('../header.inc.php');

  $GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

  $GLOBALS['phpgw']->template->set_file('index','index.tpl');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_title', 'tts_title');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_links', 'tts_links');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_search', 'tts_search');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_list', 'tts_list');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_row', 'tts_row');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_col_ifviewall', 'tts_col_ifviewall');
  $GLOBALS['phpgw']->template->set_block('index', 'tts_head_ifviewall', 'tts_head_ifviewall');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');
  $GLOBALS['phpgw']->template->set_block('index','options_select');

  $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System'));
  $GLOBALS['phpgw']->template->set_var('tts_prefs_link', $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=tts'));
  $GLOBALS['phpgw']->template->set_var('lang_preferences', lang('Preferences'));
  $GLOBALS['phpgw']->template->set_var('lang_search', lang('search'));
  $GLOBALS['phpgw']->template->set_var('tts_head_status','');
  $GLOBALS['phpgw']->template->set_var('tts_notickets','');
  $GLOBALS['phpgw']->template->set_var('lang_category',lang('Category'));


  //add by Josip
  $can_add=False;
  $can_mon=False;
  $can_vip=False;
  $can_view_all=False;

  $def_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];
  
  
  // insert into acl table 'add', 'mon' or 'vip' similar to 'run' acl
  // let's assume that user can have run, add, mon, vip privileges to the whole tts appllication (this is only for a begining)
  // ACL is for discussion, and additional development must be made here
  //INSERT INTO phpgw_acl VALUES ('tts', 'add', 1, 1);
  //INSERT INTO phpgw_acl VALUES ('tts', 'vip', 1, 1);
  //INSERT INTO phpgw_acl VALUES ('tts', 'mon', 1, 1);
  
  // if user is admin, or VIP user, or HD_OPER user then ...
//ACL  if (($def_group == '16') || ($def_group == '6') || $GLOBALS['phpgw']->acl->check('add',1,'tts'))
//ACL  {
           $can_view_all=True;
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
//ACL  }

  // if user can add new ticket
  if ($can_add)
  {
    $GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link('/tts/newticket.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));
    $GLOBALS['phpgw']->template->set_var('tts_newticket_delimiter', "&nbsp;|&nbsp;");
    $GLOBALS['phpgw']->template->set_var('tts_newticket', lang('New ticket'));
  }
  ////

  // get group membership for filters
  $group_list = $GLOBALS['phpgw']->accounts->membership($GLOBALS['phpgw_info']['user']['account_id']);

  $group_ids == null;

  foreach($group_list as $rowg)
  {
        if ($group_ids == null)
        {
                    $group_ids = "'".$rowg['account_id']."'";
        }
        else
        {
                $group_ids = $group_ids.",'".$rowg['account_id']."'";
        }
  }
  ////


  $messages = rtrim($GLOBALS['phpgw']->session->appsession('messages','tts'),"\0");
  if($messages)
  {
    $GLOBALS['phpgw']->template->set_var('messages',$messages);
    $GLOBALS['phpgw']->session->appsession('messages','tts','');
  }



  // select what tickets to view
  if ($can_add)
  {
         $filter = get_var('filter',array('POST','GET'),'viewownedbyme');
        $order  = get_var('order',array('POST','GET'),'ticket_id');

  }
  else
  {
        if ($can_vip)
        {
           $filter = get_var('filter',array('POST','GET'),'view');
        }
        else
        {
           $filter = get_var('filter',array('POST','GET'),'viewmy');
        }

        $order  = get_var('order',array('POST','GET'),'ticket_priority');
  }


  $f_status  = get_var('f_status',array('POST','GET'),'O');
  $start  = (int) get_var('start',array('POST','GET'));
  $sort   = get_var('sort',array('POST','GET'),'DESC');
  $searchfilter = reg_var('searchfilter','POST','any');

  // Append the filter to the search URL, so that the mode carries forward on a search
  $GLOBALS['phpgw']->template->set_var('tts_search_link',$GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));

  $caller_telephone_2 = reg_var('telephone2','GET');

  //if agent recive call from specified telephone number
  if($caller_telephone_2<>"")
  {
    $filtermethod ="where ticket_caller_telephone_2 ='".$caller_telephone_2."'";
  }
else
{

  if ($filter == 'viewmy')
  {
    $filtermethod = "WHERE ticket_assignedto='".$GLOBALS['phpgw_info']['user']['account_id']."'";
  }

  if ($filter == 'viewownedbyme')
  {
    $filtermethod = "WHERE ticket_owner='".$GLOBALS['phpgw_info']['user']['account_id']."'";
  }

  if ($filter == 'view')
  {
    $filtermethod = "";
  }


  if ($f_status == "O" || $f_status == "I")
  {
    if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'])
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','<META HTTP-EQUIV="Refresh" CONTENT="'.$GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'].'; URL='.$GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)).'">');
    }
    else
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','');
    }
  }


  //  if ($filter <> 'viewmyopen' && $filter <> 'viewall' && $filter <> 'viewopen' && substr($filter,5) <> 'group' && $filter <>'' )
  // if filter is g + group id then search for open tickets by group
  if (substr($filter,0,1) == "g")
  {
    $group_filter=(int) substr($filter,1);

      if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_group='".$group_filter."'";
        }
        else
        {
           $filtermethod = $filtermethod." AND ticket_group='".$group_filter."'";
        }
  }

  // if filter is s + state id then search for open tickets by group
  if (substr($filter,0,1) == "u")
  {
    $status_filter=(int) substr($filter,1);

       if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_state=$status_filter";
        }
        else
        {
           $filtermethod = $filtermethod." AND ticket_state=$status_filter";
        }
  }

   // if filter is c + category id then search for open tickets by category
  if (substr($filter,0,1) == "c")
  {
    $category_filter=(int) substr($filter,1);

       if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_category='".$category_filter."'";
        }
        else
        {
           $filtermethod = $filtermethod." AND ticket_category='".$category_filter."'";
        }
  }
  //$group_in = $GLOBALS['phpgw_info']['accounts']['user']['account_id']."'";
//  $def_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];


  // set for a possible search filter, outside of the all/open only "state" filter above
  // but filter_status is used to additionaly specify filtering by ticket status
  if ($searchfilter)
  {
    $s_quoted = $GLOBALS['phpgw']->db->quote('%'.$searchfilter.'%');
    //$filtermethod = "WHERE (ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted)";
    if ($filtermethod == "")
    {
        $filtermethod = "WHERE (ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted OR ticket_caller_name LIKE $s_quoted OR ticket_caller_email LIKE $s_quoted OR ticket_caller_address LIKE $s_quoted OR ticket_caller_address_2 LIKE $s_quoted OR ticket_caller_telephone LIKE $s_quoted OR ticket_caller_telephone_2 LIKE $s_quoted OR ticket_caller_ticket_id LIKE $s_quoted)";
    }
    else
    {
        $filtermethod = $filtermethod." AND  (ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted OR ticket_caller_name LIKE $s_quoted OR ticket_caller_email LIKE $s_quoted OR ticket_caller_address LIKE $s_quoted OR ticket_caller_address_2 LIKE $s_quoted OR ticket_caller_telephone LIKE $s_quoted OR ticket_caller_telephone_2 LIKE $s_quoted OR ticket_caller_ticket_id LIKE $s_quoted)";
    }


  }

  $GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));
}

  // Added ACL check by Josip
  // if can NOT view all then use restricted search
  if (!$can_view_all)
  {
     if ($group_ids <> null)
     {
        if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_group IN ($group_ids)";
        }
        else
        {
           $filtermethod = "$filtermethod AND ticket_group IN ($group_ids)";
        }
     }
  }

  $search_status = "";

  if ($f_status == "O")
  {
           $search_status = "ticket_status='O'";
  }
  elseif ($f_status == "I")
  {
        $search_status = "ticket_status='I'";
  }
  elseif ($f_status == "X")
  {
        $search_status = "ticket_status='X'";
  }

  if ($search_status <> "")
  {
        if ($filtermethod == "")
        {
           $filtermethod = "WHERE $search_status";
        }
        else
        {
           $filtermethod = "$filtermethod AND $search_status";
        }
  }

  if (!preg_match('/^[a-z_]+$/i',$order) || !preg_match('/^(asc|desc)$/i',$sort))
  {
    // if user can add ticket then default sort is by ticket id, else sort is by priority
    if ($can_add)
    {
      $sortmethod = 'ORDER BY ticket_id DESC';
    }
    else
    {
      $sortmethod = 'ORDER BY ticket_priority DESC';
    }
  }
  else
  {
    $sortmethod = "ORDER BY $order $sort";
  }
  $db = clone($GLOBALS['phpgw']->db);
  $db2 = clone($GLOBALS['phpgw']->db);
  $db->query($sql="SELECT count(*) FROM phpgw_tts_tickets $filtermethod",__LINE__,__FILE__);
  $total = $db->next_record() ? $db->f(0) : 0;

  $db->limit_query("SELECT * FROM phpgw_tts_tickets $filtermethod $sortmethod",$start,__LINE__,__FILE__);


  $GLOBALS['phpgw']->template->set_var('tts_numfound',$GLOBALS['phpgw']->nextmatchs->show_hits($total,$start));
  $GLOBALS['phpgw']->template->set_var('left',$GLOBALS['phpgw']->nextmatchs->left('/tts/index.php',$start,$total,'f_status='.$f_status));
//                               array('filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));
  $GLOBALS['phpgw']->template->set_var('right',$GLOBALS['phpgw']->nextmatchs->right('/tts/index.php',$start,$total,'f_status='.$f_status));

  //add filter status into variable filter


  if ($can_add)
  {
      $tag = '';
      $GLOBALS['phpgw']->template->set_var('optionname', lang('View tickets created by me'));
      $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewownedbyme');
      if ($filter == 'viewownedbyme' || substr($filter,1) == 'viewownedbyme')
      {
      $tag = 'selected';
      }
      $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
      $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);
  }

  $tag = '';
  $GLOBALS['phpgw']->template->set_var('optionname', lang('View all tickets'));
  $GLOBALS['phpgw']->template->set_var('optionvalue', 'view');
  //if filter is search then it is appropriate to options set to View All Tickets
  if ($filter == 'view' || substr($filter,1) == 'view' || $filter == 'search')
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  $tag = '';
  $GLOBALS['phpgw']->template->set_var('optionname', lang('View my tickets'));
  $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewmy');
  if ($filter == 'viewmy' || substr($filter,1) == 'viewmy' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  //Added custom filter - view tickets for group a, ...
  foreach($group_list as $rowg)
  {
     $tag = '';
     $GLOBALS['phpgw']->template->set_var('optionname', lang('View tickets for group').' '.$rowg['account_name']);
     $GLOBALS['phpgw']->template->set_var('optionvalue', "g".$rowg['account_id']);

     if ($filter == "g".$rowg['account_id'] )
     {
        $tag = 'selected';
     }
     $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
     $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  }

  if($can_view_all || $can_mon)
  {
      $db_s = $GLOBALS['phpgw']->db;
      $db_s->query("select * from phpgw_tts_states where state_id > 1",__LINE__,__FILE__);

      while($db_s->next_record())
      {


         $tag = '';
         $GLOBALS['phpgw']->template->set_var('optionname', lang('View tickets with state').' '.try_lang($db_s->f('state_name')));
         $GLOBALS['phpgw']->template->set_var('optionvalue', "u".$db_s->f('state_id'));

         if ($filter == "u".$db_s->f('state_id'))
         {
            $tag = 'selected';
         }
         $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
         $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);
      }
  }

  if($can_view_all)
  {
      $db_c = $GLOBALS['phpgw']->db;
      $db_c->query("SELECT distinct a.cat_id, b.cat_name FROM phpgw_tts_categories_groups a, phpgw_categories b WHERE a.cat_id = b.cat_id AND b.cat_appname = 'tts' ORDER BY b.cat_name",__LINE__,__FILE__);

      while($db_c->next_record())
      {


         $tag = '';
         $GLOBALS['phpgw']->template->set_var('optionname', lang('View tickets for category').' '.try_lang($db_c->f('cat_name')));
         $GLOBALS['phpgw']->template->set_var('optionvalue', "c".$db_c->f('cat_id'));

         if ($filter == "c".$db_c->f('cat_id'))
         {
            $tag = 'selected';
         }
         $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
         $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);


      }
  }
  // end custom filter

  $ticket_status[$f_status] = ' selected';

  $s = '<option value="O"' . $ticket_status['O'] . '>' . lang('Status'). ' ' . lang('Open') . '</option>';
  $s .= '<option value="I"' . $ticket_status['I'] . '>' . lang('Status'). ' ' . lang('Initiative') . '</option>';
  $s .= '<option value="X"' . $ticket_status['X'] . '>' . lang('Status'). ' ' . lang('Closed') . '</option>';
  $s .= '<option value="A"' . $ticket_status['A'] . '>' . lang('Any status') . '</option>';

  $GLOBALS['phpgw']->template->set_var('options_f_status',$s);
  //$GLOBALS['phpgw']->template->set_var('lang_status',lang('Open / Closed'));


  $GLOBALS['phpgw']->template->set_var('tts_ticketstotal', lang('Tickets total %1',$numtotal));
  $GLOBALS['phpgw']->template->set_var('tts_ticketsopen', lang('Tickets open %1',$numopen));

  // fill header
  $GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('tts_head_ticket', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_id',$order,'/tts/index.php',lang('Ticket').' #'));
  $GLOBALS['phpgw']->template->set_var('tts_head_prio', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_priority',$order,'/tts/index.php',lang('Priority')));
  $GLOBALS['phpgw']->template->set_var('tts_head_group',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_group',$order,'/tts/index.php',lang('Group')));
  $GLOBALS['phpgw']->template->set_var('tts_head_category',$GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_category',$order,'/tts/index.php',lang('Category')));
  $GLOBALS['phpgw']->template->set_var('tts_head_assignedto', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_assignedto',$order,'/tts/index.php',lang('Assigned to')));
  $GLOBALS['phpgw']->template->set_var('tts_head_openedby', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_owner',$order,'/tts/index.php',lang('Opened by')));

  // I am not sure how the sorting will work for this, if at all. (jengo)
  $GLOBALS['phpgw']->template->set_var('tts_head_dateopened',lang('Date opened'));
//  $GLOBALS['phpgw']->template->set_var('tts_head_dateopened', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'',$order,'/tts/index.php',lang('Date opened')));
//  if ($filter != 'viewopen')
  if ($f_status != 'O')
  {
    $GLOBALS['phpgw']->template->set_var('tts_head_dateclosed', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_status',$order,'/tts/index.php',lang('Status/Date closed')));
    $GLOBALS['phpgw']->template->parse('tts_head_status','tts_head_ifviewall',false);
  }
  $GLOBALS['phpgw']->template->set_var('tts_head_subject', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_subject',$order,'/tts/index.php',lang('Subject')));
  $GLOBALS['phpgw']->template->set_var('tts_head_state', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'ticket_state',$order,'/tts/index.php',lang('State')));

  if ($db->num_rows() == 0)
  {
    $GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No tickets found').'</center>');
  }
  else
  {
    while ($db->next_record())
    {
      $GLOBALS['phpgw']->template->set_var('tts_col_status','');
      $priority = $db->f('ticket_priority');
      $GLOBALS['phpgw']->template->set_var('tts_t_prio',$priority);

      switch ($priority)
      {
        case 1:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg01']; break;
        case 2:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg02']; break;
        case 3:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg03']; break;
        case 4:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg04']; break;
        case 5:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg05']; break;
        case 6:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg06']; break;
        case 7:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg07']; break;
        case 8:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg08']; break;
        case 9:  $tr_color = $GLOBALS['phpgw_info']['theme']['bg09']; break;
        case 10: $tr_color = $GLOBALS['phpgw_info']['theme']['bg10']; break;
        default: $tr_color = $GLOBALS['phpgw_info']['theme']['bg_color'];
      }

      if ($filter!="viewopen" && $db->f('t_timestamp_closed'))
      {
        $tr_color = $GLOBALS['phpgw_info']['theme']['th_bg']; /*"#CCCCCC";*/
      }

      $db2->query("select count(*) from phpgw_tts_views where view_id='" . $db->f('ticket_id')
        . "' and view_account_id='" . $GLOBALS['phpgw_info']['user']['account_id'] . "'",__LINE__,__FILE__);
      $db2->next_record();

      if ($db2->f(0))
      {
        $ticket_read = True;
      }
      else
      {
        $ticket_read = False;
      }

      $GLOBALS['phpgw']->template->set_var('tts_row_color', $tr_color );
      $GLOBALS['phpgw']->template->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/viewticket_details.php',array('ticket_id'=>$db->f('ticket_id'),'filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)));

      $view_link = '<a href="' . $GLOBALS['phpgw']->link('/tts/viewticket_details.php',array('ticket_id'=>$db->f('ticket_id'),'filter'=>$filter,'f_status'=>$f_status,'order'=>$order,'start'=>$start,'sort'=>$sort)). '">';
      $GLOBALS['phpgw']->template->set_var('row_ticket_id',$view_link . $db->f('ticket_id') . '</a>');

      if (! $ticket_read)
      {
        $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/updated.gif">');
      }
      else
      {
        $GLOBALS['phpgw']->template->set_var('row_status','&nbsp;');
      }

      $cat_name   = $GLOBALS['phpgw']->categories->id2name($db->f('ticket_category'));
      $GLOBALS['phpgw']->template->set_var('row_category',$cat_name);

      $group_name = $GLOBALS['phpgw']->accounts->id2name($db->f('ticket_group'));
      $group_name = ($group_name ? $group_name : '--');
      $GLOBALS['phpgw']->template->set_var('row_group',$group_name);

      $GLOBALS['phpgw']->template->set_var('tts_t_assignedto', $db->f('ticket_assignedto')?$GLOBALS['phpgw']->accounts->id2name($db->f('ticket_assignedto')):lang('None'));
      $GLOBALS['phpgw']->template->set_var('tts_t_user',$GLOBALS['phpgw']->accounts->id2name($db->f('ticket_owner')));

      $history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('O'),'history_timestamp','ASC',$db->f('ticket_id'));
      $GLOBALS['phpgw']->template->set_var('tts_t_timestampopened',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));

      if ($db->f('ticket_status') == 'X')
      {
        $history_values = $GLOBALS['phpgw']->historylog->return_array(array(),array('X'),'history_timestamp','ASC',$db->f('ticket_id'));
        $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
        $GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
      }
//      elseif ($filter != 'viewopen')
      elseif ($db->f('ticket_status') == 'I')
      {
        $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',lang('Initiative'));
        $GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
      }

      elseif ($f_status != 'O')
      {
//        if ($db->f('ticket_assignedto') != -1)
//        {
//          $assigned_to = lang('Not assigned');
//        }
//        else
//        {
//          $assigned_to = $GLOBALS['phpgw']->accounts->id2name($db->f('ticket_assignedto'));
//        }
//        $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$assigned_to);

   $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',lang('Open'));
        $GLOBALS['phpgw']->template->parse('tts_col_status','tts_col_ifviewall',False);
      }
      // cope with old, wrongly saved entries, stripslashes would remove single backslashes too
      $subject = str_replace(array('\\\'','\\"','\\\\'),array("'",'"','\\'),$db->f('ticket_subject'));
      $GLOBALS['phpgw']->template->set_var('tts_t_subject', $view_link.$subject.'</a>');
      $GLOBALS['phpgw']->template->set_var('tts_t_state',
        id2field('phpgw_tts_states','state_name','state_id',$db->f('ticket_state')));

      $GLOBALS['phpgw']->template->parse('rows','tts_row',True);
    }
  }

  // this is a workaround to clear the subblocks autogenerated vars
  $GLOBALS['phpgw']->template->set_var('tts_row','');
  $GLOBALS['phpgw']->template->set_var('tts_col_ifviewall','');
  $GLOBALS['phpgw']->template->set_var('tts_head_ifviewall','');
  $GLOBALS['phpgw']->template->set_var('tts_ticket_id_read','');
  $GLOBALS['phpgw']->template->set_var('tts_ticket_id_unread','');
  $GLOBALS['phpgw']->template->set_var('options_select','');

  $GLOBALS['phpgw']->template->pfp('out','index');

  $GLOBALS['phpgw']->common->phpgw_footer();
  ?>
