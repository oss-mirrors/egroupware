<html>
   <head>
	  <style>
		 body
		 {
			   font-size:12px;
			   font-family:Sans-serif,Arial;
		 }
		 .genmenuimgcontainer
		 {
		 }
		 .genmenuimgbox
		 {
			   border: solid 1px #aaaaaa;
			   position:absolute;
			   top:7px;
			   left:0px;
			   width:300px;
			   height:100px;
			   z-index:89px;
		 }

		 .genmenuimglabel
		 {
			   position:absolute;
			   background-color:white;
			   top:0px;
			   left:10px;
			   z-index:99px;
			   padding:0px 3px 0px 3px;
		 }
	  </style>
	  <script>

	  </script>
   </head>
   <body>
	  <form name="frm">
		 <select name="numbox" onchange="imageboxes()">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
		 </select>

		 <div id="imgbox1" class="genmenuimgcontainer">
			<div class="genmenuimgbox">
			</div>
			<div  class="genmenuimglabel"><span><?php lang("Image 1")?></span>

			</div>
		 </div>

	  </body>
   </form>
</html>
