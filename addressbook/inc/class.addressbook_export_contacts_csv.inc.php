<?php
/**
 * eGroupWare
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package addressbook
 * @subpackage importexport
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <nelius@cwtech.de>
 * @copyright Cornelius Weiss <nelius@cwtech.de>
 * @version $Id$
 */

/**
 * export plugin of addressbook
 */
class addressbook_export_contacts_csv implements importexport_iface_export_plugin {

	// Used in conversions
	static $types = array(
                'select-account' => array('owner','creator','modifier'),
                'date-time' => array('modified','created','last_event','next_event'),
                'select-cat' => array('cat_id'),
        );

	/**
	 * Constants used for exploding categories & multi-selectboxes into seperate fields
	 */
	const NO_EXPLODE = False;
	const MAIN_CATS = 'main_cats';	// Only the top-level categories get their own field
	const EACH_CAT = 'each_cat';	// Every category gets its own field
	const EXPLODE = 'explode';	// For [custom] multi-selects, each option gets its own field

	/**
	 * Exports records as defined in $_definition
	 *
	 * @param egw_record $_definition
	 */
	public function export( $_stream, importexport_definition $_definition) {
		$options = $_definition->plugin_options;

		$uicontacts = new addressbook_ui();
		$selection = array();
		if ($options['selection'] == 'use_all') {
			// uicontacts selection with checkbox 'use_all'
			$query = $GLOBALS['egw']->session->appsession('index','addressbook');
			$query['num_rows'] = -1;	// all
			$uicontacts->get_rows($query,$selection,$readonlys,true);	// true = only return the id's
		}
		elseif ( $options['selection'] == 'all_contacts' ) {
			$selection = ExecMethod('addressbook.addressbook_bo.search',array());
			//$uicontacts->get_rows($query,$selection,$readonlys,true);
		} else {
			$selection = explode(',',$options['selection']);
		}

		if($options['explode_multiselects']) {
			$customfields = config::get_customfields('addressbook');
			$additional_fields = array();
			$cat_obj = new categories('', 'addressbook');
			foreach($options['explode_multiselects'] as $field => $explode) {
				switch($explode['explode']) {
					case self::MAIN_CATS:
						$cats = $cat_obj->return_array('mains', 0, false);
						foreach($cats as $settings) {
							$additional_fields[$field][$settings['id']] = array(
								'count' => 0,
								'label' => $settings['name'],
								'subs' => array(),
							);
							$subs = $cat_obj->return_array('subs', 0, false, '', 'ASC','', True, $settings['id']);
							foreach($subs as $sub) {
								$additional_fields[$field][$settings['id']]['subs'][$sub['id']] = $sub['name'];
							}
						}
						break;
					case self::EACH_CAT:
						$cats = $cat_obj->return_array('all', 0, false);
						foreach($cats as $settings) {
							$additional_fields[$field][$settings['id']] = array(
								'count' => 0,
								'label' => $settings['name']
							);
						}
						break;
					case self::EXPLODE:
						// Only works for custom fields
						$index = substr($field, 1);
						foreach($customfields[$index]['values'] as $key => $value) {
							$additional_fields[$field][$key] = array(
								'count' => 0,
								'label' => $customfields[$index]['label'] . ': ' . $value,
							);
						}
						break;
				}
			}

			// Check records to see if additional fields are acutally used
			foreach ($selection as $identifier) {
				$contact = new addressbook_egw_record($identifier);
				foreach($additional_fields as $field => &$values) {
					if(!$contact->$field) continue;
					foreach($values as $value => &$settings) {
						if(!is_array($contact->$field)) {
							$contact->$field = explode(',', $contact->$field);
						}
						if(is_array($contact->$field) && in_array($value, $contact->$field)) {
							$settings['count']++;
						} elseif($contact->$field == $value) {
							$settings['count']++;
						}
					}
				}
			}

			unset($field);
			unset($value);
			unset($settings);

			// Add additional columns
			foreach($additional_fields as $field => $additional_values) {
				// Remove original
				unset($options['mapping'][$field]);
				// Add exploded
				$field_count = 0;
				foreach($additional_values as $value => $settings) {
					if($settings['count'] > 0) {
						$field_count += $settings['count'];
						$options['mapping'][$field.'-'.$value] = $settings['label'];
					}
				}
				if($field_count > 0) {
					// Set some options for converting
					$options['explode_multiselects'][$field]['values'] = $additional_values;
				} else {
					// Don't need this anymore
					unset($options['explode_multiselects'][$field]);
				}
			}
		}

		$export_object = new importexport_export_csv($_stream, (array)$options);
		$export_object->set_mapping($options['mapping']);

		// $options['selection'] is array of identifiers as this plugin doesn't
		// support other selectors atm.
		foreach ($selection as $identifier) {
			$contact = new addressbook_egw_record($identifier);
			// Some conversion
			$this->convert($contact, $options);
			importexport_export_csv::convert($contact, self::$types, 'addressbook');
			$export_object->export_record($contact);
			unset($contact);
		}
	}

	/**
	 * returns translated name of plugin
	 *
	 * @return string name
	 */
	public static function get_name() {
		return lang('Addressbook CSV export');
	}

	/**
	 * returns translated (user) description of plugin
	 *
	 * @return string descriprion
	 */
	public static function get_description() {
		return lang("Exports contacts from your Addressbook into a CSV File.");
	}

	/**
	 * retruns file suffix for exported file
	 *
	 * @return string suffix
	 */
	public static function get_filesuffix() {
		return 'csv';
	}

	public static function get_mimetype() {
		return 'text/csv';
	}

	/**
	 * return html for options.
	 * this way the plugin has all opertunities for options tab
	 *
	 * @return string html
	 */
	public function get_options_etpl() {
	}

	/**
	 * returns slectors of this plugin via xajax
	 *
	 */
	public function get_selectors_etpl() {
		return 'addressbook.export_csv_selectors';
	}

	/**
	* Convert some internal data to something with more meaning
	* 
	* Dates, times, user IDs, category IDs
	*/
	public static function convert(addressbook_egw_record &$record, $options) {
		
		if ($record->tel_prefer) {
			$field = $record->tel_prefer;
			$record->tel_prefer = $record->$field;
		}

		foreach((array)$options['explode_multiselects'] as $field => $explode_settings) {
			if(!is_array($record->$field)) $record->$field = explode(',', $record->$field);
			foreach($explode_settings['values'] as $value => $settings) {
				$field_name = "$field-$value";
				$record->$field_name = array();
				if(is_array($record->$field) && in_array($value, $record->$field) || $record->$field == $value) {
					if($explode_settings['explode'] != self::MAIN_CATS) {
						$record->$field_name = lang('Yes');
					} else {
						// 3 part assign due to magic get method
						$record_value = $record->$field_name;
						$record_value[] = $settings['label'];
						$record->$field_name = $record_value;
					}
				}
				if($explode_settings['explode'] == self::MAIN_CATS && count(array_intersect($record->$field, array_keys($settings['subs'])))) {
					// 3 part assign due to magic get method
					$record_value = $record->$field_name;
					foreach(array_intersect($record->$field, array_keys($settings['subs'])) as $sub_id) {
						$record_value[] = $settings['subs'][$sub_id];
					}
					$record->$field_name = $record_value;
				}
				if(is_array($record->$field_name)) $record->$field_name = implode(', ', $record->$field_name);
			}
		}
	}
}
