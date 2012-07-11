<?php
/**
 * Tracker - history and notifications
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @package tracker
 * @copyright (c) 2006-12 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * Tracker - tracking object for the tracker
 */
class tracker_tracking extends bo_tracking
{
	/**
	 * Application we are tracking (required!)
	 *
	 * @var string
	 */
	var $app = 'tracker';
	/**
	 * Name of the id-field, used as id in the history log (required!)
	 *
	 * @var string
	 */
	var $id_field = 'tr_id';
	/**
	 * Name of the field with the creator id, if the creator of an entry should be notified
	 *
	 * @var string
	 */
	var $creator_field = 'tr_creator';
	/**
	 * Name of the field with the id(s) of assinged users, if they should be notified
	 *
	 * @var string
	 */
	var $assigned_field = 'tr_assigned';
	/**
	 * Translate field-name to 2-char history status
	 *
	 * @var array
	 */
	var $field2history = array();
	/**
	 * Should the user (passed to the track method or current user if not passed) be used as sender or get_config('sender')
	 *
	 * @var boolean
	 */
	var $prefer_user_as_sender = false;
	/**
	 * Instance of the botracker class calling us
	 *
	 * @access private
	 * @var tracker_bo
	 */
	var $tracker;

	/**
	 * Constructor
	 *
	 * @param tracker_bo $botracker
	 * @return tracker_tracking
	 */
	function __construct(tracker_bo $botracker)
	{
		$this->tracker = $botracker;
		$this->field2history = $botracker->field2history;

		parent::__construct('tracker');	// adding custom fields for tracker
	}

	/**
	 * Tracks the changes in one entry $data, by comparing it with the last version in $old
	 *
	 * Overridden from parent to hide restricted comments
	 *
	 * @param array $data current entry
	 * @param array $old=null old/last state of the entry or null for a new entry
	 * @param int $user=null user who made the changes, default to current user
	 * @param boolean $deleted=null can be set to true to let the tracking know the item got deleted or undeleted
	 * @param array $changed_fields=null changed fields from ealier call to $this->changed_fields($data,$old), to not compute it again
	 * @param boolean $skip_notification=false do NOT send any notification
	 * @return int|boolean false on error, integer number of changes logged or true for new entries ($old == null)
	 */
	public function track(array $data,array $old=null,$user=null,$deleted=null,array $changed_fields=null,$skip_notification=false)
	{
		$this->user = !is_null($user) ? $user : $GLOBALS['egw_info']['user']['account_id'];

		$changes = true;

		// Hide restricted comments from reply count
		foreach((array)$data['replies'] as $key => $reply)
		{
			if($reply['reply_visible'] != 0)
			{
				$data['num_replies']--;
			}
		}
		// If someone made a restricted comment, hide that from change tracking (notification & history)
		$old['num_replies'] = $data['num_replies'] - (!$data['reply_message'] || $data['reply_visible'] != 0 ? 0 : 1);
		if ($old && $this->field2history)
		{
			$changes = $this->save_history($data,$old,$deleted,$changed_fields);
		}

		// do not run do_notifications if we have no changes, unless there was a restricted comment just made
		if (($changes || ($data['reply_visible'] != 0)) && !$skip_notification && !$this->do_notifications($data,$old,$deleted,$changes))
		{
			$changes = false;
		}
		return $changes;
	}

	/**
	 * Send an autoreply to the ticket creator or replier by the mailhandler
	 *
	 * @param array $data current entry
	 * @param array $autoreply values for:
	 *			'reply_text' => Texline to add to the mail message
	 *			'reply_to' => UserID or email address
	 * @param array $old=null old/last state of the entry or null for a new entry
	 */
	function autoreply($data,$autoreply,$old=null)
	{
		if (is_integer($autoreply['reply_to'])) // Mail from a known user
		{
			if ($this->notify_current_user)
			{
				return; // Already notified while saving
			}
			else
			{
				$this->notify_current_user = true; // Ensure send_notification() doesn't fail this check
			}
			$email = $GLOBALS['egw']->accounts->id2name($this->user,'account_email');
		}
		else
		{
			$email = $autoreply['reply_to']; // mail from an unknown user (set here, so we need to send a notification)
		}
		if ($autoreply['reply_text'])
		{
			$data['reply_text'] = $autoreply['reply_text'];
		}
		// Send notification to the creator only; assignee, CC etc have been notified already
		$this->send_notification($data,$old,$email,$data[$this->creator_field]);
	}

	/**
	 * Send notifications for changed entry
	 *
	 * Overridden to hide restricted comments.  Sends restricted first to all but creator, then unrestricted to creator
	 *
	 * @internal use only track($data,$old,$user)
	 * @param array $data current entry
	 * @param array $old=null old/last state of the entry or null for a new entry
	 * @param boolean $deleted=null can be set to true to let the tracking know the item got deleted or undelted
	 * @return boolean true on success, false on error (error messages are in $this->errors)
	 */
	public function do_notifications($data,$old,$deleted, $changes)
	{
		$success = True;
		$email_notified = array();

		// Send all to others
		$creator = $data[$this->creator_field];
		$creator_field = $this->creator_field;
		if(!($this->tracker->is_admin($data['tr_tracker'], $creator) || $this->tracker->is_technician($data['tr_tracker'], $creator)))
		{
			// Notify the creator with full info if they're an admin or technician
			$this->creator_field = null;
		}

		// Don't send CC
		$private = $data['tr_private'];
		$data['tr_private'] = true;

		// Send notification - $email_notified will be skipped
		$success = $success && parent::do_notifications($data, $old, $deleted, $email_notified);

		//error_log(__METHOD__.__LINE__." email notified with restricted comments:".array2string($email_notified));

		if(!$changes)
		{
			// Only thing that really changed was a restricted comment
			//error_log(__METHOD__.':'.__LINE__.' Stopping, no other changes');
			return $success;
		}

		// Edit messages
		foreach((array)$data['replies'] as $key => $reply)
		{
			if($reply['reply_visible'] != 0)
			{
				unset($data['replies'][$key]);
			}
		}

		// Send to creator (if not already notified) && CC
		if(!($this->tracker->is_admin($data['tr_tracker'], $creator) || $this->tracker->is_technician($data['tr_tracker'], $creator)))
		{
			$this->creator_field = $creator_field;
		}
		$data['tr_private'] = $private;
		$already_notified = $email_notified;
		$success = $success && parent::do_notifications($data, $old, $deleted, $email_notified);
		//error_log(__METHOD__.__LINE__." email notified, restricted comments removed:".array2string(array_diff($email_notified,$already_notified)));

		return $success;
	}

	/**
	 * Get a notification-config value
	 *
	 * @param string $what
	 * 	- 'copy' array of email addresses notifications should be copied too, can depend on $data
	 *  - 'lang' string lang code for copy mail
	 *  - 'sender' string send email address
	 * @param array $data current entry
	 * @param array $old=null old/last state of the entry or null for a new entry
	 * @return mixed
	 */
	function get_config($name,$data,$old=null)
	{
		$tracker = $data['tr_tracker'];

		$config = $this->tracker->notification[$tracker][$name] ? $this->tracker->notification[$tracker][$name] : $this->tracker->notification[0][$name];

		switch($name)
		{
			case 'copy':	// include the tr_cc addresses
				if ($data['tr_private']) return array();	// no copies for private entries
				$config = $config ? preg_split('/, ?/',$config) : array();
				if ($data['tr_cc'])
				{
					$config = array_merge($config,preg_split('/, ?/',$data['tr_cc']));
				}
				break;
		}
		//error_log(__METHOD__.__LINE__.' Name:'.$name.' -> '.array2string($config).' Data:'.array2string($data));
		return $config;
	}

	/**
	 * Get the subject for a given entry, reimplementation for get_subject in bo_tracking
	 *
	 * Default implementation uses the link-title
	 *
	 * @param array $data
	 * @param array $old
	 * @return string
	 */
	function get_subject($data,$old)
	{
		return $this->tracker->trackers[$data['tr_tracker']].' #'.$data['tr_id'].': '.$data['tr_summary'];
	}

	/**
	 * Get the body of the notification message
	 * If there is a custom notification message configured, that will be used.  Otherwise, the
	 * default message will be used.
	 *
	 * @param boolean $html_email
	 * @param array $data
	 * @param array $old
	 * @param boolean $integrate_link to have links embedded inside the body
	 * @param int|string $receiver numeric account_id or email address
	 * @return string
	 */
	function get_body($html_email,$data,$old,$integrate_link = true,$receiver=null)
	{
		$notification = $this->tracker->notification[$data['tr_tracker']];
		if(trim(strip_tags($notification['message'])) == '' || !$notification['use_custom'])
		{
			$notification['message'] = $this->tracker->notification[0]['message'];
		}
		if(trim(strip_tags($notification['signature'])) == '' || !$notification['use_signature'])
		{
			$notification['signature'] = $this->tracker->notification[0]['signature'];
		}
		if(!$notification['use_signature'] && !$this->tracker->notification[0]['use_signature']) $notification['signature'] = '';

		if((!$notification['use_custom'] && !$this->tracker->notification[0]['use_custom']) || !$notification['message'])
		{
			return parent::get_body($html_email,$data,$old,$integrate_link,$receiver).($html_email?"<br />\n":"\n").
				$notification['signature'];
		}

		$merge = new tracker_merge();
		$message = $merge->merge_string($notification['message'], array($data['tr_id']), $error, 'text/html');
		if(strpos($notification['message'], '{{signature}}') === False)
		{
			$message.=($html_email?"<br />\n":"\n").
				$notification['signature'];
		}
		if($error)
		{
			error_log($error);
			return parent::get_body($html_email,$data,$old,$integrate_link,$receiver)."\n".$notification['signature'];
		}
		return $html_email ? $message : strip_tags($message);
	}

	/**
	 * Get the modified / new message (1. line of mail body) for a given entry, can be reimplemented
	 *
	 * @param array $data
	 * @param array $old
	 * @return string
	 */
	function get_message($data,$old)
	{
		if (!$data['tr_modified'] || !$old)
		{
			return lang('New ticket submitted by %1 at %2',
				common::grab_owner_name($data['tr_creator']),
				$this->datetime($data['tr_created_servertime']));
		}
		return lang('Ticket modified by %1 at %2',
			$data['tr_modifier'] ? common::grab_owner_name($data['tr_modifier']) : lang('Tracker'),
			$this->datetime($data['tr_modified_servertime']));
	}

	/**
	 * Get the details of an entry
	 *
	 * @param array $data
	 * @param string $datetime_format of user to notify, eg. 'Y-m-d H:i'
	 * @param int $tz_offset_s offset in sec to be add to server-time to get the user-time of the user to notify
	 * @return array of details as array with values for keys 'label','value','type'
	 */
	function get_details($data)
	{
		static $cats,$versions,$statis,$priorities,$resolutions;
		if (!$cats)
		{
			$cats = $this->tracker->get_tracker_labels('cat',$data['tr_tracker']);
			$versions = $this->tracker->get_tracker_labels('version',$data['tr_tracker']);
			$statis = $this->tracker->get_tracker_stati($data['tr_tracker']);
			$priorities = $this->tracker->get_tracker_priorities($data['tr_tracker']);
			$resolutions = $this->tracker->get_tracker_labels('resolution',$data['tr_tracker']);
		}
		if ($data['tr_assigned'])
		{
			foreach($data['tr_assigned'] as $uid)
			{
				$assigned[] = common::grab_owner_name($uid);
			}
			$assigned = implode(', ',$assigned);
		}
		if ($data['reply_text'])
		{
			$details['reply_text'] = array(
				'value' => $data['reply_text'],
				'type' => 'message',
			);
		}
		foreach(array(
			'tr_tracker'     => $this->tracker->trackers[$data['tr_tracker']],
			'cat_id'         => $cats[$data['cat_id']],
			'tr_version'     => $versions[$data['tr_version']],
			'tr_status'      => lang($statis[$data['tr_status']]),
			'tr_resolution'  => lang($resolutions[$data['tr_resolution']]),
			'tr_completion'  => (int)$data['tr_completion'].'%',
			'tr_priority'    => lang($priorities[$data['tr_priority']]),
			'tr_creator'     => common::grab_owner_name($data['tr_creator']),
			'tr_created'     => $this->datetime($data['tr_created']),
			'tr_assigned'	 => !$data['tr_assigned'] ? lang('Not assigned') : $assigned,
			'tr_cc'			 => $data['tr_cc'],
			// The layout of tr_summary should NOT be changed in order for
			// tracker.tracker_mailhandler.get_ticketId() to work!
			'tr_summary'     => '#'.$data['tr_id'].' - '.$data['tr_summary'],
		) as $name => $value)
		{
			$details[$name] = array(
				'label' => lang($this->tracker->field2label[$name]),
				'value' => $value,
			);
			if ($name == 'tr_summary') $details[$name]['type'] = 'summary';
		}
		$details['tr_description'] = array(
			'value' => $data['tr_description'],
			'type'  => 'multiline',
		);
		if ($data['replies'])
		{
			foreach($data['replies'] as $n => $reply)
			{
				$details[$n ? 2*$n : 'replies'] = array(	// first reply need to be checked against old to marked modified for new
					'value' => lang('Comment by %1 at %2:',$reply['reply_creator'] ? common::grab_owner_name($reply['reply_creator']) : lang('Tracker'),
						$this->datetime($reply['reply_servertime'])),
					'type'  => 'reply',
				);
				$details[2*$n+1] = array(
					'value' => $reply['reply_message'],
					'type'  => 'multiline',
				);
			}
		}
		return $details;
	}

	/**
	 * Override to extend permission so tracker_merge can use it
	 */
	public function get_link($data,$old,$allow_popup=false,$receiver=null)
	{
		return parent::get_link($data,$old,$allow_popup,$receiver);
	}

	/**
	 * Compute changes between new and old data
	 *
	 * Reimplemented to cope with some tracker specialties:
	 * - tr_completion is postfixed with a percent
	 *
	 * @param array $data
	 * @param array $old=null
	 * @return array of keys with different values in $data and $old
	 */
	public function changed_fields(array $data,array $old=null)
	{
		$changed = parent::changed_fields($data, $old);

		// for tr_completion ignore percent postfix
		if (($k = array_search('tr_completion', $changed)) !== false &&
			(int)$data['tr_completion'] === (int)$old['tr_completion'])
		{
			unset($changed[$k]);
		}
		return $changed;
	}
}
