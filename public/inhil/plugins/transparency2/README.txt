Transparency2 plugin add a slider for each groups / layer in TOC.

It contains:
- config.inc: include the other files in pmapper
- transparency2.css: styles for sliders
- transparency2.js: plugin code and redefine "PM.Plugin.Transparency" and setGroupTransparency function to add a small part of code concerning sliders

Dependancies:
Plugin transparency present im pmapper installation, but activated or not.

How to use:
- Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
....
            <plugins>transparency2</plugins>
....
        </pmapper>
	</ini>
</pmapper>
- Specify if sliders have to represent opacity or transparency by adding in config_XXXXX.xml file:
<pmapper>
    <ini>
....
        <pluginsConfig>
....
			<transparency2>
				<useOpacity>off</useOpacity>
			</transparency2>
....
        </pluginsConfig>
    </ini>
</pmapper>

TO DO:
add sliders tooltip
