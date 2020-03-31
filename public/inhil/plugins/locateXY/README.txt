This is a small plugin for pmapper framework.
It just allow to use a button in user interface witch show the search for Point coordinates.

It contains:
- config.inc: include the other files in pmapper
- locateXY.js: the function to execute when button is clicked
- locateXY.php: generate the HTML code for the "searchForm".
- install subdirectory: files needed for installation / configuration 

Dependancies:
- proj4js plugin and its projections definitions
- optionaly: displayMarker plugin. If not installed or not activated via configuration file, the functionnality is not available.

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>locateXY</plugins>
....
        </pmapper>
	</ini>
</pmapper>


- Set default plugin configuration by adding a line in config_XXXXX.xml file:
<pmapper>
   <ini>
....
		<pluginsConfig>
....        
			<locateXY>
				<!-- dialog type to use -->
        		<dlgType>dynwin</dlgType>
        		<!-- dialog options -->
                <dlgOptions>
                    <width>300</width>
                    <height>225</height>
                    <left>250</left>
                    <top>250</top>
                    <resizeable>true</resizeable>
                    <newsize>true</newsize>
                    <container>pmLocateXYContainer</container>
                    <name>locateXY</name>
                </dlgOptions>
                <!-- margin -->
        		<marginX>500</marginX>
        		<marginY>500</marginY>
        		<!-- Digitize point (0 = zoom only ; 1 = zomm and digitize point) -->
        		<digitize>0</digitize>
        		<!-- Marker (requires displayMarker plugin) -->
        		<userMarker>1</userMarker>
        		<markerTimeout>400</markerTimeout>
        		<markerNumMax>3</markerNumMax>
        		<!-- map projection definition -->
        		<mapPrjDef>EPSG:900913</mapPrjDef>
        		<!-- Define available projections -->
				<!--
				If coordinates plugin is activated before this one,
				man can use an empty <projections> tag to use its projections.
				-->
				<projections>
	                <prj name="lat/lon WGS84">
	                    <definition>init=epsg:4326</definition>
	                </prj>
	                <prj name="UTM32">
	                    <definition>init=epsg:32632</definition>
	                </prj>
					<prj name="WGS 84">
						<definition>init=epsg:4326</definition>
					</prj>
					<prj name="Greek Grid - EPSG:2100">
						<definition>init=epsg:2100</definition>
					</prj>
					<prj name="Google-EPSG:900913">
						<definition>init=epsg:900913</definition>
					</prj>
				</projections>
        	</locateXY>
....
        <pluginsConfig>
    </ini>
</pmapper>

- Add the search tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
.....
    buttons: [
.....
        {tool:'locateXY',      name:'locateXY_ZoomToXY', run:'PM.Plugin.locateXY.openDlg'},
....
	]
}

- Add the appropriate icon from plugins/locateXY/install/locateXY_off.gif to images/buttons/default (or the corresponding theme directory).

- Add default translation from plugins/locateXY/install/language_en-part.php to incphp/locale/language_en.php. You can add translations for other language (French is provided) by doing the same.
 

