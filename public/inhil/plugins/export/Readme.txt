===================================================
 Plugin to export query results to various formats
===================================================

- adds export radio buttons to query result display
- currently implemented: XLS, PDF, CSV, SHP, DXF (geometry only: no attributes), MIF/MID

- Export to XLS requires the installation of the PEAR modules
  Spreadsheet_Excel_Writer and OLE

   => Run the following PEAR comands to install them:
      pear install -f OLE
      pear install -f Spreadsheet_Excel_Writer
      
- define export formats in config_yourcfg.xml by adding 
  the plugin to the tag <pluginsConfig>, like

    <pluginsConfig>
        <export>
            <formats>XLS</formats>
            <formats>CSV</formats>
            <formats>PDF</formats>
            <formats>SHP</formats>
            <formats>MIF</formats>
            <formats>DXF</formats>
        </export>
    </pluginsConfig>
    
- additional config is available for some formats:
  (added inside section <export></export>)
    * PDF formatting:
            <PDF>
                <defaultFont>FreeSans</defaultFont>
                <defaultFontSize>9</defaultFontSize>
                <!-- <headerFont>FreeSans</headerFont> -->
                <headerFontSize>9</headerFontSize>
                <headerFontStyle>BI</headerFontStyle>
                <!-- <layerrFont>FreeSans</layerFont> -->
                <layerFontSize>11</layerFontSize>
                <layerFontStyle>UB</layerFontStyle>
            </PDF>


- Export to DXF or MIF/MID requires PHP OGR