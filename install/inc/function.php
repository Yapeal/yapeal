<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal installer php functions.
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
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
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
// Input standard Site header
function OpenSite($subtitle = "") {
  global $ini,$knownlang;
	if ($subtitle != "") {
    $subtitle = ' - '.$subtitle;
  };
  /*
   * Set page title
   */
  if (isset($ini)) {
    $pagetitle = '<h1>Config</h1>';
  } else {
    $pagetitle = '<h1>Setup</h1>';
  }; // if isset $ini
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL
      .'<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL
      .'<head>' . PHP_EOL
      .'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL
      .'<title>Yapeal Setup' . $subtitle . '</title>' . PHP_EOL
      .'<link href="inc/style.css" rel="stylesheet" type="text/css" />' . PHP_EOL
      .'</head>' . PHP_EOL
      .'<body>' . PHP_EOL
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
  echo '<table summary="">' . PHP_EOL
      .'  <tr>' . PHP_EOL
      .'    <th>Config Menu</th>' . PHP_EOL
      .'  </tr>' . PHP_EOL
      .'  <tr>' . PHP_EOL
      .'    <td style="text-align: center;">' . PHP_EOL
      .'      <table summary="" class="notable">' . PHP_EOL
      .'        <tr>' . PHP_EOL
      .'          <td class="notd">' . PHP_EOL
      .'            <form action="' . $_SERVER['SCRIPT_NAME'] . '?funk=configini" method="post">' . PHP_EOL
      .'              <input type="submit" value="Yapeal Setup" />' . PHP_EOL
      .'            </form>' . PHP_EOL
      .'          </td><td class="notd">' . PHP_EOL
      .'            <form action="' . $_SERVER['SCRIPT_NAME'] . '?funk=configdb" method="post">' . PHP_EOL
      .'              <input type="submit" value="Database Settings" />' . PHP_EOL
      .'            </form>' . PHP_EOL
      .'          </td><td class="notd">' . PHP_EOL
      .'            <form action="' . $_SERVER['SCRIPT_NAME'] . '?funk=configapi" method="post">' . PHP_EOL
      .'              <input type="submit" value="Test Character" />' . PHP_EOL
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
 * BuildInsertUpdateQuery($data,$types,'CacheUntil');
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
      $request_type = "Database: Connecting To";
      $okay = 'Connected';
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
      $request_type = "Database: Update";
      $okay = 'Done';
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
        $errortext = 'Failed';
      }; // if $select
		  break;
    case 'UPDUS':
      // Update utilSection isActive
      $errorval = 1;
      $request_type = "Database: Update";
      $okay = 'Done';
      $info = $prefix.$info;
      if (isset($con)) {
        $select = "UPDATE ".$info." SET `isActive` = ".$data." WHERE `sectionName` = '".$types."'";
      } else {
        $select = false;
      };
      if ($select) {
        try {
          $con->Execute($select);
					$info = $info . ' -> ' . $types;
        } catch (ADODB_Exception $e) {
          trigger_error($e->getMessage(),E_USER_NOTICE);
					$info = $info . ' -> ' . $types;
          $errortext = $e->getMessage();
        }
      } else {
        $errortext = 'Failed';
      }; // if $select
		  break;
    case 'USACTIVE':
      $info = $prefix.$info;
      if (isset($con)) {
        $select = "SELECT `isActive` FROM ".$info." WHERE `sectionName` = '".$types."'";
      } else {
        $select = false;
      };
      try {
        $rs = $con->GetOne($select);
        return $rs;
      } catch (ADODB_Exception $e) {
        trigger_error($e->getMessage(),E_USER_NOTICE);
      }
			return 0;
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
  if (is_file(YAPEAL_BASE . $path)) {
    $type = '(File)';
    $cmod = 'file to 666';
  } else {
    $type = '(Dir)';
    $cmod = 'dir to 777';
  }
  $content2 .= '  <tr>' . PHP_EOL;
  $content2 .= '    <td width="220">'.$type.' '.$path.'</td>' . PHP_EOL;
  if (is_writable(YAPEAL_BASE . $path)) {
    $content2 .= '    <td class="good">Yes</td>' . PHP_EOL;
  } else {
    $content2 .= '    <td class="warning">No - Chmod '.$cmod.'</td>' . PHP_EOL;
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
/*
 * Convert to bool value
 * @internal Return 1 or 0.
 */
function convertToBool($value) {
  if ($value == 1) {
    return 1;
  } else {
    return 0;
  }
}
?>
