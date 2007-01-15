<?php
   include_once(EGW_INCLUDE_ROOT.'/jinn/inc/class.bojinn.inc.php');

   class plugin_reportengine_super extends bojinn
   {
	  var $tplsav2;
	  function plugin_reportengine_super()
	  {
		 parent::bojinn($session_name);
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
	  }

	  function _def_textarea($fieldname,$val)
	  {
		 $this->tplsav2->assign('val',$val);
		 $this->tplsav2->assign('fieldname',$fieldname);
		 return $this->tplsav2->fetch('report_def_textarea.tpl.php');
	  }

	  function insertfield_javascript()
	  {
		 return "	
		 function insertValue(id) 
		 {
			alert('text'+id);
			select = document.getElementById('sel'+id);
			var myField=document.getElementById('text'+id);
			var myValue='%%'+select.value+'%%';

			//IE support
			if (document.selection) 
			{
			   myField.focus();
			   sel = document.selection.createRange();
			   sel.text = myValue;
			}
			//MOZILLA/NETSCAPE support
			else if (myField.selectionStart || myField.selectionStart == '0') 
			{
			   var startPos = myField.selectionStart;
			   var endPos = myField.selectionEnd;
			   myField.value = myField.value.substring(0, startPos) + myValue + '\\n\\n' + myField.value.substring(endPos, myField.value.length);
			} 
			else 
			{
			   myField.value += myValue;
			}
		 }
		 ";
	  }

	  function resolve_extra_config($report_arr)
	  {
		 return unserialize($report_arr['report_type_confdata']); 
	  }

	  function send_save_headers($report_arr)
	  {}

	  function report_header_input($val)
	  {
		 return $this->_def_textarea('text1',$val);
	  }

	  function report_body_input($val)
	  {
		 return $this->_def_textarea('text2',$val);
	  }

	  function report_footer_input($val)
	  {
		 return $this->_def_textarea('text3',$val);
	  }

	  function pre_show_merged_report($records,$report_arr)
	  {}
	  
	  function show_merged_report($records,$report_arr,$output)
	  {}


   }
