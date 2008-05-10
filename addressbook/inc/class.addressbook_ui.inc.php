<?php
/**
 * Addressbook - user interface
 *
 * @link www.egroupware.org
 * @author Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2005-8 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2005/6 by Cornelius Weiss <egw@von-und-zu-weiss.de>
 * @package addressbook
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * General user interface object of the adressbook
 */
class addressbook_ui extends addressbook_bo
{
	var $public_functions = array(
		'search'	=> True,
		'edit'		=> True,
		'view'		=> True,
		'index'     => True,
		'photo'		=> True,
		'emailpopup'=> True,
		'migrate2ldap' => True,
		'cat_add' => True,
	);
	/**
	 * use a separate private addressbook (former private flag), for contacts not shareable via regular read acl
	 *
	 * @var boolean
	 */
	var $private_addressbook = false;
	var $org_views;

	/**
	 * Addressbook configuration (stored as phpgwapi = general server config)
	 *
	 * @var array
	 */
	var $config;
	/**
	 * Name(s) of the tabs in the edit dialog
	 *
	 * @var string
	 */
	var $tabs = 'general|cats|home|details|links|distribution_list|custom|custom_private';

	function __construct($contact_app='addressbook')
	{
		parent::__construct($contact_app);

		$this->tmpl = new etemplate();

		$this->private_addressbook = $this->contact_repository == 'sql' && $this->prefs['private_addressbook'];

		$this->org_views = array(
			'org_name'                  => lang('Organisations'),
			'org_name,adr_one_locality' => lang('Organisations by location'),
			'org_name,org_unit'         => lang('Organisations by departments'),
		);

		// our javascript
		// to be moved in a seperate file if rewrite is over
		if (strpos($GLOBALS['egw_info']['flags']['java_script'],'add_new_list') === false)
		{
			$GLOBALS['egw_info']['flags']['java_script'].= $this->js();
		}
		$this->config =& $GLOBALS['egw_info']['server'];
	}

	/**
	 * List contacts of an addressbook
	 *
	 * @param array $content=null submitted content
	 * @param string $msg=null	message to show
	 * @param boolean $do_email=false do an email-selection popup or the regular index-page
	 */
	function index($content=null,$msg=null,$do_email=false)
	{
		//echo "<p>uicontacts::index(".print_r($content,true).",'$msg')</p>\n";
		if (($re_submit = is_array($content)))
		{
			$do_email = $content['do_email'];

			if (isset($content['nm']['rows']['delete']))	// handle a single delete like delete with the checkboxes
			{
				list($id) = @each($content['nm']['rows']['delete']);
				$content['action'] = 'delete';
				$content['nm']['rows']['checked'] = array($id);
			}
			if (isset($content['nm']['rows']['document']))	// handle insert in default document button like an action
			{
				list($id) = @each($content['nm']['rows']['document']);
				$content['action'] = 'document';
				$content['nm']['rows']['checked'] = array($id);
			}
			if ($content['action'] !== '')
			{
				if (!count($content['nm']['rows']['checked']) && !$content['use_all'] && $content['action'] != 'delete_list')
				{
					$msg = lang('You need to select some contacts first');
				}
				else
				{
					if ($this->action($content['action'],$content['nm']['rows']['checked'],$content['use_all'],
						$success,$failed,$action_msg,$content['do_email'] ? 'email' : 'index',$msg))
					{
						$msg .= lang('%1 contact(s) %2',$success,$action_msg);
					}
					elseif(is_null($msg))
					{
						$msg .= lang('%1 contact(s) %2, %3 failed because of insufficent rights !!!',$success,$action_msg,$failed);
					}
				}
			}
			if ($content['nm']['rows']['infolog'])
			{
				list($org) = each($content['nm']['rows']['infolog']);
				return $this->infolog_org_view($org);
			}
			if ($content['nm']['rows']['view'])	// show all contacts of an organisation
			{
				list($org_view) = each($content['nm']['rows']['view']);
			}
			else
			{
				$org_view = $content['nm']['org_view'];
			}
			if ($content['nm']['col_filter']['tid'])
			{
				$typeselection=$content['nm']['col_filter']['tid'] ;
			} else {
				$typeselection='n';
			}
		}
		elseif($_GET['add_list'])
		{
			$list = $this->add_list($_GET['add_list'],$_GET['owner']?$_GET['owner']:$this->user);
			if ($list === true)
			{
				$msg = lang('List already exists!');
			}
			elseif ($list)
			{
				$msg = lang('List created');
			}
			else
			{
				$msg = lang('List creation failed, no rights!');
			}
		}
		$preserv = array(
			'do_email' => $do_email,
		);
		$to = $content['nm']['to'];
		$content = array(
			'msg' => $msg ? $msg : $_GET['msg'],
		);

		$content['nm'] = $GLOBALS['egw']->session->appsession($do_email ? 'email' : 'index','addressbook');
		if (!is_array($content['nm']))
		{
			$content['nm'] = array(
				'get_rows'       =>	'addressbook.addressbook_ui.get_rows',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
				'bottom_too'     => false,		// I  show the nextmatch-line (arrows, filters, search, ...) again after the rows
				'never_hide'     => True,		// I  never hide the nextmatch-line if less then maxmatch entrie
				'start'          =>	0,			// IO position in list
				'cat_id'         =>	'',			// IO category, if not 'no_cat' => True
				'options-cat_id' => array(lang('none')),
				'search'         =>	'',			// IO search pattern
				'order'          =>	'n_family',	// IO name of the column to sort after (optional for the sortheaders)
				'sort'           =>	'ASC',		// IO direction of the sort: 'ASC' or 'DESC'
				'col_filter'     =>	array(),	// IO array of column-name value pairs (optional for the filterheaders)
				'filter_label'   =>	lang('Addressbook'),	// I  label for filter    (optional)
				'filter'         =>	'',	// =All	// IO filter, if not 'no_filter' => True
				'filter_no_lang' => True,		// I  set no_lang for filter (=dont translate the options)
				'no_filter2'     => True,		// I  disable the 2. filter (params are the same as for filter)
				'filter2_label'  =>	lang('Distribution lists'),			// IO filter2, if not 'no_filter2' => True
				'filter2'        =>	'',			// IO filter2, if not 'no_filter2' => True
				'filter2_no_lang'=> True,		// I  set no_lang for filter2 (=dont translate the options)
				'lettersearch'   => true,
				'do_email'       => $do_email,
				'default_cols'   => '!cat_id,contact_created_contact_modified,distribution_list',
				'filter2_onchange' => "if(this.value=='add') { add_new_list(document.getElementById(form::name('filter')).value); this.value='';} else this.form.submit();",
				'manual' => $do_email ? ' ' : false,	// space for the manual icon
			);
			if ($do_email)
			{
				$content['nm']['filter2_onchange'] = str_replace('this.form.submit();',
					"{ if (this.value && confirm('".lang('Add emails of whole distribution list?')."')) add_whole_list(this.value); else this.form.submit(); }",
					$content['nm']['filter2_onchange']);
			}
			// use the state of the last session stored in the user prefs
			if (($state = @unserialize($this->prefs[$do_email ? 'email_state' : 'index_state'])))
			{
				$content['nm'] = array_merge($content['nm'],$state);
			}
		}
		if ($this->lists_available())
		{
			$sel_options['filter2'] = $this->get_lists(EGW_ACL_READ,array('' => lang('none')));
			$sel_options['filter2']['add'] = lang('Add a new list').'...';	// put it at the end
		}
		if ($do_email)
		{
			if (!$re_submit)
			{
				$content['nm']['to'] = 'to';
				$content['nm']['email_type'] = $this->prefs['distributionListPreferredMail'] ? $this->prefs['distributionListPreferredMail'] : 'email';
				$content['nm']['search'] = '@';
			}
			else
			{
				$content['nm']['to'] = $to;
				$content['nm']['email_type'] = $this->prefs['distributionListPreferredMail'] ? $this->prefs['distributionListPreferredMail'] : 'email';
			}
			$content['nm']['header_left'] = 'addressbook.email.left';
		}
		// Organisation stuff is not (yet) availible with ldap
		elseif($GLOBALS['egw_info']['server']['contact_repository'] != 'ldap')
		{
			$content['nm']['header_left'] = 'addressbook.index.left';
		}
		$sel_options['filter'] = $this->get_addressbooks(EGW_ACL_READ,lang('All'));
		$sel_options['to'] = array(
			'to'  => 'To',
			'cc'  => 'Cc',
			'bcc' => 'Bcc',
		);
		$sel_options['action'] = array();
		if ($do_email)
		{
			$GLOBALS['egw_info']['flags']['include_xajax'] = true;
			$sel_options['action'] = array(
				'email' => lang('Add %1',lang('business email')),
				'email_home' => lang('Add %1',lang('home email')),
			);
		}
		$sel_options['action'] += array(
			'delete' => lang('Delete'),
			'csv'    => lang('Export as CSV'),
			'vcard'  => lang('Export as VCard'), // ToDo: move this to importexport framework
			'merge'  => lang('Merge into first or account, deletes all other!'),
			'cat_add' => lang('Add or delete Categoies'), // add a categirie to multible addresses
			'infolog_add' => lang('Add a new Infolog'),
		);
		if ($GLOBALS['egw_info']['user']['apps']['infolog'])
		{
			$sel_options['action']['infolog'] = lang('View linked InfoLog entries');
		}
		if (($move2addressbooks=$this->get_addressbooks(EGW_ACL_ADD)))	// do we have addressbooks, we should
		{
			foreach ($move2addressbooks as $m2a_id => $m2alabel)
			{
				$m2a['move_to_'.$m2a_id] = $m2alabel;
			}
			$sel_options['action'][lang('Move to addressbook:')] = $m2a;
		}
		if (($add_lists = $this->get_lists(EGW_ACL_EDIT)))	// do we have distribution lists?
		{
			$lists = array();
			foreach ($add_lists as $list_id => $label)
			{
				$lists['to_list_'.$list_id] = $label;
			}
			$sel_options['action'][lang('Add to distribution list:')] = $lists;
			unset($lists);
			$sel_options['action']['remove_from_list'] = lang('Remove selected contacts from distribution list');
			$sel_options['action']['delete_list'] = lang('Delete selected distribution list!');
		}


		if ($this->prefs['document_dir'])
		{
			$sel_options['action'][lang('Insert in document').':'] = $this->get_document_actions();
		}
		if (!array_key_exists('importexport',$GLOBALS['egw_info']['user']['apps'])) unset($sel_options['action']['export']);

		// dont show tid-selection if we have only one content_type
		if (count($this->content_types) <= 1)
		{
			$content['nm']['col_filter']['tid'] = 'n';
			$content['nm']['header_right'] = 'addressbook.index.right_add';
		}
		else
		{
			$content['nm']['col_filter']['tid'] = ($typeselection ? $typeselection : 'n');
			$content['nm']['header_right'] = 'addressbook.index.right';
			foreach($this->content_types as $tid => $data)
			{
				$sel_options['col_filter[tid]'][$tid] = $data['name'];
			}
		}

		// get the availible org-views plus the label of the contacts view of one org
		$sel_options['org_view'] = $this->org_views;
		if (isset($org_view)) $content['nm']['org_view'] = $org_view;
		if (!isset($sel_options['org_view'][(string) $content['nm']['org_view']]))
		{
			$org_name = array();
			foreach(explode('|||',$content['nm']['org_view']) as $part)
			{
				list(,$name) = explode(':',$part,2);
				if ($name) $org_name[] = $name;
			}
			$org_name = implode(': ',$org_name);
			$sel_options['org_view'][(string) $content['nm']['org_view']] = $org_name;
		}
		$content['nm']['org_view_label'] = $sel_options['org_view'][(string) $content['nm']['org_view']];

		$this->tmpl->read(/*$do_email ? 'addressbook.email' :*/ 'addressbook.index');
		return $this->tmpl->exec($do_email ? 'addressbook.addressbook_ui.emailpopup' : 'addressbook.addressbook_ui.index',
			$content,$sel_options,$readonlys,$preserv,$do_email ? 2 : 0);
	}

	/**
	 * Email address-selection popup
	 *
	 * @param array $content=null submitted content
	 * @param string $msg=null	message to show
	 */
	function emailpopup($content=null,$msg=null)
	{
		if (strpos($GLOBALS['egw_info']['flags']['java_script'],'addEmail') === false)
		{
			if ($_GET['compat'])	// 1.2 felamimail or old email
			{
				$handler = "if (opener.document.doit[to].value != '')
		{
			opener.document.doit[to].value += ',';
		}
		opener.document.doit[to].value += email";
			}
			else	// 1.3+ felamimail
			{
				$handler = 'opener.addEmail(to,email)';
			}
			$GLOBALS['egw_info']['flags']['java_script'].= "
<script>
	window.focus();

	function addEmail(email)
	{
		var to = 'to';
		if (document.getElementById('exec[nm][to][cc]').checked == true)
		{
			to = 'cc';
		}
		else
		{
			if (document.getElementById('exec[nm][to][bcc]').checked == true)
			{
				to = 'bcc';
			}
		}
		$handler;
	}
</script>
";
		}
		return $this->index($content,$msg,true);
	}

	/**
	 * Show the infologs of an whole organisation
	 *
	 * @param string $org
	 */
	function infolog_org_view($org)
	{
		$query = $GLOBALS['egw']->session->appsession('index','addressbook');
		$query['num_rows'] = -1;	// all
		$query['org_view'] = $org;
		$query['searchletter'] = '';
		$this->get_rows($query,$checked,$readonlys,true);	// true = only return the id's

		if (count($checked) > 1)	// use a nicely formatted org-name as title in infolog
		{
			$parts = array();
			foreach(explode('|||',$org) as $part)
			{
				list(,$part) = explode(':',$part,2);
				if ($part) $parts[] = $part;
			}
			$org = implode(', ',$parts);
		}
		else
		{
			$org = '';	// use infolog default of link-title
		}
		$GLOBALS['egw']->redirect_link('/index.php',array(
			'menuaction' => 'infolog.uiinfolog.index',
			'action' => 'addressbook',
			'action_id' => implode(',',$checked),
			'action_title' => $org,
		));
	}

	function ajax_add_whole_list($list, $email_type = 'email')
	{
		$query = $GLOBALS['egw']->session->appsession('email','addressbook');
		$query['filter2'] = (int)$list;
		$this->action($email_type,array(),true,$success,$failed,$action_msg,$query,$msg);

		$response =& new xajaxResponse();

		if ($success) $response->addScript($GLOBALS['egw']->js->body['onLoad']);

		// close window only if no errors AND something added
		if ($failed || !$success)
		{
			if (!$msg) $msg = $failed ? lang('%1 contact(s) %2, %3 failed because of insufficent rights !!!',$success,$action_msg,$failed) :
				lang('%1 contact(s) %2',$success,$action_msg);

			$response->addScript("alert('".addslashes($msg)."')");
			// reset the filter
			$response->addScript("document.getElementById('exec[nm][filter2]').value='';");
		}
		else
		{
			if (!$msg) $msg = lang('%1 contact(s) %2',$success,$action_msg);
			$response->addScript("alert('".addslashes($msg)."')");
			$response->addScript('window.close();');
		}
		return $response->getXML();
	}

	/**
	 * apply an action to multiple contacts
	 *
	 * @param string/int $action 'delete', 'vcard', 'csv' or nummerical account_id to move contacts to that addessbook
	 * @param array $checked contact id's to use if !$use_all
	 * @param boolean $use_all if true use all contacts of the current selection (in the session)
	 * @param int &$success number of succeded actions
	 * @param int &$failed number of failed actions (not enought permissions)
	 * @param string &$action_msg translated verb for the actions, to be used in a message like %1 contacts 'deleted'
	 * @param string/array $session_name 'index' or 'email', or array with session-data depending if we are in the main list or the popup
	 * @return boolean true if all actions succeded, false otherwise
	 */
	function action($action,$checked,$use_all,&$success,&$failed,&$action_msg,$session_name,&$msg)
	{
		//echo "<p>uicontacts::action('$action',".print_r($checked,true).','.(int)$use_all.",...)</p>\n";
		$success = $failed = 0;
		if ($use_all || in_array($action,array('remove_from_list','delete_list')))
		{
			// get the whole selection
			$query = is_array($session_name) ? $session_name : $GLOBALS['egw']->session->appsession($session_name,'addressbook');

			if ($use_all)
			{
				@set_time_limit(0);			// switch off the execution time limit, as it's for big selections to small
				$query['num_rows'] = -1;	// all
				$this->get_rows($query,$checked,$readonlys,true);	// true = only return the id's
			}
		}
		// replace org_name:* id's with all id's of that org
		$org_contacts = array();
		foreach((array)$checked as $n => $id)
		{
			if (substr($id,0,9) == 'org_name:')
			{
				if (count($checked) == 1 && !count($org_contacts) && $action == 'infolog')
				{
					return $this->infolog_org_view($id);	// uses the org-name, instead of 'selected contacts'
				}
				unset($checked[$n]);
				$query = $GLOBALS['egw']->session->appsession($session_name,'addressbook');
				$query['num_rows'] = -1;	// all
				$query['org_view'] = $id;
				unset($query['filter2']);
				$this->get_rows($query,$extra,$readonlys,true);	// true = only return the id's
				if ($extra[0]) $org_contacts = array_merge($org_contacts,$extra);
			}
		}
		if ($org_contacts) $checked = array_unique($checked ? array_merge($checked,$org_contacts) : $org_contacts);
		//_debug_array($checked); exit;

		if (substr($action,0,8) == 'move_to_')
		{
			$action = (int)substr($action,8);
		}
		if (substr($action,0,7) == 'to_list')
		{
			$to_list = (int)substr($action,8);
			$action = 'to_list';
		}
		if (substr($action,0,9) == 'document-')
		{
			$document = substr($action,9);
			$action = 'document';
		}
		// Security: stop non-admins to export more then the configured number of contacts
		if (in_array($action,array('csv','vcard')) && (int)$this->config['contact_export_limit'] &&
			!isset($GLOBALS['egw_info']['user']['apps']['admin']) && count($checked) > $this->config['contact_export_limit'])
		{
			$action_msg = lang('exported');
			$failed = count($checked);
			return false;
		}
		switch($action)
		{
			case 'csv':
				$action_msg = lang('exported');
				$csv_export =& CreateObject('addressbook.csv_export',$this,$this->prefs['csv_charset']);
				switch ($this->prefs['csv_fields'])
				{
					case 'business':
						$fields = $this->business_contact_fields;
						break;
					case 'home':
						$fields = $this->home_contact_fields;
						break;
					default:
						$fields = $this->contact_fields;
						foreach($this->customfields as $name => $data)
						{
							$fields['#'.$name] = $data['label'];
						}
						break;
				}
				$csv_export->export($checked,$fields);
				// does not return!
				$Ok = true;
				break;

			case 'vcard':
				$action_msg = lang('exported');
				ExecMethod('addressbook.vcaladdressbook.export',$checked);
				// does not return!
				$Ok = false;
				break;

			case 'infolog':
				$GLOBALS['egw']->redirect_link('/index.php',array(
					'menuaction' => 'infolog.uiinfolog.index',
					'action' => 'addressbook',
					'action_id' => implode(',',$checked),
					'action_title' => count($checked) > 1 ? lang('selected contacts') : '',
				));
				break;

			case 'merge':
				$success = $this->merge($checked,$error_msg);
				$failed = count($checked) - (int)$success;
				$action_msg = lang('merged');
				$checked = array();	// to not start the single actions
				break;

			case 'delete_list':
				if (!$query['filter2'])
				{
					$msg = lang('You need to select a distribution list');
				}
				elseif($this->delete_list($query['filter2']) === false)
				{
					$msg = lang('Insufficent rights to delete this list!');
				}
				else
				{
					$msg = lang('Distribution list deleted');
					unset($query['filter2']);
					$GLOBALS['egw']->session->appsession($session_name,'addressbook',$query);
				}
				return false;

			case 'document':
				$msg = $this->download_document($checked,$document);
				return false;

			case 'infolog_add':
				$msg = lang('New window opened to edit Infolog for your selection ');
				return false;

			case 'cat_add':
				foreach($checked as $id)
				{
					if (($Ok = !!($contact = $this->read($id)) && $this->check_perms(EGW_ACL_EDIT,$contact)))
					{
						$action_msg = lang('categorie');
						$cat_ids = explode(",",$contact['cat_id']);   //existing categiries
						if (!is_array($cat_ids_new) && $cat_ids_new) $cat_ids_new = explode(",",$cat_ids_new);
						//categarie add
						if ((!($cat_ids_new = $GLOBALS['egw']->session->appsession('cat_add','addressbook')) && !($cat_ids_new = $GLOBALS['egw']->session->appsession('cat_delete','addressbook'))))
						{
							$action_msg = lang('no categories selected');
						}
						if ($GLOBALS['egw']->session->appsession('cat_add','addressbook'))
						{
							if (is_array($cat_ids_new) && ($ids_to_add = array_diff($cat_ids_new,$cat_ids)))
							{
								$cat_ids = array_merge($cat_ids,$ids_to_add);
								$contact['cat_id'] = implode(",",$cat_ids);
								$Ok = $this->save($contact);
								$success++;
								$action_msg = lang('categorie added');
							}
							else
							{
								$failed++;
							}
						}
						//categories delete
						if ($GLOBALS['egw']->session->appsession('cat_delete','addressbook'))
						{
							if (is_array($cat_ids_new) && ($ids_to_delete = array_diff($cat_ids,$cat_ids_new)) or ($cat_ids = $cat_ids_new))
							{
								$contact['cat_id'] = implode(",",$ids_to_delete);
								$action_msg = lang('categorie delete');
								$Ok = $this->save($contact);
								$success++;
							}
							else
							{
								$failed++;
							}
						}
					}
				}
				$checked = array();	// to not start the single actions
				$GLOBALS['egw']->session->appsession('cat_add','addressbook','');      //delete stored categories to add
				$GLOBALS['egw']->session->appsession('cat_delete','addressbook','');    //delete stored categories to delete
				break;
		}
		foreach($checked as $id)
		{
			switch($action)
			{
				case 'delete':
					$action_msg = lang('deleted');
					if (($Ok = !!($contact = $this->read($id)) && $this->check_perms(EGW_ACL_DELETE,$contact)))
					{
						if ($contact['owner'])	// regular contact
						{
							$Ok = $this->delete($id);
						}
						// delete single account --> redirect to admin
						elseif (count($checked) == 1 && $contact['account_id'])
						{
							$GLOBALS['egw']->redirect_link('/index.php',array(
								'menuaction' => 'admin.uiaccounts.delete_user',
								'account_id' => $contact['account_id'],
							));
							// this does NOT return!
						}
						else	// no mass delete of accounts
						{
							$Ok = false;
						}
					}
					break;

				case 'email':
				case 'email_home':
					$action == 'email' ? $action_fallback = 'email_home' : $action_fallback = 'email';
					$action_msg = lang('added');
					if($contact = $this->read($id))
					{
						if(strpos($contact[$action],'@') !== false)
						{
							$email = $contact[$action];
						}
						elseif(strpos($contact[$action_fallback],'@') !== false)
						{
							$email = $contact[$action_fallback];
						}
						else
						{
							$Ok = $email = false;
						}
						if($email)
						{
							$GLOBALS['egw']->js->set_onload("addEmail('".addslashes(
								$contact['n_fn'] ? $contact['n_fn'].' <'.$email.'>' : $email)."');");
							$Ok = true;
						}
					}
					break;

				case 'remove_from_list':
					$action_msg = lang('removed from distribution list');
					if (!$query['filter2'])
					{
						$msg = lang('You need to select a distribution list');
						return false;
					}
					else
					{
						$Ok = $this->remove_from_list($id,$query['filter2']) !== false;
					}
					break;

				case 'to_list':
					$action_msg = lang('added to distribution list');
					if (!$to_list)
					{
						$msg = lang('You need to select a distribution list');
						return false;
					}
					else
					{
						$Ok = $this->add2list($id,$to_list) !== false;
					}
					break;
				default:	// move to an other addressbook
					if (!(int)$action || !($this->grants[(string) (int) $action] & EGW_ACL_EDIT))	// might be ADD in the future
					{
						return false;
					}
					$action_msg = lang('moved');
					if (($Ok = !!($contact = $this->read($id)) && $this->check_perms(EGW_ACL_DELETE,$contact)))
					{
						if (!$contact['owner'])		// no mass-change of accounts
						{
							$Ok = false;
						}
						elseif ($contact['owner'] != (int)$action || $contact['private'] != (int)(substr($action,-1) == 'p'))
						{
							$contact['owner'] = (int) $action;
							$contact['private'] = (int)(substr($action,-1) == 'p');
							$Ok = $this->save($contact);
						}
					}
					break;
			}
			if ($Ok)
			{
				++$success;
			}
			elseif ($action != 'email' && $action != 'email_home')
			{
				++$failed;
			}
		}
		return !$failed;
	}

	/**
	 * rows callback for index nextmatch
	 *
	 * @internal
	 * @param array &$query
	 * @param array &$rows returned rows/cups
	 * @param array &$readonlys eg. to disable buttons based on acl
	 * @param boolean $id_only=false if true only return (via $rows) an array of contact-ids, dont save state to session
	 * @return int total number of contacts matching the selection
	 */
	function get_rows(&$query,&$rows,&$readonlys,$id_only=false)
	{
		$do_email = $query['do_email'];
		// is this wanted???
		if ($query['sitemgr_display'])
		{
			$old_state = $GLOBALS['egw']->session->appsession($query['sitemgr_display'],'addressbook');
		} else {
			$old_state = $GLOBALS['egw']->session->appsession($do_email ? 'email' : 'index','addressbook');
		}
		if (!isset($this->org_views[(string) $query['org_view']]))   // we dont have an org view, unset the according col_filters
		{
			if (isset($query['col_filter']['org_name'])) unset($query['col_filter']['org_name']);
			if (isset($query['col_filter']['adr_one_locality'])) unset($query['col_filter']['adr_one_locality']);
			if (isset($query['col_filter']['org_unit'])) unset($query['col_filter']['org_unit']);
		}

		if (isset($this->org_views[(string) $query['org_view']]))	// we have an org view, reset the advanced search
		{
			if (is_array($query['search'])) unset($query['search']);
			unset($query['advanced_search']);
		}
		elseif(!$query['search'] && $old_state['advanced_search'])	// eg. paging in an advanced search
		{
			$query['advanced_search'] = $old_state['advanced_search'];
		}
		if ($do_email && $GLOBALS['egw_info']['etemplate']['loop'] && is_object($GLOBALS['egw']->js))
		{	// remove previous addEmail() calls, otherwise they will be run again
			$GLOBALS['egw']->js->body['onLoad'] = preg_replace('/addEmail\([^)]+\);/','',$GLOBALS['egw']->js->body['onLoad']);
		}
		//echo "<p>uicontacts::get_rows(".print_r($query,true).")</p>\n";
		if (!$id_only)
		{
			// check if accounts are stored in ldap, which does NOT yet support the org-views
			if ($this->so_accounts && $query['filter'] === '0' && $query['org_view'])
			{
				if ($old_state['filter'] === '0')	// user changed to org_view
				{
					$query['filter'] = '';			// --> change filter to all contacts
				}
				else								// user changed to accounts
				{
					$query['org_view'] = '';		// --> change to regular contacts view
				}
			}
			if ($query['org_view'] && isset($this->org_views[$old_state['org_view']]) && !isset($this->org_views[$query['org_view']]))
			{
				$query['searchletter'] = '';		// reset lettersearch if viewing the contacts of one organisation
			}
			if ($query['sitemgr_display'])
			{
				$old_state = $GLOBALS['egw']->session->appsession($query['sitemgr_display'],'addressbook',$query);
			} else {
				$GLOBALS['egw']->session->appsession(($do_email ? 'email' : 'index'),'addressbook',$query);
			}
			// save the state of the index in the user prefs
			$state = serialize(array(
				'filter'     => $query['filter'],
				'cat_id'     => $query['cat_id'],
				'order'      => $query['order'],
				'sort'       => $query['sort'],
				'col_filter' => array('tid' => $query['col_filter']['tid']),
				'org_view'   => $query['org_view'],
			));
			if ($state != $this->prefs[($query['sitemgr_display'] ? $query['sitemgr_display'].'_state' : 'index_state')])
			{
				$GLOBALS['egw']->preferences->add('addressbook',($query['sitemgr_display'] ? $query['sitemgr_display'].'_state' : 'index_state'),$state);
				// save prefs, but do NOT invalid the cache (unnecessary)
				$GLOBALS['egw']->preferences->save_repository(false,'user',false);
			}
			if ($state != $this->prefs[$do_email ? 'email_state' : 'index_state'])
			{
				$GLOBALS['egw']->preferences->add('addressbook',$do_email ? 'email_state' : 'index_state',$state);
				// save prefs, but do NOT invalid the cache (unnecessary)
				$GLOBALS['egw']->preferences->save_repository(false,'user',false);
			}
		}
		unset($old_state);

		if ((string)$query['cat_id'] != '')
		{
			$query['col_filter']['cat_id'] = $query['cat_id'] ? $query['cat_id'] : null;
		}
		else
		{
			unset($query['col_filter']['cat_id']);
		}
		if ($query['filter'] !== '')	// not all addressbooks
		{
			$query['col_filter']['owner'] = (string) (int) $query['filter'];

			if ($this->private_addressbook)
			{
				$query['col_filter']['private'] = substr($query['filter'],-1) == 'p' ? 1 : 0;
			}
		}
		if ((int)$query['filter2'])	// not no distribution list
		{
			$query['col_filter']['list'] = (string) (int) $query['filter2'];
		}
		else
		{
			unset($query['col_filter']['list']);
		}
		if ($GLOBALS['egw_info']['user']['preferences']['addressbook']['hide_accounts'])
		{
			$query['col_filter']['account_id'] = null;
		}
		// enable/disable distribution lists depending on backend
		$query['no_filter2'] = !$this->lists_available($query['filter']);

		if (isset($this->org_views[(string) $query['org_view']]))	// we have an org view
		{
			unset($query['col_filter']['list']);	// does not work together
			$query['no_filter2'] = true;			// switch the distribution list selection off

			$query['template'] = 'addressbook.index.org_rows';

			if ($query['order'] != 'org_name')
			{
				$query['sort'] = 'ASC';
				$query['order'] = 'org_name';
			}
			$rows = parent::organisations($query);

			$GLOBALS['egw_info']['flags']['params']['manual'] = array('page' => 'ManualAddressbookIndexOrga');
		}
		else	// contacts view
		{
			if ($query['sitemgr_display'])
			{
				$query['template'] = $query['sitemgr_display'].'.rows';
			} else {
				$query['template'] = $do_email ? 'addressbook.email.rows' : 'addressbook.index.rows';
			}
			if ($query['org_view'])	// view the contacts of one organisation only
			{
				foreach(explode('|||',$query['org_view']) as $part)
				{
					list($name,$value) = explode(':',$part,2);
					$query['col_filter'][$name] = $value;
				}
			}
			// translate the select order to the really used over all 3 columns
			$sort = $query['sort'];
			switch($query['order'])		// "xxx<>'' DESC" sorts contacts with empty order-criteria always at the end
			{							// we don't exclude them, as the total would otherwise depend on the order-criteria
				case 'org_name':
					$order = "org_name<>'' DESC,org_name $sort,n_family $sort,n_given $sort";
					break;
				default:
					if ($query['order'][0] == '#')	// we order by a custom field
					{
						$order = "$query[order]<>'' DESC,$query[order] $sort,org_name $sort,n_family $sort,n_given $sort";
						break;
					}
					$query['order'] = 'n_family';
				case 'n_family':
					$order = "n_family<>'' DESC,n_family $sort,n_given $sort,org_name $sort";
					break;
				case 'n_given':
					$order = "n_given<>'' DESC,n_given $sort,n_family $sort,org_name $sort";
					break;
				case 'n_fileas':
					$order = "n_fileas<>'' DESC,n_fileas $sort";
					break;
				case 'adr_one_postalcode':
					$order = "adr_one_postalcode<>'' DESC,adr_one_postalcode $sort,org_name $sort,n_family $sort,n_given $sort";
					break;
				case 'contact_modified':
				case 'contact_created':
					$order = "$query[order] IS NULL,$query[order] $sort,org_name $sort,n_family $sort,n_given $sort";
					break;
			}
			if ($query['searchletter'])	// only show contacts if the order-criteria starts with the given letter
			{
				$query['col_filter'][] = ($query['order'] == 'adr_one_postalcode' ? 'org_name' : $query['order']).' '.
					$GLOBALS['egw']->db->capabilities['case_insensitive_like'].' '.$GLOBALS['egw']->db->quote($query['searchletter'].'%');
			}
			$wildcard = '%';
			$op = 'OR';
			if ($query['advanced_search'])
			{
				$op = $query['advanced_search']['operator'];
				unset($query['advanced_search']['operator']);
				$wildcard = $query['advanced_search']['meth_select'];
				unset($query['advanced_search']['meth_select']);
			}
			$rows = parent::search($query['advanced_search'] ? $query['advanced_search'] : $query['search'],$id_only,
				$order,'',$wildcard,false,$op,array((int)$query['start'],(int) $query['num_rows']),$query['col_filter']);

			// do we need to read the custom fields, depends on the column is enabled and customfields exist
			$columselection = $this->prefs['nextmatch-addressbook.'.($do_email ? 'email' : 'index').'.rows'];
			$available_distib_lists=$this->get_lists(EGW_ACL_EDIT);
			$columselection = $columselection ? explode(',',$columselection) : array();
			if (!$id_only && $rows)
			{
				$show_custom_fields = (!$columselection || in_array('customfields',$columselection)) && $this->customfields;
				$show_calendar = !$columselection || in_array('calendar',$columselection);
				$show_distributionlist = !$columselection || in_array('distrib_lists',$columselection) ||count($available_distib_lists);
				if ($show_calendar || $show_custom_fields || $show_distributionlist)
				{
					foreach($rows as $val)
					{
						$ids[] = $val['id'];
					}
					if ($show_custom_fields)
					{
						foreach($columselection as $col)
						{
							if ($col[0] == '#') $selected_cfs[] = substr($col,1);
						}
						$customfields = $this->read_customfields($ids,$selected_cfs);
					}
					if ($show_calendar) $calendar = $this->read_calendar($ids);
					// distributionlist memership for the entrys
					//_debug_array($this->get_lists(EGW_ACL_EDIT));
					if ($show_distributionlist && $available_distib_lists)
					{
						$distributionlist = $this->read_distributionlist($ids,array_keys($available_distib_lists));
					}
				}
			}
		}
		if (!$rows) $rows = array();

		if ($id_only)
		{
			foreach($rows as $n => $row)
			{
				$rows[$n] = $row['id'];
			}
			return $this->total;	// no need to set other fields or $readonlys
		}
		$order = $query['order'];

		$readonlys = array();
		$photos = $homeaddress = $roles = false;
		foreach($rows as $n => $val)
		{
			$row =& $rows[$n];

			$given = $row['n_given'] ? $row['n_given'] : ($row['n_prefix'] ? $row['n_prefix'] : '');

			switch($order)
			{
				default:	// postalcode, created, modified, ...
				case 'org_name':
					$row['line1'] = $row['org_name'];
					$row['line2'] = $row['n_family'].($given ? ', '.$given : '');
					break;
				case 'n_family':
					$row['line1'] = $row['n_family'].($given ? ', '.$given : '');
					$row['line2'] = $row['org_name'];
					break;
				case 'n_given':
					$row['line1'] = $given.' '.$row['n_family'];
					$row['line2'] = $row['org_name'];
					break;
				case 'n_fileas':
					if (!$row['n_fileas']) $row['n_fileas'] = $this->fileas($row);
					list($row['line1'],$row['line2']) = explode(': ',$row['n_fileas']);
					break;
			}
			if (isset($this->org_views[(string) $query['org_view']]))
			{
				$row['type'] = 'home';
				$row['type_label'] = lang('Organisation');

				$readonlys["delete[$row[id]]"] = $query['filter'] && !($this->grants[(int)$query['filter']] & EGW_ACL_DELETE);
				$readonlys["infolog[$row[id]]"] = !$GLOBALS['egw_info']['user']['apps']['infolog'];
			}
			else
			{
				$this->type_icon($row['owner'],$row['private'],$row['tid'],$row['type'],$row['type_label']);

				static $tel2show = array('tel_work','tel_cell','tel_home');
				foreach($tel2show as $name)
				{
					$this->call_link($row[$name],$row[$name.'_link']);
					$row[$name] .= ' '.($row['tel_prefer'] == $name ? '&#9829;' : '');		// .' ' to NOT remove the field
				}
				// allways show the prefered phone, if not already shown
				if (!in_array($row['tel_prefer'],$tel2show) && $row[$row['tel_prefer']])
				{
					$this->call_link($row[$row['tel_prefer']],$row['tel_prefered_link']);
					$row['tel_prefered'] = $row[$row['tel_prefer']].' &#9829;';
				}
				foreach(array('email','email_home') as $name)
				{
					if ($row[$name])
					{
						$row[$name.'_link'] = $this->email2link($row[$name]);
						if ($GLOBALS['egw_info']['user']['apps']['felamimail'])
						{
							$row[$name.'_popup'] = '700x750';
						}
					}
					else
					{
						$row[$name] = ' ';	// to NOT remove the field
					}
				}
				$readonlys["delete[$row[id]]"] = !$this->check_perms(EGW_ACL_DELETE,$row);
				$readonlys["edit[$row[id]]"] = !$this->check_perms(EGW_ACL_EDIT,$row);

				if ($row['photo']) $photos = true;
				if ($row['role']) $roles = true;
				if (isset($customfields[$row['id']]))
				{
					foreach($this->customfields as $name => $data)
					{
						$row['#'.$name] = $customfields[$row['id']][$name];
					}
				}
				if (isset($distributionlist[$row['id']]))
				{
					$row['distrib_lists'] = implode("\n",array_values($distributionlist[$row['id']]));
					//if ($show_distributionlist) $readonlys['distrib_lists'] =true;
				}
				if (isset($calendar[$row['id']]))
				{
					foreach($calendar[$row['id']] as $name => $data)
					{
						$row[$name] = $data;
					}
				}
				if ($this->prefs['home_column'] != 'never' && !$homeaddress)
				{
					foreach(array('adr_two_countryname','adr_two_locality','adr_two_postalcode','adr_two_street','adr_two_street2') as $name)
					{
						if ($row[$name]) $homeaddress = true;
					}
				}
			}
			$readonlys["document[$row[id]]"] = !$this->prefs['default_document'];

			// hide region for address format 'postcode_city'
			if (($row['addr_format']  = $this->addr_format_by_country($row['adr_one_countryname']))=='postcode_city') unset($row['adr_one_region']);
			if (($row['addr_format2'] = $this->addr_format_by_country($row['adr_two_countryname']))=='postcode_city') unset($row['adr_two_region']);
		}
		if ($show_distributionlist) {
			$readonlys['no_distrib_lists'] =true;
		} else {
			$readonlys['no_distrib_lists'] =false;
		}
		if (!$this->prefs['no_auto_hide'])
		{
			// disable photo column, if view contains no photo(s)
			if (!$photos) $rows['no_photo'] = true;
			// disable homeaddress column, if we have no homeaddress(es)
			if (!$homeaddress) $rows['no_home'] = true;
			// disable roles column
			if (!$roles) $rows['no_role'] = true;
		}
		// disable customfields column, if we have no customefield(s)
		if (!$this->customfields/* || !$this->prefs['no_auto_hide'] && !$customfields*/) $rows['no_customfields'] = true;

		$rows['order'] = $order;
		$rows['call_popup'] = $this->config['call_popup'];
		$rows['customfields'] = array_values($this->customfields);

		// full app-header with all search criteria specially for the print
		$GLOBALS['egw_info']['flags']['app_header'] = lang('addressbook');
		if ($query['filter'] !== '' && !isset($this->org_views[$query['org_view']]))
		{
			$GLOBALS['egw_info']['flags']['app_header'] .= ' '.($query['filter'] == '0' ? lang('accounts') :
				($GLOBALS['egw']->accounts->get_type($query['filter']) == 'g' ?
					lang('Group %1',$GLOBALS['egw']->accounts->id2name($query['filter'])) :
					$GLOBALS['egw']->common->grab_owner_name((int)$query['filter']).
						(substr($query['filter'],-1) == 'p' ? ' ('.lang('private').')' : '')));
		}
		if ($query['org_view'])
		{
			$GLOBALS['egw_info']['flags']['app_header'] .= ': '.$query['org_view_label'];
		}
		if($query['advanced_search'])
		{
				$GLOBALS['egw_info']['flags']['app_header'] .= ': '.lang('Advanced search');
		}
		if ($query['cat_id'])
		{
			$GLOBALS['egw_info']['flags']['app_header'] .= ': '.lang('Category').' '.$GLOBALS['egw']->categories->id2name($query['cat_id']);
		}
		if ($query['searchletter'])
		{
			$order = $order == 'n_given' ? lang('first name') : ($order == 'n_family' ? lang('last name') : lang('Organisation'));
			$GLOBALS['egw_info']['flags']['app_header'] .= ' - '.lang("%1 starts with '%2'",$order,$query['searchletter']);
		}
		if ($query['search'])
		{
			$GLOBALS['egw_info']['flags']['app_header'] .= ' - '.lang("Search for '%1'",$query['search']);
		}
		return $this->total;
	}

	/**
	 * Get addressbook type icon from owner, private and tid
	 *
	 * @param int $owner user- or group-id or 0 for accounts
	 * @param boolean $private
	 * @param string $tid 'n' for regular addressbook
	 * @param string &$icon icon-name
	 * @param string &$label translated label
	 */
	function type_icon($owner,$private,$tid,&$icon,&$label)
	{
		if (!$owner)
		{
			$icon = 'accounts';
			$label = lang('accounts');
		}
		elseif ($private)
		{
			$icon = 'private';
			$label = lang('private');
		}
		elseif ($tid != 'n')
		{
			// ToDo Conny: tid-icons
			$icon = '';
			$label = $tid;
		}
		elseif ($GLOBALS['egw']->accounts->get_type($owner) == 'g')
		{
			$icon = 'group';
			$label = lang('group %1',$GLOBALS['egw']->accounts->id2name($owner));
		}
		else
		{
			$icon = 'personal';
			$label = $owner == $this->user ? lang('personal') : $GLOBALS['egw']->common->grab_owner_name($owner);
		}
	}

	/**
	 * Get the availible addressbooks of the user
	 *
	 * @param int $required=EGW_ACL_READ required rights on the addressbook
	 * @param string $extra_label first label if given (already translated)
	 * @return array with owner => label pairs
	 */
	function get_addressbooks($required=EGW_ACL_READ,$extra_label=null)
	{
		//echo "uicontacts::get_addressbooks($required,$include_all) grants="; _debug_array($this->grants);

		$addressbooks = array();
		if ($extra_label) $addressbooks[''] = $extra_label;
		$addressbooks[$this->user] = lang('Personal');
		// add all group addressbooks the user has the necessary rights too
		foreach($this->grants as $uid => $rights)
		{
			if (($rights & $required) && $GLOBALS['egw']->accounts->get_type($uid) == 'g')
			{
				$addressbooks[$uid] = lang('Group %1',$GLOBALS['egw']->accounts->id2name($uid));
			}
		}
		if (($this->grants[0] & $required) && !$GLOBALS['egw_info']['user']['preferences']['addressbook']['hide_accounts'])
		{
			$addressbooks[0] = lang('Accounts');
		}
		// add all other user addressbooks the user has the necessary rights too
		foreach($this->grants as $uid => $rights)
		{
			if ($uid != $this->user && ($rights & $required) && $GLOBALS['egw']->accounts->get_type($uid) == 'u')
			{
				$addressbooks[$uid] = $GLOBALS['egw']->common->grab_owner_name($uid);
			}
		}
		if ($this->private_addressbook)
		{
			$addressbooks[$this->user.'p'] = lang('Private');
		}
		//_debug_array($addressbooks);
		return $addressbooks;
	}

	/**
	* Edit a contact
	*
	* @param array $content=null submitted content
	* @param int $_GET['contact_id'] contact_id manly for popup use
	* @param bool $_GET['makecp'] ture if you want do copy the contact given by $_GET['contact_id']
	*/
	function edit($content=null)
	{
		if (is_array($content))
		{
			list($button) = @each($content['button']);
			unset($content['button']);
			$content['private'] = (int) ($content['owner'] && substr($content['owner'],-1) == 'p');
			$content['owner'] = (string) (int) $content['owner'];

			switch($button)
			{
				case 'save':
				case 'apply':
					if ($content['delete_photo']) $content['jpegphoto'] = null;
					if (is_array($content['upload_photo']) && !empty($content['upload_photo']['tmp_name']) &&
						$content['upload_photo']['tmp_name'] != 'none')
					{
						$content['jpegphoto'] = $this->resize_photo($content['upload_photo']);
						unset($content['upload_photo']);
					}
					$links = false;
					if (!$content['id'] && is_array($content['link_to']['to_id']))
					{
						$links = $content['link_to']['to_id'];
					}
					if ($content['id'] && $content['org_name'] && $content['change_org'])
					{
						$old_org_entry = $this->read($content['id']);
					}
					if ($this->save($content))
					{
						$content['msg'] = lang('Contact saved');
						if ($content['change_org'] && $old_org_entry && ($changed = $this->changed_fields($old_org_entry,$content,true)) &&
							($members = $this->org_similar($old_org_entry['org_name'],$changed)))
						{
							//foreach($changed as $name => $old_value) echo "<p>$name: '$old_value' --> '{$content[$name]}'</p>\n";
							list($changed_members,$changed_fields,$failed_members) = $this->change_org($old_org_entry['org_name'],$changed,$content,$members);
							if ($changed_members)
							{
								$content['msg'] .= ', '.lang('%1 fields in %2 other organisation member(s) changed',$changed_fields,$changed_members);
							}
							if ($failed_members)
							{
								$content['msg'] .= ', '.lang('failed to change %1 organisation member(s) (insufficent rights) !!!',$failed_members);
							}
						}
					}
					elseif($this->error === true)
					{
						$content['msg'] = lang('Error: the entry has been updated since you opened it for editing!').'<br />'.
							lang('Copy your changes to the clipboard, %1reload the entry%2 and merge them.','<a href="'.
								htmlspecialchars($GLOBALS['egw']->link('/index.php',array(
									'menuaction' => 'addressbook.addressbook_ui.edit',
									'contact_id' => $content['id'],
								))).'">','</a>');
						break;	// dont refresh the list
					}
					else
					{
						$content['msg'] = lang('Error saving the contact !!!').
							($this->error ? ' '.$this->error : '');
						$button = 'apply';	// to not leave the dialog
					}
					// writing links for new entry, existing ones are handled by the widget itself
					if ($links && $content['id'])
					{
						egw_link::link('addressbook',$content['id'],$links);
					}
					if ($button == 'save')
					{
						echo "<html><body><script>var referer = opener.location;opener.location.href = referer+(referer.search?'&':'?')+'msg=".
							addslashes(urlencode($content['msg']))."'; window.close();</script></body></html>\n";
/*
						$link = $GLOBALS['egw']->link('/index.php',array(
							'menuaction' => 'addressbook.addressbook_ui.view',
							'contact_id' => $content['id'],
						));
						echo "<html><body><script>opener.location.href = '$link&msg=".
							addslashes(urlencode($content['msg']))."'; window.close();</script></body></html>\n";
*/
						$GLOBALS['egw']->common->egw_exit();
					}
					$content['link_to']['to_id'] = $content['id'];
					$GLOBALS['egw_info']['flags']['java_script'] .= "<script language=\"JavaScript\">
						var referer = opener.location;
						opener.location.href = referer+(referer.search?'&':'?')+'msg=".addslashes(urlencode($content['msg']))."';</script>";
					break;

				case 'delete':
					if($this->action('delete',array($content['id']),false,$success,$failed,$action_msg,'',$content['msg']))
					{
						echo "<html><body><script>var referer = opener.location; opener.location.href = referer+(referer.search?'&':'?')+'msg=".
							addslashes(urlencode(lang('Contact deleted')))."';window.close();</script></body></html>\n";
						$GLOBALS['egw']->common->egw_exit();
					}
					else
					{
						$content['msg'] = lang('Error deleting the contact !!!');
					}
					break;
			}
			// type change
		}
		else
		{
			$content = array();
			$contact_id = $_GET['contact_id'] ? $_GET['contact_id'] : ((int)$_GET['account_id'] ? 'account:'.(int)$_GET['account_id'] : 0);
			$view = $_GET['view'];
			// new contact --> set some defaults
			if ($contact_id && is_array($content = $this->read($contact_id)))
			{
				$contact_id = $content['id'];	// it could have been: "account:$account_id"
			}
			else // not found
			{
				$state = $GLOBALS['egw']->session->appsession('index','addressbook');
				// check if we create the new contact in an existing org
				if ($_GET['org'])
				{
					$content = $this->read_org($_GET['org']);
				}
				elseif ($state['org_view'] && !isset($this->org_views[$state['org_view']]))
				{
					$content = $this->read_org($state['org_view']);
				}
				elseif ($GLOBALS['egw_info']['user']['preferences']['common']['country'])
				{
					$content['adr_one_countryname'] =
						$GLOBALS['egw']->country->get_full_name($GLOBALS['egw_info']['user']['preferences']['common']['country']);
				}
				if (isset($_GET['owner']) && $_GET['owner'] !== '')
				{
					$content['owner'] = $_GET['owner'];
				}
				else
				{
					$content['owner'] = $state['filter'];
				}
				$content['private'] = (int) ($content['owner'] && substr($content['owner'],-1) == 'p');
				if (!($this->grants[$content['owner'] = (string) (int) $content['owner']] & EGW_ACL_ADD))
				{
					$content['owner'] = $this->default_addressbook;
					$content['private'] = (int)$this->default_private;

					if (!($this->grants[$content['owner'] = (string) (int) $content['owner']] & EGW_ACL_ADD))
					{
						$content['owner'] = (string) $this->user;
						$content['private'] = 0;
					}
				}
				$new_type = array_keys($this->content_types);
				$content['tid'] = $_GET['typeid'] ? $_GET['typeid'] : $new_type[0];
				foreach($this->get_contact_columns() as $field)
				{
					if ($_GET['presets'][$field]) $content[$field] = $_GET['presets'][$field];
				}
				$content['creator'] = $this->user;
				$content['created'] = $this->now_su;
				unset($state);
			}
			if($content && $_GET['makecp'])	// copy the contact
			{
				$content['link_to']['to_id'] = 0;
				egw_link::link('addressbook',$content['link_to']['to_id'],'addressbook',$content['id'],
					lang('Copied by %1, from record #%2.',$GLOBALS['egw']->common->display_fullname('',
					$GLOBALS['egw_info']['user']['account_firstname'],$GLOBALS['egw_info']['user']['account_lastname']),
					$content['id']));
				// create a new contact with the content of the old
				foreach(array('id','modified','modifier','account_id') as $key) unset($content[$key]);
				$content['owner'] = $this->default_private ? $this->user.'p' : $this->default_addressbook;
				$content['creator'] = $this->user;
				$content['created'] = $this->now_su;
				$content['msg'] = lang('Contact copied');
			}
			else
			{
				$content['link_to']['to_id'] = $contact_id;
			}
			// automatic link new entries to entries specified in the url
			if (!$contact_id && isset($_REQUEST['link_app']) && isset($_REQUEST['link_id']) && !is_array($content['link_to']['to_id']))
			{
				$link_ids = is_array($_REQUEST['link_id']) ? $_REQUEST['link_id'] : array($_REQUEST['link_id']);
				foreach(is_array($_REQUEST['link_app']) ? $_REQUEST['link_app'] : array($_REQUEST['link_app']) as $n => $link_app)
				{
					$link_id = $link_ids[$n];
					if (preg_match('/^[a-z_0-9-]+:[:a-z_0-9-]+$/i',$link_app.':'.$link_id))	// gard against XSS
					{
						egw_link::link('addressbook',$content['link_to']['to_id'],$link_app,$link_id);
					}
				}
			}
		}
		if ($content['id'])
		{
			// last and next calendar date
			list(,$dates) = each($this->read_calendar(array($content['id']),false));
			if(is_array($dates)) $content += $dates;
		}
		// how to display addresses
		$content['addr_format']  = $this->addr_format_by_country($content['adr_one_countryname']);
		$content['addr_format2'] = $this->addr_format_by_country($content['adr_two_countryname']);

		$content['disable_change_org'] = $view || !$content['org_name'];
		//_debug_array($content);
		$readonlys['button[delete]'] = !$content['owner'] || !$this->check_perms(EGW_ACL_DELETE,$content);
		$readonlys['button[copy]'] = $readonlys['button[edit]'] = $readonlys['button[vcard]'] = true;

		$sel_options['fileas_type'] = $this->fileas_options($content);
		$sel_options['owner'] = $this->get_addressbooks(EGW_ACL_ADD);
		if ((string) $content['owner'] !== '')
		{
			if (!isset($sel_options['owner'][(int)$content['owner']]))
			{
				$sel_options['owner'][(int)$content['owner']] = !$content['owner'] ? lang('Accounts') :
					$GLOBALS['egw']->common->grab_owner_name($content['owner']);
			}
			$readonlys['owner'] = !$content['owner'] || 		// dont allow to move accounts, as this mean deleting the user incl. all content he owns
				$content['id'] && !$this->check_perms(EGW_ACL_DELETE,$content);	// you need delete rights to move an existing contact into an other addressbook
		}
		// set the unsupported fields from the backend to readonly
		foreach($this->get_fields('unsupported',$content['id'],$content['owner']) as $field)
		{
			$readonlys[$field] = true;
		}
		// disable not needed tabs
		$readonlys[$this->tabs]['cats'] = !($content['cat_tab'] = $this->config['cat_tab']);
		$readonlys[$this->tabs]['custom'] = !$this->customfields;
		$readonlys[$this->tabs]['custom_private'] = !$this->customfields || !$this->config['private_cf_tab'];
		$readonlys[$this->tabs]['distribution_list'] = !$content['distrib_lists'];#false;
		$readonlys['button[delete]'] = !$content['id'];
		if ($this->config['private_cf_tab']) $content['no_private_cfs'] = 0;

		// for editing the own account (by a non-admin), enable only the fields allowed via the "own_account_acl"
		if (!$content['owner'] && !$this->is_admin($content))
		{
			$this->_set_readonlys_for_own_account_acl($readonlys,$id);
		}
		for($i = -23; $i<=23; $i++) $tz[$i] = ($i > 0 ? '+' : '').$i;
		$sel_options['tz'] = $tz;
		$content['tz'] = $content['tz'] ? $content['tz'] : 0;
		if (count($this->content_types) > 1)
		{
			foreach($this->content_types as $type => $data)
			{
				$sel_options['tid'][$type] = $data['name'];
			}
			$content['typegfx'] = html::image('addressbook',$this->content_types[$content['tid']]['options']['icon'],'',' width="16px" height="16px"');
		}
		else
		{
			$content['no_tid'] = true;
		}

		$content['link_to'] = array(
			'to_app' => 'addressbook',
			'to_id'  => $content['link_to']['to_id'],
		);
		$content['photo'] = $this->photo_src($content['id'],$content['jpegphoto'],'photo');

		if ($content['private']) $content['owner'] .= 'p';

		$GLOBALS['egw_info']['flags']['include_xajax'] = true;

		if (!$this->tmpl->read($this->content_types[$content['tid']]['options']['template']))
		{
			$content['msg']  = lang('WARNING: Template "%1" not found, using default template instead.', $this->content_types[$content['tid']]['options']['template'])."\n";
			$content['msg'] .= lang('Please update the templatename in your customfields section!');
			$this->tmpl->read('addressbook.edit');
		}
		return $this->tmpl->exec('addressbook.addressbook_ui.edit',$content,$sel_options,$readonlys,$content, 2);
	}

	/**
	 * Set the readonlys for non-admins editing their own account
	 *
	 * @param array &$readonlys
	 * @param int $id
	 */
	function _set_readonlys_for_own_account_acl(&$readonlys,$id)
	{
		// regular fields depending on the backend
		foreach($this->get_fields('supported',$id,0) as $field)
		{
			if (!$this->own_account_acl || !in_array($field,$this->own_account_acl))
			{
				$readonlys[$field] = true;
				switch($field)
				{
					case 'tel_work':
					case 'tel_cell':
					case 'tel_home':
						$readonlys[$field.'2'] = true;
						break;
					case 'n_fileas':
						$readonlys['fileas_type'] = true;
						break;
				}
			}
		}
		// custom fields
		if ($this->customfields)
		{
			foreach($this->customfields as $name => $data)
			{
				if (!$this->own_account_acl || !in_array('#'.$name,$this->own_account_acl))
				{
					$readonlys['#'.$name] = true;
				}
			}
		}
		// links
		if (!$this->own_account_acl || !in_array('link_to',$this->own_account_acl))
		{
			$readonlys['link_to'] = true;
		}
	}

	function ajax_setFileasOptions($n_prefix,$n_given,$n_middle,$n_family,$n_suffix,$org_name)
	{
		$names = array(
			'n_prefix' => $n_prefix,
			'n_given'  => $n_given,
			'n_middle' => $n_middle,
			'n_family' => $n_family,
			'n_suffix' => $n_suffix,
			'org_name' => $org_name,
		);
		$response =& new xajaxResponse();
		$response->addScript("setOptions('".addslashes(implode("\b",$this->fileas_options($names)))."');");

		return $response->getXML();
	}

	/**
	 * resizes the uploaded photo to 60*80 pixel and returns it
	 *
	 * @param array $file info uploaded file
	 * @return string with resized jpeg photo
	 */
	function resize_photo($file)
	{
		switch($file['type'])
		{
			case 'image/gif':
				$upload = imagecreatefromgif($file['tmp_name']);
				break;
			case 'image/jpeg':
			case 'image/pjpeg':
				$upload = imagecreatefromjpeg($file['tmp_name']);
				break;
			case 'image/png':
			case 'image/x-png':
				$upload = imagecreatefrompng($file['tmp_name']);
				break;
			default:
				return null;
		}
		if (!$upload) return null;

		list($src_w,$src_h) = getimagesize($file['tmp_name']);

		// scale the image to a width of 60 and a height according to the proportion of the source image
		$photo = imagecreatetruecolor($dst_w = 60,$dst_h = round($src_h * 60 / $src_w));
		imagecopyresized($photo,$upload,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);
		//echo "<p>imagecopyresized(\$photo,\$upload,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);</p>\n";

		ob_start();
		imagejpeg($photo,'',90);
		$jpeg = ob_get_contents();
		ob_end_clean();

		imagedestroy($photo);
		imagedestroy($upload);

		return $jpeg;
	}

	function view($content=null)
	{
		if(is_array($content))
		{
			list($button) = each($content['button']);
			switch ($button)
			{
				case 'vcard':
					$GLOBALS['egw']->redirect_link('/index.php','menuaction=addressbook.uivcard.out&ab_id=' .$content['id']);

				case 'cancel':
					$GLOBALS['egw']->redirect_link('/index.php','menuaction=addressbook.addressbook_ui.index');

				case 'delete':
					$GLOBALS['egw']->redirect_link('/index.php',array(
						'menuaction' => 'addressbook.addressbook_ui.index',
						'msg' => $this->delete($content) ? lang('Contact deleted') : lang('Error deleting the contact !!!'),
					));
			}
		}
		else
		{
			if(!$_GET['contact_id'] || !is_array($content = $this->read($_GET['contact_id'])))
			{
				$GLOBALS['egw']->redirect_link('/index.php',array(
					'menuaction' => 'addressbook.addressbook_ui.index',
					'msg' => $content,
				));
			}
		}
		foreach(array_keys($this->contact_fields) as $key)
		{
			$readonlys[$key] = true;
			if (in_array($key,array('tel_home','tel_work','tel_cell')))
			{
				$readonlys[$key.'2'] = true;
				$content[$key.'2'] = $content[$key];
			}
		}
		$content['view'] = true;
		$content['link_to'] = array(
			'to_app' => 'addressbook',
			'to_id'  => $content['id'],
		);
		$readonlys['link_to'] = $readonlys['customfields'] = $readonlys['fileas_type'] = true;
		$readonlys['button[save]'] = $readonlys['button[apply]'] = $readonlys['change_photo'] = true;
		$readonlys['button[delete]'] = !$content['owner'] || !$this->check_perms(EGW_ACL_DELETE,$content);
		$readonlys['button[edit]'] = !$this->check_perms(EGW_ACL_EDIT,$content);
		$content['disable_change_org'] = true;
// ToDo: fix vCard export
$readonlys['button[vcard]'] = true;

		// how to display addresses
		$content['addr_format']  = $this->addr_format_by_country($content['adr_one_countryname']);
		$content['addr_format2'] = $this->addr_format_by_country($content['adr_two_countryname']);

		$sel_options['fileas_type'][$content['fileas_type']] = $this->fileas($content);
		$sel_options['owner'] = $this->get_addressbooks();
		for($i = -23; $i<=23; $i++) $tz[$i] = ($i > 0 ? '+' : '').$i;
		$sel_options['tz'] = $tz;
		$content['tz'] = $content['tz'] ? $content['tz'] : 0;
		if (count($this->content_types) > 1)
		{
			foreach($this->content_types as $type => $data)
			{
				$sel_options['tid'][$type] = $data['name'];
			}
			$content['typegfx'] = html::image('addressbook',$this->content_types[$content['tid']]['options']['icon'],'',' width="16px" height="16px"');
		}
		else
		{
			$content['no_tid'] = true;
		}
		if (!$this->tmpl->read($this->content_types[$content['tid']]['options']['template']))
		{
			$content['msg']  = lang('WARNING: Template "%1" not found, using default template instead.', $this->content_types[$content['tid']]['options']['template'])."\n";
			$content['msg'] .= lang('Please update the templatename in your customfields section!');
			$this->tmpl->read('addressbook.edit');
		}
		foreach(array('email','email_home','url','url_home') as $name)
		{
			if ($content[$name] )
			{
				$url = substr($name,0,3) == 'url' ? $content[$name] : $this->email2link($content[$name]);
				if (!is_array($url))
				{
					$this->tmpl->set_cell_attribute($name,'size','b,,1');
				}
				elseif ($url)
				{
					$content[$name.'_link'] = $url;
					$this->tmpl->set_cell_attribute($name,'size','b,@'.$name.'_link,,,_blank'.
						($GLOBALS['egw_info']['user']['apps']['felamimail']?',700x750':''));
				}
				$this->tmpl->set_cell_attribute($name,'type','label');
				$this->tmpl->set_cell_attribute($name,'no_lang',true);
			}
		}
		if ($this->private_addressbook && $content['private'] && $content['owner'] == $this->user)
		{
			$content['owner'] .= 'p';
		}
		// disable not needed tabs
		$readonlys[$this->tabs]['cats'] = !($content['cat_tab'] = $this->config['cat_tab']);
		$readonlys[$this->tabs]['custom'] = !$this->customfields;
		$readonlys[$this->tabs]['custom_private'] = !$this->customfields || !$this->config['private_cf_tab'];
		$readonlys[$this->tabs]['distribution_list'] = !$content['distrib_lists'];#false;
		if ($this->config['private_cf_tab']) $content['no_private_cfs'] = 0;

		// last and next calendar date
		list(,$dates) = each($this->read_calendar(array($content['id']),false));
		if(is_array($dates)) $content += $dates;

		// set id for automatic linking via quick add
		$GLOBALS['egw_info']['flags']['currentid'] = $content['id'];

		$this->tmpl->exec('addressbook.addressbook_ui.view',$content,$sel_options,$readonlys,array('id' => $content['id']));

		$GLOBALS['egw']->hooks->process(array(
			'location' => 'addressbook_view',
			'ab_id'    => $content['id']
		));
	}

	/**
	 * convert email-address in compose link
	 *
	 * @param string $email email-addresse
	 * @return array/string array with get-params or mailto:$email, or '' or no mail addresse
	 */
	function email2link($email)
	{
		if (strpos($email,'@') == false) return '';

		if($GLOBALS['egw_info']['user']['apps']['felamimail'])
		{
			return array(
				'menuaction' => 'felamimail.uicompose.compose',
				'send_to'    => base64_encode($email)
			);
		}
		if($GLOBALS['egw_info']['user']['apps']['email'])
		{
			return array(
				'menuaction' => 'email.uicompose.compose',
				'to' => $email,
			);
		}
		return 'mailto:' . $email;
	}

	/**
	 * Extended search
	 *
	 * @param array $_content
	 * @return string
	 */
	function search($_content=array())
	{
		if(!empty($_content)) {
			$response = new xajaxResponse();

			$query = $GLOBALS['egw']->session->appsession($do_email ? 'email' : 'index','addressbook');

			$query['advanced_search'] = array_intersect_key($_content,array_flip(array_merge($this->get_contact_columns(),array('operator','meth_select'))));
			foreach ($query['advanced_search'] as $key => $value)
			{
				if(!$value) unset($query['advanced_search'][$key]);
			}
			$query['start'] = 0;
			$query['search'] = '';
			// store the index state in the session
			$GLOBALS['egw']->session->appsession($do_email ? 'email' : 'index','addressbook',$query);

			// store the advanced search in the session to call it again
			$GLOBALS['egw']->session->appsession('advanced_search','addressbook',$query['advanced_search']);

			$response->addScript("
				var link = opener.location.href;
				link = link.replace(/#/,'');
				opener.location.href=link.replace(/\#/,'');
				xajax_eT_wrapper();
			");
			return $response->getXML();
		}
		else {

		}
		$GLOBALS['egw_info']['flags']['include_xajax'] = true;
		$GLOBALS['egw_info']['flags']['java_script'] .= "<script>window.focus()</script>";
		$GLOBALS['egw_info']['etemplate']['advanced_search'] = true;

		// initialize etemplate arrays
		$sel_options = $readonlys = $preserv = array();
		$content = $GLOBALS['egw']->session->appsession('advanced_search','addressbook');

		for($i = -23; $i<=23; $i++) $tz[$i] = ($i > 0 ? '+' : '').$i;
		$sel_options['tz'] = $tz + array('' => lang('doesn\'t matter'));
		$sel_options['tid'][] = lang('all');
		//foreach($this->content_types as $type => $data) $sel_options['tid'][$type] = $data['name'];

		// configure search options
		$sel_options['owner'] = $this->get_addressbooks(EGW_ACL_READ,lang('all'));
		$sel_options['operator'] =  array(
			'OR' => 'OR',
			'AND' => 'AND'
		);
		$sel_options['meth_select'] = array(
			'%'		=> lang('contains'),
			false	=> lang('exact'),
		);
		if ($this->customfields)
		{
			foreach($this->customfields as $name => $data)
			{
				if ($data['type'] == 'select')
				{
					if (!isset($content['#'.$name])) $content['#'.$name] = '';
					if(!isset($data['values'][''])) $sel_options['#'.$name][''] = lang('Select one');
				}
			}
		}
		// configure edit template as search dialog
		$readonlys['change_photo'] = true;
		$readonlys['fileas_type'] = true;
		$readonlys['creator'] = true;
		$readonlys['button'] = true;
		// disable not needed tabs
		$readonlys[$this->tabs]['cats'] = !($content['cat_tab'] = $this->config['cat_tab']);
		$readonlys[$this->tabs]['custom'] = !$this->customfields;
		$readonlys[$this->tabs]['custom_private'] = !$this->customfields || !$this->config['private_cf_tab'];
		$readonlys[$this->tabs]['links'] = true;
		$readonlys[$this->tabs]['distribution_list'] = true;
		$content['hidebuttons'] = true;
		$content['no_tid'] = true;
		$content['disable_change_org'] = true;

		$this->tmpl->read('addressbook.search');
		return $this->tmpl->exec('addressbook.addressbook_ui.search',$content,$sel_options,$readonlys,$preserv,2);
	}

	/**
	 * download photo of the given ($_GET['contact_id'] or $_GET['account_id']) contact
	 */
	function photo()
	{
		ob_start();
		$contact_id = isset($_GET['contact_id']) ? $_GET['contact_id'] :
			(isset($_GET['account_id']) ? 'account:'.$_GET['account_id'] : 0);

		if (substr($contact_id,0,8) == 'account:')
		{
			$contact_id = $GLOBALS['egw']->accounts->id2name(substr($contact_id,8),'person_id');
		}
		if (!($contact = $this->read($contact_id)) || !$contact['jpegphoto'])
		{
			$GLOBALS['egw']->redirect($GLOBALS['egw']->common->image('addressbook','photo'));
		}
		if (!ob_get_contents())
		{
			header('Content-type: image/jpeg');
			header('Content-length: '.(extension_loaded(mbstring) ? mb_strlen($contact['jpegphoto'],'ascii') : strlen($contact['jpegphoto'])));
			echo $contact['jpegphoto'];
			exit;
		}
	}

	/**
	 * returns link to call the given phonenumber
	 *
	 * replaces '%1' with the phonenumber to call, '%u' with the user's account_lid and '%t' with his work-phone-number
	 *
	 * @param string $number phone number
	 * @param string &$link returns the link
	 * @return boolean true if we have a link, false if not
	 */
	function call_link($number,&$link)
	{
		if (!$number || !$this->config['call_link']) return false;

		static $userphone;
		if (is_null($userphone))
		{
			$user = $this->read('account:'.$GLOBALS['egw_info']['user']['account_id']);
			$userphone = is_array($user) ? ($user['tel_work'] ? $user['tel_work'] : $user['tel_home']) : false;
		}
		$number = str_replace(array(' ','(',')','/','-'),'',$number);	// remove number formatting chars messing up the links

		$link = str_replace(array('%1','%u','%t'),array(urlencode($number),$GLOBALS['egw_info']['user']['account_lid'],$userphone),
			$this->config['call_link']);
	}

	function js()
	{
		return '<script LANGUAGE="JavaScript">

		function showphones(form)
		{
			if (form) {
				copyvalues(form,"tel_home","tel_home2");
				copyvalues(form,"tel_work","tel_work2");
				copyvalues(form,"tel_cell","tel_cell2");
				copyvalues(form,"tel_fax","tel_fax2");
			}
		}

		function hidephones(form)
		{
			if (form) {
				copyvalues(form,"tel_home2","tel_home");
				copyvalues(form,"tel_work2","tel_work");
				copyvalues(form,"tel_cell2","tel_cell");
				copyvalues(form,"tel_fax2","tel_fax");
			}
		}

		function copyvalues(form,src,dst){
			var srcelement = getElement(form,src);  //ById("exec["+src+"]");
			var dstelement = getElement(form,dst);  //ById("exec["+dst+"]");
			if (srcelement && dstelement) {
				dstelement.value = srcelement.value;
			}
		}

		function getElement(form,pattern){
			for (i = 0; i < form.length; i++){
				if(form.elements[i].name){
					var found = form.elements[i].name.search("\\\\["+pattern+"\\\\]");
					if (found != -1){
						return form.elements[i];
					}
				}
			}
		}

		function setName(input)
		{
			var prefix = document.getElementById("exec[n_prefix]").value;
			var given  = document.getElementById("exec[n_given]").value;
			var middle = document.getElementById("exec[n_middle]").value;
			var family = document.getElementById("exec[n_family]").value;
			var suffix = document.getElementById("exec[n_suffix]").value;
			var org    = document.getElementById("exec[org_name]").value;

			var name = document.getElementById("exec[n_fn]");

			name.value = "";
			if (prefix) name.value += prefix+" ";
			if (given) name.value += given+" ";
			if (middle) name.value += middle+" ";
			if (family) name.value += family+" ";
			if (suffix) name.value += suffix;

			xajax_doXMLHTTP("addressbook.addressbook_ui.ajax_setFileasOptions",prefix,given,middle,family,suffix,org);
		}

		function add_whole_list(list)
		{
			if (document.getElementById("exec[nm][email_type][email_home]").checked == true)
			{
				email_type = "email_home";
			}
			else
			{
				email_type = "email";
			}
			xajax_doXMLHTTP("addressbook.addressbook_ui.ajax_add_whole_list",list,email_type);
		}

		function setOptions(options_str)
		{
			var options = options_str.split("\\\\b");
			var selbox = document.getElementById("exec[fileas_type]");
			var i;
			for (i=0; i < options.length; i++)
			{
				selbox.options[i].text = options[i];
			}
		}

		function adb_get_selection(form)
		{
			var use_all = document.getElementById("exec[use_all]");
			var action = document.getElementById("exec[action]");
			egw_openWindowCentered(
				"'. $GLOBALS['egw']->link('/index.php','menuaction=importexport.uiexport.export_dialog&appname=addressbook').
					'&selection="+( use_all.checked  ? "use_all" : get_selected(form,"[rows][checked][]")),
				"Export",400,400);
			action.value="";
			use_all.checked = false;
			return false;
		}

		function add_new_list(owner)
		{
			var name = window.prompt("'.lang('Name for the distribution list').'");
			if (name)
			{
				document.location.href = "'.$GLOBALS['egw']->link('/index.php',array(
					'menuaction'=>$_GET['menuaction'],//'addressbook.addressbook_ui.index',
					'add_list'=>'',
				)).'"+encodeURIComponent(name)+"&owner="+owner;
			}
		}
		</script>';
	}

	function migrate2ldap()
	{
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Addressbook').' - '.lang('Migration to LDAP');
		$GLOBALS['egw']->common->egw_header();
		parse_navbar();

		if (!$this->is_admin())
		{
			echo '<h1>'.lang('Permission denied !!!')."</h1>\n";
		}
		else
		{
			parent::migrate2ldap($_GET['type']);
			echo '<p style="margin-top: 20px;"><b>'.lang('Migration finished')."</b></p>\n";
		}
		$GLOBALS['egw']->common->egw_footer();
	}

	/**
	 * Download a document with inserted contact(s)
	 *
	 * @param array $ids contact-ids
	 * @param string $document vfs-path of document
	 * @return string error-message or error, otherwise the function does NOT return!
	 */
	function download_document($ids,$document='')
	{
		if (!$document)
		{
			$document = $this->prefs['default_document'];
		}
		else
		{
			$document = $this->prefs['document_dir'].'/'.$document;
		}
		if (!@egw_vfs::stat($document))
		{
			return lang("Document '%1' does not exist or is not readable for you!",$document);
		}
		require_once(EGW_INCLUDE_ROOT.'/addressbook/inc/class.addressbook_merge.inc.php');
		$document_merge =& new addressbook_merge();

		return $document_merge->download($document,$ids);
	}

	/**
	 * Returning document actions / files from the document_dir
	 *
	 * @return array
	 */
	function get_document_actions()
	{
		if (!$this->prefs['document_dir']) return array();

		if (!is_array($actions = $GLOBALS['egw']->session->appsession('document_actions','addressbook')))
		{
			$actions = array();
			if (($files = egw_vfs::find($this->prefs['document_dir'],array('need_mime'=>true),true)))
			{
				foreach($files as $file)
				{
					// return only the mime-types we support
					if (!($file['mime'] == 'application/rtf' ||
						$file['mime'] == 'application/msword' && !strcasecmp(substr($file['name'],-4),'.rtf') ||
						substr($file['mime'],0,5) == 'text/')) continue;
					// As browsers not always return the right mime_type, you could use a negative list instead
					//if ($file['mime'] == egw_vfs::DIR_MIME_TYPE  || substr($file['mime'],0,6) == 'image/') continue;

					$actions['document-'.$file['name']] = /*lang('Insert in document').': '.*/$file['name'];
				}
			}
			$GLOBALS['egw']->session->appsession('document_actions','addressbook',$actions);
		}
		return $actions;
	}

	/**
	 * Read the next and last event of given contacts
	 *
	 * @param array $ids contact_id's
	 * @param boolean $extra_title=true if true, use a short date only title and put the full title as extra_title (tooltip)
	 * @return array
	 */
	function read_calendar($ids,$extra_title=true)
	{
		if (!$GLOBALS['egw_info']['user']['apps']['calendar']) return null;

		$uids = array();
		foreach($ids as $id)
		{
			if (is_numeric($id)) $uids[] = 'c'.$id;
		}
		if (!$uids) return array();

		include_once(EGW_INCLUDE_ROOT.'/calendar/inc/class.bocal.inc.php');
		$bocal = new bocal;
		$events = $bocal->search(array(
			'users' => $uids,
			'enum_recuring' => true,
		));
		if (!$events) return array();

		//_debug_array($events);
		$calendars = array();
		foreach($events as $event)
		{
			foreach($event['participants'] as $uid => $status)
			{
				if ($uid{0} != 'c' || ($status == 'R' && !$GLOBALS['egw_info']['user']['preferences']['calendar']['show_rejected']))
				{
					continue;
				}
				$id = (int)substr($uid,1);

				if ($event['start'] < $this->now_su)	// past event --> check for last event
				{
					if (!isset($calendars[$id]['last_event']) || $event['start'] > $calendars[$id]['last_event'])
					{
						$calendars[$id]['last_event'] = $event['start'];
						$link = array(
							'id' => $event['id'],
							'app' => 'calendar',
							'title' => $bocal->link_title($event),
						);
						if ($extra_title)
						{
							$link['extra_title'] = $link['title'];
							$link['title'] = date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'],$event['start']);
						}
						$calendars[$id]['last_link'] = $link;
					}
				}
				else	// future event --> check for next event
				{
					if (!isset($calendars[$id]['next_event']) || $event['start'] < $calendars[$id]['next_event'])
					{
						$calendars[$id]['next_event'] = $event['start'];
						$link = array(
							'id' => $event['id'],
							'app' => 'calendar',
							'title' => $bocal->link_title($event),
						);
						if ($extra_title)
						{
							$link['extra_title'] = $link['title'];
							$link['title'] = date($GLOBALS['egw_info']['user']['preferences']['common']['dateformat'],$event['start']);
						}
						$calendars[$id]['next_link'] = $link;
					}
				}
			}
		}
		//_debug_array($calendars);
		return $calendars;
	}

	/**
	* add a new categorie to any addressbock entry
	*
	* @author Stefan Becker <StefanBecker-AT-outdoor-training.de>
	* @param array $content=null
	*/
	function cat_add($content=null)
	{
		if (is_array($content))
		{
			if ($content['cat_id'])   //add categorie
			{
				if ($content['cat_add'])
				{
					$GLOBALS['egw']->session->appsession('cat_add','addressbook',$content['cat_id']);
					$js = "opener.document.getElementById('exec[action]').value='cat_add'; opener.document.forms.eTemplate.submit();";
				}
				if ($content['cat_delete'])   //delete categorie
				{
					$GLOBALS['egw']->session->appsession('cat_delete','addressbook',$content['cat_id']);
					$js = "opener.document.getElementById('exec[action]').value='cat_add'; opener.document.forms.eTemplate.submit();";
				}
			}
			echo "<html><head><script>$js window.close();</script></head><html>\n";
			$GLOBALS['egw']->common->egw_exit();
		}
		$content['cat_tab'] = $this->config['cat_tab'];

		$this->tmpl->read('addressbook.index.cat_add');
		return $this->tmpl->exec('addressbook.addressbook_ui.cat_add',$content,$sel_options,$readonlys,$content, 2);
	}
}
