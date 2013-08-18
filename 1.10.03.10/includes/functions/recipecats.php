<?php 
/***************************************************************************
 *                               recipecats.php
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
// Gets a partcat's name from an id
//=========================================================
function getRecipeCatNameFromID($recipeCatID) {
	$sql = "SELECT name FROM `" . DBTABLEPREFIX . "recipecats` WHERE id='" . $recipeCatID . "'";
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			return $row['name'];
		}	
		mysql_free_result($result);
	}
}
 
//=========================================================
// Gets a partcat's description from an id
//=========================================================
function getRecipeCatDescriptionFromID($recipeCatID) {
	$sql = "SELECT description FROM `" . DBTABLEPREFIX . "recipecats` WHERE id='" . $recipeCatID . "'";
	$result = mysql_query($sql);
	
	if ($result && mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result)) {
			return $row['description'];
		}	
		mysql_free_result($result);
	}
}

//=========================================================
// Prints the recipe blocks for a certain category
//=========================================================
function getNumberOfRecipesInCat($recipeCatID) {
	$totalRows = 0;
	
	$extraSQL = ($recipeCatID != "") ? " WHERE cat_id = '" . $recipeCatID . "'" : "";
	$sql = "SELECT COUNT(id) AS totalRows FROM `" . DBTABLEPREFIX . "recipes`" . $extraSQL . "";
	$result = mysql_query($sql);
			
	if ($result && mysql_num_rows($result) > 0) {						
		while ($row = mysql_fetch_array($result)) {
			$totalRows = $row['totalRows'];
		}
		mysql_free_result($result);
	}
	
	return $totalRows;
}

//=========================================================
// Prints the recipe blocks for a certain category
//=========================================================
function printRecipeCatsRecipes($recipeCatID) {
	global $menuvar, $rs_config, $actual_page;
	$content = "";
	$thumbnailHTML = "";
	
	// Figure out if we are using pagination due to the number of recipes in this category
	$numOfRecipesInCat = getNumberOfRecipesInCat($recipeCatID);
	$usePagination = ($numOfRecipesInCat <= $rs_config['ftsrs_recipes_per_page']) ? 0 : 1;
	
	// Handle our pagination SQL building function
	if ($usePagination == 0) {
		$startAt = 0;
		$stopAt = $numOfRecipesInCat;
	}
	else {
		// Make sure to take care of 0 values
		$actual_page = ($actual_page == 0) ? 1 : $actual_page;
		$usePagination = ($numOfRecipesInCat <= $rs_config['ftsrs_recipes_per_page']) ? 0 : 1;
		$totalPages = ($totalPages == 0) ? 1 : $totalPages;	
		
		// Determine which record to start and stop at based on page number
		$totalPages = $numOfRecipesInCat / $rs_config['ftsrs_recipes_per_page'];
				
		// Decimal places signify that another $page is needed
		$totalPages = ($totalPages > stripChange($totalPages)) ? stripChange($totalPages) + 1 : $totalPages;
		//echo $totalPages . " " . stripChange($totalPages, 0);
		
		// Calculate our starting row
		$startAt = ($actual_page == 1) ? 0 : $rs_config['ftsrs_recipes_per_page'] * ($actual_page - 1);
				
		// Calculate our ending row
		$stopAt = ($actual_page == "" || $actual_page == 1) ? $rs_config['ftsrs_recipes_per_page'] : $startAt + $rs_config['ftsrs_recipes_per_page'];
					
		// Don't loop through a number that is greater than the total number of rows
		$stopAt = ($stopAt > $numOfRecipesInCat) ? $numOfRecipesInCat : $stopAt;
	}
	//echo $actual_page . " " . $usePagination . " " . $totalPages . " " . $startAt . " " . $stopAt . "<br />";
	
	$extraSQL = ($recipeCatID != "") ? " WHERE cat_id = '" . $recipeCatID . "'" : "";
	$sql = "SELECT id FROM `" . DBTABLEPREFIX . "recipes`" . $extraSQL . " LIMIT " . $startAt . ", " . $stopAt;
	$result = mysql_query($sql);
	//echo $sql . "<br />";
			
	if ($result && mysql_num_rows($result) > 0) {						
		while ($row = mysql_fetch_array($result)) {
			$content .= printRecipeBlock($recipeCatID, $row['id']);
		}
		mysql_free_result($result);
	}
	else {
			$content .= "There are currently no recipes in this category.";	
	}
	
	$content .= "
				<br class=\"clear\" /><br class=\"clear\" />";
				
	$content .= ($usePagination == 1) ? generatePagination($menuvar['VIEWRECIPECATEGORY'] . "&id=" . $actual_id, $actual_page, $totalPages) : "";
	
	return $content;
}

//=========================================================
// Print the Recipe Cats Table
//=========================================================
function printRecipeCatsTable() {
	global $menuvar, $rs_config;
	$recipecatsids = array();
	
	$sql = "SELECT * FROM `" . DBTABLEPREFIX . "recipecats` ORDER BY name ASC";
	$result = mysql_query($sql);
				
	$x = 1; //reset the variable we use for our row colors	
	
	$content = "
							<table class=\"contentBox\" cellpadding=\"1\" cellspacing=\"1\" width=\"100%\">
								<tr>
									<td class=\"title1\" colspan=\"5\">
										<div class=\"floatRight\">
											<form name=\"newRecipecatsForm\" action=\"" . $menuvar['RECIPECATS'] . "\" method=\"post\" onSubmit=\"ValidateForm(this); return false;\">
												<input type=\"text\" name=\"newrecipecatsname\" />
												<input type=\"image\" src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/add.png\" />
											</form>
										</div>
										Recipe Categories
									</td>
								</tr>							
								<tr class=\"title2\">
									<td><strong>Name</strong></td><td><strong>Description</strong></td><td></td>
								</tr>";
								
			if (!$result || mysql_num_rows($result) == 0) { // No recipecats yet!
				$content .= "\n					<tr class=\"greenRow\">
															<td colspan=\"4\">There are no recipecats in the database.</td>
													</tr>";	
			}
			else {	 // Print all our recipecats								
				while ($row = mysql_fetch_array($result)) {
					
					$content .=	"					
										<tr id=\"" . $row['id'] . "_row\" class=\"row" . $x . "\">
											<td><div id=\"" . $row['id'] . "_text\">" . $row['name'] . "</div></td>
											<td><div id=\"" . $row['id'] . "_description\">" . bbcode($row['description']) . "</div></td>
											<td width=\"5%\">
												<span class=\"center\"><a href=\"" . $menuvar['RECIPECATS'] . "&action=editrecipecats&id=" . $row['id'] . "\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/check.png\" alt=\"Edit\" /></a> &nbsp; <a style=\"cursor: pointer; cursor: hand;\" onclick=\"ajaxDeleteNotifier('" . $row['id'] . "RecipecatsSpinner', 'ajax.php?action=deleteitem&table=recipecats&id=" . $row['id'] . "', 'Recipe Category', '" . $row['id'] . "_row');\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/delete.png\" alt=\"Delete Recipecats\" /></a><span id=\"" . $row['id'] . "RecipecatsSpinner\" style=\"display: none;\"><img src=\"themes/" . $rs_config['ftsrs_theme'] . "/icons/indicator.gif\" alt=\"spinner\" /></span></span>
											</td>
										</tr>";
					$recipecatsids[$row['id']] = $row['name'];					
					$x = ($x==2) ? 1 : 2;
				}
				mysql_free_result($result);
			}
			
			$content .= "					
									</table>
									<script type=\"text/javascript\">";
			
			$x = 1; //reset the variable we use for our highlight colors
			foreach($recipecatsids as $key => $value) {
				$content .= ($x == 1) ? "\n							new Ajax.InPlaceEditor('" . $key . "_text', 'ajax.php?action=updateitem&table=recipecats&item=name&id=" . $key . "', {rows:1,cols:50,highlightcolor:'#CBD5DC',highlightendcolor:'#5194B6',loadTextURL:'ajax.php?action=getitem&table=recipecats&item=name&id=" . $key . "'});" : "\n							new Ajax.InPlaceEditor('" . $key . "_text', 'ajax.php?action=updateitem&table=recipecats&item=name&id=" . $key . "', {rows:1,cols:50,highlightcolor:'#5194B6',highlightendcolor:'#CBD5DC',loadTextURL:'ajax.php?action=getitem&table=recipecats&item=name&id=" . $key . "'});";
				$content .= ($x == 1) ? "\n							new Ajax.InPlaceEditor('" . $key . "_description', 'ajax.php?action=updateitem&table=recipecats&item=description&id=" . $key . "', {rows:1,cols:50,highlightcolor:'#CBD5DC',highlightendcolor:'#5194B6',loadTextURL:'ajax.php?action=getitem&table=recipecats&item=description&id=" . $key . "'});" : "\n							new Ajax.InPlaceEditor('" . $key . "_description', 'ajax.php?action=updateitem&table=recipecats&item=description&id=" . $key . "', {rows:1,cols:50,highlightcolor:'#5194B6',highlightendcolor:'#CBD5DC',loadTextURL:'ajax.php?action=getitem&table=recipecats&item=description&id=" . $key . "'});";
				$x = ($x==2) ? 1 : 2;
			}
			
			$content .= "
									</script>";
			
			return $content;
}

?>