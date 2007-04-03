<script>
   function load_project(_nodeId) 
   { 
		 location.href='<?=$this->select_link?>'+_nodeId.substr(_nodeId.lastIndexOf('/')+1,99); 
   }
</script>
<?=$this->tree?>
