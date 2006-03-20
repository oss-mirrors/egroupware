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

  function mail_ticket($ticket_id, $owner_notify = True)
  {
    $members = array();

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

      // build subject
      $subject = '['.lang('Ticket').' #'.$ticket_id.' '.$group_name.'] '.lang(($GLOBALS['phpgw']->db->f('ticket_status')!='X')?'Updated':'Closed').': '.$GLOBALS['phpgw']->db->f('ticket_subject');

      // build body
      $body  = '';
      $body .= lang('Ticket').' #'.$ticket_id."\n";
      $body .= lang('Subject').': '.$t_subject."\n";
      $body .= lang('Assigned To').': '.$t_assigned_name."\n";
      $body .= lang('Priority').': '.$GLOBALS['phpgw']->db->f('ticket_priority')."\n";
      $body .= lang('Group').': '.$group_name."\n";
      $body .= lang('Opened By').': '.$t_owner_name."\n";
      $body .= lang('Due Date').': '.$GLOBALS['phpgw']->db->f('ticket_due')."\n\n";
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
      $body .= stripslashes(strip_tags($GLOBALS['phpgw']->db->f('ticket_detail')))."\n.\n";

      $body .= sprintf ("\nURL: ".$GLOBALS['egw_info']['server']['webserver_url']."/tts/viewticket_details.php?ticket_id=%d\n",
	      $ticket_id);


      // do we need to email all the users in the group assigned to this ticket?
      // or is it assigned to None (in this case, we notify always)  -- MSc 050824
      if ($GLOBALS['phpgw']->config->config_data['groupnotification'] || $t_assigned == 0)
      {
        // select group recipients
        $members  = $GLOBALS['phpgw']->accounts->member($group_id);
      }

      // do we need to email the owner of this ticket?
      if ($GLOBALS['phpgw']->config->config_data['ownernotification'] && $owner_notify)
      {
        // add owner to recipients
		$members[] = array('account_id' => $GLOBALS['phpgw']->db->f('ticket_owner'));
      }

      // do we need to email the user who is assigned to this ticket?
      if ($GLOBALS['phpgw']->config->config_data['assignednotification'] && $t_assigned > 0)
      {
        // add assigned to recipients
        $members[] = array('account_id' => $t_assigned);
      }

      $toarray = Array();
      $i=0;
      for ($i=0;$i<count($members);$i++)
      {
        if ($members[$i])
        {
          $account_email = $GLOBALS['phpgw']->accounts->id2name($members[$i]['account_id'], 'account_email');
          if ($account_email)
          {
              $toarray[] = $account_email;
          }
        }
      }
      if(count($toarray) > 1)
      {
        $to = implode(',',$toarray);
      }
      else
      {
        $to = current($toarray);
      }

      $body=html_deactivate_urls($body);
      if ($members)
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
?>
