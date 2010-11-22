<?php
/**
 * eGroupWare
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package infolog
 * @subpackage importexport
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright Nathan Gray
 * @version $Id$
 */

/**
 * export plugin of infolog
 */
class infolog_export_csv implements importexport_iface_export_plugin {

	// Used in conversions
	static $types = array(
                'select-account' => array('info_owner','info_responsible','modifier'),
                'date-time' => array('info_datecompleted', 'info_datemodified','created','last_event','next_event'),
		'date' => array('info_startdate', 'info_enddate'),
                'select-cat' => array('info_cat', 'cat_id'),
		'links' => array('info_link_id'),
        );

	/**
	 * Exports records as defined in $_definition
	 *
	 * @param egw_record $_definition
	 */
	public function export( $_stream, importexport_definition $_definition) {
		$options = $_definition->plugin_options;

		$bo = new infolog_bo();
		$selection = array();
		$query = array();

		// do we need to query the cf's
		foreach($options['mapping'] as $field => $map) {
			if($field[0] == '#') $query['custom_fields'][] = $field;
		}

		if ($options['selection'] == 'search') {
			$query = array_merge($GLOBALS['egw']->session->appsession('session_data','infolog'), $query);
			$query['num_rows'] = -1;	// all
			$selection = $bo->search($query);
		}
		elseif ( $options['selection'] == 'all' ) {
			$query['num_rows'] = -1;
			$selection = $bo->search($query);
		} else {
			$selection = explode(',',$options['selection']);
		}

		$export_object = new importexport_export_csv($_stream, (array)$options);
		$export_object->set_mapping($options['mapping']);

		foreach ($selection as $_identifier) {
			if(!is_array($_identifier)) {
				$record = new infolog_egw_record($_identifier);
			} else {
				$record = new infolog_egw_record();
				$record->set_record($_identifier);
			}

			// Some conversion
			if($options['convert']) {
				importexport_export_csv::convert($record, self::$types, 'infolog');
				$this->convert($record);
			}
			$export_object->export_record($record);
			unset($record);
		}
	}

	/**
	 * returns translated name of plugin
	 *
	 * @return string name
	 */
	public static function get_name() {
		return lang('Infolog CSV export');
	}

	/**
	 * returns translated (user) description of plugin
	 *
	 * @return string descriprion
	 */
	public static function get_description() {
		return lang("Exports Infolog entries into a CSV File.");
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
		return 'infolog.export_csv_selectors';
	}

	/**
	* Convert some internal data to something with more meaning
	*
	* This is for something specific to Infolog, in addition to the normal conversions.
	*/
	public static function convert(infolog_egw_record &$record) {
		// Stub, for now
	}
}
