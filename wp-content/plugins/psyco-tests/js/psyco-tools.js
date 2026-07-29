function Zebra ()
{
	jQuery ('tbody tr:visible:odd').css ('background-color','#909090');
	jQuery ('tbody tr:visible:even').css ('background-color','white');
}

function set_tree_checkbox_click_handler () {
	
	jQuery('input:checkbox').click(function() {
	var new_value = jQuery(this).prop('checked');
	jQuery(this).prop('indeterminate', false);
	if (jQuery(this).parent().next().is('ul')) {
		jQuery(this).parent().next().find('input:checkbox').prop ('checked', new_value);
		jQuery(this).parent().next().find('input:checkbox').prop ('indeterminate', false);
	}
	let root = jQuery(this).parents('ul').last();


	//alert (root.find ('ul').length);
	root.find ('ul').each(function () {
			let uncheck_cnt = jQuery(this).find ('input:checkbox:not(:checked)').length;
	
			if (uncheck_cnt!= 0 && uncheck_cnt != jQuery(this).find ('input:checkbox').length) {
				//jQuery(this).find('input:checkbox:first').prop ('indeterminate', true);
				jQuery(this).prev().find ('input:checkbox:first').prop ('indeterminate', true);
				jQuery(this).prev().find ('input:checkbox:first').prop ('checked', false);
				
			}
			else {
				jQuery(this).prev().find ('input:checkbox:first').prop ('indeterminate', false);
				jQuery(this).prev().find ('input:checkbox:first').prop ('checked', uncheck_cnt == 0);
			}
	});
	let elems = root.find ('input:checkbox:not(:checked)');
	let uncnt = elems.length;
	let elem1 = elems.first();
	let temp =	root.find('input:checkbox:first');
	let un = uncnt > 0 && uncnt != root.find ('input:checkbox').length && !(uncnt == 1 && temp.prop('checked') === false);
	temp.prop('indeterminate',un);
	temp.prop('checked', uncnt==0 || (uncnt == 1 && un == false));

	apply_organization_filter();
	});
}
function apply_organization_filter(){
	jQuery('tr').show();
	var tags_checked = jQuery('input:checkbox:checked').map(function(){return this.value;});
	// Loop through all the table rows
	jQuery("tr:not(:first)").each(function() {
		// Assume that the row should be hidden
		var hide = true;
		// Loop through all the cells in the row
		jQuery(this).find("td").each(function() {
			// Get the cell text
			var text = jQuery(this).text();
			// Check if the cell text is in the array
			if (jQuery.inArray(text, tags_checked) !== -1) {
				// If yes, the row should not be hidden
				hide = false;
				// Break the loop
				return false;
			}
		});
		// Hide the row if needed
		if (hide) {
		jQuery(this).hide();
		}
	});
	var tags_unchecked = jQuery('input:checkbox:not(:checked)').map(function(){return this.value;});
	jQuery('td').each (function () {	
		if(jQuery.inArray (jQuery(this).html() , tags_unchecked ) > -1) {
			jQuery(this).parent().hide();
		}
		if (jQuery(this).html() == null) {
			jQuery(this).hide();
		}
	});
	
	Zebra();
}
function set_organization_filter_handler() {
	set_tree_checkbox_click_handler ();
	apply_organization_filter();
}

function set_search_box(search_box_id, search_table_id){
	// Select the input element by its id
	jQuery("#" + search_box_id).on("keyup", function() {
		// Get the input value and convert it to uppercase
		var value = jQuery(this).val().toUpperCase();
		// Select the table rows by their tag name
		jQuery("#" + search_table_id + " tr:not(:first)").each(function(){
			// Get the text of the first and second cell of each row
			var name = jQuery(this).find("td:eq(1)").text().toUpperCase();
			var phone = jQuery(this).find("td:eq(2)").text().toUpperCase();
			// Check if the input value matches the name or the phone number
			if (name.includes(value) || phone.includes(value)) {
			// Show the row if it matches
				jQuery(this).show();
			} else {
			// Hide the row if it does not match
				jQuery(this).hide();
			}
		});
		Zebra();
	}); 
}

function reloadCSS() {
	// Get the link element that contains the CSS file
	var link = document.getElementById("psyco_tests_style-css");

	// Get the current href attribute of the link element
	var href = link.getAttribute("href");

	// Append a new version number to the href attribute
	href += "?v=" + Date.now();

	// Set the new href attribute to the link element
	link.setAttribute("href", href);
}