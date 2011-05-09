<?php
/**
 * eGroupware Wiki - Business Object
 *
 * @link http://www.egroupware.org
 * @package wiki
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (C) 2004-8 by RalfBecker-AT-outdoor-training.de
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

// old global stuff, is still need for now, but hopefully will go away
global $ParseEngine,$DiffEngine,$DisplayEngine,$ConvertEngine,$SaveMacroEngine,$ViewMacroEngine;
global $UpperPtn,$LowerPtn,$AlphaPtn,$LinkPtn,$UrlPtn,$InterwikiPtn,$MaxNesting,$MaxHeading,$MinEntries,$DayLimit;
global $EditBase,$ViewBase,$HistoryBase,$FindBase,$FindScript;

require_once(EGW_INCLUDE_ROOT.'/wiki/lib/defaults.php');
if (is_object($GLOBALS['egw']->translation) && $GLOBALS['egw']->translation->charset() == 'iso-8859-1')	// allow all iso-8859-1 extra-chars
{
	$UpperPtn = "[A-Z\xc0-\xde]";
	$LowerPtn = "[a-z\xdf-\xff]";
	$AlphaPtn = "[A-Za-z\xc0-\xff]";
	$LinkPtn = $UpperPtn . $AlphaPtn . '*' . $LowerPtn . '+' .
		$UpperPtn . $AlphaPtn . '*(\\/' . $UpperPtn . $AlphaPtn . '*)?';
}

require_once(EGW_INCLUDE_ROOT.'/wiki/lib/url.php');
require_once(EGW_INCLUDE_ROOT.'/wiki/lib/messages.php');

global $pagestore,$FlgChr,$Entity;
$FlgChr = chr(255);                     // Flag character for parse engine.
$Entity = array();                      // Global parser entity list.

require_once(EGW_INCLUDE_ROOT.'/wiki/parse/transforms.php');
require_once(EGW_INCLUDE_ROOT.'/wiki/parse/main.php');
require_once(EGW_INCLUDE_ROOT.'/wiki/parse/macros.php');
require_once(EGW_INCLUDE_ROOT.'/wiki/parse/html.php');
require_once(EGW_INCLUDE_ROOT.'/wiki/parse/save.php');

require_once(EGW_INCLUDE_ROOT.'/wiki/lib/category.php');

class wiki_bo extends wiki_so
{
	var $config;

	function __construct($wiki_id=0)
	{
		parent::__construct($wiki_id);

		global $pagestore;
		if (!is_object($pagestore))
		{
			$pagestore = new wiki_so($wiki_id);	// cant use =& as global $pagestore is a reverence!
		}
		global $Admin,$HomePage,$InterWikiPrefix,$EnableFreeLinks,$EnableWikiLinks;
		$c =& CreateObject('phpgwapi.config','wiki');
		$c->read_repository();
		$this->config = $c->config_data;
		unset($c);

		$Admin = $this->config['emailadmin'];
		if (!isset($this->config['wikihome'])) $this->config['wikihome'] = 'eGroupWare';
		$HomePage = $this->config['wikihome'];
		$InterWikiPrefix = isset($this->config['InterWikiPrefix'])   ? $this->config['InterWikiPrefix'] : 'EGroupWare';
		$EnableFreeLinks = isset($this->config['Enable_Free_Links']) ? $this->config['Enable_Free_Links'] == 'True' : true;
		$EnableWikiLinks = isset($this->config['Enable_Wiki_Links']) ? $this->config['Enable_Wiki_Links'] == 'True' : true;

		$this->ExpireLen = $this->config['ExpireLen'];

		global $Charset,$UserName;
		$Charset = $GLOBALS['egw']->translation->charset();
		$UserName = $GLOBALS['egw_info']['user']['account_lid'];

		$this->AutoconvertPages = $this->config['AutoconvertPages'];

		global $ViewBase,$EditBase;
		$ViewBase = $this->viewURL('');
		if(!isset($EditBase)) { $EditBase = $this->editURL(''); }
	}

	function bowiki($wiki_id=0)
	{
		self::__construct($wiki_id);
	}

	/**
	 * Generate a short summary for the search-result from the page-content
	 *
	 * @param array $page array with keys name, title, lang and text
	 * @return string
	 */
	function summary($page)
	{
		$text = $page['text'];
		// remove pictures
		$text = preg_replace('/egw:[a-z]+\\/[a-z.-]+ /i','',$text);
		// replace freelinks with their title
		$text = preg_replace('/\\(\\([^|]*\\|? ?([^)]+)\\)\\)/','\\1',$text);
		// remove some formatting and the title itself
		$text = str_replace(array('= '.$page['title'].' =','=','#','*',"'''","''",'----'),'',$text);
		// remove html tags
		$text = strip_tags($text);

		return substr($text,0,330);
	}

	function get($page,$lang='',$wiki_id=0)
	{
		if (!is_object($page))
		{
			$page = $this->page($page,$lang,$wiki_id);
			$page->read();
		}
		return $this->parse($page);
	}

	function parse($page,$engine='Parse',$name='')
	{
		if (is_object($page))
		{
			$text = $page->text;
			$name = $name ? $name : $page->name;
		}
		elseif (is_array($page))
		{
			$text = $page['text'];
			$name = $name ? $name : $page['name'];
		}
		else
		{
			$text = $page;
		}
		switch($engine)
		{
			case 'Convert': case 'convert':
				$engine = $GLOBALS['ConvertEngine'];
				break;
			case 'Parse': case 'parse':
			default:
				$engine = $GLOBALS['ParseEngine'];
				break;
			case 'Diff': case 'diff':
				$engine = $GLOBALS['DiffEngine'];
				break;
			case 'Save': case 'save':
				$engine = $GLOBALS['SaveMacroEngine'];
				break;
		}
		//echo "<p>parseText(\$text,\$engine,'$name'); \$engine=<pre>\n".print_r($engine,True)."</pre>";
		return parseText($text,$engine,$name);
	}

	function write($values,$set_host_user=True)
	{
		//error_log(__METHOD__. " Values to save (".print_r($values,True).")");
		$page = $this->page($values['name'],$values['lang']);
		//error_log(__METHOD__.' PageObject:'.array2string($page));
		if ($page->read() !== False)	// !== as an empty page would return '' == False
		{
			$page->version++;
		}
		else
		{
			$page->version = 1;
		}
		$needs_write = False;
		foreach(array('text','title','comment','readable','writable') as $name)
		{
			$needs_write = $needs_write || $page->$name != $values[$name];
			$page->$name = $values[$name];
		}
		if (!$needs_write) return False;	// no change => dont write it back

		$page->hostname = $set_host_user ? gethostbyaddr($_SERVER['REMOTE_ADDR']) : $values['hostname'];
		$page->username = $set_host_user ? $GLOBALS['egw_info']['user']['account_lid'] : $values['username'];

		$page->write();

		// Check for notifications that need to be sent
		$this->send_notifications($page);
		
		$GLOBALS['page'] = $page->as_array();	// we need this to track lang for new_link, sister_wiki, ...

		if(!empty($values['category']))		// Editor asked page to be added to a category or categories.
		{
			add_to_category($page, $values['category']);
		}
		// delete the links of the page
		$this->clear_link($values);
		// Process save macros (eg. store the links or define interwiki entries).
		$this->parse($page,'Save');
		// delete the page finaly if it is to be expunged
		//error_log(__METHOD__.$page->text.','.$page->comment.'.');
		if (trim($page->text)=='##EXPUNGE##' && trim($page->comment)=='##EXPUNGE##') $this->expunge_page($values);

		return True;
	}

	function rename_links($old_name,$name,$title,$text)
	{
		global $LinkPtn;
		//echo "<p>rename_links('$old_name','$name','$title'), preg_match('/$LinkPtn/',\$name)=".(preg_match('/'.$LinkPtn.'/',$name)?'True':'False')."</p>";

		$is_wiki_link = preg_match('/'.$LinkPtn.'/',$name);

		// construct the new link
		$new_link = $name != $title ? '(('.$name.'|'.$title.'))' : ($is_wiki_link ? $name : '(('.$name.'))');

		$to_replace = array(
			'/\(\('.preg_quote($old_name).'\ ?\| ?[^)]+\)\)/i',	// free link with given appearence
			'/\(\('.preg_quote($old_name).'\)\)/i',				// free link
		);
		if (preg_match('/'.$LinkPtn.'/',$old_name))		// only replace the plain old_name, if it is a wiki link
		{
			$to_replace[] = '/(?=\b)'.preg_quote($old_name).'(?=\b)/i';	// wiki link
			$to_replace[] = '/(?=>)'.preg_quote($old_name).'(?=\b)/i';	// wiki link in mixed mode with leading tag
			$to_replace[] = '/(?=\b)'.preg_quote($old_name).'(?=<)/i';	// wiki link in mixed mode with trailing tag
			$to_replace[] = '/(?=>)'.preg_quote($old_name).'(?=<)/i';	// wiki link enclosed in Tags
		}
		return preg_replace($to_replace,$new_link,$text);
	}

	function rename(&$values,$old_name,$old_lang)
	{
		@set_time_limit(0);
		//echo "<p>bowiki::rename '$old_name:$old_lang' to '$values[name]:$values[lang]'</p>";
		$page = $this->page($old_name,$old_lang);

		if ($page->read() === False || !$page->rename($values['name'],$values['lang']))
		{
			//echo "<p>\$page->rename('$values[name]','$values[lang]') == False</p>";
			return False;
		}
		// change all links to old_name with the new link
		foreach($this->get_links($old_name) as $page => $langs)
		{
			foreach($langs as $lang => $link)
			{
				$to_replace = $this->page($page,$lang);
				if ($to_replace->read() !== False)
				{
					$to_replace = $to_replace->as_array();
					$to_replace['text'] = $this->rename_links($old_name,$values['name'],$values['title'],$was=$to_replace['text']);
					$to_replace['comment'] = $old_name . ($old_lang && $old_lang != $values['lang'] ? ':'.$old_lang : '') . ' --> ' .
						$values['name'] . ($values['lang']  && $old_lang != $values['lang'] ? ':'.$values['lang'] : '');
					//echo "<p><b>$to_replace[name]</b>: $to_replace[comment]<br>\n<b>From:</b><br>\n$was<br>\n<b>To</b><br>\n$to_replace[text]</p>\n";
					$this->write($to_replace);
				}
			}
		}
		// also rename links in our own content
		$values['text'] = $this->rename_links($old_name,$values['name'],$values['title'],$values['text']);

		foreach(array('text','title','comment','readable','writable') as $name)
		{
			if (isset($values[$name]) && $values[$name] != $page->$name)
			{
				// other changes, write them
				return $this->write($values);
			}
		}
		// delete the links of the old page
		$this->clear_link(array('name' => $old_name,'lang' => $old_lang));

		$GLOBALS['page'] = $page->as_array();	// we need this to track lang for new_link, sister_wiki, ...
		if(!empty($values['category']))		// Editor asked page to be added to a category or categories.
		{
			add_to_category($page, $values['category']);
		}
		// Process save macros (eg. store the links or define interwiki entries).
		$this->parse($page,'Save');
	}

	function editURL($page, $lang='',$version = '')
	{
		$args = array(
			'menuaction' => 'wiki.wiki_ui.edit',
			'page' => is_array($page) ? $page['name'] : $page
		);
		if ($lang || is_array($page) && $page['lang'])
		{
			$args['lang'] = $lang ? $lang : @$page['lang'];
			if ($args['lang'] == $GLOBALS['egw_info']['user']['prefereces']['common']['lang']) unset($args['lang']);
		}
		if ($version)
		{
			$args['version'] = $version;
		}
		return $GLOBALS['egw']->link('/index.php',$args);
	}

	function viewURL($page, $lang='', $version='', $full = '')
	{
		$args = array(
			'menuaction' => 'wiki.wiki_ui.view',
		);
		if ($lang || is_array($page) && $page['lang'])
		{
			$args['lang'] = $lang ? $lang : $page['lang'];
			if ($args['lang'] == $GLOBALS['egw_info']['user']['prefereces']['common']['lang']) unset($args['lang']);
		}
		if ($version)
		{
			$args['version'] = $version;
		}
		if ($full)
		{
			$args['full'] = 1;
		}
		// the page-parameter has to be the last one, as the old wiki code only calls it once with empty page and appends the pages later
		return $GLOBALS['egw']->link('/index.php',$args).'&page='.urlencode(is_array($page) ? $page['name'] : $page);
	}

	function historyURL($page, $full = '',$lang='')
	{
		global $HistoryBase;

		if ($lang || (is_array($page) && isset($page['lang'])))
		{
			$lang = '&lang=' . ($lang ? $lang : $page['lang']);
		}
		return $HistoryBase . urlencode(is_array($page) ? $page['name'] : $page) . $lang;
				($full == '' ? '' : '&full=1');
	}

	/**
	 * Hook called by link-class to include infolog in the appregistry of the linkage
	 *
	 * @param array/string $location location and other parameters (not used)
	 * @deprecated use wiki_hooks::search_link() moved there, because it get's called at setup time, which fails here!
	 * @return array with method-names
	 */
	static function search_link($location)
	{
		return wiki_hooks::search_link($location);
	}

	/**
	 * get title of a wiki-page identified by $page
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param string/object $page string with page-name or sowikipage object
	 * @return string/boolean string with title, null if page not found or false if not view perms
	 */
	function link_title( $page )
	{
		if (!is_object($page))
		{
			$page =& $this->page( $page );
			$page->read();
		}
		if (!$page->exists) return null;

		return $page->acl_check(true)  ? strip_tags($page->title) : false;
	}

	/**
	 * query wiki for pages matching $pattern
	 *
	 * Is called as hook to participate in the linking
	 *
	 * @param string $pattern pattern to search
	 * @return array with info_id - title pairs of the matching entries
	 */
	function link_query( $pattern )
	{
		$content = array();
		foreach($this->find($pattern) as $page)
		{
			$content[$page['name']] = strip_tags($page['title']);
		}
		return $content;
	}

	/**
	 * Send any notifications of a page change
	 *
	 * A user wants notifications if they set it in their preferences
	 *
	 * @param $page Page that changed
	 */
	function send_notifications($page)
	{

		$save_prefs = $GLOBALS['egw_info']['user'];
		$values = array(
			'Title'         =>      $page->title,
			'Editor'	=>	common::display_fullname('','','',$GLOBALS['egw_info']['user']['account_id']),
			'Summary'       =>      $page->comment,
			'Category'      =>      $page->category,
			'Content'	=>	$page->text,
		);

		if(substr($page->text, 0, 6) != '<html>')
		{
			$text = $page->text;
			// remove pictures
			$text = preg_replace('/egw:[a-z]+\\/[a-z.-]+ /i','',$text);
			// replace freelinks with their title
			$text = preg_replace('/\\(\\([^|]*\\|? ?([^)]+)\\)\\)/','\\1',$text);
			// remove some formatting and the title itself
			$text = str_replace(array('= '.$page->title.' =','=','#','*',"'''","''",'----'),'',$text);
			// remove html tags
			$text = strip_tags($text);
			$values['Content'] = nl2br($text);
		}

		// Need to get a list of people who want the change
		$user_ids = array();
		$notification = array();

		// Find out who's involved
		foreach(array('read'=>$page->readable, 'write' => $page->writable) as $name => $perm) {
			$user_ids[$name] = array();
			foreach($perm as $group)
			{
				$filter = array(
					'type'	=>	'accounts',
					'app'	=>	'wiki'
				);
				if($group[0] == '_')
				{
					// Pseudo groups
					switch($group) {
						case WIKI_ACL_ADMIN:
							$accounts = $GLOBALS['egw']->accounts->search($filter);
							$filter['app'] = 'admin';
							$admins = $GLOBALS['egw']->accounts->search($filter);
							$user_ids[$name] += array_intersect(array_keys($accounts), array_keys($admins));
							break;
						case WIKI_ACL_USER:
						case WIKI_ACL_ALL:
							$accounts = $GLOBALS['egw']->accounts->search($filter);
							$user_ids[$name] += array_keys($accounts);
							break;
					}
				} else {
					$members = array_merge($user_ids[$name], $GLOBALS['egw']->accounts->members($group, true));
					$accounts = $GLOBALS['egw']->accounts->search($filter);
					$user_ids[$name] = array_intersect(array_keys($accounts), $members);
				}
			}
			$user_ids[$name] = array_unique($user_ids[$name]);
		}
		$id_list = array_unique(array_merge($user_ids['read'], $user_ids['write']));

		// Check to see if they want notification
		foreach($id_list as $id)
		{
			if($id == $GLOBALS['egw_info']['user']['account_id']) continue;
			$prefs = new preferences($id);
			$data = $prefs->read_repository(false);
			$regex = true;
			if($data['wiki']['notification_regex']) {
				// Break pseudo regexes (field: search) into real regexes
				$regexes = self::parse_regex($data['wiki']['notification_regex']);
				foreach($regexes as $field => $test) {
					$regex = $regex && preg_match($test, $page->$field) == 1;
				}
			}
			if(($regex && in_array($id, $user_ids['read'])) || 
					($data['wiki']['notification_read'] && in_array($id, $user_ids['read'])) || 
					($data['wiki']['notification_write'] && in_array($id, $user_ids['write']))) {
				$message = $prefs->parse_notify(nl2br($data['wiki']['notification_message']), $values, true);
				if(trim($message) != '') {
					$notification[$message][] = $id;
				}
			}
		}
		if(count($notification) > 0) {
			foreach($notification as $message => $ids) {
				$n = new notifications();
				$n->set_sender($GLOBALS['egw_info']['user']['account_id']);
				$n->set_receivers($ids);
				$n->set_subject($page->title);
				$n->set_message($message . "\n");
				$n->add_link($page->title, array(
					'menuaction'	=>	'wiki.wiki_ui.view',
					'lang'		=>	$page->lang,
					'version'	=>	$page->version,
					'page'		=>	$page->name
				));
				$n->send();
				$n = null;
			}
		}

		// Restore user environment
		$GLOBALS['egw_info']['user'] = $save_prefs;
	}

	/**
	 * Parse a pseudo-regex in the form of field: value (optional or)
	 * into a real regex that can be used to search
	 */
	public static function parse_regex($in)
	{
		$regex = array();
		if(strpos($in, ':') === false)
		{
			$regex['name'] = trim($in);
		}
		else
		{
			$fields = array('title','name','lang','text');
			$pattern = '/('.implode('|',$fields).'):([^\n]*)/';
			$patterns = array();
			preg_match_all($pattern, $in, $patterns);
			for($i = 0; $i < count($patterns[0]); $i++) {
				$regex[$patterns[1][$i]] = trim($patterns[2][$i]);
			}
		}

		// Make sure there are delimiters
		foreach($regex as $field => &$pattern)
		{
			if(substr($pattern,0,1) !== substr($pattern, -1) || !in_array(substr($pattern,0,1), array('/','#','~','@')))
			{
				$pattern = '/'.$pattern.'/';
			}
		}
		
		return $regex;
	}
}
