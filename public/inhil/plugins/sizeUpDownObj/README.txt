sizeUpDownObj allow to increase or decrease objet size using context menu on group/category tree

It contains:
- config.inc: include the other files in pmapper
- sizeUpDownObj.js: the function to execute when context menu item are selected
- x_setLayerSizeUpDownObj.php: create the session varaible that store the layer code
- sizeUpDownObjApply.php : call to set object new size in $map
- install subdirectory: files needed for installation / configuration -> copy gifs files in images\menus directory

Dependancies:
No dependancy

How to use:

- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>sizeUpDownObj</plugins>
....
        </pmapper>
	</ini>
</pmapper>

- Add the tools to context menu in /config/XXXXX/js_config.php file:
PM.contextMenuList = [     
    // layers/group in TOC
    {bindto: 'li.tocgrp',        
     menuid: 'cmenu_tocgroup',
     menulist: [   
...
        {id:'sizeUpDownObj-upObj', imgsrc:'sizeUpDownObj_upObj-b.png', text:'sizeUpDownObj__size_up', run:'PM.Plugin.SizeUpDownObj.cmSizeUpObj'},
        {id:'ssizeUpDownObj-downObj', imgsrc:'sizeUpDownObj_downObj-b.png', text:'sizeUpDownObj__size_down', run:'PM.Plugin.SizeUpDownObj.cmSizeDownObj'},
        {id:'sizeUpDownObj-resetObj', imgsrc:'sizeUpDownObj_resetObj-b.png', text:'sizeUpDownObj__size_reset', run:'PM.Plugin.SizeUpDownObj.cmResetSizeObj'},
		{id:'sizeUpDownObj-resetAll', imgsrc:'sizeUpDownObj_resetAll-b.png', text:'sizeUpDownObj__size_resetall', run:'PM.Plugin.SizeUpDownObj.cmResetSizeAllObj'},
...
        ], 
     styles: {menuStyle: {width:'auto'}}
    },
...
];

- Set default plugin configuration by adding a line in config_XXXXX.xml file:

</pmapper>
	</ini>
...
		</pluginsConfig>
...
			<sizeUpDownObj>
				<!-- factor to increase or decrease size (default 1.33) -->
				<factor>1.33</factor>
				<!-- max increase or decrease iterations (default values: 4) -->
				<!-- newsizemax = size * abs(max * factor)-->
				<!-- newsizemin = size / abs(min * factor)-->
				<max>4</max>
				<min>4</min>
				<!-- apply to labels too ? 0 or 1 (default 0) -->
				<doLabels>0</doLabels>
				<!-- do not decrease label size down to the following value -->
				<!-- -1 = do not use this functionnality -->
				<labelminsize>10</labelminsize>
			</sizeUpDownObj>
...
		</pluginsConfig>
	</ini>
</pmapper>


- Add the appropriate images from plugins/sizeUpDownObj/install/images/ to images/menus

- Add default translation from plugins/sizeUpDownObj/install/language_en-part.php to incphp/locale/language_en.php.
You can add translations for other language (French is provided) by doing the same.

