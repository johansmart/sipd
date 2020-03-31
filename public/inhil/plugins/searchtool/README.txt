This is a small plugin for pmapper framework.
It just allow to use a button in user interface witch show the search form layer.

It contains:
- config.inc: include the other files in pmapper
- searchtool.js: the function to execute when button is clicked
- x_searchtool.php: generate the HTML code for the "searchForm".
- install subdirectory: files needed for installation / configuration 

Dependancies:
No dependancy

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>searchtool</plugins>
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
			<searchtool>
				<!-- dialog type to use:dynwin or 0 (if 0, see conatiner) -->
        		<type>dynwin</type>
				<!-- style: block or inline -->
				<style>block</style>
				<!-- ID (with the '#') of the element that will contain the search form --> 
				<container>#uiLayoutCenter</container>
				<!-- Dialog option (if type = dynwin) -->
				<dlgOptions>
					<width>200</width>
					<height>150</height>
					<left>300</left>
					<top>100</top>
					<resizeable>false</resizeable>
					<newsize>true</newsize>
					<container>pmSearchToolContainer</container>
					<name>Search</name>
				</dlgOptions>
        	</searchtool>
....
        <pluginsConfig>
    </ini>
</pmapper> 


- Add the search tool button to the interface in /config/XXXXX/js_config.php file:
PM.buttonsDefault = {
.....
    buttons: [
.....
{tool:'searchtool',		name:'Search', run:'PM.Plugin.SearchTool.click'},
.....
	]
}

- Add the appropriate icon from plugins/searchtool/install/searchtool_off.gif to images/buttons/default (or the corresponding theme directory).


BE CARREFULL:
Maybe you will need to modify your CSS properties, like for instance :
#mapToolArea {
	height: 25px;
	text-align: left;
}
.pm-searchcont {
	position: relative;
}
