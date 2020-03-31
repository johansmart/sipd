<?php

class Projection
{
    function Projection($inX, $inY, $fromPrj, $toPrj)
    {
    	$poPoint = ms_newpointobj();
    	$poPoint->setXY($inX, $inY);
    	
    	if ($fromPrj && $toPrj && $fromPrj != $toPrj) {
    		if ($_SESSION['MS_VERSION'] >= 6) {
    			$fromPrjObj = new projectionObj($fromPrj);
    			$toPrjObj = new projectionObj($toPrj);
    		} else {
    			$fromPrjObj = ms_newprojectionobj($fromPrj);
    			$toPrjObj = ms_newprojectionobj($toPrj);
    		}
    		$poPoint->project($fromPrjObj, $toPrjObj);
    	}
        
        $this->x = $poPoint->x;
        $this->y = $poPoint->y;
    }
    
    function getX()
    {
        return $this->x;
    }
    
    function getY()
    {
        return $this->y;
    }
    
}

?>