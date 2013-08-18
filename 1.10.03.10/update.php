<? 
/***************************************************************************
 *                               install.php
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
	ini_set('arg_separator.output','&amp;');
	//error_reporting(E_ALL);
	//ini_set('display_errors', '1');
	
	// Set up our installer
	define('UPDATER_SCRIPT_NAME', 'Recipe System');
	
	// Inlcude the needed files
	include_once ('includes/constants.php');
	if (substr(phpversion(), 0, 1) == 5) { include_once ('includes/php5/pageclass.php'); }
	else { include_once ('includes/php4/pageclass.php'); }

	// Instantiate our page class
	$page = &new pageClass;

	// Handle our variables
	$requested_step = $_GET['step'];

	$actual_step = ($requested_step == "" || !isset($requested_step)) ? 1 : keepsafe($requested_step);
	$page_content = "";
	$failed = 0;
	$totalfailure = 0;
	$failed = array();
	$failedsql = array();
	$currentdate = time();

	
	//========================================
	// Custom Functions for this Page
	//========================================
	function keepsafe($makesafe) {
		$makesafe=strip_tags($makesafe); // strip away any dangerous tags
		$makesafe=str_replace(" ","",$makesafe); // remove spaces from variables
		$makesafe=str_replace("%20","",$makesafe); // remove escaped spaces
		$makesafe = trim(preg_replace('/[^\x09\x0A\x0D\x20-\x7F]/e', '"&#".ord($0).";"', $makesafe)); //encodes all ascii items above #127
		$makesafe = stripslashes($makesafe);
		
		return $makesafe;
	}
	
	function keeptasafe($makesafe) {
		$makesafe=strip_tags($makesafe); // strip away any dangerous tags
		$makesafe = trim(preg_replace('/[^\x09\x0A\x0D\x20-\x7F]/e', '"&#".ord($0).";"', $makesafe)); //encodes all ascii items above #127
		$makesafe = stripslashes($makesafe);
		
		return $makesafe;
	}

	function checkresult($result, $sql, $table) {
		global $failed;
		global $failedsql;
		global $totalfailure;
		
		if (!$result || $result == "") {
			$failed[$table] = "failed";
			$failedsql[$table] = $sql;
			$totalfailure = 1;
		}  
		else {
			$failed[$table] = "succeeded";
			$failedsql[$table] = $sql;
		}	
	}
	
	//========================================
	// Build our Page
	//========================================
	switch ($actual_step) {
		case 1:
			$page->setTemplateVar("PageTitle", UPDATER_SCRIPT_NAME . " Step 1 - Update Database Tables");	
			
			// Print this page
			$page_content = "
					<h2>Welcome to the Fast Track Sites Script Updater</h2>
					Thank you for downloading the " . UPDATER_SCRIPT_NAME . " this page will walk you through the update procedure.
					<br /><br />
					<h2><span class=\"iconText38px\"><img src=\"themes/installer/icons/table_38px.png\" alt=\"Update Tables\" /></span> <span class=\"iconText38px\">Update database Tables</span></h2>
					Press Next to update the database tables.
					<br /><br />
					<a href=\"update.php?step=2\" class=\"button\">Next</a>";
			break;
		case 2:
			$page->setTemplateVar("PageTitle", UPDATER_SCRIPT_NAME . " Step 2 - Finish");	
			
			include('_db.php');
			
			// Update our Database Tables	
			$sql = "ALTER TABLE `" . DBTABLEPREFIX . "recipecats` 
					CHANGE `recipecats_id` `id` MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT ,
					CHANGE `recipecats_name` `name` VARCHAR( 50 ) NOT NULL ,
					CHANGE `recipecats_description` `description` TEXT NOT NULL;";
			$result = mysql_query($sql);
			checkresult($result, $sql, "recipecats");
			
			$sql = "ALTER TABLE `" . DBTABLEPREFIX . "recipes` 
					CHANGE `recipes_id` `id` MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT ,
					CHANGE `recipes_cat_id` `cat_id` MEDIUMINT( 8 ) NOT NULL DEFAULT '0',
					CHANGE `recipes_name` `name` VARCHAR( 250 ) NOT NULL ,
					CHANGE `recipes_description` `description` TEXT NOT NULL ,
					CHANGE `recipes_preperation` `preperation` TEXT NOT NULL ,
					CHANGE `recipes_preperation_time` `preperation_time` TEXT NOT NULL ,
					CHANGE `recipes_servings` `servings` MEDIUMINT( 8 ) NOT NULL DEFAULT '0',
					CHANGE `recipes_image_full` `image_full` VARCHAR( 250 ) NOT NULL ,
					CHANGE `recipes_image_thumb` `image_thumb` VARCHAR( 250 ) NOT NULL;";
			$result = mysql_query($sql);
			checkresult($result, $sql, "recipes");
			
			$sql = "ALTER TABLE `" . DBTABLEPREFIX . "config` 
					CHANGE `config_name` `name` VARCHAR( 255 ) NOT NULL ,
					CHANGE `config_value` `value` VARCHAR( 255 ) NOT NULL;";
			$result = mysql_query($sql);
			checkresult($result, $sql, "config");
			
			$sql = "ALTER TABLE `" . DBTABLEPREFIX . "recipes_ingredients` 
					CHANGE `recipes_ingredients_id` `id` MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT ,
					CHANGE `recipes_ingredients_recipe_id` `recipe_id` MEDIUMINT( 8 ) NOT NULL DEFAULT '0',
					CHANGE `recipes_ingredients_qty` `qty` VARCHAR( 250 ) NOT NULL ,
					CHANGE `recipes_ingredients_name` `name` VARCHAR( 250 ) NOT NULL;";
			$result = mysql_query($sql);
			checkresult($result, $sql, "recipes_ingredients");
			
			$sql = "ALTER TABLE `" . USERSDBTABLEPREFIX . "users` 
					DROP `users_cat_id` ,
					DROP `users_activation` ,
					DROP `users_tech` ,
					DROP `users_preffered_client` ,
					DROP `users_on_email_list` ,
					DROP `users_clms_title` ,
					DROP `users_title` ,
					DROP `users_company` ,
					DROP `users_gender` ,
					DROP `users_style` ,
					DROP `users_country` ,
					DROP `users_info` ,
					DROP `users_sig` ,
					DROP `users_attachsig` ,
					DROP `users_aim` ,
					DROP `users_yim` ,
					DROP `users_msn` ,
					DROP `users_birthday` ,
					DROP `users_avatar` ,
					DROP `users_avatar_type` ,
					DROP `users_last_login` ,
					DROP `users_posts` ,
					DROP `users_found_us_through` ,
					DROP `users_password_reset_code`,
					CHANGE `users_id` `id` MEDIUMINT( 8 ) NOT NULL AUTO_INCREMENT ,
					CHANGE `users_username` `username` VARCHAR( 255 ) NOT NULL ,
					CHANGE `users_password` `password` VARCHAR( 255 ) NOT NULL ,
					CHANGE `users_first_name` `first_name` VARCHAR( 50 ) NOT NULL ,
					CHANGE `users_last_name` `last_name` VARCHAR( 50 ) NOT NULL ,
					CHANGE `users_email_address` `email_address` VARCHAR( 255 ) NOT NULL ,
					CHANGE `users_website` `website` VARCHAR( 255 ) NOT NULL ,
					CHANGE `users_signup_date` `signup_date` INT( 11 ) NULL DEFAULT NULL ,
					CHANGE `users_notes` `notes` TEXT NOT NULL ,
					CHANGE `users_user_level` `user_level` TINYINT( 1 ) NOT NULL DEFAULT '0',
					CHANGE `users_active` `active` TINYINT( 1 ) NOT NULL DEFAULT '1',
					CHANGE `users_language` `language` VARCHAR( 5 ) NOT NULL DEFAULT 'en';";
			$result = mysql_query($sql);
			checkresult($result, $sql, "users");
		
			// Print this page
			$page_content = "
					<h2>Update Database Tables Results</h2>";
			
			if ($totalfailure == 1) {
				$page_content .= "
					<span class=\"actionFailed\"><span class=\"iconText20px\"><img src=\"themes/installer/icons/delete_20px.png\" alt=\"Action Failed\" /></span> <span class=\"iconText20px\">Unable to update database tables.</span></span>";
			}
			else {
				$page_content .= "
					<span class=\"actionSucceeded\"><span class=\"iconText20px\"><img src=\"themes/installer/icons/check_20px.png\" alt=\"Action Succeeded\" /></span> <span class=\"iconText20px\">Successfully updated database tables.</span></span>";
			}
			
			$page_content .= "
					<br /><br />
					<h2>Finishing Up</h2>
					Update is now complete, before using the system please make sure and delete this file (update.php) so that it cannot be reused by someone else.
					<br /><br />
					<a href=\"index.php\" class=\"button\">Finish</a>";
			break;	
	}
	
	// Send out the content
	$page->setTemplateVar("PageContent", $page_content);	
	
	include "themes/installer/updatertemplate.php";
?>