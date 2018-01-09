var usersUI = {};
usersUI.elements = {};
/*Top most toolbar*/
usersUI.elements.mainBar = {
    view:"toolbar",type:"MainBar", elements:[
        {view:"label", value:"Users", align:"center", css:"mainlabel"}
    ]
};
/*Top toolbar in List view*/
usersUI.elements.topListBar =  {
    view:"toolbar", type:"SubBar", elements:[
        {
           id: "filter",
           view: "text",
           label: "<div class='dhx_el_icon'><div class='dhx_el_icon_search' style='margin: 5px 0'></div></div>",
           labelWidth: 30
        }
    ]
};
/*Bottom toolbar in List view*/
usersUI.elements.bottomListBar =  {
    view:"toolbar", type:"BottomBar", elements:[
        {
            id: "add",
            view: "button",
            value: '+',
            align: "right",
            css: "add",
            inputWidth: 30

        }
    ]
};
/*Users list*/
usersUI.elements.list = {
    id: "list",
    view: "unitlist",
    select:true,
    sort:{
        by:"#name#"
    },
    uniteBy:function(obj){
        return obj.name.substr(0,1);
    },
    template:"#name#",
    url: "server/data.php"
};
/*Pad Layout: Top toolbar in User Info view*/
usersUI.elements.topUserBar =  {
    view:"toolbar", type:"SubBar", elements:[
        {
            view:"label",
            value:"Personal Details",
            align:"center",
            css: "info"
        }
    ]
};
/*Pad Layout: Bottom toolbar in User Info view*/
usersUI.elements.bottomUserBar =  {
    view:"toolbar", type:"BottomBar", elements:[
        {
            id: "edit",
            view: "button",
            value: 'Edit',
            inputWidth: 90,
            align: 'left'
        }
    ]
};
/*Mobile layout: Toolbar in User Info view*/
usersUI.elements.userBar =  {
    view:"toolbar", type:"SubBar", elements:[
        {
            id: "back",
            view: "button",
            type: "prev",
            value: 'List',
            inputWidth: 90,
            align: 'left'
        },
        {
            id: "edit",
            view: "button",
            value: 'Edit',
            inputWidth: 90,
            align: 'right',
            css:"main"
        }
    ]
};
/*Labels for properties of user's data*/
usersUI.infoLabels = {name:"Name",age:"Age",gender:"Gender",country:"Country",city:"City",phone:"Phone"};
/*User Info template view*/
usersUI.elements.userInfo = {
    id:"info",
    template: function(obj){
        var html,value;
        html = "<div class='userinfo'>";
        for(value in usersUI.infoLabels)
           html += "<div class='row'><div class='cell0'>"+usersUI.infoLabels[value]+"</div><div class='cell1'>"+(obj[value]||"")+"</div></div>";
        html += "</div>";
        return html;
    }
};
/*Toolbar in Form view*/
usersUI.elements.formBar =  {
    view:"toolbar", type:"SubBar", elements:[
        {
            id: "cancel",
            view:"button",
            value:"Cancel",
            align:"left",
            inputWidth:90,
            css:"cancel"
        },
        {
            id: "save",
            view:"button",
            value:"Save",
            inputWidth:90,
            css:"main",
            align:"right"
        }
    ]
};
/*Edit form for user's data*/
usersUI.elements.form ={
    id:"form",
    view:"form",
    elements: [
        {height:10},
        {name:"name",view:"text",label:usersUI.infoLabels["name"]},
        {name:"age",view:"text", label:usersUI.infoLabels["age"], maxlength:3},
        {name:"gender", view: "radio", value: "male", options:[
            { value:"female",label:"Female"},
            { value:"male",label:"Male" }
        ]},
        {name:"country",view:"text",label:usersUI.infoLabels["country"]},
        {name:"city",view:"text",label:usersUI.infoLabels["city"]},
        {name:"phone",view:"text",label:usersUI.infoLabels["phone"]},
        {height:10},
        {id:"delete", view:"button",type:"form",value:"Delete",css:"delete"}
    ]
};
usersUI.elements.winForm = {
     width:350
};
dhx.extend(usersUI.elements.winForm,usersUI.elements.form);

/*Popup with Edit Form (Pad Layout)*/
usersUI.configPopup = {
    view:"window",
    id:"editPopup",
    position: 'center',
    width:450,
    height:390,
    modal:true,
    head:false,
    body:{
        id:"formView",
        css:"form",
        rows:[
            usersUI.elements.formBar,
            {
                type:"clean",
                cols:[
                    {gravity:1},
                    usersUI.elements.winForm,
                    {gravity:1}

                ]
            }
        ]
    }
};
/* Configuration, Pad Layout */
usersUI.config = {
	rows:[
        usersUI.elements.mainBar,
        {
            type:"wide",
            cols:[
                {
                    width:320,
                    rows:[
                        usersUI.elements.topListBar,
                        usersUI.elements.list,
                        usersUI.elements.bottomListBar
                    ]
                },
                {
                    rows:[
                        usersUI.elements.topUserBar,
                        usersUI.elements.userInfo,
                        usersUI.elements.bottomUserBar
                    ]
                }
            ]
       }
	]
};
/* Configuration, Mobile Layout */
usersUI.configMobile = {
	rows:[
        usersUI.elements.mainBar,
        {
            id:"views",
            view:"multiview",
            cells:[
                {
                    id:"listView",
                    rows:[
                        usersUI.elements.topListBar,
                        usersUI.elements.list,
                        usersUI.elements.bottomListBar
                    ]
                },
                {
                    id:"infoView",
                    rows:[
                        usersUI.elements.userBar,
                        usersUI.elements.userInfo
                    ]
                },
                {
                    id:"formView",
                    css:"form",
                    rows:[
                        usersUI.elements.formBar,
                        usersUI.elements.form
                    ]
                }
            ]
        }
	]
};
