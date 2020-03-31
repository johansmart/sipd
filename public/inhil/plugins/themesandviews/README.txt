The ThemesAndViews plugin can extend the layers selection.

Few definitions:
- a "theme" is a list of layer to select, possibily with opacity.
- a "view" is a theme but with extent.

What does this plugins can do ?
- auto insert a selectbox to chose a theme
- auto insert a selectbox to chose a view
- use a button tool to show theme box in "mapToolArea"
- use a button tool to show view box in "mapToolArea"
- if you don't display TOC but only legend, the layer management behavior become very simple (but with less functionalities !) for GIS beginner 

Complementary plugin : ThemesAndViewsAdmin? plugin (maybe soon available...)

It contains:
- config.inc: include the other files in pmapper
- tav.css: boxes styles
- tav.js: plugin's js code 
- tavCommon.js: functions used by this plugin and ThemesAndViewsAdmin plugin
- tav.php: server part (XML loading, boxes code generation, ...)
- x_tavBox.php: call in AJAX to generate boxes for the interface
- x_tavApply.php: call in AJAX to apply a theme or a view
- install directory: language files and themes file example 

Dependancies: none

Themes and views file content: example provided in plugins/themesandviews/install/themesAndViews.xml 

How to use:

- Enable the plugin by adding a line in config_XXXXX.xml file:

<pmapper>
    <ini>
        <pmapper>
....
            <plugins>themesandviews</plugins>
....
        </pmapper>
	</ini>
</pmapper>

- Set plugin configuration by adding a line in config_XXXXX.xml file:

<pmapper>
    <ini>
....
        <pluginsConfig>
....
            <themesandviews>
                <file>common/themesAndViews.xml</file>
                <defaultType>none</defaultType>
                <defaultCodeValue></defaultCodeValue>
                <themes>
                    <insertBoxType>first</insertBoxType> 
                    <boxContainer>.map-top-bar</boxContainer> 
                    <initAfterTOC>0</initAfterTOC> 
                    <selBoxStr>&lt;div id='selThemeBox' class='tavSelectBox' /&gt;</selBoxStr>
                    <keepSelected>0</keepSelected>
                </themes>
                <views>
                    <insertBoxType>last</insertBoxType> 
                    <boxContainer>.map-top-bar</boxContainer> 
                    <initAfterTOC>0</initAfterTOC> 
                    <selBoxStr>&lt;div id='selViewBox' class='tavSelectBox' /&gt;</selBoxStr>
                    <keepSelected>0</keepSelected>
                </views>
            </themesandviews>   
....
        </pluginsConfig>
    </ini>
</pmapper>

Common parameters for themes and views:

    * file: file that will define the themes and views
    * defaultType: what to automatically apply at startup. Possible values: none, theme, view
    * defaultCodeValue: empty or name of a theme or view to load at startup (depends on defaultType) 

Parameters that can be different for themes and views:

    * insertBoxType: user (user defined in layout), first (= at first position in the specifyed container), last (= at the end of the specified container)
    * boxContainer: jQuery selector for box container
    * initAfterTOC: 0=before or 1=after
    * selBoxStr: HTML code for container
    * keepSelected: 0 (the box will not keep selected value) or 1 (keep selected value) 

- (optional) Add the themesandviews tool buttons to the interface in /config/XXXXX/js_config.php file:

PM.buttonsDefault = {
.....
    buttons: [
.....
		{tool:'themesbox',		name:'themesbox'},
		{tool:'viewsbox',		name:'viewsbox'},
.....
	]
}

- Add default translation from plugins/themesandviews /install/language_en-part.php to incphp/locale/language_en.php. You can add translations for other language (French is provided) by doing the same.

- (optional) Add the appropriate icons for the buttons in images/buttons/default (or the corresponding theme directory)

- (optional) Add styles. For instance with pmapper default layout and plugin default configuration:
	* in a CSS file:
.tavSelectBox {
    float: left;
    padding-right: 50px;
}
	* or replace in your configuration file "{{{class='tavSelectBox'}}}" with "{{{class='tavSelectBox' style='float:right; padding-right:50px;'}}}".

- Themes and views file content example provided in plugins/themesandviews/install/themesAndViews.xml