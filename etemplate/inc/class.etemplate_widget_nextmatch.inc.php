<?php
/**
 * EGroupware - eTemplate serverside implementation of the nextmatch widget
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package etemplate
 * @subpackage api
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker@outdoor-training.de>
 * @copyright 2002-11 by RalfBecker@outdoor-training.de
 * @version $Id$
 */

/**
 * eTemplate serverside implementation of the nextmatch widget
 *
 * $content[$id] = array(	// I = value set by the app, 0 = value on return / output
 * 	'get_rows'       =>		// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
 * 	'filter_label'   =>		// I  label for filter    (optional)
 * 	'filter_help'    =>		// I  help-msg for filter (optional)
 * 	'no_filter'      => True// I  disable the 1. filter
 * 	'no_filter2'     => True// I  disable the 2. filter (params are the same as for filter)
 * 	'no_cat'         => True// I  disable the cat-selectbox
 *  'cat_app'        =>     // I  application the cat's should be from, default app in get_rows
 *  'cat_is_select'  =>     // I  true||'no_lang' use selectbox instead of category selection, default null
 * 	'template'       =>		// I  template to use for the rows, if not set via options
 * 	'header_left'    =>		// I  template to show left of the range-value, left-aligned (optional)
 * 	'header_right'   =>		// I  template to show right of the range-value, right-aligned (optional)
 * 	'bottom_too'     => True// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
 *	'never_hide'     => True// I  never hide the nextmatch-line if less then maxmatch entries
 *  'lettersearch'   => True// I  show a lettersearch
 *  'searchletter'   =>     // I0 active letter of the lettersearch or false for [all]
 * 	'start'          =>		// IO position in list
 *	'num_rows'       =>     // IO number of rows to show, defaults to maxmatches from the general prefs
 * 	'cat_id'         =>		// IO category, if not 'no_cat' => True
 * 	'search'         =>		// IO search pattern
 * 	'order'          =>		// IO name of the column to sort after (optional for the sortheaders)
 * 	'sort'           =>		// IO direction of the sort: 'ASC' or 'DESC'
 * 	'col_filter'     =>		// IO array of column-name value pairs (optional for the filterheaders)
 * 	'filter'         =>		// IO filter, if not 'no_filter' => True
 * 	'filter_no_lang' => True// I  set no_lang for filter (=dont translate the options)
 *	'filter_onchange'=> 'this.form.submit();' // I onChange action for filter, default: this.form.submit();
 * 	'filter2'        =>		// IO filter2, if not 'no_filter2' => True
 * 	'filter2_no_lang'=> True// I  set no_lang for filter2 (=dont translate the options)
 *	'filter2_onchange'=> 'this.form.submit();' // I onChange action for filter2, default: this.form.submit();
 * 	'rows'           =>		//  O content set by callback
 * 	'total'          =>		//  O the total number of entries
 * 	'sel_options'    =>		//  O additional or changed sel_options set by the callback and merged into $tmpl->sel_options
 * 	'no_columnselection' => // I  turns off the columnselection completly, turned on by default
 * 	'columnselection-pref' => // I  name of the preference (plus 'nextmatch-' prefix), default = template-name
 * 	'default_cols'   => 	// I  columns to use if there's no user or default pref (! as first char uses all but the named columns), default all columns
 * 	'options-selectcols' => // I  array with name/label pairs for the column-selection, this gets autodetected by default. A name => false suppresses a column completly.
 *  'return'         =>     // IO allows to return something from the get_rows function if $query is a var-param!
 *  'csv_fields'     =>		// I  false=disable csv export, true or unset=enable it with auto-detected fieldnames or preferred importexport definition,
 * 		array with name=>label or name=>array('label'=>label,'type'=>type) pairs (type is a eT widget-type)
 *		or name of import/export definition
 *  'row_id'         =>     // I  key into row content to set it's value as tr id, eg. 'id'
 *  'actions'        =>     // I  array with actions, see nextmatch_widget::egw_actions
 *  'action_links'   =>     // I  array with enabled actions or ones which should be checked if they are enabled
 *                                optional, default id of all first level actions plus the ones with enabled='javaScript:...'
 *  'action_var'     => 'action'	// I name of var to return choosen action, default 'action'
 *  'action'         =>     //  O string selected action
 *  'selected'       =>     //  O array with selected id's
 *  'checkboxes'     =>     //  O array with checkbox id as key and boolean checked value
 *  'select_all'     =>     //  O boolean value of select_all checkbox, reference to above value for key 'select_all'
 */
class etemplate_widget_nextmatch extends etemplate_widget
{
	public function __construct($xml='')
	{
		if($xml) {
			parent::__construct($xml);

			// TODO: probably a better way to do this
			egw_framework::includeCSS('/phpgwapi/js/egw_action/test/skins/dhtmlxmenu_egw.css');
		}
	}

	/**
	 * Number of rows to send initially
	 */
	const INITIAL_ROWS = 25;

	/**
	 * Set up what we know on the server side.
	 *
	 * Sending a first chunk of rows
	 *
	 * @param string $cname
	 */
	public function beforeSendToClient($cname)
	{
		$attrs = $this->attrs;
		$form_name = self::form_name($cname, $this->id);
		$value =& self::get_array(self::$request->content, $form_name, true);

		$value['start'] = 0;
		$value['num_rows'] = self::INITIAL_ROWS;
		$value['rows'] = array();
		$value['total'] = self::call_get_rows($value, $value['rows'], self::$request->readonlys);
		// todo: no need to store rows in request, it's enought to send them to client

		error_log(__METHOD__."() $this: total=$value[total]");
		//foreach($value['rows'] as $n => $row) error_log("$n: ".array2string($row));

		// set up actions, but only if they are defined AND not already set up (run throught self::egw_actions())
		if (isset($value['actions']) && !isset($value['actions'][0]))
		{
			$template_name = isset($value['template']) ? $value['template'] : $this->attrs['options'];
			if (!is_array($value['action_links'])) $value['action_links'] = array();
			$value['actions'] = self::egw_actions($value['actions'], $template_name, '', $value['action_links']);
		}
	}

	/**
	 * Callback to fetch more rows
	 *
	 * @param string $exec_id identifys the etemplate request
	 * @param array $fetchList array of array with values for keys "startIdx" and "count"
	 * @param array $filters Search and filter parameters, passed to data source
	 * @param string full id of widget incl. all namespaces
	 * @return array with values for keys 'total', 'rows', 'readonlys'
	 */
	static public function ajax_get_rows($exec_id, $fetchList, $filters = array(), $form_name='nm')
	{
		error_log(__METHOD__."('".substr($exec_id,0,10)."...',".array2string($fetchList).','.array2string($filters).",'$form_name')");

		self::$request = etemplate_request::read($exec_id);
		$value = self::get_array(self::$request->content, $form_name, true);
		$value = array_merge($value, $filters);
		$result = array('rows' => array());

		foreach ($fetchList as $entry)
		{
			$value['start'] = $entry['startIdx'];
			$value['num_rows'] = $entry['count'];

			$result['total'] = self::call_get_rows($value, $result['rows'], $result['readonlys']);
		}

		egw_json_response::get()->data($result);
	}

	/**
	 * Calling our callback
	 *
	 * Signature of get_rows callback is either:
	 * a) int get_rows($query,&$rows,&$readonlys)
	 * b) int get_rows(&$query,&$rows,&$readonlys)
	 *
	 * If get_rows is called static (and php >= 5.2.3), it is always b) independent on how it's defined!
	 *
	 * @param array &$value
	 * @param array &$rows on return: rows are indexed by their row-number: $value[start], ..., $value[start]+$value[num_rows]-1
	 * @param array &$readonlys=null
	 * @param object $obj=null (internal)
	 * @param string|array $method=null (internal)
	 * @return int|boolean total items found of false on error ($value['get_rows'] not callable)
	 */
	private static function call_get_rows(array &$value,array &$rows,array &$readonlys=null,$obj=null,$method=null)
	{
		if (is_null($method)) $method = $value['get_rows'];

		if (is_null($obj))
		{
			// allow static callbacks
			if(strpos($method,'::') !== false)
			{
				list($class,$method) = explode('::',$method);

				//  workaround for php < 5.2.3: do NOT call it static, but allow application code to specify static callbacks
				if (version_compare(PHP_VERSION,'5.2.3','>='))
				{
					$method = array($class,$method);
					unset($class);
				}
			}
			else
			{
				list($app,$class,$method) = explode('.',$value['get_rows']);
			}
			if ($class)
			{
				if (!$app && !is_object($GLOBALS[$class]))
				{
					$GLOBALS[$class] = new $class();
				}
				if (is_object($GLOBALS[$class]))	// use existing instance (put there by a previous CreateObject)
				{
					$obj = $GLOBALS[$class];
				}
				else
				{
					$obj = CreateObject($app.'.'.$class);
				}
			}
		}
		if (!is_array($readonlys)) $readonlys = array();
		if(is_callable($method))	// php5.2.3+ static call (value is always a var param!)
		{
			$total = call_user_func_array($method,array(&$value,&$raw_rows,&$readonlys));
		}
		elseif(is_object($obj) && method_exists($obj,$method))
		{
			$total = $obj->$method($value,$raw_rows,$readonlys);
		}
		else
		{
			$total = false;	// method not callable
		}
		/* no automatic fallback to start=0
		if ($method && $total && $value['start'] >= $total)
		{
			$value['start'] = 0;
			$total = self::call_get_rows($value,$rows,$readonlys,$obj,$method);
		}
		*/
		// otherwise we might get stoped by max_excutiontime
		if ($total > 200) @set_time_limit(0);

		// remove empty rows required by old etemplate to compensate for header rows
		$first = null;
		foreach($raw_rows as $n => $row)
		{
			// skip empty rows inserted for each header-line in old etemplate
			if (is_int($n) && is_array($rows))
			{
				if (is_null($first)) $first = $n;
				$rows[$n-$first+$value['start']] = $row;
			}
			elseif(!is_null($first))	// rows with string-keys, after numeric rows
			{
				$rows[$n] = $row;
			}
		}

		//error_log($value['get_rows'].'() returning '.array2string($total).', method = '.array2string($method).', value = '.array2string($value));
		return $total;
	}
	/**
	 * Default maximum lenght for context submenus, longer menus are put as a "More" submenu
	 */
	const DEFAULT_MAX_MENU_LENGTH = 14;

	/**
	 * Return egw_actions
	 *
	 * The following attributes are understood for actions on eTemplate/PHP side:
	 * - string 'id' id of the action (set as key not attribute!)
	 * - string 'caption' name/label or action, get's automatic translated
	 * - boolean 'no_lang' do NOT translate caption, default false
	 * - string 'icon' icon, eg. 'edit' or 'infolog/task', if no app given app of template or API is used
	 * - string 'iconUrl' full url of icon, better use 'icon'
	 * - boolean|string 'allowOnMultiple' should action be shown if multiple lines are marked, or string 'only', default true!
	 * - boolean|string 'enabled' is action available, or string with javascript function to call, default true!
	 * - string 'disableClass' class name to use with enabled='javaScript:nm_not_disableClass'
	 *   (add that css class in get_rows(), if row lacks rights for an action)
	 * - boolena 'hideOnDisabled' hide disabled actions, default false
	 * - string 'type' type of action, default 'popup' for contenxt menus, 'drag' or 'drop'
	 * - boolean 'default' is that action the default action, default false
	 * - array  'children' array with actions of submenu
	 * - int    'group' to group items, default all actions are in one group
	 * - string 'onExecute' javascript to run, default 'javaScript:nm_action',
	 *   which runs action specified in nm_action attribute:
	 * - string 'nm_action'
	 *   + 'alert'  debug action, shows alert with action caption, id and id's of selected rows
	 *   + 'submit' default action, sets nm[action], nm[selected] and nm[select_all]
	 *   + 'location' redirects / set location.href to 'url' attribute
	 *   + 'popup'  opens popup with url given in 'url' attribute
	 * - string 'url' url for location or popup
	 * - string 'target' target for location or popup
	 * - string 'width' for popup
	 * - string 'height' for popup
	 * - string 'confirm' confirmation message
	 * - string 'confirm_multiple' confirmation message for multiple selected, defaults to 'confirm'
	 *
	 * That's what we should return looks JSON encoded like
	 * [
	 * 		{
	 *			"id": "folder_open",
	 *			"iconUrl": "imgs/folder.png",
	 *			"caption": "Open folder",
	 *			"onExecute": "javaScript:nm_action",
	 *			"allowOnMultiple": false,
	 *			"type": "popup",
	 *			"default": true
	 *		},
	 * ]
	 *
	 * @param array $actions id indexed array of actions / array with valus for keys: 'iconUrl', 'caption', 'onExecute', ...
	 * @param string $template_name='' name of the template, used as default for app name of images
	 * @param string $prefix='' prefix for ids
	 * @param array &$action_links=array() on return all first-level actions plus the ones with enabled='javaScript:...'
	 * @param int $max_length=self::DEFAULT_MAX_MENU_LENGTH automatic pagination, not for first menu level!
	 * @param array $default_attrs=null default attributes
	 * @return array
	 */
	public static function egw_actions(array $actions=null, $template_name='', $prefix='', array &$action_links=array(),
		$max_length=self::DEFAULT_MAX_MENU_LENGTH, array $default_attrs=null)
	{
		//echo "<p>".__METHOD__."(\$actions, '$template_name', '$prefix', \$action_links, $max_length) \$actions="; _debug_array($actions);
		// default icons for some common actions
		static $default_icons = array(
			'view' => 'view',
			'edit' => 'edit',
			'open' => 'edit',	// does edit if possible, otherwise view
			'add'  => 'new',
			'new'  => 'new',
			'delete' => 'delete',
			'cat'  => 'attach',		// add as category icon to api
			'document' => 'etemplate/merge',
			'print'=> 'print',
			'copy' => 'copy',
			'move' => 'move',
			'cut'  => 'cut',
			'paste'=> 'editpaste',
		);

		$first_level = !$action_links;	// add all first level actions

		//echo "actions="; _debug_array($actions);
		$egw_actions = array();
		$n = 1;
		foreach((array)$actions as $id => $action)
		{
			// in case it's only selectbox  id => label pairs
			if (!is_array($action)) $action = array('caption' => $action);
			if ($default_attrs) $action += $default_attrs;

			if (!$first_level && $n == $max_length && count($actions) > $max_length)
			{
				$id = 'more_'.count($actions);	// we need a new unique id
				$action = array(
					'caption' => 'More',
					'prefix' => $prefix,
					// display rest of actions incl. current one as children
					'children' => array_slice($actions, $max_length-1, count($actions)-$max_length+1, true),
				);
				//echo "*** Inserting id=$prefix$id"; _debug_array($action);
				// we break at end of foreach loop, as rest of actions is already dealt with
				// by putting them as children
			}
			$action['id'] = $prefix.$id;

			// set certain enable functions
			foreach(array(
				'enableClass'  => 'javaScript:nm_enableClass',
				'disableClass' => 'javaScript:nm_not_disableClass',
				'enableId'     => 'javaScript:nm_enableId',
			) as $attr => $check)
			{
				if (isset($action[$attr]) && !isset($action['enabled']))
				{
					$action['enabled'] = $check;
				}
			}

			// add all first level popup actions plus ones with enabled = 'javaScript:...' to action_links
			if ((!isset($action['type']) || in_array($action['type'],array('popup','drag'))) &&	// popup is the default
				($first_level || substr($action['enabled'],0,11) == 'javaScript:'))
			{
				$action_links[] = $action['id'];
			}

			// set default icon, if no other is specified
			if (!isset($action['icon']) && isset($default_icons[$id]))
			{
				$action['icon'] = $default_icons[$id];
			}
			// use common eTemplate image semantics
			if (!isset($action['iconUrl']) && !empty($action['icon']))
			{
				list($app,$img) = explode('/',$action['icon'],2);
				if (!$app || !$img || !is_dir(EGW_SERVER_ROOT.'/'.$app) || strpos($img,'/')!==false)
				{
					$img = $action['icon'];
					list($app) = explode('.', $template_name);
				}
				$action['iconUrl'] = common::find_image($app, $img);
				unset($action['icon']);	// no need to submit it
			}
			// translate labels
			if (!$action['no_lang'])
			{
				$action['caption'] = lang($action['caption']);
				if ($action['hint']) $action['hint'] = lang($action['hint']);
			}
			unset($action['no_lang']);

			foreach(array('confirm','confirm_multiple') as $confirm)
			{
				if (isset($action[$confirm]))
				{
					$action[$confirm] = lang($action[$confirm]).(substr($action[$confirm],-1) != '?' ? '?' : '');
				}
			}

			// add sub-menues
			if ($action['children'])
			{
				static $inherit_attrs = array('url','popup','nm_action','onExecute','type','egw_open','allowOnMultiple','confirm','confirm_multiple');
				$action['children'] = self::egw_actions($action['children'], $template_name, $action['prefix'], $action_links, $max_length,
					array_intersect_key($action, array_flip($inherit_attrs)));

				unset($action['prefix']);
				$action = array_diff_key($action, array_flip($inherit_attrs));
			}

			// link or popup action
			if ($action['url'])
			{
				$action['url'] = egw::link('/index.php',str_replace('$action',$id,$action['url']));
				if ($action['popup'])
				{
					list($action['data']['width'],$action['data']['height']) = explode('x',$action['popup']);
					unset($action['popup']);
					$action['data']['nm_action'] = 'popup';
				}
				else
				{
					$action['data']['nm_action'] = 'location';
				}
			}
			if ($action['egw_open'])
			{
				$action['data']['nm_action'] = 'egw_open';
			}

			// give all delete actions a delete shortcut
			if ($id === 'delete' && !isset($action['shortcut']))
			{
				$action['shortcut'] = egw_keymanager::shortcut(egw_keymanager::DELETE);
			}

			static $egw_action_supported = array(	// attributes supported by egw_action
				'id','caption','iconUrl','type','default','onExecute','group',
				'enabled','allowOnMultiple','hideOnDisabled','data','children',
				'hint','checkbox','checked','radioGroup','acceptedTypes','dragType',
				'shortcut'
			);
			// add all not egw_action supported attributes to data
			$action['data'] = array_merge(array_diff_key($action, array_flip($egw_action_supported)),(array)$action['data']);
			if (!$action['data']) unset($action['data']);
			// only add egw_action attributes
			$egw_actions[] = array_intersect_key($action, array_flip($egw_action_supported));

			if (!$first_level && $n++ == $max_length) break;
		}
		//echo "egw_actions="; _debug_array($egw_actions);
		return $egw_actions;
	}

	/**
	 * Action with submenu for categories
	 *
	 * Automatic switch to hierarchical display, if more then $max_cats_flat=14 cats found.
	 *
	 * @param string $app
	 * @param int $group=0 see self::egw_actions
	 * @param string $caption='Change category'
	 * @param string $prefix='cat_' prefix category id to get action id
	 * @param boolean $globals=true application global categories too
	 * @param int $parent_id=0 only returns cats of a certain parent
	 * @param int $max_cats_flat=self::DEFAULT_MAX_MENU_LENGTH use hierarchical display if more cats
	 * @return array like self::egw_actions
	 */
	public static function category_action($app, $group=0, $caption='Change category',
		$prefix='cat_', $globals=true, $parent_id=0, $max_cats_flat=self::DEFAULT_MAX_MENU_LENGTH)
	{
		$cat = new categories(null,$app);
		$cats = $cat->return_sorted_array($start=0, $limit=false, $query='', $sort='ASC', $order='cat_name', $globals, $parent_id, $unserialize_data=true);

		// if more then max_length cats, switch automatically to hierarchical display
		if (count($cats) > $max_cats_flat)
		{
			$cat_actions = self::category_hierarchy($cats, $prefix, $parent_id);
		}
		else	// flat, indented categories
		{
			$cat_actions = array();
			foreach((array)$cats as $cat)
			{
				$name = str_repeat('&nbsp;',2*$cat['level']) . stripslashes($cat['name']);
				if (categories::is_global($cat)) $name .= ' &#9830;';

				$cat_actions[$cat['id']] = array(
					'caption' => $name,
					'no_lang' => true,
				);
				// add category icon
				if ($cat['data']['icon'] && file_exists(EGW_SERVER_ROOT.'/phpgwapi/images/'.basename($cat['data']['icon'])))
				{
					$cat_actions[$cat['id']]['iconUrl'] = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/images/'.$cat['data']['icon'];
				}
			}
		}
		return array(
			'caption' => $caption,
			'children' => $cat_actions,
			'enabled' => (boolean)$cat_actions,
			'group' => $group,
			'prefix' => $prefix,
		);
	}

	/**
	 * Return one level of the category hierarchy
	 *
	 * @param array $cats=null all cats if already read
	 * @param string $prefix='cat_' prefix category id to get action id
	 * @param int $parent_id=0 only returns cats of a certain parent
	 * @return array
	 */
	private static function category_hierarchy(array $cats, $prefix, $parent_id=0)
	{
		$cat_actions = array();
		foreach($cats as $key => $cat)
		{
			// current hierarchy level
			if ($cat['parent'] == $parent_id)
			{
				$name = stripslashes($cat['name']);
				if (categories::is_global($cat)) $name .= ' &#9830;';

				$cat_actions[$cat['id']] = array(
					'caption' => $name,
					'no_lang' => true,
					'prefix' => $prefix,
				);
				// add category icon
				if ($cat['data']['icon'] && file_exists(EGW_SERVER_ROOT.'/phpgwapi/images/'.basename($cat['data']['icon'])))
				{
					$cat_actions[$cat['id']]['iconUrl'] = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/images/'.$cat['data']['icon'];
				}
				unset($cats[$key]);
			}
			// direct children
			elseif(isset($cat_actions[$cat['parent']]))
			{
				$cat_actions['sub_'.$cat['parent']] = $cat_actions[$cat['parent']];
				// have to add category itself to children, to be able to select it!
				$cat_actions[$cat['parent']]['group'] = -1;	// own group on top
				$cat_actions['sub_'.$cat['parent']]['children'] = array(
					$cat['parent'] => $cat_actions[$cat['parent']],
				)+self::category_hierarchy($cats, $prefix, $cat['parent']);
				unset($cat_actions[$cat['parent']]);
			}
		}
		return $cat_actions;
	}
}

