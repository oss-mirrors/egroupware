<!-- BEGIN pre_block -->
<html>
	<head>
		<title>{lang_objecteventconfiguration}</title>
	</head>
	<body{close}>
		<h1>{lang_objecteventconfiguration}</h1>
		<form method="post" name="events_config" action={action}>
		<br/>
		<hr/>
		<!-- END pre_block -->


		
		<!-- BEGIN delete_block -->
		<a href="{edit_url}">{config_description}</a><input type="checkbox" name="delete_{config_id}" value="true"/>{delete_label}
		<br/>
		<!-- END delete_block -->


		
		<!-- BEGIN new_block -->
		<hr/>
		<b>{block_title}</b>
		<br/>
		{event_label}
		<br/>
		<select name="event" onChange="{option_selected}">
			{event_options}
		</select>
		<br/>
		{plugin_label}
		<br/>
		<select name="plugin" onChange="{option_selected}">
			{plugin_options}
		</select>
		<br/>
		<!-- END new_block -->


		
		<!-- BEGIN config_block -->
		{title} - {plug_name}
		<br/>
		<!-- END config_block -->


		
		<!-- BEGIN row_block -->
		<br/>
		{description}{row}
		<!-- END row_block -->


		
		<!-- BEGIN post_block -->
		<br/>
		<input type="submit" value="{submit}">
		<input type="button" value="{close}" onClick="self.close()">
		<input type="button" value="{back}" onclick="history.back()">
		<br/>
		</form>
	</body>
</html>
<!-- END post_block -->
