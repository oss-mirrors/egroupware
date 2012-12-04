<?php
/**
 * eGroupWare - resources
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package resources
 * @link http://www.egroupware.org
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @author Lukas Weiss <wnz_gh05t@users.sourceforge.net>
 * @version $Id$
 */

/**
 * General userinterface object for resources
 *
 * @package resources
 */
class resources_ui
{
	var $public_functions = array(
		'index'		=> True,
		'edit'		=> True,
		'select'	=> True,
		'writeLangFile'	=> True
	);

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
// 		print_r($GLOBALS['egw_info']); die();
		$this->tmpl	= new etemplate('resources.show');
		$this->bo	= new resources_bo();
// 		$this->calui	= CreateObject('resources.ui_calviews');

	}

	/**
	 * main resources list.
	 *
	 * Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * @param array $content content from eTemplate callback
	 *
	 */
	function index($content='')
	{
		if (is_array($content))
		{
			$sessiondata = $content['nm'];
			unset($sessiondata['rows']);
			$GLOBALS['egw']->session->appsession('session_data','resources_index_nm',$sessiondata);

			if (isset($content['btn_delete_selected']))
			{
				foreach($content['nm']['rows'] as $row)
				{
					if($res_id = $row['checkbox'][0])
					{
						$msg .= '<p>'. $this->bo->delete($res_id). '</p><br>';
					}
				}
				return $this->index($msg);
			}
			foreach($content['nm']['rows'] as $row)
			{
				if(isset($row['delete']))
				{
					$res_id = array_search('pressed',$row['delete']);
					return $this->index($this->bo->delete($res_id));
				}
				if(isset($row['view_acc']))
				{
					$sessiondata['filter2'] = array_search('pressed',$row['view_acc']);
					$GLOBALS['egw']->session->appsession('session_data','resources_index_nm',$sessiondata);
					return $this->index();
				}
			}
			if ($content['nm']['action'])
			{
				if (!count($content['nm']['selected']) && !$content['nm']['select_all'])
				{
					$msg = lang('You need to select some entries first!');
				}
				else
				{
					if ($this->action($content['nm']['action'],$content['nm']['selected'],$content['nm']['select_all'],
						$success,$failed,$action_msg,'resources_index_nm',$msg))
					{
						$msg .= lang('%1 resource(s) %2',$success,$action_msg);
					}
					elseif(empty($msg))
					{
						$msg .= lang('%1 resource(s) %2, %3 failed because of insufficent rights !!!',$success,$action_msg,$failed);
					}
					else
					{
						$msg .= lang('%1 resource(s) %2, %3 failed',$success,$action_msg,$failed);
					}
				}
			}
		} else {
			$msg = $content;
		}
		$content = array();
		$content['msg'] = $msg;

		$content['nm']['header_left']	= 'resources.resource_select.header';
		$content['nm']['header_right']	= 'resources.show.nm_right';
		$content['nm']['get_rows'] 	= 'resources.resources_bo.get_rows';
		$content['nm']['no_filter'] 	= False;
		$content['nm']['filter_label']	= lang('Category');
		$content['nm']['filter2_label']	= 'Display';
		$content['nm']['filter_no_lang'] = true;
		$content['nm']['no_cat']	= true;
		$content['nm']['bottom_too']	= true;
		$content['nm']['order']		= 'name';
		$content['nm']['sort']		= 'ASC';
		$content['nm']['store_state']	= 'get_rows';
		$content['nm']['row_id']	= 'res_id';

		$nm_session_data = $GLOBALS['egw']->session->appsession('session_data','resources_index_nm');
		if($nm_session_data)
		{
			$content['nm'] = $nm_session_data;
		}
		$content['nm']['options-filter']= array(''=>lang('all categories'))+(array)$this->bo->acl->get_cats(EGW_ACL_READ);
		$content['nm']['options-filter2'] = resources_bo::$filter_options;

		if($_GET['search']) {
			$content['nm']['search'] = $_GET['search'];
		}
		if($_GET['view_accs_of'])
		{
			$content['nm']['filter2'] = (int)$_GET['view_accs_of'];
		}
		$content['nm']['actions']	= $this->get_actions();

		// check if user is permitted to add resources
		if(!$this->bo->acl->get_cats(EGW_ACL_ADD))
		{
			$no_button['add'] = true;
		}
		$no_button['back'] = true;
		$GLOBALS['egw_info']['flags']['app_header'] = lang('resources');

		$GLOBALS['egw_info']['flags']['java_script'] .= "<script LANGUAGE=\"JavaScript\">
			function js_btn_book_selected(form)
			{
				resources = '';

				el = form.getElementsByTagName(\"input\");
				for (var i = 0; i < el.length; i++)
				{
					if(el[i].name.substr(el[i].name.length-12,el[i].name.length) == '[checkbox][]' && el[i].checked)
					{
						if(resources.length > 0)
						{
							resources += ',';
						}
						resources += 'r' + el[i].value;
					}
				}
				if(resources.length == 0)
				{
					alert('". lang('No resources selected'). "');
					return false;
				}
				return resources;
			}
		</script>";

		egw_framework::validate_file('.','resources','resources');

		if($content['nm']['filter2'] > 0)
		{
			$master = $this->bo->so->read(array('res_id' => $content['nm']['filter2']));
			$content['nm']['options-filter2'] = resources_bo::$filter_options + array(
				$master['res_id'] => lang('accessories of') . ' ' . $master['name']
			);
			$content['nm']['get_rows'] 	= 'resources.resources_bo.get_rows';
			$GLOBALS['egw_info']['flags']['app_header'] = lang('resources') . ' - ' . lang('accessories of '). ' '. $master['name'] .
				($master['short_description'] ? ' [' . $master['short_description'] . ']' : '');
		}
		$preserv = $content;

		$options = array();

		$GLOBALS['egw']->session->appsession('session_data','resources_index_nm',$content['nm']);
		$this->tmpl->read('resources.show');
		return $this->tmpl->exec('resources.resources_ui.index',$content,$sel_options,$no_button,$preserv);
	}

	/**
	 * Get actions / context menu for index
	 *
	 * @return array see nextmatch_widget::egw_actions()
	 */
	protected function get_actions()
	{
		$actions = array(
			'edit' => array(
				'default' => true,
				'caption' => 'open',
				'allowOnMultiple' => false,
				'url' => 'menuaction=resources.resources_ui.edit&res_id=$id',
				'popup' => egw_link::get_registry('resources', 'add_popup'),
				'group' => $group=1,
				'disableClass' => 'rowNoEdit',
			),
			'add' => array(
				'caption' => 'New resource',
				'url' => 'menuaction=resources.resources_ui.edit',
				'popup' => egw_link::get_registry('resources', 'add_popup'),
				'group' => $group,
			),
			'view-acc' => array(
				'caption' => 'View accessories',
				'icon' => 'view_acc',
				'allowOnMultiple' => false,
				'url' => 'menuaction=resources.resources_ui.index&view_accs_of=$id',
				'group' => $group,
				'enableClass' => 'hasAccessories'
			),
			'new_accessory' => array(
				'caption' => 'New accessory',
				'icon' => 'new',
				'group' => $group,
				'url' => 'menuaction=resources.resources_ui.edit&res_id=0&accessory_of=$id',
				'popup' => egw_link::get_registry('resources', 'add_popup'),
				'disableClass' => 'no_new_accessory',
				'allowOnMultiple' => false
			),


			'select_all' => array(
				'caption' => 'Whole query',
				'checkbox' => true,
				'hint' => 'Apply the action on the whole query, NOT only the shown entries',
				'group' => ++$group,
			),
			'view-calendar' => array(
				'caption' => 'View calendar',
				'icon' => 'calendar/planner',
				'group' => ++$group,
				'allowOnMultiple' => true,
				'disableClass' => 'no_view_calendar',
			),
			'book' => array(
				'caption' => 'Book resource',
				'icon' => 'navbar',
				'group' => $group,
				'allowOnMultiple' => true,
				'disableClass' => 'no_book',
			),
/*
			'documents' => resources_merge::document_action(
				$GLOBALS['egw_info']['user']['preferences']['resources']['document_dir'],
				++$group, 'Insert in document', 'document_',
				$GLOBALS['egw_info']['user']['preferences']['resources']['default_document']
			),
*/
			'delete' => array(
				'caption' => 'Delete',
				'group' => ++$group,
				'disableClass' => 'no_delete',
				'nm_action' => 'open_popup',
				'hideOnDisabled' => true
			),
		);
		return $actions;
	}

	/**
	 * apply an action to multiple timesheets
	 *
	 * @param string/int $action 'status_to',set status to timeshhets
	 * @param array $checked timesheet id's to use if !$use_all
	 * @param boolean $use_all if true use all timesheets of the current selection (in the session)
	 * @param int &$success number of succeded actions
	 * @param int &$failed number of failed actions (not enought permissions)
	 * @param string &$action_msg translated verb for the actions, to be used in a message like %1 timesheets 'deleted'
	 * @param string/array $session_name 'index' or 'email', or array with session-data depending if we are in the main list or the popup
	 * @return boolean true if all actions succeded, false otherwise
	 */
	function action($action,$checked,$use_all,&$success,&$failed,&$action_msg,$session_name,&$msg)
	{
		$success = $failed = 0;
		if ($use_all)
		{
			// get the whole selection
			$query = is_array($session_name) ? $session_name : $GLOBALS['egw']->session->appsession('session_data', $session_name);

			@set_time_limit(0);                     // switch off the execution time limit, as it's for big selections to small
			$query['num_rows'] = -1;        // all
			$this->bo->get_rows($query,$resources,$readonlys);
			foreach($resources as $resource)
			{
				$checked[] = $resource['res_id'];
			}
		}
		//echo __METHOD__."('$action', ".array2string($checked).', '.array2string($use_all).",,, '$session_name')";

		// Dialogs to get options
		list($action, $settings) = explode('_', $action, 2);

		switch($action)
		{
			case 'view-calendar':
				echo "window.location = '".egw::link('/index.php',$url_params);
				$resource_ids = array(0);
				$url_params = array(
					'menuaction' => 'calendar.calendar_uiviews.planner',
					'sortby' => 'user',
				);
				foreach($checked as $n => $id)
				{
					$resource_ids[] = 'r'.$id;
				}
				$url_params['owner'] = implode(',',$resource_ids);
				$success = count($resource_ids);
				egw_framework::set_onload("window.location = '".egw::link('/index.php',$url_params).'\';');
				$action_msg = lang('view calendar');
				break;
			case 'book':
				$resource_ids = array();
				$url_params = array(
					'menuaction' => 'calendar.calendar_uiforms.edit'
				);
				foreach($checked as $n => $id)
				{
					$resource_ids[] = 'r'.$id;
				}
				$url_params['participants'] = implode(',',$resource_ids);
				$success = count($resource_ids);
				egw_framework::set_onload("egw_openWindowCentered2('".egw::link('/index.php',$url_params) ."','_blank');");
				$action_msg = lang('booked');
				break;
			case 'delete':
				$action_msg = lang('deleted');
				$promoted_accessories = 0;
				foreach($checked as $n => &$id)
				{
					if($settings == 'promote')
					{
						// Handle a selected accessory
						$resource = $this->bo->read($id);
						if($resource['accessory_of'] > 0)
						{
							$resource['accessory_of'] = -1;
							$this->bo->save($resource);
							$promoted_accessories++;
							continue;
						}
						
						// Make associated accessories into resources
						$accessories = $this->bo->get_acc_list($id);
						foreach($accessories as $acc_id => $name)
						{
							$acc = $this->bo->read($acc_id);
							$acc['accessory_of'] = -1;
							$this->bo->save($acc);
							$promoted_accessories++;

							// Don't need to process these ones now
							$checked_key = array_search($acc_id, $checked);
							if($checked_key !== false) unset($checked[$checked_key]);
						}
					}
					$error = $this->bo->delete($id);
					if (!$error)
					{
						$success++;
					}
					else
					{
						$msg = $error . "\n";
						$failed++;
					}
				}
				if($promoted_accessories) $action_msg .= ", " . lang('%1 accessories now resources',$promoted_accessories);
				break;
		}
		return $failed == 0;
	}

	/**
	 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
	 * invokes add or edit dialog for resources
	 *
	 * @param $content   Content from the eTemplate Exec call or id on inital call
	 */
	function edit($content=0,$accessory_of = -1)
	{
		if (is_array($content))
		{
			list($button) = @each($content['button']);
			unset($content['button']);
			switch($button)
			{
				case 'save':
				case 'apply':
					unset($content['save']);
					unset($content['apply']);
// 					if($content['id'] != 0)
// 					{
// 						// links are already saved by eTemplate
// 						unset($resource['link_to']['to_id']);
// 					}
					$result = $this->bo->save($content);
					if(is_numeric($result))
					{
						$content['res_id'] = $result;
					}
					else
					{
						$content['msg'] = $result;
					}
					break;
				case 'delete':
					unset($content['delete']);
					$content['msg'] = $this->bo->delete($content['res_id']);
					break;
			}
			$js = "opener.egw_refresh('".str_replace("'","\\'",$content['msg'])."','addressbook',{$content['res_id']});";
			if($button != 'apply' && !$content['msg'])
			{
				$js .= 'window.close();';
				echo "<html><body><script>$js</script></body></html>\n";
				$GLOBALS['egw']->common->egw_exit();
			}
			else
			{
				$GLOBALS['egw_info']['flags']['java_script'] .= "<script>$js</script>";
			}
		}

		$nm_session_data = $GLOBALS['egw']->session->appsession('session_data','resources_index_nm');
		$res_id = is_numeric($content) ? (int)$content : $content['res_id'];
		if (isset($_GET['res_id'])) $res_id = $_GET['res_id'];
		if (isset($nm_session_data['filter2']) && $nm_session_data['filter2'] > 0) $accessory_of = $nm_session_data['filter2'];
		if (isset($_GET['accessory_of'])) $accessory_of = $_GET['accessory_of'];
		$content = array('res_id' => $res_id);

		if ($res_id > 0)
		{
			$content = $this->bo->read($res_id);
			$content['gen_src_list'] = strpos($content['picture_src'],'.') !== false ? $content['picture_src'] : false;
			$content['picture_src'] = strpos($content['picture_src'],'.') !== false ? 'gen_src' : $content['picture_src'];
			$content['link_to'] = array(
				'to_id' => $res_id,
				'to_app' => 'resources'
			);
		} elseif ($accessory_of > 0) {
			// Pre-set according to parent
			$owner = $this->bo->read($accessory_of);
			if($owner['accessory_of'] > 0)
			{
				// Accessory of accessory not allowed, grab parent resource
				$accessory_of = $owner['accessory_of'];
				$owner = $this->bo->read($accessory_of);
			}
			$content['cat_id'] = $owner['cat_id'];
			$content['bookable'] = true;
		} else {
			// New resource
			$content['cat_id'] = $nm_session_data['filter'];
			$content['bookable'] = true;
		}
		if ($_GET['msg']) $content['msg'] = strip_tags($_GET['msg']);
	
		// some presetes
		$content['resource_picture'] = $this->bo->get_picture($content['res_id'],$content['picture_src'],$size=true);
		$content['quantity'] = $content['quantity'] ? $content['quantity'] : 1;
		$content['useable'] = $content['useable'] ? $content['useable'] : 1;
		$content['accessory_of'] = $content['accessory_of'] ? $content['accessory_of'] : $accessory_of;

		$sel_options['gen_src_list'] = $this->bo->get_genpicturelist();
		$sel_options['cat_id'] =  $this->bo->acl->get_cats(EGW_ACL_ADD);
		$sel_options['cat_id'] = count($sel_options['cat_id']) == 1 ? $sel_options['cat_id'] :
			array('' => lang('select one')) + $sel_options['cat_id'];
		if($accessory_of > 0 || $content['accessory_of'] > 0)
		{
			$content['accessory_of'] = $content['accessory_of'] ? $content['accessory_of'] : $accessory_of;
		}
		$search_options = array('accessory_of' => -1);
		$sel_options['accessory_of'] = array(-1 => lang('none')) + (array)$this->bo->link_query('',$search_options);
		if($res_id) unset($sel_options['accessory_of'][$res_id]);

// 		$content['general|page|pictures|links'] = 'resources.edit_tabs.page';  //debug

		// Permissions
		$read_only = array();
		if($res_id && !$this->bo->acl->is_permitted($content['cat_id'],EGW_ACL_EDIT))
		{
			$read_only['__ALL__'] = true;
		}
		if(!$this->bo->acl->is_permitted($content['cat_id'],EGW_ACL_DELETE))
		{
			$read_only['delete'] = true;
		}

		// Disable custom tab if there are no custom fields defined
		$read_only['tabs']['custom'] = !(config::get_customfields('resources',true));

		$preserv = $content;
		$this->tmpl->read('resources.edit');
		return $this->tmpl->exec('resources.resources_ui.edit',$content,$sel_options,$read_only,$preserv,2);
	}

	/**
	 * select resources
	 *
	 * @author Lukas Weiss <wnz.gh05t@users.sourceforge.net>
	 */
	function select($content='')
	{
		$GLOBALS['phpgw']->js->set_onload("copyOptions('exec[resources][selectbox]');");

		$GLOBALS['egw_info']['flags']['java_script'] .= "<script LANGUAGE=\"JavaScript\">
			window.focus();

			openerid='resources_selectbox';
			id='exec[nm][rows][selectbox]';

			function addOption(label,value,button_id,useable)
			{
				var quantity = document.getElementById(button_id+'[default_qty]').value;
				value = value+':'+quantity;
				if(quantity>useable) {
					alert('".lang('You chose more resources than available')."');
					return false;
				}
				label = label+'['+quantity+'/'+useable+']';
				openerSelectBox = opener.document.getElementById(openerid);
				if (openerSelectBox) {
					select = '';
					for(i=0; i < openerSelectBox.length; i++) {
						with (openerSelectBox.options[i]) {
							if (selected || openerSelectBox.selectedIndex == i) {
								select += (value.slice(0,1)==',' ? '' : ',')+value;
							}
						}
					}
					select += (select ? ',' : '')+value;
					opener.selectbox_add_option(openerid,label,value,0);
				}
				selectBox = document.getElementById(id);
				if (selectBox) {
					var resource_value = value.split(':');
					for (i=0; i < selectBox.length; i++) {
						var selectvalue = selectBox.options[i].value.split(':');
						if (selectvalue[0] == resource_value[0]) {
							selectBox.options[i] = null;
							selectBox.options[selectBox.length] = new Option(label,value,false,true);
							break;
						}
					}
					if (i >= selectBox.length) {
						selectBox.options[selectBox.length] = new Option(label,value,false,true);
					}
				}
			}

			function removeSelectedOptions()
			{
				openerSelectBox = opener.document.getElementById(openerid);
				if (openerSelectBox == null) window.close();
				selectBox = document.getElementById(id);
				for (i=0; i < selectBox.length; i++) {
					if (selectBox.options[i].selected) {
						for (j=0; j < openerSelectBox.length; j++) {
							if (openerSelectBox[j].value == selectBox.options[i].value) {
								openerSelectBox.removeChild(openerSelectBox[j]);
							}
						}
						selectBox.options[i--] = null;
					}
				}
			}

			function copyOptions()
			{
				openerSelectBox = opener.document.getElementById(openerid);
				selectBox = document.getElementById(id);
				for (i=0; i < openerSelectBox.length; i++) {
					with (openerSelectBox.options[i]) {
						if (selected && value.slice(0,1) != ',') {
							selectBox.options[selectBox.length] =  new Option(text,value);
						}
					}
				}
			}

			function oneLineSubmit()
			{
			/*
				openerSelectBox = opener.document.getElementById(openerid);

				if (openerSelectBox) {
					if (openerSelectBox.selectedIndex >= 0) {
						selected = openerSelectBox.options[openerSelectBox.selectedIndex].value;
						if (selected.slice(0,1) == ',') selected = selected.slice(1);
						opener.selectbox_add_option(openerid,'multiple*',selected,1);
					}
					else {
						for (i=0; i < openerSelectBox.length; i++) {
							with (openerSelectBox.options[i]) {
								if (selected) {
									opener.selectbox_add_option(openerid,text,value,1);
									break;
								}
							}
						}
					}
				}
			*/
				window.close();
			}</script>";

		if (!is_array($content))
		{
			if (!($content['nm'] = egw_cache::getSession('resources','get_rows')))
			{
				$content['nm'] = array(
					'header_left'   => 'resources.resource_select.header',
					'show_bookable' => true,
					'get_rows' 	    => 'resources.resources_bo.get_rows',
					'filter_label'	=> 'Category',
					'filter_help'	=> lang('Select a category'),
					'options-filter'=> array(''=>lang('all categories'))+(array)$this->bo->acl->get_cats(EGW_ACL_READ),
					'no_filter2'	=> true,
					'filter_no_lang'=> true,
					'no_cat'	    => true,
					'rows'          => array('js_id' => 1),
					'csv_fields'    => false,
					'default_cols'  => 'name,cat_id,quantity',	// I  columns to use if there's no user or default pref
					'store_state' => 'get_rows',	// store in session as for location get_rows
				);
				$content['nm']['filter'] = $GLOBALS['egw_info']['user']['preferences']['resources']['filter'];
			}
		}
		$sel_options = array();
		$no_button = array();
		$this->tmpl->read('resources.resource_select');
		return $this->tmpl->exec('resources.resources_ui.select',$content,$sel_options,$no_button,$preserv,2);
	}

	/**
	 * get_calendar_sidebox
	 * get data für calendar sidebox
	 *
	 * @author Lukas Weiss <wnz_gh05t@users.sourceforge.net>
	 * @param array $param with keys menuaction, owner and optional date and return_array
	 * @return array with: label=>link or array with text
	 */
	function get_calendar_sidebox($param)
	{
		$cats = $this->bo->acl->get_cats(EGW_ACL_CALREAD);
		if (!$cats) return array();

		if(array_key_exists('return_array', $param))
		{
			$return_array = $param['return_array'];
			unset($param['return_array']);
		}

		$owners = explode(',',$param['owner']);
		unset($param['owner']);
		$res_cats = $selected = array();

		// this gets the resource-ids of the cats and implodes them to the array-key of the selectbox,
		// so it is possible to select all resources of a category
		$allowed_list = array();
		foreach($cats as $cat_id => $cat_name)
		{
			if ($resources = $this->bo->so->search(array('cat_id' => $cat_id, 'bookable' => '1'),'res_id'))
			{
				$keys = array();
				foreach($resources as $res)
				{
					$keys[] = 'r'.$res['res_id'];
					$allowed_list[] = $res['res_id'];
				}
				$res_cats[implode(',',$keys)] = $cat_name;

				if (count(array_intersect($keys,$owners)) == count($keys))
				{
					$selected[] = implode(',',$keys);
					$owners = array_diff($owners,$keys);
				}
			}
		}
		// add already selected single resources to the selectbox, eg. call of the resource-calendar from the resources app
		$resources = array('r0' => lang('none'));
		$res_ids = array();
		foreach($owners as $key => $owner)
		{
			if ($owner{0} == 'r')
			{
				$res_ids[] = (int) substr($owner,1);
				$selected[] = $owner;
			}
		}
		// Take out resources not allowed by perms, above
		$res_ids = array_intersect($res_ids,$allowed_list);
		if (count($res_ids))
		{
			foreach($this->bo->so->search(array('res_id' => $res_ids),'res_id,name') as $data)
			{
				$resources['r'.$data['res_id']] = $data['name'];
			}
		}
		if(!isset($return_array))
		{
			$selectbox = html::select(
				'owner',
				$selected,
				array_merge($resources,$res_cats),
				$no_lang=true,
				$options='style="width: 100%;" onchange="load_cal(\''.
					egw::link('/index.php',$param,false).'\',\'uical_select_resource\',true);" id="uical_select_resource"',
				$multiple=count($selected) ? 4 : 0
			);
			return array(
				array(
					'text' => $selectbox,
					'no_lang' => True,
					'link' => False
				)
			);
		}
		else
		{
			return array_merge($resources,$res_cats);
		}
	}
}

