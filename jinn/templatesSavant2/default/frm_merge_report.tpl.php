<style>
   iframe
   {
		 width:100%;
		 height: 400px;
		 background-color:white;
   }
   #options
   {
		 background-color:#ddd;
		 border:1px solid black;
		 width: auto;
   }
</style>
<div id='options'>
   <form name='sel'>
	  <input type='hidden' id='temp' name ='temp' value='all'>
	  <input name='data_source' type='radio' value='filtered' onClick = 'document.forms[0].temp.value=this.value'><?=lang('filterd list');?><br>
	  <input name='data_source' type='radio' value='all'  onClick = 'document.forms[0].temp.value=this.value' checked='checked'><?=lang('all records');?><br>
	  <input name='data_source' type='radio' value='selection'onClick = 'document.forms[0].temp.value=this.value'><?=lang('selection');?><br>
	  <input type='button' value='<?=lang('apply');?>' onClick='document.getElementById("merged_rapport").src="<?=$GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uireport.show_merged_report&report_id=".$this->report_id."&obj_id=".$this->obj_id."&sel_values=".$this->sel_val."&selection=");?>"+document.forms[0].temp.value'>
</form>
</div><br>
<iframe src='<?=$GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uireport.show_merged_report&report_id=".$this->report_id);?>' id='merged_rapport'></iframe>
<form>
   <input type='button' value ='<?=lang('Save as');?>' onClick='document.getElementById("merged_rapport").src="<?=$GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uireport.save_merged_report&report_id=".$this->report_id."&sel_values=".$this->sel_values."&selection=");?>"+document.forms[0].temp.value'>
   <input type='button' value ='<?=lang('Print');?>' onClick='document.getElementById("merged_rapport").src="<?=$GLOBALS[phpgw]->link('/index.php',"menuaction=jinn.uireport.print_merged_report&report_id=".$this->report_id."&sel_values=".$this->sel_values."&selection=");?>"+document.forms[0].temp.value'>
   <input type='button' value='<?=lang('Close');?>' onClick='parent.close();'>
</form>

