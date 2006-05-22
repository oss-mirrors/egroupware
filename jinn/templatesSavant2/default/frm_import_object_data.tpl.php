<!--<h3><?=lang('importeer Object')?>&nbsp;:&nbsp;<span style="background-color:#dddddd">&nbsp;<?=$this->objectname?>&nbsp;</span></h3>-->

<style>
   .previewtable 
   {
		 border:0;
		 border-spacing:0px;
   }
   .previewtable thead tr th 
   {
		 background-color:#cccccc;
		 font-weight:bold;
		 border:outset 1px #aaaaaa;
		 padding:2px;
   }
   .previewtable tbody tr td
   {
		 background-color:#ffffff;
		 border:inset 1px #aaaaaa;
		 padding:2px;
   }

   legend
   {
		 font-weight:bold;
   }
</style>
<form name="frm" method="post" action="<?=$this->action?>" enctype="multipart/form-data">

   <div style="width:460px;float:left;">
	  <table cellspacing="0" cellpadding="0" width="460" >
		 <tr>
			<td valign="top">
			   <fieldset>   
				  <legend><?=lang('Database settings')?></legend>
				  <table>
					 <tr>
						<td>
						   <?=lang('Re-create table')?>
						</td>
						<td>
						   <input type="checkbox" name="recreatetab" value="true" <?=$this->recreatetab?>/>
						</td>
					 </tr>
					 <tr>
						<td>
						   <?=lang('flush existing records');?>
						</td>
						<td>
						   <input type="checkbox" name="flushrecs" value="true" <?=$this->flushrecs?>/>
						</td>
					 </tr>
				  </table>
			   </fieldset>
			</td>

			<td>
			   <fieldset>   
				  <legend><?=lang('Seperator Settings')?></legend>
				  <table>
					 <tr>
						<td>
						   <?=lang('first row has field names')?>
						</td>
						<td>
						   <input type="checkbox" name="field_names_row" value="true" <?=$this->field_names_row_checked?>/>
						</td>
					 </tr>
					 <tr>
						<td>
						   <?=lang('fields terminated by')?>
						</td>
						<td>
						   <input type="text" size="1" name="field_terminator" value="<?=$this->field_terminator?>"/>
						</td>
					 </tr>
					 <tr>
						<td>
						   <?=lang('fields enclosed by')?>
						</td>
						<td>
						   <input type="text" size="1" name="field_wrapper" value="<?=$this->field_wrapper?>"/>
						</td>
					 </tr>
					 <tr>
						<td>
						   <?=lang('fields escaped by')?>
						</td>
						<td>
						   <input type="text" size="1" name="escape_character" value="<?=$this->escape_character?>"/>
						</td>
					 </tr>
					 <tr>
						<td>
						   <?=lang('lines terminated by')?>
						</td>
						<td>
						   <input type="text" size="1" name="row_terminator" value="<?=$this->row_terminator?>"/>
						</td>
					 </tr>
				  </table>
			   </fieldset>
			</td>
		 </tr>
	  </table>

	  <fieldset>   
		 <legend><?=lang('Field Mapping')?></legend>

		 <table width="100%" cellspacing="0" cellpadding="0">
			<tr style="font-weight:bold;background-color:#dddddd">
			   <td style="width:50%"><?=lang('Database fields')?></td>
			   <td style="width:50%"><?=lang('CSV Fields')?></td>
			</tr>
			<?php foreach($this->cols_arr as $col_arr):?>
			<tr>
			   <td>
				  <?=$col_arr[column_label]?>
			   </td>
			   <td>
				  <!-- possible CSV fields -->
				  <?php if($this->importpreview_head_arr):?>
				  <?php
					 $fld_arr=$this->importpreview_head_arr;
					 $fld_arr['--ignore--']='--ignore--';

				  ?>
				  <select name="csvfld_<?=$col_arr[column]?>">
					 <?php if($this->importpreview_head_arr):?>
					 <?php foreach($fld_arr as $keyoption=>$maptoption):?>
					 <?php
						unset($selected);
						if($keyoption==$_POST['csvfld_'.$col_arr['column']])
						{
						   $selected='selected="selected"';
						}
					 ?>
					 <option <?=$selected?> value="<?=$keyoption?>"><?=$maptoption?></option>
					 <?php endforeach?>
					 <?php endif?>
				  </select>
				  <?php else:?>
				  <input type="hidden" name="csvfld_<?=$col_arr[column]?>" value="<?=$_POST['csvfld_'.$col_arr['column']]?>" />
				  <?php
					 if(is_numeric($_POST['csvfld_'.$col_arr['column']]))
					 {
						echo intval($_POST['csvfld_'.$col_arr['column']])+1;
					 }
					 else
					 {
						echo $_POST['csvfld_'.$col_arr['column']];	
					 }

				  ?>
				  <?php endif?>

			   </td>
			</tr>
			<?php endforeach?>
		 </table>
	  </fieldset>

	  <fieldset>   
		 <legend><?=lang('Profiles')?></legend>
		 <table>
			<tr>
			   <td>
				  <?=lang('load import profile')?>
			   </td>
			   <td>
				  <select onChange="frm.load.value='true'; frm.do_profile.click();" name="load_profile"><?=$this->profiles?></select>
				  <input type="hidden" name="load" value=""/>
			   </td>
			</tr>
			<tr>
			   <td>
				  <?=lang('save import profile')?>
			   </td>
			   <td>
				  <input type="text" name="save_profile" value="<?=$this->save_profile?>"/>
				  <input class="egwbutton"  type="submit" name="do_profile" value="<?=lang('save profile')?>"/>
			   </td>
			</tr>
		 </table>
	  </fieldset>

   </div>

   <div style="width:250px;">
	  <fieldset style="width:100%;">
		 <legend><?=lang('Buttons')?></legend>
		 <div>
			<!-- Do Upload -->
			<?=$this->import?>

			<strong><?= lang('CSV File to import')?></strong><br/>
			<input type="file" name="importfile" /><br/>
			<input class="egwbutton"  type="submit" name="do_upload" value="<?=lang('Upload');?>"/>
			<!--				  <input class="egwbutton"  type="button" value="<?=lang('cancel')?>" onClick="history.back();"/>-->
		 </div>
		 <br/>

		 <!-- Do Import -->
		 <?php if($this->newtemp):?>
		 <div>
			<div><strong><?=lang('Uploaded CSV File')?></strong>:<br/><?=$this->newtempfilename;?></div>
			<input name="csvfile" type="hidden" value="<?=$this->newtemp;?>" />
			<br/>
			<input name="newtempfilename" type="hidden" value="<?=$this->newtempfilename;?>" />
			<input type="submit" name="do_reload_preview" value="<?=lang('Reload Import Preview')?>" />
			<input type="submit" name="do_import" value="<?=lang('Import')?>" />
		 </div>
		 <?php endif?>
	  </fieldset>


   </div>

   <div style="clear:both;"></div>
   <div style="margin-top:20px;background-color:#dedede;padding:5px;">
   <?php if(is_array($this->importpreview_arr)):?>
   <fieldset >
	  <legend><?=lang('CSV Fields Preview')?></legend>
	  <div style="overflow:auto;">
		 <table border="0" class="previewtable" style="">
			<?php if(is_array($this->importpreview_head_arr)):?>
			<thead style="">
			   <tr>
				  <?php foreach($this->importpreview_head_arr as $prev_head_cell):?>
				  <th>
					 <?=$prev_head_cell?>
				  </th>
				  <?php endforeach?>
			   </tr>
			</thead>
			<?php endif?>

			<tbody>
			   <?php foreach($this->importpreview_arr as $prev_row_arr):?>
			   <tr>
				  <?php foreach($prev_row_arr as $prev_cell):?>
				  <td>
					 <?=$prev_cell?>
				  </td>
				  <?php endforeach?>
			   </tr>
			   <?php endforeach?>
			</tbody>
		 </table>
	  </div>
   </fieldset>

<!--   <fieldset >
	  <legend><?=lang('Mapping Preview')?></legend>
	  <div style="width:470px;overflow:auto;">
		 <table border="0" class="previewtable" style="">
			<?php if(is_array($this->cols_arr)):?>
			<thead style="">
			   <tr>
				  <?php foreach($this->cols_arr as $col_arr):?>
				  <?php //foreach($this->importpreview_head_arr as $prev_head_cell):?>
				  <th>
					 <?=$col_arr[column_label]?>
					 <?=//$prev_head_cell?>
				  </th>
				  <?php endforeach?>
			   </tr>
			</thead>
			<?php endif?>

			<tbody>
			   <?php foreach($this->importpreview_arr as $prev_row_arr):?>
			   <tr>
				  <?$iii=0;?>
				  <?php foreach($prev_row_arr as $prev_cell):?>
				  <td>
					 <?=$prev_row_arr[$fld_arr[$iii]]?>
					 <?php $iii++;?>
					 <?=//$prev_cell?>
				  </td>
				  <?php endforeach?>
			   </tr>
			   <?php endforeach?>
			</tbody>
		 </table>
	  </div>
   </fieldset>
   -->
</div>
   <?php endif?>


   <!--
   <pre>
	  <?=//$this->insertprev?>
   </pre>
   -->

</form>
<div>&nbsp;</div>
