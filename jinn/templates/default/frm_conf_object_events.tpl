<!-- BEGIN pre_block -->
<html>
	<head>
		<title>object event configuratie</title>
	</head>
	<body{close}>
<h1>object event configuratie</h1>
		<form method="post" name="events_config" action={action}>
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
<!-- END pre_block -->

<!-- BEGIN delete_block -->
{config_description}<input type="checkbox" name="delete_{config_id}" value="true"/>{delete_label}
<br/>
<!-- END delete_block -->

<!-- BEGIN config_block -->
{title} - {plug_name}
<!-- END config_block -->

<!-- BEGIN row_block -->
<br/>
{description}{row}
<!-- END row_block -->

<!-- BEGIN post_block -->
		<br/>
		<input type="submit" value="{submit}">
		<br/>
		</form>
	</body>
</html>
<!-- END post_block -->
