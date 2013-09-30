<?php
  /**
   *               %%%copyright%%%
   *
   * FusionTicket - ticket reservation system
   *  Copyright (C) 2007-2013 FusionTicket Solution Limited . All rights reserved.
   *
   * Original Design:
   *	phpMyTicket - ticket reservation system
   * 	Copyright (C) 2004-2005 Anna Putrino, Stanislav Chachkov. All rights reserved.
   *
   * This file is part of FusionTicket.
   *
   * This file may be distributed and/or modified under the terms of the
   * "GNU General Public License" version 3 as published by the Free
   * Software Foundation and appearing in the file LICENSE included in
   * the packaging of this file.
   *
   * This file is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING
   * THE WARRANTY OF DESIGN, MERCHANTABILITY AND FITNESS FOR A PARTICULAR
   * PURPOSE.
   *
   * Any links or references to Fusion Ticket must be left in under our licensing agreement.
   *
   * By USING this file you are agreeing to the above terms of use. REMOVING this licence does NOT
   * remove your obligation to the terms of use.
   *
   * The "GNU General Public License" (GPL) is available at
   * http://www.gnu.org/copyleft/gpl.html.
   *
   * Contact help@fusionticket.com if any conditions of this licencing isn't
   * clear to you.
   */

/**
 *
 *
 * @version 0.0.7 - beta version
 * @copyright Lxsparks, 2013
 * Please read the readme file in the plugins folder
 *
 */

class plugin_AdvancedSearch extends baseplugin {

	public $plugin_info		  = 'Advanced Search/Edit/Delete Customer details';
	/**
	 * description - A full description of your plugin.
	 */
	public $plugin_description	= 'This plugin extends the Search pages and allows customer\'s details to be edited or deleted';
	/**
	 * version - Your plugin's version string. Required value.
	 */
	public $plugin_myversion		= '0.0.7';
	/**
	 * requires - An array of key/value pairs of basename/version plugin dependencies.
	 * Prefixing a version with '<' will allow your plugin to specify a maximum version (non-inclusive) for a dependency.
	 */
	public $plugin_requires	= null;
	/**
	 * author - Your name, or an array of names.
	 */
	public $plugin_author		= 'Lxsparks';
	/**
	 * contact - An email address where you can be contacted.
	 */
	public $plugin_email		= 'lxsparks@hotmail.com';
	/**
	 * url - A web address for your plugin.
	 */
	public $plugin_url			= 'http://www.fusionticket.org';

  public $plugin_actions  = array ('install','uninstall');
  protected $directpdf = null;


  function getTables(& $tbls){
      /*
         Use this section to add new database tables/fields needed for this plug-in.
      */
   }

	function showFormOptions($array, $active, $echo=true){
		$string = '';

		foreach($array as $k => $v){
			$s = ($active == $k)? ' selected="selected"' : '';
			$string .= '<option value="'.$k.'"'.$s.'>'.$v.'</option>'."\n";
		}

		if($echo) {
			echo $string;
        } else {
			return $string;
		}
	}

	function print_status ($user_status){  //An extended version of the function in view.search
		if($user_status=='1'){
		  return con('sale_point');
		}else if ($user_status=='2'){
		  return con('member');
		}else if($user_status=='3'){
		  return con('guest');
		}else if($user_status=='4'){
		  return con('pos');
		}
	}

	function print_events(){ // Based on the version in view.adminusers.php

		$query="SELECT event_id,event_name,event_date,event_time
				FROM Event
				WHERE event_pm_id is not null
					AND event_rep LIKE '%sub%'
					AND event_status <> 'unpub'
					AND event_date >= date(now())
				ORDER BY event_date,event_time";
		if(!$res=ShopDB::query($query)){
		  user_error(shopDB::error());
		  return;
		}

		echo "<select size='10' name='event_ids' style='width: 500px;'>";
		while($row=shopDB::fetch_assoc($res)){
		  $sel=(in_array($row["event_id"], $event))?"selected":"";
		  $date=formatDate($row["event_date"], "%d-%b-%y");
		  $time=formatTime($row["event_time"]);
		  echo "<option value='".$row["event_id"]."|".$row["event_name"]."' $sel>$date $time : ". $row["event_name"]."</option>";
		}
		echo "</select>";
	}

	function page_links ($currentpage, $range, $totalpages, $limit){
	/******  build the pagination links ******/

		$pageLinks='';
						// range of num links to show
						$range = 3;

						// if not on page 1, don't show back links
						if ($currentpage > 1) {
						   // show << link to go back to page 1
						   $pageLinks= " <a href='{$_SERVER['PHP_SELF']}?currentpage=1&submit=1&npp={$limit}'><<</a> ";
						   // get previous page num
						   $prevpage = $currentpage - 1;
						   // show < link to go back to 1 page
						   $pageLinks.= " <a href='{$_SERVER['PHP_SELF']}?currentpage=$prevpage&submit=1&npp={$limit}'><</a> ";
						} // end if

						// loop to show links to range of pages around current page
						for ($x = ($currentpage - $range); $x < (($currentpage + $range) + 1); $x++) {
						   // if it's a valid page number...
						   if (($x > 0) && ($x <= $totalpages)) {
							  // if we're on current page...
							  if ($x == $currentpage) {
								 // 'highlight' it but don't make a link
								 $pageLinks.=  " [<b>$x</b>] ";
							  // if not current page...
							  } else {
								 // make it a link
								 $pageLinks.=  " <a href='{$_SERVER['PHP_SELF']}?currentpage=$x&submit=1&npp={$limit}'>$x</a> ";
							  } // end else
						   } // end if
						} // end for

						// if not on last page, show forward and last page links
						if ($currentpage != $totalpages) {
						   // get next page
						   $nextpage = $currentpage + 1;
							// echo forward link for next page
						   $pageLinks.=  " <a href='{$_SERVER['PHP_SELF']}?currentpage=$nextpage&submit=1&npp={$limit}'>></a> ";
						   // echo forward link for lastpage
						   $pageLinks.=  " <a href='{$_SERVER['PHP_SELF']}?currentpage=$totalpages&submit=1&npp={$limit}'>>></a> ";
						} // end if


				/****** end build pagination links ******/
		return($pageLinks);
	}

	function search_pattern ($str, $type, $length) {

		$result = "";
		/**
		* Sanitize data, comming from the forms.
		*
		* @param $data mixed Anykind of data, posted from a particualr form field.
		* @param $type string data type name - "string", "integer", etc...
		* @param $allowed_tags string List of allowed HTML tags, if any. Example: "<p><b><font>"
		* function sanitize($data, $type, $allowed_tags)
		*/

		if($type == "string"){
		$result = (string) trim(strip_tags($str));
		}elseif($type == "int"){
		$result = (int) trim(strip_tags($str));
		}

		$result = preg_replace('/[^A-Za-z0-9\-\?]/', '', $result); // Ensure we only have letters or hyphens and our question mark/s
		$result = substr($result,0,$length); // Make sure it is ($length) characters or less

		$result = str_replace('?', '%', $result);
		echo "<br />".con('fnc_search_term').": <em>".$result."</em><br />";

		return ($result);
	}

	function clean_customerData($str, $len) { // Clean up the data to match specific patterns

		$str = (strlen($str > $len)) ?  substr($str, 0, $len) : $str;
		if (stristr($str, '-') === FALSE) { // Is the name hypenated? No >

		$str = trim(ucwords(strtolower($str)));

		} else { $str=explode('-', $str);  // Is the name hypenated? Yes >

			foreach ($str as $key=>$data) {

				$data = trim(ucwords(strtolower($data)));
				$str2[$key] = $data;
				}
			$str = implode("-", $str2);
			$ucList=array('On', 'In', 'Under','Upon','Cum'); // Place connector names that need to be back to lowercase
			$lcList=array('on', 'in', 'under','upon','cum');
			$str=str_replace($ucList, $lcList, $str);
		}

		$str=preg_replace ('/\s\s+/', ' ', $str); // Reduce extra whitespaces

		// Not perfect - Macintosh for example will be MacIntosh...
		$str=preg_replace(
			'/
			(?: ^ | \\b )         # assertion: beginning of string or a word boundary
			( O\' | Ma?c | M?c |Fitz)  # attempt to match Irish surnames
			( [^\W\d_] )          # match next char; we exclude digits and _ from \w
			/xe',
			"'\$1' . strtoupper('\$2')",
			$str);
			return ($str);
		}

	function clean_customerZip($str, $ctry) { // Clean up Postcodes to match specific Country patterns

			$str = trim(strtoupper($str));
			$str=preg_replace ('/\s+/', '', $str); // Remove all whitespaces

			if ($ctry == 'GB') {  // UK Post Code

				$s1 = substr($str, 0, -3);
				$s2 = substr($str, -3);
				$str = $s1.' '.$s2;

			} elseif ($ctry == 'CA' ) {  // Canadia Post code

				$s1 = substr($str, 0, 3);
				$s2 = substr($str, 3, 3);
				$str = $s1.' '.$s2;

			} elseif ($ctry == 'US' || is_numeric($str)) {  // US Post code

				$str = substr($str, 0, 5);

			} else {  // Default setting

				$str = $str;
			}

			return ($str);
		}

	function clean_customerTel($str, $ctry) { // Clean up Telephone Numbers to match specific Country patterns

			$str = trim($str);
			$str=preg_replace ('/\s+/', '', $str); // Remove all whitespaces

			if ($ctry == 'GB') {  // UK Phone Numbers

				$std3 = array('028','029','023','024'); //3 digit STD
				$std4 = array('0117','0113','0114','0115','0116'); // 4 digit STD
				$stdLon = array('0207','0208'); // London STD
				$i = 0;

				if ($i == 0) {
					foreach ($std3 as $std) {
						if (strpos($str, $std) === 0) {
							$s1 = $std;
							$s2 = substr($str, 3, 4);
							$s3 = substr($str, 7, 4);
							$str = $s1.' '.$s2.' '.$s3;
							$i =1;
						}
					}
				}

				if ($i == 0) {
					foreach ($std4 as $std) {
						if (strpos($str, $std) === 0) {
							$s1 = $std;
							$s2 = substr($str, 4, 3);
							$s3 = substr($str, 7, 4);
							$str = $s1.' '.$s2.' '.$s3;
							$i = 1;
						}
					}
				}

				if ($i == 0) {
					foreach ($stdLon as $std) {
						if (strpos($str, $std) === 0) {
							$s1 = substr($std, 0, 3);
							$s2 = substr($str, 3, 4);
							$s3 = substr($str, 7, 4);
							$str = $s1.' '.$s2.' '.$s3;
							$i = 1;
						}
					}
				}

				if ($i == 0) {  // if number doesn't match any of the above: default UK format

					$s1 = substr($str, 0, 5);
					$s2 = substr($str, 5, 6);
					$str = $s1.' '.$s2;
				}

			} elseif ($ctry == 'CA'  || 'US') { // Format: [1] xxx xxx xxxx

				$num_len = strlen($str);
				$s1 ='';
				if ($num_len == 11 && $str[0] == '1') {  // Is it 11 numbers starting with 1?
				$s1 = '1 ';
				$str = preg_replace('/^1/', '', $str);
				}
				$s2 = substr($str, 0, 3);
				$s3 = substr($str, 3, 3);
				$s4 = substr($str, 6, 4);
				$str = $s1.$s2.' '.$s3.' '.$s4;

			} else { // Default format
				// ToDo: add default format
				//  *************************** ???  ****************************
			}
		return ($str);
	}

	function clean_customerEmail($email = "") { // Added by Lxsparks 04/13
		$email = trim($email);
		$email = str_replace(" ", "", $email);

		// Check for more than one @
		if (substr_count($email, '@') > 1) {
			return false;
		}

		$email = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $email);

		if (preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $email)) {
			return $email;
		} else {
			return false;
		}
	}

	function clean_customerManual($str, $len) { // Sanitise data added manually
		print_r($str);
		//$str = trim($str);
		$str = preg_replace ('/\s\s+/', ' ', $str); // Reduce extra whitespaces inside
		//$str = (strlen($str > $len)) ?  substr($str, 0, $len) : $str; // Truncate anything longer then it should be
		/*$str = preg_replace('/<.[^<>]*?>/', '', $str);*/
		/*$str = preg_replace('/&nbsp;|&#160;/', '', $str);*/

		return ($str);
	}

	function global_editCustomer (&$data, $view){ // Added by Lxsparks 05/11
		global $_SHOP, $_COUNTRY_LIST;

		if (!isset($_COUNTRY_LIST)) {
		  If (file_exists(INC."lang".DS."countries_". $_SHOP->lang.".inc")){
			include_once(INC."lang".DS."countries_". $_SHOP->lang.".inc");
		  }else {
			include_once(INC."lang".DS."countries_en.inc");
		  }
		}

		$count = 0;
		$query_title = array();

		if (is_array($_POST['rowID'])) {
			//print('It is an array');
			$id=($_POST['rowID']);
			//print_r($id);

			$query="SELECT * FROM User WHERE User.user_id IN ('".join("','", $id)."') ORDER BY User.user_lastname ASC, User.user_firstname ASC, User.user_status ASC";

		} else {
			return addWarning(con('msg_choice_one_fields'));
		}

		if(!$res=ShopDB::query($query)){
			user_error(shopDB::error());
			return;
		}

		if (!ShopDB::num_rows($res)) {
			return addWarning(con('msg_no_result'));
		} else {
			$num_results = ShopDB::num_rows($res);
		}

	if ($_POST['submit'] == 'edit_manual') {	//Start of edit_manual

		$results_table = '0';
		$readonly='';
		$notes='fnc_form_notes_global_edit_manual';

		while($row=shopDB::fetch_assoc($res)){

			$rows['user_id'][]=$row['user_id'];
			$rows['user_firstname'][]=$row['user_firstname'];
			$rows['user_lastname'][]=$row['user_lastname'];
			$rows['user_address'][]=$row['user_address'];
			$rows['user_address1'][]=$row['user_address1'];
			$rows['user_city'][]=$row['user_city'];
			$rows['user_state'][]=$row['user_state'];
			$rows['user_zip'][]=$row['user_zip'];
			$rows['user_phone'][]=$row['user_phone'];
			$rows['user_fax'][]=$row['user_fax'];
			$rows['user_email'][]=$row['user_email'];
			$rows['user_status'][]=$row['user_status'];
			$rows['user_country'][]=$row['user_country'];
		}

	  } elseif ($_POST['submit'] == 'edit_format') { // End of Manual Edit Data / Start of Cleanup Data

			$results_table = '0';
			$readonly="readonly='readonly'";
			$notes='fnc_search_notes_edit_format';

			while($row=shopDB::fetch_assoc($res)){

				$rows['user_id'][]=$row['user_id'];
				$ctry = $rows['user_country'][]=$row['user_country'];

				$rows['user_firstname'][]=$this->clean_customerData($row['user_firstname'], 50);
				$rows['user_lastname'][]=$this->clean_customerData($row['user_lastname'], 50);
				$rows['user_address'][]=$this->clean_customerData($row['user_address'], 75);
				$rows['user_address1'][]=$this->clean_customerData($row['user_address1'], 75);
				$rows['user_city'][]=$this->clean_customerData($row['user_city'], 50);
				$rows['user_state'][]=$this->clean_customerData($row['user_state'], 50);
				$rows['user_zip'][]=$this->clean_customerZip($row['user_zip'],$ctry);

				$rows['user_phone'][]=$this->clean_customerTel($row['user_phone'], $ctry);
				$rows['user_fax'][]=$this->clean_customerTel($row['user_fax'], $ctry);

				$rows['user_email'][]=$row['user_email'];
				$rows['user_status'][]=$row['user_status'];

				$exp="(GIR 0AA|[A-PR-UWYZ]([0-9][0-9A-HJKPS-UW]?|[A-HK-Y][0-9][0-9ABEHMNPRV-Y]?) [0-9][ABD-HJLNP-UW-Z]{2})";

			}

	  } elseif ($_POST['submit'] == 'edit_global') { // End of Cleanup Data / Global edit

			$results_table = '1';
			$readonly='';
			$notes='fnc_form_notes_global_edit_global';

			while($row=shopDB::fetch_assoc($res)){

			$rows['user_id'][]=$row['user_id'];
			$rows['user_firstname'][]=$row['user_firstname'];
			$rows['user_lastname'][]=$row['user_lastname'];
			$rows['user_address'][]="";
			$rows['user_address1'][]="";
			$rows['user_city'][]="";
			$rows['user_state'][]="";
			$rows['user_zip'][]="";
			$rows['user_phone'][]="";
			$rows['user_fax'][]="";
			$rows['user_email'][]=$row['user_email'];
			$rows['user_status'][]=$row['user_status'];
			$rows['user_country'][]=$row['user_country'];
			}
	  }

	  if ($results_table=='0') { // DISPLAY results table '0'

			echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>
					<input type='hidden' name='action' value='global_search'/>\n";

			echo "<table>";
			echo "<tr><td class='admin_list_title'>".con('global_table_results_header_edit').": ".$num_results." ".con('global_table_customers')."</td></tr>";
			echo "<tr><td>".con("$notes")."</td></tr>\n";

			//while($row=shopDB::fetch_assoc($res)){
			for ($i='0';$i<count($rows['user_id']); $i++) {

				echo "<tr><td>";
				echo "<table id='resultsEdit'>";

				echo "<tr>
							<td><input ".$readonly." type='text' name='firstname[]' size='25' value ='". $rows['user_firstname'][$i]."' /></td>
							<td><input ".$readonly." type='text' name='lastname[]' size='25' value ='". $rows['user_lastname'][$i]."' /></td>
							<td class='admin_list_item'>".$this->print_status($rows['user_status'][$i])."</td>
							<td></td>
							<td></td>
							<td class='admin_list_item' width ='25' style='font-weight:bold;'>".con('global_table_ref').": ".$rows['user_id'][$i]." <input type='hidden' name='id[]' value='".$rows['user_id'][$i]."' /> </td>
							</tr>";
				echo "<tr>
							<td><input ".$readonly." type='text' name='address[]' size='25' value ='". $rows['user_address'][$i]."' /></td>
							<td><input ".$readonly." type='text' name='address1[]' size='25' value ='". $rows['user_address1'][$i]."' /></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							</tr>";

				echo "<tr>
							<td><input ".$readonly." type='text' name='city[]' size='25' value ='". $rows['user_city'][$i]."' /></td>
							<td><input ".$readonly." type='text' name='state[]' size='25' value ='". $rows['user_state'][$i]."' /></td>
							<td><input ".$readonly." type='text' name='zip[]' size='10' value ='". $rows['user_zip'][$i]."' /></td>
							<td colspan='2'><SELECT name='country[]'>";
							foreach  ($_COUNTRY_LIST as $k => $v) {
								echo '<option value="'.$k.'"';
								echo ($rows['user_country'][$i] == $k ? 'selected="selected"' : '').">";
								echo $v.'</option>';
							}
				echo "</SELECT></td></tr>";

				echo "<tr>
							<td><input ".$readonly." type='text' name='phone[]' size='25' value ='". $rows['user_phone'][$i]."' /></td>
							<td><input ".$readonly." type='text' name='fax[]' size='25' value ='". $rows['user_fax'][$i]."' /></td>
							<td></td>
							<td></td>
							<td colspan='2'><input ".$readonly." type='text' name='email[]' size='40' value ='". $rows['user_email'][$i]."' /></td>
							</tr>";

				echo "</td></tr></table>";
			}

			echo "<tr class='admin_value'><td class='admin_value' align='center' >";

			echo	"<button id='search' class='admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Job1' style='' name='action' value='global_update' type='submit'
				onClick=\"return confirmSubmit('".con(confirm_sure)."')\">Update data</button>";
			echo	"<button id='cancel' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Cancel' style='' name='cancel'  onClick=\"return confirmSubmit('".con(confirm_cancel)."')\" onclick=\"window.location = '{$_SERVER['PHP_SELF']}'\">Cancel</button>";

			echo "</td></tr>";
			echo "</table>";
			echo "</form>";

			return true;

		} // End of results_table '0'

		else if ($results_table=='1'){

			$users=serialize($rows['user_id']);

			echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>
				<input type='hidden' name='action' value='global_update_global'/>\n
				<input type='hidden' name='users' value='$users'/>\n";

			echo "<table>";
			echo "<tr><td class='admin_list_title'>".con('global_table_results_header_edit').": ".$num_results." ".con('global_table_customers')."</td></tr>";
			echo "<tr><td>".con("$notes")."</td></tr>\n";

			// Have to PASS user ID values as an array to the form_r
			// Have to PASS a value to distinguish which query needs to be used
			// form_r will then have to have two seperate query builds

			echo "<tr><td><table>";

			//echo "<td class='admin_list_item' width='40' align='left' nowrap>

			echo "	<tr><th>".con('global_table_data')."</th><th>".con('global_table_info')."</th><th>".con('global_table_select')."</th>";

			echo "	<tr><td>".con('user_address')."</td><td><input type='text' name='address' size='25' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_address' value='1' /></td></tr>
					<tr><td>".con('user_address1')."</td><td><input type='text' name='address1' size='25' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_address1' value='1' /></td></tr>";

			echo "	<tr><td>".con('user_city')."</td><td><input type='text' name='city' size='25' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_city' value='1' /></td></tr>
					<tr><td>".con('user_state')."</td><td><input type='text' name='state' size='25' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_state' value='1' /></td></tr>
					<tr><td>".con('user_zip')."</td><td><input type='text' name='zip' size='10' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_zip' value='1' /></td></tr>";

			echo  "		<tr><td>".con('user_country')."</td><td><SELECT name='country'>";

			foreach  ($_COUNTRY_LIST as $k => $v) {
				echo '<option value="'.$k.'"'.$s.'>'.$v.'</option>';
			}

			echo "</SELECT></td><td><input type='checkbox' class='case' name='sel_country' value='1' /></td></tr>";

			echo "<tr><td></td><td></td></tr>";

			echo "<tr><td>".con('user_phone')."</td><td><input type='text' name='phone' size='25' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_phone' value='1' /></td></tr>
					<tr><td>".con('user_fax')."</td><td><input type='text' name='fax' size='25' value ='' /></td>
					<td><input type='checkbox' class='case' name='sel_fax' value='1' /></td></tr>";

			echo "<tr><td></td><td></td></tr>";

			echo "</td></tr></table>";

			echo "<tr class='admin_value'><td class='admin_value' align='center' >";

			echo	"<button id='search' class='submit admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Job1' style='' name='submit' value='update_global' type='submit'
				onClick=\"return confirmSubmit('".con('confirm_sure')."')\">".con('btn_update_data')."</button>";
			echo	"<button id='cancel' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Cancel' style='' name='action' value='cancel' onClick=\"return confirmSubmit('".con('confirm_cancel')."')\" >".con('btn_cancel')."</button>
					</td></tr>";

			echo "</table>";
			echo "</form>";

			return true;
		} // End of results_table '1'

	}

	function global_editCustomerR ($data, $view) {

		Global $_SHOP, $_COUNTRY_LIST;
		if (!isset($_COUNTRY_LIST)) {
		  If (file_exists(INC."lang".DS."countries_". $_SHOP->lang.".inc")){
			include_once(INC."lang".DS."countries_". $_SHOP->lang.".inc");
		  } else {
			include_once(INC."lang".DS."countries_en.inc");
		  }
		}

		if ($_POST['submit']=='update_global') { // Global update customers with the same details

			$id=unserialize($data['users']);  // Get the users back into an array
			$IDs=implode(',', $id);
			$ctry=$data['country'];

			if ($data['sel_address'] =='1') {
				$address=$this->clean_customerData($data['address'], 75);
				$query_type[]="tb1.user_address='$address'";}
			if ($data['sel_address1'] =='1') {$address1=$this->clean_customerData($data['address1'], 75);
				$query_type[]="tb1.user_address1='$address1'";}
			if ($data['sel_city'] =='1') {$city=$this->clean_customerData($data['city'], 50);

				echo "City: ".$city."<br/><br/>";
				$query_type[]="tb1.user_city='$city'";}
			if ($data['sel_state'] =='1') {$state=$this->clean_customerData($data['state'], 50);
				$query_type[]="tb1.user_state='$state'";}
			if ($data['sel_zip'] =='1') { $zip=$this->clean_customerZip($data['zip'],$ctry);
				echo "Postcode: ".$zip."<br/><br/>";
				$query_type[]="tb1.user_zip='$zip'";}

			if ($data['sel_country'] =='1' && array_key_exists($ctry, $_COUNTRY_LIST)) {
				$query_type[]="tb1.user_country='$ctry'";}
			if ($data['sel_phone'] =='1') {$phone=$this->clean_customerTel($data['phone'],$ctry);
				$query_type[]="tb1.user_phone='$phone'";}
			if ($data['sel_fax'] =='1') {$fax=$this->clean_customerTel($data['fax'],$ctry);
				$query_type[]="tb1.user_fax='$fax'";}

			if (!isset($query_type)) {
					return addWarning(con('msg_choice_one_fields'));
				} else {
					$query="UPDATE User tb1 SET ". implode("\n , ",$query_type)." WHERE tb1.user_id IN($IDs)";
				}

		} else {

			$id=$data['id']; //Assign each colum data with an array sent from the form
			$firstname=$data['firstname'];
			$lastname=$data['lastname'];
			$address=$data['address'];
			$address1=$data['address1'];
			$city=$data['city'];
			$state=$data['state'];
			$zip=$data['zip'];
			$country=$data['country'];
			$phone=$data['phone'];
			$fax=$data['fax'];
			$email=$data['email'];

		// Loop through and update each record set
		$i='0';
		$v='0';
		$user_IDs=implode(',', $id);
		$query = "UPDATE user AS tb1 LEFT JOIN auth AS tb2 ON tb1.user_id = tb2.user_id SET ";
		foreach($id as $ids){

			$query1 .="WHEN ".$id[$i]." THEN '".$firstname[$i]."' ";
			$query2 .="WHEN ".$id[$i]." THEN '".$lastname[$i]."' ";
			$query3 .="WHEN ".$id[$i]." THEN '".$address[$i]."' ";
			$query4 .="WHEN ".$id[$i]." THEN '".$address1[$i]."' ";
			$query5 .="WHEN ".$id[$i]." THEN '".$city[$i]."' ";
			$query6 .="WHEN ".$id[$i]." THEN '".$state[$i]."' ";
			$query7 .="WHEN ".$id[$i]." THEN '".$zip[$i]."' ";
			$query8 .="WHEN ".$id[$i]." THEN '".$country[$i]."' ";
			$query9 .="WHEN ".$id[$i]." THEN '".$phone[$i]."' ";
			$query10 .="WHEN ".$id[$i]." THEN '".$fax[$i]."' ";
			$query11 .="WHEN ".$id[$i]." THEN '".$email[$i]."' ";

			$i++;
	}

			$query .="tb1.user_firstname = (CASE tb1.user_id ";
			$query .= $query1." ELSE tb1.user_firstname END), ";
			$query .="tb1.user_lastname = (CASE tb1.user_id ";
			$query .= $query2." ELSE tb1.user_lastname END), ";
			$query .="tb1.user_address = (CASE tb1.user_id ";
			$query .= $query3." ELSE tb1.user_address END), ";
			$query .="tb1.user_address1 = (CASE tb1.user_id ";
			$query .= $query4." ELSE tb1.user_address1 END), ";
			$query .="tb1.user_city = (CASE tb1.user_id ";
			$query .= $query5." ELSE tb1.user_city END), ";
			$query .="tb1.user_state = (CASE tb1.user_id ";
			$query .= $query6." ELSE tb1.user_state END), ";
			$query .="tb1.user_zip = (CASE tb1.user_id ";
			$query .= $query7." ELSE tb1.user_zip END), ";
			$query .="tb1.user_country = (CASE tb1.user_id ";
			$query .= $query8." ELSE tb1.user_country END), ";
			$query .="tb1.user_phone = (CASE tb1.user_id ";
			$query .= $query9." ELSE tb1.user_phone END), ";
			$query .="tb1.user_fax = (CASE tb1.user_id ";
			$query .= $query10." ELSE tb1.user_fax END), ";
			$query .="tb1.user_email = (CASE tb1.user_id ";
			$query .= $query11." ELSE tb1.user_email END)";

			$query .="WHERE tb1.user_id IN (".$user_IDs.")";
			//echo $query."<br/><br/>"; Checkpoint
	}

		if(!$res=ShopDB::query($query)){
			user_error(shopDB::error());
			echo "Update failed.<br />";
			return;
		} else {
			$v=shopDB::affected_rows(); // If record was successfully updated - record it
		}

		if ($v > 0) {
			echo "<p style='font-weight:bold;'>".con("fnc_update_title_updated")."</p>";
			echo "<p>".con("fnc_update_notes_updated").": ".$v."</p>";
			return true;
		} else {
			echo "<p style='font-weight:bold;'>".con("global_update_title_notupdated")."</p>";
			echo "<p>".con("fnc_update_notes_notupdated")."</p>";
			return true;
		}
	}

	function global_editAccountR(&$data, $view) { // Added by Lxsparks 06/11
		global $_SHOP;

		if ($data["user_pos_id"] || $data["user_member_id"]) {

			$ref_ID=array_values($data["ref_id"]);
			$member_ID=$data["user_member_id"];
			$pos_ID=$data["user_pos_id"];
			$guest_ID=$data["user_guest_id"];
			$count=count($ref_ID);

			$ii=0;
			for($i=0;$i<$count;$i++){
			$v=$ref_ID[$i];

			//***********************************//

			// Build two arrays (OLD and NEW) which are the pairs of customer IDs where one will be replaced by the other
			// If there are 3 customer types Guests will go straight to Members
			// Echo the results on screen

			if ($member_ID[$v] !=0 && $pos_ID[$v] !=0) { //POS -> Members
				$new_ID[$ii]=$member_ID[$v];
				$old_ID[$ii]=$pos_ID[$v];
				echo con("fnc_pos_cust_rec")." ".$pos_ID[$v].", ".con("fnc_replaced_by")." ".con("fnc_member_cust_rec")." ".$member_ID[$v]."<br />";
				$ii++;
				}

			if ($member_ID[$v] !=0 && $guest_ID[$v] !=0) { //Guest -> Members
				$new_ID[$ii]=$member_ID[$v];
				$old_ID[$ii]=$guest_ID[$v];
				echo con("fnc_guest_cust_rec")." ".$guest_ID[$v].", ".con("fnc_replaced_by")." ".con("fnc_member_cust_rec")." ".$member_ID[$v]."<br />";
				$ii++;
				}

			if ($pos_ID[$v] !=0 && $guest_ID[$v] !=0  && $member_ID[$v]=0) { //Guest -> POS (if not into Member)
				$new_ID[$ii]=$pos_ID[$v];
				$old_ID[$ii]=$guest_ID[$v];
				echo con("fnc_guest_cust_rec")." ".$guest_ID[$v].", ".con("fnc_replaced_by")." ".con("fnc_pos_cust_rec")." ".$pos_ID[$v]."<br />";
				$ii++;
				}
		}

			//This query loops through the number of old member details that need updating
			//1) Updates all OLD ID Orders to NEW ID Orders
			//2) Updates all OLD ID Seats to NEW ID Seats
			//3) Updates the NEW ID current tickets with itself and the value of those in the OLD ID current tickets
			for($i=0;$i<count($old_ID);$i++){

				$query="UPDATE `Order` AS `O`, `Seat` AS `S`, `User` AS `a` JOIN `User` AS `b` ON a.user_id='".$new_ID[$i]."' AND b.User_id='".$old_ID[$i]."'
				SET O.order_user_id=IF(O.order_user_id='".$old_ID[$i]."','".$new_ID[$i]."',O.order_user_id),
				S.seat_user_id=IF(S.seat_user_id='".$old_ID[$i]."','".$new_ID[$i]."',S.seat_user_id),
				a.user_order_total=a.user_order_total+b.user_order_total,
				a.user_current_tickets=a.user_current_tickets+b.user_current_tickets,
				a.user_total_tickets=a.user_total_tickets+b.user_total_tickets";

				if(!$res=ShopDB::query($query)){
					user_error(shopDB::error());
					echo "Order/Seats NOT updated<br>";
					return;
				} else { echo "<br />Order/Seats updated customer id: ".$old_ID[$i]." with customer id: ".$new_ID[$i]."<br />";}

			}
			//***********************************//
			// Merge empty fields of the new (user being kept/updated) with old data if it exists.
			// Custom fields are also merged for future use.
			// Update last_login with the newest date.
			// Update user_creatred with the oldest date.

			$old = implode(',', $old_ID);
			$query="SELECT * FROM User WHERE user_id IN ($old)";

			if(!$res_old=ShopDB::query($query)){
				user_error(shopDB::error());
			   return;
			}

			$new = implode(',', $new_ID);
			$query="SELECT * FROM User WHERE user_id IN ($new)";

			if(!$res_new=ShopDB::query($query)){
				user_error(shopDB::error());
			   return;
			}

			if (!ShopDB::num_rows($res_new)) {
			return addWarning(con('msg_no_result'));
			} else {$n=ShopDB::num_rows($res_new);
			}

			$i=0;
			for($i;$i<$n;$i++){
				$row_old[$i]=shopDB::fetch_assoc($res_old);
				$row_new[$i]=shopDB::fetch_assoc($res_new);
				}

			$i=0;
			for($i;$i<$n;$i++){
				if (strlen($row_new[$i]['user_address1']) == 0 && strlen($row_old[$i]['user_address1']) != 0){ $row_new[$i]['user_address1']=$row_old[$i]['user_address1'];}
				if (strlen($row_new[$i]['user_state']) == 0 && strlen($row_old[$i]['user_state']) != 0){ $row_new[$i]['user_state']=$row_old[$i]['user_state'];}
				if (strlen($row_new[$i]['user_phone']) == 0 && strlen($row_old[$i]['user_phone']) != 0){ $row_new[$i]['user_phone']=$row_old[$i]['user_phone'];}
				if (strlen($row_new[$i]['user_fax']) == 0 && strlen($row_old[$i]['user_fax']) != 0){ $row_new[$i]['user_fax']=$row_old[$i]['user_fax'];}
				if (strlen($row_new[$i]['user_prefs']) == 0 && strlen($row_old[$i]['user_prefs']) != 0){ $row_new[$i]['user_prefs']=$row_old[$i]['user_prefs'];}
				if (strlen($row_new[$i]['user_custom1']) == 0 && strlen($row_old[$i]['user_custom1']) != 0){ $row_new[$i]['user_custom1']=$row_old[$i]['user_custom1'];}
				if (strlen($row_new[$i]['user_custom2']) == 0 && strlen($row_old[$i]['user_custom2']) != 0){ $row_new[$i]['user_custom2']=$row_old[$i]['user_custom2'];}

				if ($row_new[$i]['user_custom3'] == 0 && $row_old[$i]['user_custom3'] != 0){ $row_new[$i]['user_custom3']=$row_old[$i]['user_custom3'];} // INT default value is 0
				if ($row_new[$i]['user_custom4'] < $row_old[$i]['user_custom4']){ $row_new[$i]['user_custom4']=$row_old[$i]['user_custom4'];} // Date format
				if ($row_new[$i]['user_created'] > $row_old[$i]['user_created']){ $row_new[$i]['user_created']=$row_old[$i]['user_created'];} // Date format user the oldest date
				if ($row_new[$i]['user_lastlogin'] < $row_old[$i]['user_lastlogin']){ $row_new[$i]['user_lastlogin']=$row_old[$i]['user_lastlogin'];} // Date format user the newest date

				if (strlen($row_new[$i]['user_owner_id']) == 0 && strlen($row_old[$i]['user_owner_id']) != 0  || $row_new[$i]['user_owner_id'] == 0 && $row_old[$i]['user_owner_id'] != 0){ $row_new[$i]['user_owner_id']=$row_old[$i]['user_owner_id'];}

			//echo "<br />New user details: ".$row_new[$i]['user_address1'].".  Old user details: ".$row_old[$i]['user_address1'].".<br />";

			}

			// Loop through and update each record set
			$i='0';
			$v='1';
			for($i;$i<$n;$i++){

				$query="UPDATE User SET
				user_address1='".$row_new[$i]['user_address1']."', user_state='".$row_new[$i]['user_state']."', user_phone='".$row_new[$i]['user_phone']."', user_fax='".$row_new[$i]['user_fax']."',
				user_prefs='".$row_new[$i]['user_prefs']."', user_custom1='".$row_new[$i]['user_custom1']."', user_custom2='".$row_new[$i]['user_custom2']."',
				user_custom3='".$row_new[$i]['user_custom3']."', user_custom4='".$row_new[$i]['user_custom4']."', user_created='".$row_new[$i]['user_created']."',
				user_lastlogin='".$row_new[$i]['user_lastlogin']."', user_owner_id='".$row_new[$i]['user_owner_id']."'
				WHERE user_id='".$row_new[$i]['user_id']."'";

				if(!$res=ShopDB::query($query)){
				   user_error(shopDB::error());
				   echo "<br />DISASTER!!<br />";
				   return;
				   } else {
						$v++; // If record was successfully updated - record it
				   }
				$i++;
			}

			if ($v > 0) {
				echo "<p style='font-weight:bold;'>".con("fnc_update_title_updated")."</p>";
				echo "<p>".con("fnc_update_notes_updated").": ".$v."</p>";
				//return true;

			} else {
				echo "<p style='font-weight:bold;'>".con("global_update_title_notupdated")."</p>";
				echo "<p>".con("fnc_update_notes_notupdated")."</p>";
				return true;
			}

			//***********************************//
			//Then we DELETE the OLD user/s from the User table
			$query="DELETE FROM User WHERE user_id IN ($old)";

			if(!$res=ShopDB::query($query)){
				user_error(shopDB::error());
				echo "Failed to delete!<br />";
			   return;
			}
			//***********************************//

		} else { return; }
	}

	function delete_customerR(&$data, $view) { // Added by Lxsparks 05/11
		global $_SHOP;
		$id= $_GET['ID'];
		$status=$_GET['status'];
		$v='0';

		if ($status == '2') {  //

			$query="DELETE auth.*, User.* FROM auth JOIN User ON auth.user_id=User.user_id WHERE auth.user_id='$id'";

		} else { $query="DELETE User.* FROM User WHERE User.user_id='$id'";
		}

		if(!$res=ShopDB::query($query)){
			user_error(shopDB::error());
			echo "Update failed.<br />";
			return;
		} else {
			$v=shopDB::affected_rows(); // If record was successfully updated - record it
		}

		if ($v > 0) {
			echo "<p style='font-weight:bold;'>".con("fnc_delete_title_updated")."</p>";
			echo "<p>".con("fnc_delete_notes_updated").": ".$v."</p>";
			return true;
		} else {
			echo "<p style='font-weight:bold;'>".con("fnc_delete_title_notupdated")."</p>";
			echo "<p>".con("fnc_delete_notes_notupdated")."</p>";
			return true;
		}

	}

	function searchForm (&$data, $view) {
		//require_once("admin/class.adminview.php");
		//$AV = new AdminView;

		global $_SHOP, $_COUNTRY_LIST;

		if (!isset($_COUNTRY_LIST)) {
		  If (file_exists(INC."lang".DS."countries_". $_SHOP->lang.".inc")){
			include_once(INC."lang".DS."countries_". $_SHOP->lang.".inc");
		  }else {
			include_once(INC."lang".DS."countries_en.inc");
		  }
		}

		$results_table = '';
		$basicOptions=array('0'=>'N/A', '1'=>'Yes', '2'=>'No');
		$alphaStart=array('A'=>'A','B'=>'B','C'=>'C','D'=>'D','E'=>'E','F'=>'F','G'=>'G','H'=>'H','I'=>'I','J'=>'J','K'=>'K','L'=>'L','M'=>'M','N'=>'N','O'=>'O','P'=>'P','Q'=>'Q','R'=>'R','S'=>'S','T'=>'T','U'=>'U','V'=>'V','X'=>'X','Y'=>'Y');
		$alphaEnd=array('B'=>'B','C'=>'C','D'=>'D','E'=>'E','F'=>'F','G'=>'G','H'=>'H','I'=>'I','J'=>'J','K'=>'K','L'=>'L','M'=>'M','N'=>'N','O'=>'O','P'=>'P','Q'=>'Q','R'=>'R','S'=>'S','T'=>'T','U'=>'U','V'=>'V','X'=>'X','Y'=>'Y','Z'=>'Z');
		unset($_SESSION['results']); //Ensure nothing is carried over from previous searches

		echo"<h1>".con(form_title_main)."</h1>";
		echo"<p>".con(form_title_main_notes)."</p>";

		echo"<div id='accordion'>"; // Start of the Accordian DIV

			// *** Start of Drop-down Slide ADDRESS block *** \\
			echo"<div class='accordion-header'>
						<h3>".con('global_search_block_title_address')." </h3>
						<span></span>
						</div>";

			// *** Start of Form *** \\
			echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>";
			echo "<input type='hidden' name='action' value='global_search'/>\n";  //Form value = global_search

				//Start of Street search
				echo "<h1>".con('global_search_title_street')."</h1>";
				echo "<p>".con('global_search_notes_street')."</p>";
				echo "<p class='admin_name'><label>".con('user_address')."</label><input type='text' name='searchAddress' size='50' /></p>";
				echo "<p class='admin_name'><label>".con('user_address1')."</label><input type='text' name='searchAddress1' size='50' /></p>";


				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_address' type='submit'>".con('btn_search')."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con('btn_reset')."</button>
					</p>";

				echo "<hr>";

				//Start of City word search
				echo "<h1>".con('global_search_title_city')."</h1>";
				echo "<p>".con('global_search_notes_city')."</p>";
				echo "<p class='admin_name'><label>".con('user_city')."</label><input type='text' name='searchCity' size='50' /></p>";

				echo "<p>
				<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_city' type='submit'>".con(btn_search)."</button>
				<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
				</p>";

				echo"<hr>";

				//Start of State word search
				echo "<h1>".con('global_search_title_state')."</h1>";
				echo "<p>".con('global_search_notes_state')."</p>";
				echo "<p class='admin_name'><label>".con('user_state')."</label><input type='text' name='searchState' size='50' /></p>";

				echo "<p>
				<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_state' type='submit'>".con(btn_search)."</button>
				<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
				</p>";

				echo "<hr>";

				//Start of Postcode word search
				echo "<h1>".con('global_search_title_zip')."</h1>";
				echo "<p>".con('global_search_notes_zip')."</p>";
				echo "<p class='admin_name'><label>".con('user_zip')."</label><input type='text' name='searchZip' size='10' /></p>";

				echo "<p>
				<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_zip' type='submit'>".con(btn_search)."</button>
				<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
				</p>";

				echo "<hr>";

				//Start of Postcode Range search
				echo "<h1>".con('global_search_title_zip_range')."</h1>";
				echo "<p>".con('global_search_notes_zip_range')."</p>";
				echo "<p class='admin_name'><label>".con('global_search_zip_start')."</label><input type='text' name='startZip' size='10' /></p>";
				echo "<p class='admin_name'><label>".con('global_search_zip_end')."</label><input type='text' name='endZip' size='10' /></p>";

				echo "<p>
				<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_zip_range' type='submit'>".con(btn_search)."</button>
				<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
				</p>";

				echo "<hr>";

				//Start of Country word search
				echo "<h1>".con('global_search_title_country')."</h1>";
				echo "<p>".con('global_search_notes_country')."</p>";
				echo "<p class='admin_name'><label>".con('user_country')."</label><SELECT name='searchCountry'>";

				foreach  ($_COUNTRY_LIST as $k => $v) {
					echo '<option value="'.$k.'"'.$s.'>'.$v.'</option>';
				}
				echo"</SELECT></p>";
				echo "<p class='admin_name'><label>".con('global_search_include_country')."</label>
					<input type='radio' name='searchCountryOpt' value='yes' checked='checked'/>".con('yes')."</input>
					<input type='radio' name='searchCountryOpt' value='no' />".con('no')."</input></p>";
				echo "<p>
				<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_country' type='submit'>".con(btn_search)."</button>
				<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
				</p>";

			echo "</form>";
			// *** End of Form *** \\
			// *** End of Drop-down Slide ADDRESS block *** \\

			// *** Start of Drop-down Slide NAME block *** \\

			echo"<div class='accordion-header'>
					<h3>".con('global_search_block_title_name')." </h3>
					<span></span>
					</div>";

			// *** Start of Form *** \\
			echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>"; // *** Start of Form *** \\
			echo "<input type='hidden' name='action' value='global_search'/>\n";  //Form value = global_search

				//Start of Surname word search
				echo "<h1>".con('global_search_title_surname')."</h1>";
				echo "<p>".con('global_search_notes_surname')."</p>";
				echo "<p class='admin_name'><label>".con('global_search_user_surname')."</label><input type='text' name='searchLastName' size='25' /></p>";

				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_surname' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

				echo "<hr>";

				//Start of Surname alphabet search
				echo "<h1>".con('global_search_title_AZ')."</h1>";
				echo "<p>".con('global_search_notes_AZ')."</p>";
				echo "<p class='admin_name'><label class='opt'>".con('global_search_user_surname_start')."</label>
							<select name='startLetter'>
							<option value='0'>Choose a letter</option>";
				$this->showFormOptions($alphaStart);
				echo "</select></p>";
				echo "<p class='admin_name'><label class='opt'>".con('global_search_user_surname_end')."</label>
							<select name='endLetter'>
							<option value='0'>Choose a letter</option>";
				$this->showFormOptions($alphaEnd);
				echo "</select></p>";

				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_surnameAZ' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

				echo "</form>"; // *** End of Form *** \\

				// *** End of Drop-down Slide NAME block *** \\

			// *** Start of Drop-down Slide PHONE block *** \\

			echo"<div class='accordion-header'>
					<h3>".con('global_search_block_title_phone')." </h3>
					<span></span>
					</div>";

			// *** Start of Form *** \\
			echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>"; // *** Start of Form *** \\
			echo "<input type='hidden' name='action' value='global_search'/>\n";  //Form value = global_search

				//Start of TELEPHONE word search
				echo "<h1>".con('global_search_block_title_phone')."</h1>";
				echo "<p>".con('global_search_notes_phone')."</p>";
				echo "<p class='admin_name'><label>".con('user_phone')."</label><input type='text' name='searchPhone' size='25' /></p>";

				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_phone' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

				echo "<hr>";

				//Start of FAX search
				echo "<h1>".con('global_search_title_fax')."</h1>";
				echo "<p>".con('global_search_notes_fax')."</p>";
				echo "<p class='admin_name'><label>".con('user_fax')."</label><input type='text' name='searchFax' size='25' /></p>";

				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_fax' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

				echo "</form>"; // *** End of Form *** \\

				// *** End of Drop-down Slide PHONE block *** \\

			// *** Start of Drop-down Slide EMAIL block *** \\

			echo"<div class='accordion-header'>
					<h3>".con('global_search_block_title_email')." </h3>
					<span></span>
					</div>";

			// *** Start of Form *** \\
			echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>"; // *** Start of Form *** \\
			echo "<input type='hidden' name='action' value='global_search'/>\n";  //Form value = global_search

				//Start of Email word search
				echo "<h1>".con('global_search_block_title_email')."</h1>";
				echo "<p>".con('global_search_notes_email')."</p>";
				echo "<p class='admin_name'><label>".con('user_email')."</label><input type='text' name='searchEmail' size='50' /></p>";
				echo "<p class='admin_name'><label>".con('global_search_include_email')."</label>
					<input type='radio' name='searchEmailOpt' value='yes' checked='checked'/>".con('yes')."</input>
					<input type='radio' name='searchEmailOpt' value='no' />".con('no')."</input></p>";
				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_email' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

				echo "</form>"; // *** End of Form *** \\

				// *** End of Drop-down Slide EMAIL block *** \\

				// *** Start of Drop-down Slide COMBINE block *** \\

				echo"<div class='accordion-header'>
							<h3>".con('global_search_block_title_combine')." </h3>
							<span></span>
							</div>";

				echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>"; // *** Start of Form *** \\
				echo "<input type='hidden' name='action' value='global_search'/>\n";  //Form value = global_search

				echo "<h1>".con('global_search_title_global_posMerge')."</h1>";
				echo "<p>".con('global_search_notes_global_posMerge')."</p>";
				echo "<p class='admin_name'><label class='opt'>".con('global_search_duplicate_email')."</label><input type='checkbox' value='selected' name='duplicate_email'>";
				echo "</p>";


				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_accountType' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

			echo "</form>";
			// *** End of Form *** \\
			// *** End of Drop-down Slide COMBINE block *** \\

			// *** Start of Drop-down Slide ACTIVITY block *** \\
			echo"<div class='accordion-header'>
						<h3>".con('global_search_block_title_activity')." </h3>
						<span></span>
						</div>";

			// *** Start of Form *** \\
			echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>";
			echo "<input type='hidden' name='action' value='global_search'/>\n";  //Form value = global_search

				//**** Customer Activity/Status Search ****\\
				echo "<h1>".(con('global_search_block_title_activity'))."</h1>";
				echo "<p>".con('global_search_notes_activity')."</p>";
				echo "<p class='admin_name'><label class='opt'>".con('global_search_opt_active')."</label>
					<select name='user_active'>
							<option value='0'>".con('N/A')."</option>
									<option value='1'>".con('Yes')."</option>
							<option value='2'>".con('No')."</option>
					</select></p>";
				echo "<p class='admin_name'><label class='opt'>".con('global_search_opt_user_ticket_status')."</label>
					<select name='user_ticket_status'>
						<option value='0'>".con('N/A')."</option>
						<option value='1'>".con('Yes')."</option>
						<option value='2'>".con('No')."</option>
					</select></p>";

				//Calender get class into the page
				//ToDo: move the 3 uses into a function?
				require_once('../includes/plugins/advancedsearch/calendar/classes/tc_calendar.php');
				echo "<script language='javascript' src='../includes/plugins/advancedsearch/calendar/calendar.js'></script>";
				echo "<link href='../includes/plugins/advancedsearch/calendar/calendar.css' rel='stylesheet' type='text/css'>";

				//instantiate class and set properties
				$date_now=date('Y-m-d');
				$myCalendar = new tc_calendar("date1", true);
				$myCalendar->setIcon("../includes/plugins/advancedsearch/calendar/images/iconCalendar.gif");
				//$myCalendar->setDate(01, 03, 2001);
				$myCalendar->setPath("../includes/plugins/advancedsearch/calendar/");
				$myCalendar->setYearInterval(2000, 2015);
				$myCalendar->dateAllow('', $date_now);
				$myCalendar->setAlignment('oleft', 'otop');
				//$myCalendar->setOnChange("myChanged('test')");
				//output the calendar

				echo "<p class='admin_name'><label class='opt'>".con('global_search_opt_user_login_last')."</label></p>";

				$myCalendar->writeScript();

				echo "<p class='form_buttons calender_clearance'>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_activity' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";
				echo "<hr>";

				//**** Customer Tickets Between Search ****\\
				echo "<h1>".(con('global_search_title_ticketsBetween'))."</h1>";
				echo "<p>".con('global_search_notes_ticketsBetween')."</p>";

				echo "<p class='admin_name'><label>".con('global_search_tickets_between')."</label></p>";

				$date3_default =date('Y-m-d');
				$date4_default = date('Y-m-d');
				$myCalendar = new tc_calendar("date3", true, false);
				$myCalendar->setIcon("../includes/plugins/advancedsearch/calendar/images/iconCalendar.gif");
				$myCalendar->setDate(date('d', strtotime($date3_default))
								, date('m', strtotime($date3_default))
								, date('Y', strtotime($date3_default)));
				$myCalendar->setPath("../includes/plugins/advancedsearch/calendar/");
				$myCalendar->setYearInterval(2000, 2020);
				$myCalendar->setAlignment('left', 'top');
				$myCalendar->setDatePair('date3', 'date4', $date4_default);
				$myCalendar->writeScript();

				$myCalendar = new tc_calendar("date4", true, false);
				$myCalendar->setIcon("../includes/plugins/advancedsearch/calendar/images/iconCalendar.gif");
				$myCalendar->setDate(date('d', strtotime($date4_default))
							 , date('m', strtotime($date4_default))
							 , date('Y', strtotime($date4_default)));
				//$myCalendar->setDate(date('d'), date('m'), date('Y'));
				$myCalendar->setPath("../includes/plugins/advancedsearch/calendar/");
				$myCalendar->setYearInterval(2000, 2020);
				$myCalendar->dateAllow('', $date_now);
				$myCalendar->setAlignment('left', 'top');
				$myCalendar->setDatePair('date3', 'date4', $date3_default);

				$myCalendar->writeScript();

				echo "<p class='form_buttons calender_clearance'>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_ticketsBetween' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

				echo "<hr>";

				//Start of EVENT search
				echo "<h1>".con('global_search_title_event')."</h1>";
				echo "<p>".con('global_search_notes_event')."</p>";
				echo "<p class='admin_name'><label>".con('event')."</label>";
				$this->print_events();
				echo "</p><p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='search_event' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

			echo "</form>";
			// *** End of Form *** \\
			// *** End of Drop-down Slide ACTIVITY block *** \\

			// *** Start of Drop-down Slide ADMIN block *** \\

			echo"<div class='accordion-header'>
						<h3>".con('global_search_block_title_admin')." </h3>
						<span></span>
						</div>";

			// *** Start of Form *** \\
			echo "<form class='accordion-content' method='POST' action='{$_SERVER['PHP_SELF']}'>";
			echo "<input type='hidden' name='action' value='dbCheck'/>\n";  //Form value = global_dbcheck_orders

				echo "<h1>".con('global_search_title_dbCheck_orders')."</h1>";
				echo "<p>".con('global_search_notes_dbCheck_orders')."</p>";
				echo "<p class='admin_name'><label class='opt'>".con('fnc_dbCheck_orders')."</label><input type='checkbox' value='selected' name='dbCheck_orders'>";
				echo "</p>";


				echo "<p>
					<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Search' style='' name='submit' value='dbCheck1' type='submit'>".con(btn_search)."</button>
					<button id='res' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Reset' style='' name='res' type='reset'>".con(btn_reset)."</button>
					</p>";

			echo "</form>";
			// *** End of Form *** \\
			// *** End of Drop-down Slide ADMIN block *** \\

		echo "</div>"; // End of the Accordian DIV
	}

	function searchForm_R (&$data, $view) {  //Added by Lxsparks 06/11

		Global $_COUNTRY_LIST;

		if (!isset($_COUNTRY_LIST)) {
		  If (file_exists(INC."lang".DS."countries_". $_SHOP->lang.".inc")){
			include_once(INC."lang".DS."countries_". $_SHOP->lang.".inc");
		  }else {
			include_once(INC."lang".DS."countries_en.inc");
		  }
		}

		if ($_POST['submit']=='search_activity') { // Search on Customer Activity

					$results_table = '0';
					$count = 0;
					$query_title = array();

					if($data["user_active"]=="0"){
					  $query_title[]=con('global_table_title_customer_activated_both');
					  $count++;
					}
					if($data["user_active"]=="1"){
					  $query_type[]="auth.active IS NULL";
					  $query_title[]=con('global_table_title_customer_activated_yes');
					  $count++;
					}
					if($data["user_active"]=="2"){
					  $query_type[]="auth.active IS NOT NULL";
					  $query_title[]=con('global_table_title_customer_activated_no');
					  $count++;
					}
					if($data["user_ticket_status"]=="0"){
					  $query_title[]=con('global_table_title_customer_tickets_both');
					  $count++;
					}
					if($data["user_ticket_status"]=="1"){
					  $query_type[]="user_current_tickets >= 1";
					  $query_title[]=con('global_table_title_customer_tickets_yes');
					  $count++;
					}
					if($data["user_ticket_status"]=="2"){
					  $query_type[]="user_current_tickets < 1" ;
					  $query_title[]=con('global_table_title_customer_tickets_none');
					  $count++;
					}
					if ($count <1) {
					  return addWarning(con('msg_search_choice_two_fields'));
					}

					$login_date = isset($_REQUEST["date1"]) ? $_REQUEST["date1"] : "";

					$table_opt='city';

					if ($login_date<>"0000-00-00") {
						$query_type[]="user_lastlogin <='$login_date'";
						$query_title[]=con('global_customer_login_req').": ". $login_date;

						} else {
						$query_title[]=con('global_table_title_customer_login_notreq');
						}

			} else if ($_POST['submit']=='search_address') { // Search on Address based on a LIKE pattern

					$results_table = '0';

					$name=$data["searchAddress"];
					$name1=$data["searchAddress1"];

					if (isset($name)) {
						$name=str_replace(chr(173), "", $name);
						$name=$this->search_pattern ($name, 'string', 75);
						if (strlen($name)!='0'){
							$query_type[]="User.user_address LIKE '$name'";
							$table_opt='address';
							$table_opt2='address1';
						}
					}

					if (isset($name1)) {
						$name1=str_replace(chr(173), "", $name1);
						$name1=$this->search_pattern ($name1, 'string', 75);
						if (strlen($name1)!='0'){
								$query_type[]="User.user_address1 LIKE '$name1'";
								$table_opt2='address1';
							}
					}

					$query_title[]=con('global_table_title_customer_address').": ". $name;

			} else if ($_POST['submit']=='search_city') { // Search on City based on a LIKE pattern

					$results_table = '0';

					$name = ($data["searchCity"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 50);

					if (isset($name)){$query_type[]="User.user_city LIKE '$name'";}
					$query_title[]=con('global_table_title_customer_city').": ". $name;
					$table_opt='city';

			} else if ($_POST['submit']=='search_zip') { // Search on Postcode based on a LIKE pattern

					$results_table = '0';

					$name = ($data["searchZip"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 10);

					if (isset($name)){$query_type[]="User.user_zip LIKE '$name'";}
					$query_title[]=con('global_table_title_customer_zip').": ". $name;
					$table_opt='zip';

			} else if ($_POST['submit']=='search_zip_range') { // Search on Postcode based on a LIKE pattern

					$results_table = '0';

					$name = ($data["startZip"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 10);

					$name1 = ($data["endZip"]);
					$name1=str_replace(chr(173), "", $name1);
					$name1=$this->search_pattern ($name1, 'string', 10);

					if (strlen($name) !='0'  && strlen($name1) !='0'){$query_type[]="User.user_zip BETWEEN '$name' AND '$name1'";}
					$query_title[]=con('global_table_title_customer_zip_range').": ". $name;
					$table_opt='zip';

			} else if ($_POST['submit']=='search_state') { // Search on State based on a LIKE pattern

					$results_table = '0';

					$name = ($data["searchState"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 50);

					if (isset($name)){$query_type[]="User.user_state LIKE '$name'";}
					$query_title[]=con('global_table_title_state').": ". $name;
					$table_opt='state';

			} else if ($_POST['submit']=='search_country') { // Search on Country based on a LIKE/NOT LIKE pattern

					$results_table = '0';

					$name = ($data["searchCountry"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 50);

					$opt = ($data["searchCountryOpt"] == 'yes' ? 'LIKE' : 'NOT LIKE');

					if (isset($name)){$query_type[]="User.user_country $opt '$name'";}
					if (isset($opt) && $opt=='LIKE') {
						$query_title[]=con('global_table_title_countryLike').": ". AdminView::getCountry($name);
					} else {
						$query_title[]=con('global_table_title_countryNot').": ". AdminView::getCountry($name);
					}
					$table_opt='country';

			} else if ($_POST['submit']=='search_surname') { // Search on the Surname based on a LIKE pattern

				$results_table = '0';

				$name = ($data["searchLastName"]);

				$name=str_replace(chr(173), "", $name);

				$name=$this->search_pattern ($name, 'string', 50);

				if (isset($name)){$query_type[]="User.user_lastname LIKE '$name'";}
				$query_title[]=con('global_table_title_customer_surname').": ". $name;
				$table_opt='city';

				//$query_type[]="User.user_lastname REGEXP '$name'";

			} else if ($_POST['submit']=='search_surnameAZ') {  // Surnames between two letters

				$results_table = '0';

				$s1 = substr(($data["startLetter"]), 0, 1);
				$s2 = substr(($data["endLetter"]), 0 ,1);


				$s3 = strcmp($s1, $s2);
				if ($s3 !== -1) {
					return addWarning(con('msg_search_AZ_aBiggerZ'));
					}


				$S1 .='%';
				$S2 .='%';

				$query_type[]="User.user_lastname BETWEEN '$s1' and '$s2' OR User.user_lastname LIKE '$s2'";

				$query_title[]=con('global_table_title_customer_surname_btwn').": ". $s1." & ".$s2;
				$table_opt='city';

			} else if ($_POST['submit']=='search_email') { // Search on Email based on a LIKE/NOT LIKE pattern

					$results_table = '0';

					$name = ($data["searchEmail"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 50);

					$opt = ($data["searchEmailOpt"] == 'yes' ? 'LIKE' : 'NOT LIKE');

					if (isset($name)){$query_type[]="User.user_email $opt '$name'";}
					if (isset($opt) && $opt=='LIKE') {
						$query_title[]=con('global_table_title_emailLike').": ". $name;
					} else {
						$query_title[]=con('global_table_title_emailNot').": ". $name;
					}
					$table_opt='email';

			} else if ($_POST['submit']=='search_phone') { // Search on Phone based on a LIKE pattern

					$results_table = '0';

					$name = ($data["searchPhone"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 50);

					if (isset($name)){$query_type[]="User.user_phone LIKE '$name'";}
					$query_title[]=con('global_table_title_state').": ". $name;
					$table_opt='phone';

			}	else if ($_POST['submit']=='search_fax') { // Search on Fax based on a LIKE pattern

					$results_table = '0';

					$name = ($data["searchFax"]);
					$name=str_replace(chr(173), "", $name);
					$name=$this->search_pattern ($name, 'string', 50);

					if (isset($name)){$query_type[]="User.user_fax LIKE '$name'";}
					$query_title[]=con('global_table_title_state').": ". $name;
					$table_opt='fax';

			} else if ($_POST['submit']=='search_accountType') {  // POS, Guest and Member duplicate checker

				$results_table = '1';
				$count = 0;
				if($data["duplicate_email"]=="selected"){
					$query_type[]="auth.active IS NULL";
					$query_title[]=con('global_table_title_customer__activated_both');
					$count++;
					}
				if ($count <1) {
					return addWarning(con('msg_choice_one_fields'));
					}

			} else if ($_POST['submit']=='search_ticketsBetween') { // Search on Tickets ordered between based on a LIKE pattern

					$results_table = '2';
					$start_date = isset($_REQUEST["date3"]) ? $_REQUEST["date3"] : "";
					$end_date = isset($_REQUEST["date4"]) ? $_REQUEST["date4"] : "";

					if ($start_date<>"0000-00-00"  && $end_date<>"0000-00-00" ) {
						$query_type[]="`ti`.order_date  BETWEEN '$start_date' AND '$end_date'";
						$query_title[]=con('global_table_title_customer_tickets_between').": ". $start_date." and ".$end_date;

						} else {
						$query_title[]=con('global_table_title_customer_login_notreq');
						}

					$table_opt='city';

			} else if ($_POST['submit']=='search_event') { // Search on Customers who have ordered for an Event

					// ToDo: Currently only accepts one event
					// change to allow multiple event selection?
					// $query_type in the Build Queries '3' section currently doesn't accpet any addition queries

					$results_table = '3';
					list ($eventID, $eventName) = split("\\|", $data['event_ids']);

					if (isset($eventID)) {
					/*
						$query_type[]="SELECT `U`.*, `ti`.order_user_id, `ti`.order_date, count(DISTINCT `s`.seat_order_id) AS Order_Nos
							FROM `Order` as `ti`, `User` as `U`, `seat` as `s`
							WHERE `s`.seat_event_id IN (22)
							AND `U`.user_id = `s`.seat_user_id
							GROUP BY `U`.user_id";
					*/
						$query_title[]=con('global_table_title_ordered_events').": ".$eventName;
						}

					//$eventIDS = implode(',', $eventID);
					$eventIDS=$eventID;
					$table_opt='ttl_orders';

			} else if ($_POST['update']=='page'){
				// Returning from a page no. of rows update, do nothing & carry on

			} else {

				if (!isset($_SESSION['currentpage']) && !is_numeric($_SESSION['currentpage'])) { //Not a page number update so flag error
					addWarning(con('msg_search_type_not_found'));
					return false;
				}
			}

			if (isset($query_type)) {

				if ($results_table == '0') {  // Build using the general query
					$query="SELECT auth.active, User.*
						FROM User LEFT JOIN auth ON auth.user_id = User.user_id
						WHERE User.user_status !=1 AND ". implode("\n AND ",$query_type)." ORDER BY User.user_lastname ASC, User.user_firstname ASC, User.user_status ASC";

					} elseif ($results_table == '1'){  // Build using the POS/Member/Guest query

						$query="SELECT * FROM User WHERE user_email IN(SELECT user_email FROM User GROUP BY user_email HAVING count(user_email)>1) ORDER BY user_email, user_status ASC";

					} elseif ($results_table == '2') {  // Build using the tickets query

						$query="SELECT  `U`.*, `ti`.order_user_id, `ti`.order_date
							FROM `Order` as `ti`, `User` as `U`
							WHERE `ti`.order_user_id=`U`.user_id AND ". implode("\n AND ",$query_type)."
							GROUP BY `U`.user_id";

						//echo "<br />".$query."<br />";  //Check the query build
					} elseif ($results_table == '3') {  // Build using the order query

						$query="SELECT `U`.*, `ti`.order_user_id, `ti`.order_date, count(DISTINCT `s`.seat_order_id) AS ttl_orders
							FROM `Order` as `ti`, `User` as `U`, `seat` as `s`
							WHERE `s`.seat_event_id IN (".$eventIDS.")
							AND `U`.user_id = `s`.seat_user_id
							GROUP BY `U`.user_id";

						//echo "<br />".$query."<br />";  //Check the query build
					}

			} else {
				$query="SELECT auth.active, User.* FROM User LEFT JOIN auth ON auth.user_id = User.user_id WHERE User.user_status !=1 ORDER BY User.user_lastname ASC, User.user_firstname ASC, User.user_status ASC";  // Otherwise go for everyone!
			}
					//echo "<br />".$query."<br />";  //Check the query build

				//
				// Take queries and connect to database
				// Then pass onto table to display
				//

				if (isset($_SESSION['results'])) {

					$query=$_SESSION['results'];
					$table_opt=$_SESSION['table_opt'];
					unset($_SESSION['results, table_opt']);
					$results_table = '0';
					}

				if(!$res=ShopDB::query($query)){
					user_error(shopDB::error());
					return;
					}

				if (!ShopDB::num_rows($res)) {
					addWarning(con('msg_no_result'));
					$this->searchForm();
					return;
					} else {$num_results = ShopDB::num_rows($res);
					}

			if ($results_table == '0' || $results_table == '2' || $results_table == '3') {  // Which table to use to display results: '0' = General

				// find out how many rows are in the table
				//$sql = "SELECT COUNT(*) FROM numbers";
				//$result = mysql_query($sql, $conn) or trigger_error("SQL", E_USER_ERROR);
				//$r = mysql_fetch_row($result);
				//$numrows = $num_results[0];
				$numrows = $num_results;

				// number of rows to show per page
				$rowsperpage = $_REQUEST['npp'];

				if (isset($rowsperpage)) {
				$limit = $rowsperpage;
				}else{
				$limit = 10; // How many items to show per page by default if visitor hasn't used dropdown.
				}
				//$rowsperpage = 20; ##
				// find out total pages
				$totalpages = ceil($numrows / $limit);

				// get the current page or set a default
				if (isset($_SESSION['currentpage']) && is_numeric($_SESSION['currentpage'])) {
				   // cast var as int
				   $currentpage = (int) $_SESSION['currentpage'];
				   unset ($_SESSION['currentpage']);
				} else {
				   // default page num
				   $currentpage = 1;
				} // end if

				// if current page is greater than total pages...
				if ($currentpage > $totalpages) {
				   // set current page to last page
				   $currentpage = $totalpages;
				} // end if
				// if current page is less than first page...
				if ($currentpage < 1) {
				   // set current page to first page
				   $currentpage = 1;
				} // end if

				// the offset of the list, based on current page
				$offset = ($currentpage - 1) * $rowsperpage;

				// get the info from the db
				//$sql = "SELECT id, number FROM numbers LIMIT $offset, $rowsperpage";
				//$result = mysql_query($sql, $conn) or trigger_error("SQL", E_USER_ERROR);

				$query2 = ($query." LIMIT $offset, $limit");

				if(!$res=ShopDB::query($query2)){
					user_error(shopDB::error());
					return;
					}

				echo "<table><tr><td>";
				echo "
					<form class='page_rows' name='npp' method='POST' action='{$_SERVER['PHP_SELF']}'>
					<input type=hidden name='page' value='$currentpage'>
					<input type=hidden name='update' value='page'>
					".con('global_table_page_rows')."<select class='form_select' name='npp'> // LX
					<option selected>$limit</option>
					<option value=5>5</option>
					<option value=10>10</option>
					<option value=25>25</option>
					<option value=50>50</option>
					<option value=100>100</option>
					</select>
					<input class='form_hide' type=submit value='Go'>
					</form>
					";
				echo "</td><td>";
				echo $this->page_links($currentpage, $range, $totalpages, $limit);
				echo "</td></tr></table>";

				echo "<form id='frm_searchResults' name='search_results' method='POST' action='{$_SERVER['PHP_SELF']}' >
				<input type='hidden' name='action' value='global_editData'/>\n";  //Form value = global_edit

				echo "<table class='admin_list' width='$this->width' cellspacing='1' cellpadding='4'>\n";
				echo "<tr><td colspan='4' class='admin_list_title'>".con('global_table_results_header').": ".$num_results." ".con('global_table_customers')."</td></tr>";
				foreach ($query_title as $search_criteria){
				echo "<tr><td colspan='4'>".con("$search_criteria")."</td></tr>";
				};

				echo "<tr><th>".con('global_table_id')."</th><th>".con('global_table_name')."</th><th>";

				if (isset($table_opt)) {
					echo con('global_table_'.$table_opt);

				} elseif (isset($table_opt2)) {
						echo con('global_table_'.$table_opt2);
				}
				echo "</th><th>".con('global_table_joined')."</th><th>".con('global_table_last_visit')."</th><th>".con('global_table_qty')."</th><th>".con('global_table_status')."</th><th></th></tr>";

				$alt=0;



				while($row=shopDB::fetch_assoc($res)){
					$flag=1;
					$joinedDate=strtotime($row["user_created"]);
					$lastVisited=strtotime($row["user_lastlogin"]);
					echo "<tr class='admin_list_row_$alt'>
						<td class='admin_list_item'>".$row["user_id"]."</td>
						<td class='admin_list_item'>
						<a class='link' href='{$_SERVER['PHP_SELF']}?action=user_detail&user_id=".$row["user_id"]."'>".
						$row["user_lastname"].", ".$row["user_firstname"]."
						</a>
						</td>
						<td class='admin_list_item'>";
							if (isset($table_opt) && $table_opt !='ttl_orders') {
								echo $row["user_".$table_opt];
								} else {
								echo $row["ttl_orders"];
							}
							if (isset($table_opt2)) { echo"<br>".$row["user_".$table_opt2]."</td>"; } else {echo "</td>";}
					echo "<td class='admin_list_item'>".date("jS M Y", $joinedDate)."</td>";
					if (!$lastVisited ==""){	//If there is no record of a visit then the the table cell will be empty
						echo "<td class='admin_list_item'>".date("jS M Y", $lastVisited)."</td>";
						} else { echo "<td></td>"; }
					echo "<td class='admin_list_item'>".$row["user_current_tickets"]."</td>
						<td class='admin_list_item'>".$this->print_status($row["user_status"])."</td>" ;


					echo "<td class='admin_list_item' width='100' align='left' nowrap>
						<input type='checkbox' class='case' name='rowID[]' class='case' value=".$row['user_id']." />";

					if ($row["user_current_tickets"]=="0") { //Delete button only available when patrons have no current tickets
						echo $view->show_button("javascript:if(confirm(\"".con('confirm_delete_user')."\")){location.href=\"{$_SERVER['PHP_SELF']}?action=delete_patron&ID={$row["user_id"]}&status={$row["user_status"]}\";}","remove",2,array('tooltiptext'=>"Delete {$row['user_firstname']} {$row['user_lastname']}?"));
						} else {
						echo "<img class='img_spacer' src='../images/../../includes/plugins/advancedsearch/images/spacer.png'/>";
					}
					if ($row["user_email"]) {
						echo $view->show_button("javascript:if(confirm(\"".con('confirm_email_user')."\")) {window.location.href = \"mailto:{$row['user_email']}\";}","email",2,array('tooltiptext'=>"Email {$row['user_email']}?", 'image'=>'../../includes/plugins/advancedsearch/images/mail.png'));
					} else {
						echo "<img class='img_spacer' src='../images/../../includes/plugins/advancedsearch/images/spacer.png'/>";
					}
					echo "</td>";



					echo "</tr>\n";
				  $alt=($alt+1)%2;
				}

				echo "</table>";

				$_SESSION['results'] = $query;
				$_SESSION['table_opt'] =$table_opt;


				echo "<table id='option_buttons'>"; // **** Select ALL and Option Buttons ****
				echo"<tr><td>
				<label><input type='checkbox' value='on' name='allbox' id='selectall' onclick='checkAll()'/>Select/De-select all</label><br />
				</td></tr>";

				echo "<tr></tr>";
				echo "<tr class='admin_value'><td class='admin_value' align='right' colspan='2'>
				<button id='search' class='submit admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Manually edit' style='' name='submit' value='edit_manual' type='submit' disabled='disabled'>".con(btn_manual_edit)."</button>
				<button id='search' class='submit admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Clean it up' style='' name='submit' value='edit_format' type='submit' disabled='disabled'>".con(btn_format_data)."</button>
				<button id='search' class='submit admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Global edit' style='' name='submit' value='edit_global' type='submit' disabled='disabled'>".con(btn_global_edit)."</button>

				</td></tr>";
				echo "<tr></tr>";
				echo "<tr class='admin_value'><td class='admin_value' align='right' colspan='2'>
				<button id='search' class='submit admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Delete customers' style='' name='submit' value='action_delete' type='submit' disabled='disabled'>".con(btn_global_delete)."</button>
				<button id='search' class='submit admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Email everyone' style='' name='submit' value='action_email' type='submit' disabled='disabled'>".con(btn_global_email)."</button>

				</td></tr>";

				echo "</table></form>";
				echo "<table><tr><td>"; // Navigation - bottom
				echo "
				<form class='page_rows' name='npp' method='POST' action='{$_SERVER['PHP_SELF']}'>
				<input type=hidden name='page' value='$currentpage'>
				<input type=hidden name='update' value='page'>
				".con('global_table_page_rows')."<select class='form_select' name='npp'>
				<option selected>$limit</option>
				<option value=5>5</option>
				<option value=10>10</option>
				<option value=25>25</option>
				<option value=50>50</option>
				<option value=100>100</option>
				</select>
				<input class='form_hide' type=submit value='Go'>
				</form>
				";
				echo "</td><td>";
				echo $this->page_links($currentpage, $range, $totalpages, $limit);
				echo "</td></tr></table>";

				return true;

			} elseif ($results_table == '1') { // Which table to use to display results: '1' = Fieldsets based on email address

				echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>
				<input type='hidden' name='action' value='global_editAccount'/>\n";

				$view->form_head(con('frm_title_global_posmerge_update'));
				echo "<tr><td colspan='4' class='admin_list_title'>".con('global_table_results_header').": ".$num_results." ".con('global_table_customers')."</td></tr>";

				echo "<tr><td colspan='2'>".con('frm_notes_global_posMerge_update')."</td></tr>\n";
				echo "<tr><td></td></tr>";


				$set = array();
				while ($record=shopDB::fetch_object($res)) {
					$set[strtolower($record->user_email)][]=$record; // Ensure emails match regardless of case
					}
				echo"<table><tr><td colspan='2'>";
				$count=1;
				foreach ($set as $user_email => $records) {
					$user_member=$user_guest=$user_pos='0';
					echo "<fieldset><table class='admin_form' width='500' cellspacing='1' cellpadding='4'>\n";
					echo "<legend>Email: {$user_email}</legend>\n";
					echo "<tr><th>".con('global_table_id')."</th><th>".con('global_table_name')."</th><th>".con('global_table_city')."</th><th>".con('global_table_joined')."</th><th>".con('global_table_last_visit')."</th><th>".con('global_table_qty')."</th><th>".con('global_table_status')."</th><th></th></tr>";
					foreach ($records as $record) {
							$joinedDate=strtotime($record->user_created);
							$lastVisited=strtotime($record->user_lastlogin);
							echo "<tr class='admin_list_row_$alt'>
							<td class='admin_list_item'>".$record->user_id."</td>
							<td class='admin_list_item'>
							<a class='link' href='{$_SERVER['PHP_SELF']}?action=user_detail&user_id=".$record->user_id."'>".
							$record->user_lastname.", ".$record->user_firstname."
							</a>
							</td>
							<td class='admin_list_item'>".$record->user_city."</td>
							<td class='admin_list_item'>".date("jS M Y", $joinedDate)."</td>";
						if (!$lastVisited ==""){	//If there is no record of a visit then the the table cell will be empty
							echo "<td class='admin_list_item'>".date("jS M Y", $lastVisited)."</td>";
							} else { echo "<td></td>"; }
						echo "<td class='admin_list_item'>".$record->user_current_tickets."</td>
							<td class='admin_list_item'>".$this->print_status($record->user_status)."</td>" ;
						echo "</tr>\n";
						if ($record->user_status ==4){$user_pos=$record->user_id;}
						if ($record->user_status ==3){$user_guest=$record->user_id;}
						if ($record->user_status ==2){$user_member=$record->user_id;}

					}
					echo  "<input type='hidden' name='user_pos_id[$count]' value='".$user_pos."'/>\n";
					echo  "<input type='hidden' name='user_guest_id[$count]' value='".$user_guest."'/>\n";
					echo  "<input type='hidden' name='user_member_id[$count]' value='".$user_member."'/>\n";
					echo "<tr><td colspan='5' class='admin_name'>".con('fnc_posMerge_select_userfnc_posMerge_select_user')."</td>
					<td colspan='2' class='admin_value'><input type='checkbox' class='case' name='ref_id[$count]' value='".$count."'></td></tr>";
					echo "</table></fieldset><br />\n";
					$count ++;
				}

			//	$view->form_foot(2,'','Merge');
					echo "<tr class='admin_value'><td class='admin_value' align='center' >";

					echo	"<button id='search' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Job1' style=''  type='submit' onClick=\"return confirmSubmit('".con('confirm_sure')."')\">".con('btn_merge')."</button>";
					echo	"<button id='cancel' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Cancel' style='' name='cancel'  onClick=\"return confirmSubmit('".con('confirm_cancel')."')\" onclick=\"window.location = '{$_SERVER['PHP_SELF']}'\">".con('btn_cancel')."</button>
					</td></tr>";

				echo "</table>";
				echo "</form>";

				return true;
			}
	}

	function db_check (){

		if (ShopDB::begin('Update User')) {

		$query="SELECT `user_id`, `user_order_total` As user_orders, `user_current_tickets` As current_tickets, `user_total_tickets` AS total_tickets,
						(SELECT count(s.seat_user_id) FROM `seat` AS s WHERE u.user_id=s.seat_user_id) as seats,
						(SELECT count(o.order_user_id) FROM `order` AS o WHERE u.user_id=o.order_user_id) as orders
						FROM `user` AS u Group BY u.user_id";

		 if(!$res=ShopDB::query($query)){
        return self::_abort('check_user_fail');
      }

		$query="UPDATE `user` SET ";
					$v=0;
					while($row=shopDB::fetch_assoc($res)){
						if ($row['user_orders']<>$row['orders']) {
							$query1 .="WHEN ".$row['user_id']." THEN ".$row['orders']." ";
							$v++;
						}
						if ($row['current_tickets']<>$row['seats']) {
							$query2 .="WHEN ".$row['user_id']." THEN ".$row['seats']." ";
							$v++;
						}
						if ($row['total_tickets']<>$row['seats']) {
							$query3 .="WHEN ".$row['user_id']." THEN ".$row['seats']." ";
							$v++;
						}
						if ($v>0) {
							$id[]=$row['user_id'];
							$v=0;
							} else {$v=0;}
					}
					$user_count=$id;
					$id=implode(',', $id);

					if (!count($id)>=1) {
						addWarning(con('msg_dbCheck_orders_fine'));
						$this->searchForm();
						return;
					}

					if ($query1){
							$query .="`user_order_total` = (CASE `user_id` ";
							$query .=$query1." ELSE `user_order_total` END)";
						}
					if ($query1 && $query2) {
						$query .=", ";
					}
					if ($query2) {
						$query .=" `user_current_tickets` = (CASE `user_id` ";
						$query .=$query2." ELSE `user_current_tickets` END)";
					}
					if ($query1 && $query3|| $query2 && $query3) {
						$query .=", ";
					}
					if ($query3) {
						$query .=" `user_total_tickets` = (CASE `user_id` ".$query3." ELSE `user_total_tickets` END)";
					}

					$query .="WHERE `user_id` IN (".$id.")";

					//echo $query."<BR>"; //Checkpoint

					if(!$res=ShopDB::query($query)){
						addWarning(con('msg_user_orders_seats_update_failed'));
						return false;
					}

				if (!ShopDB::commit('event Saved')){
					addWarning(con('msg_update_failed'));

				}
			echo con('msg_users_updated_total').count($user_count).".<br>";
		}
	}

	function global_email (&$data, $view){ //Added by Lxsparks 04/13
	global $_SHOP;
	//print_r($_SHOP);

	if (is_array($_POST['rowID'])) {
		$id=($_POST['rowID']);
		print_r($id);
	}

			$num_results=count($id);
			$users=serialize($id);

			echo "<form method='POST' action='{$_SERVER['PHP_SELF']}'>
				<input type='hidden' name='action' value='global_action_email'/>\n
				<input type='hidden' name='users' value='$users'/>\n";

			echo "<table>";
			echo "<tr><td class='admin_list_title'>".con('global_table_emailing').": ".$num_results." ".con('global_table_customers')."</td></tr>";
			echo "<tr><td>".con('global_table_notes_emailing')."</td></tr>\n";

			// Have to PASS user ID values as an array to the form_r

			echo "<tr><td><table>";

			//echo "	<tr><th>".con('global_table_data')."</th><th>".con('global_table_info')."</th><th></th>";

			echo "	<tr><td>".con('email_from')."</td><td><input type='text' name='email_from' size='50' value ='".$_SHOP->organizer_email."' /></td>
				<td></td></tr>";

			echo "	<tr><td>".con('email_from_name')."</td><td><input type='text' name='email_from_name' size='50' value ='".$_SHOP->organizer_name."' /></td>
				<td></td></tr>";

			echo "	<tr><td>".con('email_to')."</td><td><input type='text' name='email_to' size='50' value ='".$_SHOP->organizer_email."' /></td>
				<td><input type='radio' name='email_opt' value='1'>".con('email_option')."</td></tr>";

			echo "	<tr><td>".con('email_to_name')."</td><td><input type='text' name='email_to_name' size='50' value ='".$_SHOP->organizer_name."' /></td>
				<td></td></tr>";

			echo "	<tr><td>".con('email_cc')."</td><td><input type='text' name='email_cc' size='50' value ='' /></td>
				<td><input type='radio' name='email_opt' value='2'>".con('email_option')."</td></tr>";

			echo "	<tr><td>".con('email_bcc')."</td><td><input type='text' name='email_bcc' size='50' value ='' /></td>
			<td><input type='radio' name='email_opt' value='3' checked='checked'>".con('email_option')."</td></tr>";

			echo " <tr class='blank_row'></tr>";

			echo "	<tr><td>".con('email_title')."</td><td><input type='text' name='email_title' size='50' value ='' /></td></tr>";

			echo " <tr class='blank_row'></tr>";

			echo "	<tr><td>".con('email_msg')."</td><td><textarea name='email_msg' col='50' rows='15' value ='' id='email_msg'/></textarea></td></tr>";

			echo "</td></tr></table>";

			echo "<tr class='admin_value'><td class='admin_value' align='center' >";

			echo	"<button id='search' class='admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Job1' style='' name='submit' value='update_global' type='submit'
				onClick=\"return confirmSubmit('".con('confirm_email')."')\">".con('btn_email_users')."</button>";
			echo	"<button id='cancel' class=' admin-button ui-state-default ui-corner-all link admin-button-text ' alt='Cancel' style='' name='action' value='cancel' onClick=\"return confirmSubmit('".con('confirm_cancel')."')\" >".con('btn_form_cancel')."</button>
					</td></tr>";

			echo "</table>";
			echo "</form>";

	}

	function global_emailR (&$data, $view){ //Added by Lxsparks 04/13
		require_once('classes/email.swift.sender.php');

		$id = implode(',', unserialize($data['users']));

		$emailFrom[$data['email_from']]=$data['email_from_name'];
		$emailTo[$data['email_to']]=$data['email_to_name'];

		$query = "SELECT user_email, user_firstname, user_lastname FROM user WHERE user_id IN ({$id})";

		if(!$res=ShopDB::query($query)){
			addWarning(con('msg_email_find_fail'));
			return false;
		}

		while($row=shopDB::fetch_assoc($res)){
			if ($row['user_email']) {
				$e=$row['user_email'];
				//$n=$row['user_firstname']. " ".$row['user_lastname']; // ToDo: Fix 'email'=>'name'
				//$groupAddrs[$e]=$n;	// Swift Mailer fails on 3 or more array pairs (e.g. "sample@box.com"=>"John Smith", "sample2@box.com"=>"Sarah Jones"...)
				$groupAddrs[]=$e;
			}
		}

		// Checked $opts = '1' - To, '2' - CC, '3' - BCC
		switch($data['email_opt']) {
			case 1:
				$emailTo = $groupAddrs;
				if (!empty($data['email_cc'])) {
					$emailCC=clean_customerEmail($data['email_cc']);
				}
				if (!empty($data['email_bcc'])) {
					$emailBCC=clean_customerEmail($data['email_cc']);
				}
				break;

			case 2:
				$emailCC=$groupAddrs;
				if (!empty($data['email_to'])) {
					$emailTo[$data['email_to']]=$data['email_to_name'];
				} else {
					$emailTo[$_SHOP['organizer_email']]=$_SHOP['organizer_name'];
				}
				if (!empty($data['email_bcc'])) {
					$emailBCC=clean_customerEmail($data['email_bcc']);
				}
				break;

			case 3:
				$emailBCC=$groupAddrs;
				if (!empty($data['email_to'])) {
					$emailTo[$data['email_to']]=$data['email_to_name'];
				} else {
					$emailTo[$_SHOP['organizer_email']]=$_SHOP['organizer_name'];
				}
				if (!empty($data['email_cc'])) {
					$emailCC=clean_customerEmail($data['email_cc']);
				}
				break;
		}

		$message = Swift_Message::newInstance()
			->setFrom($emailFrom)
			->setSubject($data['email_title'])
			->setBody($data['email_msg'])
			->setTo($emailTo)
			->setCC($emailCC)
			->setBCC($emailBCC);
	/*
		echo "<br/><br/>";
		echo "To: "; print_r($emailTo);
		echo "<br/><br/>";
		echo "From: "; print_r($emailFrom);
		echo "<br/><br/>";
		echo "CC: "; print_r($emailCC);
		echo "<br/><br/>";
		echo "BCC: "; print_r($emailBCC);
		echo "<br/><br/>";
		echo "Title: ".($data['email_title']);
		echo "<br/><br/>";
		echo"Msg: ".($data['email_msg']);
		echo "<br/><br/>";
		echo "Group: "; print_r($group);
		echo "<br/><br/>";
	*/
		// ToDo: add return confirmation/fail from Swift Sender
		// ToDo: add email logging as per system emails
		EmailSwiftSender::send($message, "", $log, $failedAddr, $data);

	}

	function doSearchView_Items($items){
    $items[$this->plugin_acl] ='Advanced Search/Edit|admin';
    return $items;
  }

  function doSearchView_Draw($id, $view){
    global $_SHOP;
    // this section works the same way as the draw() function insite the views it self.

	?>
		<link rel="stylesheet" type="text/css" href="../includes/plugins/advancedsearch/style.css" type="text/css">
		<script type="text/javascript" src="../includes/plugins/advancedsearch/scripts/scripts.js"></script>
	<?php

		if ($_POST['action']=='global_search') { // Test: no action so carry on!
			$this->searchForm_R($_POST, $view);
			}

		elseif($_POST['submit']=='action_delete'){
		if ($this->delete_customerR($_POST, $view)) return;
		}

		elseif($_POST['submit']=='action_email'){
		if ($this->global_email($_POST, $view)) return;
		}

		elseif($_GET['submit']=='1'){
		$_SESSION['currentpage'] = $_GET['currentpage'];
		if ($this->searchForm_R($_POST, $view)) return;
		}

		elseif($_GET['action']=='cancel'){
			$this->searchForm_R($_POST, $view);
		}

		elseif($_POST['update']=='page'){
		$_SESSION['npp'] = $_POST['npp'];
		if ($this->searchForm_R($_POST, $view)) return;
		}

		elseif($_POST['action']=='global_editData'){
		//$_SESSION['npp'] = $_POST['npp'];
		if ($this->global_editCustomer($_POST, $view)) return;
		}

		elseif($_REQUEST['action']=='global_update'){
		if ($this->global_editCustomerR($_POST, $view)) return;
		}

		elseif($_REQUEST['action']=='global_update_global'){
		if ($this->global_editCustomerR($_POST, $view)) return;
		}

		elseif($_REQUEST['action']=='delete_patron'){
		if ($this->delete_customerR($_POST, $view)) return;
		}

		elseif($_REQUEST['action']=='global_editAccount'){
		if ($this->global_editAccountR($_POST, $view)) return;
		}

		elseif($_REQUEST['action']=='global_action_email'){
		if ($this->global_emailR($_POST, $view)) return;
		}

		elseif($_REQUEST['action']=='dbCheck'){
		if ($this->db_check($_POST, $view)) return;
		}

		else {
		$this->searchForm($_POST, $view);
		}


    if ($id==$this->plugin_acl) {
      // do your tasks here.  // Is this part extending
		}
  }
}
?>