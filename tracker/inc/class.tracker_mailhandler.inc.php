<?php
/**
 * eGroupWare Tracker - Handle incoming mails
 *
 * This class handles incoming mails in the async services.
 * It is an addition for the eGW Tracker app by Ralf Becker 
 *
 * @link http://www.egroupware.org
 * @author Oscar van Eijk <oscar.van.eijk-AT-oveas.com>
 * @package tracker
 * @copyright (c) 2008 by Oscar van Eijk <oscar.van.eijk-AT-oveas.com>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

class tracker_mailhandler extends tracker_bo
{
	/*
	 * UID of the mailsender, 0 if not recognized
	 * 
	 * @var int
	 */
	var $mailSender;
	
	/**
	 * Subject line of the incoming mail
	 * 
	 * @var string
	 */
	var $mailSubject;
	
	/**
	 * Text from the mailbody (1st part)
	 * 
	 * @var string
	 */
	var $mailBody;
	
	/**
	 * Identification of the mailbox
	 * 
	 * @var string
	 */
	var $mailBox;
	
	/**
	 * List with all messages retrieved from the server
	 * 
	 * @var array
	 */
	var $msgList = array();

	/**
	 * Mailbox stream
	 * 
	 * @var int
	 */
	var $mbox;
	
	/**
	 * Ticket ID or 0 if not recognize
	 * 
	 * @var int
	 */
	var $ticketId;
	
	/**
	 * User ID currently executing. Used in case we execute in fallback
	 * 
	 * @var int
	 */
	var $originalUser;
	
	/**
	 * Supported mailservertypes, extracted from parent::mailservertypes
	 * 
	 * @var array
	 */
	var $serverTypes = array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// In case we run in fallback, make sure the original user gets restored
		$this->originalUser = $this->user;

		foreach($this->mailservertypes as $ind => $typ)
		{
			$this->serverTypes[] = $typ[0];
		}
		if (($this->mailBox = self::get_mailbox()) === false)
		{
			return false;
		}
	}

	/**
	 * Destructor, close the stream if not done before.
	 */
	function __destruct()
	{
		if($this->mbox)
		{
			@imap_close($this->mbox);
		}
	}

	/**
	 * Compose the mailbox identification
	 * 
	 * @return string mailbox identification as '{server[:port]/type}folder'
	 */
	function get_mailbox()
	{
		if (empty($this->mailhandling[0]['server']))
		{
			return false; // Or should we default to 'localhost'?
		}		

		$mBox = '{'.$this->mailhandling[0]['server'];	// Set the servername

		if(!empty($this->mailhandling[0]['serverport']))
		{
			// If set, add the portnumber
			$mBox .= (':'.$this->mailhandling[0]['serverport']);
		}
		// Add the Servertype 
		$mBox .= ('/'.$this->serverTypes[($this->mailhandling[0]['servertype'])]);

		// Close the server ID
		$mBox .= '}';

		// Add the default incoming folder or the one specified
		if(empty($this->mailhandling[0]['folder']))
		{
			$mBox .= 'INBOX';
		}
		else
		{
			$mBox .= $this->mailhandling[0]['folder'];
		}
		return $mBox;
	}

	/**
	 * Get all mails from the server. Invoked by the async timer
	 * 
	 * @return boolean true=run finished, false=an error occured
	 */
	function check_mail()
	{
		if (!($this->mbox = imap_open ($this->mailBox,
									$this->mailhandling[0]['username'],
									$this->mailhandling[0]['password'])))
		{
			return false; // Open mailbox failed, don't we wanna log this?
		}

		if (empty($this->mailhandling[0]['address']))
		{
			// Use sort here to ensure the format returned equals search
			$this->msgList = imap_sort ($this->mbox, SORTARRIVAL, 1);
		}
		else
		{
			$this->msgList = imap_search ($this->mbox, 'TO ' . $this->mailhandling[0]['address']);
		}

		if ($this->msgList)
		{
			$_cnt = count ($this->msgList);
			for ($_idx = 0; $_idx < $_cnt; $_idx++)
			{
				if (self::process_message($this->msgList[$_idx]) && $this->mailhandling[0]['delete_from_server'])
				{
					@imap_delete($this->mbox, $this->msgList[$_idx]);
				}
			}
		}
		// Expunge delete mails, if any
		@imap_expunge($this->mbox);

		// Close the stream
		@imap_close($this->mbox);

		// Restore original user (for fallback)
		$this->user = $this->originalUser;
	}

	/**
	 * Process a messages from the mailbox
	 * 
	 * @param int Message ID from the server
	 * @return boolean true=message successfully processed, false=message couldn't or shouldn't be processed
	 */
	function process_message ($mid)
	{
		$senderIdentified = false;
		$this->mailBody = null; // Clear previous message
		$msgHeader = imap_headerinfo ($this->mbox, $mid);


		if ($msgHeader->Deleted == 'D')
		{
			return false; // Already deleted
		}

		if ($msgHeader->Recent == 'R' ||		// Recent and seen or	
				($msgHeader->Recent == ' ' &&	// not recent but
				$msgHeader->Unseen == ' '))		// seen
		{
			return false;
		}
		
		// Try several headers to identify the sender
		$try_addr = array(
			0 => $msgHeader->from[0],
			1 => $msgHeader->sender[0],
			2 => $msgHeader->return_path[0],
			3 => $msgHeader->reply_to[0],
		);

		foreach ($try_addr as $id => $sender)
		{
			if (($extracted = self::extract_mailaddress ($sender->mailbox.'@'.$sender->host)) !== false)
			{
				$senderIdentified = self::search_user($extracted);
			}
			if ($senderIdentified === true)
			{
				break;
			}
		}

		// Handle unrecognized mails
		if (!$senderIdentified)
		{
			switch ($this->mailhandling[0]['unrecognized_mails'])
			{
				case 'ignore' :		// Do nothing
					return false;
					break;
				case 'delete' :		// Delete, whatever the overall delete setting is
					@imap_delete($this->mbox, $mid);
					return false;	// Prevent from a second delete attempt
					break;
				case 'forward' :	// Return the status of the forward attempt
					return(self::forward_message($mid));
					break;
				case 'default' :	// Save as default user; handled below
				default :			// Duh ??
					break;
			}
		}

		$this->mailSubject = $msgHeader->subject;
		$this->ticketId = self::get_ticketId($this->mailSubject);

		if ($this->ticketId == 0) // Create new ticket?
		{
			if (empty($this->mailhandling[0]['default_tracker']))
			{
				return false; // Not allowed
			}
			if (!$senderIdentified) // Unknown user
			{
				if (empty($this->mailhandling[0]['unrec_mail']))
				{
					return false; // Not allowed for unknown users
				} 
				$this->mailSender = $this->mailhandling[0]['unrec_mail']; // Ok, set default user
			}
		}

		// By the time we get here, we know this ticket will be updated or created
		$this->mailBody = imap_fetchbody ($this->mbox, $mid, "1");
		quoted_printable_decode($this->mailBody);

		if ($this->ticketId == 0)
		{
			$this->init();
			$this->user = $this->mailSender;
			$this->data['tr_summary'] = $this->mailSubject;
			$this->data['tr_tracker'] = $this->mailhandling[0]['default_tracker'];
//			** Required?? It's not in eTemplate yet...
//			$this->data['tr_cat'] = $this->mailhandling[0]['default_cat'];
//			$this->data['tr_version'] = $this->mailhandling[0]['default_version'];
			$this->data['tr_priority'] = 5;
			$this->data['tr_description'] = $this->mailBody;
		}
		else
		{
			$this->read($this->ticketId);
			if (!$senderIdentified)
			{
				switch ($this->mailhandling[0]['unrec_reply'])
				{
					case 0 :
						$this->user = $this->data['tr_creator'];
						break;
					case 0 :
						$this->user = 0;
						break;
					default :
						$this->user = 0;
						break;
				}
			}
			$this->data['reply_message'] = $this->mailBody;

		}
		$this->data['tr_status'] = 'o'; // If the ticket isn't new, (re)open it anyway
		return ($this->save() == 0);
	}

	/**
	 * Get an email address in plain format, no matter how the address was specified
	 * 
	 * @param string $addr a string (probably) containing an email address
	 */
	function extract_mailaddress($addr='')
	{
		if (empty($addr))
		{
			return false;
		}
		preg_match_all("/[a-zA-Z0-9_\-\.]+?@([a-zA-Z0-9_\-]+?\.)+?[a-zA-Z]{2,}/", $addr, $address);
		return ($address[0][0]);
	}

	/**
	 * Retrieve the user ID based on the mail address that was extracted from the mailheaders
	 * 
	 * @param string $mail_addr, the mail address.
	 */
	function search_user($mail_addr='')
	{
		$this->mailSender = null; // Make sure previous msg data is cleared

		$acc_search = array(
			'type' => 'accounts',
//			'app' => 'tracker', // Make this a config item?
			'query' => $mail_addr,
			'query_type' => 'email',
		);
		$account_info = $GLOBALS['egw']->accounts->search($acc_search);
		$match_cnt = $GLOBALS['egw']->accounts->total;

		if ($match_cnt != 1) {
			// No matches (0) or ambigious (>1)
			return false;
		}
		
		$first_match = array_shift($account_info); // shift, since the key is numeric, so [0] won't work 
		$this->mailSender = $first_match['account_id'];
		return true;
	}

	/**
	 * Try to extract a ticket number from a subject line
	 * 
	 * @param string the subjectline from the incoming message
	 * @return int ticket ID, or 0 of no ticket ID was recognized
	 */
	function get_ticketId($subj='')
	{
		if (empty($subj))
		{
			return 0; // Don't bother...
		}

		// The subject line is expected to be in the format:
		// [Re: |Fwd: |etc ]<Tracker name> #<id>: <Summary>
		preg_match_all("/(.*?)( #[0-9]+: )(.*?)$/",$subj, $tr_data);
		if (!$tr_data[2])
		{
			return 0; // 
		}

		preg_match_all("/[0-9]+/",$tr_data[2][0], $tr_id);
		$tracker_id = $tr_id[0][0];

		$trackerData = $this->search(array('tr_id' => $tracker_id),'tr_summary');
		if ($trackerData[0]['tr_summary'] != $tr_data[3][0])
		{
			return 0; // Summary doesn't match. Should this be ok?
		}
		return $tracker_id;
	}

	/**
	 * Forward a mail that was not recognized
	 * 
	 * @param int message ID from the server
	 * @return boolean status
	 */
	function forward_message($mid)
	{
		// This function is not yet implemented, so for now, return status false
		return false;
	}


	/**
	 * Check if exist and if not start or stop an async job to check incoming mails
	 *
	 * @param int $interval=1 >0=start, 0=stop
	 */
	static function set_async_job($interval=0)
	{
		$async =& new asyncservice();

		// Make sure an existing timer is cancelled
		$async->cancel_timer('tracker-check-mail');

		if ($interval > 0)
		{
			if ($interval == 60)
			{
				$async->set_timer(array('hour' => '*'),'tracker-check-mail','tracker.tracker_mailhandler.check_mail',null);
			}
			else
			{
				$async->set_timer(array('min' => "*/$interval"),'tracker-check-mail','tracker.tracker_mailhandler.check_mail',null);
			}
		}
	}
}