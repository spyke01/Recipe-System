<? 
/***************************************************************************
 *                               ajax.php
 *                            -------------------
 *   begin                : Tuseday, March 14, 2006
 *   copyright            : (C) 2006 Fast Track Sites
 *   email                : sales@fasttracksites.com
 *
 *
 ***************************************************************************/

/***************************************************************************
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the <organization> nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 ***************************************************************************/
	include 'includes/header.php';
	
	$actual_id = parseurl($_GET['id']);
	$actual_action = parseurl($_GET['action']);
	$actual_value = parseurl($_GET['value']);
	$actual_type = parseurl($_GET['type']);
	
	//================================================
	// Main updater and get functions
	//================================================
	// Update an item in a DB table
	if ($actual_action == "updateitem") {
		$item = parseurl($_GET['item']);
		$table = parseurl($_GET['table']);
		$updateto = ($item == "datetimestamp" || $item == "date_ordered" || $item == "date_shipped") ? strtotime(keeptasafe($_REQUEST['value'])) : keeptasafe($_REQUEST['value']);
		
		$sql = "UPDATE `" . DBTABLEPREFIX . $table . "` SET " . $item ." = '$updateto' WHERE " . $tableabrev . "_id = '" . $actual_id . "'";
		
		// Only admins or Mods should be able to get whatever they want things
		if ($_SESSION['user_level'] == MOD || $_SESSION['user_level'] == ADMIN) {
			$result = mysql_query($sql);
			echo stripslashes($updateto);	
		}		
		else {
			// Run checks to verify access rights
		 	$authorized = 0;
			
			if ($table == "users" && $item == "language") { $authorized = 1; }
			if ($table == "systems" && $item == "qty") { $authorized = 1; }
			
			if ($authorized) {
				$result = mysql_query($sql);
				echo stripslashes($updateto);
			}
		}			
	}
	// Get an item from a DB table
	elseif ($actual_action == "getitem") {
		$item = parseurl($_GET['item']);
		$table = parseurl($_GET['table']);
		
		$sql = "SELECT " . $item . " FROM `" . DBTABLEPREFIX . $table . "` WHERE id = '" . $actual_id . "'";
		$result = mysql_query($sql);
		
		$row = mysql_fetch_array($result);
		mysql_free_result($result);
		
		if ($_SESSION['user_level'] == MOD || $_SESSION['user_level'] == ADMIN) {
				if ($item == "datetimestamp" || $item == "date_ordered" || $item == "date_shipped") { 
					$result =  (trim($row[$item]) != "") ? @gmdate('m/d/Y h:i A', $row[$item] + (3600 * '-5.00')) : ""; 
					echo $result;
				}
				elseif ($item == "items_total" || $item == "total_cost" || $item == "tax" || $item == "price") { 
					echo formatCurrency($row[$item]);
				}
				else { echo bbcode($row[$item]); }	
		}
	}	
	// Delete a row from a DB table
	elseif ($actual_action == "deleteitem") {
		$table = parseurl($_GET['table']);		
		
		// Kill the chosen row in the chosen DB
		$sql = "DELETE FROM `" . DBTABLEPREFIX . $table . "` WHERE " . $table . "_id = '" . $actual_id . "'";
		$result = mysql_query($sql);		
	}
	
	//================================================
	// Update our recipe categorys in the database
	//================================================
	// Post a partcat
	elseif ($actual_action == "postrecipecats") {
		$name = keeptasafe($_POST['newrecipecatsname']);	
		
		$sql = "INSERT INTO `" . DBTABLEPREFIX . "recipecats` (`name`) VALUES ('" . $name . "')";
		$result = mysql_query($sql);

		$sql = "SELECT * FROM `" . DBTABLEPREFIX . "recipecats` ORDER BY name ASC";
		$result = mysql_query($sql);
		
		$content = printRecipeCatsTable();	
		
		echo $content;
	}
	
	//================================================
	// Add a new part to the database
	//================================================
	elseif ($actual_action == "postrecipes") {
		$name = keeptasafe($_POST['newrecipesname']);	
		
		$sql = "INSERT INTO `" . DBTABLEPREFIX . "recipes` (`name`) VALUES ('" . $name . "')";
		$result = mysql_query($sql);

		$content = printRecipesTable(-1);	
		
		echo $content;
	}
	
	//================================================
	// Select recipes for a certain model from the database
	//================================================
	elseif ($actual_action == "searchrecipes") {	
		$actual_id = ($actual_id == "" || !is_numeric($actual_id)) ? "-1" : $actual_id;	
		echo printRecipesTable($actual_id);
	}
	
	//================================================
	// Outputs the form to the lytebox popup
	//================================================
	if ($actual_action == "showCreateUserEdit") {	
		$content = "";
		
		$content .= "
						<form id=\"createUserForm\" class=\"plasmaForm\" action=\"" . $menuvar['SETTINGS'] . "\" method=\"post\" onSubmit=\"return false;\">
							<table class=\"contentBox\" cellpadding=\"1\" cellspacing=\"1\">
								<tr class=\"title1\">
									<td colspan=\"2\">Create a New Account</td>
								</tr>
								<tr> 
									<td class=\"title2\">Email Address</td>
									<td class=\"row1\"><div id=\"emailaddressCheckerHolder\" class=\"floatRight\"><a style=\"cursor: pointer; cursor: hand; color: red;\" onclick=\"new Ajax.Updater('emailaddressCheckerHolder', 'ajax.php?action=checkemailaddress&value=' + document.newUserForm.email_address.value, {asynchronous:true});\">[Check]</a></div><input name=\"email_address\" type=\"text\" size=\"60\" id=\"email_address\" class=\"required validate-email\" value=\"" . keeptasafe($_POST['email_address']) . "\" /></td>
								</tr>
								<tr> 
									<td class=\"title2\">Password</td>
									<td class=\"row1\"><input name=\"password1\" type=\"password\" size=\"60\" id=\"password1\" class=\"required validate-password\" value=\"\" /></td>
								</tr>
								<tr> 
									<td class=\"title2\">Confirm Password</td>
									<td class=\"row1\"><input name=\"password2\" type=\"password\" size=\"60\" id=\"password2\" class=\"required validate-password-confirm\" value=\"\" /></td>
								</tr>
								<tr> 
									<td class=\"title2\">First Name</td>
									<td class=\"row1\"><input name=\"first_name\" type=\"text\" size=\"60\" id=\"first_name\" class=\"required validate-alpha\" value=\"" . keeptasafe($_POST['first_name']) . "\" /></td>
								</tr>
								<tr> 
									<td class=\"title2\">Last name</td>
									<td class=\"row1\"><input name=\"last_name\" type=\"text\" size=\"60\" id=\"last_name\" class=\"required validate-alpha\" value=\"" . keeptasafe($_POST['last_name']) . "\" /></td>
								</tr>
								<tr> 
									<td class=\"title2\">Would you like to receive emails from us?</td>
									<td class=\"row1\"><input name=\"on_email_list\" type=\"checkbox\" value=\"1\" /></td>
								</tr>
							</table>
							<script type=\"text/javascript\">
								var valid = new Validation('createUserForm', {immediate : true, useTitles:true});
								Validation.addAllThese([
									['validate-password', 'Your password must be more than 6 characters and not be \'password\' or the same as your username.', {
										minLength : 7,
										notOneOf : ['password','PASSWORD','1234567','0123456'],
										notEqualToField : 'username'
									}],
									['validate-password-confirm', 'Your passwords do not match, please re-enter them.', {
										equalToField : 'password1'
									}]
								]);
							</script>
						<br />
						<input type=\"button\" name=\"submit\" value=\"Create User\" onClick=\"ajaxSubmitCreateUser(document.forms[0], '" . $actual_id . "', '1');\" /> 
					</form>
					<br />
					<span id=\"createUserFormSpinner\" style=\"display: none;\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/indicator.gif\" alt=\"spinner\" /></span><div id=\"updateMe\"></div>";
		
		$page->setTemplateVar('PageContent', $content);
		
		include "themes/default/popUpTemplate.php";
	}

	//================================================
	// Updates the DB using the values from the 
	// lytebox popup form 
	//================================================
	if ($actual_action == "submitCreateUser") {	
		$current_time = time();	
		$postpassword = keepsafe($_POST['password1']);
		$postfirst_name = keepsafe($_POST['first_name']);
		$postlast_name = keepsafe($_POST['last_name']);
		$postemail_address = keepsafe($_POST['email_address']);
		$poston_email_list = keepsafe($_POST['on_email_list']);
		$poston_email_list = ($poston_email_list != 1) ? 0 : 1;
		
		$sql_email_check = mysql_query("SELECT email_address FROM `" . USERSDBTABLEPREFIX . "users` WHERE email_address='$postemail_address'");
		 
		$email_check = mysql_num_rows($sql_email_check);
		 
		if($email_check > 0){
			$content .= $T_FIX_ERRORS . "<br />";
			$content .= "This email address has already been used.<br />";
			echo "<span class=\"result-failure\">" . $content . "</span>";
		}
		else {
			//=====================================================
			// Everything has passed both error checks that we 
			// have done. It's time to create the account!
			//=====================================================
		
			$db_password = md5($postpassword);
			
			// generate SQL.
			$sql = "INSERT INTO `" . USERSDBTABLEPREFIX . "users` (first_name, last_name, email_address, password, users_on_email_list, signup_date) VALUES('" . $postfirst_name . "', '" . $postlast_name . "', '" . $postemail_address . "', '" . $db_password . "', '" . $poston_email_list . "', '" . $current_time . "')";
			$result = mysql_query($sql);
		
			if ($result) { echo "<span class=\"result-success\">User was created. You may now close this popup.</span>"; }
			else { echo "<span class=\"result-failure\">Your User could not be created. Please try again.</span>"; }
			
		}
	}
	
	//================================================
	// Echo's nothing so that a div or span gets cleared
	//================================================
	elseif ($actual_action == "clearIt") {	
		echo "";
	}
	
	//================================================
	// Echo's a spinner
	//================================================
	elseif ($actual_action == "showSpinner") {	
		echo "<img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/indicator.gif\" alt=\"spinner\" />";
	}

	//================================================
	// Checks to see if a username is already in use
	//================================================
	if ($actual_action == "checkusername") {	
		$sql_username_check = mysql_query("SELECT username FROM `" . USERSDBTABLEPREFIX . "users` WHERE username='" . $actual_value . "'");
	
		if (mysql_num_rows($sql_username_check) > 0 && trim($actual_value) != "") {
			echo "<a style=\"cursor: pointer; cursor: hand; color: red;\" onclick=\"new Ajax.Updater('usernameCheckerHolder', 'ajax.php?action=checkusername&value=' + document.newUserForm.username.value, {asynchronous:true});\">[In Use]</a>";
		}
		else {
			echo "<a style=\"cursor: pointer; cursor: hand; color: green;\" onclick=\"new Ajax.Updater('usernameCheckerHolder', 'ajax.php?action=checkusername&value=' + document.newUserForm.username.value, {asynchronous:true});\">[Available]</a>";
		}
	}
	
	//================================================
	// Checks to see if an email address is already in use
	//================================================
	elseif ($actual_action == "checkemailaddress") {	
		$sql_email_addrers_check = mysql_query("SELECT email_address FROM `" . USERSDBTABLEPREFIX . "users` WHERE email_address='" . $actual_value . "'");
	
		if (mysql_num_rows($sql_email_addrers_check) > 0 && trim($actual_value) != "") {
			echo "<a style=\"cursor: pointer; cursor: hand; color: red;\" onclick=\"new Ajax.Updater('emailaddressCheckerHolder', 'ajax.php?action=checkemailaddress&value=' + document.newUserForm.email_address.value, {asynchronous:true});\">[In Use]</a>";
		}
		else {
			echo "<a style=\"cursor: pointer; cursor: hand; color: green;\" onclick=\"new Ajax.Updater('emailaddressCheckerHolder', 'ajax.php?action=checkemailaddress&value=' + document.newUserForm.email_address.value, {asynchronous:true});\">[Available]</a>";
		}
	}
	
	else {
		// Do Nothing
	}

?>
