/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer Javascript code.
 *
 *
 * Javascript
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
 */

// JavaScript Document
// Basic variables
var DB_connect_error = 0;
var API_Val_error = 0;
var API_CharSel_error = 1;
/*
* Fixes a Internet Explore bug
*/
if (/msie/i.test (navigator.userAgent)) { //only override IE
  document.nativeGetElementById = document.getElementById;
  document.getElementById = function(id) {
    var elem = document.nativeGetElementById(id);
    if(elem) {
      //make sure that it is a valid match on id
      if(elem.attributes['id'].value == id) {
        return elem;
      } else {
        //otherwise find the correct element
        for(var i=1;i<document.all[id].length;i++) {
          if(document.all[id][i].attributes['id'].value == id) {
            return document.all[id][i];
          };
        };
      };
    };
    return null;
  };
}
/*
*
*/
function Go_Back() {
  document.getElementById('Go_Back').innerHTML = '<a href="javascript:history.go(-1)">Go Back</a>';
}
/*
* Get characters
*/
function api_get_chars() {
  var api_user_id = document.getElementById("api_user_id").value;
  var api_limit_key = document.getElementById("api_limit_key").value;
  var api_full_key = document.getElementById("api_full_key").value;
  // Validation and if valid then pull the character list
  if (api_user_id != "") {
    if (!isNaN(api_user_id)) {
      var valid_Limit_Key = true;
      var valid_Full_Key = true;
      // Validate limit API Key
      if (api_limit_key != "") {
        if (/[^a-zA-Z0-9]/.test(api_limit_key)) {
          valid_Limit_Key = false;
        };
        if (api_limit_key.length != 64) {
          valid_Limit_Key = false;
        };
      };
      // Validate full API Key
      if (api_full_key != "") {
        if (/[^a-zA-Z0-9]/.test(api_full_key)) {
          valid_Full_Key = false;
        };
        if (api_full_key.length != 64) {
          valid_Full_Key = false;
        };
      };
      // if API keys is not valid, tell the user
      if (valid_Limit_Key == false || valid_Full_Key == false) {
        if  (valid_Limit_Key == false && valid_Full_Key == true) {
          alert(lang.Limit_API_Not_Vanid);
        } else if (valid_Limit_Key == true && valid_Full_Key == false) {
          alert(lang.Full_API_Not_Vanid);
        } else {
          alert(lang.Limit_and_Full_API_Not_Vanid);
        };
        document.getElementById("api_char_select").innerHTML = "<font class=\"good\">" + lang.Will_load_on_API + "<input type=\"hidden\" name=\"config[api_char_info]\" value=\"\">";
        API_Val_error = 1;
        check_submit();
      }
      // All is okay. now get the character list
      else if ((api_full_key != "" || api_limit_key != "") && valid_Limit_Key == true && valid_Full_Key == true) {
        if (api_full_key != "") {
          var API_Key = api_full_key;
        } else {
          var API_Key = api_limit_key;
        };
        API_Val_error = 0;
        check_submit();
        SendCharListRequest(api_user_id,API_Key);
      } else {
        document.getElementById("api_char_select").innerHTML = lang.Will_load_on_API + '<input type="hidden" name="config[api_char_info]" value="" />';
        document.getElementById("api_char_select").className = 'good';
        API_Val_error = 1;
        check_submit();
      };
    } else {
      document.getElementById("api_char_select").innerHTML = lang.Will_load_on_API + '<input type="hidden" name="config[api_char_info]" value="" />';
      document.getElementById("api_char_select").className = 'good';
      alert(lang.API_User_ID_NAN);
    };
  } else {
    document.getElementById("api_char_select").innerHTML = lang.Will_load_on_API + '<input type="hidden" name="config[api_char_info]" value="" />';
    document.getElementById("api_char_select").className = 'good';
  };
}

function api_get_chars2(charselect) {
  var api_user_id = document.getElementById("api_user_id").value;
  var api_limit_key = document.getElementById("api_limit_key").value;
  var api_full_key = document.getElementById("api_full_key").value;
  // Validation and if valid then pull the character list
  if (api_user_id != "") {
    if (!isNaN(api_user_id)) {
      var valid_Limit_Key = true;
      var valid_Full_Key = true;
      // Validate limit API Key
      if (api_limit_key != "") {
        if (/[^a-zA-Z0-9]/.test(api_limit_key)) {
          valid_Limit_Key = false;
        };
        if (api_limit_key.length != 64) {
          valid_Limit_Key = false;
        };
      };
      // Validate full API Key
      if (api_full_key != "") {
        if (/[^a-zA-Z0-9]/.test(api_full_key)) {
          valid_Full_Key = false;
        };
        if (api_full_key.length != 64) {
          valid_Full_Key = false;
        };
      };
      // if API keys is not valid, tell the user
      if (valid_Limit_Key == false || valid_Full_Key == false) {
        if  (valid_Limit_Key == false && valid_Full_Key == true) {
          alert(lang.Limit_API_Not_Vanid);
        } else if (valid_Limit_Key == true && valid_Full_Key == false) {
          alert(lang.Full_API_Not_Vanid);
        } else {
          alert(lang.Limit_and_Full_API_Not_Vanid);
        };
        document.getElementById("api_char_select").innerHTML = "<font class=\"good\">" + lang.Will_load_on_API + "<input type=\"hidden\" name=\"config[api_char_info]\" value=\"\">";
        API_Val_error = 1;
        check_submit2();
      }
      // All is okay. now get the character list
      else if ((api_full_key != "" || api_limit_key != "") && valid_Limit_Key == true && valid_Full_Key == true) {
        if (api_full_key != "") {
          var API_Key = api_full_key;
        } else {
          var API_Key = api_limit_key;
        };
        API_Val_error = 0;
        check_submit();
        SendCharListRequest(api_user_id,API_Key,charselect,true);
      } else {
        document.getElementById("api_char_select").innerHTML = lang.Will_load_on_API + '<input type="hidden" name="config[api_char_info]" value="" />';
        document.getElementById("api_char_select").className = 'good';
        API_Val_error = 1;
        check_submit2();
      };
    } else {
      document.getElementById("api_char_select").innerHTML = lang.Will_load_on_API + '<input type="hidden" name="config[api_char_info]" value="" />';
      document.getElementById("api_char_select").className = 'good';
      alert(lang.API_User_ID_NAN);
    };
  } else {
    document.getElementById("api_char_select").innerHTML = lang.Will_load_on_API + '<input type="hidden" name="config[api_char_info]" value="" />';
    document.getElementById("api_char_select").className = 'good';
  };
}

// On character select change check if
function Select_Character() {
  var selected_char = document.getElementById("api_char_choise").value;
  if (selected_char != "") {
    API_CharSel_error = 0;
    check_submit();
  } else {
    API_CharSel_error = 1;
    check_submit();
  };
}

// On character select change check if
function Select_Character2() {
  var selected_char = document.getElementById("api_char_choise").value;
  if (selected_char != "") {
    API_CharSel_error = 0;
    check_submit2();
  } else {
    API_CharSel_error = 1;
    check_submit2();
  };
}

// Function to check if you are ready to run the install.
function check_submit() {
  var checker = 0;
  checker += DB_connect_error;
  checker += API_Val_error;
  checker += API_CharSel_error;
  if (checker > 0) {
    document.getElementById("submit_select").innerHTML = '<input type="button" value="' + lang.Run_Setup + '" disabled="disabled" />';
  } else {
    document.getElementById("submit_select").innerHTML = '<input type="submit" value="' + lang.Run_Setup + '" />';
  };
}

// Function to check if you are ready to run the install.
function check_submit2() {
  var checker = 0;
  checker += DB_connect_error;
  checker += API_Val_error;
  checker += API_CharSel_error;
  if (checker > 0) {
    document.getElementById("submit_select").innerHTML = '<input type="button" value="' + lang.Update + '" disabled="disabled" />';
  } else {
    document.getElementById("submit_select").innerHTML = '<input type="submit" value="' + lang.Update + '" />';
  };
}

// Function to load JavaScript elements in the API Setup
function load_Install_Setup_JavaScript() {
  // Change the form action path
  document.go_no_go.setAttribute("action", window.location.pathname + "?lang=" + lang.lang + "&install=go");

  // Disable the submit button
  document.getElementById("submit_select").innerHTML = '<input type="button" value="' + lang.Run_Setup + '" disabled="disabled" />';

  // Create the character select table with default text.
  var table = document.getElementById('api_setup_table');

  var tr    = document.createElement('TR');
  var td1   = document.createElement('TD');
  var td2   = document.createElement('TD');

  td1.className = 'tableinfolbl';
  td1.innerHTML = lang.Character + ':';
  td2.setAttribute("id", "api_char_select");
  td2.className = 'good';
  td2.innerHTML = lang.Will_load_on_API + '<input type="hidden" name="api_char_info" value="" />';

  table.appendChild(tr);
  tr.appendChild(td1);
  tr.appendChild(td2);
}

// Function to load JavaScript elements in the API Setup
function load_Edit_Setup_JavaScript(charselect) {
  // Change the form action path
  document.go_no_go.setAttribute("action", window.location.pathname + "?lang=" + lang.lang + "&edit=go");

  // Disable the submit button
  document.getElementById("submit_select").innerHTML = '<input type="button" value="' + lang.Update + '" disabled="disabled" />';

  // Create the character select table with default text.
  var table = document.getElementById('api_setup_table');

  var tr    = document.createElement('TR');
  var td1   = document.createElement('TD');
  var td2   = document.createElement('TD');

  td1.className = 'tableinfolbl';
  td1.innerHTML = lang.Character + ':';
  td2.setAttribute("id", "api_char_select");
  td2.className = 'good';
  td2.innerHTML = lang.Will_load_on_API + '<input type="hidden" name="api_char_info" value="" />';

  table.appendChild(tr);
  tr.appendChild(td1);
  tr.appendChild(td2);
  api_get_chars2(charselect);
}

// Function to load JavaScript elements in the API Setup
function load_Convert_Setup_JavaScript() {
  // Change the form action path
  document.go_no_go.setAttribute("action", window.location.pathname + "?lang=" + lang.lang + "&convert=go");

  // Disable the submit button
  document.getElementById("submit_select").innerHTML = '<input type="button" value="' + lang.Run_Setup + '" disabled="disabled" />';

  // Create the character select table with default text.
  var table = document.getElementById('api_setup_table');

  var tr    = document.createElement('TR');
  var td1   = document.createElement('TD');
  var td2   = document.createElement('TD');

  td1.className = 'tableinfolbl';
  td1.innerHTML = lang.Character + ':';
  td2.setAttribute("id", "api_char_select");
  td2.className = 'good';
  td2.innerHTML = lang.Will_load_on_API + '<input type="hidden" name="api_char_info" value="" />';

  table.appendChild(tr);
  tr.appendChild(td1);
  tr.appendChild(td2);
}


var clientHttpHandler;
clientHttpHandler = create();

/*
* This method creates the xmlHttpRequest object and returns it.
*/
function create() {
  var xmlHttpRequest = false;
  //Internet Explorer
  try {
    xmlHttpRequest = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (xml2Exception) {
    try {
      xmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (xmlException) {
      xmlHttpRequest = false;
    }
  }
  //Netscape, Mozila, Firefox, Safari, Opera
  if (!xmlHttpRequest && typeof XMLHTTPRequest == 'undefined') {
    try {
      xmlHttpRequest = new XMLHttpRequest();
    } catch (genException) {
      XMLHttpRequest = false;
    }
  };
  return xmlHttpRequest;
}

/*
* This method checks the state and the status of the response and
* Depending on that fetches the response text.
* readystate: 0 - uninitialized, 1 - loading, 2 - loaded, 3 - interactive, 4 - complete
*/
function receive() {
  //var response = "";
  try {
    if (clientHttpHandler.readyState == 1) {
      document.getElementById("api_char_select").innerHTML = lang.Loading;
      document.getElementById("api_char_select").className = 'notis';
    } else if (clientHttpHandler.readyState == 2) {
      document.getElementById("api_char_select").innerHTML = lang.Loaded;
      document.getElementById("api_char_select").className = 'notis';
    } else if (clientHttpHandler.readyState == 3) {
      document.getElementById("api_char_select").innerHTML = lang.Interactive;
      document.getElementById("api_char_select").className = 'notis';
    } else if (clientHttpHandler.readyState == 4) { // Completed
      if (clientHttpHandler.status == 200) { // "OK"
        return true;
      } else if (clientHttpHandler.status == 403) { // "Forbidden"
        alert(lang.Forbidden);
      } else if (clientHttpHandler.status == 404) { // "URL Not Found"
        alert(lang.URL_Not_Found);
      } else { // Miscellaneous
        //alert(lang.Error_Status_Code + " " + clientHttpHandler.status);
      };
    };
  } catch (genException) { }
  return false;
}

function SendCharListRequest(ID,Key,CharSelect,Update){
  /*
  We will append a random number each time we need to
  Send the request. So that browsers does not cache the request
  And every time a fresh page will be executed.
  */
  var rand = Math.floor(Math.random()*1000001);
  // The URL where our background page resides
  sURL = "backend/api.php"; // I have used a relative path
  /*
  I have here used the GET method to send data to the background
  Page so builds the query string here.
  */
  if (Update == true) {
    queryString = "?rand=" + rand + "&lang=" + lang.lang + "&id=" + ID + "&key=" + Key + "&char=" + CharSelect;
  } else {
    queryString = "?rand=" + rand + "&lang=" + lang.lang + "&id=" + ID + "&key=" + Key;
  }
  // Append the query string at the end of the URL
  sURL += "?" + queryString;
  // If this request is a synchronous or not.
  // If we use multiple synchronous requests at a time then only the
  // Last request will be executed.
  isSync = true;
  // Now send the request.
  clientHttpHandler.open("GET", sURL, isSync);
  clientHttpHandler.onreadystatechange = function() {
    if (receive()) {
      response = clientHttpHandler.responseText;
      if(response == "ERROR"){
        API_CharSel_error = 1;
        if (Update == true) {
          check_submit2();
        } else {
          check_submit();
        };
      } else {
        document.getElementById("api_char_select").innerHTML = response;
        document.getElementById("api_char_select").className = 'good';
        if (Update == true) {
          Select_Character2();
        };
      };
    };
  };
  clientHttpHandler.send(null);
}
