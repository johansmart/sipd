
Enable the plugin by adding a line in config_default.xml file 
below the tag <pmapper>

<plugins>coordinates</plugins>

In the same file define the projections you would like to display 
under the tag <pluginsConfig> with an entry like the following:

<coordinates>
    <mapPrj name="ETRS LAEA" roundTo="0">
    </mapPrj>
    <prj name="lat/lon WGS84" roundTo="4">
        <definition>init=epsg:4326</definition>
    </prj>
    <prj name="UTM32" roundTo="0">
        <definition>init=epsg:32632</definition>
    </prj>
</coordinates>


All listed tags and attributes are mandatory.
