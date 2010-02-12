<?php
/**
 * Calendar - history and notifications
 *
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @package calendar
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id: class.addressbook_tracking.inc.php 29164 2010-02-09 21:56:39Z jlehrke $
 */

/**
 * Calendar - tracking object
 */
class calendar_tracking extends bo_tracking
{
	/**
	 * Application we are tracking (required!)
	 *
	 * @var string
	 */
	var $app = 'calendar';
	/**
	 * Name of the id-field, used as id in the history log (required!)
	 *
	 * @var string
	 */
	var $id_field = 'id';
	/**
	 * Name of the field with the creator id, if the creator of an entry should be notified
	 *
	 * @var string
	 */
	var $creator_field = 'creator';
	/**
	 * Name of the field with the id(s) of assinged users, if they should be notified
	 *
	 * @var string
	 */
	var $assigned_field;
	/**
	 * Translate field-name to 2-char history status
	 *
	 * @var array
	 */
	var $field2history = array(
		'owner'		=>	'owner',
		'category'	=>	'category',
		'priority'	=>	'priority',
		'public'	=>	'public',
		'title'		=>	'title',
		'description'	=>	'description',
		'location'	=>	'location',
		'reference'	=>	'reference',
		'modifier'	=>	'modifier',
		'non_blocking'	=>	'non_blocking',
		'special'	=>	'special',
		'creator'	=>	'creator',
		'recurrence'	=>	'recurrence',
		'tz_id'		=>	'tz_id',

		'start'		=>	'start',
		'end'		=>	'end',
		
		'participants'	=>	array('user_id', 'status', 'role', 'recur'),
		'participants-c'	=>	array('user_id', 'status', 'quantity', 'role', 'recur'),
		
		// Custom fields added in constructor
	);

	/**
	* Translate field name to label
	*/
	public $field2label = array(
		'owner'		=>	'owner',
		'category'	=>	'category',
		'priority'	=>	'priority',
		'public'	=>	'public',
		'title'		=>	'title',
		'description'	=>	'description',
		'location'	=>	'location',
		'reference'	=>	'reference',
		'modifier'	=>	'modifier',
		'non_blocking'	=>	'non blocking',
		'special'	=>	'special',
		'creator'	=>	'creator',
		'recurrence'	=>	'recurrence',
		'tz_id'		=>	'tz_id',

		'start'		=>	'start',
		'end'		=>	'end',
		
		'participants'	=>	'Participants: User, Status, Role',
		'participants-c'=>	'Participants: User, Status, Quantity, Role'

		// Custom fields added in constructor
	);

	/**
	 * Should the user (passed to the track method or current user if not passed) be used as sender or get_config('sender')
	 *
	 * @var boolean
	 */
	var $prefer_user_as_sender = true;

	/**
	 * Constructor
	 *
	 * @param calendar_bo &$calendar_bo
	 */
	public function __construct()
	{
		parent::__construct();	// calling the constructor of the extended class

		$custom = config::get_customfields('calendar', true);
		if(is_array($custom)) {
			foreach($custom as $name => $settings) {
				$this->field2history['#'.$name] = '#'.$name;
				$this->field2label['#'.$name] = $settings['label'];
			}
		}
	}

	/**
	* Tracks the changes in one entry $data, by comparing it with the last version in $old
	* Overrides parent to reformat participants into a format parent can handle
	*/
	public function track(array $data,array $old=null,$user=null,$deleted=null,array $changed_fields=null)
        {
		// Don't try to track dates on recurring events.  
		// It won't change for the base event, and any change to the time creates an exception
		if($data['recur_type']) {
			unset($data['start']); unset($data['end']);
			unset($old['start']); unset($old['end']);
		}

		/**
		* Do some magic with the participants and recurrance.
		* If this is one of a recurring event, append the recur_date to the participant field so we can
		* filter by it later.  
		*/
		$recur_prefix = $data['recur_date'] ? $data['recur_date'] : '';
		if(is_array($data['participants'])) {
			$participants = $data['participants'];
			$data['participants'] = array();
			foreach($participants as $id => $status) {
				calendar_so::split_status($status, $quantity, $role);
				$field = ($id[0] == 'c' ? 'participants-c' : 'participants');
				$data[$field][] = array(
					'user_id'	=>	$id[0] == 'c' ? substr($id,1) : $id,
					'status'	=>	$status,
					'quantity'	=>	$quantity,
					'role'		=>	$role,
					'recur'		=>	$data['recur_date'] ? $data['recur_date'] : 0,
				);
			}
		}
		if(is_array($old['participants'])) {
			$participants = $old['participants'];
			$old['participants'] = array();
			foreach($participants as $id => $status) {
				calendar_so::split_status($status, $quantity, $role);
				$field = ($id[0] == 'c' ? 'participants-c' : 'participants');
				$old[$field][] = array(
					'user_id'	=>	$id[0] == 'c' ? substr($id,1) : $id,
					'status'	=>	$status,
					'quantity'	=>	$quantity,
					'role'		=>	$role,
					'recur'		=>	$data['recur_date'] ? $data['recur_date'] : 0,
				);
			}
		}
		parent::track($data,$old,$user,$deleted, $changed_fields);
	}

	/**
	* Overrides parent because calendar_boupdates handles the notifications
	*/
	public function do_notifications($data,$old,$deleted=null) {
		return true;
	}
}
