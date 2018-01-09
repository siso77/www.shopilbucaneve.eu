if(!usersUI)
    dhx.alert("config.js needs to be included first!");
/*
    Inits app
*/
usersUI.init = function(){
    if(usersUI.layout=="mobile"){
        dhx.ui.fullScreen();
        dhx.ui(usersUI.configMobile);
        usersUI.setFormEvents();
    }
    else{
        dhx.ui(usersUI.config);
    }
    usersUI.setEvents();
};
/*
    Sets events for views
*/
usersUI.setEvents = function(){
	/*shows user's details on list item selection*/
    $$('list').attachEvent("onItemClick", usersUI.showDetails);
	/*opens add popup on button click*/
    $$('add').attachEvent("onItemClick", usersUI.addUser);
	/*opens edit popup on button click*/
    $$('edit').attachEvent("onItemClick", usersUI.editUser);
    /*handler for "Back" button click (only for Mobile config)*/
    if(usersUI.layout=="mobile")
        $$('back').attachEvent("onItemClick", usersUI.back);
	/*adds key events to "filter" text control*/
	dhx.extend($$('filter'),dhx.KeyEvents);
	/*filtering on keypress with timeout*/
	$$('filter').attachEvent("onTimedKeyPress", usersUI.filterList);
};
/*
    Creates popup with add/edit form for personal data
*/
usersUI.initPopup = function(){
    dhx.ui(usersUI.configPopup);
    usersUI.setFormEvents();
};
/*
    Sets events handlers for form buttons
*/
usersUI.setFormEvents = function(){
    $$('save').attachEvent("onItemClick", usersUI.save);
    $$('cancel').attachEvent("onItemClick", usersUI.cancel);
    /*opens "delete" dialog on button click*/
    $$('delete').attachEvent("onItemClick", usersUI.deleteUser);
};
/*
    Displays personal data in "info" Template view
*/
usersUI.showDetails = function(id){
    if(usersUI.layout=="mobile")
        $$("infoView").show();
    $$("info").setValues($$('list').item(id));
};
/*
    Default data for a new user
*/
usersUI.defaultUser = {
	name: 'New user',
	age: '25',
	gender: 'male',
	country:'',
	city:'',
	phone:'',
	email:''
};
/*
    Opens edit form for a new user data
*/
usersUI.addUser = function(){
    usersUI.userId = dhx.uid();
    usersUI.showEditForm(usersUI.defaultUser);
};
/*
    Opens edit form for an existent user
*/
usersUI.editUser = function(){
    if(!$$("list").getSelected()){
        dhx.alert("Please select user data  for editing");
		return;
	}
    usersUI.userId = $$("list").getSelected();
    usersUI.showEditForm($$("list").item( usersUI.userId));
};
/*
    Displays edit form
*/
usersUI.showEditForm = function(data){
    if(usersUI.layout=="mobile"){
        $$("formView").show();
    }else{
        if(!$$("editPopup"))
            usersUI.initPopup();
        $$("editPopup").show();
        $$("editPopup").resize();
    }
    $$("form").setValues(data);
};
/*
   called on "Cancel" button click, closes edit form
*/
usersUI.cancel = function(){
    usersUI.closeEditForm();
};
/*
    Closes edit form
*/
usersUI.closeEditForm = function(view){
    if(usersUI.layout=="mobile"){
        $$(view||"infoView").show();
    }
    else{
        $$("editPopup").hide();
    }

};
/*
    Mobile Layout: Returns to list view
*/
usersUI.back = function(){
    $$("listView").show();
};
/*
    Clears "Personal details"
*/
usersUI.clearInfo = function(){
    $$("info").parse({});
};
/*
    Saves user data (sends request to server)
*/
usersUI.save = function(){
    var data = $$("form").getValues();
    data.id = usersUI.userId;
	/*if an existent user data was edited*/
    if($$("list").getSelected()==usersUI.userId){
        dhx.ajax().post("server/data.php?action=update", data, function(response){
		/* processing answer from server*/
		if (response != "false") {
			/*update a user in List and show it*/
			$$("list").update(data.id,data);
			$$("list").select(data.id);
            $$("list").showItem(data.id);
			/*displays details for the updated user*/
			usersUI.showDetails(data.id);
			/*closes edit form*/
            usersUI.closeEditForm();
		} else
			dhx.alert({
				title: "Update user data",
				message: "Some problem occurs during update"
			});

	    });
    }
	/*new user was added*/
    else{
        dhx.ajax().post("server/data.php?action=insert", data, function(response){
            /* processing answer from server*/
            if (response != "false") {
                data.id = response; /*set new id, from database*/
				/*add the new user in list*/
                $$("list").add(data);
                $$("list").select(data.id);
                $$("list").showItem(data.id);
				/*displays details for the added user*/
				usersUI.showDetails(data.id);
				/*closes edit form*/
                usersUI.closeEditForm();
            } else
                dhx.alert({
                    title: "New user",
                    message: "Some problem occurs during adding"
			    });
	    });
    }
};
/*
    Called when "Delete" button clicked
*/
usersUI.deleteUser = function(){
	var id = $$("list").getSelected();
	if (id){
        dhx.confirm({
            title: "Delete",
            message: "Are you sure you want to delete selected user?",
            callback: usersUI.confirmDelete,
            details:id
	    });
    }
};
/*
    Deletes a user from list
*/
usersUI.confirmDelete = function(confirm, details){
	if (confirm) {
		dhx.ajax().post(
			"server/data.php?action=delete",
			{ id: details },
			function(response) {
				if (response == "true") {
					/*removes an item from list*/
					$$("list").remove(details);
					/*clear "Personal details"*/
					usersUI.clearInfo();
					/*closes edit form*/
                    usersUI.closeEditForm("listView");
                    dhx.notice("Deleting finished");
				} else {
					dhx.alert({
						title: "Delete user",
						message: "Some problem occurs during deleting"
					});
				}
			}
		);
	}
};
/*
    Filters list items
*/
usersUI.filterList = function(){
    $$("list").unselectAll();
	$$("list").filter("#name#",$$("filter").getValue());
	usersUI.clearInfo();
};

