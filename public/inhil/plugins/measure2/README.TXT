Measure2 plugin: measure lines lengths and polygons areas, uses the geometry.js library.
Measurements are printable contrary to the default pmapper measurement function. 

It contains:
- config.inc: include the other files in pmapper
- measure2.css: styles for html table
- measure2.js: plugin code 
- images subdirectory
- install subdirectory: files needed for installation / configuration

Dependancies:
- plugins/drawing_base/drawing_base.js: drawing_base parent class
- plugins/common/common.js: somme common functions (colors, ...) 
- plugins/common/jquery.SevenColorPicker.js: a modified jQuery color picker plugin
- plugins/clientdynamiclayers/*: dynamic layers functionnality
- config/common/template.map: template mapfile that contains layers definition to add dynamically

How to use:

- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>measure2</plugins>
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
			<measure2>
				<!-- dialog type to use -->
        		<dlgType>dynwin</dlgType>
				<!-- Default measurement color (in hexadecimal) -->
        		<default_color>#FF0000</default_color>
				<!-- create or not inputs measure to show dynamicaly measurements values when user's mouse move -->
        		<createMeasureInput>true</createMeasureInput>
        	</measure2>
....
        <pluginsConfig>
    </ini>
</pmapper> 

- Add the measure2 tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
.....
    buttons: [
.....
		{tool:'measure2',		name:'Measure2'},
.....
	]
}

- Add the appropriate icon from plugins/measure2/install/measure2_off.gif to images/buttons/default (or the corresponding theme directory)

- Add the appropriate layers from plugins/measure2/install/template-part.map to your template.map (by default config/common/template.map). Be carreful to respect the correct mapfile syntax / nesting

- Each symbol used in your your template mapfile (by default config/common/template.map) have to be defined in it and your mapfile. By default, lines are drawn using circle symbol and areas are drawn using square symbol. The square symbol is defined in the default mapfile, but not in the template mapfile. Just add the following definition in your template mapfile. It could be a good thing top add them in the '# Symbols used in p.mapper' part...
  SYMBOL
    NAME 'square'
    TYPE VECTOR
    FILLED TRUE
    POINTS
      0 1
      0 0
      1 0
      1 1
      0 1
    END
  END
An other solution is to use circle symbol (that is defined in both files by default) to drawn areas... 

- Add default translation from plugins/measure2/install/language_en-part.php to incphp/locale/language_en.php. You can add translations for other language (French is provided) by doing the same.
 
