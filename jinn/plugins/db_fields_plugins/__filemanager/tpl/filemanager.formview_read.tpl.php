<table cellpadding="3" width="100%">
   <?php $i=0;?>
   <?php if(is_array($this->files)):?>
   <?php foreach($this->files as $onefile_html):?>
   <?$i++;?>
   <tr>
	  <td style="border-width:1px;border-style:solid;border-color:grey" valign="top"><?=$i?></td>
	  <td style="border-width:1px;border-style:solid;border-color:grey">
		 <?=$onefile_html?>
	  </td>
   </tr>							  
   <?php endforeach?>
   <?php endif?>
</table>
