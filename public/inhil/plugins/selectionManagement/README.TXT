Plugin to manage selection:
- tools to load all / remove all / and remove objects from the query container table,
- selection or search operators like add / exclude / intersect / replace.
		
It contains:
- config.inc
- selectionManagement.js
- x_selectionManagement.php
- install subdirectory: language
- images

Dependancies:
- plugins/common/selectTools.inc.php
- plugins/clientDynamicLayers/*

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>selectionManagement</plugins>
....
        </pmapper>
	</ini>
</pmapper>

- Add the selectionManagement tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
.....
    buttons: [
.....
		{tool:'selectionManagement_reloadMap', name:'selectionManagement_reloadMap', run:'PM.Plugin.SelectionManagement.reloadMap'},
		{tool:'selectionManagement_removeSelection', name:'selectionManagement_removeSelection', run:'PM.Plugin.SelectionManagement.removeSelection'},
		{tool:'selectionManagement_reloadSelection', name:'selectionManagement_reloadSelection', run:'PM.Plugin.SelectionManagement.reloadSelection'},
.....
	]
}

- Add default translation from plugins/selectionManagement/install/language_en-part.php to incphp/locale/language_en.php. You can add translations for other language (French is provided) by doing the same.

- Add the appropriate icons from plugins/selectionManagement/install/selectionManagement_*_off.gif to images/buttons/default (or the corresponding theme directory).