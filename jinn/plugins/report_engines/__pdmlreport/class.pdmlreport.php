<?php
   include_once(EGW_INCLUDE_ROOT.'/jinn/inc/class.plugin_reportengine_super.php');
   
   /**
    * pdmlreport 
    * 
    * @uses plugin
    * @uses _reportengine_super
    * @package 
    * @version $Id$
    * @copyright Lingewoud B.V.
    * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
    * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
    */
   class pdmlreport extends plugin_reportengine_super
   {
	  var $pdml;

	  function pdmlreport()
	  {
		 parent::plugin_reportengine_super();
	  }

	  function init_pdml_object($orientation,$papersize='a4')
	  {
		 require_once(EGW_INCLUDE_ROOT."/phpgwapi/inc/class.pdmlwrapper.inc.php" );

		 if(!is_object($this->pdmlwrapper))
		 {
			$this->pdml = new pdmlwrapper($orientation,'pt',$papersize); // P and A4 should be customizable. 
		 }
	  }

	  function show_merged_report($records,$report_arr,$buffer)
	  {
		 $extra_config=$this->resolve_extra_config($report_arr);
		 
		 $this->init_pdml_object($extra_config['orientation'],$extra_config['papersize']);
		 $this->pdml->compress=0;
		 $this->pdml->ParsePDML($buffer);
		 $s = $this->pdml->Output('report.pdf',"I");
	  }



   }

