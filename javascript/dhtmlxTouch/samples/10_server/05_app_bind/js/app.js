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
    usersUI.bind();
    usersUI.initDP();
};
/*
    Binds form and template to lis
*/
usersUI.bind = function(){
    $$('info').bind($$('list'));
    if(usersUI.layout=="mobile"){
        $$('form').bind($$('list'));
    }
};
/*
    Sets events for views
*/
usersUI.setEvents = function(){
	/*shows user's details on list item selection*/
    $$('list').attachEvent("onItemClick", usersUI.showDetails);
	/*opens edit form on button click*/
    $$('add').attachEvent("onItemClick", usersUI.addUser);
	/*opens edit form on button click*/
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
    Creates a popup with add/edit form for personal data
*/
usersUI.initPopup = function(){
    dhx.ui(usersUI.configPopup);
    usersUI.setFormEvents();
	/*binds form to list*/
    $$('form').bind($$('list'));
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
    Shows "info" Template view (Mobile Layout)
*/
usersUI.showDetails = function(){
    if(usersUI.layout=="mobile")
        $$("infoView").show();
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
		dhx.alert("Please select user data for editing");
		return; 
	}
    usersUI.userId = $$("list").getSelected();
    usersUI.showEditForm();
};
/*
    Displays edit form
*/
usersUI.showEditForm = function(data){
    if(usersUI.layout=="mobile"){
		/*in "mobile" configution opens "formView" of multiview*/
        $$("formView").show();
    }else{
		/*opens window with edit form in ("pad" layout)*/
        if(!$$("editPopup"))
            usersUI.initPopup();
        $$("editPopup").show();
        $$("editPopup").resize();
    }
    if(data)
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
   Returns to list view ( Mobile Layout)
*/
usersUI.back = function(){
    $$("listView").show();
};
/*
    Saves user data (sends request to server)
*/
usersUI.save = function(){
    var data;
	/*if an existent user data was edited*/
    if($$("list").getSelected()==usersUI.userId){
        $$("form").save();
    }
	/*new user was added*/
    else{
        data = $$("form").getValues();
        data.id = usersUI.userId;
        $$("list").add(data);
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
		$$("list").remove(details);
	}
};
/*
* Inits DataProcessor for list
* */
usersUI.initDP = function(){
    /*creates DataProcessor object for List*/
    usersUI.dp = new dhx.DataProcessor({
	    master:$$("list"),
		url:"server/data.php"
	});
    /*response handlers*/
    usersUI.dp.attachEvent("onAfterUpdate",usersUI.processResponse);
    usersUI.dp.attachEvent("onAfterInsert",usersUI.processResponse);
    usersUI.dp.attachEvent("onAfterDelete",usersUI.processResponse);
    usersUI.dp.attachEvent("onDBError",usersUI.processError);
};
/*
    Called on update,insert or delete response from server
*/
usersUI.processResponse = function(obj){
	/*"delete" response*/
    if(obj.type == "delete"){
        dhx.notice("Deleting finished");
		/*closes edit form and in "mobile" layout opens listView*/
        usersUI.closeEditForm("listView");
    }
	/*"update" or "insert" response*/
    else{
        usersUI.closeEditForm();
        $$("list").select(obj.tid);
    }
};
/*
    Called on error response from server
*/
usersUI.processError = function(responseObj,requestObj){
    dhx.alert({
	    title: requestObj.operation+" user",
		message: "An error has occured during operation"
	});
};
/*
    Filters list items
*/
usersUI.filterList = function(){
    $$("list").unselectAll();
	$$("list").filter("#name#",$$("filter").getValue());
	/*clear "Personal data" after filtring*/
	$$("info").parse({});
};

