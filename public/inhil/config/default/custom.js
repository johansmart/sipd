
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
                    var linkUrl = 'http:/' + '/en.wikipedia.org/wiki/' + fldValue; 
                    window.open(linkUrl, 'wikiquery');
                    //this.openHyperlinkDialog(linkUrl);
                //}
                break;
                
            default:
                alert ('See function openHyperlink in custom.js: ' + layer + ' - ' + fldName + ' - ' + fldValue);
        }
    },

    
    // Sample how to open a link in a p.mapper dialog box
    hyperlinkDlgOptions: {width:600, height:600, resizeable:true, newsize:false, container:'pmDlgContainerHyperlink'},
    
    openHyperlinkDialog: function(linkUrl) {
        var dlg = PM.Dlg.createDnRDlg(this.hyperlinkDlgOptions, _p('Hyperlink'), false);
        var h = '<iframe width="99%" height="98%" src="' + linkUrl + '" />';
        $('#pmDlgContainerHyperlink_MSG').html(h);
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
