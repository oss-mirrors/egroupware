<?php
/**
 * eGroupWare - A basic implementation of a wizard to go with the basic CSV plugin.
 * 
 * To add or remove steps, change $this->steps appropriately.  The key is the function, the value is the title.
 * Don't go past 80, as that's where the wizard picks it back up again to finish it off.
 * 
 * For the mapping to work properly, you will have to fill $mapping_fields with the target fields for your application.
 * 
 * NB: Your wizard class must be in <appname>/importexport/class.wizzard_<plugin_name>.inc.php
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package importexport
 * @link http://www.egroupware.org
 * @author Nathan Gray
 */

require_once(EGW_INCLUDE_ROOT.'/importexport/inc/class.basic_import_csv.inc.php');

class wizzard_basic_import_csv 
{

	const TEMPLATE_MARKER = '-eTemplate-';

	/**
	* List of steps.  Key is the function, value is the translated title.
	*/
	public $steps;

	/**
	* List of eTemplates to use for each step.  You can override this with your own etemplates steps.
	*/
	protected $step_templates = array(
		'wizzard_step30' => 'importexport.wizard_basic_import_csv.sample_file',
		'wizzard_step40' => 'importexport.wizard_basic_import_csv.choosesepncharset',
		'wizzard_step50' => 'importexport.wizard_basic_import_csv.fieldmapping',
		'wizzard_step55' => 'importexport.wizard_basic_import_csv.conditions'
	);
		

	/**
	* Destination fields for the mapping
	* Key is the field name, value is the human version
	*/
	protected $mapping_fields = array();
	
	/**
	* List of conditions your plugin supports
	*/
	protected $conditions = array();

	/**
	* List of actions your plugin supports
	*/
	protected $actions = array();

	/**
	 * constructor
	 */
	function __construct()
	{
		$this->steps = array(
			'wizzard_step30' => lang('Load Sample file'),
			'wizzard_step40' => lang('Choose seperator and charset'),
			'wizzard_step50' => lang('Manage mapping'),
			'wizzard_step55' => lang('Edit conditions'),
		);
	}

	/**
	* Take a sample CSV file.  It will be processed in later steps
	*/
	function wizzard_step30(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizzard_step30->$content '.print_r($content,true));
		// return from step30
		if ($content['step'] == 'wizzard_step30')
		{
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					// Move sample file to temp
					if($content['file']['tmp_name']) {
						$csvfile = tempnam($GLOBALS['egw_info']['server']['temp_dir'],$content['plugin']."_");
						move_uploaded_file($content['file']['tmp_name'], $csvfile);
						$GLOBALS['egw']->session->appsession('csvfile','',$csvfile);
					}
					unset($content['file']);
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],-1);
				case 'finish':
					return 'wizzard_finish';
				default :
					return $this->wizzard_step30($content,$sel_options,$readonlys,$preserv);
			}
		}
		// init step30
		else
		{
			$content['msg'] = $this->steps['wizzard_step30'];
			$content['step'] = 'wizzard_step30';
			$preserv = $content;
			unset ($preserv['button']);
			$GLOBALS['egw']->js->set_onload("var btn = document.getElementById('exec[button][next]'); btn.attributes.removeNamedItem('onclick');");
			return $this->step_templates[$content['step']];
		}
		
	}
	
	/**
	 * choose fieldseperator, charset and headerline
	 *
	 * @param array $content
	 * @param array $sel_options
	 * @param array $readonlys
	 * @param array $preserv
	 * @return string template name
	 */
	function wizzard_step40(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizzard_step40->$content '.print_r($content,true));
		// return from step40
		if ($content['step'] == 'wizzard_step40') {
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					// Process sample file for fields
					if (($handle = fopen($GLOBALS['egw']->session->appsession('csvfile'), "rb")) !== FALSE) {
						$data = fgetcsv($handle, 8000, $content['fieldsep']);
						$content['csv_fields'] = translation::convert($data,$content['charset']);
					} elseif($content['plugin_options']['csv_fields']) {
						$content['csv_fields'] = $content['plugin_options']['csv_fields'];
					}
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],-1);
				case 'finish':
					return 'wizzard_finish';
				default :
					return $this->wizzard_step40($content,$sel_options,$readonlys,$preserv);
			}
		}
		// init step40
		else
		{
			$content['msg'] = $this->steps['wizzard_step40'];
			$content['step'] = 'wizzard_step40';

			// If editing an existing definition, these will be in plugin_options
			if(!$content['fieldsep'] && $content['plugin_options']['fieldsep']) {
				$content['fieldsep'] = $content['plugin_options']['fieldsep'];
			} elseif (!$content['fieldsep']) {
				$content['fieldsep'] = ';';
			}
			if(!$content['charset'] && $content['plugin_options']['charset']) {
				$content['charset'] = $content['plugin_options']['charset'];
			}
			if(!$content['has_header_line'] && $content['plugin_options']['has_header_line']) {
				$content['num_header_lines'] = 1;
			}
			if(!$content['num_header_lines'] && $content['plugin_options']['num_header_lines']) {
				$content['num_header_lines'] = $content['plugin_options']['num_header_lines'];
			}

			$sel_options['charset'] = $GLOBALS['egw']->translation->get_installed_charsets()+
				array('utf-8' => 'utf-8 (Unicode)');
			$preserv = $content;
			unset ($preserv['button']);
			return $this->step_templates[$content['step']];
		}
		
	}
	
	/**
	* Process the sample file, get the fields out of it, then allow them to be mapped onto 
	* the fields the destination understands.  Also, set any translations to be done to the field.
	* 
	* You can use the eTemplate 
	*/
	function wizzard_step50(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizzard_step50->$content '.print_r($content,true));
		// return from step50
		if ($content['step'] == 'wizzard_step50')
		{
			array_shift($content['csv_fields']);
			array_shift($content['field_mapping']);
			array_shift($content['field_conversion']);

			foreach($content['field_conversion'] as $field => $convert) {
				if(!trim($convert)) unset($content['field_conversion'][$field]);
			}
			
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],-1);
				case 'finish':
					return 'wizzard_finish';
				default :
					return $this->wizzard_step50($content,$sel_options,$readonlys,$preserv);
			}
		}
		// init step50
		else
		{
			$content['msg'] = $this->steps['wizzard_step50'];
			$content['step'] = 'wizzard_step50';

			if(!$content['field_mapping'] && $content['plugin_options']) {
				$content['field_mapping'] = $content['plugin_options']['field_mapping'];
				$content['field_conversion'] = $content['plugin_options']['field_conversion'];
			}
			array_unshift($content['csv_fields'],array('row0'));
			array_unshift($content['field_mapping'],array('row0'));
			array_unshift($content['field_conversion'],array('row0'));
			
			$j = 1;
			foreach ($content['csv_fields'] as $field)
			{
				if(strstr($field,'no_csv_')) $j++;
			}
			while ($j <= 3) 
			{
				$content['csv_fields'][] = 'no_csv_'.$j;
				$content['field_mapping'][] = $content['field_conversion'][] = '';
				$j++;
			}
			$sel_options['field_mapping'] = array('' => lang('none')) + $this->mapping_fields;
			$preserv = $content;
			unset ($preserv['button']);
			return $this->step_templates[$content['step']];
		}
		
	}
	
	/**
	* Edit conditions
	*/
	function wizzard_step55(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizzard_step55->$content '.print_r($content,true));
		// return from step55
		if ($content['step'] == 'wizzard_step55')
		{
			array_shift($content['conditions']);

			foreach($content['conditions'] as $key => &$condition) {
				// Clear empties
				if($condition['string'] == '') {
					unset($content['conditions'][$key]);
					continue;
				}
			}
			
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],-1);
				case 'finish':
					return 'wizzard_finish';
				case 'add':
					return $GLOBALS['egw']->uidefinitions->get_step($content['step'],0);
				default :
					return $this->wizzard_step55($content,$sel_options,$readonlys,$preserv);
					break;
			}
		}
		// init step55
		$content['msg'] = $this->steps['wizzard_step55'];
		$content['step'] = 'wizzard_step55';

		if(!$content['conditions'] && $content['plugin_options']['conditions']) {
			$content['conditions'] = $content['plugin_options']['conditions'];
		}

		foreach($content['field_mapping'] as $field) {
			$sel_options['string'][$field] = $this->mapping_fields[$field];
		}
		$sel_options['type'] = array_combine($this->conditions, $this->conditions);
		$sel_options['action'] = array_combine($this->actions, $this->actions);

		// Make 3 empty conditions
		$j = 1;
		foreach ($content['conditions'] as $condition)
		{
			if(!$condition['string']) $j++;
		}
		while ($j <= 3) 
		{
			$content['conditions'][] = array('string' => '');
			$j++;
		}

		// Leave room for heading
		array_unshift($content['conditions'], false);

		$preserv = $content;
		unset ($preserv['button']);
		return $this->step_templates[$content['step']];
	}
}
