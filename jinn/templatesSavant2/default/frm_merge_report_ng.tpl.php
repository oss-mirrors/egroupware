<style>
</style>
<div style="margin-left:100px;">
   <form id="dataset" name="dataset">
	  <input type="hidden" id="temp" name="temp" value="firstrec">
	  <input type="hidden" id="temp2" name="temp2" value="save">
	  <fieldset style="width:250px;">
		 <legend>1. <?=lang('Dataset')?></legend>
		 <table style="border-spacing:10px;" >
			<tr>
			   <td>
				  <input name="data_source" type="radio" value="firstrec" onclick="document.getElementById('temp').value=this.value" checked="checked">
			   </td>
			   <td>
				  <?=lang('first record');?>
			   </td>
			</tr>
			<tr>
			   <td>
				  <input name="data_source" type="radio" value="filtered" onclick="document.getElementById('temp').value=this.value">
			   </td>
			   <td>
				  <?=lang('filterd list');?>
			   </td>
			</tr>
			<tr>
			   <td>
				  <input name="data_source" type="radio" value="all" onclick="document.getElementById('temp').value=this.value">
			   </td>
			   <td>
				  <?=lang('all records');?>
			   </td>
			</tr>
			<!--
			<tr>
			   <td>
				  <input name="data_source" type="radio" value="selection" onclick="document.getElementById('temp').value=this.value">
			   </td>
			   <td>
				  <?=lang('selection');?>
			   </td>
			</tr>
			-->
		 </table>
	  </fieldset>
	  <br/>
	  <fieldset style="width:250px;">
		 <legend>2. <?=lang('Destination')?></legend>
		 <table style="border-spacing:10px;" >
			<tr>
			   <td>
				  <input name="dest" type="radio" value="save" onclick="document.getElementById('temp2').value=this.value" checked="checked">
			   </td>
			   <td>
				  <?=lang('Download File');?>
			   </td>
			</tr>
			<tr>
			   <td>
				  <input name="dest" type="radio" value="screen" onclick="document.getElementById('temp2').value=this.value">
			   </td>
			   <td>
				  <?=lang('Display in new window');?>
			   </td>
			</tr>
			<!--
			<tr>
			   <td>
				  <input name="dest" type="radio" value="print" onclick="document.getElementById('temp2').value=this.value">
			   </td>
			   <td>
				  <?=lang('Print');?>
			   </td>
			</tr>
			<tr>
			   <td>
				  <input name="dest" type="radio" value="email" onclick="document.getElementById('temp2').value=this.value">
			   </td>
			   <td>
				  <?=lang('Email');?>
			   </td>
			</tr>
			-->
		 </table>
	  </fieldset>
	  <script>

		 function do_merge()
		 {
			   var temp2=document.getElementById('temp2').value;
			   var mergelink='<?=$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uireport.merge&report_id=".$this->report_id."&obj_id=".$this->obj_id."&sel_values=".$this->sel_val."&selection=");?>'+document.getElementById('temp').value+'&dest='+temp2;

			   if(temp2=='save' || temp2=='email')
			   {
					 parent.window.location.href=mergelink; 
			   }
			   else if(temp2=='screen' || temp2=='print')
			   {
					 parent.window.open(mergelink, 'pop', 'width=800,height=600,location=no,menubar=yes,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no');			  
			   }
		 }
	  </script>

	  <br/>
	  <input class="egwbutton"  type="button" value="<?=lang('Merge');?>" onclick="do_merge();"> 
	  <input class="egwbutton"  type="button" value="<?=lang('Cancel');?>" onclick="parent.window.location.href='<?=$this->returnlink?>';">
	  <br/>
	  <br/>
   </form>
</div>
