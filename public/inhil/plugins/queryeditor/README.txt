The QueryEditor plugin is a form witch permit to construct attribute query (syntax is near SQL). Layer and fields names are used in the UI and permit user to execute free queries that are not configured in search.xml

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- queryeditor.css: form styles
- queryeditor.js: the js applicative code for the plugin
- queryeditordlg.phtml: the html page containing the form for query generation 
- queryeditor.php: function that return groups to show in the form for query generation
- x_queryeditor.php: load fields headers for the selected layer, and execute query
- install directory: images for buttons

Dependancies :
- plugins/common/commonforms.css (and gif associated files if needed): inspired of cmxform, but no js associated
- plugins/common/common.js
- plugins/common/easyincludes.php
- plugins/common/groupsAndLayers.php
- plugins/common/SelectTools.inc.php

How to use:

- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
			....
            <plugins>queryeditor</plugins>
			....
        </pmapper>
	</ini>
</pmapper>

- Add the search tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
	.....
    buttons: [
		.....
		{tool:'queryeditor',		name:'QueryEditor', run:'PM.Plugin.QueryEditor.openDlg'},
		.....
	]
}

- Add an image to use for the button in the corresponding theme directory.

Parameters:

Configure the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pluginsConfig>
			.....
			<queryeditor>
				<layersType>3</layersType>
				<queryableLayers>
					<queryableLayer>
						<name>countries</name>
						<description>My country layer description</description>
					</queryableLayer>
					<queryableLayer>
						<name>cities10000eu</name>
						<description>My cities level description</description>
					</queryableLayer>
				</queryableLayers>
				<dlgType>dynwin</dlgType>
                <dlgOptions>
                    <width>450</width>
                    <height>565</height>
                    <left>300</left>
                    <top>100</top>
                </dlgOptions>
			</queryeditor>
        </pluginsConfig>
    </ini>
</pmapper>

layersType:
indicate witch layers/groups are available for attribut query in the query editor
 - 1 = all non raster layers
 - 2 = pre-defined list of layers (see queryableLayers parameter)
 - 3 = checked and non raster (default value)
 - 4 = checked and visible (depending on scale) and non raster

queryableLayers:
list of layer available in the query editor (only used if layersType = 2)


TO DO :
- try to find a generic way to show the first 5 different values for the chosen attribute field
- change operator organisation in the window
- try to see with Armin if/how it is possible to re-use part of search.php (getSearchParameters functions)
- maybe use the custom styles (search "// custom CSS for this window" in queryeditor.php)