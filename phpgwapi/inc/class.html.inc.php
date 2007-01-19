<?php
/**
 * generates html with methods representing html-tags or higher widgets
 *
 * @link http://www.egroupware.org
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de> complete rewrite in 6/2006 and earlier modifications
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

/**
 * generates html with methods representing html-tags or higher widgets
 *
 * @package api
 * @subpackage html
 * @access public
 * @author RalfBecker-AT-outdoor-training.de
 * @license GPL
 */
class html
{
	/**
	 * user-agent: 'mozilla','msie','konqueror', 'safari', 'opera'
	 * @var string
	 */
	var $user_agent;
	/**
	 * version of user-agent as specified by browser
	 * @var string
	 */
	var $ua_version;
	/**
	 * what attribute to use for the title of an image: 'title' for everything but netscape4='alt'
	 * @var string
	 */
	var $prefered_img_title;
	/**
	 * charset used by the page, as returned by $GLOBALS['egw']->translation->charset()
	 * @var string
	 */
	var $charset;
	/**
	 * URL (NOT path) of the js directory in the api
	 * @var string
	 */
	var $phpgwapi_js_url;
	/**
	 * do we need to set the wz_tooltip class, to be included at the end of the page
	 * @var boolean
	 */
	var $wz_tooltip_included = False;

	/**
	 * Constructor: initialised the class-vars
	 */
	function html()
	{
		// should be Ok for all HTML 4 compatible browsers
		if (!eregi('(Safari)/([0-9.]+)',$_SERVER['HTTP_USER_AGENT'],$parts) &&
			!eregi('compatible; ([a-z_]+)[/ ]+([0-9.]+)',$_SERVER['HTTP_USER_AGENT'],$parts))
		{
			eregi('^([a-z_]+)/([0-9.]+)',$_SERVER['HTTP_USER_AGENT'],$parts);
		}
		list(,$this->user_agent,$this->ua_version) = $parts;
		$this->user_agent = strtolower($this->user_agent);

		$this->netscape4 = $this->user_agent == 'mozilla' && $this->ua_version < 5;
		$this->prefered_img_title = $this->netscape4 ? 'alt' : 'title';
		//echo "<p>HTTP_USER_AGENT='$_SERVER[HTTP_USER_AGENT]', UserAgent: '$this->user_agent', Version: '$this->ua_version', img_title: '$this->prefered_img_title'</p>\n";

		if ($GLOBALS['egw']->translation)
		{
			$this->charset = $GLOBALS['egw']->translation->charset();
		}
		$this->phpgwapi_js_url = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js';
	}

	/**
	* Created an input-field with an attached color-picker
	*
	* Please note: it need to be called before the call to phpgw_header() !!!
	*
	* @param string $name the name of the input-field
	* @param string $value the actual value for the input-field, default ''
	* @param string $title tooltip/title for the picker-activation-icon
	* @return string the html
	*/
	function inputColor($name,$value='',$title='')
	{
		$id = str_replace(array('[',']'),array('_',''),$name).'_colorpicker';
		$onclick = "javascript:window.open('".$this->phpgwapi_js_url.'/colorpicker/select_color.html?id='.urlencode($id)."&color='+document.getElementById('$id').value,'colorPicker','width=240,height=187,scrollbars=no,resizable=no,toolbar=no');";
		return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$this->htmlspecialchars($value).'" /> '.
			'<a href="#" onclick="'.$onclick.'">'.
			'<img src="'.$this->phpgwapi_js_url.'/colorpicker/ed_color_bg.gif'.'"'.($title ? ' title="'.$this->htmlspecialchars($title).'"' : '')." /></a>";
	}

	/**
	* Handles tooltips via the wz_tooltip class from Walter Zorn
	*
	* Note: The wz_tooltip.js file gets automaticaly loaded at the end of the page
	*
	* @param string/boolean $text text or html for the tooltip, all chars allowed, they will be quoted approperiate
	*	Or if False the content (innerHTML) of the element itself is used.
	* @param boolean $do_lang (default False) should the text be run though lang()
	* @param array $options param/value pairs, eg. 'TITLE' => 'I am the title'. Some common parameters:
	*  title (string) gives extra title-row, width (int,'auto') , padding (int), above (bool), bgcolor (color), bgimg (URL)
	*  For a complete list and description see http://www.walterzorn.com/tooltip/tooltip_e.htm
	* @return string to be included in any tag, like '<p'.$html->tooltip('Hello <b>Ralf</b>').'>Text with tooltip</p>'
	*/
	function tooltip($text,$do_lang=False,$options=False)
	{
		if (!$this->wz_tooltip_included)
		{
			if (strpos($GLOBALS['egw_info']['flags']['need_footer'],'wz_tooltip')===false)
			{
				$GLOBALS['egw_info']['flags']['need_footer'] .= '<script language="JavaScript" type="text/javascript" src="'.$this->phpgwapi_js_url.'/wz_tooltip/wz_tooltip.js"></script>'."\n";
			}
			$this->wz_tooltip_included = True;
		}
		if ($do_lang) $text = lang($text);

		$opt_out = 'this.T_WIDTH = 200;';
		if (is_array($options))
		{
			foreach($options as $option => $value)
			{
				$opt_out .= 'this.T_'.strtoupper($option).'='.(is_numeric($value)?$value:"'".str_replace(array("'",'"'),array("\\'",'&quot;'),$value)."'").'; ';

			}
		}
		if ($text === False) return ' onmouseover="'.$opt_out.'return escape(this.innerHTML);"';

		return ' onmouseover="'.$opt_out.'return escape(\''.str_replace(array("\n","\r","'",'"'),array('','',"\\'",'&quot;'),$text).'\')"';
	}

	/**
	 * activates URLs in a text, URLs get replaced by html-links
	 *
	 * @param string $content text containing URLs
	 * @return string html with activated links
	 */
	function activate_links($content)
	{
		// Exclude everything which is already a link
		$NotAnchor = '(?<!"|href=|href\s=\s|href=\s|href\s=)';

		// spamsaver emailaddress
		$result = preg_replace('/'.$NotAnchor.'mailto:([a-z0-9._-]+)@([a-z0-9_-]+)\.([a-z0-9._-]+)/i',
			'<a href="#" onclick="document.location=\'mai\'+\'lto:\\1\'+unescape(\'%40\')+\'\\2.\\3\'; return false;">\\1 AT \\2 DOT \\3</a>',
			$content);

		//  First match things beginning with http:// (or other protocols)
		$Protocol = '(http|ftp|https):\/\/';
		$Domain = '([\w]+.[\w]+)';
		$Subdir = '([\w\-\.,@?^=%&;:\/~\+#]*[\w\-\@?^=%&\/~\+#])?';
		$Expr = '/' . $NotAnchor . $Protocol . $Domain . $Subdir . '/i';

		$result = preg_replace( $Expr, "<a href=\"$0\" target=\"_blank\">$2$3</a>", $result );

		//  Now match things beginning with www.
		$NotHTTP = '(?<!:\/\/)';
		$Domain = 'www(.[\w]+)';
		$Subdir = '([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?';
		$Expr = '/' . $NotAnchor . $NotHTTP . $Domain . $Subdir . '/i';

		return preg_replace( $Expr, "<a href=\"http://$0\" target=\"_blank\">$0</a>", $result );
	}

	/**
	 * escapes chars with special meaning in html as entities
	 *
	 * Allows to use and char in the html-output and prefents XSS attacks.
	 * Some entities are allowed and get NOT escaped:
	 * - &# some translations (AFAIK the arabic ones) need this
	 * - &nbsp; &lt; &gt; for convinience
	 *
	 * @param string $str string to escape
	 * @return string
	 */
	function htmlspecialchars($str)
	{
		// add @ by lkneschke to supress warning about unknown charset
		$str = @htmlspecialchars($str,ENT_COMPAT,$this->charset);

		// we need '&#' unchanged, so we translate it back
		$str = str_replace(array('&amp;#','&amp;nbsp;','&amp;lt;','&amp;gt;'),array('&#','&nbsp;','&lt;','&gt;'),$str);

		return $str;
	}

	/**
	 * allows to show and select one item from an array
	 *
	 * @param string $name	string with name of the submitted var which holds the key of the selected item form array
	 * @param string/array $key key(s) of already selected item(s) from $arr, eg. '1' or '1,2' or array with keys
	 * @param array $arr array with items to select, eg. $arr = array ( 'y' => 'yes','n' => 'no','m' => 'maybe');
	 * @param boolean $no_lang NOT run the labels of the options through lang(), default false=use lang()
	 * @param string $options additional options (e.g. 'width')
	 * @param int $multiple number of lines for a multiselect, default 0 = no multiselect, < 0 sets size without multiple
	 * @return string to set for a template or to echo into html page
	 */
	function select($name, $key, $arr=0,$no_lang=false,$options='',$multiple=0)
	{
		if (!is_array($arr))
		{
			$arr = array('no','yes');
		}
		if ((int)$multiple > 0)
		{
			$options .= ' multiple="1" size="'.(int)$multiple.'"';
			if (substr($name,-2) != '[]')
			{
				$name .= '[]';
			}
		}
		elseif($multiple < 0)
		{
			$options .= ' size="'.abs($multiple).'"';
		}
		$out = "<select name=\"$name\" $options>\n";

		if (!is_array($key))
		{
			// explode on ',' only if multiple values expected and the key contains just numbers and commas
			$key = $multiple > 0 && preg_match('/^[,0-9]+$/',$key) ? explode(',',$key) : array($key);
		}
		foreach($arr as $k => $data)
		{
			if (!is_array($data) || count($data) == 2 && isset($data['label']) && isset($data['title']))
			{
				$out .= $this->select_option($k,is_array($data)?$data['label']:$data,$key,$no_lang,
					is_array($data)?$data['title']:'');
			}
			else
			{
				$out .= '<optgroup label="'.$this->htmlspecialchars($no_lang || $k == '' ? $k : lang($k))."\">\n";

				foreach($data as $k => $label)
				{
					$out .= $this->select_option($k,is_array($label)?$label['label']:$label,$key,$no_lang,
						is_array($label)?$lable['title']:'');
				}
				$out .= "</optgroup>\n";
			}
		}
		$out .= "</select>\n";

		return $out;
	}

	/**
	 * emulating a multiselectbox using checkboxes
	 *
	 * Unfortunaly this is not in all aspects like a multi-selectbox, eg. you cant select options via javascript
	 * in the same way. Therefor I made it an extra function.
	 *
	 * @param string $name	string with name of the submitted var which holds the key of the selected item form array
	 * @param string/array $key key(s) of already selected item(s) from $arr, eg. '1' or '1,2' or array with keys
	 * @param array $arr array with items to select, eg. $arr = array ( 'y' => 'yes','n' => 'no','m' => 'maybe');
	 * @param boolean $no_lang NOT run the labels of the options through lang(), default false=use lang()
	 * @param string $options additional options (e.g. 'width')
	 * @param int $multiple number of lines for a multiselect, default 3
	 * @param boolean $selected_first show the selected items before the not selected ones, default true
	 * @param string $style='' extra style settings like "width: 100%", default '' none
	 * @return string to set for a template or to echo into html page
	 */
	function checkbox_multiselect($name, $key, $arr=0,$no_lang=false,$options='',$multiple=3,$selected_first=true,$style='')
	{
		//echo "<p align=right>checkbox_multiselect('$name',".print_r($key,true).",".print_r($arr,true).",$no_lang,'$options',$multiple,$selected_first,'$style')</p>\n";
		if (!is_array($arr))
		{
			$arr = array('no','yes');
		}
		if ((int)$multiple <= 0) $multiple = 1;

		if (substr($name,-2) != '[]')
		{
			$name .= '[]';
		}
		$base_name = substr($name,0,-2);

		if (!is_array($key))
		{
			// explode on ',' only if multiple values expected and the key contains just numbers and commas
			$key = preg_match('/^[,0-9]+$/',$key) ? explode(',',$key) : array($key);
		}
		$html = '';
		$options_no_id = preg_replace('/id="[^"]+"/i','',$options);

		if ($selected_first)
		{
			$selected = $not_selected = array();
			foreach($arr as $val => $label)
			{
				if (in_array($val,$key,!$val))
				{
					$selected[$val] = $label;
				}
				else
				{
					$not_selected[$val] = $label;
				}
			}
			$arr = $selected + $not_selected;
		}
		foreach($arr as $val => $label)
		{
			if (is_array($label))
			{
				$title = $label['title'];
				$label = $label['label'];
			}
			else
			{
				$title = '';
			}
			if ($label && !$no_lang) $label = lang($label);
			if ($title && !$no_lang) $title = lang($title);

			if (strlen($label) > $max_len) $max_len = strlen($label);

			$html .= $this->label($this->checkbox($name,in_array($val,$key),$val,$options_no_id.
				' id="'.$base_name.'['.$val.']'.'"').$this->htmlspecialchars($label),
				$base_name.'['.$val.']','',($title ? 'title="'.$this->htmlspecialchars($title).'" ':''))."<br />\n";
		}
		if ($style && substr($style,-1) != ';') $style .= '; ';
		if (strpos($style,'height')===false) $style .= 'height: '.(1.7*$multiple).'em; ';
		if (strpos($style,'width')===false)  $style .= 'width: '.(4+$max_len*($max_len < 15 ? 0.65 : 0.55)).'em; ';
		$style .= 'background-color: white; overflow: auto; border: lightgray 2px inset;';

		return $this->div($html,$options,'',$style);
	}

	/**
	 * generates an option-tag for a selectbox
	 *
	 * @param string $value value
	 * @param string $label label
	 * @param mixed $selected value or array of values of options to mark as selected
	 * @param boolean $no_lang NOT running the label through lang(), default false=use lang()
	 * @return string html
	 */
	function select_option($value,$label,$selected,$no_lang=0,$title='')
	{
		// the following compares strict as strings, to archive: '0' == 0 != ''
		// the first non-strict search via array_search, is for performance reasons, to not always search the whole array with php
		if (($found = ($key = array_search($value,$selected)) !== false) && (string) $value !== (string) $selected[$key])
		{
			$found = false;
			foreach($selected as $sel)
			{
				if ($found = (((string) $value) === ((string) $selected[$key]))) break;
			}
		}
		return '<option value="'.$this->htmlspecialchars($value).'"'.($found  ? ' selected="selected"' : '') .
			($title ? ' title="'.$this->htmlspecialchars($no_lang ? $title : lang($title)).'"' : '') . '>'.
			$this->htmlspecialchars($no_lang || $label == '' ? $label : lang($label)) . "</option>\n";
	}

	/**
	 * generates a div-tag
	 *
	 * @param string $content of a div, or '' to generate only the opening tag
	 * @param string $options to include in the tag, default ''=none
	 * @param string $class css-class attribute, default ''=none
	 * @param string $style css-styles attribute, default ''=none
	 * @return string html
	 */
	function div($content,$options='',$class='',$style='')
	{
		if ($class) $options .= ' class="'.$class.'"';
		if ($style) $options .= ' style="'.$style.'"';

		return "<div $options>\n".($content ? "$content</div>\n" : '');
	}

	/**
	 * generate one or more hidden input tag(s)
	 *
	 * @param array/string $vars var-name or array with name / value pairs
	 * @param string $value value if $vars is no array, default ''
	 * @param boolean $ignore_empty if true all empty, zero (!) or unset values, plus filer=none
	 * @param string html
	 */
	function input_hidden($vars,$value='',$ignore_empty=True)
	{
		if (!is_array($vars))
		{
			$vars = array( $vars => $value );
		}
		foreach($vars as $name => $value)
		{
			if (is_array($value))
			{
				$value = serialize($value);
			}
			if (!$ignore_empty || $value && !($name == 'filter' && $value == 'none'))	// dont need to send all the empty vars
			{
				$html .= "<input type=\"hidden\" name=\"$name\" value=\"".$this->htmlspecialchars($value)."\" />\n";
			}
		}
		return $html;
	}

	/**
	 * generate a textarea tag
	 *
	 * @param string $name name attr. of the tag
	 * @param string $value default
	 * @param boolean $ignore_empty if true all empty, zero (!) or unset values, plus filer=none
	 * @param string html
	 */
	function textarea($name,$value='',$options='' )
	{
		return "<textarea name=\"$name\" $options>".$this->htmlspecialchars($value)."</textarea>\n";
	}

	/**
	 * Checks if HTMLarea (or an other richtext editor) is availible for the used browser
	 *
	 * @return boolean
	 */
	function htmlarea_availible()
	{
		switch($this->user_agent)
		{
			case 'msie':
				return $this->ua_version >= 5.5;
			case 'mozilla':
				return $this->ua_version >= 1.3;
			default:
				return False;
		}
	}

	/**
	 * compability function for former used htmlarea. Please use function fckeditor now!
	 *
	 * creates a textarea inputfield for the htmlarea js-widget (returns the necessary html and js)
	 */
	function htmlarea($name,$content='',$style='',$base_href='',$plugins='',$custom_toolbar='',$set_width_height_in_config=false)
	{
		if (!$this->htmlarea_availible())
		{
			return $this->textarea($name,$content,'style="'.$style.'"');
		}
		return $this->fckEditor($name, $content, 'extended', array('toolbar_expanded' =>'true'), '400px', '100%', $base_href);
	}

	
	
	/**
	* init the tinymce js-widget by adding the js file in the head of the page
	*
	* Please note: it need to be called before the call to phpgw_header() !!!
	*
	
	function init_tinymce()
	{
	   // do stuff once
	   if (!is_object($GLOBALS['egw']->js))
	   {
		  $GLOBALS['egw']->js = CreateObject('phpgwapi.javascript');
	   }

	   if (strpos($GLOBALS['egw_info']['flags']['java_script'],'tinyMCE')===false)
	   {
		  $GLOBALS['egw']->js->validate_file('tiny_mce','tiny_mce');
	   }
	}*/

	/**
	* creates a textarea inputfield for the tinymce js-widget (returns the necessary html and js)
	*
	* Please note: if you did not run init_tinymce already you this function need to be called before the call to phpgw_header() !!!
	*
	* @param string $name name and id of the input-field
	* @param string $content='' of the tinymce (will be run through htmlspecialchars !!!), default ''
	* @param string $style='' initial css for the style attribute
	* @param string $init_options='', see http://tinymce.moxiecode.com/ for all init options. mode and elements are allready set.
	*                                 to make things more easy, you also can just provide a comma seperated list here with the options you need.
	*                                 Supportet are: 'TableOperations','ContextMenu','ColorChooser'
	* @param string $base_href=''
	* @return string the necessary html for the textarea
	* @todo make wrapper for backwards compatibility with htmlarea
	* @todo enable all features from htmlarea
	
	function tinymce($name,$content='',$style='',$init_options='',$base_href='')
	{
		if (!$style)
		{
			$style = 'width:100%; min-width:500px; height:300px;';
		}
		if (!$this->htmlarea_availible())
		{
			return $this->textarea($name,$content,'style="'.$style.'"');
		}

		// do stuff once
		$this->init_tinymce();

		if(strpos($init_options,':') === false)
		{
			$init = 'theme : "advanced", theme_advanced_toolbar_location : "top", theme_advanced_toolbar_align : "left"';
			$tab1a = 'theme_advanced_buttons1_add : "';
			$tab2a = 'theme_advanced_buttons2_add : "';
			$tab3a = 'theme_advanced_buttons3_add : "separator,fullscreen';
			$plugs = 'plugins : "paste,fullscreen,advimage,advlink';
			$eve = 'extended_valid_elements : "a[name|href|target|title|onclick], img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],font[*]';
			if($init_options)
			{
				foreach(explode(',',$init_options) as $plugin)
				{
					switch($plugin)
					{
						case 'TableOperations' :
							$plugs .= ',table';
							$tab3a .= ',separator,tablecontrols';
							break;
						case 'ContextMenu' :
							$plugs .= ',contextmenu';
							break;
						case 'ColorChooser' :
							$tab3a .= ',separator,forecolor,backcolor';
							break;
						case 'FontChooser' :
							$tab1a .= ',fontselect,fontsizeselect';
							$init .= ',theme_advanced_disable : "styleselect"';
							break;
						case 'FileManager' :
							$plugs .= ',filemanager';
							$tab3a .= ',separator,filemanager';
							break;
						case 'SearchReplace' :
							$plugs .= ',searchreplace';
							$tab1a .= ',separator,search,replace';
							break;
						case 'InsertDateTime' :
							$plugs .= ',insertdatetime';
							$tab2a .= ',separator,insertdate,inserttime';
							break;
						default:
							if(strpos($plugin,'=')!==false)
							{
								$init .= ','. str_replace('=',':',$plugin);
							}
					}
				}
			}
			if($base_href)
			{
				$init .= ',document_base_url : "'. $base_href. '", relative_urls : true';
			}

			$init_options = $init. ','. $tab1a. '",'. $tab2a. '",'. $tab3a. '",'. $plugs. '",'. $eve. '"';
		}
		
		// do again and again
		return '<script language="javascript" type="text/javascript">
			tinyMCE.init({
			 mode : "exact",
			 relative_urls : false,
 			 language: "'.$GLOBALS['egw_info']['user']['preferences']['common']['lang'].'",
			 plugin_insertdate_dateFormat : "'.str_replace(array('Y','m','M','d'),array('%Y','%m','%b','%d'),$GLOBALS['egw_info']['user']['preferences']['common']['dateformat']).' ",
			 plugin_insertdate_timeFormat : "'.($GLOBALS['egw_info']['user']['preferences']['common']['timeformat'] == 12 ? '%I:%M %p' : '%H:%M').' ",
			 elements : "'.$name.'",
			 '.$init_options.'
			});
			</script>

			<textarea id="'.$name.'" name="'.$name.'" style="'.$style.'">'.
			@htmlspecialchars($content, ENT_QUOTES, $this->charset) 
			.'</textarea>';
	}*/

	/**
	* this function is a wrapper for fckEditor to create some reuseable layouts
	*
	* @param string $_name name and id of the input-field
	* @param string $_content='' of the tinymce (will be run through htmlspecialchars !!!), default ''	
	* @param string $_mode display mode of the tinymce editor can be: simple, extended or advanced
	* @param array  $_options (toolbar_expanded true/false)
	* @param string $_height='400px'
	* @param string $_width='100%'
	* @param string $base_href='' if passed activates the browser for image at absolute path passed
	* @return string the necessary html for the textarea
	*/
	function fckEditor($_name, $_content='', $_mode, $_options=array('toolbar_expanded' =>'true'), $_height='400px', $_width='100%',$_base_href) {
		if (!$this->htmlarea_availible())
		{
			return $this->textarea($_name,$_content,'style="width: '.$_width.'; height: '.$_height.';"');
		}
		include_once(EGW_INCLUDE_ROOT."/phpgwapi/js/fckeditor/fckeditor.php");

		$oFCKeditor = new FCKeditor($_name) ;
		$oFCKeditor->BasePath	= $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/fckeditor/' ;
		$oFCKeditor->Value	= $_content;
		$oFCKeditor->Width	= $_width ;
		$oFCKeditor->Height	= $_height ;
		$oFCKeditor->Config['LinkBrowser'] = 'false';
		$oFCKeditor->Config['FlashBrowser'] = 'false';
		$oFCKeditor->Config['LinkUpload'] = 'false';
		
		// Activate the browser and config upload pathimage dir ($_base_href/images) if passed (absolute path)
		if ($_base_href)
		{
			// Only images for now
			$oFCKeditor->Config['ImageBrowserURL'] = $oFCKeditor->BasePath.'editor/filemanager/browser/default/browser.html?ServerPath='.$_base_href.'&Type=images&Connector=connectors/php/connector.php';
		}
		else
		{
			$oFCKeditor->Config['ImageBrowser'] = 'false';
		}
		// By default the editor start expanded
		if ($_options['toolbar_expanded'] == 'false')
		{
			$oFCKeditor->Config['ToolbarStartExpanded'] = $_options['toolbar_expanded'];
		}
		 
		switch($_mode) {
			case 'ascii':
				return "<textarea name=\"$_name\" style=\"width:".$_width."; height:".$_height."; border:0px;\">$_content</textarea>";
				break;
			case 'simple':
				$oFCKeditor->ToolbarSet = 'egw_simple';
				return $oFCKeditor->CreateHTML() ;
				break;
			case 'extended':
				$oFCKeditor->ToolbarSet = 'egw_extended';
				return $oFCKeditor->CreateHTML() ;
				break;
			case 'advanced':
				$oFCKeditor->ToolbarSet = 'egw_advanced';
				return $oFCKeditor->CreateHTML() ;
				break;						
		}

	}

	/**
	* this function is a wrapper for tinymce to create some reuseable layouts
	*
	* Please note: if you did not run init_tinymce already you this function need to be called before the call to phpgw_header() !!!
	*
	* @param string $_name name and id of the input-field
	* @param string $_mode display mode of the tinymce editor can be: simple, extended or advanced
	* @param string $_content='' of the tinymce (will be run through htmlspecialchars !!!), default ''
	* @param string $style='' initial css for the style attribute
	* @param string $base_href=''
	* @return string the necessary html for the textarea
	*/
	function fckEditorQuick($_name, $_mode, $_content='', $_height='400px', $_width='100%') {
		include_once(EGW_INCLUDE_ROOT."/phpgwapi/js/fckeditor/fckeditor.php");

		$oFCKeditor		= new FCKeditor($_name) ;
		$oFCKeditor->BasePath	= $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/fckeditor/' ;
		$oFCKeditor->Value	= $_content;
		$oFCKeditor->Width	= $_width ;
		$oFCKeditor->Height	= $_height ;
		switch($_mode) {
			case 'ascii':
				return "<textarea name=\"$_name\" style=\"width:100%; height:400px; border:0px;\">$_content</textarea>";
				break;
			case 'simple':
				$oFCKeditor->ToolbarSet = 'egw_simple';
				return $oFCKeditor->CreateHTML() ;
				break;
		}

	}
	
	/**
	* this function is a wrapper for tinymce to create some reuseable layouts
	*
	* Please note: if you did not run init_tinymce already you this function need to be called before the call to phpgw_header() !!!
	*
	* @param string $_name name and id of the input-field
	* @param string $_mode display mode of the tinymce editor can be: simple, extended or advanced
	* @param string $_content='' of the tinymce (will be run through htmlspecialchars !!!), default ''
	* @param string $style='' initial css for the style attribute
	* @param string $base_href=''
	* @return string the necessary html for the textarea
	
	function tinymceQuick($_name, $_mode, $_content='', $_style='', $base_href='') {
		switch($_mode) {
			case 'ascii':
				$init_options='theme : "advanced",
					theme_advanced_toolbar_location : "top", 
					theme_advanced_toolbar_align : "left",
					theme_advanced_buttons1 : "",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : "",
					valid_elements : "br"';

				return $this->tinymce($_name, $_content, $_style, $init_options, $_base_href);
				break;
			
			case 'simple':
				$init_options='theme : "advanced",
					theme_advanced_toolbar_location : "top", 
					theme_advanced_toolbar_align : "left",
					theme_advanced_buttons1 : "bold,italic,underline,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,bullist,numlist,outdent,indent,undo,redo,separator,forecolor",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : "",
					valid_elements : "strong/b[class],em/i[class],strike[class],u[class],p[dir|class|align],ol,ul,li,br,\
					sub,sup,blockquote[dir|style],pre[class|align],address[class|align],hr,font[color]"';

				return $this->tinymce($_name, $_content, $_style, $init_options, $_base_href);
				break;
			
			case 'extended':
				$init_options='';
				return $this->tinymce($_name, $_content,$_style, $init_options='',$_base_href);
				break;
			
			case 'advanced':
				$init_options='';
				return $this->tinymce($_name, $_content,$_style, $init_options='',$_base_href);
				break;
		}
	}*/

	/**
	 * represents html's input tag
	 *
	 * @param string $name name
	 * @param string $value default value of the field
	 * @param string $type type, default ''=not specified = text
	 * @param string $options attributes for the tag, default ''=none
	 */
	function input($name,$value='',$type='',$options='' )
	{
		if ($type)
		{
			$type = 'type="'.$type.'"';
		}
		return "<input $type name=\"$name\" value=\"".$this->htmlspecialchars($value)."\" $options />\n";
	}

	/**
	 * represents html's button (input type submit or image)
	 *
	 * @param string $name name
	 * @param string $label label of the button
	 * @param string $onClick javascript to call, when button is clicked
	 * @param boolean $no_lang NOT running the label through lang(), default false=use lang()
	 * @param string $options attributes for the tag, default ''=none
	 * @param string $image to show instead of the label, default ''=none
	 * @param string $app app to search the image in
	 * @return string html
	 */
	function submit_button($name,$label,$onClick='',$no_lang=false,$options='',$image='',$app='phpgwapi')
	{
		// workaround for idots and IE button problem (wrong cursor-image)
		if ($this->user_agent == 'msie')
		{
			$options .= ' style="cursor: pointer;"';
		}
		if ($image != '')
		{
			$image = str_replace(array('.gif','.GIF','.png','.PNG'),'',$image);

			if (!($path = $GLOBALS['egw']->common->image($app,$image)))
			{
				$path = $image;		// name may already contain absolut path
			}
			$image = ' src="'.$path.'"';
		}
		if (!$no_lang)
		{
			$label = lang($label);
		}
		if (($accesskey = @strstr($label,'&')) && $accesskey[1] != ' ' &&
			(($pos = strpos($accesskey,';')) === false || $pos > 5))
		{
			$label_u = str_replace('&'.$accesskey[1],'<u>'.$accesskey[1].'</u>',$label);
			$label = str_replace('&','',$label);
			$options = 'accesskey="'.$accesskey[1].'" '.$options;
		}
		else
		{
			$accesskey = '';
			$label_u = $label;
		}
		if ($onClick) $options .= ' onclick="'.str_replace('"','\\"',$onClick).'"';

		// <button> is not working in all cases if ($this->user_agent == 'mozilla' && $this->ua_version < 5 || $image)
		{
			return $this->input($name,$label,$image != '' ? 'image' : 'submit',$options.$image);
		}
		return '<button type="submit" name="'.$name.'" value="'.$label.'" '.$options.' />'.
			($image != '' ? /*$this->image($app,$image,$label,$options)*/"<img$image $this->prefered_img_title=\"$label\"> " : '').
			($image == '' || $accesskey ? $label_u : '').'</button>';
	}

	/**
	 * creates an absolut link + the query / get-variables
	 *
	 * Example link('/index.php?menuaction=infolog.uiinfolog.get_list',array('info_id' => 123))
	 *	gives 'http://domain/phpgw-path/index.php?menuaction=infolog.uiinfolog.get_list&info_id=123'
	 *
	 * @param string $url phpgw-relative link, may include query / get-vars
	 * @param array/string $vars query or array ('name' => 'value', ...) with query
	 * @return string absolut link already run through $phpgw->link
	 */
	function link($url,$vars='')
	{
		//echo "<p>html::link(url='$url',vars='"; print_r($vars); echo "')</p>\n";
		if (!is_array($vars))
		{
			parse_str($vars,$vars);
		}
		list($url,$v) = explode('?',$url);	// url may contain additional vars
		if ($v)
		{
			parse_str($v,$v);
			$vars += $v;
		}
		return $GLOBALS['egw']->link($url,$vars);
	}

	/**
	 * represents html checkbox
	 *
	 * @param string $name name
	 * @param boolean $checked box checked on display
	 * @param string $value value the var should be set to, default 'True'
	 * @param string $options attributes for the tag, default ''=none
	 * @return string html
	 */
	function checkbox($name,$checked=false,$value='True',$options='')
	{
		return '<input type="checkbox" name="'.$name.'" value="'.$this->htmlspecialchars($value).'"' .($checked ? ' checked="1"' : '') . "$options />\n";
	}

	/**
	 * represents a html form
	 *
	 * @param string $content of the form, if '' only the opening tag gets returned
	 * @param array $hidden_vars array with name-value pairs for hidden input fields
	 * @param string $url eGW relative URL, will be run through the link function, if empty the current url is used
	 * @param string/array $url_vars parameters for the URL, send to link function too
	 * @param string $name name of the form, defaul ''=none
	 * @param string $options attributes for the tag, default ''=none
	 * @param string $method method of the form, default 'POST'
	 * @return string html
	 */
	function form($content,$hidden_vars,$url,$url_vars='',$name='',$options='',$method='POST')
	{
		$url = $url ? $this->link($url,$url_vars) : $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$html = "<form method=\"$method\" ".($name != '' ? "name=\"$name\" " : '')."action=\"$url\" $options>\n";
		$html .= $this->input_hidden($hidden_vars);

		if ($content)
		{
			$html .= $content;
			$html .= "</form>\n";
		}
		return $html;
	}

	/**
	 * represents a html form with one button
	 *
	 * @param string $name name of the button
	 * @param string $label label of the button
	 * @param array $hidden_vars array with name-value pairs for hidden input fields
	 * @param string $url eGW relative URL, will be run through the link function
	 * @param string/array $url_vars parameters for the URL, send to link function too
	 * @param string $options attributes for the tag, default ''=none
	 * @param string $form_name name of the form, defaul ''=none
	 * @param string $method method of the form, default 'POST'
	 * @return string html
	 */
	function form_1button($name,$label,$hidden_vars,$url,$url_vars='',$form_name='',$method='POST')
	{
		return $this->form($this->submit_button($name,$label),$hidden_vars,$url,$url_vars,$form_name,'',$method);
	}

	/**
	 * creates table from array of rows
	 *
	 * abstracts the html stuff for the table creation
	 * Example: $rows = array (
	 *	'1'  => array(
	 *		1 => 'cell1', '.1' => 'colspan=3',
	 *		2 => 'cell2',
	 *		3 => 'cell3', '.3' => 'width="10%"'
	 *	),'.1' => 'BGCOLOR="#0000FF"' );
	 *	table($rows,'width="100%"') = '<table width="100%"><tr><td colspan=3>cell1</td><td>cell2</td><td width="10%">cell3</td></tr></table>'
	 *
	 * @param array $rows with rows, each row is an array of the cols
	 * @param string $options options for the table-tag
	 * @param boolean $no_table_tr dont return the table- and outmost tr-tabs, default false=return table+tr
	 * @return string with html-code of the table
	 */
	function table($rows,$options = '',$no_table_tr=False)
	{
		$html = $no_table_tr ? '' : "<table $options>\n";

		foreach($rows as $key => $row)
		{
			if (!is_array($row))
			{
				continue;					// parameter
			}
			$html .= $no_table_tr && $key == 1 ? '' : "\t<tr ".$rows['.'.$key].">\n";

			foreach($row as $key => $cell)
			{
				if ($key[0] == '.')
				{
					continue;				// parameter
				}
				$table_pos = strpos($cell,'<table');
				$td_pos = strpos($cell,'<td');
				if ($td_pos !== False && ($table_pos === False || $td_pos < $table_pos))
				{
					$html .= $cell;
				}
				else
				{
					$html .= "\t\t<td ".$row['.'.$key].">$cell</td>\n";
				}
			}
			$html .= "\t</tr>\n";
		}
		if (!is_array($rows))
		{
			echo "<p>".function_backtrace()."</p>\n";
		}
		$html .= "</table>\n";

		if ($no_table_tr)
		{
			$html = substr($html,0,-16);
		}
		return $html;
	}

	/**
	 * changes a selectbox to submit the form if it gets changed, to be used with the sbox-class
	 *
	 * @param string $sbox html with the select-box
	 * @param boolean $no_script if true generate a submit-button if javascript is off
	 * @return string html
	 */
	function sbox_submit( $sbox,$no_script=false )
	{
		$html = str_replace('<select','<select onchange="this.form.submit()" ',$sbox);
		if ($no_script)
		{
			$html .= '<noscript>'.$this->submit_button('send','>').'</noscript>';
		}
		return $html;
	}

	/**
	 * html-widget showing progessbar with a view div's (html4 only, textual percentage otherwise)
	 *
	 * @param mixed $percent percent-value, gets casted to int
	 * @param string $title title for the progressbar, default ''=the percentage itself
	 * @param string $options attributes for the outmost div (may include onclick="...")
	 * @param string $width width, default 30px
	 * @param string $color color, default '#D00000' (dark red)
	 * @param string $height height, default 5px
	 * @return string html
	 */
	function progressbar( $percent,$title='',$options='',$width='',$color='',$height='' )
	{
		$percent = (int) $percent;
		if (!$width) $width = '30px';
		if (!$height)$height= '5px';
		if (!$color) $color = '#D00000';
		$title = $title ? $this->htmlspecialchars($title) : $percent.'%';

		if ($this->netscape4)
		{
			return $title;
		}
		return '<div class="onlyPrint">'.$title.'</div><div class="noPrint" title="'.$title.'" '.$options.
			' style="height: '.$height.'; width: '.$width.'; border: 1px solid black; padding: 1px; text-align: left;'.
			(@stristr($options,'onclick="') ? ' cursor: pointer;' : '').'">'."\n\t".
			'<div style="height: '.$height.'; width: '.$percent.'%; background: '.$color.';"></div>'."\n</div>\n";
	}

	/**
	 * representates a html img tag, output a picture
	 *
	 * If the name ends with a '%' and the rest is numeric, a progressionbar is shown instead of an image.
	 * The vfs:/ pseudo protocoll allows to access images in the vfs, eg. vfs:/home/ralf/me.png
	 * Instead of a name you specify an array with get-vars, it is passed to eGW's link function.
	 * This way session-information gets passed, eg. $name=array('menuaction'=>'myapp.class.image','id'=>123).
	 *
	 * @param string $app app-name to search the image
	 * @param string/array $name image-name or URL (incl. vfs:/) or array with get-vars
	 * @param string $title tooltip, default '' = none
	 * @param string $options further options for the tag, default '' = none
	 * @return string the html
	 */
	function image( $app,$name,$title='',$options='' )
	{
		if (substr($name,0,5) == 'vfs:/')	// vfs pseudo protocoll
		{
			$parts = explode('/',substr($name,4));
			$file = array_pop($parts);
			$path = implode('/',$parts);
			$name = array(
				'menuaction' => 'filemanager.uifilemanager.view',
				'path'       => rawurlencode(base64_encode($path)),
				'file'       => rawurlencode(base64_encode($file)),
			);
		}
		if (is_array($name))	// menuaction and other get-vars
		{
			$name = $GLOBALS['egw']->link('/index.php',$name);
		}
		if ($name[0] == '/' || substr($name,0,7) == 'http://' || substr($name,0,8) == 'https://')
		{
			$url = $name;
		}
		else	// no URL, so try searching the image
		{
			$name = str_replace(array('.gif','.GIF','.png','.PNG'),'',$name);

			if (!($url = $GLOBALS['egw']->common->image($app,$name)))
			{
				$url = $name;		// name may already contain absolut path
			}
			if($GLOBALS['egw_info']['server']['webserver_url'])
			{
				list(,$path) = explode($GLOBALS['egw_info']['server']['webserver_url'],$url);

				if (!is_null($path)) $path = EGW_SERVER_ROOT.$path;
			}
			else
			{
				$path = EGW_SERVER_ROOT.$url;
			}
			
			if (is_null($path) || !@is_readable($path))
			{
				// if the image-name is a percentage, use a progressbar
				if (substr($name,-1) == '%' && is_numeric($percent = substr($name,0,-1)))
				{
					return $this->progressbar($percent,$title);
				}
				return $title;
			}
		}
		if ($title)
		{
			$options .= " $this->prefered_img_title=\"".$this->htmlspecialchars($title).'"';
		}
		if ($this->user_agent == 'msie' && $this->ua_version >= 5.5 && substr($url,-4) == '.png')
		{
			$extra_styles = "display: inline-block; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$url',sizingMethod='image'); width: 1px; height: 1px;";
			if (strstr($options,'style="'))
			{
				$options = str_replace('style="','style="'.$extra_styles);
			}
			else
			{
				$options .= ' style="'.$extra_styles.'"';
			}
			return "<span $options></span>";
		}
		return "<img src=\"$url\" $options />";
	}

	/**
	 * representates a html link
	 *
	 * @param string $content of the link, if '' only the opening tag gets returned
	 * @param string $url eGW relative URL, will be run through the link function
	 * @param string/array $vars parameters for the URL, send to link function too
	 * @param string $options attributes for the tag, default ''=none
	 * @return string the html
	 */
	function a_href( $content,$url,$vars='',$options='')
	{
		if (is_array($url))
		{
			$vars = $url;
			$url = '/index.php';
		}
		elseif (strpos($url,'/')===false && 
			count(explode('.',$url)) >= 3 && 
			!(strpos($url,'mailto:')!==false || 
			strpos($url,'://')!==false || 
			strpos($url,'javascript:')!==false))
		{
			$url = "/index.php?menuaction=$url";
		}
		if ($url{0} == '/')		// link relative to eGW
		{
			$url = $this->link($url,$vars);
		}
		//echo "<p>html::a_href('".htmlspecialchars($content)."','$url',".print_r($vars,True).") = ".$this->link($url,$vars)."</p>";
		return '<a href="'.$url.'" '.$options.'>'.$content.'</a>';
	}

	/**
	 * representates a b tab (bold)
	 *
	 * @param string $content of the link, if '' only the opening tag gets returned
	 * @return string the html
	 */
	function bold($content)
	{
		return '<b>'.$content.'</b>';
	}

	/**
	 * representates a i tab (bold)
	 *
	 * @param string $content of the link, if '' only the opening tag gets returned
	 * @return string the html
	 */
	function italic($content)
	{
		return '<i>'.$content.'</i>';
	}

	/**
	 * representates a hr tag (horizontal rule)
	 *
	 * @param string $width default ''=none given
	 * @param string $options attributes for the tag, default ''=none
	 * @return string the html
	 */
	function hr($width='',$options='')
	{
		if ($width) $options .= " width=\"$width\"";

		return "<hr $options />\n";
	}

	/**
	 * formats option-string for most of the above functions
	 *
	 * Example: formatOptions('100%,,1','width,height,border') = ' width="100%" border="1"'
	 *
	 * @param mixed $options String (or Array) with option-values eg. '100%,,1'
	 * @param mixed $names String (or Array) with the option-names eg. 'WIDTH,HEIGHT,BORDER'
	 * @return string with options/attributes
	 */
	function formatOptions($options,$names)
	{
		if (!is_array($options)) $options = explode(',',$options);
		if (!is_array($names))   $names   = explode(',',$names);

		foreach($options as $n => $val)
		{
			if ($val != '' && $names[$n] != '')
			{
				$html .= ' '.strtolower($names[$n]).'="'.$val.'"';
			}
		}
		return $html;
	}

	/**
	 * returns simple stylesheet (incl. <STYLE> tags) for nextmatch row-colors
	 *
	 * @deprecated  included now always by the framework
	 * @return string classes 'th' = nextmatch header, 'row_on'+'row_off' = alternating rows
	 */
	function themeStyles()
	{
		return $this->style($this->theme2css());
	}

	/**
	 * returns simple stylesheet for nextmatch row-colors
	 *
	 * @deprecated included now always by the framework
	 * @return string classes 'th' = nextmatch header, 'row_on'+'row_off' = alternating rows
	 */
	function theme2css()
	{
		return ".th { background: ".$GLOBALS['egw_info']['theme']['th_bg']."; }\n".
			".row_on,.th_bright { background: ".$GLOBALS['egw_info']['theme']['row_on']."; }\n".
			".row_off { background: ".$GLOBALS['egw_info']['theme']['row_off']."; }\n";
	}

	/**
	 * html style tag (incl. type)
	 *
	 * @param string $styles css-style definitions
	 * @return string html
	 */
	function style($styles)
	{
		return $styles ? "<style type=\"text/css\">\n<!--\n$styles\n-->\n</style>" : '';
	}

	/**
	 * html label tag
	 *
	 * @param string $content the label
	 * @param string $id for the for attribute, default ''=none
	 * @param string $accesskey accesskey, default ''=none
	 * @param string $options attributes for the tag, default ''=none
	 * @return string the html
	 */
	function label($content,$id='',$accesskey='',$options='')
	{
		if ($id != '')
		{
			$id = " for=\"$id\"";
		}
		if ($accesskey != '')
		{
			$accesskey = " accesskey=\"$accesskey\"";
		}
		return "<label$id$accesskey $options>$content</label>";
	}

	/**
	 * html fieldset, eg. groups a group of radiobuttons
	 *
	 * @param string $content the content
	 * @param string $legend legend / label of the fieldset, default ''=none
	 * @param string $options attributes for the tag, default ''=none
	 * @return string the html
	 */
	function fieldset($content,$legend='',$options='')
	{
		$html = "<fieldset $options>".($legend ? '<legend>'.$this->htmlspecialchars($legend).'</legend>' : '')."\n";

		if ($content)
		{
			$html .= $content;
			$html .= "\n</fieldset>\n";
		}
		return $html;
	}
	
	/**
	* tree widget using dhtmlXtree
	*
	* Code inspired by Lars's Felamimail uiwidgets::createFolderTree()
	*
	* @author Lars Kneschke <lars-AT-kneschke.de> original code in felamimail
	* @param array $_folders array of folders: pairs path => node (string label or array with keys: label, (optional) image, (optional) title, (optional) checked)
	* @param string $_selected path of selected folder
	* @param mixed $_topFolder=false node of topFolder or false for none
	* @param string $_onNodeSelect='alert' js function to call if node gets selected
	* @param string $_divId='foldertree' id of the div
	* @param string $_divClass='' css class of the div
	* @param string $_leafImage='' default image of a leaf-node, ''=default of foldertree, set it eg. 'folderClosed.gif' to show leafs as folders
	* @param boolean/string $_onCheckHandler=false string with handler-name to display a checkbox for each folder, or false (default)
	* @param string $delimiter='/' path-delimiter, default /
	* @param mixed $folderImageDir=null string path to the tree menu images, null uses default path
	*
	* @return string the html code, to be added into the template
	*/
	function tree($_folders,$_selected,$_topFolder=false,$_onNodeSelect="null",$_divId='foldertree',$_divClass='',$_leafImage='',$_onCheckHandler=false,$delimiter='/',$folderImageDir=null)
	{
	   if(is_null($folderImageDir))
	   {
		  $folderImageDir = $GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/templates/default/images/';
	   }

		$html = $this->div("\n",'id="'.$_divId.'"',$_divClass);

		static $tree_initialised=false;
		if (!$tree_initialised)
		{
			$html .= '<link rel="STYLESHEET" type="text/css" href="'.$GLOBALS['egw_info']['server']['webserver_url'].'/phpgwapi/js/dhtmlxtree/css/dhtmlXTree.css">'."\n";
			$html .= "<script type='text/javascript' src='{$GLOBALS['egw_info']['server']['webserver_url']}/phpgwapi/js/dhtmlxtree/js/dhtmlXCommon.js'></script>\n";
			$html .= "<script type='text/javascript' src='{$GLOBALS['egw_info']['server']['webserver_url']}/phpgwapi/js/dhtmlxtree/js/dhtmlXTree.js'></script>\n";
			$tree_initialised = true;
		}
		$html .= "<script type='text/javascript'>\n";
		$html .= "tree=new dhtmlXTreeObject('$_divId','100%','100%',0);\n";
		$html .= "tree.setImagePath('$folderImageDir/dhtmlxtree/');\n";

		if($_onCheckHandler)
		{
			$html .= "tree.enableCheckBoxes(1);\n";
			$html .= "tree.setOnCheckHandler('$_onCheckHandler');\n";
		}
		
		$top = 0;
		if ($_topFolder)
		{
			$top = '--topfolder--';
			$topImage = '';
			if (is_array($_topFolder))
			{
				$label = $_topFolder['label'];
				if (isset($_topFolder['image']))
				{
					$topImage = $_topFolder['image'];
				}
			}
			else
			{
				$label = $_topFolder;
			}	
			$html .= "\ntree.insertNewItem(0,'$top','".addslashes($label)."',$_onNodeSelect,'$topImage','$topImage','$topImage','CHILD,TOP');\n";

			if (is_array($_topFolder) && isset($_topFolder['title']))
			{
				$html .= "tree.setItemText('$top','".addslashes($label)."','".addslashes($_topFolder['title'])."');\n";
			}
		}
		// evtl. remove leading delimiter
		if ($_selected{0} == $delimiter) $_selected = substr($_selected,1);
		foreach($_folders as $path => $data)
		{
			if (!is_array($data))
			{
				$data = array('label' => $data);
			}
			$image1 = $image2 = $image3 = '0';
			if (isset($data['image']))
			{
				$image1 = $image2 = $image3 = "'".$data['image']."'";
			}
			// evtl. remove leading delimiter
			if ($path{0} == $delimiter) $path = substr($path,1);
			$folderParts = explode($delimiter,$path);
			
			//get rightmost folderpart
			$label = array_pop($folderParts);
			if (isset($data['label'])) $label = $data['label'];
			
			// the rest of the array is the name of the parent
			$parentName = implode((array)$folderParts,$delimiter);
			if(empty($parentName)) $parentName = $top;
			
			$entryOptions = 'CHILD,CHECKED';
			// highlight currently item
			if ($_selected === $path)
			{
				$entryOptions .= ',SELECT';
			}
			$html .= "tree.insertNewItem('".addslashes($parentName)."','".addslashes($path)."','".addslashes($label).
				"',$_onNodeSelect,$image1,$image2,$image3,'$entryOptions');\n";
			if (isset($data['title']))
			{
				$html .= "tree.setItemText('".addslashes($path)."','".addslashes($label)."','".addslashes($data['title'])."');\n";
			}
			if($_displayCheckBox)
			{
				$html .= "tree.setCheck('".addslashes($path)."','".(int)$data['checked']."');";
			}
		}
		$html .= "tree.closeAllItems(0);\n";
		$html .= "tree.openItem('".($_selected ? addslashes($_selected) : $top)."');\n";
		$html .= "</script>\n";
		
		return $html;
	}
}
