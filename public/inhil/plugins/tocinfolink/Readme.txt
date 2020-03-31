Adds info link image for info about layer/group.


By default all groups/layers will get an info link inserted BEFORE the layer name.
To change default behaviour define an entry in js_config.php in your config directory:

Create an entry in 
PM.Plugin.TocInfolink.options = {
    groupList: ['countries', 'cities10000eu'],   // list of layers/groups
    insert: 'after',                             // where to insert
    linkimg: 'images/infolink.gif'               // image used for link
};


