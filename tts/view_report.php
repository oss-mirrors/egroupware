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



  $GLOBALS['phpgw_info']['flags']['currentapp'] = 'tts';
  $GLOBALS['phpgw_info']['flags']['enable_contacts_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
  $GLOBALS['phpgw_info']['flags']['enable_nextmatchs_class'] = True;
  $GLOBALS['phpgw_info']['flags']['noheader']            = True;

  include('../header.inc.php');

  $GLOBALS['phpgw']->template->set_file('view_report','view_report.tpl');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_title', 'tts_title');




//  $GLOBALS['phpgw']->template->set_block('index', 'tts_links', 'tts_links');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_search', 'tts_search');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_list', 'tts_list');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_row', 'tts_row');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_row_total', 'tts_row_total');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_col_ifviewall_1', 'tts_col_ifviewall_1');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_head_ifviewall_1', 'tts_head_ifviewall_1');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_col_ifviewall_2', 'tts_col_ifviewall_2');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_head_ifviewall_2', 'tts_head_ifviewall_2');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_col_viewallgroup', 'tts_col_viewallgroup');
  $GLOBALS['phpgw']->template->set_block('view_report', 'tts_head_viewallgroup', 'tts_head_viewallgroup');

//  $GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_read', 'tts_ticket_id_read');
//  $GLOBALS['phpgw']->template->set_block('index', 'tts_ticket_id_unread', 'tts_ticket_id_unread');
  $GLOBALS['phpgw']->template->set_block('view_report','options_select');


//  $GLOBALS['phpgw_info']['apps']['tts']['title'].' - '.lang("List of available groups per categories.")
//  $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('categories'));
//  $GLOBALS['phpgw']->template->set_var('tts_prefs_link', $GLOBALS['phpgw']->link('/preferences/preferences.php','appname=tts'));
//  $GLOBALS['phpgw']->template->set_var('lang_preferences', lang('Preferences'));
  $GLOBALS['phpgw']->template->set_var('lang_search', lang('search'));

  $GLOBALS['phpgw']->template->set_var('tts_notickets','');


  $sb = CreateObject('phpgwapi.sbox2');
  $jscal = CreateObject('phpgwapi.jscalendar');    // before phpgw_header() !!!


  //add by Josip
  $can_add=False;
  $can_mon=False;
  $can_vip=False;
  $can_view_all=False;

  //if report is statistic then other query is executed and other columns are shown
  $statistic_report=False;
  //if report is group then other query is executed and other columns are shown
  $group_report=False;
  //if report is customer satisfaction level statistic report then other query is executed and other columns are shown
  $customer_satisfaction_report=False;
  //if total number of rows is > 1 then row with totals must be shown
  $show_total_row = False;


  $def_group = $GLOBALS['phpgw_info']['user']['account_primary_group'];
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
/*  if ($can_add)
  {
    $GLOBALS['phpgw']->template->set_var('tts_newticket_link', $GLOBALS['phpgw']->link('/tts/newticket.php',array('filter'=>$filter,'order'=>$order,'sort'=>$sort)));
    $GLOBALS['phpgw']->template->set_var('tts_newticket_delimiter', "&nbsp;|&nbsp;");
    $GLOBALS['phpgw']->template->set_var('tts_newticket', lang('New ticket'));
  }
  */
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

  // get all groups
  $all_groups_ids = null;
  $all_group_where = null;

  if ($can_view_all)
  {
           $all_group_where = '';
  }
  else
  {
           $all_group_where = 'AND account_id IN ($group_ids)';
  }

  $db_gr = $GLOBALS['phpgw']->db;
  $db_gr->query("SELECT distinct b.account_id, b.account_lid FROM phpgw_tts_categories_groups a, phpgw_accounts b WHERE a.account_id = b.account_id $all_group_where ORDER BY b.account_lid",__LINE__,__FILE__);


  while($db_gr->next_record())
  {
      $all_group_list[]=Array('account_id' => $db_gr->f('account_id'), 'account_name' => $db_gr->f('account_lid'));

        if ($all_groups_ids == null)
        {
                $all_groups_ids = "'".$db_gr->f('account_id')."'";
        }
        else
        {
                $all_groups_ids = $all_groups_ids.",'".$db_gr->f('account_id')."'";
        }
  }
  ////


  // get all categories
  $all_categories_ids = null;
  $all_cat_where = null;

  if ($can_view_all)
  {
       $all_cat_where = '';
  }
  else
  {
       $all_cat_where = 'AND account_id IN ($group_ids)';
  }

  $db_ca = $GLOBALS['phpgw']->db;
  $db_ca->query("SELECT distinct a.cat_id FROM phpgw_tts_categories_groups a, phpgw_categories b WHERE a.cat_id = b.cat_id AND b.cat_appname = 'tts' $all_cat_where ORDER BY b.cat_name",__LINE__,__FILE__);


  while($db_ca->next_record())
  {
        if ($all_categories_ids == null)
        {
                $all_categories_ids = "'".$db_ca->f('cat_id')."'";
        }
        else
        {
                $all_categories_ids = $all_categories_ids.",'".$db_ca->f('cat_id')."'";
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
  $filter = get_var('filter',array('POST','GET'),'viewhighcritical');

  // type of the report - different columns are shown
  // 0 = None (if view highcritical or medium critical) (only number of tickets are shown)
  // 1 = status, priority       (num of tickets,count by --> open,initiative,closed,low priority,medium priority, high priority)
  // 2 = customer satisfaction  (num of tickets,count by --> different level of customer satisfaction)
  $f_type  = get_var('f_type',array('POST','GET'),'1');
  $start  = (int) get_var('start',array('POST','GET'));
  $sort   = get_var('sort',array('POST','GET'),'DESC');
  $order  = get_var('order',array('POST','GET'),'cat_name');
  $f_startdate = get_var('f_startdate',array('POST','GET'),'0');
  $f_enddate = get_var('f_enddate',array('POST','GET'),'0');
  //$searchfilter = reg_var('searchfilter','POST','any');
  $from = "";

  // Append the filter to the search URL, so that the mode carries forward on a search
  $GLOBALS['phpgw']->template->set_var('tts_search_link',$GLOBALS['phpgw']->link('/tts/view_report.php',array('filter'=>$filter,'f_type'=>$f_type,'f_startdate'=>$f_startdate,'f_enddate'=>$f_enddate,'order'=>$order,'sort'=>$sort)));

  $GLOBALS['phpgw']->common->phpgw_header();


  if ($filter == 'viewmediumcritical')
  {
     $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Medium Critical Categories'));
     $filtermethod = "WHERE (ticket_priority IN ('5','6','7') OR ticket_escalation = 1) AND ticket_category = c.cat_id AND ticket_status = 'O'";
     $statistic_report=False;
     //reset type of the report to None because only number of tickets is shown
     $f_type = '0';
  }
  elseif ($filter == 'viewhighcritical')
  {
     $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Highly Critical Categories'));
     $filtermethod = "WHERE (ticket_priority > 7 OR ticket_escalation > 1) AND ticket_category = c.cat_id AND ticket_status = 'O'";
     $statistic_report=False;
     //reset type of the report to None because only number of tickets is shown
     $f_type = '0';
  }
  else
  {
     $statistic_report=True;
     //default report type for statistic report is 1 --> status, priority
     if ($f_type == '0')
     {
        $f_type = '1';
     }
  }

  if ($filter == 'viewallcategories')
  {
    $statistic_report = True;
    $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Statistic for all Categories'));
    $filtermethod = "WHERE ticket_category = c.cat_id";
  }

  if ($filter == 'viewallgroups')
  {
    $statistic_report = True;
    $group_report = True;
    $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Statistic for all Groups'));
    $filtermethod = "WHERE ticket_category = c.cat_id AND ticket_group = a.account_id";
    $from = $from.', phpgw_accounts a ';
    $groupby = $groupby.',ticket_group ';
  }

  // if filter is g + group id then search for open tickets by group
  if (substr($filter,0,1) == "g")
  {
    $group_report = True;
    $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Group Statistic'));
    $statistic_report = True;
    $group_filter=substr($filter,1);

       if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_category = c.cat_id AND ticket_group = a.account_id AND ticket_group='".$group_filter."'";
        }
        else
        {
           $filtermethod = $filtermethod." ticket_category = c.cat_id AND ticket_group = a.account_id AND ticket_group='".$group_filter."'";
        }
    $from = $from.', phpgw_accounts a ';
    $groupby = $groupby.',ticket_group ';
  }

  //show detail groups report for certain category (d + category_id)
  if (substr($filter,0,1) == "d")
  {
    $group_report = True;
    $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Group Statistic'));
    $statistic_report = True;
    $group_filter=substr($filter,1);

       if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_category = c.cat_id AND ticket_group = a.account_id AND ticket_category='".$group_filter."'";
        }
        else
        {
           $filtermethod = $filtermethod." ticket_category = c.cat_id AND ticket_group = a.account_id AND ticket_category='".$group_filter."'";
        }
    $from = $from.', phpgw_accounts a ';
    $groupby = $groupby.',ticket_group ';
  }

  // if filter is c + category id then search for open tickets by category
  if (substr($filter,0,1) == "c")
  {
    $GLOBALS['phpgw']->template->set_var('lang_appname', lang('Trouble Ticket System').' - '.lang('Category Statistic'));
    $statistic_report = True;
    $category_filter=substr($filter,1);

       if ($filtermethod == "")
        {
           $filtermethod = "WHERE ticket_category='".$category_filter."' AND ticket_category = c.cat_id";
        }
        else
        {
           $filtermethod = $filtermethod." AND ticket_category='".$category_filter."' AND ticket_category = c.cat_id";
        }
  }

  if ($filter == 'viewhighcritical' || $filter =='viewmediumcritical' )
    {
    if ($GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'])
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','<META HTTP-EQUIV="Refresh" CONTENT="'.$GLOBALS['phpgw_info']['user']['preferences']['tts']['refreshinterval'].'; URL='.$GLOBALS['phpgw']->link('/tts/view_report.php',array('filter'=>$filter,'f_type'=>$f_type,'f_startdate'=>$f_startdate,'f_enddate'=>$f_enddate,'order'=>$order,'sort'=>$sort)));
    }
    else
    {
      $GLOBALS['phpgw']->template->set_var('autorefresh','');
    }
  }

  $f_startdate_d = '';
  $f_startdate_m = '';
  $f_startdate_Y = '';

  if (!($f_startdate == '' || $f_startdate == '0'))
  {

      $f_startdate_d = substr($f_startdate,0,2);
      $f_startdate_m = substr($f_startdate,3,2);
      $f_startdate_Y = substr($f_startdate,6,10);

      if ($GLOBALS['phpgw']->datetime->date_valid($f_startdate_Y,$f_startdate_m,$f_startdate_d) == True)
      {
              $filtermethod = $filtermethod." AND "
                            . " ((year(history_timestamp) > '".$f_startdate_Y."')"
                            . " OR (year(history_timestamp) >= '".$f_startdate_Y."'"
                            . "  AND month(history_timestamp) > '".$f_startdate_m."')"
                            . " OR (year(history_timestamp) >= '".$f_startdate_Y."'"
                            . "  AND month(history_timestamp) >= '".$f_startdate_m."'"
                            . "  AND dayofmonth(history_timestamp) >= '".$f_startdate_d."')"
                            . " )";

      }
      else
      {
         $f_startdate = '';
        $f_startdate_d = '';
        $f_startdate_m = '';
        $f_startdate_Y = '';
      }
  }


  $f_enddate_d = '';
  $f_enddate_m = '';
  $f_enddate_Y = '';

  if (!($f_enddate == '' || $f_enddate == '0'))
  {

      $f_enddate_d = substr($f_enddate,0,2);
      $f_enddate_m = substr($f_enddate,3,2);
      $f_enddate_Y = substr($f_enddate,6,10);
        /*
        function date_valid($year,$month,$day)
        {
            return checkdate((int)$month,(int)$day,(int)$year);
        }
        */
      if ($GLOBALS['phpgw']->datetime->date_valid($f_enddate_Y,$f_enddate_m,$f_enddate_d) == True)
      {
            // if start date is not o.k. then we need to add history_log table

            $filtermethod = $filtermethod." AND "
                            . " ((year(history_timestamp) < '".$f_enddate_Y."')"
                            . " OR (year(history_timestamp) <= '".$f_enddate_Y."'"
                            . "  AND month(history_timestamp) < '".$f_enddate_m."')"
                            . " OR (year(history_timestamp) <= '".$f_enddate_Y."'"
                            . "  AND month(history_timestamp) <= '".$f_enddate_m."'"
                            . "  AND dayofmonth(history_timestamp) <= '".$f_enddate_d."')"
                            . " )";

      }
      else
      {
        $f_enddate = '';
        $f_enddate_d = '';
        $f_enddate_m = '';
        $f_enddate_Y = '';
      }
  }

  if ($f_startdate_d <> '' || $f_enddate_d <> '')
  {
        $filtermethod = $filtermethod." AND history_appname = 'tts'"
                    . " AND history_record_id = ticket_id "
                    . " AND history_status = 'O' ";

        $from = $from.", phpgw_history_log h";
  }

  $search_type = "";

//  // set for a possible search filter, outside of the all/open only "state" filter above
//  if ($searchfilter)
//  {
//    $s_quoted = $GLOBALS['phpgw']->db->quote('%'.$searchfilter.'%');
//    //$filtermethod = "WHERE (ticket_details LIKE $s_quoted OR ticket_subject LIKE $s_quoted)";
//    $filtermethod = "WHERE (cat_name LIKE $s_quoted)";
//  }

//  $GLOBALS['phpgw']->template->set_var('tts_searchfilter',addslashes($searchfilter));


  // Added ACL check by Josip
  // if can NOT view all then use restricted search
  if (!$can_view_all)
  {
     if ($group_ids <> null)
     {
       /* if ($filtermethod == "")
        {
           $filtermethod == $filtermethod."WHERE";
        }
        else
        {
           $filtermethod == $filtermethod." AND";
        }
        */

        $filtermethod = $filtermethod." AND c.cat_id = g.cat_id AND g.account_id IN ($group_ids)";
        $from = $from.", phpgw_tts_categories_groups g";
     }
  }



  if (!preg_match('/^[a-z_]+$/i',$order) || !preg_match('/^(asc|desc)$/i',$sort))
  {
    $sortmethod = 'ORDER BY cat_name ASC';
  }
  else
  {
    $sortmethod = "ORDER BY $order $sort";
  }

  //$filtermethod == $filtermethod." GROUP BY ticket_category";

  $db_total = $db2 = $db = $GLOBALS['phpgw']->db;
//  $db->query($sql="SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);
//  $db->query($sql="SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);
//  $total = $db->num_rows();
//  $total = $db->next_record() ? $db->f(0) : 0;
  /*
  SELECT distinct ticket_category, cat_name FROM phpgw_tts_tickets t, phpgw_categories c
  WHERE ticket_category = c.cat_id
  ORDER BY cat_name
  */
  if ($statistic_report)
  {
       if ($group_report)
       {
            $db2->query($sql="SELECT ticket_category, cat_name, ticket_group, account_lid group_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $groupby $sortmethod",$start,__LINE__,__FILE__);
            $db->limit_query("SELECT ticket_category, cat_name, ticket_group, account_lid group_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $groupby $sortmethod",$start,__LINE__,__FILE__);

            $db_total->query($sql="SELECT count(*) total_number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod",$start,__LINE__,__FILE__);
       }
       else
       {
            $db2->query($sql="SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);
            $db->limit_query("SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);

            $db_total->query($sql="SELECT count(*) total_number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod",$start,__LINE__,__FILE__);
       }

  }
  else
  {
       $db2->query($sql="SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);
       $db->limit_query("SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);

       $db_total->query($sql="SELECT count(*) total_number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod",$start,__LINE__,__FILE__);
  }

  $total = $db2->num_rows();

  // if number of rows is larger then 1 then rows with totals is shown
  if ($total > 1)
  {
       $show_total_row = True;
  }

//  $db->limit_query("SELECT ticket_category, cat_name, count(*) number_of_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod GROUP BY ticket_category $sortmethod",$start,__LINE__,__FILE__);

  $GLOBALS['phpgw']->template->set_var('tts_numfound',$GLOBALS['phpgw']->nextmatchs->show_hits($total,$start));
  $GLOBALS['phpgw']->template->set_var('left',$GLOBALS['phpgw']->nextmatchs->left('/tts/view_report.php',$start,$total));
  $GLOBALS['phpgw']->template->set_var('right',$GLOBALS['phpgw']->nextmatchs->right('/tts/view_report.php',$start,$total));

  $tag = '';
    $GLOBALS['phpgw']->template->set_var('optionname', lang('View high critical categories'));
    $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewhighcritical');
    if ($filter == 'viewhighcritical' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  $tag = '';
  $GLOBALS['phpgw']->template->set_var('optionname', lang('View medium critical categories'));
  $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewmediumcritical');
  if ($filter == 'viewmediumcritical' )
  {
  $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  //Added custom filter - view statistic for group and category, ...

  if($can_view_all)
  {
      $tag = '';
      $GLOBALS['phpgw']->template->set_var('optionname', lang('View statistic for all categories'));
      $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewallcategories');
      if ($filter == 'viewallcategories' )
      {
      $tag = 'selected';
      }
      $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
      $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);


      $db_c = $GLOBALS['phpgw']->db;
      // $all_cat_where is included in where for category
      // maybe if($can_view_all) can be removed so that every user can see only category in which his group belongs
      // then this can be set for main query also
      $db_c->query("SELECT distinct a.cat_id, b.cat_name FROM phpgw_tts_categories_groups a, phpgw_categories b WHERE a.cat_id = b.cat_id AND b.cat_appname = 'tts' $all_cat_where ORDER BY b.cat_name",__LINE__,__FILE__);

      while($db_c->next_record())
      {


         $tag = '';
         $GLOBALS['phpgw']->template->set_var('optionname', lang('View statistic for category').' '.try_lang($db_c->f('cat_name')));
         $GLOBALS['phpgw']->template->set_var('optionvalue', "c".$db_c->f('cat_id'));

         if ($filter == "c".$db_c->f('cat_id') || $filter == "d".$db_c->f('cat_id'))
         {
            $tag = 'selected';
         }
         $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
         $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);


      }


      $tag = '';
      $GLOBALS['phpgw']->template->set_var('optionname', lang('View statistic for all groups'));
      $GLOBALS['phpgw']->template->set_var('optionvalue', 'viewallgroups');
      if ($filter == 'viewallgroups' )
      {
      $tag = 'selected';
      }
      $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
      $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);


  }


  // Additional add into filer combo
  //_________________________________________________________________________________
  /* if you want to add user groups (in which user is a member) --> to filter combo box
  foreach($group_list as $rowg)
  {
     $tag = '';
     $GLOBALS['phpgw']->template->set_var('optionname', lang('View statistic for group').' '.$rowg['account_name']);
     $GLOBALS['phpgw']->template->set_var('optionvalue', "g".$rowg['account_id']);

     if ($filter == "g".$rowg['account_id'] )
     {
        $tag = 'selected';
     }
     $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
     $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  }
  */
  //_________________________________________________________
  /* if you want to add all group which are connected to tts
  foreach($all_group_list as $rowg)
  {
     $tag = '';
     $GLOBALS['phpgw']->template->set_var('optionname', lang('View statistic for group').' '.$rowg['account_name']);
     $GLOBALS['phpgw']->template->set_var('optionvalue', "g".$rowg['account_id']);

     if ($filter == "g".$rowg['account_id'] )
     {
        $tag = 'selected';
     }
     $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
     $GLOBALS['phpgw']->template->parse('options_filter','options_select',True);

  }
  */
  //_________________________________________________________________________________


  // f_type display add into filer combo
  //_____________________________________________________________________
  $tag = '';
  $GLOBALS['phpgw']->template->set_var('optionname', lang('None'));
  $GLOBALS['phpgw']->template->set_var('optionvalue', '0');
  if ($f_type == '0' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_f_type','options_select',True);
  //______________________________
  $tag = '';
  $GLOBALS['phpgw']->template->set_var('optionname', lang('status, priority'));
  $GLOBALS['phpgw']->template->set_var('optionvalue', '1');
  if ($f_type == '1' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_f_type','options_select',True);
  //______________________________
  $tag = '';
  $GLOBALS['phpgw']->template->set_var('optionname', lang('customer satisfaction'));
  $GLOBALS['phpgw']->template->set_var('optionvalue', '2');
  if ($f_type == '2' )
  {
    $tag = 'selected';
  }
  $GLOBALS['phpgw']->template->set_var('optionselected', $tag);
  $GLOBALS['phpgw']->template->parse('options_f_type','options_select',True);
  //_____________________________________________________________________


  $GLOBALS['phpgw']->template->set_var('label_report_type', lang('Type of report'));
  $GLOBALS['phpgw']->template->set_var('label_startdate', lang('Start Date'));
  $GLOBALS['phpgw']->template->set_var('f_startdate', $jscal->input('f_startdate',$f_startdate));
  $GLOBALS['phpgw']->template->set_var('label_enddate', lang('End Date'));
  $GLOBALS['phpgw']->template->set_var('f_enddate', $jscal->input('f_enddate',$f_enddate));

  $GLOBALS['phpgw']->template->set_var('tts_ticketstotal', lang('Categories total %1',$numtotal));
  $GLOBALS['phpgw']->template->set_var('tts_ticketsopen', lang('Categories open %1',$numopen));

  // fill header
  $GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg'] );
  $GLOBALS['phpgw']->template->set_var('tts_head_category_name', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'cat_name',$order,'/tts/view_report.php',lang('Category')));
  $GLOBALS['phpgw']->template->set_var('tts_head_count_ticket', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'number_of_tickets',$order,'/tts/view_report.php',lang('Number of tickets')));

  if ($group_report)
  {
         $GLOBALS['phpgw']->template->set_var('tts_head_group_name',  $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'group_name',$order,'/tts/view_report.php',lang('Group')));
         $GLOBALS['phpgw']->template->parse('tts_head_group','tts_head_viewallgroup',false);
  }

  if ($statistic_report)
  {

    if ($f_type == '1' )
    {

        // sort doesn't work - it must be some other main select (UNION --> which doesn't work on all db verison)
        /*
        $GLOBALS['phpgw']->template->set_var('tts_head_num_open_tickets', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'num_open_tickets',$order,'/tts/view_report.php',lang('Open')));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_closed_tickets', $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'num_closed_tickets',$order,'/tts/view_report.php',lang('Closed')));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_initiative_tickets',  $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'num_initiative_tickets',$order,'/tts/view_report.php',lang('Initiative')));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_low',  $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'num_prio_low',$order,'/tts/view_report.php',lang('Low Priority')));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_medium',  $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'num_prio_medium',$order,'/tts/view_report.php',lang('Medium Priority')));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_high',  $GLOBALS['phpgw']->nextmatchs->show_sort_order($sort,'num_prio_high',$order,'/tts/view_report.php',lang('High Priority')));
        */

        // without sort ...
        $GLOBALS['phpgw']->template->set_var('tts_head_num_open_tickets', lang('Open'));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_closed_tickets', lang('Closed'));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_initiative_tickets', lang('Initiative'));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_low', lang('Low Priority'));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_medium', lang('Medium Priority'));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_high', lang('High Priority'));

        $GLOBALS['phpgw']->template->parse('tts_head_counts','tts_head_ifviewall_1',false);
    }
    elseif ($f_type == '2' )
    {

        $GLOBALS['phpgw']->template->set_var('tts_head_num_0', lang(csat_id2name("0")));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_1', lang(csat_id2name("1")));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_2', lang(csat_id2name("2")));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_3', lang(csat_id2name("3")));
        $GLOBALS['phpgw']->template->set_var('tts_head_num_5', lang(csat_id2name("5")));

        $GLOBALS['phpgw']->template->parse('tts_head_counts','tts_head_ifviewall_2',false);
    }

//    $GLOBALS['phpgw']->template->set_var('tts_t_timestampclosed',$GLOBALS['phpgw']->common->show_date($history_values[0]['datetime'] - ((60*60) * $GLOBALS['phpgw_info']['user']['preferences']['common']['tz_offset'])));
//    $GLOBALS['phpgw']->template->parse('tts_col_counts','tts_col_ifviewall_1',False);
  }



  if ($db->num_rows() == 0)
  {
    //$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No category found').'</center>');
    //$GLOBALS['phpgw']->template->set_var('rows', '<p><center>'.lang('No category found').'</center>');

    $GLOBALS['phpgw']->template->set_var('tts_head_bgcolor',$GLOBALS['phpgw_info']['theme']['bg00'] );
    $GLOBALS['phpgw']->template->set_var('tts_head_category_name', "");
    $GLOBALS['phpgw']->template->set_var('tts_head_count_ticket', "");
    if ($statistic_report)
    {
      // No data found without smiley image
        $GLOBALS['phpgw']->template->set_var('rows','<p><center>'.lang('No data found').'</center>');

        if ($group_report)
        {
             $GLOBALS['phpgw']->template->set_var('tts_head_group_name', "");
            $GLOBALS['phpgw']->template->parse('tts_head_group','tts_head_viewallgroup',false);
        }

        if ($f_type == '1' )
        {
         $GLOBALS['phpgw']->template->set_var('tts_head_num_open_tickets', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_closed_tickets', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_initiative_tickets', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_low', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_medium', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_prio_high', "");

            $GLOBALS['phpgw']->template->parse('tts_head_counts','tts_head_ifviewall_1',false);
        }
        elseif ($f_type == '2' )
        {
            $GLOBALS['phpgw']->template->set_var('tts_head_num_0', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_1', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_2', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_3', "");
            $GLOBALS['phpgw']->template->set_var('tts_head_num_5', "");

            $GLOBALS['phpgw']->template->parse('tts_head_counts','tts_head_ifviewall_2',false);
        }

    }
    else
    {
        // No category found but with smiley image which shows that everything is o.k. in tts, no critical tickets
        $GLOBALS['phpgw']->template->set_var('rows','<p><center><img src="templates/default/images/smiley.gif">'.lang('No category found').'</center>');
    }



  }
  else
  {
    while ($db->next_record())
    {

      if ($filter == 'viewhighcritical' )
      {
          $tr_color = $GLOBALS['phpgw_info']['theme']['bg10'];
          $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/red_dot.gif">');
      }
      elseif ($filter == 'viewmediumcritical' )
      {
          $tr_color = $GLOBALS['phpgw_info']['theme']['bg05'];
          $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/yellow_dot.gif">');
      }
      elseif ($f_type == '1' )
      {
          $tr_color = $GLOBALS['phpgw_info']['theme']['bg_color'];

        if ($filtermethod <> '')
          {
                $filtermethod_d = $filtermethod." AND";
          }
          else
          {
                $filtermethod_d = $filtermethod." WHERE";
          }


          $db_d = $GLOBALS['phpgw']->db;

          if ($group_report)
          {
                $filtermethod_d = $filtermethod_d." ticket_group = '".$db->f('ticket_group')."'";
          }
          else
          {
                $filtermethod_d = $filtermethod_d." ticket_category = '".$db->f('ticket_category')."'";
          }

          // get number of open tickets
          $filtermethod_detail = $filtermethod_d." AND ticket_status = 'O'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_open_tickets',$db_d->f('num_tickets'));
          }

          //get number of initiative ticket
          $filtermethod_detail = $filtermethod_d." AND ticket_status = 'I'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_initiative_tickets',$db_d->f('num_tickets'));
          }

          //get number of closed ticket
          $filtermethod_detail = $filtermethod_d." AND ticket_status = 'X'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_closed_tickets',$db_d->f('num_tickets'));
          }

          $priority_low = false;
          $priority_medium = false;
          $priority_high = false;

          //get number of ticket with priority between 1-4
          $filtermethod_detail = $filtermethod_d." AND ticket_priority IN ('1','2','3','4')";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_prio_low',$db_d->f('num_tickets'));
                if ($db_d->f('num_tickets') > 0)
                {
                  $priority_low = true;
                }
          }

          //get number of ticket with priority between 5-7
          $filtermethod_detail = $filtermethod_d." AND ticket_priority IN ('5','6','7')";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_prio_medium',$db_d->f('num_tickets'));
                if ($db_d->f('num_tickets') > 0)
                {
                     $priority_medium = true;
                }

          }

          //get number of ticket with priority between 8-10
          $filtermethod_detail = $filtermethod_d." AND ticket_priority IN ('8','9','10')";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_prio_high',$db_d->f('num_tickets'));
                if ($db_d->f('num_tickets') > 0)
                {
                     $priority_high = true;
                }
          }


          $GLOBALS['phpgw']->template->parse('tts_col_counts','tts_col_ifviewall_1',false);
          $GLOBALS['phpgw']->template->parse('tts_col_group_name','tts_col_ifviewall_1',false);

          /* if you want to see red, yellow or green dot - according to the priority level uncomment the following lines
          //_________________________________________________________
          if ($priority_high)
          {
              $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/red_dot.gif">');
          }
          elseif ($priority_medium)
          {
              $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/yellow_dot.gif">');
          }
          elseif ($priority_low)
          {
              $GLOBALS['phpgw']->template->set_var('row_status','<img src="templates/default/images/green_dot.gif">');
          }
          //_______________________________________
          */
          // or you can see details for certain category
           if (!$group_report)
          {
              $view_link = '<a href="' . $GLOBALS['phpgw']->link('/tts/view_report.php',array('filter'=>"d".$db->f('ticket_category'),'f_type'=>$f_type,'f_startdate'=>$f_startdate,'f_enddate'=>$f_enddate,'order'=>"",'sort'=>"")). '">';
              $GLOBALS['phpgw']->template->set_var('row_status',$view_link . '+' . '</a>');
          }
          //_________________________________________________________

      }
      elseif ($f_type == '2' )
      {
          $tr_color = $GLOBALS['phpgw_info']['theme']['bg_color'];

            if ($filtermethod <> '')
          {
                $filtermethod_d = $filtermethod." AND";
          }
          else
          {
                $filtermethod_d = $filtermethod." WHERE";
          }


          $db_d = $GLOBALS['phpgw']->db;

          if ($group_report)
          {
                $filtermethod_d = $filtermethod_d." ticket_group = '".$db->f('ticket_group')."'";
          }
          else
          {
                $filtermethod_d = $filtermethod_d." ticket_category = '".$db->f('ticket_category')."'";
          }

          // get number of ticket where customer satisfaction = 0
          $filtermethod_detail = $filtermethod_d." AND ticket_caller_satisfaction = 'O'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_0',$db_d->f('num_tickets'));
          }

          // get number of ticket where customer satisfaction = 1
          $filtermethod_detail = $filtermethod_d." AND ticket_caller_satisfaction = '1'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_1',$db_d->f('num_tickets'));
          }

          // get number of ticket where customer satisfaction = 2
          $filtermethod_detail = $filtermethod_d." AND ticket_caller_satisfaction = '2'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_2',$db_d->f('num_tickets'));
          }

          // get number of ticket where customer satisfaction = 3
          $filtermethod_detail = $filtermethod_d." AND ticket_caller_satisfaction = '3'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_3',$db_d->f('num_tickets'));
          }

          // get number of ticket where customer satisfaction = 5
          $filtermethod_detail = $filtermethod_d." AND ticket_caller_satisfaction = '5'";
          $db_d->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_d->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_5',$db_d->f('num_tickets'));
          }


          $GLOBALS['phpgw']->template->parse('tts_col_counts','tts_col_ifviewall_2',false);
          $GLOBALS['phpgw']->template->parse('tts_col_group_name','tts_col_ifviewall_2',false);


          // click to details for certain category displayed by groups
           if (!$group_report)
          {
              $view_link = '<a href="' . $GLOBALS['phpgw']->link('/tts/view_report.php',array('filter'=>"d".$db->f('ticket_category'),'f_type'=>$f_type,'f_startdate'=>$f_startdate,'f_enddate'=>$f_enddate,'order'=>"",'sort'=>"")). '">';
              $GLOBALS['phpgw']->template->set_var('row_status',$view_link . '+' . '</a>');
          }

      }


      $GLOBALS['phpgw']->template->set_var('tts_row_color', $tr_color );
      $GLOBALS['phpgw']->template->set_var('tts_ticketdetails_link', $GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>"c".$db->f('ticket_category'),'order'=>"",'sort'=>"")));

      $view_link = '<a href="' . $GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>"c".$db->f('ticket_category'),'order'=>"",'sort'=>"")). '">';
      $GLOBALS['phpgw']->template->set_var('tts_category_name',$view_link . $db->f('cat_name') . '</a>');
      if ($group_report)
      {
            $view_link = '<a href="' . $GLOBALS['phpgw']->link('/tts/index.php',array('filter'=>"g".$db->f('ticket_group'),'order'=>"",'sort'=>"")). '">';
            $GLOBALS['phpgw']->template->set_var('tts_col_group_name',$view_link . $db->f('group_name') . '</a>');
            $GLOBALS['phpgw']->template->parse('tts_col_group','tts_col_viewallgroup',False);
      }


      $GLOBALS['phpgw']->template->set_var('tts_number_of_tickets',$db->f('number_of_tickets'));



      $GLOBALS['phpgw']->template->parse('rows','tts_row',True);
    }
  }

  //if row with totals must be shown, then show total for columns according to the report type
  if ($show_total_row)
  {
      $tr_color = $GLOBALS['phpgw_info']['theme']['bg01'];
      $GLOBALS['phpgw']->template->set_var('tts_row_total_color', $tr_color );
      $GLOBALS['phpgw']->template->set_var('row_total_status','');

      $GLOBALS['phpgw']->template->set_var('tts_total_category_name','Total');
      if ($group_report)
      {
            $GLOBALS['phpgw']->template->set_var('tts_col_group_name','');
            $GLOBALS['phpgw']->template->parse('tts_total_col_group','tts_col_viewallgroup',False);
      }


      $db_total->next_record();

      $GLOBALS['phpgw']->template->set_var('tts_total_number_of_tickets',$db_total->f('total_number_of_tickets'));

      if ($f_type == '1')
      {
          // get total number of open tickets
          $filtermethod_detail = $filtermethod." AND ticket_status = 'O'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_open_tickets',$db_total->f('num_tickets'));
          }



          //get total number of initiative ticket
          $filtermethod_detail = $filtermethod." AND ticket_status = 'I'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_initiative_tickets',$db_total->f('num_tickets'));
          }

          //get total number of closed ticket
          $filtermethod_detail = $filtermethod." AND ticket_status = 'X'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_closed_tickets',$db_total->f('num_tickets'));
          }

          //get number of ticket with priority between 1-4
          $filtermethod_detail = $filtermethod." AND ticket_priority IN ('1','2','3','4')";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_prio_low',$db_total->f('num_tickets'));
          }

          //get number of ticket with priority between 5-7
          $filtermethod_detail = $filtermethod." AND ticket_priority IN ('5','6','7')";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_prio_medium',$db_total->f('num_tickets'));
          }

          //get number of ticket with priority between 8-10
          $filtermethod_detail = $filtermethod." AND ticket_priority IN ('8','9','10')";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_prio_high',$db_total->f('num_tickets'));
          }

          $GLOBALS['phpgw']->template->parse('tts_total_col_counts','tts_col_ifviewall_1',false);

      }
      elseif ($f_type == '2')
      {

          // get total number of ticket where customer satisfaction = 0
          $filtermethod_detail = $filtermethod." AND ticket_caller_satisfaction = 'O'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_0',$db_total->f('num_tickets'));
          }

          // get total number of ticket where customer satisfaction = 1
          $filtermethod_detail = $filtermethod." AND ticket_caller_satisfaction = '1'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_1',$db_total->f('num_tickets'));
          }

          // get total number of ticket where customer satisfaction = 2
          $filtermethod_detail = $filtermethod." AND ticket_caller_satisfaction = '2'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_2',$db_total->f('num_tickets'));
          }

          // get total number of ticket where customer satisfaction = 3
          $filtermethod_detail = $filtermethod." AND ticket_caller_satisfaction = '3'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_3',$db_total->f('num_tickets'));
          }

          // get total number of ticket where customer satisfaction = 5
          $filtermethod_detail = $filtermethod." AND ticket_caller_satisfaction = '5'";
          $db_total->query("SELECT count(*) num_tickets FROM phpgw_tts_tickets t, phpgw_categories c $from $filtermethod_detail",__LINE__,__FILE__);
          while($db_total->next_record())
          {
                $GLOBALS['phpgw']->template->set_var('tts_num_5',$db_total->f('num_tickets'));
          }


          $GLOBALS['phpgw']->template->parse('tts_total_col_counts','tts_col_ifviewall_2',false);

      }




      $GLOBALS['phpgw']->template->parse('rows_total','tts_row_total',True);


  }

  // this is a workaround to clear the subblocks autogenerated vars
  $GLOBALS['phpgw']->template->set_var('tts_row','');
  $GLOBALS['phpgw']->template->set_var('tts_row_total','');
  $GLOBALS['phpgw']->template->set_var('tts_col_ifviewall_1','');
  $GLOBALS['phpgw']->template->set_var('tts_head_ifviewall_1','');
  $GLOBALS['phpgw']->template->set_var('tts_col_ifviewall_2','');
  $GLOBALS['phpgw']->template->set_var('tts_head_ifviewall_2','');
  $GLOBALS['phpgw']->template->set_var('tts_col_viewallgroup','');
  $GLOBALS['phpgw']->template->set_var('tts_head_viewallgroup','');

  $GLOBALS['phpgw']->template->set_var('options_select','');

  $GLOBALS['phpgw']->template->pfp('out','view_report');

  $GLOBALS['phpgw']->common->phpgw_footer();
  ?>
