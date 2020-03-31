<?php
require_once("../../incphp/group.php");
require_once("../../incphp/pmsession.php");
require_once("../../incphp/globals.php");
require_once("../../incphp/common.php");

function createGroupSelect()
{
    $grouplist = $_SESSION["grouplist"];
    $html = _p("Layer");
    $html .= " <select id=\"transpdlg_groupsel\" onchange=\"PM.Plugin.Transparency.setTransprarencySlider(false)\">";
    $html .= "<option value=\"#\">" . _p("Select") . "...</option>";
    foreach ($grouplist as $grp) {
        $grpName = $grp->getGroupName();
        $grpDesc = $grp->getDescription();
        $html .= "<option value=\"$grpName\">$grpDesc</option>";
    }
    $html .= "</select>";
    
    return $html;

}

?>