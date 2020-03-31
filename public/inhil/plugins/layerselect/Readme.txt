
DESCRIPTION:
------------
In a dialog window the user can select the layers should be visible in the TOC


CONFIGURATION:
--------------
The following configuration settings are needed:

config_default.xml:
under <pluginsConfig> add a block like

    <layerselect>
        <categories>
            <category name="cat_admin">
                <group>countries</group>
                <group>admins</group>
                <group>cities10000eu</group>
                <group>urban</group>
            </category>
            <category name="cat_nature">
                <group>rivers</group>
                <group>corine</group>
                <group>water</group>
            </category>
            <category name="cat_infrastructure">
                <group>roads</group>
                <group>railroad</group>
            </category>
            <category name="cat_raster">
                <group>dem</group>
                <group>jpl_wms_global_mosaic</group>
            </category>
        </categories>
    </layerselect>
    
These are all possible layers a user can select. 

IMPORTANT: 
The plugin only works correctly if:
  - the layers/groups are used together with category definitions
    <map>
        <categories>
  - there is no entry for 
    <map>
        <allGroups>


Opening the dialog is performed via a tool link, toolbar button or whatever control
someone would like to use. It has to call the JavaScript method
    PM.Plugin.Layerselect.openDlg

an entry for the toolLink config PM.linksDefault in js_config.php would be e.g.
    {linkid:'layerselect', name:'Select Layers', run:'PM.Plugin.Layerselect.openDlg', imgsrc:'layers-bw.png'}
    
    
The initial size of the dialog can be changed by adding a line in 'js_config.php' under the config/..../ directory:

PM.Plugin.Layerselect.layerselectDlgOptions = {width:240, height:500, left:200, top:150, resizeable:true, newsize:false, container:'pmDlgContainer'};

adapt width and height values according to your needs.

