<?php
  /**************************************************************************\
  * phpGroupWare - E-Mail                                                    *
  * http://www.phpgroupware.org                                              *
  * Based on Aeromail by Mark Cushman <mark@cushman.net>                     *
  *          http://the.cushman.net/                                         *
  * --------------------------------------------                             *
  *  This program is free software; you can redistribute it and/or modify it *
  *  under the terms of the GNU General Public License as published by the   *
  *  Free Software Foundation; either version 2 of the License, or (at your  *
  *  option) any later version.                                              *
  \**************************************************************************/

  /* $Id$ */

	if(empty($folder)){ $folder="INBOX"; }

	Header("Cache-Control: no-cache");
	Header("Pragma: no-cache");
	Header("Expires: Sat, Jan 01 2000 01:01:01 GMT");
  
	$phpgw_info["flags"] = array(
		'currentapp' => 'email', 
		'enable_network_class' => True, 
		'enable_nextmatchs_class' => True);
	
	if (isset($newsmode) && $newsmode == "on")
	{
		$phpgw_info['flags']['newsmode'] = True;
	}
	
	include("../header.inc.php");

	@set_time_limit(0);
	
	$t = new Template($phpgw->common->get_tpl_dir('email'));
	$t->set_file(array(		
		'any_deleted' => 'any_deleted.tpl',
		'delmov_init' => 'delmov_init.tpl',
		'email_index' => 'email_index.tpl',
	));
	
	// ---- lang var for checkbox javascript  -----
	$t->set_var('select_msg',lang('Please select a message first'));

	// ---- report on number of messages deleted (if any)  -----
	if ($td)
	{
		if ($td == 1) 
		{
			$num_deleted = lang("1 message has been deleted",$td);
		} else {
			$num_deleted = lang("x messages have been deleted",$td);
		}
		// template only outputs if msgs were deleted, otherwise skipped
		$t->set_var('num_deleted',$num_deleted);
		$t->parse('report_deleted','any_deleted',True);
	}
	else
	{
		// nothing deleted, so template gets blank string
		$t->set_var('report_deleted',' ');
	}
	
	// ----  Previous and Next arrows navigation  -----
	$nummsg = $phpgw->msg->num_msg($mailbox);
	if (! $start)
	{
		$start = 0;
	}
	
	$td_prev_arrows = $phpgw->nextmatchs->left('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					$start,$nummsg,
					'&sort=' .$sort .'&order=' .$order .'&folder=' .urlencode($folder));

	$td_next_arrows = $phpgw->nextmatchs->right('/'.$phpgw_info['flags']['currentapp'].'/index.php',
					$start,$nummsg,
					'&sort=' .$sort .'&order=' .$order .'&folder=' .urlencode($folder));

	$t->set_var('arrows_backcolor',$phpgw_info['theme']['bg_color']);
	$t->set_var('prev_arrows',$td_prev_arrows);
	$t->set_var('next_arrows',$td_next_arrows);

	// ----  Folder Stats,  SwitchTo Folder Listbox,  and Folder Button  -----
	if ($sort == "ASC") {
		$oursort = 0;
	} else {
		$oursort = 1;
	}

	if (!isset($order))
	{
		// Only do this on first visit to the app, where order should depend on prefs date order
		$order = 0;
		if ($phpgw_info["user"]["preferences"]["email"]["default_sorting"] == "new_old") 
		{
			$oursort = 1;
		} else {
			$oursort = 0;
		}
	}

	$mailbox_info = $phpgw->msg->mailboxmsginfo($mailbox);

	if ($folder != "INBOX")
	{
		$t_folder_s = $phpgw->msg->construct_folder_str($folder);
	} else {
		$t_folder_s = "INBOX";
	}
	
	if ($phpgw_info['user']['preferences']['email']['mail_server_type']=='imaps')
	{
 		/* IMAP over SSL */
		$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . "/ssl/novalidate-cert:993}$t_folder_s", SA_UNSEEN);
	} else {
		/* No SSL, normal connection */
		$mailbox_status = $phpgw->msg->status($mailbox,"{" . $phpgw_info["user"]["preferences"]["email"]["mail_server"] . ":". $phpgw_info["server"]["mail_port"] ."}$t_folder_s",SA_UNSEEN);
	}

	if ($nummsg > 0) 
	{
		$msg_array = array();
		// Note: sorting on email is on address, not displayed name per php imap_sort
		//echo "<br>SORT GOT: column '$order', '$oursort'.";
		$msg_array = $phpgw->msg->sort($mailbox, $order, $oursort);
		$stats_new = $mailbox_status->unseen;
		$ksize = round(10*($mailbox_info->Size/1024))/10;
	} else {
		$stats_new = '-';
		$ksize = '-';
	}

	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" 
	  || $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps" 
	  || $phpgw_info["flags"]["newsmode"])
	{
		$folder_listbox = '<select name="folder" onChange="document.switchbox.submit()">'
				. '<option value="INBOX">' . lang('switch current folder to') . ':' .'</option>'
				. list_folders($mailbox,$folder,False)
				. '</select>';
	} else {
		$folder_listbox = '&nbsp';
	}
	
	if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" 
	  || $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps" )
	{
		$folder_button = '<input type="button" value="' . lang("folder") . '" onClick="'
				. 'window.location=\'' . $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/folder.php',
				'folder=' .urlencode($folder)) . '\'">';
	} else {
		$folder_button = '&nbsp';
	}
		
	$t->set_var('switchbox_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/index.php'));
	$t->set_var('stats_backcolor',$phpgw_info['theme']['em_folder']);
	$t->set_var('stats_font',$phpgw_info['theme']['font']);
	$t->set_var('stats_color',$phpgw_info['theme']['em_folder_text']);
	$t->set_var('stats_folder',$folder);
	$t->set_var('stats_saved',$nummsg);
	$t->set_var('stats_new',$stats_new);
	$t->set_var('stats_size',$ksize);	
	$t->set_var('folder_listbox',$folder_listbox);
	$t->set_var('folder_button',$folder_button);

	// ----  Form delmov Intialization  -----
	// ----  FUTURE:  will be moved inside the table and occur only 1st loop through (FIXME)  -----
	$t->set_var('delmov_action',$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php'));
	$t->set_var('current_folder',$folder);
	$t->parse('form_delmov_init','delmov_init',True);
	
	// ----  Message List Table Headers  -----
	  /*
	     Sorting defs:
	     SORTDATE:  0
	     SORTFROM:  2
	     SORTSUBJECT: 3
	     SORTSIZE:  6
	  */
	if ($newsmode == "on")
	{
		$sizesort = lang("lines");
	} else {
		$sizesort = lang("size");
	}
	
	$t->set_var('hdr_backcolor',$phpgw_info["theme"]["th_bg"]);
	$t->set_var('hdr_font',$phpgw_info['theme']['font']);
	$t->set_var('hdr_subject',$phpgw->nextmatchs->show_sort_order($sort,"3",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("subject"),"&folder=".urlencode($folder)) );
	$t->set_var('hdr_from',$phpgw->nextmatchs->show_sort_order($sort,"2",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("from"),"&folder=".urlencode($folder)) );
	$t->set_var('hdr_date',$phpgw->nextmatchs->show_sort_order($sort,"0",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',lang("date"),"&folder=".urlencode($folder)) );
	$t->set_var('hdr_size',$phpgw->nextmatchs->show_sort_order($sort,"6",$order,'/'.$phpgw_info['flags']['currentapp'].'/index.php',$sizesort) );

	$t->pparse('out','email_index');

	// STOPPED HERE - templatization not yet complete

        if ($nummsg == 0) {
          if (!$mailbox) {
	   echo "<tr><td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" colspan=\"6\" align=\"center\">"
	      . lang("Could not open this mailbox")."</td></tr>";
	  } else {
           echo "<tr><td bgcolor=\"" . $phpgw_info["theme"]["row_on"] . "\" colspan=\"6\" align=\"center\">"
              . lang("this folder is empty")."</td></tr>";
	  }
        }

        if ($nummsg < $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
           $totaltodisplay = $nummsg;
        } else if (($nummsg - $start) > $phpgw_info["user"]["preferences"]["common"]["maxmatchs"]) {
           $totaltodisplay = $start + $phpgw_info["user"]["preferences"]["common"]["maxmatchs"];
        } else {
           $totaltodisplay = $nummsg;
        }

        for ($i=$start; $i < $totaltodisplay; $i++) {

           $struct = $phpgw->msg->fetchstructure($mailbox, $msg_array[$i]);
           $attach = "&nbsp;";

           for ($j = 0; $j< (count($struct->parts) - 1); $j++) {
              if (!$struct->parts[$j]) {
                 $part = $struct;
              } else {
                 $part = $struct->parts[$j];          }

              $att_name = get_att_name($part);
              if ($att_name != "Unknown") {
                 $attach = "&nbsp;<font face=\"".$phpgw_info["theme"]["font"]
                         . "\" size=\"1\"><div align=\"right\">"
                         . "<img src=\"" . $phpgw_info["server"]["images_dir"] 
                         . "/attach.gif\" alt=\"file\"></div></font>";
              }
           }

           $msg = $phpgw->msg->header($mailbox, $msg_array[$i]);

           $subject = !$msg->Subject ? "[".lang("no subject")."]" : $msg->Subject;

	   if (isset($phpgw_info["flags"]["newsmode"]) && $phpgw_info["flags"]["newsmode"]) {
	     $size = $msg->Size;
	   } else {
             $ksize = round(10*($msg->Size/1024))/10;
             $size = $msg->Size > 1024 ? "$ksize k" : $msg->Size;
	  }

           // Whats up with this ??
           $bg = (($i + 1)/2 == floor(($i + 1)/2)) ? $phpgw_info["theme"]["row_off"] : $phpgw_info["theme"]["row_on"];
                        
           echo  '<tr><td bgcolor="'.$bg.'" align="center">'
              . '<input type="checkbox" name="msglist[]" value="'.$msg_array[$i].'"></td>' ."\n";
           if (($msg->Unseen == "U") || ($msg->Recent == "N"))
              echo  '<td bgcolor="'.$bg.'" width="1%" align="center"><font color="FF0000">'
                 . '*</font>&nbsp;'.$attach.'</td>';
           else
              echo '<td bgcolor="'.$bg.'" width="1%">&nbsp;'.$attach.'</td>';

           echo  '<td bgcolor="'.$bg.'"><font size="2" face="'.$phpgw_info['theme']['font'].'">'
              . '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php','folder='.urlencode($folder).'&msgnum='.$msg_array[$i]).'">'
              . decode_header_string($subject) . '</a></font></td>' ."\n"
              . '<td bgcolor="'.$bg.'"><font size="2" face="'.$phpgw_info["theme"]["font"].'">';

           if ($msg->reply_to[0]) {
             $reply   = $msg->reply_to[0];
           } else {
             $reply   = $msg->from[0];
           }
           $replyto = $reply->mailbox . "@" . $reply->host;
           
           $from = $msg->from[0];
           $personal = !$from->personal ? "$from->mailbox@$from->host" : $from->personal;
           if ($personal == "@")
              $personal = $replyto;
          if ($phpgw_info["user"]["preferences"]["email"]["show_addresses"] == "from" && ($personal != "$from->mailbox@$from->host"))
               $display_address->from = "($from->mailbox@$from->host)";
          elseif ($phpgw_info["user"]["preferences"]["email"]["show_addresses"] == "replyto" && ($personal != $replyto))
               $display_address->from = "($replyto)";
          else
               $display_address->from = "";

           echo "<a href=\"" . $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',"folder="
              . urlencode($folder) . "&to=" . urlencode($replyto)) . "\">"
              . decode_header_string($personal) . "</a> $display_address->from";

           echo '</font></td>' ."\n"
              . '<td bgcolor="'.$bg.'"><font size="2" face="'.$phpgw_info["theme"]["font"].'">';

           echo $phpgw->common->show_date($msg->udate);

           echo '</font></td><td bgcolor="'.$bg.'"><font size="2" face="'.$phpgw_info["theme"]["font"].'">'.$size
              // . '</td></tr></font></td></tr>' ."\n\n";
	      . '</font></td></tr>' ."\n\n";
        }
?>
<tr>
  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" align="center">
   <a href="javascript:check_all()">
    <img src="<?php echo $phpgw_info["server"]["app_images"]; ?>/check.gif" border="0" height="16" width="21">
   </a>
  </td>

  <td bgcolor="<?php echo $phpgw_info["theme"]["th_bg"] ?>" colspan="5">

        <table width="100%" border="0" cellpadding="0" cellspacing="0">
         <tr>
          <td>
            <input type="button" value="<?php echo lang("delete"); ?>" onClick="do_action('delall')">
            <a href="<?php echo $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php',"folder=".urlencode($folder)); ?>"><?php echo lang("compose"); ?></a>
          </td>
          <td align="right">
           <?php
             if ($phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imap" || $phpgw_info["user"]["preferences"]["email"]["mail_server_type"] == "imaps") {

                echo '<select name="tofolder" onChange="do_action(\'move\')">'
                   . '<option>' . lang("move selected messages into") . ':';
                echo list_folders($mailbox);
		echo '</select>';
            
             }
             $phpgw->msg->close($mailbox);
             ?>
          </td>
         </tr>
        </table>

  </td>
</tr>
</form></table>

<br> 
<table border="0" align="center" width="95%">
 <tr>
  <td align="left"><font color="#ff0000">*</font>&nbsp;<?php echo lang("New message"); ?></td>
 </tr>
</table>
<?php $phpgw->common->phpgw_footer(); ?>
