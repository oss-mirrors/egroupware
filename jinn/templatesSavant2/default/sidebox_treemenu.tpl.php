<!--<table>
   <tr>
	  <td><img src="<?=$this->start_img?>"></td>
	  <td><a href="<?=$GLOBALS['phpgw']->link('/qproject/index.php')?>"><?=lang('My Projects');?></a></td>
   </tr>
</table>-->
<script>
   function load_project(_nodeId) 
   { 
		 location.href='<?=$this->select_link?>'+_nodeId.substr(_nodeId.lastIndexOf('/')+1,99); 
   }
   //tree.openAllItems(1);
</script>
<?=$this->tree?>
