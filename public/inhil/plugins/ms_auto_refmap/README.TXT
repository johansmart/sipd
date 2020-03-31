ms_auto_refmap:	Reference map auto calculate.

It contains:
- config.inc
- refmap.js
- refMapCalculate.php

Dependancies:
- plugins/common/groupsAndLayers.php
- plugins/common/pluginsMapUtils.inc.php
- plugins/common/easyMDB2.inc.php

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>ms_auto_refmap</plugins>
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
            <!-- Automatic generation of refmap -->
            <ms_auto_refmap>
            
                <!-- Image -->
                
                <!-- outputformat to use (have to be defined in mapfile) -->
                <format>png</format>
                <!-- alternate outputformat to use (have to be defined in mapfile) -->
                <!-- for MS 6 trnasparency not supported -->
                <!-- do not use RGBA in MS 6 if refmap will be used in mapObj::drawReferenceMap (transparency not supported) -->
                <altFormat>png_opaque</altFormat>
                <!-- URL of the refmap containing directory -->
                <imgBaseURL>/tmp/</imgBaseURL>
                <!-- groups / layers to draw in refmap -->
                <groups>
                    <group>countries</group>
                    <group>rivers</group>
                </groups>
                
                <!-- Extent -->
                
                <!-- Auto calculate extent (0 / 1) ? -->
                <autoExtent>1</autoExtent>
                <!-- groups / layers to use to determinate extent -->
                <autoExtentGroups>
                    <group>countries</group>
                </autoExtentGroups>
                <!-- add margin in percent -->
                <marginPercent>10</marginPercent>
                <!-- add magin in pixel (in percent not defined !)-->
                <marginFixValue>0</marginFixValue>
                
                <!-- Size -->
                
                <!-- Auto calculate refmap size (0 / 1) ? -->
                <autoSize>0</autoSize>
                <!-- decrease size to find the best one (0 / 1) ? -->
                <decreaseHeight>0</decreaseHeight>
                <!-- decrease height (0 / 1) ? -->
                <decreaseWidth>0</decreaseWidth>
                <!-- step in pixel to find the best size -->
                <stepSize>10</stepSize>
            </ms_auto_refmap>
....
        </pluginsConfig>
    </ini>
</pmapper>  

To do:
	