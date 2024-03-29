<? 
/***************************************************************************
 *                               recipes.php
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
				
		// Delete all ingredients that are blank
		$sql = "DELETE FROM `" . DBTABLEPREFIX . "recipes_ingredients` WHERE qty='' AND name=''";
		$result = mysql_query($sql);
		
		if ($actual_action == "editrecipes" && isset($actual_id)) { 
			// Add breadcrumb
			$page->addBreadCrumb("Edit Recipe", "");
			
			if(isset($_POST['name'])) {
				//print_r($_POST);
				$errors = 0;
				
				// Handle basic recipe
				$name = keeptasafe($_POST['name']);
				$cat_id = keepsafe($_POST['cat_id']);
				$description = keeptasafe($_POST['description']);
				$preperation = keeptasafe($_POST['preperation']);
				$preperation_time = keeptasafe($_POST['preperation_time']);
				$servings = keepsafe($_POST['servings']);
				$image_full = keeptasafe($_POST['image_full']);
				$image_thumb = keeptasafe($_POST['image_thumb']);
				
				$sql = "UPDATE `" . DBTABLEPREFIX . "recipes` SET name='" . $name . "', cat_id = '" . $cat_id . "', description='" . $description . "', preperation = '" . $preperation . "', preperation_time = '" . $preperation_time . "', servings = '" . $servings . "', image_full='" . $image_full . "', image_thumb='" . $image_thumb . "' WHERE id='" . $actual_id . "'";
			    $result = mysql_query($sql);
				$errors += ($result) ? 0 : 1;
				//echo $sql . "<br />";
				
				// Handle ingredients
				$ingredientQtys = $_POST['ingredientSetQty'];
				$ingredientNames = $_POST['ingredientSetName'];
				
				// Delete all ingredients for this recipe
				$sql = "DELETE FROM `" . DBTABLEPREFIX . "recipes_ingredients` WHERE recipe_id='" . $actual_id . "'";
			    $result = mysql_query($sql);
				
				// Add the ingredients back in
				for ($i = 0; $i < count($ingredientQtys); $i++) {
					$sql = "INSERT INTO `" . DBTABLEPREFIX . "recipes_ingredients` (`recipe_id`, `qty`, `name`) VALUES ('" . $actual_id . "', '" . keeptasafe($ingredientQtys[$i]) . "', '" . keeptasafe($ingredientNames[$i]) . "')";
			    	$result = mysql_query($sql);
					$errors += ($result) ? 0 : 1;
					//echo $sql . "<br />";
				}
				
				
				if ($errors == 0) {
					$page_content = "<span class=\"center\">Your recipe has been updated, and you are being redirected to the main page.</span>
								<meta http-equiv=\"refresh\" content=\"1;url=" . $menuvar['RECIPES'] . "\">";
				}
				else {
					$page_content = "<span class=\"center\">There was an error while updating your recipe. You are being redirected to the main page.</span>
								<meta http-equiv=\"refresh\" content=\"5;url=" . $menuvar['RECIPES'] . "\">";						
				}
				
				
				unset($_POST['name']);
			}
			else{
				$sql = "SELECT * FROM `" . DBTABLEPREFIX . "recipes` WHERE id='" . $actual_id . "'";
				$result = mysql_query($sql);
					
				if ($result && mysql_num_rows($result) > 0) {
					$row = mysql_fetch_array($result);
					
					$page_content .= "
											<form action=\"" . $menuvar['RECIPES'] . "&action=editrecipes&id=" . $actual_id . "\" method=\"post\">
												<table class=\"contentBox\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">
													<tr class=\"title1\">
														<td colspan=\"2\"><strong>Edit Recipe</strong></td>
													</tr>
													<tr class=\"row1\">
														<td width=\"30%\"><strong>Name: </strong></td>
														<td><input type=\"text\" name=\"name\" size=\"40\" value=\"" . $row['name'] . "\" /></td>
													</tr>
													<tr class=\"row2\">
														<td><strong>Category: </strong></td>
														<td>
															" . createDropdown("recipecats", "cat_id", $row['cat_id'], "") . "
														</td>
													</tr>
													<tr class=\"row1\">
														<td><strong>Description: </strong></td>
														<td><textarea name=\"description\" cols=\"45\" rows=\"5\">" . $row['description'] . "</textarea></td>
													</tr>
													<tr class=\"row2\">
														<td><strong>Ingredients: </strong></td>
														<td>
															<table class=\"ingredientsTable\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">
																<thead><tr><th>Qty</th><th>Name</th></tr></thead>
																<tbody id=\"ingredients\"></tbody>
															</table>
															<br /><br />
															<script type=\"text/javascript\">
																// Set our basic settings
																configureIngredientsSettings('0', 'themes/" . $rs_config['ftsrs_theme'] . "/icons/add.png', 'themes/" . $rs_config['ftsrs_theme'] . "/icons/delete.png');
																
																// Add our ingredients";
					
						$sql2 = "SELECT qty, name FROM `" . DBTABLEPREFIX . "recipes_ingredients` WHERE recipe_id='" . $actual_id . "' ORDER BY id ASC";
						$result2 = mysql_query($sql2);
					
						if ($result2 && mysql_num_rows($result2) > 0) {
							while ($row2 = mysql_fetch_array($result2)) {
								$page_content .= "\n																addIngredientSet('" . $row2['qty'] . "', '" . $row2['name'] . "');";
							}
							mysql_free_result($result2);
						}
					
					$page_content .= "
																// Add a blank ingredient set
																addIngredientSet('', '');
															</script>
														</td>
													</tr>
													<tr class=\"row1\">
														<td><strong>Preperation: </strong></td>
														<td><textarea name=\"preperation\" cols=\"45\" rows=\"5\">" . $row['preperation'] . "</textarea></td>
													</tr>
													<tr class=\"row2\">
														<td><strong>Preperation Time: </strong></td>
														<td><input type=\"text\" name=\"preperation_time\" size=\"40\" value=\"" . $row['preperation_time'] . "\" /></td>
													</tr>
													<tr class=\"row1\">
														<td><strong>Servings: </strong></td>
														<td><input type=\"text\" name=\"servings\" size=\"40\" value=\"" . $row['servings'] . "\" /></td>
													</tr>
													<tr class=\"row2\">
														<td width=\"80\"><strong>Image: </strong></td>
														<td width=\"80\"><input type=\"text\" name=\"image_full\" size=\"40\" value=\"" . $row['image_full'] . "\" /></td>
													</tr>
													<tr class=\"row1\">
														<td width=\"80\"><strong>Thumbnail Image: </strong></td>
														<td width=\"80\"><input type=\"text\" name=\"image_thumb\" size=\"40\" value=\"" . $row['image_thumb'] . "\" /></td>
													</tr>
												</table>
												<br />
												<div class=\"center\"><input type=\"submit\" name=\"submit\" value=\"Submit\" class=\"button\" onClick=\"selectAllItems('recipes_type[]'); selectAllItems('recipes_models[]'); selectAllItems('recipes_default_on_models[]');\" /></div>
											</form>
											<br /><br />";
											
					mysql_free_result($result);
				}
				else { $page_content .= "No such ID was found in the database!"; }
			}			
		}
		else {
			//==================================================
			// Print out our recipes table
			//==================================================
				
			$page_content = "
						<div id=\"updateMe\">" . printRecipesTable(-1) . "</div>
				<script language = \"Javascript\">
				
				function ValidateForm(theForm){
					var name=document.newRecipesForm.newrecipesname
					
					if ((name.value==null)||(name.value==\"\")){
						alert(\"Please enter the new recipe\'s name.\")
						name.focus()
						return false
					}
					new Ajax.Updater('updateMe', 'ajax.php?action=postrecipes', {onComplete:function(){ new Effect.Highlight('newRecipes');},asynchronous:true, parameters:Form.serialize(theForm), evalScripts:true}); 
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