<? 
/***************************************************************************
 *                               settings.php
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

if ($_SESSION['user_level'] == ADMIN) {	
	//==================================================
	// Update our DB
	//==================================================
	if (isset($_POST['submit'])) {
		foreach($_POST as $name => $value) {
			if ($name != "submit"){			
				if ($name == "ftsrs_active" || $name == "ftsrs_paypal_active" || $name == "ftsrs_google_checkout_active" || $name == "ftsrs_credit_card_payment_active" || $name == "ftsrs_checkmowire_active") {
					$value = ($value == "") ? 0 : 1;
				}
				
				$sql = "UPDATE `" . DBTABLEPREFIX . "config` SET value = '" . $value . "' WHERE name = '" . $name . "'";
				$result = mysql_query($sql);
				//echo $sql . "<br />";
			}
		}
		
		// Handle checkboxes, unchecked boxes are not posted so we check for this and mark them in the DB as such
		if (!isset($_POST['ftsrs_active'])) {
			$sql = "UPDATE `" . DBTABLEPREFIX . "config` SET value = '0' WHERE name = 'ftsrs_active'";
			$result = mysql_query($sql);
		}
		
		unset($_POST['submit']);
	}
	
	//==================================================
	// Print out our settings table
	//==================================================
	$sql = "SELECT * FROM `" . DBTABLEPREFIX . "config`";
	$result = mysql_query($sql);
	
	// This is used to let us get the actual items and not just name and value
	while ($row = mysql_fetch_array($result)) {
		extract($row);
		$name = $row['name'];
		$value = $row['value'];
		$current_config[$name] = $value;
	}	
	extract($current_config);
		
	// Give our template the values
	$content = "<form action=\"" . $menuvar['SETTINGS'] . "\" method=\"post\" target=\"_top\">
					<table class=\"contentBox\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">
						<tr><td class=\"title1\" colspan=\"2\">Basic Settings</td></tr>
						<tr class=\"row1\">
							<td><strong>Website Name: </strong></td>
							<td>
								<input name=\"ftsrs_site_name\" type=\"text\" size=\"60\" value=\"" . $ftsrs_site_name . "\" />
							</td>
						</tr>
						<tr class=\"row2\">
							<td><strong>Logo URL: </strong></td>
							<td>
								<input name=\"ftsrs_logo_url\" type=\"text\" size=\"60\" value=\"" . $ftsrs_logo_url . "\" />
							</td>
						</tr>
						<tr class=\"row1\">
							<td><strong>Admin Email: </strong></td>
							<td>
								<input name=\"ftsrs_admin_email\" type=\"text\" size=\"60\" value=\"" . $ftsrs_admin_email . "\" />
							</td>
						</tr>
						<tr class=\"row2\">
							<td><strong>Sales Email: </strong></td>
							<td>
								<input name=\"ftsrs_sales_email\" type=\"text\" size=\"60\" value=\"" . $ftsrs_sales_email . "\" />
							</td>
						</tr>
						<tr class=\"row2\">
							<td><strong>Active: </strong></td>
							<td>
								<input name=\"ftsrs_active\" type=\"checkbox\" value=\"1\"". testChecked($ftsrs_active, ACTIVE) . " />
							</td>
						</tr>
						<tr class=\"row1\">
							<td><strong>Inactive Message:</strong></td>
							<td>
								<textarea name=\"ftsrs_inactive_msg\" cols=\"45\" rows=\"10\">" . $ftsrs_inactive_msg . "</textarea>
							</td>
						</tr>
						<tr><td class=\"title1\" colspan=\"2\">Advanced Settings</td></tr>
						<tr class=\"row1\">
							<td><strong>Store URL: </strong></td>
							<td>
								<input name=\"ftsrs_site_url\" type=\"text\" size=\"60\" value=\"" . $ftsrs_site_url . "\" />
							</td>
						</tr>
						<tr class=\"row2\">
							<td><strong>Cookie Name: </strong></td>
							<td>
								<input name=\"ftsrs_cookie_name\" type=\"text\" size=\"60\" value=\"" . $ftsrs_cookie_name . "\" />
							</td>
						</tr>
						<tr class=\"row1\">
							<td><strong>Lightbox Style Script for Images: </strong></td>
							<td>
								" . createDropdown("lightboxscript", "ftsrs_thumbnail_rel_tag", $ftsrs_thumbnail_rel_tag, "") . "
							</td>
						</tr>
						<tr class=\"row2\">
							<td><strong>Recipes Per Page: </strong></td>
							<td>
								" . createDropdown("recipesPerPage", "ftsrs_recipes_per_page", $ftsrs_recipes_per_page, "") . "
							</td>
						</tr>
					</table>
					<br />
					<span class=\"center\"><input type=\"submit\" name=\"submit\" class=\"button\" value=\"Update Settings\" /></span>
				</form>";

	$page->setTemplateVar('PageContent', $content);
}
else {
	$page->setTemplateVar('PageContent', "\nYou Are Not Authorized To Access This Area. Please Refrain From Trying To Do So Again.");
}
?>