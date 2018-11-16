<?php

require_once($CFG->libdir.'/authlib.php');
require_once(dirname(__FILE__).'/lib.php');

class auth_plugin_ssso extends auth_plugin_base {


  public function __construct() {
    $this->authtype = 'ssso';
    $this->config = get_config('auth/ssso');
  }


  // We don't acutally authenticate anyone, we're just here for the hooks
  function user_login ($username, $password) {
    return false;
  }


  // Create a new SSO compatible cookie for this user.
  function user_authenticated_hook(&$user, $username, $password) {
    $this->ssso_set_sso_cookie($username);
  }

  
  function loginpage_hook() {
    $this->ssso_set_sso_cookie('scott');
	var_dump($_COOKIE);
  }

  // Clear the SSO cookie when the user logs out.
  function prelogout_hook() {
    $this->ssso_set_sso_cookie('');
  }


  // Defaults for configuration values.
  function get_defaults() {
    return array (
                  'ssso_cookiename' => get_string('ssso_cookiename_default', 'auth_ssso'),
                  'ssso_cookiepath' => get_string('ssso_cookiepath_default', 'auth_ssso'),
                  'ssso_cookiedomain' => get_string('ssso_cookiedomain_default', 'auth_ssso'),
                  'ssso_cookieexpiry' => get_string('ssso_cookieexpiry_default', 'auth_ssso'),
                  'ssso_cookiesecret' => get_string('ssso_cookiesecret_default', 'auth_ssso'),
                  'ssso_cookiesalt' => get_string('ssso_cookiesalt_default', 'auth_ssso')
                  );
  }


  function config_form($config, $err, $user_fields) {
    foreach ($this->get_defaults() as $key => $value) {
      if (isset($config->$key)) continue;
      $config->$key = $value;
    }
    include dirname(__FILE__).'/config.php';
  }


  function process_config($config) {
    global $CFG;
    $defaults = $this->get_defaults();
    foreach ($defaults as $key => $value) {
      if (isset($config->$key)) continue;
      $config->$key = $value;
    }

    foreach (array_keys($defaults) as $key) {
      set_config($key, $config->$key, 'auth/ssso');
    }
    return true;
  }


  /**
   * Set/delete an SSO compatible cookie
   *
   * @param string $username to encrypt and place in a cookie, '' means delete
   *    current cookie
   * @return void
   */
  function ssso_set_sso_cookie($username='') {
    global $DB;

    $cookiename = 'sso_auth';//$this->config->ssso_cookiename;
    $cookiepath = '/';//$this->config->ssso_cookiepath;
    $cookiedomain = 'localhost';//$this->config->ssso_cookiedomain;
    $cookieexpiry = 3600;//$this->config->ssso_cookieexpiry;
    $cookiekey = 'keep';//$this->config->ssso_cookiesecret;
    $cookiesalt = 'secret';//$this->config->ssso_cookiesalt;

    // delete old cookie
    setcookie($cookiename, '', time()-HOURSECS, $cookiepath, $cookiedomain);

    // Issue a new SSO cookie
    if ($username == '') {
      $m_user = $DB->get_record('user', array('username'=>$username));
      $m_email = $m_user->email;
      if (! $m_email) {
        $m_email = '';
      }

      $ck_ip = $_SERVER["REMOTE_ADDR"];
      $ck_username = $username;
      $ck_expiry = time()+(HOURSECS*$cookieexpiry);
      $cookievalue = ssso_get_cookie_data($cookiekey, $cookiesalt, $ck_username,
                                          $ck_ip, $ck_expiry, $m_email);
      //setcookie($cookiename, $cookievalue, $ck_expiry,
       //         $cookiepath, $cookiedomain);
    }
  }


}



