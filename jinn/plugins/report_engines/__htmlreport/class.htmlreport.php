<?php
   include_once(EGW_INCLUDE_ROOT.'/jinn/inc/class.plugin_reportengine_super.php');

   class htmlreport extends plugin_reportengine_super
   {
	  var $init_options;
	  function htmlreport()
	  {
		 parent::plugin_reportengine_super();
		 $this->init_options = 'cleanup: "false"';

		 if (!is_object($GLOBALS['phpgw']->html))
		 {
			$GLOBALS['egw']->html = CreateObject('phpgwapi.html');
		 }
	  }

	  function insertfield_javascript()
	  {
		 return "	
		 function insertValue(id)
		 {
			select = document.getElementById('sel'+id);
			text = document.getElementById('text'+id);
			text.focus();
			tinyMCE.execInstanceCommand('text'+id,'mceInsertContent',false,'%%'+select.value+'%%');
		 }
		 ";
	  }

	  function report_header_input($val)
	  {
		 return $this->_richtextarea('text1',$val);
	  }

	  function report_body_input($val)
	  {
		 return $this->_richtextarea('text2',$val);
	  }

	  function report_footer_input($val)
	  {
		 return $this->_richtextarea('text3',$val);
	  }

	  function _richtextarea($fieldname,$val)
	  {
		 return $GLOBALS['egw']->html->tinymce($fieldname,$val,'',$this->init_options);
	  } 

	  function send_save_headers()
	  {
		 header ("Content-Type: html; name=\"".$link."\"");
		 // ask for download
		 header ("Content-Disposition: attachment; filename=\"report.html");
		 header("Expires: 0");
		 // the next headers are for IE and SSL
		 header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		 header("Pragma: public");
	  }


	  function pre_show_merged_report($records,$report_arr)
	  {
	//	 echo "hal.lo";
		 $extra_config=$this->resolve_extra_config($report_arr);
		 //_debug_array($extra_config);

	//	 _debug_array($report_arr);
		 if($extra_config['genheadfoot'])
		 {
			$output='
			<html>
			   <head>
				  <title>'.$extra_config['htmltitle'].'</title>
			   </head>
			   <body>
				  ';
			   }
			   else
			   {
				  $output ='';
			   }

			   return $output;
			}

			function show_merged_report($records,$report_arr,$output)
			{
			   $extra_config=$this->resolve_extra_config($report_arr);
			   if($extra_config['genheadfoot'])
			   {
				  $output.='
			   </body>
			</html>
			';
		 }

		 echo  $output;

	  }


   }
