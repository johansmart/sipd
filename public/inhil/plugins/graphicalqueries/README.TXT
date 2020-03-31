graphicalqueries: Plugin for queries by point, polygon, polyline, circle and rectangle.

It contains:
- config.inc: include the other files in pmapper
- graphicalqueries.js: plugin client code
- graphicalqueries.css: styles
- x_graphicalqueries.php: call in AJAX to return new selection after querying
- queryExtended.php: querying class
- /images: icons used for add/remove/intersect buttons
- /install: files needed for installation / configuration (graphicalqueries.css is optional, take it as example if plugin toolbox is not displayed correctly in mapToolArea)

Dependancies:
- plugins/common/common.js
- plugins/drawing_base/
- plugins/common/selectTools.php

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>graphicalqueries</plugins>
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
			<graphicalqueries>
				<!-- Use only layers visible at current scale (for buffer search) -->
				<bufferOnlyWithScale>1</bufferOnlyWithScale>
			</graphicalqueries>
....
        <pluginsConfig>
    </ini>
</pmapper>  

- Add the graphicalqueries tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
.....
    buttons: [
.....
		{tool:'graphicalqueries_point',  name:'graphicalqueries_point'},
        {tool:'graphicalqueries_circle',  name:'graphicalqueries_circle'},
        {tool:'graphicalqueries_polygon', name:'graphicalqueries_polygon'},
        {tool:'graphicalqueries_polyline',  name:'graphicalqueries_polyline'},
        {tool:'graphicalqueries_rectangle',  name:'graphicalqueries_rectangle'},
.....
	]
}

- Add the appropriate icons from plugins/graphicalqueries/install/images to images/buttons/default (or the corresponding theme directory).

- Add default translation from plugins/graphicalqueries/install/language_en-part.php to incphp/locale/language_en.php. You can add translations for other language (French is provided) by doing the same.

- optional: add styles (from plugins/graphicalqueries/install/graphicalqueries.css to your configuration directory)