<?php
	/**************************************************************************\
	* phpGroupWare - EditableTemplates - HTML User Interface                   *
	* http://www.phpgroupware.org                                              *
	* Written by Ralf Becker <RalfBecker@outdoor-training.de>                  *
	* --------------------------------------------                             *
	*  This program is free software; you can redistribute it and/or modify it *
	*  under the terms of the GNU General Public License as published by the   *
	*  Free Software Foundation; either version 2 of the License, or (at your  *
	*  option) any later version.                                              *
	\**************************************************************************/

	/* $Id$ */

	include(PHPGW_API_INC . '/../../etemplate/inc/class.boetemplate.inc.php');

	/*!
	@class etemplate
	@abstract creates dialogs / HTML-forms from eTemplate descriptions
	@discussion etemplate or uietemplate extends boetemplate, all vars and public functions are inherited
	@example $tmpl = CreateObject('etemplate.etemplate','app.template.name');
	@example $tmpl->exec('app.class.callback',$content_to_show);
	@example This creates a form from the eTemplate 'app.template.name' and takes care that
	@example the method / public function 'callback' in (bo)class 'class' of 'app' gets called
	@example if the user submitts the form. Vor the complete param's see the description of exec.
	@param $debug enables debug messages: 0=no, 1=calls to show and process_show, 2=content of process_show
	@param                                3=calls to show_cell OR template- or cell-type name
	@param $html,$sbox instances of html and sbox2 class used to generate the html
	*/
	class etemplate extends boetemplate
	{
		var $debug;//='etemplate.editor.edit'; // 1=calls to show and process_show, 2=content after process_show,
						// 3=calls to show_cell and process_show_cell, or template-name or cell-type
		var $html,$sbox;	// instance of html / sbox2-class

		/*!
		@function etemplate
		@abstract constructor of etemplate class, reads an eTemplate if $name is given
		@param as soetemplate.read
		*/
		function etemplate($name='',$template='default',$lang='default',$group=0,$version='',$rows=2,$cols=2)
		{
			$this->public_functions += array(
				'exec'			=> True,
				'process_exec'	=> True,
				'show'			=> True,
				'process_show'	=> True,
			);
			$this->boetemplate();
			$this->html = CreateObject('etemplate.html');	// should  be in the api (older version in infolog)
			$this->sbox = CreateObject('etemplate.sbox2');	// older version is in the api

			if (!$this->read($name,$template,$lang,$group,$version))
			{
				$this->init($name,$template,$lang,$group,$version,$rows,$cols);
				return False;
			}
			return True;
		}

		/*!
		@function exec
		@abstract Generats a Dialog from an eTemplate - abstract the UI-layer
		@discussion This is the only function an application should use, all other are INTERNAL and
		@discussion do NOT abstract the UI-layer, because they return HTML.
		@discussion Generates a webpage with a form from the template and puts process_exec in the
		@discussion form as submit-url to call process_show for the template before it
		@discussion ExecuteMethod's the given $methode of the caller.
		@param $methode Methode (e.g. 'etemplate.editor.edit') to be called if form is submitted
		@param $content Array with content to fill the input-fields of template, eg. the text-field
		@param          with name 'name' gets its content from $content['name']
		@param $sel_options Array or arrays with the options for each select-field, keys are the
		@param              field-names, eg. array('name' => array(1 => 'one',2 => 'two')) set the
		@param              options for field 'name'. ($content['options-name'] is possible too !!!)
		@param $readonlys Array with field-names as keys for fields with should be readonly
		@param            (eg. to implement ACL grants on field-level or to remove buttons not applicable)
		@param $preserv Array with vars which should be transported to the $method-call (eg. an id) array('id' => $id) sets $HTTP_POST_VARS['id'] for the $method-call
		@param $cname Basename for the submitted content in $HTTP_POST_VARS (default = 'cont')
		@returns nothing
		*/
		function exec($method,$content,$sel_options='',$readonlys='',$preserv='',$cname='cont')
		{
			if (!$sel_options)
			{
				$sel_options = array();
			}
			if (!$readonlys)
			{
				$readonlys = array();
			}
			if (!$preserv)
			{
				$preserv = array();
			}
			$GLOBALS['phpgw']->common->phpgw_header();
			echo parse_navbar();

			echo $this->html->nextMatchStyles($this->style)."\n\n". // so they get included once
				$this->html->form($this->show($content,$sel_options,$readonlys,$cname),array(
					'etemplate_exec[name]' => $this->name,
					'etemplate_exec[template]' => $this->template,
					'etemplate_exec[lang]' => $this->lang,
					'etemplate_exec[group]' => $this->group,
					'etemplate_exec[readonlys]' => $readonlys,
					'etemplate_exec[cname]' => $cname,
					'etemplate_exec[method]' => $method
				)+$preserv,'/index.php?menuaction=etemplate.etemplate.process_exec');
		}

		/*!
		@function process_exec
		@abstract Makes the necessary adjustments to HTTP_POST_VARS before it calls the app's method
		@discussion This function is only to submit forms to, create with exec.
		@discussion All eTemplates / forms executed with exec are submited to this function
		@discussion (via the global index.php and menuaction). It then calls process_show
		@discussion for the eTemplate (to adjust the content of the HTTP_POST_VARS) and
		@discussion ExecMethod's the given callback from the app.
		*/
		function process_exec()
		{
			$exec = $GLOBALS['HTTP_POST_VARS']['etemplate_exec'];
			//echo "<p>uietemplate.process_exec('${exec['name']}'): exec = "; _debug_array($exec);

			$this->read($exec);
			$readonlys = unserialize(stripslashes($exec['readonlys']));
			//echo "<p>uietemplate.process_exec: process_show(cname=${exec['cname']}): readonlys ="; _debug_array($readonlys);
			$this->process_show($GLOBALS['HTTP_POST_VARS'][$exec['cname']],$readonlys);

			// set application name so that lang, etc. works
			list($GLOBALS['phpgw_info']['flags']['currentapp']) = explode('.',$exec['method']);

			//echo "<p>uietemplate.process_exec: ExecMethod('${exec['method']}')</p>\n";
			ExecMethod($exec['method']);
		}

		/*!
		@function isset_array($idx,$arr)
		@abstract checks if idx, which may contain ONE subindex is set in array
		*/
		function isset_array($idx,$arr)
		{
			if (ereg('^([^[]*)\\[(.*)\\]$',$idx,$regs))
			{
				return $regs[2] && isset($arr[$regs[1]][$regs[2]]);
			}
			return isset($arr[$idx]);
		}

		/*!
		@function show
		@abstract creates HTML from an eTemplate
		@discussion This is done by calling show_cell for each cell in the form. show_cell itself
		@discussion calls show recursivly for each included eTemplate.
		@discussion You can use it in the UI-layer of an app, just make shure to call process_show !!!
		@discussion This is intended as internal function and should NOT be called by new app's direct,
		@discussion as it deals with HTML and is so UI-dependent, use exec instead.
		@param $content array with content for the cells, keys are the names given in the cells/form elements
		@param $sel_options array with options for the selectboxes, keys are the name of the selectbox
		@param $readonlys array with names of cells/form-elements to be not allowed to change
		@param            This is to facilitate complex ACL's which denies access on field-level !!!
		@param $cname basename of names for form-elements, means index in $HTTP_POST_VARS
		@param        eg. $cname='cont', element-name = 'name' returned content in $HTTP_POST_VARS['cont']['name']
		@param $show_xxx row,col name/index for name expansion
		@returns the generated HTML
		*/
		function show($content,$sel_options='',$readonlys='',$cname='cont',
						$show_c=0,$show_row=0)
		{
			if (!$sel_options)
			{
				$sel_options = array();
			}
			if (!$readonlys)
			{
				$readonlys = array();
			}
			if ($this->debug >= 1 || $this->debug == $this->name && $this->name)
			{
				echo "<p>etemplate.show($this->name): $cname =\n"; _debug_array($content);
			}
			if (!is_array($content))
			{
				$content = array();	// happens if incl. template has no content
			}
			$content += array(	// for var-expansion in names in show_cell
				'.c' => $show_c,
				'.col' => $this->num2chrs($show_c-1),
				'.row' => $show_row
			);
         			reset($this->data);
			if (isset($this->data[0]))
			{
				list($nul,$width) = each($this->data);
			}
			else
			{
				$width = array();
			}
			for ($r = 0; $row = 1+$r /*list($row,$cols) = each($this->data)*/; ++$r)
			{
				$old_cols = $cols; $old_class = $class; $old_height = $height;
				if (!(list($nul,$cols) = each($this->data)))	// no further row
				{
					$cols = $old_cols; $class = $old_class; $height = $old_height;
					list($nul,$cell) = each($cols); reset($cols);
					if (!($this->autorepeat_idx($cols['A'],0,$r,$idx,$idx_cname) && $idx_cname) &&
						!($this->autorepeat_idx($cols['B'],1,$r,$idx,$idx_cname) && $idx_cname) ||
						!$this->isset_array($idx,$content))
					{
						break;                     	// no auto-row-repeat
					}
				}
				else
				{
					$height = $this->data[0]["h$row"];
					$class = $this->data[0]["c$row"];
				}
				$row_data = array();
				for ($c = 0; True /*list($col,$cell) = each($cols)*/; ++$c)
				{
					$old_cell = $cell;
					if (!(list($nul,$cell) = each($cols)))		// no further cols
					{
						$cell = $old_cell;
						if (!$this->autorepeat_idx($cell,$c,$r,$idx,$idx_cname,True) ||
							!$this->isset_array($idx,$content))
						{
							break;	// no auto-col-repeat
						}
					}
					$col = $this->num2chrs($c);
					$row_data[$col] = $this->show_cell($cell,$content,$sel_options,$readonlys,$cname,
						$c,$r,$span);
					if ($row_data[$col] == '' && $this->rows == 1)
					{
						unset($row_data[$col]);	// omit empty/disabled cells if only one row
						continue;
					}
					$colspan = $span == 'all' ? $this->cols-$c : 0+$span;
					if ($colspan > 1)
					{
						$row_data[".$col"] .= " COLSPAN=$colspan";
						for ($i = 1; $i < $colspan; ++$i,++$c)
						{
							each($cols);	// skip next cell(s)
						}
					}
					elseif ($width[$col])	// width only once for a non colspan cell
					{
						$row_data[".$col"] .= ' WIDTH='.$width[$col];
						$width[$col] = 0;
					}
					$row_data[".$col"] .= $this->html->formatOptions($cell['align'],'ALIGN');
					$row_data[".$col"] .= $this->html->formatOptions($cell['span'],',CLASS');
				}
				$rows[$row] = $row_data;

				$rows[".$row"] .= $this->html->formatOptions($height,'HEIGHT');
				list($cl) = explode(',',$class);
				if ($cl == 'nmr')
				{
					$cl .= $nmr_alternate++ & 1; // alternate color
				}
				$rows[".$row"] .= $this->html->formatOptions($cl,'CLASS');
				$rows[".$row"] .= $this->html->formatOptions($class,',VALIGN');
			}
			if (!$GLOBALS['phpgw_info']['etemplate']['styles_included'][$this->name])
			{
				$style = $this->html->style($this->style);
				$GLOBALS['phpgw_info']['etemplate']['styles_included'][$this->name] = True;
			}
			return "\n\n<!-- BEGIN $this->name -->\n$style\n".
				$this->html->table($rows,$this->html->formatOptions($this->size,'WIDTH,HEIGHT,BORDER,CLASS')).
				"<!-- END $this->name -->\n\n";
		}

		/*!
		@function show_cell
		@abstract generates HTML for 1 input-field / cell
		@discussion calls show to generate included eTemplates. Again only an INTERMAL function.
		@param $cell array with data of the cell: name, type, ...
		@param for rest see show
		@returns the generated HTML
		*/
		function show_cell($cell,$content,$sel_options,$readonlys,$cname,$show_c,$show_row,&$span)
		{
			if ($this->debug >= 3 || $this->debug == $cell['type'])
			{
				echo "<p>etemplate.show_cell($this->name,name='${cell['name']}',type='${cell['type']}',cname='$cname')</p>\n";
			}
			list($span) = explode(',',$cell['span']);	// evtl. overriten later for type template

			$name = $this->expand_name($cell['name'],$show_c,$show_row,$content['.c'],$content['.row'],$content);
			$value = $content[$name];

			$org_name = $name;
			if ($cname == '')	// building the form-field-name depending on prefix $cname and possibl. Array-subscript in name
			{
				$form_name = $name;
			}
			elseif (ereg('^([^[]*)\\[(.*)\\]$',$name,$regs))	// name contains array-index
			{
				$form_name = $cname.'['.$regs[1].']['.$regs[2].']';
				$value = $content[$regs[1]][$regs[2]];
				$org_name = $regs[2];
			}
			else
			{
				$form_name = $cname.'['.$name.']';
			}
			if ($readonly = $cell['readonly'] || $readonlys[$name] || $readonlys['__ALL__'])
			{
				$options .= ' READONLY';
			}
			if ($cell['disabled'] || $cell['type'] == 'button' && $readonly)
			{
				if ($this->rows == 1) {
					return '';	// if only one row omit cell
				}
				$cell = $this->empty_cell(); // show nothing
				$value = '';
			}
			if ($cell['help'])
			{
				$options .= " onFocus=\"self.status='".addslashes(lang($cell['help']))."'; return true;\"";
				$options .= " onBlur=\"self.status=''; return true;\"";
				if ($cell['type'] == 'button')	// for button additionally when mouse over button
				{
					$options .= " onMouseOver=\"self.status='".addslashes(lang($cell['help']))."'; return true;\"";
					$options .= " onMouseOut=\"self.status=''; return true;\"";
				}
			}
			if ($cell['onchange'])	// values != '1' can only set by a program (not in the editor so far)
			{
				$options .= ' onChange="'.($cell['onchange']=='1'?'this.form.submit();':$cell['onchange']).'"';
			}
			switch ($cell['type'])
			{
				case 'label':		//  size: [[b]old][[i]talic]
					$value = strlen($value) > 1 && !$cell['no_lang'] ? lang($value) : $value;
					if ($value != '' && strstr($cell['size'],'b')) $value = $this->html->bold($value);
					if ($value != '' && strstr($cell['size'],'i')) $value = $this->html->italic($value);
					$html .= $value;
					break;
				case 'raw':
					$html .= $value;
					break;
				case 'int':		// size: [min][,[max][,len]]
				case 'float':
					list($min,$max,$cell['size']) = explode(',',$cell['size']);
					if ($cell['size'] == '')
					{
						$cell['size'] = $cell['type'] == 'int' ? 5 : 8;
					}
					// fall-through
				case 'text':		// size: [length][,maxLength]
					if ($readonly)
					{
						$html .= $this->html->bold($value);
					}
					else
					{
						$html .= $this->html->input($form_name,$value,'',
							$options.$this->html->formatOptions($cell['size'],'SIZE,MAXLENGTH'));
					}
					break;
				case 'textarea':	// Multiline Text Input, size: [rows][,cols]
					$html .= $this->html->textarea($form_name,$value,
						$options.$this->html->formatOptions($cell['size'],'ROWS,COLS'));
					break;
				case 'date':
					if ($cell['size'] != '')
					{
						$date = split('[/.-]',$value);
						$mdy  = split('[/.-]',$cell['size']);
						for ($value=array(),$n = 0; $n < 3; ++$n)
						{
							switch($mdy[$n])
							{
								case 'Y': $value[0] = $date[$n]; break;
								case 'm': $value[1] = $date[$n]; break;
								case 'd': $value[2] = $date[$n]; break;
							}
						}
					}
					else
					{
						$value = array(date('Y',$value),date('m',$value),date('d',$value));
					}
					if ($readonly)
					{
						$html .= $GLOBALS['phpgw']->common->dateformatorder($value[0],$value[1],$value[2]);
					}
					else
					{
						$html .= $this->sbox->getDate($name.'[Y]',$name.'[m]',$name.'[d]',$value,$options);
					}
					break;
				case 'checkbox':
					if ($value)
					{
						$options .= ' CHECKED';
					}
					$html .= $this->html->input($form_name,'1','CHECKBOX',$options);
					break;
				case 'radio':		// size: value if checked
					if ($value == $cell['size'])
					{
						$options .= ' CHECKED';
					}
					$html .= $this->html->input($form_name,$cell['size'],'RADIO',$options);
					break;
				case 'button':
					$html .= $this->html->submit_button($form_name,$cell['label'],'',
						strlen($cell['label']) <= 1 || $cell['no_lang'],$options);
					break;
				case 'hrule':
					$html .= $this->html->hr($cell['size']);
					break;
				case 'template':	// size: index in content-array (if not full content is past further on)
					if ($this->autorepeat_idx($cell,$show_c,$show_row,$idx,$idx_cname) || $cell['size'] != '')
					{
						if ($span == '' && isset($content[$idx]['span']))
						{	// this allows a colspan in autorepeated cells like the editor
							$span = explode(',',$content[$idx]['span']); $span = $span[0];
							if ($span == 'all')
							{
								$span = 1 + $content['cols'] - $show_c;
							}
						}
						$readonlys = $readonlys[$idx];
						$content = $content[$idx];
						if ($idx_cname != '')
						{
							$cname .= $cname == '' ? $idx_cname : "[$idx_cname]";
						}
						//echo "<p>show_cell-autorepeat($name,$show_c,$show_row,cname='$cname',idx='$idx',idx_cname='$idx_cname',span='$span'): readonlys[$idx] ="; _debug_array($readonlys);
					}
					if ($readonly)
					{
						$readonlys['__ALL__'] = True;
					}
					$templ = is_object($cell['name']) ? $cell['name'] : new etemplate($name);
					$html .= $templ->show($content,$sel_options,$readonlys,$cname,$show_c,$show_row);
					break;
				case 'select':	// size:[linesOnMultiselect]
					if (isset($sel_options[$name]))
					{
						$sel_options = $sel_options[$name];
					}
					elseif (isset($sel_options[$org_name]))
					{
						$sel_options = $sel_options[$org_name];
					} elseif (isset($content["options-$name"]))
					{
						$sel_options = $content["options-$name"];
					}
					$html .= $this->sbox->getArrayItem($form_name.'[]',$value,$sel_options,$cell['no_lang'],
						$options,$cell['size']);
					break;
				case 'select-percent':
					$html .= $this->sbox->getPercentage($form_name,$value,$options);
					break;
				case 'select-priority':
					$html .= $this->sbox->getPriority($form_name,$value,$options);
					break;
				case 'select-access':
					$html .= $this->sbox->getAccessList($form_name,$value,$options);
					break;
				case 'select-country':
					$html .= $this->sbox->getCountry($form_name,$value,$options);
					break;
				case 'select-state':
					$html .= $this->sbox->list_states($form_name,$value);  // no helptext - old Function!!!
					break;
				case 'select-cat':
					$html .= $this->sbox->getCategory($form_name.'[]',$value,$cell['size'] >= 0,
						False,$cell['size'],$options);
					break;
				case 'select-account':
					$type = substr(strstr($cell['size'],','),1);
					if ($type == '')
					{
						$type = 'accounts';	// default is accounts
					}
					$html .= $this->sbox->getAccount($form_name.'[]',$value,2,$type,0+$cell['size'],$options);
					break;
				case 'image':
					$image = $this->html->image(substr($this->name,0,strpos($this->name,'.')),
						$cell['label'],lang($cell['help']),'BORDER=0');
					$html .= $name == '' ? $image : $this->html->a_href($image,$name);
					break;
				default:
					$html .= '<i>unknown type</i>';
					break;
			}
			if ($cell['type'] != 'button' && $cell['type'] != 'image' && (($label = $cell['label']) != '' || $html == ''))
			{
				if (strlen($label) > 1)
				{
					$label = lang($label);
				}
				$html_label = $html != '' && $label != '';

				if (strstr($label,'%s'))
				{
					$html = str_replace('%s',$html,$label);
				}
				elseif (($html = $label . ' ' . $html) == ' ')
				{
					$html = '&nbsp;';
				}
				if ($html_label)
				{
					$html = $this->html->label($html);
				}
			}
			return $html;
		}

		/*!
		@function process_show
		@abstract makes necessary adjustments on HTTP_POST_VARS after a eTemplate / form gots submitted
		@discussion This is only an internal function, dont call it direct use only exec
		@discussion process_show recursivly calls itself for the included eTemplates.
		@param $vars HTTP_POST_VARS on first call, later (deeper recursions) subscripts of it
		@param $readonly array with cell- / var-names which should NOT return content (this is to workaround browsers who not understand READONLY correct)
		@param $cname basename of our returnt content (same as in call to show)
		@returns the adjusted content (in the simplest case that would be $vars[$cname])
		*/
		function process_show(&$content,$readonlys='')
		{
			if (!$readonlys)
			{
				$readonlys = array();
			}
			if (!isset($content) || !is_array($content))
			{
				return;
			}
			if ($this->debug >= 1 || $this->debug == $this->name && $this->name)
			{
				echo "<p>process_show($this->name) start: content ="; _debug_array($content);
			}
			reset($this->data);
			if (isset($this->data[0]))
			{
				each($this->data);	// skip width
			}
			for ($r = 0; True /*list($row,$cols) = each($this->data)*/; ++$r)
			{
				$old_cols = $cols;
				if (!(list($nul,$cols) = each($this->data)))	// no further row
				{
					$cols = $old_cols;
					list($nul,$cell) = each($cols); reset($cols);
					if ((!$this->autorepeat_idx($cols['A'],0,$r,$idx,$idx_cname) ||
						$idx_cname == '' || !$this->isset_array($idx,$content)) &&
						(!$this->autorepeat_idx($cols['B'],1,$r,$idx,$idx_cname) ||
						$idx_cname == '' || !$this->isset_array($idx,$content)))
					{
						break;	// no auto-row-repeat
					}
				}
				$row = 1+$r;
				for ($c = 0; True /*list($col,$cell) = each($cols)*/; ++$c)
				{
					$old_cell = $cell;
					if (!(list($nul,$cell) = each($cols)))	// no further cols
					{
						$cell = $old_cell;
						if (!$this->autorepeat_idx($cell,$c,$r,$idx,$idx_cname,True) ||
							$idx_cname == '' || !$this->isset_array($idx,$content))
						{
							break;	// no auto-col-repeat
						}
					}
					else
					{
						$this->autorepeat_idx($cell,$c,$r,$idx,$idx_cname,True); // get idx_cname
					}
					$col = $this->num2chrs($c);

					$name = $this->expand_name($cell['name'],$c,$r);
					$readonly = $cell['readonly'] || $readonlys[$name] || $readonlys['__ALL__'] ||
						$cell['type'] == 'label' || $cell['type'] == 'image' || $cell['type'] == 'raw' ||
						$cell['type'] == 'hrule';

					if ($idx_cname == '' && $cell['type'] == 'template')	// only templates
					{
						if ($readonly)			// can't unset whole content!!!
						{
							$readonlys['__ALL__'] = True;
						}
						$this->process_show_cell($cell,$name,$c,$r,$readonlys,$content);
					}
					elseif (ereg('^([^[]*)\\[(.*)\\]$',$idx_cname,$regs))	// name contains array-index
					{
						/*  Attention: the unsets here and in the next else are vor two reasons:
						*  1) some browsers does NOT understand the READONLY-tag and sent content back
						*     this has to be unset, as we only report no-readonly fields
						*  2) php has a fault / feature :-) that it set unset array-elements passed as
						*     variable / changeable (&$var) to a function, this messes up a lot, as we
						*     depend on the fact variables are set or not for the autorepeat. To work
						*     around that, process_show_cell reports back if a variable is set or not
						*     via the returnvalue and we unset it or even the parent if is was not set.
						*/
						$parent_isset = isset($content[$regs[1]]);

						if ($readonly || !$this->process_show_cell($cell,$name,$c,$r,
								$readonlys[$regs[1]][$regs[2]],$content[$regs[1]][$regs[2]]))
						{
							if (!$parent_isset)
							{
								unset($content[$regs[1]]);
							}
							else
							{
								unset($content[$regs[1]][$regs[2]]);
							}
						}
					}
					else
					{
						if ($readonly || !$this->process_show_cell($cell,$name,$c,$r,
								$readonlys[$idx_cname],$content[$idx_cname]))
						{
							unset($content[$idx_cname]);
						}
					}
				}
			}
			if ($this->debug >= 2 || $this->debug == $this->name && $this->name)
			{
				echo "<p>process_show($this->name) end: content ="; _debug_array($content);
			}
		}

		/*!
		@function process_show_cell($cell,$name,$c,$r,$readonlys,&$value)
		@abstract makes necessary adjustments on $value eTemplate / form gots submitted
		@discussion This is only an internal function, dont call it direct use only exec
		@discussion process_show recursivly calls itself for the included eTemplates.
		@param $cell processed cell
		@param $name expanded name of cell
		@param $c,$r col,row index
		@param $readonlys readonlys-array to pass on for templates
		@param &$value value to change
		@returns if $value is set
		*/
		function process_show_cell($cell,$name,$c,$r,$readonlys,&$value)
		{
			if (is_array($cell['type']))
			{
				$cell['type'] = $cell['type'][0];
			}
			if ($this->debug >= 3 || $this->debug == $this->name || $this->debug == $cell['type'])
			{
				echo "<p>process_show_cell(c=$c, r=$r, name='$name',type='${cell['type']}) start: isset(value)=".(0+isset($value)).", value=";
				if (is_array($value))
				{
					_debug_array($value);
				}
				else
				{
					echo "'$value'</p>\n";
				}
			}
			switch ($cell['type'])
			{
				case 'int':
				case 'float':
					list($min,$max) = explode(',',$cell['size']);
					/*
					* TO DO: number- and range-check, if not enshured by java-script
					*/
					break;
				case 'text':
				case 'textarea':
					if (isset($value))
					{
						$value = stripslashes($value);
					}
					break;
				case 'date':
					if ($value['d'])
					{
						if (!$value['m'])
						{
							$value['m'] = date('m');
						}
						if (!$value['Y'])
						{
							$value['Y'] = date('Y');
						}
						if ($cell['size'] == '')
						{
							$value = mktime(0,0,0,$value['m'],$value['d'],$value['Y']);
						}
						else
						{
							for ($n = 0,$str = ''; $n < strlen($cell['size']); ++$n)
							{
								if (strstr('Ymd',$c = $cell['size'][$n]))
								{
									$str .= sprintf($c=='Y'?'%04d':'%02d',$value[$c]);
								}
								else
								{
									$str .= $c;
								}
							}
							$value = $str;
						}
					}
					else
					{
						$value = '';
					}
					break;
				case 'checkbox':
					if (!isset($value))	// checkbox was not checked
					{
						$value = 0;			// need to be reported too
					}
					break;
				case 'template':
					$templ = new etemplate($name);
					$templ->process_show($value,$readonlys);
					break;
				case 'select':
				case 'select-cat':
				case 'select-account':
					if (is_array($value))
					{
						$value = count($value) <= 1 ? $value[0] : implode(',',$value);
					}
					break;
				default: // do nothing, $value is correct as is
			}
			if ($this->debug >= 3 || $this->debug == $this->name || $this->debug == $cell['type'])
			{
				echo "<p>process_show_cell(name='$name',type='${cell['type']}) end: isset(value)=".(0+isset($value)).", value=";
				if (is_array($value))
				{
					_debug_array($value);
				}
				else
				{
					echo "'$value'</p>\n";
				}
			}
			return isset($value);
		}
	};