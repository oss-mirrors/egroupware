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

	$num_expected = 0;
	$total_files = 0;

	$upload_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];

	if (file_exists($upload_dir))
	{
		@set_time_limit(0);
		$dh = opendir($upload_dir);
		// how many attachments do we have?
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

		// if there are attachments then we need to add the sig to the body BEFORE the attachments
		if (($num_expected > 0)
		&& ($phpgw_info['user']['preferences']['email']['email_sig'])
		&& ($attach_sig))
		{
			//$body .= "\n-----\n".$phpgw_info['user']['preferences']['email']['email_sig'];
			$user_sig = $phpgw_info['user']['preferences']['email']['email_sig'];
			// obsoleted: ereg_replace should not be needed after 0.9.13
			$user_sig = ereg_replace('&quot;', '"', $user_sig);
			$user_sig = ereg_replace('&#039;', '\'', $user_sig);
			$body .= "\n-----\n" .$user_sig;
		}

		$dh = opendir($upload_dir);
		// encode attachments and add to the email
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
					
					// what boundry do we use
					if ($total_files >= $num_expected)
					{
						$mess_boundary = '--Message-Boundary--';
					}
					else
					{
						$mess_boundary = '';
					}
					//echo 'tot: '.$total_files .' expext: '.$num_expected;

					set_magic_quotes_runtime(0); 
					$fh = fopen($upload_dir.SEP.$file,'rb');
//					$rawfile = fread($fh,$size);
					$encoded_attach = chunk_split(base64_encode(fread($fh,$size)));
					fclose($fh);
					set_magic_quotes_runtime(get_magic_quotes_gpc());

					$body .= "\n\n".'--Message-Boundary'."\n"
						. 'Content-type: '.$content_type.'; name="'.$content_name.'"'."\n"
						. 'Content-Transfer-Encoding: BASE64'."\n"
						. 'Content-disposition: attachment; filename="'.$content_name.'"'."\n\n"
						//. $encoded_attach."$message_boundary"."\n";
						. $encoded_attach .$mess_boundary ."\n";
					unlink($upload_dir.SEP.$file);

					unlink($upload_dir.SEP.$file.'.info');
				}	// if ! .info
			}	// if ! . or ..
		} 		// while dirread
		rmdir($upload_dir);
	}		// if dir

	// if there are NO attachments then add the sig to the body here
	if (($num_expected == 0)
	&& ($phpgw_info['user']['preferences']['email']['email_sig'])
	&& ($attach_sig))
	{
		//$body .= "\n-----\n".$phpgw_info['user']['preferences']['email']['email_sig'];
		$user_sig = $phpgw_info['user']['preferences']['email']['email_sig'];
		// obsoleted: ereg_replace should not be needed after 0.9.13
		$user_sig = ereg_replace('&quot;', '"', $user_sig);
		$user_sig = ereg_replace('&#039;', '\'', $user_sig);
		$body .= "\n-----\n" .$user_sig;
	}

	$rc = $phpgw->send->msg('email', $to, $subject, stripslashes($body), '', $cc, $bcc);
	if ($rc)
	{
//		header('Location: '.$phpgw->link('index.php','cd=13&folder='.urlencode($return)));
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
