<?php
/**
 * eGroupWare
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package calendar
 * @subpackage importexport
 * @link http://www.egroupware.org
 * @author Nathan Gray
 * @copyright Nathan Gray
 * @version $Id$
 */

/**
 * export CSV plugin of calendar
 */
class calendar_export_csv implements importexport_iface_export_plugin {

	/**
	 * Exports records as defined in $_definition
	 *
	 * @param egw_record $_definition
	 */
	public function export( $_stream, importexport_definition $_definition) {
		$options = $_definition->plugin_options;
		$this->bo = new calendar_bo();
		$events =& $this->bo->search(array(
			'start' => $options['selection']['start'],
			'end'   => $options['selection']['end'],
			'categories'	=> $options['categories'] ? $options['categories'] : $options['selection']['categories'],
			'enum_recuring' => false,
			'daywise'       => false,
			'owner'         => $options['owner'],
		));

		$export_object = new importexport_export_csv($_stream, (array)$options);
		$export_object->set_mapping($options['mapping']);
		$convert_fields = importexport_export_csv::$types;
		$convert_fields['select-account'][] = 'owner';
		$convert_fields['date-time'][] = 'start';
		$convert_fields['date-time'][] = 'end';

		$recurrence = $this->bo->recur_types;

		// $options['selection'] is array of identifiers as this plugin doesn't
		// support other selectors atm.
		$record = new calendar_egw_record();
		foreach ($events as $event) {
			// Add in participants
			if($options['mapping']['participants']) {
				$event['participants'] = implode(", ",$this->bo->participants($event,true));
			}

			$record->set_record($event);
			if($options['mapping']['recurrence']) {
				$record->recurrence = $recurrence[$record->recur_type];
				if($record->recur_type != MCAL_RECUR_NONE) $record->recurrence .= ' / '. $record->recur_interval;
			}

			// Standard stuff
			importexport_export_csv::convert($record, $convert_fields, 'calendar');

			$export_object->export_record($record);
		}
		unset($record);
	}

	/**
	 * returns translated name of plugin
	 *
	 * @return string name
	 */
	public static function get_name() {
		return lang('Calendar CSV export');
	}

	/**
	 * returns translated (user) description of plugin
	 *
	 * @return string descriprion
	 */
	public static function get_description() {
		return lang("Exports events from your Calendar into a CSV File.");
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
	 *
	 */
	public function get_options_etpl() {
	}

	/**
	 * returns selectors of this plugin
	 *
	 */
	public function get_selectors_etpl() {
		return array(
			'name'		=> 'calendar.export_csv_select',
			'content'	=> array(
				'start'		=> time(),
				'end'		=> time()
			)
		);
	}
}
