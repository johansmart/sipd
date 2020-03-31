This is a small plugin for pmapper framework.
It just hide toc in order to show only the legend (and force legendStyle in swap mode)

It contains:
- config.inc: include the other files in pmapper
- legendonly.js: just the function to call after TOC loading
- legendonly.css:

Dependancies :
No dependancy

How to use:
Enable the plugin by adding a line in config_XXXXX.xml file:
<pmapper>
    <ini>
        <pmapper>
			....
            <plugins>legendonly</plugins>
			....
        </pmapper>
	</ini>
</pmapper>

Parameters:
No parameters.
