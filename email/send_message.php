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

	$phpgw_flags = Array(
		'currentapp'		=> 'email',
		'enable_network_class'	=> True,
		'enable_send_class'	=> True,
		'noheader'		=> True,
		'nonavbar'		=> True
	);
	
	$phpgw_info['flags'] = $phpgw_flags;
	include('../header.inc.php');

	/* get rid of the escape \ that magic_quotes (if enabled) HTTP POST will add, " becomes \" and  '  becomes  \'  */
	$body = $phpgw->msg->stripslashes_gpc($body);
	$subject = $phpgw->msg->stripslashes_gpc($subject);

// ----  DE-code HTML SpecialChars in the body   -----
	// THIS NEEDS TO BE CHANGED WHEN MULTIPLE PART FORWARDS ARE ENABLED
	// BECAUSE WE CAN ONLY ALTER THE 1ST PART, I.E. THE PART THE USER JUST TYPED IN
	/*  // I think the email needs to be sent out as if it were PLAIN text (at least the part we are handling here)
	// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $rt; and " instead of &quot; . etc...
	// it's up to the endusers MUA to handle any htmlspecialchars, whether to encode them or leave as it, the MUA should decide  */
	$body = $phpgw->msg->htmlspecialchars_decode($body);

// ----  Add Email Sig to Body   -----
	if (($phpgw_info['user']['preferences']['email']['email_sig'])
	&& ($attach_sig))
	{
		$user_sig = $phpgw_info['user']['preferences']['email']['email_sig'];
		// may be obsoleted someday:  workaround for a preferences database issue (<=pgpgw ver 0.9.13)
		$user_sig = $phpgw->msg->html_quotes_decode($user_sig);
		$body = $body ."\n-----\n" .$user_sig;
	}

// ----  Prepare Body for RFC821 Compliance  -----
	/* // thanks to: Squirrelmail <Luke Ehresman> http://www.squirrelmail.org
	// In order to remove the problem of users not able to create
	// messages with "." on a blank line, RFC821 has made provision  in section 4.5.2 (Transparency). */
	$body = ereg_replace("\n\.", "\n\.\.", $body);
	$body = ereg_replace("^\.", "\.\.", $body);

	// this is to catch all plain \n instances and replace them with \r\n.  
	$body = ereg_replace("\r\n", "\n", $body);
	$body = ereg_replace("\n", "\r\n", $body);

// ----  Ensure To: and CC:  and BCC: are comma seperated   -----
	$to = $phpgw->msg->rfc_comma_sep($to);
	$cc = $phpgw->msg->rfc_comma_sep($cc);
	$bcc = $phpgw->msg->rfc_comma_sep($bcc);

// ----  Attachment Handling   -----
	//$sep = $phpgw->common->filesystem_separator();
	// SEP has been adopted as a global always available variable
	$upload_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];

	if (file_exists($upload_dir))
	{
		@set_time_limit(0);
		// how many attachments do we need to process?
		$dh = opendir($upload_dir);
		$num_expected = 0;
		while ($file = readdir($dh))
		{
			if (($file != '.')
			&& ($file != '..')
			&& (ereg("\.info",$file)))
			{
				$num_expected++;
			}
		}
		closedir($dh);

		// process (encode) attachments and add to the email body
		$total_files = 0;
		$dh = opendir($upload_dir);
		while ($file = readdir($dh))
		{
			if (($file != '.')
			&& ($file != '..'))
			{
				if (! ereg("\.info",$file))
				{
					$total_files++;
					$size = filesize($upload_dir.SEP.$file);

					$info_file = $upload_dir.SEP.$file.'.info';
					$file_info = file($info_file);
					$content_type = trim($file_info[0]);
					$content_name = trim($file_info[1]);
					
					// what boundry do we use?
					if ($total_files >= $num_expected)
					{
						// the "final" boundry
						$mess_boundary = '--Message-Boundary--';
					}
					else
					{
						/* // attachments have their own boundry preceeding them (see below)
						// do not add another one between attachments
						// ( i.e. this particular code loop should not put 2 boundry strings, like it was doing, inbetween each part )
						// or else MUAs will not see the later attachments 
						// all boundry strings are have 2 dashes "--" added to their begining
						// and the FINAL boundry string (after all other parts) ALSO has 
						// 2 dashes "--" tacked on tho the end of it, very important !! */
						$mess_boundary = '';
					}
					//echo 'tot: '.$total_files .' expext: '.$num_expected; // for debugging

					//set_magic_quotes_runtime(0);   MOVED to email/inc/functions.inc.php
					$fh = fopen($upload_dir.SEP.$file,'rb');
					// $rawfile = fread($fh,$size);
					$encoded_attach = chunk_split(base64_encode(fread($fh,$size)));
					fclose($fh);
					//set_magic_quotes_runtime(get_magic_quotes_gpc()); // LEAVE IT OFF

					$body .= "\n\n".'--Message-Boundary'."\n"
						. 'Content-type: '.$content_type.'; name="'.$content_name.'"'."\n"
						. 'Content-Transfer-Encoding: BASE64'."\n"
						. 'Content-disposition: attachment; filename="'.$content_name.'"'."\n\n"
						. $encoded_attach .$mess_boundary ."\n";
					unlink($upload_dir.SEP.$file);

					unlink($upload_dir.SEP.$file.'.info');
				}
			}
		}
		rmdir($upload_dir);
	}

// ----  Send The Email  -----
	//$rc = $phpgw->send->msg('email', $to, $subject, stripslashes($body), '', $cc, $bcc);
	// should not need stripslashes because we stripped it above
	$rc = $phpgw->send->msg('email', $to, $subject, $body, '', $cc, $bcc);
	// BUT I am NOT SURE!
	if ($rc)
	{
		//header('Location: '.$phpgw->link('index.php','cd=13&folder='.urlencode($return)));
		$return = ereg_replace ("^\r\n", '', $return);
		header('Location: '.$phpgw->link('/email/index.php','folder='.urlencode($return)));
	}
	else
	{
		echo 'Your message could <B>not</B> be sent!<BR>'."\n"
    		. 'The mail server returned:<BR>'
			. "err_code: '".$phpgw->send->err['code']."';<BR>"
			. "err_msg: '".htmlspecialchars($phpgw->send->err['msg'])."';<BR>\n"
			. "err_desc: '".$phpgw->err['desc']."'.<P>\n"
			. 'To go back to the msg list, click <a href="'.$phpgw->link('/email/index.php','cd=13&folder='.urlencode($return)).'">here</a>';
	}
	$phpgw->common->phpgw_footer();
?>
