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

// This will eventually be written using templates.

	Header('Cache-Control: no-cache');
	Header('Pragma: no-cache');
	Header('Expires: Sat, Jan 01 2000 01:01:01 GMT');

	$phpgw_flags = Array(
		'currentapp'					=>	'email',
		'enable_network_class'		=>	True,
		'enable_nextmatchs_class'	=>	True,
	);

	if (isset($newsmode) && $newsmode == 'on')
	{
		$phpgw_flags['newsmode'] = True;
	}

	$phpgw_info["flags"] = $phpgw_flags;
	include('../header.inc.php');

	$application = '';
	$msgtype = $phpgw->msg->get_flag($mailbox,$msgnum,'X-phpGW-Type');
	if (!empty($msgtype))
	{
		$msg_type = explode(';',$msgtype);
		$application = substr($msg_type[0],1,strlen($msg_type[0])-2);
		echo '<center><h1>THIS IS A phpGroupWare-'.strtoupper($application).' EMAIL</h1><hr></center>'."\n";
//			.'In the future, this will process a specially formated email msg.<hr></center>';
	}

	#set_time_limit(0);

	$msg = $phpgw->msg->header($mailbox, $msgnum);
	$struct = $phpgw->msg->fetchstructure($mailbox, $msgnum);
	$totalmessages = $phpgw->msg->num_msg($mailbox);

	$subject = !$msg->Subject ? lang('no subject') : $msg->Subject;
	$subject = decode_header_string($subject);
	$from = $msg->from[0];

	$message_date = $phpgw->common->show_date($msg->udate);

	$personal = !isset($from->personal) || !$from->personal ? $from->mailbox.'@'.$from->host : $from->personal;

	if ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'no' && ($personal != $from->mailbox.'@'.$from->host))
	{
		$display_address->from = '('.$from->mailbox.'@'.$from->host.')';
	}

	if (!$folder)
	{
		$folder = 'INBOX';
	}
?>
<table cellpadding="1" cellspacing="1" width="95%" align="center">
<tr><td colspan="2" bgcolor="<?php echo $phpgw_info['theme']['em_folder']; ?>">

      <table border="0" cellpadding="0" cellspacing="1" width="100%">
       <tr>
         <td>
  	  <font size="3" face="<?php echo $phpgw_info['theme']['font'].'" color="'.$phpgw_info['theme']['em_folder_text']; ?>">
	   <a href="<?php echo $phpgw->link('/email/index.php','folder='.urlencode($folder)); ?>"><?php echo $folder; ?></a>
         </font>
        </td>

        <td align=right><font size="3" face="<?php echo $phpgw_info['theme']['font'].'" color="'.$phpgw_info['theme']['em_folder_text']; ?>">
         <a href="<?php echo $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=reply&folder='.urlencode($folder).'&msgnum='.$msgnum); ?>">
          <img src="<?php echo $phpgw_info['server']['app_images']; ?>/sm_reply.gif" height="19" width="26" alt="<?php echo lang('reply'); ?>"></a>
         <a href="<?php echo $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=replyall&folder='.urlencode($folder).'&msgnum='.$msgnum); ?>">
          <img src="<?php echo $phpgw_info['server']['app_images']; ?>/sm_reply_all.gif" height="19" width="26" alt="<?php echo lang('reply all'); ?>"></a>
         <a href="<?php echo $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','action=forward&folder='.urlencode($folder).'&msgnum='.$msgnum); ?>">
         <img src="<?php echo $phpgw_info['server']['app_images']; ?>/sm_forward.gif" height="19" width="26" alt="<?php echo lang('forward'); ?>"></a>
         <a href="<?php echo $phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/action.php','what=delete&folder='.urlencode($folder).'&msgnum='.$msgnum); ?>">
          <img src="<?php echo $phpgw_info['server']['app_images']; ?>/sm_delete.gif" height="19" width="26" alt="<?php echo lang('delete'); ?>"></a></font>
	</td>
        <td align="right">
<?php
	// Move this up top.
	$session_folder = 'folder='.urlencode($folder).'&msgnum=';

	$default_sorting = $phpgw_info['user']['preferences']['email']['default_sorting'];

	if ($msgnum != 1 || ($default_sorting == 'new_old' && $msgnum != $totalmeesages))
	{
		if ($default_sorting == 'new_old')
		{
			$pm = $msgnum + 1;
		}
		else
		{
			$pm = $msgnum - 1;
		}

		if ($default_sorting == 'new_old' && ($msgnum == $totalmessages && $msgnum != 1 || $totalmessages == 1))
		{
			echo '<img border="0" src="'.$phpgw_info['server']['images_dir'].'/left-grey.gif" alt="No Previous Message">';
		}
		else
		{
			echo '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',$session_folder.$pm).'">'
				. '<img border="0" src="'.$phpgw_info['server']['images_dir'].'/left.gif" alt="Previous Message"></a>';
		}
	}
	else
	{
		echo '<img border="0" src="'.$phpgw_info['server']['images_dir'].'/left-grey.gif" alt="No Previous Message">';
	}

	if ($msgnum < $totalmessages || ($default_sorting == 'new_old' && $msgnum != 1))
	{
		if ($default_sorting == 'new_old')
		{
			$nm = $msgnum - 1;
		}
		else
		{
			$nm = $msgnum + 1;
		}

		if ($default_sorting == 'new_old' && $msgnum == 1 && $totalmessages != $msgnum)
		{
			echo '<img border="0" src="'.$phpgw_info['server']['images_dir'].'/right-grey.gif" alt="No Next Message">';
		}
		else
		{
			echo '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/message.php',$session_folder.$nm).'">'
				. '<img border="0" src="'.$phpgw_info['server']['images_dir'].'/right.gif" alt="Next Message"></a>';
		}
	}
	else
	{
		echo '<img border="0" src="'.$phpgw_info['server']['images_dir'].'/right-grey.gif" alt="No Next Message">';
	}
?>
        </td>
       </tr>
      </table>

</td>
</tr>

<tr>
 <td bgcolor="<?php echo $phpgw_info['theme']['th_bg']; ?>" valign="top">
  <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
   <b><?php echo lang('from'); ?>:</b>
  </font> 
 </td> 
 <td bgcolor="<?php echo $phpgw_info['theme']['row_on']; ?>" width="570">
  
<?php 

	if ($msg->from)
	{
		echo '<font size="2" face="'.$phpgw_info['theme']['font'].'">'
			. '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder).'&to='.urlencode($from->mailbox.'@'.$from->host)).'">'.decode_header_string($personal).'</a>'.$display_address->from.'</font>';
		echo '<font size="2" face="'.$phpgw_info['theme']['font'].'"> <a href="'.$phpgw->link('/addressbook/add.php','add_email='.urlencode($from->mailbox.'@'.$from->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)).'">'
			. '<img src="'.$phpgw_info['server']['app_images'].'/sm_envelope.gif" width="10" height="8" alt="Add to address book" border="0" align="absmiddle"></a></font>';
	}
	else
	{
		echo lang('Undisclosed Sender')."\n";
	}
?>
  </font>
 </td>
</tr>

<tr>
 <td bgcolor="<?php echo $phpgw_info['theme']['th_bg']; ?>" valign="top">
  <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
   <b><?php echo lang('to'); ?>:</b>
  </font> 
 </td> 
 <td bgcolor="<?php echo $phpgw_info['theme']['row_on']; ?>" width="570">
  <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
<?php
	if ($msg->to)
	{
		for ($i = 0; $i < count($msg->to); $i++)
		{
			$topeople = $msg->to[$i];
			$personal = !isset($topeople->personal) || !$topeople->personal ? $topeople->mailbox.'@'.$topeople->host : $topeople->personal;
			$personal = decode_header_string($personal);
			if ($phpgw_info['user']['preferences']['email']['show_addresses'] != 'no' && ($personal != $topeople->mailbox.'@'.$topeople->host))
			{
				$display_address->to = '('.$topeople->mailbox.'@'.$topeople->host.')';
			}
       
			echo '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder).'&to='.$topeople->mailbox.'@'.$topeople->host).'">'.$personal.'</a> '.$display_address->to;

			echo '&nbsp;<a href="'.$phpgw->link('/addressbook/add.php','add_email='.urlencode($topeople->mailbox.'@'.$topeople->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING)).'">'
				. '<img src="'.$phpgw_info['server']['app_images'].'/sm_envelope.gif" height="8" width="10" alt="Add to address book" border="0" align="absmiddle"></a>';
			if($i + 1 < count($msg->to))
			{
				echo ', '; // throw a spacer comma in between addresses.
			}
//			echo "</td></tr>\n";
		}
	}
	else
	{
		echo lang('Undisclosed Recipients')."\n";
	}

	echo '</td></tr>';

	if (isset($msg->cc) && count($msg->cc) > 0)
	{
?>
   <tr>
    <td bgcolor="<?php echo $phpgw_info['theme']['th_bg']; ?>" valign="top">
     <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
      <b><?php echo lang("cc"); ?>:</b>
    </td>
    <td bgcolor="<?php echo $phpgw_info['theme']['row_on']; ?>" width="570">
     <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
<?php
		for ($i = 0; $i < count($msg->cc); $i++)
		{
			$ccpeople = $msg->cc[$i];
			$personal = !$ccpeople->personal ? $ccpeople->mailbox.'@'.$ccpeople->host : $ccpeople->personal;
			$personal = decode_header_string($personal);

			echo '<a href="'.$phpgw->link('/'.$phpgw_info['flags']['currentapp'].'/compose.php','folder='.urlencode($folder)
				. '&to='.urlencode($ccpeople->mailbox.'@'.$ccpeople->host)).'">'.$personal.'</a>';

			echo '&nbsp;<a href="'.$phpgw->link('/addressbook/add.php','add_email='.urlencode($topeople->mailbox.'@'.$topeople->host).'&name='.urlencode($personal).'&referer='.urlencode($PHP_SELF.'?'.$QUERY_STRING))
				. '"><img src="'.$phpgw_info['server']['app_images'].'/sm_envelope.gif" height="8" width="10" alt="Add to address book" border="0" align="absmiddle"></a>';
			if($i + 1 < count($msg->cc))
			{
				echo ', '; // throw a spacer comma in between addresses.
			}
		}
		echo '</td></tr>'."\n";
	}
?>

<tr>
  <td bgcolor="<?php echo $phpgw_info['theme']['th_bg']; ?>" valign="top">
    <font size=2 face="<?php echo $phpgw_info['theme']['font']; ?>">
      <b><?php echo lang('date'); ?>:</b>
    </font>
    </td>
    <td bgcolor="<?php echo $phpgw_info['theme']['row_on']; ?>" width="570">
     <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
     <?php echo $message_date; ?>
     </font>
  </td>
</tr>
<?php
	$flag = 0;
	$struct_count = (!isset($struct->parts) || !$struct->parts ? 1 : count($struct->parts));
	for ($z = 0; $z < $struct_count; $z++)
	{
		$part = !isset($struct->parts[$z]) || !$struct->parts[$z] ? $struct : $struct->parts[$z];
		$att_name = get_att_name($part);

		if ($att_name != 'Unknown')
		{
	 // if it has a name, it's an attachment
			$f_name[$flag] = attach_display($part, $z+1);
			$flag++;
		}
	}
	if ($flag != 0)
	{
		echo '<tr><td bgcolor="'.$phpgw_info['theme']['th_bg'].'" valign="top">';
		echo '<font size="2" face="'.$phpgw_info['theme']['font'].'"><b>';
		echo lang('files').':</b></td><td bgcolor="'.$phpgw_info['theme']['row_on'].'" width="570">';
		echo '<font size="2" face="'.$phpgw_info['theme']['font'].'">';
		echo implode(', ',$f_name);
		echo '</td></tr>';
	}
?>
 <tr>
  <td bgcolor="<?php echo $phpgw_info['theme']['th_bg'] ?>" valign=top>
   <font size="2" face="<?php echo $phpgw_info['theme']['font'] ?>">
    <b><?php echo lang('subject') ?>:</b>   </font>
  </td>  <td bgcolor="<?php echo $phpgw_info['theme']['row_on']; ?>" width="570">
   <font size="2" face="<?php echo $phpgw_info['theme']['font']; ?>">
    <?php echo $subject; ?>
   </font>
  </td>
 </tr>
</table>

<br><table border="0" cellpadding="1" cellspacing="1" width="95%" align="center">
<tr>
  <td align="center">

<?php
	$numparts = (!isset($struct->parts) || !$struct->parts ? 1 : count($struct->parts));
	echo '<!-- This message has '.$numparts.' part(s) -->'."\n";

	for ($i = 0; $i < $numparts; $i++)
	{
		$part = (!isset($struct->parts[$i]) || !$struct->parts[$i] ? $struct : $struct->parts[$i]);

		$att_name = get_att_name($part);
		if ($att_name == 'Unknown')
		{
			if (strtoupper(get_mime_type($part)) == 'MESSAGE')
			{
				inline_display($part, $i+1);
				echo "\n<p>";
			}
			else
			{
				inline_display($part, $i+1);
				echo "\n<p>";
			}
		}

		$mime_encoding = get_mime_encoding($part);
		if (($mime_encoding == 'base64') && ($part->subtype == 'JPEG' || $part->subtype == 'GIF' || $part->subtype == 'PJPEG'))
		{
			// we want to display images here, even though they are attachments.
			echo '<p>'.image_display($folder, $msgnum, $part, $i+1, $att_name)."<p>\n";
		}
	}
	echo '</td></tr>';
	if($application)
	{
		if(strstr($msgtype,'"; Id="'))
		{
			$msg_type = explode(';',$msgtype);
			$id_array = explode('=',$msg_type[2]);
			$calendar_id = intval(substr($id_array[1],1,strlen($id_array[1])-2));

			echo '<tr><td align="center">';
			$phpgw->common->hook_single('email',$application);
			echo '</td></tr>';
		}
	}
?>
</table>
<?php
	$phpgw->msg->close($mailbox); 
	$phpgw->common->phpgw_footer();
?>
