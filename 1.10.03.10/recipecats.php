<? 
/***************************************************************************
 *                               recipecats.php
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
	if ($_SESSION['user_level'] == MOD || $_SESSION['user_level'] == ADMIN) {
		if ($actual_action == "editrecipecats" && isset($actual_id)) { 
			// Add breadcrumb
			$page->addBreadCrumb("Edit Recipe Category", "");
			if(isset($_POST['name']))
			{
				$name = keeptasafe($_POST['name']);
				$description = $_POST['description'];
				
				$sql = "UPDATE `" . DBTABLEPREFIX . "recipecats` SET name='" . $name . "', description='" . $description . "' WHERE id='" . $actual_id . "'";
			    $result = mysql_query($sql);
				
				if ($result) {
					$page_content = "<span class=\"center\">Your recipe category has been updated, and you are being redirected to the main page.</span>
								<meta http-equiv=\"refresh\" content=\"1;url=" . $menuvar['RECIPECATS'] . "\">";
				}
				else {
					$page_content = "<span class=\"center\">There was an error while updating your recipe category. You are being redirected to the main page.</span>
								<meta http-equiv=\"refresh\" content=\"5;url=" . $menuvar['RECIPECATS'] . "\">";						
				}
				
				unset($_POST['name']);
			}
			else{
				$sql = "SELECT * FROM `" . DBTABLEPREFIX . "recipecats` WHERE id='" . $actual_id . "'";
				$result = mysql_query($sql);
					
				if($result && mysql_num_rows($result) > 0) {
					$row = mysql_fetch_array($result);
					
					$page_content .= "\n
											<form action=\"" . $menuvar['RECIPECATS'] . "&action=editrecipecats&id=" . $actual_id . "\" method=\"post\">
												<table class=\"contentBox\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">
													<tr class=\"title1\">
														<td colspan=\"2\"><strong>Edit Recipe Category</strong></td>
													</tr>
													<tr class=\"row1\">
														<td width=\"30%\"><strong>Name: </strong></td>
														<td><input type=\"text\" name=\"name\" size=\"40\" value=\"" . $row['name'] . "\" /></td>
													</tr>
													<tr class=\"row2\">
														<td><strong>Description: </strong></td>
														<td><textarea name=\"description\" cols=\"45\" rows=\"5\">" . $row['description'] . "</textarea></td>
													</tr>
												</table>
												<br />
												<div class=\"center\"><input type=\"submit\" name=\"submit\" value=\"Submit\" class=\"button\" /></div>
											</form>
											<br /><br />";
				}
				else { $page_content .= "No such ID was found in the database!"; }
			}			
		}			
		else {
			//==================================================
			// Print out our recipecats table
			//==================================================
			$sql = "SELECT * FROM `" . DBTABLEPREFIX . "recipecats` ORDER BY recipecats_sort_order ASC";
			$result = mysql_query($sql);
				
			$x = 1; //reset the variable we use for our row colors	
				
			$page_content = "
								<div id=\"updateMe\">
								" . printRecipeCatsTable() . "
								</div>
				<script language = \"Javascript\">				
				function ValidateForm(theForm){
					var name=document.newRecipecatsForm.newrecipecatsname
					
					if ((name.value==null)||(name.value==\"\")){
						alert(\"Please enter the new recipe category\'s name.\")
						name.focus()
						return false
					}
					new Ajax.Updater('updateMe', 'ajax.php?action=postrecipecats', {onComplete:function(){ new Effect.Highlight('newRecipecats');},asynchronous:true, parameters:Form.serialize(theForm), evalScripts:true}); 
					name.value = '';
					return false;
				 }
				</script>";	
		}
		
		$page->setTemplateVar("PageContent", $page_content);
	}
	else {
		$page->setTemplateVar("PageContent", "\nYou Are Not Authorized To Access This Area. Please Refrain From Trying To Do So Again.");
	}
?>