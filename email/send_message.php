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

	$upload_dir = $phpgw_info['server']['temp_dir'].SEP.$phpgw_info['user']['sessionid'];

	if (file_exists($upload_dir))
	{
		@set_time_limit(0);
		$dh = opendir($upload_dir);
		while ($file = readdir($dh))
		{
			if ($file != '.' && $file != '..')
			{
				if (! ereg("\.info",$file))
				{
					$total_files++;
					$size = filesize($upload_dir.SEP.$file);

					$fd = fopen ($upload_dir.SEP.$file.'.info','rb');
					while (!feof ($fd))
					{
						$file_info[] = chop(fgets($fd, 4096));
					}
					fclose ($fd);

					set_magic_quotes_runtime(0); 
					$fh = fopen($upload_dir.SEP.$file,'rb');
//					$rawfile = fread($fh,$size);
					$encoded_attach = chunk_split(base64_encode(fread($fh,$size)));
					fclose($fh);
					set_magic_quotes_runtime(get_magic_quotes_gpc());

					$body .= "\n\n".'--Message-Boundary'."\n"
						. 'Content-type: '.$file_info[0].'; name="'.$file_info[1].'"'."\n"
						. 'Content-Transfer-Encoding: BASE64'."\n"
						. 'Content-disposition: attachment; filename="'.$file_info[1].'"'."\n\n"
						. $encoded_attach.'--Message-Boundary--'."\n";
					unlink($upload_dir.SEP.$file);

					unlink($upload_dir.SEP.$file.'.info');
				}	// if ! .info
			}	// if ! . or ..
		} 		// while dirread
		rmdir($upload_dir);
	}		// if dir

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
