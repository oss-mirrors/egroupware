<table border="0" cellspacing="1" cellpadding="0" width="100%" style="background-color:white;padding-bottom:3px;border-bottom:solid 1px #006699">
   <tr>
	  <td colspan="2"  valign="top" style="background-color:#d3dce3;width:1%;font-weight:bold;padding:3px 5px 3px 5px;"><?=lang('Actions')?></td>
   <?php foreach($this->visible_cols as $visible_col):?>
   <td style="background-color:#d3dce3;font-weight:bold;padding:3px;" align="center"><a href="javascript:void(0)" onclick="ajaxReorderList('<?=$this->m2o_arr[id]?>','<?=$visible_col[name]?>')"><?=$visible_col[label]?>&nbsp;<?=$colhead_order_by_img?></a><?=$tipmouseover?></td>
   <?php endforeach?>
</tr>
<?php if(is_array($this->linked_records)):?>
<?php foreach($this->linked_records as $linked_rec):?>
<?php 
   if($rowcolor=='#e8f0f0')
   {
	  $rowcolor='#ffffff';
   }
   else
   {
	  $rowcolor='#e8f0f0';
   }
?>
<tr>
   <td align="left" style="background-color:<?=$rowcolor?>;padding:0px 0px 0px 0px"><a title="<?=lang('Edit')?>" href="javascript:void(0);" onclick="ajax2_m2o_edit_frm('<?=$this->m2o_arr[id]?>','<?=$this->m2o_arr[object_conf]?>','<?=$linked_rec[where_string]?>','<?=$this->xmlhttp_get_m2o_link2?>&obj_conf=<?=$this->m2o_arr[object_conf]?>&where_string=<?=$linked_rec[where_string]?>','<?=$this->xmlhttp_save_m2o_link2?>&obj_conf=<?=$this->m2o_arr[object_conf]?>&where_string=<?=$linked_rec[where_string]?>')"><img width="16" src="<?=$this->img_edit?>" alt="<?=lang('edit')?>" /></a></td>

<td align="left" style="background-color:<?=$rowcolor?>;padding:0px 0px 0px 0px"><a title="<?=lang('delete')?>" href="javascript:void(0)" onClick="ajax2_delete_record('<?=$this->m2o_arr[id]?>','<?=$this->xmlhttp_delete_m2o_link2?>&where_string=<?=$linked_rec[where_string]?>');//return window.confirm('<?=lang('Delete this record?')?>');"><img width="16" src="<?=$this->img_delete?>" alt="<?=lang('delete')?>" /></a></td>

   <?php foreach($linked_rec[rec_parsed_arr] as $fkey=>$fval):?>
   <td valign="top" style="background-color:<?=$rowcolor?>;padding:0px 2px 0px 2px"><?=$fval?></td>
   <?php endforeach?>
</tr>
<?php endforeach?>
<?php else:?>
<tr><td><?=lang('No records linked')?></td></tr>
<?php endif?>
</table>

