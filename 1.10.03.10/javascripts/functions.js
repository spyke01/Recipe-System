/*-------------------------------------------------------------------------*/
// General Functions
/*-------------------------------------------------------------------------*/	
function confirmDelete(text) {
    return confirm("Are you sure you want to delete this "+ text +"?");
}

function fetchItem(itemID) {
	if (document.getElementById) { return document.getElementById(itemID); }
	else if (document.all) { return document.all[itemID]; }
	else if (document.layers) { return document.layers[itemID]; }
	else { return null; }
}

function sqr_show_hide(id) {
	var item = fetchItem(id)

	if (item && item.style) {
		if (item.style.display == "none") {
			item.style.display = "";
		}
		else {
			item.style.display = "none";
		}
	}
	else if (item) {
		item.visibility = "show";
	}
}

function sqr_show(id) {
	var item = fetchItem(id)

	if (item && item.style) {
		item.style.display = "";
	}
	else if (item) {
		item.visibility = "show";
	}
}

function sqr_hide(id) {
	var item = fetchItem(id)

	item.style.display = "none";
}

function MM_swapImage() { //v3.0
	var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
	if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function MM_swapImgRestore() { //v3.0
	var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_findObj(n, d) { //v4.0
	var p,i,x; if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && document.getElementById) x=document.getElementById(n); return x;
}

function sqr_show_hide_with_img(itemID) {
	obj = fetchItem('slideDiv' + itemID);
	img = fetchItem('slideImg' + itemID);

	if (!obj) {
		// nothing to collapse!
		if (img) {
			// hide the clicky image if there is one
			img.style.display = 'none';
		}
		return false;
	}
	else {
		if (obj.style.display == 'none') {
			obj.style.display = '';
			if (img) {
				img_re = new RegExp("_collapsed\\.jpg$");
				img.src = img.src.replace(img_re, '.jpg');
			}
		}
		else {
			obj.style.display = 'none';
			if (img) {
				img_re = new RegExp("\\.jpg$");
				img.src = img.src.replace(img_re, '_collapsed.jpg');
			}
		}
	}
	return false;
}

function more_info_win(id, place) {
	newWindow = window.open('moreinfo.php?id=' + id + '#'+place, 'MoreInfo', 'height=570,width=715,status=yes, scrollbars=yes,toolbar=no,menubar=no,location=no');
}

/*-------------------------------------------------------------------------*/
// Ajax Functions
/*-------------------------------------------------------------------------*/	
function ajaxDeleteNotifier(spinDivID, action, text, row) {
    if (confirm("Are you sure you want to delete this "+ text +"?")) {
		sqr_show_hide(spinDivID);
		new Ajax.Request(action, {asynchronous:true, onSuccess:function(){ new Effect.SlideUp(row);}});
	}
}

function ajaxSubmitCreateUser(theForm, quoteID, updateParentTable) {
    sqr_show_hide('createUserFormSpinner');
	new Ajax.Updater('updateMe', 'ajax.php?action=submitCreateUser', {asynchronous:true, parameters:Form.serialize(theForm), evalScripts:true, onSuccess:function(){ sqr_show_hide('createUserFormSpinner'); }});
	if (updateParentTable = '1') {
		self.parent.ajaxUpdateCreateUser(quoteID);
	}
}

function ajaxUpdateCreateUser(quoteID) {
	new Ajax.Updater('quoteOwnerListHolder', 'ajax.php?action=updateQuoteOwnerListHolder&&id=' + quoteID, {asynchronous:true, evalScripts:true});
	new Ajax.Updater('quoteEnteredByListHolder', 'ajax.php?action=updateQuoteEnteredByListHolder&&id=' + quoteID, {asynchronous:true, evalScripts:true});
}

function ajaxShowHideSliderWithImg(itemID) {
	obj = fetchItem('slideDiv' + itemID);
	img = fetchItem('slideImg' + itemID);
	status = fetchItem('slideStatus' + itemID);

	if (!obj) {
		// nothing to collapse!
		if (img) {
			// hide the clicky image if there is one
			img.style.display = 'none';
		}
		return false;
	}
	else {
		if (status.value == '0') {
			new Effect.SlideDown('slideDiv' + itemID);
			status.value = '1';
			if (img) {
				img_re = new RegExp("_collapsed\\.jpg$");
				img.src = img.src.replace(img_re, '.jpg');
			}
		}
		else {
			new Effect.SlideUp('slideDiv' + itemID);
			status.value = '0';
			if (img) {
				img_re = new RegExp("\\.jpg$");
				img.src = img.src.replace(img_re, '_collapsed.jpg');
			}
		}
	}
	return false;
}

function showStateDropBox(countryBox, addressType) {
	stateDropBoxRow = addressType + 'StateRow';
	stateDropBoxRow2 = addressType + 'StateRow2';
	
    if (countryBox.options[countryBox.selectedIndex].value == 'USA') {
		sqr_show(stateDropBoxRow);
		sqr_hide(stateDropBoxRow2);
	}
	else { 
		sqr_show(stateDropBoxRow2);
		sqr_hide(stateDropBoxRow);
	}
}

/*-------------------------------------------------------------------------*/
// Ingredients Functions
/*-------------------------------------------------------------------------*/	
ingredientsCounter = 0;
ingredientSetAddImageSrc = "";
ingredientSetDeleteImageSrc = "";

function configureIngredientsSettings(count, addImageSrc, deleteImageSrc) {
	ingredientsCounter = count;
	ingredientSetAddImageSrc = addImageSrc;
	ingredientSetDeleteImageSrc = deleteImageSrc;
}

function addElement(parentId, elementTag, elementId, html) {
	// Adds an element to the document
	var p = document.getElementById(parentId);
	var newElement = document.createElement(elementTag);
	if (elementId != "") { newElement.setAttribute('id', elementId); }
	newElement.innerHTML = html;
	p.appendChild(newElement);
}

function removeElement(elementId) {
	// Removes an element from the document
	var element = document.getElementById(elementId);
	element.parentNode.removeChild(element);
}

function ajaxDeleteIngredientSet(elementId) {
    if (confirm("Are you sure you want to delete this Ingredient?")) {
		removeElement(elementId);
	}
	checkForNoIngredients();
}

function checkForNoIngredients() {
	var p = document.getElementById('ingredients');
    if (p.innerHTML == "") {
		p.innerHTML = "You deleted all the Ingredients, <a href=\"\" onclick=\"javascript:clearIngredientsAndAddIngredientSet()(); return false;\">click here to add a new one</a>.";
	}
}

function clearIngredientsAndAddIngredientSet() {
	document.getElementById('ingredients').innerHTML == "";
	addIngredientSet('', '');
}
			
function addIngredientSet(qtyValue, nameValue) {
	ingredientsCounter++; // increment ingredientsCounter to get a unique ID for the new element
	
	// Create our TR
	addElement('ingredients', 'tr', 'ingredientSetRow' + ingredientsCounter, '');
	
	// Build our code for our items
	var ingredientSetQtyTDContents = "<input type=\"text\" name=\"ingredientSetQty[]\" size=\"10\" value=\"" + qtyValue + "\" />";
	var ingredientSetNameTDContents = "<input type=\"text\" name=\"ingredientSetName[]\" size=\"60\" value=\"" + nameValue + "\" /><a href=\"\" onclick=\"javascript:addIngredientSet('', ''); return false;\"><img src=\"" + ingredientSetAddImageSrc + "\" alt=\"Add New Ingredient\" /></a><a href=\"\" onclick=\"javascript:ajaxDeleteIngredientSet('ingredientSetRow" + ingredientsCounter + "'); return false;\"><img src=\"" + ingredientSetDeleteImageSrc + "\" alt=\"Remove This Ingredient\" /></a>";
	
	// Add the contents to the table
	addElement('ingredientSetRow' + ingredientsCounter, 'td', '', ingredientSetQtyTDContents);
	addElement('ingredientSetRow' + ingredientsCounter, 'td', '', ingredientSetNameTDContents);
}
