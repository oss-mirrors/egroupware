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

   $GLOBALS['phpgw_info']['flags']['currentapp']          = 'tts';
   $GLOBALS['phpgw_info']['flags']['enable_send_class']   = True;
   $GLOBALS['phpgw_info']['flags']['enable_config_class'] = True;
   $GLOBALS['phpgw_info']['flags']['enable_categories_class'] = True;
   $GLOBALS['phpgw_info']['flags']['noheader']            = True;

   include('../header.inc.php');

   $escalation_id = intval(get_var('escalation_id',array('POST','GET')));

   if($_POST['cancel'])
   {
      $GLOBALS['phpgw']->redirect_link('/tts/escalation.php');
   }

   $GLOBALS['phpgw']->config->read_repository();

   if($_POST['save'])
   {
      $escalation = $_POST['escalation'];
      /*if (get_magic_quotes_gpc())
      {
         foreach(array('name','description') as $name)
         {
            $transition[$name] = stripslashes($transition[$name]);
         }
      }
                */

      if (!$escalation_id)
      {
         $GLOBALS['phpgw']->db->query("insert into phpgw_tts_escalation "
                        ."(ticket_group, ticket_priority_1, ticket_priority_2, time_1, time_2, time_3, email_1, email_2) values ("
         . $GLOBALS['phpgw']->db->quote($escalation['ticket_group'],'int') . ", "
                        . $GLOBALS['phpgw']->db->quote($escalation['ticket_priority_1'],'int') . ", "
                        . $GLOBALS['phpgw']->db->quote($escalation['ticket_priority_2'],'int') . ", "
                        . $GLOBALS['phpgw']->db->quote($escalation['time_1'],'int') . ", "
                        . $GLOBALS['phpgw']->db->quote($escalation['time_2'],'int') . ", "
                        . $GLOBALS['phpgw']->db->quote($escalation['time_3'],'int') . ", "
                        . $GLOBALS['phpgw']->db->quote(($escalation['email_1']=='on'?1:0),'int'). ", "
                        . $GLOBALS['phpgw']->db->quote(($escalation['email_2']=='on'?1:0),'int'). ")",__LINE__,__FILE__);


      }
      else
      {
         $GLOBALS['phpgw']->db->query("update phpgw_tts_escalation "
            . " set ticket_group=". $GLOBALS['phpgw']->db->quote($escalation['ticket_group'],'int'). ", "
            . " ticket_priority_1=". $GLOBALS['phpgw']->db->quote($escalation['ticket_priority_1'],'int'). ", "
                                . " ticket_priority_2=". $GLOBALS['phpgw']->db->quote($escalation['ticket_priority_2'],'int'). ", "
                                . " time_1=". $GLOBALS['phpgw']->db->quote($escalation['time_1'],'int'). ", "
                                . " time_2=". $GLOBALS['phpgw']->db->quote($escalation['time_2'],'int'). ", "
                                . " time_3=". $GLOBALS['phpgw']->db->quote($escalation['time_3'],'int'). ", "
                                . " email_1=". $GLOBALS['phpgw']->db->quote(($escalation['email_1']=='on'?1:0),'int'). ", "
                                . " email_2=". $GLOBALS['phpgw']->db->quote(($escalation['email_2']=='on'?1:0),'int')
            . " WHERE escalation_id=".$GLOBALS['phpgw']->db->quote($escalation_id,'int'),__LINE__,__FILE__);

      }
      $GLOBALS['phpgw']->redirect_link('/tts/escalation.php');
   }
   else
   {
      $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['tts']['title'].
         ' - '.($escalation_id ? lang('Edit the escalation parameters for certain group') : lang('Create new escalation parameters for certain group'));
      $GLOBALS['phpgw']->common->phpgw_header();

      // select the escalation that you selected
      $GLOBALS['phpgw']->db->query("select * from phpgw_tts_escalation where escalation_id='$escalation_id'",__LINE__,__FILE__);
      $GLOBALS['phpgw']->db->next_record();

      $escalation['ticket_group']      = $GLOBALS['phpgw']->db->f('ticket_group');
      $escalation['ticket_priority_1'] = $GLOBALS['phpgw']->db->f('ticket_priority_1');
                $escalation['ticket_priority_2'] = $GLOBALS['phpgw']->db->f('ticket_priority_2');
                $escalation['time_1'] = $GLOBALS['phpgw']->db->f('time_1');
                $escalation['time_2'] = $GLOBALS['phpgw']->db->f('time_2');
                $escalation['time_3'] = $GLOBALS['phpgw']->db->f('time_3');
                $escalation['email_1'] = $GLOBALS['phpgw']->db->f('email_1');
                $escalation['email_2'] = $GLOBALS['phpgw']->db->f('email_2');

      $GLOBALS['phpgw']->template->set_file(array(
         'edit_escalation'   => 'edit_escalation.tpl'
      ));
      $GLOBALS['phpgw']->template->set_block('edit_escalation','form');
                $GLOBALS['phpgw']->template->set_block('edit_escalation','options_select');

      $GLOBALS['phpgw']->template->set_var('form_action', $GLOBALS['phpgw']->link('/tts/edit_escalation.php','&escalation_id='.$escalation_id));

      $GLOBALS['phpgw']->template->set_var('lang_group_name', lang('Group'));
                $GLOBALS['phpgw']->template->set_var('lang_priority_between', lang('Priority between'));
                $GLOBALS['phpgw']->template->set_var('lang_time_1', lang('Time for escalation 1 (in seconds)'));
                $GLOBALS['phpgw']->template->set_var('lang_time_2', lang('Time for escalation 2 (in seconds)'));
                $GLOBALS['phpgw']->template->set_var('lang_time_3', lang('Time for escalation 3 (in seconds)'));
                $GLOBALS['phpgw']->template->set_var('lang_email_1', lang('Send escalation to primary email'));
                $GLOBALS['phpgw']->template->set_var('lang_email_2', lang('Send escalation to secondary email'));
      $GLOBALS['phpgw']->template->set_var('lang_save',lang('Save'));
      $GLOBALS['phpgw']->template->set_var('lang_cancel',lang('Cancel'));

                $GLOBALS['phpgw']->template->set_var('options_account_id',listid_field('phpgw_accounts','account_lid','account_id',$escalation['ticket_group'],'account_type="g"'));


                 // Choose the correct priority 1 to display
                $priority_selected_1[$escalation['ticket_priority_1']] = ' selected';
                $priority_comment_1[1]  = ' - '.lang('Lowest');
                $priority_comment_1[5]  = ' - '.lang('Medium');
                $priority_comment_1[10] = ' - '.lang('Highest');

                for($i=1; $i<=10; $i++)
                {
                  $GLOBALS['phpgw']->template->set_var('optionname', $i.$priority_comment_1[$i]);
                  $GLOBALS['phpgw']->template->set_var('optionvalue', $i);
                  $GLOBALS['phpgw']->template->set_var('optionselected', $priority_selected_1[$i]);
                  $GLOBALS['phpgw']->template->parse('options_priority_1','options_select',true);
                }


                 // Choose the correct priority 2 to display
                $priority_selected_1[$escalation['ticket_priority_2']] = ' selected';
                $priority_comment_1[1]  = ' - '.lang('Lowest');
                $priority_comment_1[5]  = ' - '.lang('Medium');
                $priority_comment_1[10] = ' - '.lang('Highest');

                for($i=1; $i<=10; $i++)
                {
                  $GLOBALS['phpgw']->template->set_var('optionname', $i.$priority_comment_1[$i]);
                  $GLOBALS['phpgw']->template->set_var('optionvalue', $i);
                  $GLOBALS['phpgw']->template->set_var('optionselected', $priority_selected_1[$i]);
                  $GLOBALS['phpgw']->template->parse('options_priority_2','options_select',true);
                }


                $GLOBALS['phpgw']->template->set_var('value_time_1',($escalation['time_1']));
                $GLOBALS['phpgw']->template->set_var('value_time_2',($escalation['time_2']));
                $GLOBALS['phpgw']->template->set_var('value_time_3',($escalation['time_3']));

                $GLOBALS['phpgw']->template->set_var('value_email_1',($escalation['email_1']?'CHECKED':''));
                $GLOBALS['phpgw']->template->set_var('value_email_2',($escalation['email_2']?'CHECKED':''));

      $GLOBALS['phpgw']->template->pfp('out','form');
      $GLOBALS['phpgw']->common->phpgw_footer();
   }


?>


