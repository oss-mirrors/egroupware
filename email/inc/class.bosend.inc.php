<?php
	/**************************************************************************\
	* phpGroupWare - email BO Class	for Folder Actions and List Display		*
	* http://www.phpgroupware.org							*
	* Written by Angelo (Angles) Puglisi <angles@phpgroupware.org>		*
	* --------------------------------------------							*
	*  This program is free software; you can redistribute it and/or modify it		*
	*  under the terms of the GNU General Public License as published by the	*
	*  Free Software Foundation; either version 2 of the License, or (at your		*
	*  option) any later version.								*
	\**************************************************************************/

	/* $Id$ */

	class bosend
	{
		var $public_functions = array(
			'get_langed_labels'	=> True,
			'send'		=> True
		);
		var $nextmatchs;
		var $mail_out = array();
		//var $debug = True;
		var $debug = False;
		var $xi;
		var $xml_functions = array();
		
		var $soap_functions = array(
			'get_langed_labels' => array(
				'in'  => array('int'),
				'out' => array('array')
			),
			'send' => array(
				'in'  => array('array'),
				'out' => array('int')
			)
		);
		
		function bosend()
		{
			
		}
		
		//  -------  This will be called just before leaving this page, to clear / unset variables / objects -----------
		function send_message_cleanup()
		{
			//echo 'send_message cleanup';
			$GLOBALS['phpgw']->msg->end_request();
			// note: the next lines can be removed since php takes care of memory management
			$this->mail_out = '';
			unset($this->mail_out);
			$GLOBALS['phpgw']->mail_send = '';
			unset($GLOBALS['phpgw']->mail_send);
		}
		
		function send()
		{
			// attempt (or not) to reuse an existing mail_msg object, i.e. if one ALREADY exists before entering
			//$attempt_reuse = True;
			$attempt_reuse = False;
			
			if ($this->debug) { echo 'ENTERING: email.bosend.send'.'<br>'; }
			if ($this->debug) { echo 'email.bosend.send: local var attempt_reuse=['.serialize($attempt_reuse).'] ; reuse_feed_args[] dump<pre>'; print_r($reuse_feed_args); echo '</pre>'; }
			// create class objects
			//$this->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			
			if (is_object($GLOBALS['phpgw']->msg))
			{
				if ($this->debug) { echo 'email.bosend.send: is_object test: $GLOBALS[phpgw]->msg is already set, do not create again<br>'; }
			}
			else
			{
				if ($this->debug) { echo 'email.bosend.send: $GLOBALS[phpgw]->msg is NOT set, creating mail_msg object<br>'; }
				$GLOBALS['phpgw']->msg = CreateObject("email.mail_msg");
			}
			
			// do we attempt to reuse the existing msg object?
			if ($attempt_reuse)
			{
				// no not create, we will reuse existing
				if ($this->debug) { echo 'email.bosend.send: reusing existing mail_msg login'.'<br>'; }
				// we need to feed the existing object some params begin_request uses to re-fill the msg->args[] data
				$args_array = Array();
				// any args passed in $args_array will override or replace any pre-existing arg value
				$args_array = $reuse_feed_args;
				// add this to keep the error checking code (below) happy
				$args_array['do_login'] = True;
			}
			else
			{
				if ($this->debug) { echo 'email.bosend.send: cannot or not trying to reusing existing'.'<br>'; }
				$args_array = Array();
				// should we log in or not
				$args_array['do_login'] = True;
			}
			
			// "start your engines"
			if ($this->debug == True) { echo 'email.bosend.send: call msg->begin_request with args array:<pre>'; print_r($args_array); echo '</pre>'; }
			$some_stream = $GLOBALS['phpgw']->msg->begin_request($args_array);
			// error if login failed
			if (($args_array['do_login'] == True)
			&& (!$some_stream))
			{
				$GLOBALS['phpgw']->msg->login_error($GLOBALS['PHP_SELF'].', send()');
			}
			
			// ---- BEGIN BO SEND LOGIC
			
			$not_set = $GLOBALS['phpgw']->msg->not_set;
			$msgball = $GLOBALS['phpgw']->msg->get_pref_value('msgball');
			
			//  -------  Init Array Structure For Outgoing Mail  -----------
			$this->mail_out = Array();
			$this->mail_out['to'] = Array();
			$this->mail_out['cc'] = Array();
			$this->mail_out['bcc'] = Array();
			$this->mail_out['mta_to'] = Array();
			$this->mail_out['mta_from'] = '<'.trim($GLOBALS['phpgw']->msg->get_pref_value('address')).'>';
			$this->mail_out['mta_elho_domain'] = '';
			$this->mail_out['message_id'] = $GLOBALS['phpgw']->msg->make_message_id();
			$this->mail_out['boundary'] = $GLOBALS['phpgw']->msg->make_boundary();
			$this->mail_out['date'] = '';
			$this->mail_out['main_headers'] = Array();
			$this->mail_out['body'] = Array();
			$this->mail_out['is_multipart'] = False;
			$this->mail_out['num_attachments'] = 0;
			$this->mail_out['whitespace'] = chr(9);
			$this->mail_out['is_forward'] = False;
			$this->mail_out['fwd_proc'] = '';
			$this->mail_out['from'] = array();
			$this->mail_out['sender'] = '';
			$this->mail_out['charset'] = '';
			$this->mail_out['msgtype'] = '';
			
			//  -------  Start Filling Array Structure For Outgoing Mail  -----------
			
			// -----  X-PHPGW flag (msgtype)  ------
			/*!
			@var msgtype
			@abstract obsoleted way phpgw apps used to inter-operate
			@discussion NOTE: this is a vestigal way for phpgw apps to inter-operate, 
			I *think* this is being obsoleted via n-tiering and xml-rpc / soap methods.
			RARELY USED, maybe NEVER used, most email code for this is now commented out
			"back in the day..." the "x-phpgw" header was specified by a phpgw app *other* than the email app
			which was used to include special phpgw related handling instructions in the message which 
			to the message intentended to be noticed and processed by the phpgw email app when the 
			user open the mail for viewing, at which time the phpgw email app would issue the 
			special handling instructions contained in the "x-phpgw" header.
			even before n-tiering of the phpgw apps and api begain, I (angles) considered this a possible
			area of abuse and I commented out the code in the email app that would notice, process and issue
			those instructions.
			*/
			if (($GLOBALS['phpgw']->msg->get_isset_arg('msgtype'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('msgtype') != ''))
			{
				// convert script GPC args into useful mail_out structure information
				$this->mail_out['msgtype'] = $GLOBALS['phpgw']->msg->get_arg_value('msgtype');
				// after this, ONLY USE $this->mail_out structure for this
			}
			
			// -----  CHARSET  -----
			/*!
			@property charset
			@abstract not user specified, not a user var, not an argument, not a paramater.
			@discussion charset could take up a lot of notes here, suffice to say that email began life as a
			US-ASCII thing and still us-ascii chars are strictly required for some headers, while other headers
			and the body have various alternative ways to deal with other charsets, ways that are well documented
			in email and other RFC's and other literature. In the rare event that the phpgw api is unable 
			to provide us with a charset value, we use the RFC specified default value of "US-ASCII"
			*/
			if (lang('charset') != '')
			{
				$this->mail_out['charset'] = lang('charset');
			}
			else
			{
				// RFC default charset, if none is specified, is US-ASCII
				$this->mail_out['charset'] = 'US-ASCII';
			}
			
			// -----  FROM  -----
			/*!
			@var from
			@abstract the mail's author, OPTIONAL, usually no need to specify this as an arg passed to the script.
			@discussion Generally this var does not need to be specified. When the mail is being sent from the 
			user's default email account (or mail on behalf of the user, like automated email notifications),
			we generate the "from" header for the user, hence no custom "from" arg is necessary.
			This is the most common scenario, in which case we generate the "from" value as follows:
			(1) the user's "fullname" (a.k.a. the "personal" part of the address) is always picked up 
			from the phpgw api's value that contains the users name, and 
			(2) the user's email address is either (2a) the default value from the phpgw api which was 
			passed into the user's preferences because the user specified no custom email address preference, or
			(2b) the user specified a custom email address in the email preferences in which case the aformentioned
			phpgw api default email address is not used in the user's preferences array, this user supplied
			value is used instead.
			Providing a "from" arg is usually for extra email accounts and/or alternative email profiles, 
			where the user wants other than the "from" info otherwise defaultly associated with this email acccount.
			NOTE: from != sender
			from is who the mail came from assuming that person is also the mail's author.
			this is by far the most common scenario, "from" and "author" are usually one in the same
			(see below for info on when to *also* use "sender" - VERY rare)
			*/
			if (($GLOBALS['phpgw']->msg->get_isset_arg('from'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('from') != ''))
			{
				$from_assembled = $GLOBALS['phpgw']->msg->get_arg_value('sender');
			}
			else
			{
				$from_name = $GLOBALS['phpgw_info']['user']['fullname'];
				$from_address = $GLOBALS['phpgw']->msg->get_pref_value('address');
				$from_assembled = '"'.$from_name.'" <'.$from_address.'>';
			}
			// this array gets filled with functiuon "make_rfc_addy_array", but it will have only 1 numbered array, $this->mail_out['from'][0]
			// note that sending it through make_rfc_addy_array will ensure correct formatting of non us-ascii chars (if any) in the use's fullname
			$this->mail_out['from'] = $GLOBALS['phpgw']->msg->make_rfc_addy_array($from_assembled);
			
			// -----  SENDER  -----
			/*!
			@var sender
			@abstract OPTIONAL only used in the rare event that the person sending the email 
			is NOT that email's author.
			@discussion RFC2822 makes clear that the Sender header is ONLY used if some one 
			NOT the author (ex. the author's secretary) is sending the author's email.
			RFC2822 considers that "From" = the author and the "Sender" = the person who clicked the
			send button. Generally they are one in the same and generally the Sender header (and hence this 
			"sender" var) is NOT needed, not used, not included in the email's headers.
			*/
			if (($GLOBALS['phpgw']->msg->get_isset_arg('sender'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('sender') != ''))
			{
				// clean data of magic_quotes escaping (if any)
				$this->mail_out['sender'] = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('sender'));
				// convert general address string into structured data array of addresses, each has properties [plain] and [personal]
				// this array gets filled with functiuon "make_rfc_addy_array", but it will have only 1 numbered array, $this->mail_out['sender'][0]
				$sender_array = $GLOBALS['phpgw']->msg->make_rfc_addy_array($this->mail_out['sender']);
				// realistically sender array should have no more than one member (can there really be more than 1 sender?)
				if (count($sender_array) > 0)
				{
					$this->mail_out['sender'] = $GLOBALS['phpgw']->msg->addy_array_to_str($sender_array);
					// bogus data check
					if (trim($this->mail_out['sender']) == '')
					{
						$this->mail_out['sender'] = '';
					}
				}
				else
				{
					$this->mail_out['sender'] = '';
				}
				// after this, ONLY USE $this->mail_out[] structure for this
				// it will either be blank string OR a string which should be 1 email address
			}
			// -----  DATE  -----
			/*!
			@property date
			@abstract not user specified, not a user var, not an argument, not a paramater.
			@discussion According to RFC2822 the Date header *should* be the local time with the correct 
			timezone offset relative to GMT, however this is problematic on many Linux boxen, and
			in general I have found that reliably extracting this data from the host OS can be tricky, 
			so instead we use a fallback value which is simply GMT time, which is allowed under RFC2822 
			but not preferred.
			FUTURE: figure out a host independant way of getting the correct rfc time and TZ offset
			*/
			$this->mail_out['date'] = gmdate('D, d M Y H:i:s').' +0000';
			
			// -----  MYMACHINE - The MTA HELO/ELHO DOMAIN ARG  -----
			/*!
			@property elho SMTP handshake domain value
			@abstract not user specified, not a user var, not an argument, not a paramater.
			@discussion when class.msg_send conducts the handshake with the SMTP server, this 
			will be the required domain value that we supply to the SMTP server. Phpgw is considered 
			the client to the SMTP server. 
			RFC2821 sect 4.1.1.1 specifies this value is almost always the Fully Qualified Domain Name 
			of the SMTP client machine, but rarely, when said client machine has dynamic FQDN or no reverse 
			mapping is available, this value *should* be "address leteral" (see sect 4.1.3).
			Refer to the documentation for BIND for further reading on reverse lookup issues.
			*/
			$this->mail_out['mta_elho_mymachine'] = trim($GLOBALS['phpgw_info']['server']['hostname']);
			
			// ----  Forwarding Detection  -----
			if (($GLOBALS['phpgw']->msg->get_isset_arg('action'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('action') == 'forward'))
			{
				// fill mail_out[] structure information
				$this->mail_out['is_forward'] = True;
				// after this, ONLY USE $this->mail_out[] structure for this
			}
			if (($GLOBALS['phpgw']->msg->get_isset_arg('fwd_proc'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('fwd_proc') != ''))
			{
				// convert script GPC args into useful mail_out[] structure information
				$this->mail_out['fwd_proc'] = $GLOBALS['phpgw']->msg->get_arg_value('fwd_proc');
				// after this, ONLY USE $this->mail_out[] structure for this
			}
			
			// ----  Attachment Detection  -----
			$upload_dir = $GLOBALS['phpgw']->msg->att_files_dir;
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
				if ($num_expected > 0)
				{
					$this->mail_out['num_attachments'] = $num_expected;
					$this->mail_out['is_multipart'] = True;
				}
			}
			
			//  ------  get rid of the escape \ that magic_quotes (if enabled) HTTP POST will add, " becomes \" and  '  becomes  \'
			// convert script GPC args into useful mail_out structure information
			$to = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('to'));
			$cc = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('cc'));
			$body = $GLOBALS['phpgw']->msg->stripslashes_gpc(trim($GLOBALS['phpgw']->msg->get_arg_value('body')));
			$subject = $GLOBALS['phpgw']->msg->stripslashes_gpc($GLOBALS['phpgw']->msg->get_arg_value('subject'));
			// after this,  do NOT use ->msg->get_arg_value() for these anymore
			
			// since arg "body" *may* be huge (and is now in local var $body), lets clear it now
			$GLOBALS['phpgw']->msg->set_arg_value('body', '');
			
			// ----  DE-code HTML SpecialChars in the body   -----
			// THIS NEEDS TO BE CHANGED WHEN MULTIPLE PART FORWARDS ARE ENABLED
			// BECAUSE WE CAN ONLY ALTER THE 1ST PART, I.E. THE PART THE USER JUST TYPED IN
			/*  // I think the email needs to be sent out as if it were PLAIN text (at least the part we are handling here)
			// i.e. with NO ENCODED HTML ENTITIES, so use > instead of $rt; and " instead of &quot; . etc...
			// it's up to the endusers MUA to handle any htmlspecialchars, whether to encode them or leave as it, the MUA should decide  */
			$body = $GLOBALS['phpgw']->msg->htmlspecialchars_decode($body);
			
			// ----  Add Email Sig to Body   -----
			if (($GLOBALS['phpgw']->msg->get_isset_pref('email_sig'))
			&& ($GLOBALS['phpgw']->msg->get_pref_value('email_sig') != '')
			&& ($GLOBALS['phpgw']->msg->get_isset_arg('attach_sig'))
			&& ($GLOBALS['phpgw']->msg->get_arg_value('attach_sig') != '')
			// ONLY ADD SIG IF USER PUTS TEXT IN THE BODY
			//&& (strlen(trim($body)) > 3))
			&& ($this->mail_out['is_forward'] == False))
			{
				$user_sig = $GLOBALS['phpgw']->msg->get_pref_value('email_sig');
				// html_quotes_decode may be obsoleted someday:  workaround for a preferences database issue (<=pgpgw ver 0.9.13)
				$user_sig = $GLOBALS['phpgw']->msg->html_quotes_decode($user_sig);
				$body = $body ."\r\n" .'-- '."\r\n" .$user_sig ."\r\n";
			}
			
			// ----  Ensure To: and CC:  and BCC: are properly formatted   -----
			if ($to)
			{
				// mail_out[to] is an array of addresses, each has properties [plain] and [personal]
				$this->mail_out['to'] = $GLOBALS['phpgw']->msg->make_rfc_addy_array($to);
				// this will make a simple comma seperated string of the plain addresses (False sets the "include_personal" arg)
				$mta_to = $GLOBALS['phpgw']->msg->addy_array_to_str($this->mail_out['to'], False);
			}
			if ($cc)
			{
				$this->mail_out['cc'] = $GLOBALS['phpgw']->msg->make_rfc_addy_array($cc);
				$mta_to .= ',' .$GLOBALS['phpgw']->msg->addy_array_to_str($this->mail_out['cc'], False);
			}
			// now make mta_to an array because we will loop through it in class mail_send
			$this->mail_out['mta_to'] = explode(',', $mta_to);
			
			// RFC2821 - RCPT TO: args (email addresses) should be enclosed in brackets
			// when we constructed the $this->mail_out['mta_to'] var, we set "include_personal" to False, so this array has only "plain" email addys
			for ($i=0; $i<count($this->mail_out['mta_to']); $i++)
			{
				if (!preg_match('/^<.*>$/', $this->mail_out['mta_to'][$i]))
				{
					$this->mail_out['mta_to'][$i] = '<'.$this->mail_out['mta_to'][$i].'>';
				}
			}
			
			/*
			// ===== DEBUG =====	
			echo '<br>';
			//$dubug_info = $to;
			//$dubug_info = ereg_replace("\r\n.", "CRLF_WSP", $dubug_info);
			//$dubug_info = ereg_replace("\r\n", "CRLF", $dubug_info);
			//$dubug_info = ereg_replace(" ", "SP", $dubug_info);
			//$dubug_info = $GLOBALS['phpgw']->msg->htmlspecialchars_encode($dubug_info);
			//echo serialize($dubug_info);
			
			//$to = $GLOBALS['phpgw']->msg->addy_array_to_str($to, True);
			//echo 'to including personal: '.$GLOBALS['phpgw']->msg->htmlspecialchars_encode($to).'<br>';
			
			echo '<br> var dump mail_out <br>';
			var_dump($this->mail_out);
			//echo '<br> var dump cc <br>';
			//var_dump($cc);
			echo '<br>';
			
			$GLOBALS['phpgw']->common->phpgw_footer();
			exit;
			// ===== DEBUG ===== 
			*/
			
			// ----  Send The Email  ==  via CLASS MAIL SEND 2822  == -----
			// USE CLASS MAIL SEND 2822
			$GLOBALS['phpgw']->mail_send = CreateObject("email.mail_send");
			$GLOBALS['phpgw']->mail_send->send_init();
			// do we need to retain a copy of the sent message for the "Sent" folder?
			if($GLOBALS['phpgw']->msg->get_isset_pref('use_sent_folder'))
			{
				$GLOBALS['phpgw']->mail_send->retain_copy = True;
			}
			
			// initialize structure for 1st part
			$body_part_num = 0;
			$this->mail_out['body'][$body_part_num]['mime_headers'] = Array();
			$this->mail_out['body'][$body_part_num]['mime_body'] = Array();
			
			// -----  ADD 1st PART's MIME HEADERS  (if necessary)  -------
			if (($this->mail_out['is_multipart'] == True)
			|| ($this->mail_out['is_forward'] == True))
			{
				// --- Add Mime Part Header to the First Body Part
				// this part _should_ be text
				$m_line = 0;
				$this->mail_out['body'][0]['mime_headers'][$m_line] = 'This is a multipart message in MIME format';
				$m_line++;
				$this->mail_out['body'][0]['mime_headers'][$m_line] = "\r\n";
				$m_line++;
				$this->mail_out['body'][0]['mime_headers'][$m_line] = '--' .$this->mail_out['boundary'];
				$m_line++;
				$this->mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Type: text/plain; charset="'.$this->mail_out['charset'].'"';
				$m_line++;
				if ($this->mail_out['msgtype'] != '')
				{
					// "folded header" opens with a "whitespace"
					$this->mail_out['body'][0]['mime_headers'][$m_line] = '  phpgw-type="'.$this->mail_out['msgtype'].'"';
					$m_line++;
				}
				// 7 BIT vs. 8 BIT Content-Transfer-Encoding
				// 7 bit means that no chars > 127 can be in the email, or else MTA's will get confused
				// if you really want to enforce 7 bit you should qprint encode the email body
				// however, if you are forwarding via MIME encapsulation then I do not believe it's cool to alter 
				// the original message's content by qprinting it if it was not already qprinted
				// in which case you should send it 8 bit instead.
				// ALSO, the top most level encoding can not be less restrictive than any embedded part's encoding
				// 7bit is more restrictive than 8 bit
				// OPTIONS:
				// 1) send it out with no encoding header - against RFC's but the MTA will probably put it there for you
				// 2) do a scan for chars > 127, if so, send 8 bit and hope the MTA can handle 8 bit
				// 3) scan for > 127 then qprint what we can (not embeded) then send out 7 bit
				// 4) listen to the initial string from the MTA indicating if it can handle MIME8BIT
				// 5) just send it out 8 bit and hope for the best (for now do this)
				//$this->mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: 7bit';
				//$m_line++;
				$this->mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: 8bit';
				$m_line++;
				$this->mail_out['body'][0]['mime_headers'][$m_line] = 'Content-Disposition: inline';
				$m_line++;
			}
			
			// -----  MAIN BODY PART (1st Part)  ------
			// Explode Body into Array of strings
			$body = $GLOBALS['phpgw']->msg->normalize_crlf($body);
			$this->mail_out['body'][$body_part_num]['mime_body'] = explode ("\r\n",$body);
			//$this->mail_out['body'][$body_part_num]['mime_body'] = $GLOBALS['phpgw']->msg->explode_linebreaks(trim($body));
			// for no real reason, I add a CRLF to the end of the body
			//$this->mail_out['body'][$body_part_num]['mime_body'][count($this->mail_out['body'][$body_part_num]['mime_body'])] = " \r\n";
			// since var $body *may* be huge, lets clear it now
			$body = '';
			
			// -----  FORWARD HANDLING  ------
			// Sanity Check - we can not "pushdown" a multipart/mixed original mail, it must be encaposulated
			if (($this->mail_out['is_forward'] == True)
			&& ($this->mail_out['fwd_proc'] == 'pushdown'))
			{
				$msg_headers = $GLOBALS['phpgw']->msg->phpgw_header('');
				$msg_struct = $GLOBALS['phpgw']->msg->phpgw_fetchstructure('');
				
				$this->mail_out['fwd_info'] = $GLOBALS['phpgw']->msg->pgw_msg_struct($msg_struct, $not_set, '1', 1, 1, 1);
				if (($this->mail_out['fwd_info']['type'] == 'multipart')
				|| ($this->mail_out['fwd_info']['subtype'] == 'mixed'))
				{
					$this->mail_out['fwd_proc'] = 'encapsulate';
				}
			}
			
			// Add Forwarded Mail as An Additional Encapsulated "message/rfc822" MIME Part
			if (($this->mail_out['is_forward'] == True)
			&& ($this->mail_out['fwd_proc'] == 'pushdown'))
			{
				// -----   INCOMPLETE CODE HERE  --------
				$body_part_num++;
				$this->mail_out['body'][$body_part_num]['mime_headers'] = Array();
				$this->mail_out['body'][$body_part_num]['mime_body'] = Array();
				
				// ----  General Information about The Original Message  -----
				$msg_headers = $GLOBALS['phpgw']->msg->phpgw_header('');
				$msg_struct = $GLOBALS['phpgw']->msg->phpgw_fetchstructure('');
				
				// use the "pgw_msg_struct" function to get the orig message main header info
				$this->mail_out['fwd_info'] = $GLOBALS['phpgw']->msg->pgw_msg_struct($msg_struct, $not_set, '1', 1, 1, 1);
				// add some more info
				$this->mail_out['fwd_info']['from'] = $GLOBALS['phpgw']->msg->make_rfc2822_address($msg_headers->from[0]);
				$this->mail_out['fwd_info']['date'] = $GLOBALS['phpgw']->common->show_date($msg_headers->udate);
				$this->mail_out['fwd_info']['subject'] = $GLOBALS['phpgw']->msg->get_subject($msg_headers,'');
				
				// normalize data to rfc2046 defaults, in the event data is not provided
				if ($this->mail_out['fwd_info']['type'] == $not_set)
				{
					$this->mail_out['fwd_info']['type'] = 'text';
				}
				if ($this->mail_out['fwd_info']['subtype'] == $not_set)
				{
					$this->mail_out['fwd_info']['subtype'] = 'plain';
				}
				if ($this->mail_out['fwd_info']['disposition'] == $not_set)
				{
					$this->mail_out['fwd_info']['disposition'] = 'inline';
				}
				
				$this->mail_out['fwd_info']['boundary'] = $not_set;
				for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
				{
					//echo '<br>params['.$p.']: '.$part_nice['params'][$p]['attribute'].'='.$part_nice['params'][$p]['value'] .'<br>';
					if (($part_nice['params'][$p]['attribute'] == 'boundary') 
					  && ($part_nice['params'][$p]['value'] != $not_set))
					{
						$this->mail_out['fwd_info']['boundary'] = $part_nice['params'][$p]['value'];
						break;
					}
				}
				if ($this->mail_out['fwd_info']['boundary'] != $not_set)
				{
					// original email ALREADY HAS a boundary., so use it!
					$this->mail_out['boundary'] = $this->mail_out['fwd_info']['boundary'];
				}
				//echo '<br>part_nice[boundary] ' .$this->mail_out['fwd_info']['boundary'] .'<br>';
				//echo '<br>part_nice: <br>' .$GLOBALS['phpgw']->msg->htmlspecialchars_encode(serialize($this->mail_out)) .'<br>';
				
				// prepare the mime part headers
				// original body gets pushed down one part, i.e. was part 1, now is part 2
				$m_line = 0;
				$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = '--' .$this->mail_out['boundary'];
				$m_line++;
				$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Type: '.$this->mail_out['fwd_info']['type'].'/'.$this->mail_out['fwd_info']['subtype'].';';
				$m_line++;
				if ($this->mail_out['fwd_info']['encoding'] != 'other')
				{
					$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: '.$this->mail_out['fwd_info']['encoding'];
					$m_line++;			
				}
				for ($p = 0; $p < $part_nice['ex_num_param_pairs']; $p++)
				{
					//echo '<br>params['.$p.']: '.$part_nice['params'][$p]['attribute'].'='.$part_nice['params'][$p]['value'] .'<br>';
					if ($part_nice['params'][$p]['attribute'] != 'boundary') 
					{
						$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = '  '.$part_nice['params'][$p]['attribute'].'="'.$part_nice['params'][$p]['value'].'"';
						$m_line++;
					}
				}
				$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Disposition: '.$this->mail_out['fwd_info']['disposition'];
				$m_line++;
				
				// dump the original BODY (with out its headers) here
				$fwd_this = $GLOBALS['phpgw']->msg->phpgw_body();
				// Explode Body into Array of strings
				$this->mail_out['body'][$body_part_num]['mime_body'] = $GLOBALS['phpgw']->msg->explode_linebreaks(trim($fwd_this));
				$fwd_this = '';		
			}
			elseif (($this->mail_out['is_forward'] == True)
			&& ($this->mail_out['fwd_proc'] == 'encapsulate'))
			{
				// generate the message/rfc822 part that is the container for the forwarded mail
				$body_part_num++;
				$this->mail_out['body'][$body_part_num]['mime_headers'] = Array();
				$this->mail_out['body'][$body_part_num]['mime_body'] = Array();
				
				// mime headers define this as a message/rfc822 part
				// following RFC2046 recommendations
				$this->mail_out['body'][$body_part_num]['mime_headers'][0] = '--' .$this->mail_out['boundary'];
				$this->mail_out['body'][$body_part_num]['mime_headers'][1] = 'Content-Type: message/rfc822'.';';
				$this->mail_out['body'][$body_part_num]['mime_headers'][2] = 'Content-Disposition: inline';
				
				// DUMP the original message verbatim into this part's "body" - i.e. encapsulate the original mail
				$fwd_this['sub_header'] = trim($GLOBALS['phpgw']->msg->phpgw_fetchheader());
				$fwd_this['sub_header'] = $GLOBALS['phpgw']->msg->normalize_crlf($fwd_this['sub_header']);
				
				// CLENSE headers of offensive artifacts that can confuse dumb MUAs
				$fwd_this['sub_header'] = preg_replace("/^[>]{0,1}From\s.{1,}\r\n/i", "", $fwd_this['sub_header']);
				$fwd_this['sub_header'] = preg_replace("/Received:\s(.{1,}\r\n\s){0,6}.{1,}\r\n(?!\s)/m", "", $fwd_this['sub_header']);
				$fwd_this['sub_header'] = preg_replace("/.{0,3}Return-Path.*\r\n/m", "", $fwd_this['sub_header']);
				$fwd_this['sub_header'] = trim($fwd_this['sub_header']);
				
				// get the body
				$fwd_this['sub_body'] = trim($GLOBALS['phpgw']->msg->phpgw_body());
				//$fwd_this['sub_body'] = $GLOBALS['phpgw']->msg->normalize_crlf($fwd_this['sub_body']);
				
				// Make Sure ALL INLINE BOUNDARY strings actually have CRLF CRLF preceeding them
				// ---- not yet complete ----
				$char_quot = '"';
				preg_match("/boundary=[$char_quot]{0,1}.*[$char_quot]{0,1}\r\n/",$fwd_this['sub_header'],$fwd_this['matches']);
				if (stristr($fwd_this['matches'][0], 'boundary='))
				{
					$fwd_this['boundaries'] = trim($fwd_this['matches'][0]);
					$fwd_this['boundaries'] = str_replace('boundary=', '', $fwd_this['boundaries']);
					$fwd_this['boundaries'] = str_replace('"', '', $fwd_this['boundaries']);
					$this_boundary = $fwd_this['boundaries'];
					//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$this_boundary/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
					//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$this_boundary/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
					//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}".$this_boundary."[-]{0,2}/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
					//$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$this_boundary/m", "\r\n\r\n".'DASHDASH'.$this_boundary, $fwd_this['sub_body']);
					$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}".$this_boundary."/m", "\r\n\r\n".'--'.$this_boundary, $fwd_this['sub_body']);
					$dash_dash = '--';
					$fwd_this['sub_body'] = preg_replace("/(?<!(\r\n\r\n))[-]{2}$dash_dash$this_boundary$dash_dash/", "\r\n\r\n".'--'.$this_boundary.'--', $fwd_this['sub_body']);
					$fwd_this['sub_body'] = trim($fwd_this['sub_body']);
				}
				
				
				// assemble it and add the blank line that seperates the headers from the body
				$fwd_this['processed'] = $fwd_this['sub_header']."\r\n"."\r\n".$fwd_this['sub_body'];
				
				
				/*
				//echo 'fwd_this[sub_header]: <br><pre>'.$GLOBALS['phpgw']->msg->htmlspecialchars_encode($fwd_this['sub_header']).'</pre><br>';
				//echo 'fwd_this[matches]: <br><pre>'.$GLOBALS['phpgw']->msg->htmlspecialchars_encode(serialize($fwd_this['matches'])).'</pre><br>';
				//echo 'fwd_this[boundaries]: <br><pre>'.$GLOBALS['phpgw']->msg->htmlspecialchars_encode($fwd_this['boundaries']).'</pre><br>';
				//echo '=== var dump    fwd_this <br><pre>';
				//var_dump($fwd_this);
				//echo '</pre><br>';			
				echo 'fwd_this[processed]: <br><pre>'.$GLOBALS['phpgw']->msg->htmlspecialchars_encode($fwd_this['processed']).'</pre><br>';
				unset($fwd_this);
				exit;
				*/
				
				
				// Explode Body into Array of strings
				//$fwd_this['processed'] = $GLOBALS['phpgw']->msg->normalize_crlf($fwd_this['processed']);
				//$this->mail_out['body'][$body_part_num]['mime_body'] = explode("\r\n", $fwd_this['processed']);
				$this->mail_out['body'][$body_part_num]['mime_body'] = $GLOBALS['phpgw']->msg->explode_linebreaks(trim($fwd_this['processed']));
				unset($fwd_this);
			}
			
			/*
			// ===== DEBUG =====	
			echo '<br>';
			echo '<br>=== mail_out ===<br>';
			$dubug_info = serialize($this->mail_out);
			$dubug_info = $GLOBALS['phpgw']->msg->htmlspecialchars_encode($dubug_info);
			echo $dubug_info;
			echo '<br>';
				
			$GLOBALS['phpgw']->common->phpgw_footer();
			exit;
			// ===== DEBUG ===== 
			*/
			
			
			// ---  ATTACHMENTS -- Add each of them as an additional mime part ---
			if ($this->mail_out['num_attachments'] > 0)
			{
				@set_time_limit(0);
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

							$body_part_num++;
							$this->mail_out['body'][$body_part_num]['mime_headers'] = Array();
							$this->mail_out['body'][$body_part_num]['mime_body'] = Array();

							$m_line = 0;
							$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = '--' .$this->mail_out['boundary'];
							$m_line++;
							$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Type: '.$content_type.'; name="'.$content_name.'"';
							$m_line++;
							$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Transfer-Encoding: base64';
							$m_line++;
							$this->mail_out['body'][$body_part_num]['mime_headers'][$m_line] = 'Content-Disposition: attachment; filename="'.$content_name.'"';
							
							// get the file and base 64 encode it
							$fh = fopen($upload_dir.SEP.$file,'rb');
							// $rawfile = fread($fh,$size);
							$b64_part = chunk_split(base64_encode(fread($fh,$size)));
							$this->mail_out['body'][$body_part_num]['mime_body'] = explode("\r\n", $b64_part);
							$b64_part = '';
							fclose($fh);
							
							/*
							/ /  * * * * MOVE THIS INTO MAIL SEND 2822 PROC * * * * *
							// IF LAST PART - GIVE THE "FINAL" boundary
							if ($total_files >= $num_expected)
							{
								// attachments (parts) have their own boundary preceeding them (see below)
								// this is: "--"boundary
								// all boundary strings are have 2 dashes "--" added to their begining
								// and the FINAL boundary string (after all other parts) ALSO has 
								// 2 dashes "--" tacked on tho the end of it, very important !! 
								// the next available array number
								$m_line = count($this->mail_out['body'][$body_part_num]['mime_body']);
								$this->mail_out['body'][$body_part_num]['mime_body'][$m_line] = '--' .$this->mail_out['boundary'].'--';
							}
							//echo 'tot: '.$total_files .' expext: '.$num_expected; // for debugging
							*/
							
							// delete the temp file (the attachment)
							unlink($upload_dir.SEP.$file);
							// delete the other temp file (the .info file)
							unlink($upload_dir.SEP.$file.'.info');
						}
					}
				}
				// get rid of the temp dir we used for the above
				rmdir($upload_dir);
			}
			
			// --- MAIN HEADERS  -------
			$hdr_line = 0;
			$this->mail_out['main_headers'][$hdr_line] = 		'From: '.$GLOBALS['phpgw']->msg->addy_array_to_str($this->mail_out['from']);
			$hdr_line++;
			if ($this->mail_out['sender'] != '')
			{
				// rfc2822 - sender is only used if some one NOT the author (ex. the author's secretary) is sending the authors email
				// $this->mail_out['sender'] is initialized as an empty array in the begining of this file
				// then, it will be filled if the ->msg->args['sender'] was passed to the script,
				// where it would have been converted to the appropriate format and put in the $this->mail_out['sender'] array
				$this->mail_out['main_headers'][$hdr_line] = 	'Sender: '.$this->mail_out['sender'];
				$hdr_line++;
			}
			//$this->mail_out['main_headers'][$hdr_line] = 		'Reply-To: '.$GLOBALS['phpgw']->msg->addy_array_to_str($this->mail_out['from']);
			//$hdr_line++;
			$this->mail_out['main_headers'][$hdr_line] = 		'To: '.$GLOBALS['phpgw']->msg->addy_array_to_str($this->mail_out['to']);
			$hdr_line++;
			if (count($this->mail_out['cc']) > 0)
			{
				$this->mail_out['main_headers'][$hdr_line] = 	'Cc: '.$GLOBALS['phpgw']->msg->addy_array_to_str($this->mail_out['cc']);
				$hdr_line++;
			}
			$this->mail_out['main_headers'][$hdr_line] = 		'Subject: '.$subject;
			$hdr_line++;
			$this->mail_out['main_headers'][$hdr_line] = 		'Date: '.$this->mail_out['date'];
			$hdr_line++;
			$this->mail_out['main_headers'][$hdr_line] = 		'Message-ID: '.$this->mail_out['message_id'];
			$hdr_line++;
			// RFC2045 REQUIRES this header in even if no embedded mime parts are in the body
			// MTA's, MUA's *should* assume the following as default (RFC2045) if not included
			$this->mail_out['main_headers'][$hdr_line] = 		'MIME-Version: 1.0';
			$hdr_line++;

			if (($this->mail_out['is_multipart'] == True)
			|| ($this->mail_out['is_forward'] == True))
			{
				// THIS MAIL INCLUDES EMBEDED MIME PARTS
				$this->mail_out['main_headers'][$hdr_line] =	'Content-Type: multipart/mixed;';
				$hdr_line++;
				$this->mail_out['main_headers'][$hdr_line] =	$this->mail_out['whitespace'].'boundary="'.$this->mail_out['boundary'].'"';
				$hdr_line++;
			}
			else
			{
				// NO MIME SUBPARTS - SIMPLE 1 PART MAIL 
				// headers = mime part 0 and  body = mime part 1
				$this->mail_out['main_headers'][$hdr_line] =	'Content-Type: text/plain;';
				$hdr_line++;
				$this->mail_out['main_headers'][$hdr_line] =	$this->mail_out['whitespace'].'charset="'.$this->mail_out['charset'].'"';
				$hdr_line++;
				// RFC2045 - the next line is *assumed* as default 7bit if it is not included
				// FUTURE: Content-Transfer-Encoding:  Needs To Match What is In the Body, i.e. may be qprint
				//$this->mail_out['main_headers'][$hdr_line] =	'Content-Transfer-Encoding: 7bit';
				//$hdr_line++;
				/*
				@discussion: 7bit vs. 8bit encoding value in top level headers
				top level 7bit requires qprinting the body if the body has 8bit chars in it
				ISSUE 1: "it's unnecessary"
				nowdays, most all MTAs and IMAP/POP servers can handle 8bit
				by todays usage, 7bit is quite restrictive, when considering the variety of
				things that may be attached to or carried in a message (and growing)
				<begin digression>
				However, stuffing RFC822 email thru a X500 (?) gateway requires 7bit body,
				which we could do here, at the MUA level, and may possibly require other
				alterations of the message that occur at the gateway, some of which may actually drop
				portions of the message, indeed it's complicated, but rare in terms of total mail volume (?)
				<end digression>
				ISSUE 2: "risks violating RFCs and confusing MTAs"
				setting top level encoding to 7bit when the body actually has 8bit chars is "TOTALLY BAD"
				MTA's will be totally confused by that mis-match, and it violates RFCs
				**More Importantly** this is a coding and functionality issue involved in forwarding:
				in general, when you forward a message you should not alter that message
				if that forwarded message has 8bit chars, I don't think that can be altered
				even to quote-print that forwarded part (i.e. to convert it to 7bit) would be altering it
				I suppose you could base64 encode it, on the theory that it decodes exactly back into
				it's original form, but the practice of base64 encoding non-attachments (i.e. text parts)
				is EXTREMELY rare in my experience (Angles) and still problematic in coding for this.
				I suppose this assumes qprint is possible "lossy" in that the exact original may not be
				exactly the same as said pre-encoded forwarded part, and, after all, it's still altering the part.
				CONCLUSION: Set Top Level Header "Content-Transfer-Encoding" to "8bit"
				because it's easier to code for and less likely to violate RFCs.
				for now send out as 8bit and hope for the best.
				*/
				$this->mail_out['main_headers'][$hdr_line] =	'Content-Transfer-Encoding: 8bit';
				$hdr_line++;
			
				$this->mail_out['main_headers'][$hdr_line] =	'Content-Disposition: inline';
				$hdr_line++;
				// Content-Description: this is not really a "technical" header
				// it can be used to inform the person reading some summary info
				//$header .= 'Content-description: Mail message body'."\r\n";
			}
			
			// finish off the main headers
			if ($this->mail_out['msgtype'] != '')
			{
				$this->mail_out['main_headers'][$hdr_line] = 	'X-phpGW-Type: '.$this->mail_out['msgtype'];
				$hdr_line++;
			}
			$this->mail_out['main_headers'][$hdr_line] = 	'X-Mailer: phpGroupWare (http://www.phpgroupware.org) v '.$GLOBALS['phpgw_info']['server']['versions']['phpgwapi'];
			$hdr_line++;
			
			/*
			// ===== DEBUG =====	
			echo '<br>';
			echo '<br>=== mail_out ===<br>';
			$dubug_info = serialize($this->mail_out);
			$dubug_info = $GLOBALS['phpgw']->msg->htmlspecialchars_encode($dubug_info);
			echo $dubug_info;
			echo '<br>';
			// ===== DEBUG ===== 
			*/
			
			// ----  Send It   -----
			$returnccode = $GLOBALS['phpgw']->mail_send->smail_2822($this->mail_out);
			
			/*
			// ===== DEBUG =====	
			echo '<br>';
			echo 'retain_copy: '.serialize($GLOBALS['phpgw']->mail_send->retain_copy);
			echo '<br>=== POST SEND ===<br>';
			echo '<pre>'.$GLOBALS['phpgw']->msg->htmlspecialchars_encode($GLOBALS['phpgw']->mail_send->assembled_copy).'</pre>';
			echo '<br>';
			// ===== DEBUG ===== 
			*/
			
			
			//  -------  Put in "Sent" Folder, if Applicable  -------
			$skip_this = False;
			//$skip_this = True;
			
			if (($skip_this == False)
			&& ($returnccode)
			&& ($GLOBALS['phpgw']->msg->get_isset_pref('use_sent_folder')))
			{
				//echo 'ENTERING SENT FOLDER CODE';
				
				// note: what format should these folder name options (sent and trash) be held in
				// i.e. long or short name form, in the prefs database
				//$sent_folder_name = $GLOBALS['phpgw']->msg->get_folder_short($GLOBALS['phpgw']->msg->get_pref_value('sent_folder_name'));
				$sent_folder_name = $GLOBALS['phpgw']->msg->get_pref_value('sent_folder_name');
				
				// NOTE: should we use the existing mailbox stream or initiate a new one just for the append?
				// using a NEW stream *seems* faster, but not sure ???
				/*
				if ((!($GLOBALS['phpgw']->msg->get_isset_arg('mailsvr_stream')))
				|| ($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream') == ''))
				{
					$stream = $GLOBALS['phpgw']->dcom->login('INBOX');
					// note: "append" will CHECK  to make sure this folder exists, and try to create it if it does not
					// also note, make sure there is a \r\n CRLF empty last line sequence so Cyrus will be happy
					$GLOBALS['phpgw']->dcom->append($stream, $sent_folder_name, $GLOBALS['phpgw']->mail_send->assembled_copy."\r\n", "\\Seen");
					$GLOBALS['phpgw']->dcom->close($stream);
				}
				else
				{
					// note: "append" will CHECK  to make sure this folder exists, and try to create it if it does not
					// also note, make sure there is a \r\n CRLF empty last line sequence so Cyrus will be happy
					//$GLOBALS['phpgw']->dcom->append($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'),
					$GLOBALS['phpgw']->msg->phpgw_append($sent_folder_name,
								$GLOBALS['phpgw']->mail_send->assembled_copy."\r\n",
								"\\Seen");
					//echo 'used existing stream for trash folder';
				//}
				*/
				
				if ((($GLOBALS['phpgw']->msg->get_isset_arg('mailsvr_stream')))
				&& ($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream') != ''))
				{
					// note: "append" will CHECK  to make sure this folder exists, and try to create it if it does not
					// also note, make sure there is a \r\n CRLF empty last line sequence so Cyrus will be happy
					//$GLOBALS['phpgw']->dcom->append($GLOBALS['phpgw']->msg->get_arg_value('mailsvr_stream'),
					//echo 'using existing stream for sent folder append<br>';
					$success = $GLOBALS['phpgw']->msg->phpgw_append($sent_folder_name,
									$GLOBALS['phpgw']->mail_send->assembled_copy."\r\n",
									"\\Seen");
					//if ($success) { echo 'append to sent OK<br>'; }
					//else { echo 'append to sent FAILED<br>'; echo 'imap_last_error: '.imap_last_error().'<br>'; }
				}
				else
				{
					//echo 'NO STREAM available for sent folder append<br>';
				}
			
			}
			
			// ----  Redirect on Success, else show Error Report   -----
			// what folder to go back to (the one we came from)
			// Personally, I think people should go back to the INBOX after sending an email
			// HOWEVER, we will go back to the folder this message came from (if available)
			if (($GLOBALS['phpgw']->msg->get_isset_arg('["msgball"]["folder"]'))
			&& ($GLOBALS['phpgw']->msg->get_isset_arg('["msgball"]["acctnum"]')))
			{
				$fldball_candidate['folder'] = $GLOBALS['phpgw']->msg->get_arg_value('["msgball"]["folder"]');
				$fldball_candidate['acctnum'] = (int)$GLOBALS['phpgw']->msg->get_arg_value('["msgball"]["acctnum"]');
			}
			elseif (($GLOBALS['phpgw']->msg->get_isset_arg('["fldball"]["folder"]'))
			&& ($GLOBALS['phpgw']->msg->get_isset_arg('["fldball"]["acctnum"]')))
			{
				$fldball_candidate['folder'] = $GLOBALS['phpgw']->msg->get_arg_value('["fldball"]["folder"]');
				$fldball_candidate['acctnum'] = (int)$GLOBALS['phpgw']->msg->get_arg_value('["fldball"]["acctnum"]');
			}
			// did we get useful data
			if ( (isset($fldball_candidate))
			&& ($fldball_candidate['folder'] != '') )
			{
				$fldball_candidate['folder'] = $GLOBALS['phpgw']->msg->prep_folder_out($fldball_candidate['folder']);
			}
			else
			{
				$fldball_candidate['folder'] = $GLOBALS['phpgw']->msg->prep_folder_out('INBOX');
				$fldball_candidate['acctnum'] = (int)$GLOBALS['phpgw']->msg->get_acctnum();
			}
			$return_to_folder_href = $GLOBALS['phpgw']->link(
						'/index.php',
						'menuaction=email.uiindex.index'
						.'&fldball[folder]='.$fldball_candidate['folder']
						.'&fldball[acctnum]='.$fldball_candidate['acctnum']
						.'&sort='.$GLOBALS['phpgw']->msg->get_arg_value('sort')
						.'&order='.$GLOBALS['phpgw']->msg->get_arg_value('order')
						.'&start='.$GLOBALS['phpgw']->msg->get_arg_value('start'));
			
			if ($returnccode)
			{
				// Success
				if ($GLOBALS['phpgw']->mail_send->trace_flag > 0)
				{
					// for debugging
					echo '<html><body>'."\r\n";
					echo '<h2>Here is the communication from the MUA(phpgw) <--> MTA(smtp server) trace data dump</h2>'."\r\n";
					echo '<h3>trace data flag set to ['.(string)$GLOBALS['phpgw']->mail_send->trace_flag.']</h3>'."\r\n";
					echo '<pre>'."\r\n";
					print_r($GLOBALS['phpgw']->mail_send->trace_data);
					echo '</pre>'."\r\n";
					echo '<p>&nbsp;<br></p>'."\r\n";
					echo '<p>To go back to the msg list, click <a href="'.$return_to_folder_href.'">here</a></p><br>';
					echo '</body></html>';
					$this->send_message_cleanup();
				}
				else
				{
					// unset some vars (is this necessary?)
					$this->send_message_cleanup();
					// redirect the browser to the index page for the appropriate folder
					header('Location: '.$return_to_folder_href);
				}
			}
			else
			{
				// ERROR - mail NOT sent
				echo '<html><body>'."\r\n";
				echo '<h2>Your message could <b>not</b> be sent!</h2>'."\r\n";
				echo '<h3>The mail server returned:</h3>'."\r\n";
				echo '<pre>';
				print_r($GLOBALS['phpgw']->mail_send->err);
				echo '</pre>'."\r\n";
				echo '<p>To go back to the msg list, click <a href="'.$return_to_folder_href.'">here</a> </p>'."\r\n";
				echo '</body></html>';
				$this->send_message_cleanup();
			}
		}
	
	}
?>
