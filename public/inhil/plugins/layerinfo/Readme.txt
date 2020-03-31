
Replaces the default group information function from context menu with specific one.

Configuration: 

add the file "layerinfo.xml" file to the your config directory 
and adapt it for your datasets. Contents can be any XHTML-compliant code.

Create the following entries in your config XML file:

<pmapper>
    ...
    <plugins>layerinfo</plugins>
    ...
<pmapper>


<pluginsConfig>
    ...
    <layerinfo>
        <configfile>dev/layerinfo.xml</configfile> <!-- path to your config file -->
    </layerinfo>
    ...
</pluginsConfig>