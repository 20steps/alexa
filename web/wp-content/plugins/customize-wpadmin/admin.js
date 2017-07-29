function MoveUpCustomMenu(id) {
	document.getElementById("CustomizeWPAdmin").elements.namedItem('CustomizeWPAdmin_Cmd2').value = "MoveUp";
	document.getElementById("CustomizeWPAdmin").elements.namedItem('CustomizeWPAdmin_Cmd2Id').value = id;
	document.getElementById("CustomizeWPAdmin").submit();
}

function MoveDnCustomMenu(id) {
	document.getElementById("CustomizeWPAdmin").elements.namedItem('CustomizeWPAdmin_Cmd2').value = "MoveDn";
	document.getElementById("CustomizeWPAdmin").elements.namedItem('CustomizeWPAdmin_Cmd2Id').value = id;
	document.getElementById("CustomizeWPAdmin").submit();
}

function DeleteCustomMenu(id) {
	document.getElementsByName("CustomMenu_Cmd[" + id + "]")[0].value = "Delete";
	var tr = document.getElementById("CustomMenu_TR[" + id + "]");
	tr.style.backgroundColor = "#ff0000";
	tr.cells[0].innerHTML = "<a href='javascript:RestoreCustomMenu(" + id + ")'>Restore</a>";
}

function RestoreCustomMenu(id) {
	document.getElementsByName("CustomMenu_Cmd[" + id + "]")[0].value = "";
	var tr = document.getElementById("CustomMenu_TR[" + id + "]");
	tr.style.backgroundColor = "";
	tr.cells[0].innerHTML = "<a href='javascript:DeleteCustomMenu(" + id + ")'>Delete</a>";
}

function ShowHideIconOptions(id) {
	var options = document.getElementById("CustomMenu_IconOptions[" + id + "]");
	if (options.style.display == "none") 
		options.style.display = "";
	else
		options.style.display = "none";
}

function SelectIcon(id, icon) {
	var options = document.getElementById("CustomMenu_IconOptions[" + id + "]").getElementsByTagName("div");
	for (var i = 0; i <= options.length - 1; i++) {
		if (options[i].getAttribute("value") == icon)
			options[i].style.border = "solid 3px #0000ff";
		else
			options[i].style.border = "";
	}
	document.getElementById("CustomMenu_IconPreview[" + id + "]").className = "dashicons dashicons-" + icon;
	document.getElementsByName("CustomMenu_Icon[" + id + "]")[0].value = icon;
	ShowHideIconOptions(id);
}
