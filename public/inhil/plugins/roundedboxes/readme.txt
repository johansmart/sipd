This is a small plugin for pmapper framework.

It uses "jQuery corner" plugin.

It contains:
- jquery.corner.js : the lpugin for jQuery ;
- roundedboxes.js : execute the plugin in pmapper ;
- roundedboxes.css : first part is the standard styles, then the 2d overwrites to styles of pmapper to draw corners ;
- config.inc : include the other files in pmapper.

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>roundedboxes</plugins>
....
        </pmapper>
	</ini>
</pmapper>