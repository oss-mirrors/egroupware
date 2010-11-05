<?php
/**
 * eGroupWare importexport
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package importexport
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <nelius@cwtech.de>
 * @copyright Cornelius Weiss <nelius@cwtech.de>
 * @version $Id$
 */

/**
 * class export_csv
 * This an record exporter.
 * An record is e.g. a single address or or single event.
 * No mater where the records come from, at the end export_entry
 * stores it into the stream
 */
class importexport_export_csv implements importexport_iface_export_record
{
	/**
	 * @var array array with field mapping in form egw_field_name => exported_field_name
	 */
	protected  $mapping = array();

	/**
	 * @var array array with conversions to be done in form: egw_field_name => conversion_string
	 */
	protected  $conversion = array();
	
	/**
	 * @var array holding the current record
	 */
	protected $record = array();
	
	/**
	 * @var translation holds (charset) translation object
	 */
	protected $translation;
	
	/**
	 * @var string charset of csv file
	 */
	protected $csv_charset;
	
	/**
	 * @var int holds number of exported records
	 */
	protected $num_of_records = 0;
	
	/**
	 * @var stream stream resource of csv file
	 */
	protected  $handle;
	
	/**
	 * @var array csv specific options
	 */
	protected $csv_options = array(
		'delimiter' => ';',
		'enclosure' => '"',
	);

	/**
	 * List of fields for data conversion
	 */
	public static $types = array(
		'select-account' => array('creator', 'modifier'),
		'date-time'	=> array('modified', 'created'),
		'date'		=> array(),
		'select-cat'	=> array('cat_id')
	);
	
	/**
	 * constructor
	 *
	 * @param stram $_stream resource where records are exported to.
	 * @param array _options options for specific backends
	 * @return bool
	 */
	public function __construct( $_stream, array $_options ) {
		if (!is_object($GLOBALS['egw']->translation)) {
			$GLOBALS['egw']->translation = new translation();
		}
		$this->translation = &$GLOBALS['egw']->translation;
		$this->handle = $_stream;
		$this->csv_charset = $_options['charset'] ? $_options['charset'] : 'utf-8';
		if ( !empty( $_options ) ) {
			$this->csv_options = array_merge( $this->csv_options, $_options );
		}
	}
	
	/**
	 * sets field mapping
	 *
	 * @param array $_mapping egw_field_name => csv_field_name
	 */
	public function set_mapping( array $_mapping) {
		if ($this->num_of_records > 0) {
			throw new Exception('Error: Field mapping can\'t be set during ongoing export!');
		}
		$this->mapping = $_mapping;
	}
	
	/**
	 * Sets conversion.
	 * @see importexport_helper_functions::conversion.
	 *
	 * @param array $_conversion
	 */
	public function set_conversion( array $_conversion) {
		$this->conversion = $_conversion;
	}
	
	/**
	 * exports a record into resource of handle
	 *
	 * @param importexport_iface_egw_record record
	 * @return bool
	 */
	public function export_record( importexport_iface_egw_record $_record ) {
		$this->record = $_record->get_record_array();
		
		// begin with fieldnames ?
		if ($this->num_of_records == 0 && $this->csv_options['begin_with_fieldnames'] ) {
			$mapping = ! empty( $this->mapping ) ? $this->mapping : array_keys ( $this->record );
			$mapping = $this->translation->convert( $mapping, $this->translation->charset(), $this->csv_charset );
			fputcsv( $this->handle ,$mapping ,$this->csv_options['delimiter'], $this->csv_options['enclosure'] );
		}
		
		// do conversions
		if ( !empty( $this->conversion )) {
			$this->record = importexport_helper_functions::conversion( $this->record, $this->conversion );
		}
		
		// do fieldmapping
		if ( !empty( $this->mapping ) ) {
			$record_data = $this->record;
			$this->record = array();
			foreach ($this->mapping as $egw_field => $csv_field) {
				$this->record[$csv_field] = $record_data[$egw_field];
			}
		}
		
		// do charset translation
		$this->record = $this->translation->convert( $this->record, $this->translation->charset(), $this->csv_charset );
		
		$this->fputcsv( $this->handle, $this->record, $this->csv_options['delimiter'], $this->csv_options['enclosure'] );
		$this->num_of_records++;
	}

	/**
	 * Retruns total number of exported records.
	 *
	 * @return int
	 */
	public function get_num_of_records() {
		return $this->num_of_records;
	}

	/**
	 * Convert system info into a format with a little more transferrable meaning
	 *
	 * Uses the static variable $types to convert various datatypes.
	 *
	 * @param record Record to be converted
	 * @parem fields List of field types => field names to be converted
	 * @param appname Current appname if you want to do custom fields too
	 */
	public static function convert(importexport_iface_egw_record &$record, Array $fields = array(), $appname = null) {
		if($appname) {
			$custom = config::get_customfields($appname);
			foreach($custom as $name => $c_field) {
				$name = '#' . $name;
				switch($c_field['type']) {
					case 'date':
						$fields['date'][] = $name;
						break;
					case 'date-time':
						$fields['date-time'][] = $name;
						break;
					case 'select-account':
						$fields['select-account'][] = $name;
						break;
				}
			}
		}
		foreach($fields['select-account'] as $name) {
			if ($record->$name) {
				if(is_array($record->$name)) {
					$names = array();
					foreach($record->$name as $_name) {
						$names[] = common::grab_owner_name($_name);
					}
					$record->$name = implode(', ', $names);
				} else {
					$record->$name = common::grab_owner_name($record->$name);
				}
			}
		}
		foreach($fields['date-time'] as $name) {
			//if ($record->$name) $record->$name = date('Y-m-d H:i:s',$record->$name); // Standard date format
			if ($record->$name) $record->$name = date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'] . ' '.
				($GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == '24' ? 'H' : 'h').':m:s',$record->$name); // User date format
		}
		foreach($fields['date'] as $name) {
			//if ($record->$name) $record->$name = date('Y-m-d',$record->$name); // Standard date format
			if ($record->$name) $record->$name = date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'], $record->$name); // User date format
		}

		foreach($fields['select-cat'] as $name) {
			if($record->$name) {
				$cats = array();
				$ids = is_array($record->$name) ? $record->$name : explode(',', $record->$name);
				foreach($ids as $n => $cat_id) {
					if ($cat_id) $cats[] = $GLOBALS['egw']->categories->id2name($cat_id);
				}
				$record->$name = implode(', ',$cats);
			}
		}
	}

	/**
	 * destructor
	 *
	 * @return 
	 */
	public function __destruct() {
		
	}

	/**
	 * The php build in fputcsv function is buggy, so we need an own one :-(
	 *
	 * @param resource $filePointer
	 * @param array $dataArray
	 * @param char $delimiter
	 * @param char $enclosure
	 */
	protected function fputcsv($filePointer, Array $dataArray, $delimiter, $enclosure){
		$string = "";
		$writeDelimiter = false;
		foreach($dataArray as $dataElement) {
			if($writeDelimiter) $string .= $delimiter;
			$string .= $enclosure . $dataElement . $enclosure;
			$writeDelimiter = true;
		} 
		$string .= "\n";
		
		fwrite($filePointer, $string);
			
	}
} // end  export_csv_record
?>
