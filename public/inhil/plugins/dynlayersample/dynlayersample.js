
/**
 * add dynamic layers from 'dynLayers' SESSION var
 */
function addDynLayers() {
    $.ajax({
        url: PM_PLUGIN_LOCATION + '/dynlayersample/x_dynlayersample.php?' + SID,
        dataType: "json",
        success: function(response){
            PM.Toc.init();
            
            // Update list in PHP session with new layerstring
            var layerstring = '&groups=' + PM.Toc.getLayers(); 
            $.each(response.activeLayers, function() { 
                layerstring += ',' + this;
            });
            PM.Map.updateSelLayers(PM_XAJAX_LOCATION + 'x_layer_update.php?'+SID+layerstring);
            
            // Add dyn default layers to PMap.defGroupList array
            $.merge(PM.defGroupList, response.activeLayers);
            
            PM.Map.reloadMap(false);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            if (window.console) console.log(errorThrown);
        }
    });  
}



