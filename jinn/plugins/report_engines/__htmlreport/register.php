<?php
   $checkbox_arr=array(
	  'genheadfoot'=>lang('Generate html start en end tags'),
   );

   $this->registry->report_plugins['htmlreport']['config'] = array
   (
	  'fenheadfoot' => array(
		 'name' => 'genheadfoot',
		 'label' => lang('HTML Options'),
		 'type' => 'checkbox',
		 'checkbox_arr' => $checkbox_arr
	  ),
	  'htmltitle' => array(
		 'name' => 'htmltitle',
		 'label' => lang('title for HTML-page'),
		 'type' => 'text',
		 'size' => 100
	  ),
   );


