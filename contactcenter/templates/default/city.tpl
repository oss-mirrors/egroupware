<!--

	eGroupWare - Contact Center
	New/Edit City Window Template
	
	Copyright (C) 2004
	Raphael Derosso Pereira <raphaelpereira@users.sourceforge.net>

    This file is licensed under the terms of the GNU GPL
	version 2 or above

-->

{cc_api}

<!-- <link rel="stylesheet" type="text/css" href="{ccCityCSS}" /> -->

<div id="ccCity" style="position: absolute; visibility: hidden">
	<!-- Title -->
	<input id="ccCity-title" type="hidden" value="{ccCity-title}" />
	
	<!-- Error Messages -->
	<input id="ccCity-errNoCountry" type="hidden" value="{ccCity-errNoCountry}" />
	<input id="ccCity-errNoState" type="hidden" value="{ccCity-errNoState}" />
	<input id="ccCity-errNoName" type="hidden" value="{ccCity-errNoName}" />
	<input id="ccCity-errLat" type="hidden" value="{ccCity-errLat}" />
	<input id="ccCity-errLon" type="hidden" value="{ccCity-errLon}" />
	<input id="ccCity-errAlt" type="hidden" value="{ccCity-errAlt}" />
	
	<form id="ccCityForm">
		<div>
			<span style="position: absolute; text-align: right; top: 10px; left: 5px; width: 100px">{ccCity-country}:</span>
			<select id="ccCity-country" style="position: absolute; top: 10px; left: 110px; width: 125px;" onchange="ccCity._getStates()">
				<option value="_NONE_">{ccCity-selectCountry}</option>
				<option value="_SEP_" style="text-align: center">-----------{cc_available}----------</option>
				{ccCity-countryList}
			</select>

			<span style="position: absolute; text-align: right; top: 10px; left: 234px; width: 60px; border: 0px solid black">{ccCity-state}:</span>
			<select id="ccCity-state" style="position: absolute; top: 10px; left: 299px; width: 125px">
				<option value="_NONE_">{ccCity-selectState}</option>
				<option value="_NOSTATE_">{ccCity-noState}</option>
				<option value="_SEP_" style="text-align: center">-----------{cc_available}----------</option>
			</select>

			<a href="javascript:void(0)" style="position: absolute; top: 10px; left: 424px" onclick="ccCity.newState()"><img id="ccCity-newState" type="button" title="{ccCity-newState}" alt="{ccCity-newState}" src="{ccCity-newStateIcon}" /></a>
			
			<span style="position: absolute; text-align: right; top: 40px; left: 5px; width: 100px">{ccCity-name}:</span>
			<input id="ccCity-name" style="position: absolute; top: 40px; left: 110px; width: 328px" type="text" />

			<span style="position: absolute; text-align: right; top: 70px; left: 5px; width: 100px">{ccCity-timezone}:</span>
			<select id="ccCity-timezone" style="position: absolute; top: 70px; left: 110px; width: 330px">
				<option value="_NONE_">{ccCity-selectTimezone}</option>
				<option value="_SEP_" style="text-align: center">-----------{cc_available}----------</option>
				{ccCity-timezones}
			</select>

			<span title="{ccCity-geoExpLat}" style="position: absolute; text-align: right; top: 100px; left: 5px; width: 100px">{ccCity-geoLat}:</span>
			<input title="{ccCity-geoExpLat}" id="ccCity-geoLat" style="position: absolute; top: 100px; left: 110px; width: 55px" type="text" />

			<span title="{ccCity-geoExpLon}" style="position: absolute; text-align: right; top: 100px; left: 145px; width: 100px">{ccCity-geoLon}:</span>
			<input title="{ccCity-geoExpLon}" id="ccCity-geoLon" style="position: absolute; top: 100px; left: 250px; width: 55px" type="text" />

			<span title="{ccCity-geoExpAlt}" style="position: absolute; text-align: right; top: 100px; left: 270px; width: 100px">{ccCity-geoAlt}:</span>
			<input title="{ccCity-geoExpAlt}" id="ccCity-geoAlt" style="position: absolute; top: 100px; left: 375px; width: 60px" type="text" />

			<input id="ccCity-save" style="position: absolute; top: 130px; left: 225px; width: 60px" type="button" value="{ccCity-save}" onclick="ccCity.send()" />
			<input id="ccCity-clear" style="position: absolute; top: 130px; left: 300px; width: 60px" type="button" value="{ccCity-clear}" onclick="ccCity.clear()" />
			<input id="ccCity-cancel" style="position: absolute; top: 130px; left: 375px; width: 60px" type="button" value="{ccCity-cancel}" onclick="ccCity.cancel()" />
		</div>
	</form>
</div>

<script type="text/javascript" src="{ccCity-jsFile}"></script>
