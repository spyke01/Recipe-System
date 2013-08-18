<?php 
/***************************************************************************
 *                               recipes.php
 *                            -------------------
 *   begin                : Saturday, Sept 24, 2005
 *   copyright            : (C) 2005 Paden Clayton - Fast Track Sites
 *   email                : sales@fasttacksites.com
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
 
//=========================================================
// Gets a recipe's name from an id
//=========================================================
function getRecipeNameFromID($recipeID, $includeItemsCalledNone = 0) {
	$extraSQL = ($includeItemsCalledNone == 0) ? " AND UCASE(name)!='NONE'" : "";
	
	$sql = "SELECT name FROM `" . DBTABLEPREFIX . "recipes` WHERE id='" . $recipeID . "'" . $extraSQL;
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			return $row['name'];
		}	
		mysql_free_result($result);
	}
}

//=========================================================
// Prints a recipe block based on a recipeID
//=========================================================
function printRecipeBlock($recipeCatID, $recipeID) {
	global $menuvar, $rs_config;
	$content = "";
	$thumbnailHTML = "";
	
	$sql = "SELECT name, servings, image_full, image_thumb FROM `" . DBTABLEPREFIX . "recipes` WHERE id = '" . $recipeID . "' LIMIT 1";
	$result = mysql_query($sql);
			
	if ($result && mysql_num_rows($result) > 0) {						
		while ($row = mysql_fetch_array($result)) {
			$thumbnailHTML .= ($row['image_thumb'] != "") ? "<img src=\"" . $row['recipecats_image'] . "\" alt=\"" . $row['name'] . "\" />" : "<img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/noImage.png\" alt=\"" . $row['name'] . "\" />";
			
			$content .= "
							<div class=\"recipeBlock\">
								<p class=\"recipeImage\"><a href=\"" . $menuvar['VIEWRECIPE'] . "&recipeCatID=" . $recipeCatID . "&id=" . $recipeID . "\">" . $thumbnailHTML . "</a></p>
								<p class=\"recipeName\"><a href=\"" . $menuvar['VIEWRECIPE'] . "&recipeCatID=" . $recipeCatID . "&id=" . $recipeID . "\">" . $row['name'] . "</a></p>
								<p class=\"recipeServings\">Servings: " . $row['servings'] . "</p>
							</div>";
		}
		mysql_free_result($result);
	}
	
	return $content;
}

//=========================================================
// Prints a recipe block based on a recipeID
//=========================================================
function printViewRecipeBlock($recipeCatID, $recipeID) {
	global $menuvar, $rs_config;
	$content = "";
	$thumbnailHTML = "";
	$ingredients = "";
	
	$sql = "SELECT name, description, preperation, preperation_time, servings, image_full, image_thumb FROM `" . DBTABLEPREFIX . "recipes` WHERE id = '" . $recipeID . "' LIMIT 1";
	$result = mysql_query($sql);
			
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			$thumbnailHTML .= ($row['image_full'] != "") ? "<a href=\"" . $row['recipecats_image'] . "\" title=\"" . $row['name'] . "\" rel=\"" . $rs_config['ftsrs_thumbnail_rel_tag'] . "\">" : "";
			$thumbnailHTML .= ($row['image_thumb'] != "") ? "<img src=\"" . $row['recipecats_image'] . "\" alt=\"" . $row['name'] . "\" />" : "<img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/noImage.png\" alt=\"" . $row['name'] . "\" />";
			$thumbnailHTML .= ($row['image_full'] != "") ? "</a>" : "";
		
			// Get ingredients for recipe
			$sql2 = "SELECT qty, name FROM `" . DBTABLEPREFIX . "recipes_ingredients` WHERE recipe_id = '" . $recipeID . "'";
			$result2 = mysql_query($sql2);
			
			if ($result2 && mysql_num_rows($result2) > 0) {						
				while ($row2 = mysql_fetch_array($result2)) {
					$ingredients .= ($ingredients != "") ? "<br />" : "";
					$ingredients .= $row2['qty'] . " " . $row2['name'];
				}
				mysql_free_result($result2);
			}
			
			// Print recipe info
			$content .= "
							<div class=\"viewRecipeBlock\">
								<div class=\"recipeImage\">" . $thumbnailHTML . "</div>
								<div class=\"recipeInformation\">
									<p class=\"recipeName\">" . $row['name'] . "</p>
									<p class=\"recipeDescription\"><strong>Description:</strong><br />" . $row['description'] . "</p>
									<p class=\"recipeIngredients\"><strong>Ingredients:</strong><br />" . $ingredients . "</p>
									<p class=\"recipePreperation\"><strong>Preperation:</strong><br />" . $row['preperation'] . "</p>
									<p class=\"recipePreperationTime\"><strong>Preperation Time:</strong> " . $row['preperation_time'] . "</p>
									<p class=\"recipeServings\"><strong>Servings:</strong> " . $row['servings'] . "</p>
							</div>
							</div>";
		}
		mysql_free_result($result);
	}
	
	return $content;
}

//=========================================================
// Print the Recipes Table
//=========================================================
function printRecipesTable($recipeCatID = "-1") {
	global $menuvar, $rs_config;
	$recipesids = array();
	$actual_id = parseurl($_GET['id']);
	
			$x = 1; //reset the variable we use for our row colors	
			$sqlParams = ($recipeCatID != "" && $recipeCatID >= 0) ? " WHERE cat_id LIKE '" . $recipeCatID . "'" : "";
			
			$content = "
							<table class=\"contentBox\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">
								<tr>
									<td class=\"title1\" colspan=\"7\">
										<div class=\"floatRight\">
											<form name=\"newRecipesForm\" id=\"newRecipesForm\" action=\"" . $menuvar['RECIPES'] . "\" method=\"post\" onSubmit=\"ValidateForm(this); return false;\">
												<input type=\"text\" name=\"newrecipesname\" />
												<input type=\"image\" src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/add.png\" />
											</form>
										</div>
										Recipes
									</td>
								</tr>
								<tr class=\"title2\">
									<td><strong>Name</strong></td><td><strong>Category</strong></td><td><strong>Servings</strong></td><td style=\"width: 5%;\"></td>
								</tr>";
			
			// get recipes that have a type
			$sql = "SELECT r.id, r.name, rc.name, r.servings FROM `" . DBTABLEPREFIX . "recipes` r LEFT JOIN `" . DBTABLEPREFIX . "recipecats` rc ON r.cat_id = rc.id" . $sqlParams . " ORDER BY rc.name, r.name";
			$result = mysql_query($sql);
			//echo $sql;
			
			if ($result && mysql_num_rows($result) > 0) {						
				while ($row = mysql_fetch_array($result)) {
					$content .=	"					
										<tr id=\"" . $row['id'] . "_row\" class=\"row" . $x . "\">
											<td><div id=\"" . $row['id'] . "_text\">" . $row['name'] . "</div></td>
											<td>" . $row['name'] . "</td>
											<td><div id=\"" . $row['id'] . "_servings\">" . $row['servings'] . "</div></td>
											<td>
												<center><a href=\"" . $menuvar['RECIPES'] . "&action=editrecipes&id=" . $row['id'] . "\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/check.png\" alt=\"Edit\" /></a> &nbsp; <a style=\"cursor: pointer; cursor: hand;\" onclick=\"ajaxDeleteNotifier('" . $row['id'] . "RecipesSpinner', 'ajax.php?action=deleteitem&table=recipes&id=" . $row['id'] . "', 'recipes', '" . $row['id'] . "_row');\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/delete.png\" alt=\"Delete Recipes\" /></a><span id=\"" . $row['id'] . "RecipesSpinner\" style=\"display: none;\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/indicator.gif\" alt=\"spinner\" /></span></center>
											</td>
										</tr>";
										
					$recipesids[$row['id']] = $row['name'];					
					$x = ($x==2) ? 1 : 2;
				}
			}
			mysql_free_result($result);
			
			$content .= "
									</table>
									<script type=\"text/javascript\">";
			
			// Generate the AJAX code for inPlaceEditors for our main table
			$x = 1; //reset the variable we use for our highlight colors
			foreach($recipesids as $key => $value) {
				$highlightColors = ($x == 1) ? "highlightcolor:'#CBD5DC',highlightendcolor:'#5194B6'" : "highlightcolor:'#5194B6',highlightendcolor:'#CBD5DC'";
				$content .= "
															new Ajax.InPlaceEditor('" . $key . "_text', 'ajax.php?action=updateitem&table=recipes&item=name&id=" . $key . "', {rows:1,cols:50," . $highlightColors . ",loadTextURL:'ajax.php?action=getitem&table=recipes&item=name&id=" . $key . "'});
															new Ajax.InPlaceEditor('" . $key . "_servings', 'ajax.php?action=updateitem&table=recipes&item=servings&id=" . $key . "', {rows:1,cols:50," . $highlightColors . ",loadTextURL:'ajax.php?action=getitem&table=recipes&item=servings&id=" . $key . "'});";
				
				$x = ($x==2) ? 1 : 2;
			}
			
			$content .= "
									</script>";
			
			return $content;
}

?>