<?php

/******************************************************************************
 *
 * Purpose: Easy MDB2 use
 * Author:  Thomas Raffin, SIRAP
 *
 ******************************************************************************
 *
 * Copyright (c) 2008 SIRAP
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. See the COPYING file.
 *
 * This software is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with p.mapper; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 ******************************************************************************/

require_once("MDB2.php");

class Easy_MDB2 {
    /**
	 * DSN configuration
	 */
	private $dsn;
	private $charset;

	private $db;
	protected $dbh;
	
	private $reverseMode;
	private $managerMode;
	
	
	public function __construct() {
		$this->dsn = "";
		$this->charset = false;
        $this->db = null;
        $this->dbh = null;
        $this->reverseMode = false;
        $this->managerMode = false;
	}
	
	private function setReverseModeON() {
		if (!$this->reverseMode) {
			$this->dbh->loadModule('Reverse', null, true);
			$this->reverseMode = true;
		}
	}
	
	private function setManagerModeON() {
		if (!$this->managerMode) {
			$this->dbh->loadModule('Manager', null, true);
			$this->managerMode = true;
		}
	}
	
	public function db_start() {
	}

	public function setCharset($charset) {
		$this->charset = $charset;
	}
	
	public function setDSN($dsn) {
		$this->dsn = $dsn;
	}
	
	public function start() {
    	$ok = false;
    	if (!$this->db) {
			$this->db = new MDB2;
		}
		if ($this->db) {
	    	if (!$this->dbh) {
		    	$this->dbh = $this->db->connect($this->dsn);
				if (!$this->db->isError($this->dbh)) {
					$this->dbh->setFetchMode(MDB2_FETCHMODE_ASSOC);
					$this->dbh->loadModule('Extended');
					if ($this->charset) {
						$this->dbh->setCharset($this->charset);
					}
					$ok = true;
				} else {
					pm_logDebug(0, "ERROR - Easy_MDB2 - DB connection: \n" . $this->dbh->getMessage());
					$this->dbh = null;
				}
	    	} else {
	    		$ok = true;
	    	}
		}
		return $ok;
	}
		
	public function end($terminateCurrentLog = false) {
		if ($this->dbh) {
			$this->dbh->disconnect();
			pm_logDebug(4, "MSG - Easy_MDB2 - DB disconnection\n");
		} 
	}
	
	public function quoteval($val, $type) {
		return $this->dbh->quote($val, $type);
	}
	
	public function selectByQuery($sqlQuery, $msg, $orderBy = '', $limit = 0, $offset = 0) {
		$ret = Array();

		if ($this->dbh) {
			if ($orderBy) {
				if (!stripos("ORDER BY", $sqlQuery)) {
					$sqlQuery .= " ORDER BY " . $orderBy;
				}
			}

			if ( ($limit > 0) || ($offset > 0) ) {
				$this->dbh->setLimit($limit, $offset);
			}

			$res = $this->dbh->query($sqlQuery);
			if ($this->db->isError($res)) {
				pm_logDebug(0, "ERROR - Easy_MDB2 (SELECT) - $msg: \n" . $res->getMessage());
			} else {
				pm_logDebug(4, "MSG - Easy_MDB2 - (SELECT OK) - $msg\n");
/*
				if ($row = $res->fetchRow()) {
					$keys = array_keys($row);
					$ret["header"] = $keys;
					$ret["values"][] = $row;
				}
				while ($row = $res->fetchRow()) {
				$ret["values"][] = $row;
				}
*/
				$ret["header"] = $res->getColumnNames(true);
				$ret["values"] = Array();
				while($row = $res->fetchRow()) {
					$ret["values"][] = $row;
				}

				$res->free();
			}
		}

		return $ret;
	}
	


	/**
	 * 
	 * Test if table exists in DB
	 * 
	 * @param string $tableName Table name
	 * @param string $msg optionnal message to log
	 */
	public function tableExists($tableName, $field, $msg = '') {
		$ret = false;

		if ($this->dbh) {
/*
			$this->setReverseModeON();
			$res = $this->dbh->getTableFieldDefinition($tableName, $field);
*/
			pm_logDebug(4, "MSG - Easy_MDB2 - (tableExists: $tableName) - $msg\n");
			
			$tableWithFieldExists = false;
				
			// "schema"."table"
			$matches = array();
			if (preg_match('#(?:["\']?(?P<schema>\w+)["\']?\.)?["\']?(?P<table>\w+)["\']?#', $tableName, $matches)) {
				if (isset($matches['schema']) && $matches['schema']
				&& isset($matches['table']) && $matches['table']) {
					$schema = $matches['schema'];
					$table = $matches['table'];
			
					$sql = "SELECT table_schema, table_name, column_name
							FROM information_schema.tables as t
							LEFT JOIN information_schema.columns as c USING (table_schema, table_name)
							WHERE table_schema = '$schema' 
							AND table_name = '$table'
							AND table_type = 'BASE TABLE'
							AND column_name = '$field';";
					
					$res = $this->dbh->query($sql);
					if ($this->db->isError($res)) {
						pm_logDebug(0, "ERROR - Easy_MDB2 (tableExists - \"$schema\".\"$table\" - \"$field\") - $msg: \n" . $res->getMessage());
					} else {
						if ($res->numRows() == 1) {
							$tableWithFieldExists = true;
						}
					}
				}
			}
			if ($tableWithFieldExists) {
				pm_logDebug(4, "MSG - Easy_MDB2 - (tableExists : $tableName --> exists)");
				$ret = true;
			} else {
				pm_logDebug(0, "ERROR - Easy_MDB2 (tableExists : $tableName --> NOT exists)");
			}
		}

		return $ret;
	}

	public function getFieldType($tableName, $fieldName) {
		$mdb2FieldType = '';

		if ($this->dbh) {
			$this->setReverseModeON();
			$resFieldDef = $this->dbh->getTableFieldDefinition($tableName, $fieldName);
			if ($this->db->isError($resFieldDef)) {
				pm_logDebug(0, "ERROR - Easy_MDB2 - getFieldType($tableName, $fieldName) - : \n" . $resFieldDef->getMessage());
			} else {
				$mdb2FieldType = $resFieldDef[0]['mdb2type']; // MDB2 field's type : integer, text, float ...
			}
		}

		return $mdb2FieldType;
	}
	
	/**
	 *
	 * Test if data base élément exists (table, fields, fields with types)
	 *
	 * @param string $schemaName schema name 
	 * @param string $msg $tableName table name
	 * @param string or array $fieldsName fields names (optional)
	 * @param string or array $fieldsType (N => numérique, S => string, or any data_type)
	 * 
	 * @return true if all exists
	 * 
	 * @example
	 * 
	 * tableOrFieldsExists('MySchema', 'MayTable')
	 * 
	 * tableOrFieldsExists('MySchema', 'MayTable', 'fieldName')
	 *
	 * tableOrFieldsExists('MySchema', 'MayTable', 'fieldName', 'N')
	 * 
	 * $fields[] = 'field1'
	 * $fields[] = 'field2'
	 * $dataTypes['field1'] = 'S'
	 * $dataTypes['field2'] = 'integer'
	 * tableOrFieldsExists('MySchema', 'MayTable', $fields, $dataTypes)
	 * 
	 * tableOrFieldsExists('MySchema', 'MayTable', $fields, 'varchar')
	 */
	
	public function tableOrFieldsExists($schemaName, $tableName, $fieldsName = NULL, $fieldsType = NULL) {
		$ret = false;
		$req = '';
		$nbRes = 1;
		
		if (isset($schemaName) || isset($tableName)) {
			$fieldsArray = NULL;
			
			// We test only if table exists
			if (!isset($fieldsName)) {
				$req = "SELECT table_name FROM information_schema.tables WHERE table_schema='$schemaName' AND table_name='$tableName'";
			// Else we test if table and flields exists 	
			} else {
				$fieldsArray = Array();
				
				if (is_array($fieldsName)) {
					$fieldsArray = $fieldsName;
				} else {
					$fieldsArray[] = $fieldsName;
				}
				
				if (!isset($fieldsArray[1])) {
					$strFields = "= '$fieldsArray[0]'";
				} else {
					$strFields = implode('\', \'', $fieldsArray);
					$strFields = "IN ('$strFields')";
				} 
									
				$req .= "SELECT column_name, data_type FROM information_schema.columns WHERE 
				table_schema='$schemaName' AND 
				table_name='$tableName' 
				AND column_name $strFields";
				
				$nbRes = count($fieldsArray);
			}	
			
			$result = $this->dbh->query($req);
			if ($this->db->isError($result)) {
				pm_logDebug(0, "ERROR - Easy_MDB2 (IsExists - \"$schema\".\"$table\") \n" .$result->getMessage());
			} else {
				if ($result->numRows() == $nbRes) {
					$ret = true;
				}
			}
			
			if ($ret && isset($fieldsArray) && $fieldsArray && isset($fieldsType) && $fieldsType) {
				while (($row = $result->fetchRow()) && $ret) {	
					$dataType = $row['data_type'];
					$dataName = $row['column_name'];
					
					if (!is_array($fieldsType)) {
						$type = $fieldsType;	
					} else {
						if (!isset($fieldsType[$dataName])) {
							continue;
						} else {
							$type = $fieldsType[$dataName];
						}	
					}
					
					switch ($type) {
						case 'N' :
							if ($dataType != 'smallint' &&
								$dataType != 'integer' &&
								$dataType != 'bigint' &&
								$dataType != 'decimal' &&
								$dataType != 'numeric' &&
								$dataType != 'real' &&
								$dataType != 'double precision' &&
								$dataType != 'serial' &&
								$dataType != 'bigserial') {
									$ret = false;
								}
							break;
							
						case 'S' :
							if ($dataType != 'character varying' &&
								$dataType != 'varchar' &&
								$dataType != 'character' &&
								$dataType != 'char' &&
								$dataType != 'text') {
									$ret = false;
								}
							break;
							
						default :
							if ($type != $dataType) {
								$ret = false;
							}
							break;
					}
				}	
			}
		}
		
		return $ret;
	}

}


?>