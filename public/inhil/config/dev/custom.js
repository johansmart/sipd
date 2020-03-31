
// 
// Some sample functions for customization
//

$.extend(PM.Custom,
{
    // Sample Hyperlink function for result window
    openHyperlink: function(layer, fldName, fldValue) {
        switch(layer) {
            case 'cities10000eu':
                //if (fldName == 'CITY_NAME') {
                    window.open('http:/' + '/en.wikipedia.org/wiki/' + fldValue, 'wikiquery');
                //}
                break;
                
            default:
                alert ('See function openHyperlink in custom.js: ' + layer + ' - ' + fldName + ' - ' + fldValue);
        }
    },

    showCategoryInfo: function(catId) {
        var catName = catId.replace(/licat_/, '');
        alert('Info about category: ' + catName);
    },

    showGroupInfo: function(groupId) {
        var groupName = groupId.replace(/ligrp_/, '');
        alert('Info about layer/group: ' + groupName);
    }

});
