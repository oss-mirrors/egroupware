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

// ----  Add Email Sig to Body   -----
	if (($phpgw_info['user']['preferences']['email']['email_sig'])
	&& ($attach_sig))
	{
		//$body .= "\n-----\n".$phpgw_info['user']['preferences']['email']['email_sig'];
		$user_sig = $phpgw_info['user']['preferences']['email']['email_sig'];
		// obsoleted: ereg_replace should not be needed after pgpgw ver 0.9.13
		$user_sig = ereg_replace('&quot;', '"', $user_sig);
		$user_sig = ereg_replace('&#039;', '\'', $user_sig);
		$body .= "\n-----\n" .$user_sig;
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

// ----  Attachment Handling   -----
	$sep = $phpgw->common->filesystem_separator();
	$upload_dir = $phpgw_info['server']['temp_dir'].$sep.$phpgw_info['user']['sessionid'];

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
					$size = filesize($upload_dir.$sep.$file);

					$info_file = $upload_dir.$sep.$file.'.info';
					$file_info = file($info_file);
					$content_type = trim($file_info[0]);
					$content_name = trim($file_info[1]);
					
					// what boundry do we use?
					if ($total_files >= $num_expected)
					{
						// the "final" boundry (IS THIS TRUE?)
						$mess_boundary = '--Message-Boundary--';
					}
					else
					{
						/* // attachments have their own boundry preceeding them (see below)
						// do not add another one between attachments
						// or else (some/all) MUAs will not see the later attachments
						// (IS THIS TRUE?) */
						$mess_boundary = '';
					}
					//echo 'tot: '.$total_files .' expext: '.$num_expected; // for debugging

					set_magic_quotes_runtime(0); 
					$fh = fopen($upload_dir.$sep.$file,'rb');
					// $rawfile = fread($fh,$size);
					$encoded_attach = chunk_split(base64_encode(fread($fh,$size)));
					fclose($fh);
					set_magic_quotes_runtime(get_magic_quotes_gpc());

					$body .= "\n\n".'--Message-Boundary'."\n"
						. 'Content-type: '.$content_type.'; name="'.$content_name.'"'."\n"
						. 'Content-Transfer-Encoding: BASE64'."\n"
						. 'Content-disposition: attachment; filename="'.$content_name.'"'."\n\n"
						. $encoded_attach .$mess_boundary ."\n";
					unlink($upload_dir.$sep.$file);

					unlink($upload_dir.$sep.$file.'.info');
				}
			}
		}
		rmdir($upload_dir);
	}

// ----  Send The Email  -----
	$rc = $phpgw->send->msg('email', $to, $subject, stripslashes($body), '', $cc, $bcc);
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
