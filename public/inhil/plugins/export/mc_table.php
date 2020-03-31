<?php

// prevent XSS
if (isset($_REQUEST['_SESSION'])) exit();

require_once($_SESSION['PM_INCPHP'] . "/print/tcpdf.php");


class PDF_MC_Table extends TCPDF
{
    var $widths;
    var $aligns;

    function SetWidths($w)
    {
        //Set the array of column widths
        $this->widths=$w;
    }

    function SetAligns($a)
    {
        //Set the array of column alignments
        $this->aligns=$a;
    }

    function Row($data)
    {
        //Calculate the height of the row
        $h = 0;
        $numData = count($data);
        for ($i=0 ; $i<$numData ; $i++) {
        	$hTmp = $this->getStringHeight($this->widths[$i], $data[$i]);
        	$h = max($h, $hTmp);
        }
        
        $h += 0.5;
             
        //Issue a page break first if needed
        $this->CheckPageBreak($h);
        //Draw the cells of the row
        for($i=0;$i<$numData;$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            //Save the current position
            $x=$this->GetX();
            $y=$this->GetY();
            //Draw the border
            $this->Rect($x,$y,$w,$h);
            //Print the text
            $this->MultiCell($w,$h,$data[$i],0,$a);
            //Put the position to the right of the cell
            $this->SetXY($x+$w,$y);
        }
        //Go to the next line
        $this->Ln($h);
    }

    function CheckPageBreak($h=0, $y='', $addpage=true)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

}
?>
