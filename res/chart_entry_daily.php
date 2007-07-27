<!-- amcolumn script-->
  <script type="text/javascript" src="/img/am/amcolumn/swfobject.js"></script>
	<div id="flashcontent_user">
		<strong>You need to upgrade your Flash Player</strong>
	</div>

	<script type="text/javascript">
		// <![CDATA[		
		var so = new SWFObject("/img/am/amcolumn/amcolumn.swf", "amcolumn", "600", "240", "8", "#FFFFFF");
		so.addVariable("path", "/img/am/amcolumn/");
		so.addVariable("settings_file", escape("/data?m=chart_settings_entry_daily"));
		so.addVariable("data_file", escape("/data?m=chart_data_entry_daily"));
		so.addVariable("preloader_color", "#FFFFFF");
		so.write("flashcontent_user");
		// ]]>
	</script>
<!-- end of amcolumn script -->