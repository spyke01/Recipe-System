<? 
/***************************************************************************
 *                               index.php
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
// If the db connection file is missing we should redirect the user to install page
if (!file_exists('_db.php')) {
	header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/install.php");	
	exit();
}

include 'includes/header.php';

$requested_page_id = $_GET['p'];
$requested_section = $_GET['s'];
$requested_id = $_GET['id'];
$requested_action = $_GET['action'];
$requested_action2 = $_GET['action2'];
$requested_redirect_to = $_GET['redirect_to'];
$requested_style = $_GET['style'];
$requested_value = $_GET['value'];
$requested_recipeCatID = $_GET['recipeCatID'];
$requested_page = $_GET['page'];

$actual_page_id = ($requested_page_id == "" || !isset($requested_page_id)) ? 1 : $requested_page_id;
$actual_page_id = parseurl($actual_page_id);
$actual_section = parseurl($requested_section);
$actual_id = parseurl($requested_id);
$actual_action = parseurl($requested_action);
$actual_action2 = parseurl($requested_action2);
$actual_redirect_to = parseurl($requested_redirect_to);
$actual_style = parseurl($requested_style);
$actual_value = parseurl($requested_value);
$actual_recipeCatID = parseurl($requested_recipeCatID);
$actual_page = parseurl($requested_page);
$page_content = "";

// Warn the user if the install.php script is present
if (file_exists('install.php')) {
	$page_content = "<div class=\"errorMessage\">Warning: install.php is present, please remove this file for security reasons.</div>";
}

// We want to show all of our menus by default
$page->setTemplateVar("pCLm_active", ACTIVE);
$page->setTemplateVar("uOLm_active", ACTIVE);
$page->setTemplateVar("aOLm_active", ACTIVE);

// Get the current theme
$themeDir = $rs_config['ftsrs_theme'];

//========================================
// Logout Function
//========================================
if ($actual_page_id == "logout") {
	define('IN_FTSRS', true);
	include '_db.php';
	include_once ('includes/menu.php');
	include_once ('config.php');
	global $rs_config;
	
	//Destroy Session Cookie
	$cookiename = $rs_config['ftsrs_cookie_name'];
	setcookie($cookiename, false, time()-2592000); //set cookie to delete back for 1 month
	
	//Destroy Session
	session_destroy();
	if(!session_is_registered('first_name')){
		header("Location: " . returnHttpLinks("http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/index.php"));	
		exit();
	}
}

//Check to see if advanced options are allowed or not
if (version_functions("advancedOptions") == true) {
	// If the system is locked, then only a moderator or admin should be able to view it
	if ($_SESSION['user_level'] != ADMIN && $_SESSION['user_level'] != MOD && $rs_config['ftsrs_active'] != ACTIVE) {
		if ($actual_page_id == "login") {
			include 'login.php';
		}
		else {	
			$page->setTemplateVar("PageTitle", 'Currently Disabled');
			$page->setTemplateVar("PageContent", bbcode($rs_config['ftsrs_inactive_msg']));
		}
	}
	else {
		//========================================
		// Admin panel options
		//========================================
		if ($actual_page_id == "admin") {
			// Add breadcrumb pointing home
			$page->addBreadCrumb("Admin", $menuvar['ADMIN']);
			
			if (!$_SESSION['email_address']) { include 'login.php'; }
			else {
				if ($_SESSION['user_level'] == MOD || $_SESSION['user_level'] == ADMIN) {
					if ($actual_section == "" || !isset($actual_section)) {
						include 'admin.php'; 
						$page->setTemplateVar("PageTitle", "Admin Panel");
					}
					elseif ($actual_section == "recipecats") {
						$page->addBreadCrumb("Recipe Categories", $menuvar['RECIPECATS']);
						$page->setTemplateVar("PageTitle", "Recipe Categories");		
						include 'recipecats.php';		
					}
					elseif ($actual_section == "recipes") {
						$page->addBreadCrumb("Recipes", $menuvar['RECIPES']);
						$page->setTemplateVar("PageTitle", "Recipes");		
						include 'recipes.php';		
					}
					elseif ($actual_section == "settings") {
						$page->addBreadCrumb("Settings", $menuvar['SETTINGS']);
						$page->setTemplateVar("PageTitle", "Settings");
						include 'settings.php';				
					}
					elseif ($actual_section == "themes") {
						$page->addBreadCrumb("Themes", $menuvar['THEMES']);
						$page->setTemplateVar("PageTitle", "Themes");		
						include 'themes.php';		
					}
					elseif ($actual_section == "users") {
						$page->addBreadCrumb("Users", $menuvar['USERS']);
						$page->setTemplateVar("PageTitle", "Users");	
						include 'users.php';			
					}
				}
				else { setTemplateVar("PageContent", "You are not authorized to access the admin panel."); }
			}
		}
		elseif ($actual_page_id == "login") {
			include 'login.php';
			$page->setTemplateVar("PageTitle", "Login");	
			if (!isset($_SESSION['userid'])) {
				$page->setTemplateVar("uOLm_active", INACTIVE);
			}
		}
		elseif ($actual_page_id == "register") {
			include 'register.php';		
			$page->setTemplateVar("PageTitle", "Register");		
		}
		elseif ($actual_page_id == "resetpass") {
			include 'resetpass.php';		
			$page->setTemplateVar("PageTitle", "Reset Password");		
		}
		elseif ($actual_page_id == "version") {
			$page->setTemplateVar("PageTitle", "Version Information");	
			$page->addBreadCrumb("Home", $menuvar['HOME']);
			$page->addBreadCrumb("Version Information", "");
			
			include('_license.php');
		
			$page_content .= "
				<div class=\"roundedBox\">
					<h1>Version Information</h1>
					<strong>Application:</strong> " . A_NAME . "<br />
					<strong>Version:</strong> " . A_VERSION . "<br />
					<strong>Registered to:</strong> " . $A_Licensed_To . "<br />
					<strong>Serial:</strong> " . $A_License . "
				</div>";
			
			$page->setTemplateVar("PageContent", $page_content);	
		}
		elseif ($actual_page_id == "viewrecipe") {
			$page->addBreadCrumb("View Recipe", $menuvar['VIEWRECIPE']);
			$page->setTemplateVar("PageTitle", "View Recipe");	
			include 'viewrecipe.php';		
		}
		elseif ($actual_page_id == "viewrecipecategory") {
			$page->addBreadCrumb("View Recipe Category", $menuvar['VIEWRECIPECATEGORY']);
			$page->setTemplateVar("PageTitle", "View Recipe Category");	
			include 'viewrecipecategory.php';		
		}
		else {
			// Show the home page
			$page_content .= "\n						Thank you for using the Fast Track Sites Recipe System, please navigate through the system using the menus at the left.";
						
			$page->setTemplateVar("PageTitle", "Home");
			$page->setTemplateVar("PageContent", $page_content);	
	
		}
	
		//================================================
		// Create Menus
		//================================================		
		// Top Menu
		if ($rs_config['ftsrs_logo_url'] != "") {
			$page->makeMenuItem("top", "<img src=\"" . $rs_config['ftsrs_logo_url'] . "\" alt=\"Fast Track Sites Recipe System Logo\" />", "", "logo");
		}
		$page->makeMenuItem("top", "Home", $menuvar['HOME'], "");
				
		// Admin Options Menu
		if (isset($_SESSION['email_address'])) {
			if ($_SESSION['user_level'] == MOD || $_SESSION['user_level'] == ADMIN) {
				$page->makeMenuItem("top", "Configure", $menuvar['SETTINGS'], "");
			}
		
			// Make usermanagement menu items
			if ($_SESSION['user_level'] == MOD || $_SESSION['user_level'] == ADMIN) {
				$page->makeMenuItem("adminOptionsLeft", "Recipe Categories", $menuvar['RECIPECATS'], "");
				$page->makeMenuItem("adminOptionsLeft", "Recipes", $menuvar['RECIPES'], "");
				$page->makeMenuItem("adminOptionsLeft", "Themes", $menuvar['THEMES'], "");
				$page->makeMenuItem("adminOptionsLeft", "User Administration", $menuvar['USERS'], "");
			}
		}
		
		// User Options Menu
		if (!isset($_SESSION['email_address'])) {
			$page->makeMenuItem("userOptionsLeft", "Login", $menuvar['LOGIN'], "");
			$page->makeMenuItem("userOptionsLeft", "Register", $menuvar['REGISTER'], "");
		}
		else {
			$page->makeMenuItem("userOptionsLeft", "Logout", $menuvar['LOGOUT'], "");
		}
		
		// Recipe Categories Menu
		$page->makeMenuItem("recipeCategoriesLeft", "All Categories", $menuvar['VIEWRECIPECATEGORY'], "");
		
		$sql = "SELECT id, name FROM `" . DBTABLEPREFIX . "recipecats` ORDER BY name ASC";
		$result = mysql_query($sql);
		
		if ($result && mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_array($result)) {
				$page->makeMenuItem("recipeCategoriesLeft", $row['name'] . " (" . getNumberOfRecipesInCat($row['id']) . ")", $menuvar['VIEWRECIPECATEGORY'] . "&id=" . $row['id'], "");
			}
			mysql_free_result($result);
		}
	}
}
else { $page->setTemplateVar("PageContent", version_functions("advancedOptionsText")); }

version_functions("no");
if (isset($actual_style) && $actual_style == "printerFriendly") { include "themes/" . $themeDir . "/printerFriendlyTemplate.php"; }
else { include "themes/" . $themeDir . "/template.php"; }
?>