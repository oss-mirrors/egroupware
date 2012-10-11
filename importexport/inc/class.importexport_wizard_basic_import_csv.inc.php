<?php
/**
 * eGroupWare - A basic implementation of a wizard to go with the basic CSV plugin.
 *
 * To add or remove steps, change $this->steps appropriately.  The key is the function, the value is the title.
 * Don't go past 80, as that's where the wizard picks it back up again to finish it off.
 *
 * For the mapping to work properly, you will have to fill $mapping_fields with the target fields for your application.
 *
 * NB: Your wizard class must be in <appname>/inc/class.appname_wizard_<plugin_name>.inc.php
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package importexport
 * @link http://www.egroupware.org
 * @author Nathan Gray
 */

class importexport_wizard_basic_import_csv
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
		'wizard_step30' => 'importexport.wizard_basic_import_csv.sample_file',
		'wizard_step40' => 'importexport.wizard_basic_import_csv.choosesepncharset',
		'wizard_step50' => 'importexport.wizard_basic_import_csv.fieldmapping',
		'wizard_step55' => 'importexport.wizard_basic_import_csv.conditions'
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
			'wizard_step30' => lang('Load Sample file'),
			'wizard_step40' => lang('Choose seperator and charset'),
			'wizard_step50' => lang('Manage mapping'),
			'wizard_step55' => lang('Edit conditions'),
		);
	}

	/**
	* Take a sample CSV file.  It will be processed in later steps
	*/
	function wizard_step30(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizard_step30->$content '.print_r($content,true));
		// return from step30
		if ($content['step'] == 'wizard_step30')
		{
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					// Move sample file to temp
					if($content['file']['tmp_name']) {
						$csvfile = tempnam($GLOBALS['egw_info']['server']['temp_dir'],$content['plugin']."_");
						move_uploaded_file($content['file']['tmp_name'], $csvfile);
						$GLOBALS['egw']->session->appsession('csvfile',$content['application'],$csvfile);
					}
					unset($content['file']);
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],-1);
				case 'finish':
					return 'wizard_finish';
				default :
					return $this->wizard_step30($content,$sel_options,$readonlys,$preserv);
			}
		}
		// init step30
		else
		{
			$content['msg'] = $this->steps['wizard_step30'];
			$content['step'] = 'wizard_step30';
			$preserv = $content;
			unset ($preserv['button']);
			//$GLOBALS['egw']->js->set_onload("xajax_eT_wrapper_init();");
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
	function wizard_step40(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizard_step40->$content '.print_r($content,true));
		// return from step40
		if ($content['step'] == 'wizard_step40') {
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					// Process sample file for fields
					if (($handle = fopen($GLOBALS['egw']->session->appsession('csvfile',$content['application']), "rb")) !== FALSE) {
						$data = fgetcsv($handle, 8000, $content['fieldsep']);
						fclose($handle);

						// Remove & forget file
						unlink($GLOBALS['egw']->session->appsession('csvfile',$content['application']));
						egw_cache::setSession($content['application'], 'csvfile', '');
						$content['csv_fields'] = translation::convert($data,$content['charset']);

						// Reset field mapping for new file
						$content['field_mapping'] = array();

						// Try to match automatically
						$english = array();
						foreach($content['csv_fields'] as $index => $field) {
							if($content['field_mapping'][$index]) continue;
							$best_match = '';
							$best_match_value = 0;
							foreach($this->mapping_fields as $key => $field_name) {
								if(is_array($field_name)) continue;
								if(strcasecmp($field, $field_name) == 0 || strcasecmp($field,$key) == 0) {
									$content['field_mapping'][$index] = $key;
									continue 2;
								}
								// Check english also
								if($GLOBALS['egw_info']['user']['preferences']['common']['lang'] != 'en' && !isset($english[$field_name])) {
									$msg_id = translation::get_message_id($field_name, $content['application']);
								}
								if($msg_id) {
									$english[$field_name] = translation::read('en', $content['application'], $msg_id);
								} else {
									$english[$field_name] = false;
								}
								if($english[$field_name] && strcasecmp($field, $english[$field_name]) == 0) {
									$content['field_mapping'][$index] = $key;
									continue 2;
								}

								// Check for similar but slightly different
								$match = 0;
								if(similar_text(strtolower($field), strtolower($field_name), $match) &&
										$match > 85 &&
										$match > $best_match_value
								) {
									$best_match = $key;
									$best_match_value = $match;
								}

							}
							if($best_match) {
								$content['field_mapping'][$index] = $best_match;
							}
						}
					} elseif($content['plugin_options']['csv_fields']) {
						$content['csv_fields'] = $content['plugin_options']['csv_fields'];
					}
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],-1);
				case 'finish':
					return 'wizard_finish';
				default :
					return $this->wizard_step40($content,$sel_options,$readonlys,$preserv);
			}
		}
		// init step40
		else
		{
			$content['msg'] = $this->steps['wizard_step40'];
			$content['step'] = 'wizard_step40';

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
			else
			{
				// Default to 1 line
				$content['num_header_lines'] = 1;
			}
			if(!$content['update_cats'] && $content['plugin_options']['update_cats']) {
				$content['update_cats'] = $content['plugin_options']['update_cats'];
			}
			if(!array_key_exists('convert', $content) && array_key_exists('convert', $content['plugin_options'])) {
				$content['convert'] = $content['plugin_options']['convert'];
			}
			else
			{
				// Default to human
				$content['convert'] = 1;
			}

			$sel_options['charset'] = $GLOBALS['egw']->translation->get_installed_charsets()+
			array(
				'user'	=> lang('User preference'),
			);

			// Add in extra allowed charsets
			$config = config::read('importexport');
			$extra_charsets = array_intersect(explode(',',$config['import_charsets']), mb_list_encodings());
			if($extra_charsets)
			{
				$sel_options['charset'] += array(lang('Extra encodings') => array_combine($extra_charsets,$extra_charsets));
			}
			$sel_options['convert'] = array(
				0       => lang('Database values'),
				1       => lang('Human friendly values')
			);
			$preserv = $content;
			if($this->mapping_fields['cat_id']) {
				$sel_options['update_cats'] = array(
					'add'	=> lang('Add'),
					'replace'=> lang('Replace')
				);
			} else {
				$content['no_cats'] = true;
			}
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
	function wizard_step50(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizard_step50->$content '.print_r($content,true));
		// return from step50
		if ($content['step'] == 'wizard_step50')
		{
			for($i = 0; $i <= count($content['csv_fields']); $i++) {
				unset($content[$i]);
			}
			if(!$content['csv_fields'][0]) {
				array_shift($content['csv_fields']);
			}
			// Need to move everything down 1 to remove header, but shift will re-key
			if(!$content['field_mapping'][0] || $content['field_mapping'][0] == $content['field_mapping'][1]) {
				unset($content['field_mapping'][0]);
				if(is_array($content['field_conversion'])) unset($content['field_conversion'][0]);
				foreach(array('field_mapping', 'field_conversion') as $field) {
					ksort($content[$field]);
					foreach($content[$field] as $key => $value)
					{
						if($value && $value != '--NONE--') {
							$content[$field][$key-1] = $content[$field][$key];
						}
						unset($content[$field][$key]);
					}
					ksort($content[$field]);
				}
			}
			foreach($content['field_conversion'] as $field => $convert) {
				if(!trim($convert)) unset($content['field_conversion'][$field]);
			}
			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],-1);
				case 'finish':
					return 'wizard_finish';
				default :
					return $this->wizard_step50($content,$sel_options,$readonlys,$preserv);
			}
		}
		// init step50
		else
		{
			$content['msg'] = $this->steps['wizard_step50'];
			$content['step'] = 'wizard_step50';
			$preserv = $content;

			if(!$content['field_mapping'] && $content['plugin_options']) {
				$content['field_mapping'] = $content['plugin_options']['field_mapping'];
				$content['field_conversion'] = $content['plugin_options']['field_conversion'];
			}

			array_unshift($content['csv_fields'], array('row0'));
			// Need to move everything down 1 to make room for header, but unshift will re-key
			// which causes problems if you skip a field.
			foreach(array('field_mapping', 'field_conversion') as $field) {
				foreach(array_reverse($content[$field], true) as $key => $value)
				{
					if($value) {
						$content[$field][$key+1] = $content[$field][$key];
					}
					unset($content[$field][$key]);
				}
				ksort($content[$field]);
			}

			$j = 1;
			$i = 0;
			foreach ($content['csv_fields'] as $field)
			{
				$content[++$i]['index'] = $i - 1;
				if(strstr($field,'no_csv_')) $j++;
			}
			while ($j <= 3)
			{
				$content['csv_fields'][] = 'no_csv_'.$j;
				$content['field_mapping'][] = $content['field_conversion'][] = '';
				$j++;
			}
			$sel_options['field_mapping'] = array('--NONE--' => lang('none')) + $this->mapping_fields;
			$GLOBALS['egw']->js->set_onload('$j("option[value=\'--NONE--\']:selected").closest("tr").animate({backgroundColor: "#ffff99"}, 1000);');
			unset ($preserv['button']);
			return $this->step_templates[$content['step']];
		}
		
	}
	
	/**
	* Edit conditions
	*/
	function wizard_step55(&$content, &$sel_options, &$readonlys, &$preserv)
	{
		if($this->debug) error_log(get_class($this) . '::wizard_step55->$content '.print_r($content,true));

		// return from step55
		if ($content['step'] == 'wizard_step55')
		{

			// Clear conditions that don't do anything
			array_shift($content['conditions']);
			foreach($content['conditions'] as $key => &$condition) {
				if(($condition['true']['action'] == 'none' || !$condition['true']['action'])  && !$condition['true']['stop']
					&& ($condition['false']['action'] == 'none' || !$condition['false']['action']) && !$condition['false']['stop']) {
					unset($content['conditions'][$key]);
					continue;
				}

				// Check for true without false, or false without true - set to 'none'
				elseif($condition['true']['action'] == '' && $condition['false']['action'] != '' ||
					$condition['true']['action'] != '' && $condition['false']['action'] == '' ||
					!$condition['true'] || !$condition['false']
				)
				{
					$condition[$condition['true']['action'] == '' ? 'true' : 'false']['action'] = "none";
				}
			}

			switch (array_search('pressed', $content['button']))
			{
				case 'next':
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],1);
				case 'previous' :
					return $GLOBALS['egw']->importexport_definitions_ui->get_step($content['step'],-1);
				case 'finish':
					return 'wizard_finish';
				case 'add':
					unset($content['button']);
					unset($content['step']);
					$content['conditions'][] = array('string' => '');
					return 'wizard_step55';
				default :
					return $this->wizard_step55($content,$sel_options,$readonlys,$preserv);
					break;
			}
		}
		// init step55
		$content['msg'] = $this->steps['wizard_step55'];
		$content['step'] = 'wizard_step55';

		if(!$content['conditions'] && $content['plugin_options']['conditions']) {
			$content['conditions'] = $content['plugin_options']['conditions'];
		}
		$preserv = $content;

		foreach($content['field_mapping'] as $field) {
			$sel_options['string'][$field] = $this->mapping_fields[$field];
		}
		$sel_options['type'] = $this->conditions;
		$sel_options['action'] = $this->actions;

		// Make at least 1 (empty) conditions
		$j = count($content['conditions']);
		while ($j < 1)
		{
			$content['conditions'][] = array(
				'string' => '',
				'true'	=> array('stop' => true),
				'false'	=> array('stop' => true),
			);
			$j++;
		}

		// Leave room for heading
		array_unshift($content['conditions'], false);
		$preserv['conditions'] = $content['conditions'];

		unset ($preserv['button']);
		return $this->step_templates[$content['step']];
	}
}
