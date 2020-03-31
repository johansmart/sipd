This plugin add buttons for each groups / layer in TOC.

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- addbuttonstogroups.js: the init function call after TOC loading.
- x_addbuttonstogroups.php: return the plugin configuration to JavaScript, via AJAX request.

Dependancies :
No dependancy

How to use:
Add "addbuttonstogroups" to the plugins list in the pmapper config file (e.g. config_default.xml):
<pmapper>
  <ini>
    <pmapper>
      <plugins>addbuttonstogroups</plugins>
...

Parameters:
Into the configuration file, locate the pluginsConfig section and add something like:
<pluginsConfig>
  <addbuttonstogroups>
    <abtgList>info|PM.Custom.showGroupInfo|Layer Info|images/infolink.gif,zoom|PM.Map.zoom2group|Zoom To Layer|images/zoomtiny.gif</abtgList>
  </addbuttonstogroups>
</pluginsConfig>

"abtgList" : specify the buttons to add.
For instance, with 'info|PM.Custom.showGroupInfo|Layer Info|images/infolink.gif,zoom|zoom2group|Zoom To Layer|images/zoomtiny.gif', the group with the following HTML code:

<span class="vis" id="spxg_countries">
  <span class="grp-title vis">Countries</span>
</span>

will become :

<span class="vis" id="spxg_countries">
  <span class="grp-title vis">Countries</span>
</span>
&nbsp;<a href="javascript:PM.Custom.showGroupInfo('countries')"
    title="Layer Info"><img alt="Layer Info" src="images/infolink.gif"></a>
&nbsp;<a href="javascript:zoom2group('countries')"
    title="Zoom To Layer"><img alt="Zoom To Layer" src="images/zoomtiny.gif"></a>

TO DO :
maybe add buttons to legend
