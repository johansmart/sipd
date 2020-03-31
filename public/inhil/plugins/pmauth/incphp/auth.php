<?php
/******************************************************************************
*
* Purpose: simple Authentication php class
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

class Auth {

  public $session = array();
  private $_session = array();
  public $server = array();
  public $post = array();
  private static $instance;
  public $tb = '';
  public $redirect = true;
  private $timeStamp;
  public $timeIdle = 0;
  public $authView = '';
  public $firstLogin = false;
	
	// Error control
	public $err;
	const AUTH_ERROR_NO_USERID = 0;
	const AUTH_ERROR_NO_PASS = 1;
	const AUTH_ERROR_NO_PASS_LEN = 2;


  private function __construct($db,$tb){
    // Take from pear Auth
    // Start the session suppress error if already started
    $cache_expire = session_cache_expire();
        if(!session_id()){
            @session_start();
            if(!session_id()) {
            Debug::setError('headers gia inviati');
            error_log('headers gia inviati');
            }
        }
    // get at start session http_request and server data
    $this->session =& $_SESSION;
    $this->server =& $_SERVER;
    $this->post =& $_POST;
    
    $this->db = $db;
    $this->tb = $tb;
    
    // building a under session section
    if(!isset($this->session['_auth_'])){ 
      $this->session['_auth_'] = array();
      $this->_session =& $this->session['_auth_'];
    }else{
      $this->_session =& $this->session['_auth_'];
    }
    // geting timestamp
    $this->timeStamp = time();
		
		// checkin for login error
		$this->getError();
    
  }
  
  /**
   * SingleTon method
   * @param PDO Object $db
   * @param String $tb
   * @return Self::Instance
   */
  public static function getInstance($db,$tb = 'pmauth_users'){
    if(self::$instance == null)
      {   
         $c = __CLASS__;
         self::$instance = new $c($db,$tb);
      }
      
      return self::$instance;
  }
  
  /**
   * Get Authentication
   * @return boolean
   */
  public function getAuth(){
     // checking authentication in session
     if($this->getAuthData()){
      // checking time
      if ($this->timeIdle != 0){
        $defTime = $this->timeStamp - $this->_session['timeStampOperation'];
        if ($defTime >= timeIdle){
          $this->showLogin();
          return false;
        }
      }
      // timestamp refresh
      $this->_session['timeStampOperation'] = $this->timeStamp;
      return true;
     }
     
     // check post data
     if ($this->post && $this->post['username'] !== "" && $this->post['password'] !== ""){
        // sanitize
        $this->post['password'] = trim($this->post['password']);
        $this->post['password'] = filter_var($this->post['password'],FILTER_SANITIZE_STRING);

        // check db
        if($this->checkAuth()){
            $this->_session['_data_']['username'] = $this->post['username'];
            $this->_session['_data_']['ipUser'] = $this->server['REMOTE_ADDR'];
            // retry the user data
            $res = $this->db->getData('pmauth_users',array('username'=>$this->post['username']));
            $this->_session['_data_']['id_user'] = $res[0]['id'];
            $this->_session['_data_']['id_role'] = $res[0]['id_role'];

            $res = $this->db->getData('pmauth_configs',array('id_users'=>$res[0]['id']));
            $this->_session['_data_']['configs'] = unserialize($res[0]['configs']);

            $this->getAuthData();
            // reset errors
            if($this->err){
              $this->resetError();
            }
            $this->firstLogin = true;
            return true;
        }else{
            $this->setError(self::AUTH_ERROR_NO_PASS);
        //				Log::write('AuthErr','Tentativo di log: User/Password sbagliata user:'.$this->post['username'].' from Ip: '.$this->server['REMOTE_ADDR']);
        }
     }
     
     // show login form if authentication failed
     $this->showLogin();
     
     return false;
  }
  
  private function checkAuth(){
    // switch db driver
    switch($this->db->driver){
        case 'sqlite':
            // retrive before the username data
            if($ud = $this->db->getData($this->tb,array("username" => $this->post['username']))){
                return md5($this->_session['prepass'].$ud[0]['password'].$this->_session['postpass']) == $this->post['password'] ? true:false;
            }
            return false;
        break;

        default:
            // build where array statment
            $wh = array(
                      "md5('".$this->_session['prepass']."'||password||'".$this->_session['postpass']."')" => $this->post['password'],
                      "username" => $this->post['username']
                  );
            return $this->db->checkData($this->tb,$wh);
    }
    
  }
	
  
  /**
   * Set local sttributes from session
   * @return boolean
   */
  private function getAuthData(){
    if (isset($this->_session['_data_'])){
      // si prendono i dati di autenticazione
      foreach($this->_session['_data_'] as $key => $val){
        $this->$key = $val;
      }
      return true;
    }else{
      return false;
    }
  }
	
	/**
	 * Set error in session
	 * @param string $err
	 */
	private function setError($err){
		// si fissa dentro la sessione
		$this->_session['_error_'] = $err;
		$this->err = true;
	}

    /**
     *Get error
     */
	public function getError(){
		if(isset($this->_session['_error_'])){
			$this->err = true;
			return $this->_session['_error_'];
		}
	}

    /**
     * Reset error in session
     */
	private function resetError(){
		if(isset($this->_session['_error_'])){
			unset($this->_session['_error_']);
		}
	}
	
	/**
	 * Redirect to request url
	 * @return 
	 */
	private function redirect(){
		header('Location: '.$this->authView);
	}

  /**
   * Show Login form
   */
  private function showLogin(){
   // prima si salva nella sessione la url richiesta
   $this->_session['uriRequest'] = $this->formTarget;
   $this->redirect();
  }

  /**
   * Logout authentication
   */
  public function logOut(){
    // si eliminano i dati di sessione
    $this->authView = $this->formTarget;
    session_destroy();
    $this->redirect();
  }
}
