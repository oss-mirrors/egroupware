<?php
  /* $Id$ */

  function try_lang($phrase,$param=False,$upcase=False)
  {
    $trans = $GLOBALS['phpgw']->translation->translate($phrase,
      is_array($param) ? $param : array($param),False);

    return $upcase ? strtoupper($trans) : $trans;
  }

   /**
  * Produce an option list from the database table to be used in HTML template.
  */
  function listid_field($table,$field,$idf,$selected,$conditions=False)
  {
    $db=$GLOBALS['phpgw']->db;
    $sql  = 'SELECT '.$db->db_addslashes($field).','.$db->db_addslashes($idf).
      ' FROM '.$db->db_addslashes($table). ($conditions?' WHERE '.$conditions:'');
    $db->query($sql,__FILE__,__LINE__);

    while ($db->next_record())
    {
      $val=$db->f($idf,True);
      $select .= '<option value="' . intval($val). ($val==$selected?'" SELECTED>':'" >');
      $select .= htmlspecialchars(try_lang($db->f($field,True),False,True))
        . "</option>\n";
    }
    return $select;
  }

  /**
  * Obtain a value of a field for a given row from the database table.
  */
  function id2field($table,$field,$idf,$id,$toupper=True)
  {
    $db=$GLOBALS['phpgw']->db;
    $sql  = 'SELECT '.$db->db_addslashes($field).' FROM '.$db->db_addslashes($table). " WHERE ".$db->db_addslashes($idf)."=" . intval($id);
    $db->query($sql,__FILE__,__LINE__);
    if($db->next_record())
    {
      return try_lang($db->f($field,True),False,$toupper);
    }
    else
    {
      return '';
    }
  }


  //open and print each line of a file
  function rfile($textFile)
  {
    $myFile = fopen("$textFile", "r");
    if(!($myFile))
    {
      print("<P><B>Error: </B>");
      print("<i>'$textFile'</i> could not be read\n");
      $phpgw->common->phpgw_exit();
    }
    if($myFile)
    {
      while(!feof($myFile)) {
        $myLine = fgets($myFile, 255);
        print("$myLine <BR>\n");
      }
      fclose($myFile);
    }
  }

  // add by Josip
  // $ticket_state_is_assigned is new parameter which is used
  // for sending mail when ticket_state = assigned
  function mail_ticket($ticket_id, $ticket_state_is_assigned=false)
  {
    $members = array();
    //add by Josip
    $members = array();
    // end add

    // $GLOBALS['phpgw']->preferences->read_repository();
    // $GLOBALS['phpgw_info']['user']['preferences']['tts']['mailnotification']

    $GLOBALS['phpgw']->config->read_repository();

    if ($GLOBALS['phpgw']->config->config_data['mailnotification']) {

      $GLOBALS['phpgw']->send = CreateObject('phpgwapi.send');

      $GLOBALS['phpgw']->db->query("select * from phpgw_tts_tickets where ticket_id='$ticket_id'");
      $GLOBALS['phpgw']->db->next_record();

      $group_id = $GLOBALS['phpgw']->db->f('ticket_group');
      $group_name = $GLOBALS['phpgw']->accounts->id2name($group_id);
      $t_subject = $GLOBALS['phpgw']->db->f('ticket_subject');
      $t_assigned = $GLOBALS['phpgw']->db->f('ticket_assignedto');
      $t_assigned_name = $GLOBALS['phpgw']->accounts->id2name($t_assigned);
      $t_owner_name = $GLOBALS['phpgw']->accounts->id2name($GLOBALS['phpgw']->db->f('ticket_owner'));

      //add by Josip
      $t_priority = $GLOBALS['phpgw']->db->f('ticket_priority');
      //end add

      // build subject
      $subject = '['.lang('Ticket').' #'.$ticket_id.' '.$group_name.'] '.lang(($GLOBALS['phpgw']->db->f('ticket_status')!='X')?'Updated':'Closed').': '.$GLOBALS['phpgw']->db->f('ticket_subject');

      // build body
      $body  = '';
      $body .= lang('Ticket').' #'.$ticket_id."\n";
      $body .= lang('Subject').': '.$t_subject."\n";
      $body .= lang('Assigned To').': '.$t_assigned_name."\n";
      $body .= lang('Priority').': '.$GLOBALS['phpgw']->db->f('ticket_priority')."\n";
      $body .= lang('Group').': '.$group_name."\n";
      $body .= lang('Opened By').': '.$t_owner_name."\n\n";
      $body .= lang('Latest Note Added').":\n";
      /**************************************************************\
      * Display latest note                                         *
      \**************************************************************/

      $GLOBALS['phpgw']->historylog = createobject('phpgwapi.historylog','tts');

      $history_array = $GLOBALS['phpgw']->historylog->return_array(array(),array('C'),'','',$ticket_id);
      while (is_array($history_array) && list(,$value) = each($history_array))
      {
        $latest_note=$GLOBALS['phpgw']->common->show_date($value['datetime'])." - ".$value['owner'];
                                $latest_note.=" - ".stripslashes($value['new_value'])."\n";
//        $GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
//        $body.= "$GLOBALS['phpgw']->template->set_var('value_date',$GLOBALS['phpgw']->common->show_date($value['datetime']));
//        $GLOBALS['phpgw']->template->set_var('value_user',$value['owner']);

//        $GLOBALS['phpgw']->template->set_var('value_note',nl2br(stripslashes($value['new_value'])));
//        $GLOBALS['phpgw']->template->fp('rows_notes','additional_notes_row',True);
      }

      if (! count($history_array))
      {
        $latest_note=lang('No notes for this ticket')."\n";
      }

      $body .= $latest_note;

      $body .= "\n\n".lang('Original Ticket Details').":\n".$GLOBALS['phpgw']->db->f('ticket_details')."\n\n";


//      if($GLOBALS['phpgw']->db->f('t_timestamp_closed'))
//      {
//        $body .= 'Date Closed: '.$GLOBALS['phpgw']->common->show_date($GLOBALS['phpgw']->db->f('t_timestamp_closed'))."\n\n";
//      }
      $body .= stripslashes(strip_tags($GLOBALS['phpgw']->db->f('ticket_detail')))."\n\n.";


      // do we need to email all the users in the group assigned to this ticket?
      if ($GLOBALS['phpgw']->config->config_data['groupnotification'])
      {
        // select group recipients
        $members  = $GLOBALS['phpgw']->accounts->member($group_id);
      }

      // do we need to email the owner of this ticket?
      if ($GLOBALS['phpgw']->config->config_data['ownernotification'])
      {
        // add owner to recipients
        $members[] = array('account_id' => $GLOBALS['phpgw']->db->f('ticket_owner'));
      }

      // do we need to email the user who is assigned to this ticket?
      if ($GLOBALS['phpgw']->config->config_data['assignednotification'])
      {
        // add assigned to recipients
        $members[] = array('account_id' => $t_assigned);
      }

      //add by Josip
      // do we need to email the user when the ticket is assigned to him?
      if ($GLOBALS['phpgw']->config->config_data['assignmentnotification'])
      {
        // add assigned to recipients when the ticket state = assigned
        if ($ticket_state_is_assigned)
        {
         	$members[] = array('account_id' => $t_assigned);
        }
      }

      // do we need to email the user group when the ticket is assigned to him?
      if ($GLOBALS['phpgw']->config->config_data['assignmentgroupnotification'])
      {
        // add group members to recipients when the ticket state = assigned
        if ($ticket_state_is_assigned)
        {
             $members[] = $GLOBALS['phpgw']->accounts->member($group_id);
        }
      }

      
      /*
      // This is under comment because account table must have additional attribute email_2
      //_____________________________________________________________________________________
      // do we need to email the user to the secondary email when the ticket is changed?
      if ($GLOBALS['phpgw']->config->config_data['email2assignednotification'])
      {
        // add assigned to recipients
        $members_email2[] = array('account_id' => $t_assigned);
      }

      // do we need to email the user to the secondary email when the ticket is assigned to him?
      if ($GLOBALS['phpgw']->config->config_data['email2assignmentnotification'])
      {
        // add assigned to recipients when the ticket state = assigned
        if ($ticket_state_is_assigned)
        {
         	$members_email2[] = array('account_id' => $t_assigned);
        }
      }

      // do we need to email the user group to the secondary email when the ticket is assigned to him?
      if ($GLOBALS['phpgw']->config->config_data['email2assignmentgroupnotification'])
      {
        // add group members to recipients when the ticket state = assigned
        if ($ticket_state_is_assigned)
        {
             $members_email2[] = $GLOBALS['phpgw']->accounts->member($group_id);
        }
      }

      // do we need to email the user to the secondary email when the high priority ticket is changed?
      if ($GLOBALS['phpgw']->config->config_data['email2highpriorityassignednotification'])
      {
        //add only if ticket priority is high
        if ($t_priority > 5)
        {
            // add assigned to recipients
            $members_email2[] = array('account_id' => $t_assigned);
        }
      }

      // do we need to email the user to the secondary email when the high priority ticket is assigned to him?
      if ($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentnotification'])
      {
        //add only if ticket priority is high
        if ($t_priority > 5)
        {
         	if ($ticket_state_is_assigned)
            {
         	 	// add assigned to recipients
                $members_email2[] = array('account_id' => $t_assigned);
            }
        }
      }

      // do we need to email the user group to the secondary email when the high priority ticket is assigned to him?
      if ($GLOBALS['phpgw']->config->config_data['email2highpriorityassignmentgroupnotification'])
      {
        //add only if ticket priority is high
        if ($t_priority > 5)
        {
             if ($ticket_state_is_assigned)
            {
                  // add group members to recipients
                $members_email2[] = $GLOBALS['phpgw']->accounts->member($group_id);
            }
        }
      }
      //_____________________________________________________________________________________
      */

      //end add

      $toarray = Array();
      $i=0;
      for ($i=0;$i<count($members);$i++)
      {
        if ($members[$i])
        {
          $toarray[] = $GLOBALS['phpgw']->accounts->id2name($members[$i]['account_id'], 'account_email');
        }
      }
      /*
      // This is under comment because account table must have additional attribute email_2
      //_____________________________________________________________________________________
      //add by Josip - email_2
      $i=0;
      for ($i=0;$i<count($members_email2);$i++)
      {
        if ($members_email2[$i])
        {
          $toarray[] = $GLOBALS['phpgw']->accounts->id2name($members_email2[$i]['account_id'], 'account_email_2');
        }
      }
      //_____________________________________________________________________________________
      */

      //
      if(count($toarray) > 1)
      {
        $to = implode(',',$toarray);
      }
      else
      {
        $to = current($toarray);
      }

      $body=html_deactivate_urls($body);
      if (($members) || ($members_email2))
      {
        $rc = $GLOBALS['phpgw']->send->msg('email', $to, $subject, $body, '', $cc, $bcc);
        if (!$rc)
        {
          echo  lang('Your message could <B>not</B> be sent!<BR>')."\n"
            . lang('the mail server returned').':<BR>'
            . "err_code: '".$GLOBALS['phpgw']->send->err['code']."';<BR>"
            . "err_msg: '".htmlspecialchars($GLOBALS['phpgw']->send->err['msg'])."';<BR>\n"
            . "err_desc: '".$GLOBALS['phpgw']->err['desc']."'.<P>\n"
            . lang('To go back to the tts index, click <a href= %1 >here</a>',$GLOBALS['phpgw']->link('/tts/index.php','cd=13'));
          $GLOBALS['phpgw']->common->phpgw_exit();
        }
     }
   }
}

function html_activate_urls($str)
{
    // lift all links, images and image maps

    preg_match_all("/<a [^>]+>.*<\/a>/is", $str, $matches, PREG_SET_ORDER);
    foreach($matches as $match)
    {
        $key = "<" . md5($match[0]) . ">";
        $search[] = $key;
        $replace[] = $match[0];
    }

    preg_match_all("/<map [^>]+>.*<\/map>/is", $str, $matches, PREG_SET_ORDER);
    foreach($matches as $match)
    {
        $key = "<" . md5($match[0]) . ">";
        $search[] = $key;
        $replace[] = $match[0];
    }


    preg_match_all("/<img [^>]+>/is", $str, $matches, PREG_SET_ORDER);
    foreach($matches as $match)
    {
        $key = "<" . md5($match[0]) . ">";
        $search[] = $key;
        $replace[] = $match[0];
    }

    $str = str_replace($replace, $search, $str);


    // indicate where urls end if they have these trailing special chars
    $sentinals = array("'&(quot|#34);'i",                 // Replace html entities
                       "'&(lt|#60);'i",
                       "'&(gt|#62);'i",
                       "'&(nbsp|#160);'i",
                       "'&(iexcl|#161);'i",
                       "'&(cent|#162);'i",
                       "'&(pound|#163);'i",
                       "'&(copy|#169);'i",
                       "'&#(\d+);'i");

    $str = preg_replace($sentinals, "^^sentinal^^\\0^^sentinal^^", $str);

    $vdom = "[:alnum:]";                // Valid domain chars
    $vurl = $vdom."_~-";                // Valid subdomain and path chars
    $vura = "A-Ya-y!#$%&*+,;=@./".$vurl; // Valid additional parameters (after '?') chars;
                                        // insert other local characters if needed
    $protocol = "[[:alpha:]]{3,10}://"; // Protocol exp
    $server = "([$vurl]+[.])*[$vdom]+"; // Server name exp
    $path = "(([$vurl]+([.][$vurl]+)*/)|([.]{1,2}/))*"; // Document path exp (/.../)
    $name = "[$vurl]+([.][$vurl]+)*";   // Document name exp
    $params = "[?][$vura]*";            // Additional parameters (for GET)

    // URL into links
    $str = eregi_replace("$protocol$server(/$path($name)?)?($params)?",  "<a href=\"\\0\">\\0</a>", $str); 

    // mailto into links
    $protocol = "mailto:"; // Protocol exp
    $str = eregi_replace("$protocol$name@$server($params)?",  "<a href=\"\\0\">\\0</a>", $str);

    // <someone@somewhere.net> into links
    $str = eregi_replace("<($name@$server($params)?)>",  "&lt;<a href=\"mailto:\\1\">\\1</a>&gt;", $str); 

    $str = str_replace("^^sentinal^^", '', $str);
    $str=str_replace($search, $replace, $str);
    return $str;
}

function html_deactivate_urls($str)
{
  $str=eregi_replace("<a[^>]+>","",$str);
  $str=eregi_replace("</a>","",$str);
  
  return $str;
}


function csat_id2name($csatisfaction_id)
{
        $csat_id = (int)$csatisfaction_id;

        switch($csat_id)
        {
                case '0':        $value = 'None'; break;
                case '1':        $value = 'No Comment'; break;
                case '2':        $value = 'Not Satisfied'; break;
                case '3':        $value = 'Partitialy Satisfied'; break;
                case '5':        $value = 'Satisfied'; break;
                default:         $value = 'None'; break;
        }
        return $value;

}

/*! - this functions can be added to pgpgw common fuctions, ...
//but for now it is here
@function randomstringnumber
@abstract return a random string number of size $size
@param $size int-size of random string number to return
*/
function randomstringnumber($size)
{
    $s = '';
    srand((double)microtime()*1000000);
    $random_char = array(
        '0','1','2','3','4','5','6','7','8','9'
    );

    for ($i=0; $i<$size; $i++)
    {
        $s .= $random_char[rand(1,9)];
    }
    return $s;
}
?>
