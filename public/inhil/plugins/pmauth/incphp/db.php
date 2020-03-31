<?php
/******************************************************************************
*
* Purpose: simple extention of PDO
* Author:  Walter Lorenzetti, gis3w, lorenzetti@gis3w.it
*
******************************************************************************
*
* Copyright (c) 2008-2010 gis3w
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version. See the COPYING file.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with p.mapper; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
******************************************************************************/


class Db extends PDO{
  const OPBOOLQUERY = 1; // caso di condizione BOOLEANA per la query
  const OPINQUERY = 2;  // cso di condizione IN per la query
  public static $retErrMsg = false; // ritorna gli errori se presenti
  public static $strErrMsg = ''; // stringa contenente gli errori del db
  public static $fetchMode = PDO::FETCH_ASSOC;
  public $driver;
  
  public function __construct(){
    // load ini db config file
    $fileSettings = $_SESSION['PM_PLUGIN_REALPATH']."/pmauth/config/settings.ini";
    if (!$settings = parse_ini_file($fileSettings, TRUE)) throw new exception('I can openS ' . $file . '.');
    // build dns connection
    $this->driver = $settings['database']['driver'];
    switch($this->driver){
        case 'sqlite':
             $dns = $this->driver.':'. $settings['database']['host'];
            error_log($dns);
             parent::__construct($dns);
        break;
        default:
             $dns = $this->driver .':host=' . $settings['database']['host'] .((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']) : '') .';dbname=' . $settings['database']['dbname'];
             parent::__construct($dns, $settings['database']['username'], $settings['database']['password']);
    }
    // set error system
    $this->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  }
  
  /**
   * Retry table info
   * @param string $tb
   * @return array or false boolean
   */
  public function tableInfo($tb){
    $q = "select * from ".$tb." limit 1;";
    $ctp = array();
    try{
      $st = $this->prepare($q);
      $st->execute();
      // a questo punto si restituisce il il vettore dei dati
      for($ncol = 0; $ncol < $st->columnCount(); $ncol++ ){
        $ctp[$ncol] = $st->getColumnMeta($ncol); 
      }
      return $ctp;
    }catch(PDOException $e){
        $this->setError($e->getMessage().' in '.$e->getFile());
      return false;
    }
  }

  /**
   * Get data froma table ora a query
   * @param String $scl
   * @param Array $op_wh
   * @param Int $op2
   * @param String $query
   * @return Data Array or False boolean
   */
  public function getData($scl,$op_wh=null,$op2=null,$query=null){
    $order='';
    $where='';

    if ($op_wh){
      $i = 0;
      $where =" WHERE ";
      foreach ($op_wh as $campo=>$valore){
        switch ($op2){
          case self::OPBOOLQUERY:
            $where .= $campo." is ".$valore;
            $i++;
          case self::OPINQUERY:
            $where .= $campo." in (".$valore.")";
            $i++;
          default:
            if(is_string($valore)) $valore = "'".$valore."'";
            $where .= $campo."=".$valore;
            $i++;
          }
            if ($i<count($op_wh)){
              $where .=" AND ";
            }
          }
     }
      
      // Building query
      $row_data=array();
        if ($query){
          $q = $query;	
        }else{
          $q="SELECT * FROM ".$scl.$where.$order;
        }

      try{
        $st = $this->prepare($q);
        $st->execute();
        $row_data = $st->fetchAll(self::$fetchMode);
        
        // data return
        return $row_data;

      }catch(PDOException $e){
        $this->setError($e->getMessage().' in '.$e->getFile());
        return false;
      }
  }

  /**
   * Check data combination in a table DB
   * @param String $tb
   * @param array $dataToCheck
   * @return boolean
   */
  public function checkData($tb,$dataToCheck){
    // per controllarlo si esegue la query specifica e si contano i risultati
    $cond = '';
    foreach($dataToCheck as $c => $v){
      $cond .= " and ".$c."=".(is_string($v) ? "'$v'" : $v);
    }
    $cond = substr($cond,5);
    $q = "select * from ".$tb." where ".$cond;
    try{
       switch($this->driver){
        case 'sqlite':
            return (count($this->query($q,PDO::FETCH_NUM)->fetchAll()) ? true : false);
        break;
        default:
            return ($this->query($q,PDO::FETCH_NUM)->fetchColumn() ? true : false);
      }
    }catch(PDOException $e){
      $this->setError($e->getMessage().' in '.$e->getFile());
      return false;
    }
    
  }

  /**
   * Exec a query
   * @param string $query
   * @return array
   */
	public function eQuery($query){
		return $this->getData('',null,null,$query);
	}
  
   /**
    * Exec a db transaction with qArr array fo query
    * @param Array $qArr
    * @return boolea
    */
	public function transaction($qArr = array()){
		$this->beginTransaction();
		try{
			foreach($qArr as $q){
                $qe = $this->exec($q);
            }
            // retrive data
			$this->commit();
      return true;
    }catch(PDOException $e){
      $this->setError($e->getMessage().' in '.$e->getFile());
      return false;
    }
	}
   
  /**
   * Set to error_log possible error msg
   * @param String $msg
   */
  private function setError($msg){
      error_log("<b>DBERROR</b>&nbsp;".$msg);
        if(self::$retErrMsg){
            self::$strErrMsg .= $msg."<br />";
        }
  }
}