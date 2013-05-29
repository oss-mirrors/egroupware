<?php
/**
 * eGroupWare
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package importexport
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <nelius@cwtech.de>
 * @copyright Cornelius Weiss <nelius@cwtech.de>
 * @version $Id$
 */

/**
 * userinterface for exports
 *
 */
class importexport_export_ui {
	const _appname = 'importexport';

	public $public_functions = array(
		'export_dialog' =>	true,
		'download' 	=>	true,
	);

	private $js;
	private $user;

	/**
	 * holds all export plugins from all apps
	 *
	 * @var array
	 */
	private $export_plugins;

	public function __construct() {
		$this->js = $GLOBALS['egw']->js = is_object($GLOBALS['egw']->js) ? $GLOBALS['egw']->js : CreateObject('phpgwapi.javascript');
		$this->js->validate_file('.','export_dialog','importexport');
		$this->js->validate_file('.','importexport','importexport');
		$this->user = $GLOBALS['egw_info']['user']['user_id'];
		$this->export_plugins = importexport_helper_functions::get_plugins('all','export');
		$GLOBALS['egw_info']['flags']['include_xajax'] = true;

	}

	public function export_dialog($_content=array()) {
		$tabs = 'general_tab|selection_tab|options_tab';
		$sel_options = array();
		$readonlys = array();
		$preserv = array();

		$et = new etemplate(self::_appname. '.export_dialog');
		$_appname = $_content['appname'] ? $_content['appname'] : $_GET['appname'];
		$_definition = $_content['definition'] ? $_content['definition'] : $_GET['definition'];
		$_plugin = $_content['plugin'] ? $_content['plugin'] : $_GET['plugin'];
		$_selection = $_content['selection'] ? $_content['selection'] : $_GET['selection'];
		if($_GET['selection'] || $_content['selection_passed']) $content['selection_passed'] = $preserv['selection_passed'] = true;

		// Check global setting
		if(!bo_merge::is_export_limit_excepted()) {
			$export_limit = bo_merge::getExportLimit($_appname);
			if($export_limit == 'no') {
				die(lang('Admin disabled exporting'));
			}
		}
			//error_log(__FILE__.__FUNCTION__. '::$_GET[\'appname\']='. $_appname. ',$_GET[\'definition\']='. $_definition. ',$_GET[\'plugin\']='.$_plugin. ',$_GET[\'selection\']='.$_selection);
		// if appname is given and valid, list available definitions (if no definition is given)
		$readonlys['appname'] = (!empty($_appname) && $GLOBALS['egw']->acl->check('run',1,$_appname));
		$content['appname'] = $_appname;
		$preserv['appname'] = $_appname;
		if(empty($_appname)) {
			$this->js->set_onload('$j("tr.select_definition").css("display","none");');
		}

		// Check for preferred definition
		if(!$_definition && $_appname) {
			$_definition = $GLOBALS['egw_info']['user']['preferences'][$_appname]['nextmatch-export-definition'];
		}
		// fill definitions
		$sel_options['definition'] = array('' => lang('Select'));
		$definitions = new importexport_definitions_bo(array(
			'type' => 'export',
			'application' => isset($content['appname']) ? $content['appname'] : '*',
			'plugin' => $_plugin ? $_plugin : '*'
		));
		foreach ((array)$definitions->get_definitions() as $identifier) {
			try {
				$definition = new importexport_definition($identifier);
			} catch (Exception $e) {
				// permission error
				continue;
			}
			if ($title = $definition->get_title()) {
				$sel_options['definition'][$title] = $title;
			}
			unset($definition);
		}
		if(count($sel_options['definition']) == 2 && !$content['definition']) {
			$content['definition'] = end($sel_options['definition']);
		}
		unset($definitions);
		//$sel_options['definition']['expert'] = lang('Expert options');

		if(isset($_definition) && array_key_exists($_definition,$sel_options['definition'])) {
			$content['definition'] = $_definition;
		}

		// fill plugins
		$sel_options['plugin'] = $this->export_plugins[$_appname]['export'];

		// show definitions or plugins in ui?
		if($content['definition'] == 'expert') {
			if(isset($_plugin) && array_key_exists($_plugin,$sel_options['plugin'])) {
				$content['plugin'] = $_plugin;
				$selected_plugin = $_plugin;
			}
			else
			{
/*
					$plugins_classnames = array_keys($sel_options['plugin']);
					$selected_plugin = $plugins_classnames[0];
					$sel_options['plugin'] = $plugins;
*/
			}
			//$this->js->set_onload("set_style_by_class('tr','select_definition','display','none');");
		}
		else {
			$readonlys['plugin'] = true;
			$readonlys['save_definition'] = true;

			$definition = new importexport_definition($content['definition']);
			if($definition) {
				$content += (array)$definition->plugin_options;
				$selected_plugin = $definition->plugin;
				$content['description'] = $definition->description;
			}
		}


		// Delimiter
		$sel_options['delimiter'] = array(
			';'	=>	';',
			','	=>	',',
			'\t'	=>	'Tab',
			' ' 	=>	'Space',
			'|'	=>	'|',
			''	=>	lang('Other')
		);
		if(!$sel_options['delimiter'][$content['delimiter']]) $sel_options['delimiter'][$content['delimiter']] = $content['delimiter'];
		$sel_options['delimiter'][$content['delimiter']] = lang('Use default') . ' "' . $sel_options['delimiter'][$content['delimiter']] . '"';

		if(!$_content['delimiter']) $this->js->set_onload('jQuery(\'[other_delimiter]\').hide();');

		// Other delimiter (options)
		if($_content['other_delimiter']) $_content['delimiter'] = $_content['other_delimiter'];

		// handle selector
		if($selected_plugin) {
			$content['plugin'] = $selected_plugin;
			$plugin_object = new $selected_plugin;

			$content['description'] = $plugin_object->get_description();

			// fill options tab
 			if(method_exists($plugin_object, 'get_selectors_html')) {
				$content['plugin_options_html'] = $plugin_object->get_options_html();
			} else {
				$options = $plugin_object->get_options_etpl($definition);
				if(is_array($options)) {
					$content['plugin_options_template'] = $options['name'];
					$content += (array)$options['content'];
					$sel_options += (array)$options['sel_options'];
					$readonlys += (array)$options['readonlys'];
					$preserv += (array)$options['preserv'];
				} else {
					$content['plugin_options_template'] = $options;
				}
			}
		}

		// fill selection tab
		if($definition && is_array($definition->plugin_options) && $definition->plugin_options['selection'] && !$content['selection_passed']) {
			$_selection = $definition->plugin_options['selection'];
		}
		
		if ($_selection && ($content['old_definition'] == $content['definition'] || $content['selection_passed'])) {
			$readonlys[$tabs]['selection_tab'] = true;
			$content['selection'] = $_selection;
			$preserv['selection'] = $_selection;
		}
		elseif ($plugin_object) {
 			if(method_exists($plugin_object, 'get_selectors_html')) {
				$content['plugin_selectors_html'] = $plugin_object->get_selectors_html();
			} else {
				$options = $plugin_object->get_selectors_etpl($definition);
				if(is_array($options)) {
					$content += is_array($options['content']) ? $options['content'] : array('selection' => $options['content']);
					$sel_options += (array)$options['sel_options'];
					$readonlys['selection'] = (array)$options['readonlys'];
					$preserv['selection'] = (array)$options['preserv'];
					$content['plugin_selectors_template'] = $options['name'];
				} else {
					$content['plugin_selectors_template'] = $options;
				}
			}
			if(!$content['plugin_selectors_html'] && !$content['plugin_selectors_template']) {
				$readonlys[$tabs]['selection_tab'] = true;
			}
			$content['filter'] = $definition->filter;
			$content['filter']['fields'] = importexport_helper_functions::get_filter_fields($_appname, $selected_plugin);
			if(!$content['filter']['fields'])
			{
				$this->js->set_onload("\$j('input[value=\"filter\"]').parent().hide();");
				$content['no_filter'] = true;
			}
			else
			{
				// Process relative dates into the current absolute date
				foreach($content['filter']['fields'] as $field => $settings)
				{
					if($content['filter'][$field] && strpos($settings['type'],'date') === 0)
					{
						$content['filter'][$field] = importexport_helper_functions::date_rel2abs($content['filter'][$field]);
					}
				}
			}
		} elseif (!$_selection) {
			$this->js->set_onload("
				disable_button('exec[preview]');
				disable_button('exec[export]');
			");
		}

		$preserv['old_definition'] = $content['definition'];

		// If not set by plugin, pre-set selection to filter if definition has one, or 'search'
		if (!$content['selection'] && $definition->filter)
		{
			$content['selection'] = 'filter';
		}
		if(!$content['selection'])
		{
			$content['selection'] = 'search';
		}

		// Disable / hide definition filter if not selected
		if($content['selection'] != 'filter')
		{
			$this->js->set_onload("
				\$j('div.filters').hide();
			");
		}
		unset ($plugin_object);
		$apps = importexport_helper_functions::get_apps('export');
		//error_log(__METHOD__.__LINE__.array2string($apps));
		if (empty($apps)) throw new Exception('Error: no application profiles available for export');
		if (!is_array($apps) && $apps) $apps = (array)$apps;
		$sel_options['appname'] = array('' => lang('Select one')) + array_combine($apps,$apps);
		if(!$_application && !$selected_plugin) {
			$content['plugin_selectors_html'] = $content['plugin_options_html'] =
					lang('You need to select an app and format first!');
			$this->js->set_onload("document.getElementById('importexport.export_dialog.options_tab-tab').style.visibility='hidden';");
			$this->js->set_onload("document.getElementById('importexport.export_dialog.selection_tab-tab').style.visibility='hidden';");
		}

		// disable preview box
		$this->js->set_onload('$j(\'tr.preview-box\').hide();');


		//xajax_eT_wrapper submit
		if(class_exists('xajaxResponse'))
		{
			//error_log(__LINE__.__FILE__.'$_content: '.print_r($_content,true));
			$response = new xajaxResponse();

			if ($_content['definition'] == 'expert') {
				$definition = new importexport_definition();
				$definition->definition_id	= $_content['definition_id'] ? $_content['definition_id'] : '';
				$definition->name		= $_content['name'] ? $_content['name'] : '';
				$definition->application	= $_content['appname'];
				$definition->plugin		= $_content['plugin'];
				$definition->type		= 'export';
				$definition->allowed_users	= $_content['allowed_users'] ? $_content['allowed_users'] : $this->user;
				$definition->owner		= $_content['owner'] ? $_content['owner'] : $this->user;
			}
			else {
				$definition = new importexport_definition($_content['definition']);
			}

			if(!is_array($definition->plugin_options)) {
				$definition->plugin_options = array(
					'mapping'	=>	array()
				);
			}

			// Set filter
			// Note that because not all dates are DB dates, the plugin has to handle them
			$filter = $definition->filter;
			if(is_array($_content['filter']))
			{
				foreach($_content['filter'] as $key => $value)
				{
					// Handle multiple values
					if(!is_array($value) && strpos($value,',') !== false) $value = explode(',',$value);

					$filter[$key] = $value;

					// Skip empty values or empty ranges
					if($value == "" || is_null($value) || (is_array($value) && count($value) == 0) || is_array($value) && array_key_exists('from',$value) && !$value['from'] && !$value['to'] )
					{
						unset($filter[$key]);
					}
					// If user selects an end date, they most likely want entries including that date
					if(is_array($value) && array_key_exists('to',$value) && $value['to'] )
					{
						// Adjust time to 23:59:59
						$filter[$key]['to'] = mktime(23,59,59,date('n',$value['to']),date('j',$value['to']),date('Y',$value['to']));
					}
				}
			}
			unset($_content['filter']);
			$definition->filter = $filter;

			$definition->plugin_options = array_merge(
				$definition->plugin_options,
				$_content
			);

			if(!$definition->plugin_options['selection']) {
				$response->addScript('alert("' . lang('No records selected') . '");');
				return $response->getXML();
			}

			$tmpfname = tempnam($GLOBALS['egw_info']['server']['temp_dir'],'export');
			$file = fopen($tmpfname, "w+");
			if (! $charset = $definition->plugin_options['charset']) {
				$charset = $GLOBALS['egw']->translation->charset();
			}
			if($charset == 'user')
			{
				switch($definition->plugin)
				{
					case 'addressbook_export_vcard':
						$charset = $GLOBALS['egw_info']['user']['preferences']['addressbook']['vcard_charset'];
						break;
					default:
						$charset = $GLOBALS['egw_info']['user']['preferences']['common']['csv_charset'];
				}
			}
			$plugin_object = new $definition->plugin;
			$result = $plugin_object->export( $file, $definition );

			if(is_object($result) && method_exists($result, 'get_num_of_records'))
			{
				$record_count = $result->get_num_of_records();
				if($record_count == 0)
				{
					$response->addScript('alert("' . lang('No matching records') . '");');
					return $response->getXML();
				}
			}

			// Store charset to use in header
			egw_cache::setSession('importexport', $tmpfname, $charset, 100);

			if($_content['export'] == 'pressed') {
				fclose($file);
				$filename = pathinfo($tmpfname, PATHINFO_FILENAME);
				$response->addScript("xajax_eT_wrapper();");
				$link_query = array(
					'menuaction'	=> 'importexport.importexport_export_ui.download',
					'_filename'	=> $filename,
					'_appname'	=> $definition->application,
					'_suffix'	=> $plugin_object->get_filesuffix(),
					'_type'		=> $plugin_object->get_mimetype()
				);

				// Allow plugins to suggest a file name - return false if they have no suggestion
				if(method_exists($plugin_object, 'get_filename') && $plugin_filename = $plugin_object->get_filename())
				{
					$link_query['filename'] = $plugin_filename;
				}
				$response->addScript("opener.location.href='". $GLOBALS['egw']->link('/index.php',$link_query)."'");
				$response->addScript('window.setTimeout("window.close();", 100);');
				return $response->getXML();
			}
			elseif($_content['preview'] == 'pressed') {
				fseek($file, 0);
				$item_count = 1;
				$preview = '';
				$search = array('[\016]','[\017]',
								'[\020]','[\021]','[\022]','[\023]','[\024]','[\025]','[\026]','[\027]',
								'[\030]','[\031]','[\032]','[\033]','[\034]','[\035]','[\036]','[\037]');
				$replace = $preview = '';

				while(!feof($file) && $item_count < 10) {
					$preview .= preg_replace($search,$replace,fgets($file,1024));
					$item_count++;
				}

				fclose($file);
				unlink($tmpfname);

				// Convert back to system charset for display
				$preview = $GLOBALS['egw']->translation->convert( $preview,
					$charset,
					$GLOBALS['egw']->translation->charset()
				);

				if($record_count)
				{
					$preview = "<div class='header'>".lang('Preview') . "<span class='count'>$record_count</span></div>".$preview;
				}
				$response->addAssign('exec[preview-box]','innerHTML',nl2br($preview));
				$response->jquery('.preview_box','show');

				$response->addScript("xajax_eT_wrapper();");
				return $response->getXML();
			}
			//nothing else expected!
			throw new Exception('Error: unexpected submit in export_dialog!');
		} else {
			$readonlys[$tabs]['selection'] = true;
			$readonlys[$tabs]['selection'] = false;
		}
		//error_log(print_r($content,true));
		return $et->exec(self::_appname. '.importexport_export_ui.export_dialog',$content,$sel_options,$readonlys,$preserv,2);
	}

	public function ajax_get_definitions($_appname, xajaxResponse &$response = null) {
		if(is_null($response)) {
			$response = new xajaxResponse();
		} else {
			$no_return = true;
		}
		if (!$_appname) {
			$response->jquery('tr.select_definition','hide');
			return $no_return ? '' : $response->getXML();
		}

		$definitions = new importexport_definitions_bo(array(
			'type' => 'export',
			'application' => $_appname
		));
		$response->addScript("clear_options('exec[definition]');");
		foreach ((array)$definitions->get_definitions() as $identifier) {
			try {
				$definition = new importexport_definition($identifier);
			} catch (Exception $e) {
				// Permission error
				continue;
			}
				if ($title = $definition->get_title()) {
					if (!$selected_plugin) $selected_plugin = $title;
					$response->addScript("selectbox_add_option('exec[definition]','$title', '$value',".($selected_plugin == $title ? 'true' : 'false').");");
				}
				unset($definition);
		}
		unset($definitions);
		$response->addScript("selectbox_add_option('exec[definition]','" . lang('Expert options') . "', 'expert',".($selected_plugin == $title ? 'true' : 'false').");");

		if($selected_plugin == 'expert') {
			$this->ajax_get_plugins($_appname, $response);
		} else {
			$response->jquery('tr.select_plugin','hide');
		}
		$response->addScript('export_dialog.change_definition(document.getElementById("exec[definition]"));');
		$response->addScript('$j("tr.select_definition").css("display","table-row");');
		return $no_return ? '' : $response->getXML();
	}

	public function ajax_get_plugins($_appname, xajaxResponse &$response = null) {
		if(!is_null($response)) {
			$no_return = true;
		} else {
			$response = new xajaxResponse();
		}
		if (!$_appname) {
			$response->jquery('tr.select_plugin','hide');
			return $no_return ? '' : $response->getXML();
		}

		(array)$plugins = importexport_helper_functions::get_plugins($_appname,'export');
		$sel_options['plugin'] = '';
		$response->addScript("clear_options('exec[plugin]');");
		foreach ($plugins[$_appname]['export'] as $plugin => $plugin_name) {
			if (!$selected_plugin) $selected_plugin = $plugin;
			$response->addScript("selectbox_add_option('exec[plugin]','$plugin_name', '$plugin',".($selected_plugin == $plugin ? 'true' : 'false').");");
		}

		$this->ajax_get_plugin_description($selected_plugin,$response);
		$this->ajax_get_plugin_options($selected_plugin, $response, $_definition);
		$this->ajax_get_plugin_selectors($selected_plugin, $response, $_definition);
		$response->addScript('$j("tr.select_plugin").css("display","table-row");');
		return $no_return ? '' : $response->getXML();
	}

	public function ajax_get_definition_description($_definition, xajaxResponse &$response=null) {
		$no_return = !is_null($response);
		if(is_null($response)) {
			$response = new xajaxResponse();
		}
		if (!$_definition) return $response->getXML();
		$_object = new importexport_definition($_definition);
		if (is_a($_object, 'importexport_definition')) {
			$description = $_object->description;
			$response->assign('exec[plugin_description]','innerHTML',$description);
		}
		unset ($_object);

		return $no_return ? '' : $response->getXML();
	}

	public function ajax_get_plugin_description($_plugin,&$_response=false) {
		$no_return = !is_null($_response);
		if(is_null($_response)) {
			$_response = new xajaxResponse();
		}
		if (!$_plugin) return $no_return ? '' : $response->getXML();

		$plugin_object = new $_plugin;
		if (is_a($plugin_object, 'importexport_iface_export_plugin')) {
			$description = $plugin_object->get_description();
			$_response->addAssign('exec[plugin_description]','innerHTML',$description);

			if (isset($definition->plugin_options['selection'])) {
				$_response->addScript("document.getElementById('importexport.export_dialog.selection_tab-tab').style.visibility='hidden';");
			}
			$this->ajax_get_plugin_options($_plugin, $_response);
		}
		unset ($plugin_object);

		return $no_return ? '' : $response->getXML();
	}

	public function ajax_get_plugin_options($_plugin,&$response=false, $definition = '') {
		$no_return = !is_null($response);
		if(is_null($response)) {
			$response = new xajaxResponse();
		}
		if (!$_plugin) return $no_return ? '' : $response->getXML();

		$plugin_object = new $_plugin;
		if (is_a($plugin_object, 'importexport_iface_export_plugin')) {
			$options = $plugin_object->get_options_etpl();
			ob_start();
			$template = new etemplate($options);
/*
			$template->exec('importexport.importexport_export_ui.dialog', array(), array(), array(), array(), 2);
			$html = ob_get_clean();
			ob_end_clean();
*/
			$html = $template->exec('importexport.importexport_export_ui.dialog', array(), array(), array(), array(), 1);
			$html = preg_replace('|<input.+id="etemplate_exec_id".*/>|',
				'',
				$html
			);
			$response->addAssign('importexport.export_dialog.options_tab', 'innerHTML', $html);
		}

		unset ($plugin_object);

		return $no_return ? '' : $response->getXML();
	}

	/**
	 * downloads file to client and deletes it.
	 *
	 * @param sting $_tmpfname
	 * @todo we need a suffix atibute in plugins e.g. .csv
	 */
	public function download($_tmpfname = '') {
		$tmpfname = $_tmpfname ? $_tmpfname : $_GET['_filename'];
		$tmpfname = $GLOBALS['egw_info']['server']['temp_dir'] .'/'. $tmpfname;
		if (!is_readable($tmpfname)) die();

		$appname = $_GET['_appname'];
		$nicefname = $_GET['filename'] ? $_GET['filename'] : 'egw_export_'.$appname.'-'.date('Y-m-d');

		// Turn off all output buffering
		while (@ob_end_clean());

		$file = fopen($tmpfname,'rb');

		// Get charset
		$charset = egw_cache::getSession('importexport', $tmpfname);

		html::content_header($nicefname.'.'.$_GET['_suffix'],
			($_GET['_type'] ? $_GET['_type'] : 'application/text') . ($charset ? '; charset='.$charset : ''),
			filesize($tmpfname));
		fpassthru($file);

		unlink($tmpfname);

		// Try to avoid any extra finishing output
		common::egw_exit();
	}

	public function ajax_get_plugin_selectors($_plugin,&$response=false, $definition = '') {
		$no_return = !is_null($response);
		if(is_null($response)) {
			$response = new xajaxResponse();
		}
		if (!$_plugin) return $no_return ? '' : $response->getXML();

		$plugin_object = new $_plugin;
		if (is_a($plugin_object, 'importexport_iface_export_plugin')) {
			$options = $plugin_object->get_selectors_etpl();
			ob_start();
			etemplate::$name_vars='exec';
			$template = new etemplate($options);
			$html = $template->exec('importexport.importexport_export_ui.dialog', array(), array(), array(), array(), 1);
			//$html = ob_get_clean();
			ob_end_clean();
			$pattern = array(
				'|<input.+id="etemplate_exec_id".*/>|',
				'|<input(.+)name="exec[0-9]*\[|'
			);
			$html = preg_replace($pattern,
				array('', '<input\\1name="exec['),
				$html
			);
			$response->addAssign('importexport.export_dialog.selection_tab', 'innerHTML', $html);
		}

		unset ($plugin_object);

		return $no_return ? '' : $response->getXML();
	}

	public function ajax_get_template($_name) {

	}
} // end class uiexport
