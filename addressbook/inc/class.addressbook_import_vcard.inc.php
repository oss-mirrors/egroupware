<?php
/**
 * importexport plugin to import vCard files
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package importexport
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright 2012 Nathan Gray
 * @version $Id$
 */


/**
 * Plugin to import vCard files
 */
class addressbook_import_vcard implements importexport_iface_import_plugin  {

	private static $plugin_options = array(
		
		'contact_owner'
	);

	/**
	 * actions wich could be done to data entries
	 */
	protected static $actions = array('insert');

	/**
	 * conditions for actions
	 *
	 * @var array
	 */
	protected static $conditions = array( );

	/**
	 * @var definition
	 */
	private $definition;

	/**
	 * @var bocontacts
	 */
	private $bocontacts;

	/**
	* For figuring out if a contact has changed
	*/
	protected $tracking;

	/**
	 * @var bool
	 */
	private $dry_run = false;

	/**
	 * @var bool is current user admin?
	 */
	private $is_admin = false;

	/**
	 * @var int
	 */
	private $user = null;

	/**
	 * List of import warnings
	 */
	protected $warnings = array();

	/**
	 * List of import errors
	 */
	protected $errors = array();

	/**
         * List of actions, and how many times that action was taken
         */
        protected $results = array();

	/**
	 * imports entries according to given definition object.
	 * @param resource $_stream
	 * @param string $_charset
	 * @param definition $_definition
	 */
	public function import( $_stream, importexport_definition $_definition ) {
		$this->definition = $_definition;

		// user, is admin ?
		$this->is_admin = isset( $GLOBALS['egw_info']['user']['apps']['admin'] ) && $GLOBALS['egw_info']['user']['apps']['admin'];
		$this->user = $GLOBALS['egw_info']['user']['account_id'];

                // set contact owner
                $contact_owner = isset( $_definition->plugin_options['contact_owner'] ) ?
                        $_definition->plugin_options['contact_owner'] : $this->user;
                // Import into importer's personal addressbook
                if($contact_owner == 'personal')
                {
                        $contact_owner = $this->user;
                }

		// dry run?
		$this->dry_run = isset( $_definition->plugin_options['dry_run'] ) ? $_definition->plugin_options['dry_run'] :  false;

		// Needed for categories to work right
		$GLOBALS['egw_info']['flags']['currentapp'] = 'addressbook';

		// fetch the addressbook bo
		$this->bocontacts = new addressbook_vcal();

		$charset = $_definition->plugin_options['charset'];

		// Start counting successes
		$this->current = 0;
		$count = 0;
		$this->results = array();

		// Failures
		$this->errors = array();

		 // Fix for Apple Addressbook
                $vCard = preg_replace('/item\d\.(ADR|TEL|EMAIL|URL)/', '\1', stream_get_contents($_stream));

		$contacts = new egw_ical_iterator($vCard, '', $charset, array($this, '_vcard'),array(
			// Owner (addressbook)
			$contact_owner
		));
		$contacts->next();
		while($contacts->valid()) {
			$this->current++;
			$contact = $contacts->current();

			$this->action('insert', $contact, $this->current);
			$count++;
			$contacts->next();
		}
		
		return $count;
	}

	/**
	 * Changes a vcard object into egw data array
	 */
	public function _vcard($_vcard, $owner)
	{
		$record = $this->bocontacts->vcardtoegw($_vcard,$this->definition->plugin_options['charset']);

		$record['owner'] = $owner;

		// Check that owner (addressbook) is allowed
		if(!array_key_exists($record['owner'], $this->bocontacts->get_addressbooks()))
		{
			$this->errors[$this->current] = lang("Unable to import into %1, using %2",
				common::grab_owner_name($record['owner']),
				common::grab_owner_name($this->user)
			);
			$record['owner'] = $this->user;
		}

		// Do not allow owner == 0 (accounts) without an account_id
		// It causes the contact to be filed as an account, and can't delete
		if(!$record['owner'] && !$record['account_id'])
		{
			$record['owner'] = $this->user;
		}

		if (is_array($record['cat_id']))
		{
			$record['cat_id'] = implode(',',$this->bocontacts->find_or_add_categories($record['cat_id'], -1));
		}
		return $record;
	}

	/**
	 * perform the required action
	 *
	 * @param int $_action one of $this->actions
	 * @param array $_data contact data for the action
	 * @return bool success or not
	 */
	private function action ( $_action, $_data, $record_num = 0 ) {
		switch ($_action) {
			case 'none' :
				return true;
			case 'update' :
				// Only update if there are changes
				$old = $this->bocontacts->read($_data['id']);
				// if we get countrycodes as countryname, try to translate them -> the rest should be handled by bo classes.
				foreach(array('adr_one_', 'adr_two_') as $c_prefix) {
					if (strlen(trim($_data[$c_prefix.'countryname']))==2) $_data[$c_prefix.'countryname'] = $GLOBALS['egw']->country->get_full_name(trim($_data[$c_prefix.'countryname']),$translated=true);
				}
				// Don't change a user account into a contact
				if($old['owner'] == 0) {
					unset($_data['owner']);
				} elseif(!$this->definition->plugin_options['change_owner']) {
					// Don't change addressbook of an existing contact
					unset($_data['owner']);
				}

				// Merge to deal with fields not in import record
				$_data = array_merge($old, $_data);
				$changed = $this->tracking->changed_fields($_data, $old);
				if(count($changed) == 0) {
					return true;
				} else {
					//error_log(__METHOD__.__LINE__.array2string($changed).' Old:'.$old['adr_one_countryname'].' ('.$old['adr_one_countrycode'].') New:'.$_data['adr_one_countryname'].' ('.$_data['adr_one_countryname'].')');
				}
				
				// Make sure n_fn gets updated
				unset($_data['n_fn']);

				// Fall through
			case 'insert' :
				if($_action == 'insert') {
					// Addressbook backend doesn't like inserting with ID specified, it screws up the owner & etag
					unset($_data['id']);
				}
				if(!isset($_data['org_name'])) {
					// org_name is a trigger to update n_fileas
					$_data['org_name'] = '';
				}

				if ( $this->dry_run ) {
					print_r($_data);
					$this->results[$_action]++;
					return true;
				} else {
					$result = $this->bocontacts->save( $_data, $this->is_admin);
					if(!$result) {
						$this->errors[$record_num] = $this->bocontacts->error;
					} else {
						$this->results[$_action]++;
					}
					return $result;
				}
			default:
				throw new egw_exception('Unsupported action: '. $_action);
			
		}
	}

	/**
	 * returns translated name of plugin
	 *
	 * @return string name
	 */
	public static function get_name() {
		return lang('Addressbook vCard import');
	}

	/**
	 * returns translated (user) description of plugin
	 *
	 * @return string descriprion
	 */
	public static function get_description() {
		return lang("Imports contacts into your Addressbook from a vCard File. ");
	}

	/**
	 * retruns file suffix(s) plugin can handle (e.g. csv)
	 *
	 * @return string suffix (comma seperated)
	 */
	public static function get_filesuffix() {
		return 'vcf';
	}

	/**
	 * return etemplate components for options.
	 * @abstract We can't deal with etemplate objects here, as an uietemplate
	 * objects itself are scipt orientated and not "dialog objects"
	 *
	 * @return array (
	 * 		name 		=> string,
	 * 		content		=> array,
	 * 		sel_options => array,
	 * 		preserv		=> array,
	 * )
	 */
	public function get_options_etpl() {
		// lets do it!
	}

	/**
	 * returns etemplate name for slectors of this plugin
	 *
	 * @return string etemplate name
	 */
	public function get_selectors_etpl() {
		// lets do it!
	}

	/**
        * Returns warnings that were encountered during importing
        * Maximum of one warning message per record, but you can append if you need to
        *
        * @return Array (
        *       record_# => warning message
        *       )
        */
        public function get_warnings() {
		return $this->warnings;
	}

	/**
        * Returns errors that were encountered during importing
        * Maximum of one error message per record, but you can append if you need to
        *
        * @return Array (
        *       record_# => error message
        *       )
        */
        public function get_errors() {
		return $this->errors;
	}

	/**
        * Returns a list of actions taken, and the number of records for that action.
        * Actions are things like 'insert', 'update', 'delete', and may be different for each plugin.
        *
        * @return Array (
        *       action => record count
        * )
        */
        public function get_results() {
                return $this->results;
        }
} // end of iface_export_plugin
?>
