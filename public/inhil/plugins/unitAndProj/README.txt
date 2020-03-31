unitAndProj display units and projection information

It contains:
- config.inc: include the other files in pmapper
- unitAndProj.js: show units and projection information in showcoords div
- x_getUnitAndProj.php: get units and projection information
- unitAndProj.css : css file used to set unitAndproj div style
- install subdirectory: files needed for installation / configuration

Dependancies:
no dependancies

How to use:

- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>unitAndProj</plugins>
....
        </pmapper>
	</ini>
</pmapper>


- Set default plugin configuration by adding a line in config_XXXXX.xml file:

</pmapper>
	</ini>
...
		</pluginsConfig>
...
			<unitAndProj><!-- Define available projections -->
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
					<prj name="Lambert 93">
						<definition>init=epsg:2154</definition>
					</prj>
					<prj name="Conique conforme 1ère Zone : CC42">
						<definition>init=epsg:RGF93CC42</definition>
					</prj>
					<prj name="Conique conforme 2ème Zone : CC43">
						<definition>init=epsg:RGF93CC43</definition>
					</prj>
					<prj name="Conique conforme 3ème Zone : CC44">
						<definition>init=epsg:RGF93CC44</definition>
					</prj>
					<prj name="Conique conforme 4ème Zone : CC45">
						<definition>init=epsg:RGF93CC45</definition>
					</prj>
					<prj name="Conique conforme 5ème Zone : CC46">
						<definition>init=epsg:RGF93CC46</definition>
					</prj>
					<prj name="Conique conforme 6ème Zone : CC47">
						<definition>init=epsg:RGF93CC47</definition>
					</prj>
					<prj name="Conique conforme 7ème Zone : CC48">
						<definition>init=epsg:RGF93CC48</definition>
					</prj>
					<prj name="Conique conforme 8ème Zone : CC49">
						<definition>init=epsg:RGF93CC49</definition>
					</prj>
					<prj name="Conique conforme 9ème Zone : CC50">
						<definition>init=epsg:RGF93CC50</definition>
					</prj>
					<prj name="Lambert Nord France">
						<definition>init=epsg:27561</definition>
					</prj>
					<prj name="Lambert Centre France">
						<definition>init=epsg:27562</definition>
					</prj>
					<prj name="Lambert Sud France">
						<definition>init=epsg:27563</definition>
					</prj>
					<prj name="Lambert Corse">
						<definition>init=epsg:27564</definition>
					</prj>
					<prj name="Lambert zone I">
						<definition>init=epsg:27571</definition>
					</prj>
					<prj name="Lambert zone II">
						<definition>init=epsg:27572</definition>
					</prj>
					<prj name="Lambert zone III">
						<definition>init=epsg:27573</definition>
					</prj>
					<prj name="Lambert zone IV">
						<definition>init=epsg:27574</definition>
					</prj>
				</projections>
			</unitAndProj>
...
		</pluginsConfig>
	</ini>
</pmapper>



- Add default translation from plugins/unitAndProj/install/language_en-part.php to incphp/locale/language_en.php.
You can add translations for other language (French is provided) by doing the same.

