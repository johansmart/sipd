Drawing plugin: drawing shapes plugin, uses the geometry.js (see file /javascript/src/pm.geometry.js) and jsGraphics (see file  /javascript/src/wz_jsgraphics.js) libraries.
  
It contains:
- config.inc: include the other files in pmapper
- drawing.css: styles for html table
- drawing.js: plugin code 
- install subdirectory: files needed for installation / configuration

Dependancies:
- plugins/drawing_base/drawing_base.js: use some functions from drawing_base parent class . 
- plugins/common/common.js: somme common functions (colors, ...) 
- plugins/common/jquery.SevenColorPicker.js: a modified jQuery color picker plugin
- javascript/src/pm.geometry.js: geometry library for measurements and digitizing.
- javascript/src/xt.wz_jsgraphics.js: provides some functions to draw shapes dynamically into a webpage.
- plugins/clientdynamiclayers/*: dynamic layers functionnality
- config/common/template.map: template mapfile that contains layers definition to add dynamically

How to use:

- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>drawing</plugins>
....
        </pmapper>
	</ini>
</pmapper>

- Add the drawing tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
.....
    buttons: [
.....
		{tool:'drawing',		name:'Drawing'},
.....
	]
}

- Set default plugin configuration by adding a line in config_XXXXX.xml file:
<pmapper>
   <ini>
....
        <pluginsConfig>
....        
			<drawing>
				<!-- common parameters -->

				<!-- dialog type to use -->
        		<dlgType>dynwin</dlgType>
				<!-- default drawing color -->
				<default_color>#FF0000</default_color>
				<!-- default drawing outline color -->
				<default_outlineColor>#00FF00</default_outlineColor>
				
				<!-- point drawing parameters -->
				<point>
					<draw>
						<defaultSymbol>circle</defaultSymbol>
						<defaultThickness>10</defaultThickness>
					</draw>	
					<label>
						<defaultFont>FreeSans</defaultFont>
						<defaultTextSize>10</defaultTextSize>
					</label>
				</point>	
				
				<!-- line drawing parameters -->
				<line>
					<draw>
						<defaultSymbol>simple</defaultSymbol>
						<defaultThickness>2</defaultThickness>
					</draw>	
					<label>
						<defaultFont>FreeSans</defaultFont>
						<defaultTextSize>10</defaultTextSize>
					</label>
				</line>
				
				<!-- polygon drawing parameters -->
				<polygon>
					<draw>
						<defaultSymbol>square</defaultSymbol>
						<defaultThickness>2</defaultThickness>
					</draw>	
					<label>
						<defaultFont>FreeSans</defaultFont>
						<defaultTextSize>10</defaultTextSize>
					</label>
				</polygon>
				
				<!-- circle drawing parameters -->
				<circle>
					<draw>
						<defaultSymbol>drawing-circle</defaultSymbol>
						<defaultThickness>2</defaultThickness>
					</draw>	
					<label>
						<defaultFont>FreeSans</defaultFont>
						<defaultTextSize>10</defaultTextSize>
					</label>
				</circle>
				
				<!-- rectangle drawing parameters -->
				<rectangle>
					<draw>
						<defaultSymbol>square</defaultSymbol>
						<defaultThickness>2</defaultThickness>
					</draw>	
					<label>
						<defaultFont>FreeSans</defaultFont>
						<defaultTextSize>10</defaultTextSize>
					</label>
				</rectangle>
				
				<!-- annotation drawing parameters -->
				<annotation>
                    <draw>
                        <defaultSymbol></defaultSymbol>
                        <defaultThickness></defaultThickness>
                    </draw>
                    <label>
                        <defaultFont>FreeSans</defaultFont>
                        <defaultTextSize>10</defaultTextSize>
                    </label>
                </annotation>
				
        	</drawing>
....
        </pluginsConfig>
    </ini>
</pmapper>  


- Add the appropriate icon from plugins/drawing/install/drawing_off.gif to images/buttons/default (or the corresponding theme directory)

- Each symbol used in your your configuration file (<defaultSymbol>XXXXX</defaultSymbol>) have to be defined in your mapfile and in the template mapfile (by default config/common/template.map). Examples are provided in plugins/drawing/install/symbol-part.map. It could be a good thing top add them in the '# Symbols used in p.mapper' part...

- Add the appropriate layers from plugins/drawing/install/template-part.map to your template.map (by default config/common/template.map). Be carreful to respect the correct mapfile syntax / nesting

- Add default translation from plugins/drawing/install/language_en-part.php to incphp/locale/language_en.php. You can add translations for other language (French is provided) by doing the same.