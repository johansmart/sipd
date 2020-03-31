
Adds a select box to let user swap to another map file configuration
uses the function to load different config files via URL, like

http://www.myurl.org?config=xyz

requires the definition of PM.Plugin.Mapselect.settings in /config/common/js_config.php
and the activation of the plugin in either config_common.xml or in all config_...xml files
 
PM.Plugin.Mapselect.settings = { 
    displayText:_p('Select Theme'),
    configList:{'default':"Map Default", 
                'dev':"Map Dev"
               }, 
    appendToDiv:".map-top-bar",
    cssDiv:{'position':'absolute', right:'60px'},
    cssSelect:{'margin-left':'5px'},
    resetSession:'groups'
};
                           
displayText: Text to show before checkbox 
configList:  contains key:value pairs, 
             key = definition of the config (corresponds to 'config_...ini' file), 
             value = the text displayed in the GUI
appendToDiv: Id of DOM element where to add the select box (prefix # for and id, . for a class)
cssDiv: styles for parent <div>
cssSelect: styles for <select> box
resetSession: comma-separated string with all session variables to be reset
              if set to 'ALL' then *all* session variables are reset.
              a typical value is 'groups', which means only the default groups are reset
              but the map extent is kept