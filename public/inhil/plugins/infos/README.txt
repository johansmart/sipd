This is a small plugin for pmapper framework.
It just load the "information" value in the mapfile for each layer.

It contains:
- config.inc: include the other files in pmapper
- init.php: load values form the configuration file
- infos.js: just overwrite the "showGroupInfo" function
- infos.phtml: generate the BODY part of an HTML page, by reading the layer corresponding value in the mapfile.
- infos.css: Styles (empty now)

Dependancies :
No dependancy

How to use:
Add the string "infos" to the plugins list in the pmapper config file

Parameters:
"infosMetadata" : specified the METADATA value to read for each layer. If not specified, the value of METADATA DESCRIPTION in the mapfile is used.