<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer php functions.
 *
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know as Yapeal.
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 * @subpackage Setup
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};

$ds = DIRECTORY_SEPARATOR;

//////////////////////////////////
// Functions
//////////////////////////////////

// Get language
function GetLang($lang){
  global $dir, $ds;
  $langStrings = array();
  $rp = realpath($dir . $ds . 'inc' . $ds . 'language' . $ds);
  require($rp . $ds . 'lang_en.php');
  if (file_exists($rp . $ds . 'lang_' . $lang . '.php')) {
    require($rp . $ds . 'lang_' . $lang . '.php');
  };
  foreach ($langStrings as $k=>$v) {
    define($k, $v);
  };
}

// Select browser language
function GetBrowserLang() {
  global $knownlang;
  $langs = array();
  if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
    if (count($lang_parse[1])) {
      // create a list like "en" => 0.8
      $langs = array_combine($lang_parse[1], $lang_parse[4]);
      // set default to 1 for any without q factor
      foreach ($langs as $lang => $val) {
        if ($val === '') $langs[$lang] = 1;
      }
      // sort list based on value	
      arsort($langs, SORT_NUMERIC);
    }; // if count $lang_parse[1]
  }; // if isset $_SERVER['HTTP_ACCEPT_LANGUAGE']
  foreach($knownlang as $Kval=>$Klang) {
    foreach ($langs as $lang => $val) {
  		if (strpos($lang, $Kval) === 0) {
        // show Danish site
        return $Kval;
      }; 
    };
  };
  return "en";
}

// Input standard Site header
function OpenSite($subtitle = "") {
  global $ini,$knownlang;
	if ($subtitle != "") {
    $subtitle = ' - '.$subtitle;
  };
  /*
   * Language selector
   */
  $count = 0;
  if (count($knownlang) > 1) {
    /*
     * Make url query
     */
    $getParse = '';
    foreach ($_GET as $getName=>$getValue) {
      if ($count==0) {
        $getParse .= '?'.$getName.'='.$getValue;
        $count++;
      } else {
        $getParse .= '&amp;'.$getName.'='.$getValue;
      }; // if ($count==0)
    }
    /*
     * Setup the language selector bar
     */
    $languageselector = '<form action="'.$_SERVER['SCRIPT_NAME'].$getParse.'" method="post">' . PHP_EOL
                       .'<select name="lang" onchange="submit();">' . PHP_EOL;
    foreach($knownlang as $langValue => $langName) {
      $languageselector .= '  <option value="'.$langValue.'"'; if ($_POST['lang'] == $langValue) { $languageselector .= ' selected="selected"'; } $languageselector .= '>'.$langName.'</option>' . PHP_EOL;
    }
    $languageselector .= '</select>' . PHP_EOL;
    /*
     * Make post query
     */
    $excp = array('lang');
    $languageselector .= inputHiddenPost($excp);
    $languageselector .='<input type="submit" value="'.CHOSELANGUAGE.'" />' . PHP_EOL
                       .'</form>' . PHP_EOL;
  } else {
    $languageselector = '';
  };
  /*
   * Set page title
   */
  if (isset($ini)) {
    $pagetitle = '<h1>'.CONFIG.'</h1>';
  } else {
    $pagetitle = '<h1>'.SETUP.'</h1>';
  }; // if isset $ini
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL
      .'<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL
      .'<head>' . PHP_EOL
      .'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL
      .'<title>Yapeal Setup' . $subtitle . '</title>' . PHP_EOL
      .'<link href="inc/style.css" rel="stylesheet" type="text/css" />' . PHP_EOL
      .'</head>' . PHP_EOL
      .'<body>' . PHP_EOL
      .$languageselector
      .'<div id="wrapper">' . PHP_EOL
      .'<img src="../pics/yapealblue.png" width="150" height="50" alt="Yapeal logo" /><br />' . PHP_EOL
      .$pagetitle . PHP_EOL;
}

// Input standard Site footer
function CloseSite() {
  echo '</div>' . PHP_EOL
      .'</body>' . PHP_EOL
      .'</html>' . PHP_EOL;
}

/*
 * Create <input> hidden post list array
 */
function configMenu() {
  echo '<table>' . PHP_EOL
      .'  <tr>' . PHP_EOL
      .'    <th>'.CONFIG_MENU.'</th>' . PHP_EOL
      .'  </tr>' . PHP_EOL
      .'  <tr>' . PHP_EOL
      .'    <td style="text-align: center;">' . PHP_EOL
      .'      <table class="notable">' . PHP_EOL
      .'        <tr>' . PHP_EOL
      .'          <td class="notd">' . PHP_EOL
      .'            <form action="' . $_SERVER['SCRIPT_NAME'] . '?funk=configini" method="post">' . PHP_EOL
      .'              <input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'              <input type="submit" value="'.INI_SETUP.'" />' . PHP_EOL
      .'            </form>' . PHP_EOL
      .'          </td><td class="notd">' . PHP_EOL
      .'            <form action="' . $_SERVER['SCRIPT_NAME'] . '?funk=configdb" method="post">' . PHP_EOL
      .'              <input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'              <input type="submit" value="'.DB_SETTING.'" />' . PHP_EOL
      .'            </form>' . PHP_EOL
      .'          </td><td class="notd">' . PHP_EOL
      .'            <form action="' . $_SERVER['SCRIPT_NAME'] . '?funk=configapi" method="post">' . PHP_EOL
      .'              <input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'              <input type="submit" value="'.TEST_CHAR.'" />' . PHP_EOL
      .'            </form>' . PHP_EOL
      .'          </td>' . PHP_EOL
      .'        </tr>' . PHP_EOL
      .'      </table>' . PHP_EOL
      .'    </td>' . PHP_EOL
      .'  </tr>' . PHP_EOL
      .'</table>' . PHP_EOL;
}

/*
 * Create <input> hidden post list array
 */
function inputHiddenPostArray($postValueArray, $Name) {
  $hiddenPost = '';
  foreach ($postValueArray as $postName=>$postValue) {
    /*
     * check if the value is a array
     */
    if (is_array($postValue)) {
      $hiddenPost .= inputHiddenPostArray($postValue, $Name.'['.$postName.']');
    } else {
      $hiddenPost .= '<input type="hidden" name="'.$Name.'['.$postName.']" value="'.$postValue.'" />' . PHP_EOL;
    }; // if is_array($postValue1)
  }
  return $hiddenPost;
}

/*
 * Create <input> hidden post list
 */
function inputHiddenPost($exceptions = '') {
  $hiddenPost = '';
  foreach ($_POST as $postName=>$postValue) {
    /*
     * Check for exceptions to leave out of the post list
     */
    if (is_array($exceptions) && in_array($postName,$exceptions)){
      continue;
    } elseif ($exceptions=='ALL_POST_CLEAN') {
      continue;
    };
    /*
     * check if the value is a array
     */
    if (is_array($postValue)) {
      $hiddenPost .= inputHiddenPostArray($postValue, $postName);
    } else {
      $hiddenPost .= '<input type="hidden" name="'.$postName.'" value="'.$postValue.'" />' . PHP_EOL;
    }; // if is_array($postValue)
  }
	return $hiddenPost;
}

/**
 * Function to build a multi-values insert ... on duplicate key update query
 * Example of how to use:
 * <code>
 * $data=array(
 * array('tableName'=>'eve-api-pull','ownerID'=>0,
 *   'cachedUntil'=>'2008-01-01 00:00:01'), ...
 * );
 * $types=array('tableName'=>'text','ownerID'=>'integer',
 *   'cachedUntil'=>'timestamp');
 * InsertUpdate($data,$types,'CacheUntil');
 * </code>
 *
 *
 * @param array $data Values to be put into query
 * @param array $types Keys are parameter names and values their types
 * @param string $table Table to use in query's from clause
 *
 * @return string Returns a complete SQL statement ready to be used by a
 * ADOdb exec
 *
 * @throws ADODB_Exception if connection used to do quoting fails.
 */
function BuildInsertUpdateQuery(array $data, array $types, $table) {
  global $config, $con;
  if (empty($data) || empty($types)) {
    return FALSE;
  };
  $pkeys = array_keys($types);
  $dkeys = array_keys($data[0]);
  $fields = array_intersect($pkeys, $dkeys);
  // Need this so we can do quoting.
  $needsQuote = array('C', 'X', 'D', 'T');
  // Build query sections
  $insert = 'INSERT INTO `' . $table . '` (`';
  $insert .= implode('`,`', $pkeys) . '`)';
  $values = ' VALUES';
  $sets = array();
  foreach ($data as $row) {
    $set = array();
    foreach ($fields as $field) {
      if (in_array($types[$field], $needsQuote)) {
        $set[] = $con->qstr($row[$field]);
      } else {
        $set[] = $row[$field];
      };// else in_array $params...
    };// foreach $fields ...
    $sets[] = '(' . implode(',', $set) . ')';
  };
  $values .= ' ' . implode(',', $sets);
  $dupup = ' ON DUPLICATE KEY UPDATE ';
  // Loop thru and build update section.
  $updates = array();
  foreach ($types as $k => $v) {
    $updates[] = '`' . $k . '`=VALUES(`' . $k . '`)';
  };
  $dupup .= implode(',', $updates);
  return $insert . $values . $dupup;
}// function makeMultiUpsert

/*
 * DB Handler
 */
function DBHandler($dbtype, $info = "", $data = "", $types = "") {
  global $con, $output, $stop, $ini, $dbconerror, $config;
  $select = false;
  $prefix = '';
	if (isset($ini['Database']['table_prefix'])) { $prefix = $ini['Database']['table_prefix']; }
	if (isset($config['DB_Prefix'])) { $prefix = $config['DB_Prefix']; }
	switch($dbtype) {
    case 'CON':
      // Check Connection
      $errorval = 1;
      $request_type = DATABASE.": ".CONNECT_TO;
      $okay = CONNECTED;
      if (isset($con)) {
        $select = $con->IsConnected();
      } else {
        $select = false;
      };
      if ($select) { 
        try {
          $con->Execute("SET NAMES utf8");
        } catch (ADODB_Exception $e) {
          trigger_error($e->getMessage(),E_USER_NOTICE);
        }
      } else {
        $errortext = $dbconerror;
      }; // if $select
		  break;
    case 'UPD':
      // Check Connection
      $errorval = 1;
      $request_type = DATABASE.": ".UPDATE;
      $okay = DONE;
      $info = $prefix.$info;
      if (isset($con)) {
        $select = BuildInsertUpdateQuery($data, $types, $info);
      } else {
        $select = false;
      };
      if ($select) { 
        try {
          $con->Execute($select);
        } catch (ADODB_Exception $e) {
          trigger_error($e->getMessage(),E_USER_NOTICE);
        }
      } else {
        $errortext = FAILED;
      }; // if $select
		  break;
    case 'CHKTAB':
      // Check If Table Exists
      $query = "SELECT count(*) FROM information_schema.tables
                WHERE table_schema = '".$ini['Database']['database']."'
                AND table_name = '".$prefix.$info."'";
      if (!isset($con)) { return false; }
      $rs = $con->GetOne($query);
      if ($rs>0) {
        return true;
      } else {
        return false;
      }; // if ($result[0]>0)
      break;
    default:
		  return false;
  }
  /*
   * Show action
   */
	if (!$select) {
    /**
     * Output error
     */
    $stop += $errorval;
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.$request_type.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$info.'</td>' . PHP_EOL;
    $output .= '    <td class="warning">'.$errortext.'</td>' . PHP_EOL;
    $output .= '  </tr>' . PHP_EOL;
    return false;
  } else {
    /**
     * Output success
     */
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.$request_type.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$info.'</td>' . PHP_EOL;
    $output .= '    <td class="good">'.$okay.'</td>' . PHP_EOL;
    $output .= '  </tr>' . PHP_EOL;
    return true;
  };
}

// Dir and File writ anabled checker
function WritChecker($path) {
  global $content2, $chmodcheck;
  if (is_file('..'. $ds .$path)) {
    $type = '('.TYPE_FILE.')';
    $cmod = TYPE_FILE_TO.' 666';
  } else {
    $type = '('.TYPE_DIR.')';
    $cmod = TYPE_DIR_TO.' 777';
  }
  $content2 .= '  <tr>' . PHP_EOL;
  $content2 .= '    <td width="220">'.$type.' '.$path.'</td>' . PHP_EOL;
  if (is_writable('..'. $ds .$path)) {
    $content2 .= '    <td class="good">'.YES.'</td>' . PHP_EOL;
  } else {
    $content2 .= '    <td class="warning">'.NO.' - Chmod '.$cmod.'</td>' . PHP_EOL;
    $chmodcheck++;
  };
  $content2 .= '  </tr>' . PHP_EOL;
}

/*
 * Get converted revision string to a integer value
 */
function conRev($rev) {
  $rev = preg_replace('/\$Revision: /', '', $rev);
  $rev = preg_replace('/ \$/', '', $rev);
  return (int)$rev;
}
/*
 * Get API info from cURL request
 */
function getApiInfo($ch) {
  $result = cURLRequest($ch);
  // Now check for errors.
  if ($result['curl_error']) {
    //echo $result['curl_error'];
	  return false;
  };
  if (200 != $result['http_code']) {
    //echo 'Bad http_code';
	  return false;
  };
  if (!$result['body']) {
    //echo 'No result';
	  return false;
  };
  if (!strpos($result['body'], '<eveapi version="')) {
    //echo 'Not eveapi<br />'.$result['body'];
	  return false;
  };
  return simplexml_load_string($result['body']);
}
/*
 * Do cURL Request
 */
function cURLRequest($ch) {
  $response = curl_exec($ch);
  $error = curl_error($ch);
  $result = array(
    'header' => '',
    'body' => '',
    'curl_error' => '',
    'http_code' => '',
    'last_url' => ''
  );
  if ($error != "") {
    $result['curl_error'] = $error;
    return $result;
  };
  $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
  $result['header'] = substr($response, 0, $header_size);
  $result['body'] = substr($response, $header_size);
  $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $result['last_url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
  return $result;
}
/*
 * Check if Browser is EVE IGB
 * @internal Return true or false.
 */
function checkIGB() {
  // Parse agent string by spliting on the '/'
  $parts = explode("/", @$_SERVER['HTTP_USER_AGENT']);
  // Test for Eve Minibrowser also test against broken Shiva IGB Agent
  if (($parts[0] == "EVE-minibrowser") or ($parts[0] == "Python-urllib")) {
    // IGB always sends this set to yes, or no,
    // so if it is missing, we smell something.
    if (!isset($_SERVER['HTTP_EVE_TRUSTED'])) {
      return false;
    };
    // return true at this point, User Agent matches,
    // and no phishy headers
    return true;
  } else {
    // User Agent, does not match required.
    return false;
  };
}
?>
