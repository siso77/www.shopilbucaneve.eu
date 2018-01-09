dhx.i18n.setLocale("en");

/*dhx.debug_bind = true;
dhx.debug_render = true;*/

dhx.Type(dhx.ui.list, {
	name:"EventsList",
	css:"events",
	cssNoEvents:"no_events",
	padding:0,
	height:42,
	width:"auto",
	dateStart:function(obj){ return dhx.i18n.dateFormatStr(obj.start_date); },
	timeStart:function(obj){ return dhx.i18n.timeFormatStr(obj.start_date); },
	color:dhx.Template("#color#"),
	marker:function(obj,type){return "<div class='dhx_event_marker' style='"+(obj.color?"background-color:"+type.color(obj):"")+"'></div>";},
	template:"<div class='dhx_day_title'>{common.dateStart()}</div><div style='margin:10px'><div class='dhx_event_time'>{common.timeStart()}</div>{common.marker()}<div class='dhx_event_text'>#text#</div></div>"	
});
dhx.Type(dhx.ui.list, {
	name:"DayEventsList",
	css:"day_events",
	cssNoEvents:"no_events",
	padding:0,
	height:42,
	width:"auto",
	timeStart:function(obj){ return dhx.i18n.timeFormatStr(obj.start_date); },
	color:dhx.Template("#color#"),
	marker:function(obj,type){return "<div class='dhx_event_marker' style='"+(obj.color?"background-color:"+type.color(obj):"")+"'></div>";},
	template:"{common.marker()}<div class='dhx_event_time'>{common.timeStart()}</div><div class='dhx_event_text'>#text#</div>"	
});
dhx.DataDriver.scheduler = {
    records:"/*/event"
};
dhx.extend(dhx.DataDriver.scheduler,dhx.DataDriver.xml);

dhx.protoUI({
	name:"scheduler",
    defaults:{
		rows:[
			{
				view:"multiview",
				id:"schViews",
				cells:[
					{
						id:"schEvents",
						view:"list",
						type:"EventsList",
						startDate:new Date()
					},
					{
						id:"schDayEvents",
						rows:[
							{	
								id:"schDayEventsBar",
								view:"toolbar",
								css:"dhx_dayeventsbar",
								elements:[
									{view:'label',id:"prev",align:"left",label:"<div class='dhx_cal_prev_button'><div></div></div>",width:70},
									{view:'label',id:"date",align:"center"},
									{view:'label',id:"next",align:"right",label:"<div class='dhx_cal_next_button'><div></div></div>",width:70}
								]
							},
							{
								id:"schDayEventsList",
								view:"dayevents"
							}
						]
					},
					{
						id:"monthEvents",
						rows:[
							{
								id:"schCalendar",
								view:"calendar",
								dayWithEvents:function(date){
									return date+"<div class='day_with_events'></div>";
								}
							},
							{
								id:"schCalendarDayEvents",
								view:"list",
								type:"DayEventsList"
							}
						]
						
					},
					{
						id:"schSelectedEvent",
						animate:{
							type:"slide",
							subtype:"in",
							direction:"top"
						},
						rows:[
							{
								id:"schSelectedEventBar",
								view:"toolbar",
								type:"DayEventsBar",
								css:"single_event",
								elements:[
									{view:'button', inputWidth:100, type:"prev", id:"back",align:"left",label:"Back"},
									{view:'button', inputWidth:100, id:"edit",align:"right",label:"Edit"}
								]
							},
							{
								id:"schSelectedEventTemplate",
								view:"template",
								template:function(obj){
									var html = "";
									if(!obj.start_date) return html;
									html += "<div  class='selected_event'>";
									html += "<div class='event_title'>"+obj.text+"</div>";
									if(dhx.Date.datePart(obj.start_date).valueOf()==dhx.Date.datePart(obj.end_date).valueOf()){
										var fd = dhx.i18n.dateFormatStr(obj.start_date);
										var fts = dhx.i18n.timeFormatStr(obj.start_date);
										var fte = dhx.i18n.timeFormatStr(obj.end_date);
										html += "<div class='event_text'>"+fd+"</div>";
										html += "<div class='event_text'>from "+fts+" to "+fte+"</div>";
									}
									else{
										var fds = dhx.i18n.longDateFormatStr(obj.start_date);
										var fde = dhx.i18n.longDateFormatStr(obj.end_date);
										var fts = dhx.i18n.timeFormatStr(obj.start_date);
										var fte = dhx.i18n.timeFormatStr(obj.end_date);
										html += "<div class='event_text'>from "+fts+" "+fds+"</div>";
										html += "<div class='event_text'>to "+fte+" "+fde+"</div>";
									}
									if(obj.notes&&obj.notes!==""){
										html += "<div class='event_title'>Notes</div>";
										html += "<div class='event_text'>"+obj.notes+"</div>";
									}
									html += "</div>";
									return html;
								}
							}
						
						]
					},
					{
						id:"schEditEvent",
						rows:[
							{	
								id:"schEditBar",
								view:"toolbar",
								type:"SchedulerBar",
								elements:[
									{view:'button', inputWidth:100, id:"cancel",css:"cancel",align:"left",label:"Cancel"},
									{view:'button', inputWidth:100, id:"save",align:"right",label:"Save"}
								]
							},
							{
								id:"schEditForm",
								view:"form",
								elements:[
									{view:"text",		label:"Event",	name:'text'},
									{view:"datepicker",	label:"Start",	name:'start_date',	timeSelect:1, dateFormat:dhx.i18n.fullDateFormat},
									{view:"datepicker",	label:"End",	name:'end_date',	timeSelect:1, dateFormat:dhx.i18n.fullDateFormat},
									{view:"textarea",	label:"Notes",	name:'notes',		width:300, height:200},
									{view:"button",		label:"Delete event",	id:'delete',type:"form" ,css:"delete"}
								],
								rules:{
									end_date:function(value,obj){
										return (obj.start_date.valueOf() < value.valueOf());
									}
								}
							}
						]
					}
				]
			},
			{
				view:"toolbar",
				id:"schToolbar",
				type:"SchedulerBar",
				elements:[
		 			{ view:"button",id:"today",label:"Today",inputWidth:72, align:"left"},
		 			{ view:"segmented", id:"buttons",selected:"schEvents",width:185, align:"center",multiview:true, options:[
						{value:"schEvents", label:"List",width:54},
						{value:"schDayEvents", label:"Day",width:54},
		    			{value:"monthEvents", label:"Month",width:68}
					]},
					{ view:"button",css:"add",id:"add", align:"right",label:"",inputWidth:42}
				]
			}
		],
		color:"#color#",
		textColor:"#textColor#"
	},
	$init: function() {
    	this.name = "Scheduler";
		this._viewobj.className += " dhx_scheduler";
		this.data.provideApi(this);
		this.data.extraParser = dhx.bind(function(data){
			data.start_date	= dhx.i18n.fullDateFormatDate(data.start_date);
			data.end_date 	= dhx.i18n.fullDateFormatDate(data.end_date);
		},this);
		
		this.$ready.push(this.initStructure);
		this.data.attachEvent("onStoreUpdated", dhx.bind(this._sortDates,this));
    },
    initStructure:function(){
		this.initToolbars();
		this.initMonthEvents();
		

		//store current date
		this.coreData = new dhx.DataValue();
		this.coreData.setValue(new Date());
		
		this.$$("schDayEventsList").define("date",this.coreData);
		
		this.selectedEvent = new dhx.DataRecord();
		
		
		
		if(!this.config.readonly&&this.config.save)
			new dhx.DataProcessor({
				master:this,
				url:this.config.save
			});
			
		
		this.$$("date").bind(this.coreData, null, dhx.i18n.dateFormatStr);
		
		
		this.$$("schEvents").sync(this);
		this.$$("schEvents").bind(this.coreData, function(target, source){
			return source < target.end_date;
		});
		
		this.$$("schDayEventsList").sync(this, true);
		this.$$("schDayEventsList").bind(this.coreData, function(target, source){
			var d = dhx.Date.datePart(source);
			return d < target.end_date && dhx.Date.add(d,1,"day") > target.start_date;
		});
		
		this.$$("schCalendar").bind(this.coreData);
		
		this.$$("schCalendarDayEvents").sync(this, true);
		this.$$("schCalendarDayEvents").bind(this.coreData, function(target, source){
			var d = dhx.Date.datePart(source);
			return d < target.end_date && dhx.Date.add(d,1,"day") > target.start_date;
		});
		
		this.$$("schSelectedEventTemplate").bind(this);
		this.$$("schEditForm").bind(this);
		
		this.$$("schEvents").attachEvent("onItemClick", dhx.bind(this._on_event_clicked, this));
		this.$$("schDayEventsList").attachEvent("onItemClick", dhx.bind(this._on_event_clicked, this));
		this.$$("schCalendarDayEvents").attachEvent("onItemClick", dhx.bind(this._on_event_clicked, this));
	},
	_on_event_clicked:function(id){
		this.setCursor(id);
		this.$$('schSelectedEvent').show();
	},
	/*Sorts dates asc, gets hash of dates with event*/
	_sortDates:function(){
		this.data.blockEvent();
		this.data.sort(function(a,b){
			return a.start_date < b.start_date?1:-1;
		});
		this.data.unblockEvent();
		this._eventsByDate = {};
		var evs = this.data.getRange();
		for(var i = 0; i < evs.length;i++)
			this._setDateEvents(evs[i]);
	},
	/*Month Events view: gets dates of a certain event*/
	_setDateEvents:function(ev){
		var start = dhx.Date.datePart(ev.start_date);
		var end = dhx.Date.datePart(ev.end_date);
		if(ev.end_date.valueOf()!=end.valueOf())
			end = dhx.Date.add(end,1,"day");
		while(start<end){
		    this._eventsByDate[start.valueOf()]=true;
			start = dhx.Date.add(start,1,"day");
		}
	},
	/* Month Events view: sets event handlers */
	initMonthEvents:function(){
		this.$$("schCalendar").attachEvent("onDateSelect",dhx.bind(function(date){
			this.setDate(date);
		},this));
		
		this.$$("schCalendar").attachEvent("onAfterMonthChange",dhx.bind(function(date){
			var today = new Date();
			if(date.getMonth()===today.getMonth()&&date.getYear()===today.getYear())
				date = today;
			else
				date.setDate(1);
			this.setDate(date);
		},this));
		
		var dayFormat = this.$$("schCalendar").config.calendarDay;
		this.$$("schCalendar").config.calendarDay=dhx.bind(function(date){
			var html = dayFormat(date);
			if(this._eventsByDate&&this._eventsByDate[date.valueOf()])
				return this.$$("schCalendar").config.dayWithEvents(html);
			return html;
		},this);
	},

	/*applies selected date to all lists*/
	setDate:function(date, inc, mode){
		if (!date)
			date = this.coreData.getValue();
		if (inc)
			date = dhx.Date.add(date, inc, mode);
		this.coreData.setValue(date);
	},
	initToolbars:function(){
		this.attachEvent("onItemClick", function(id){
			var view_id = this.innerId(id);
			switch(view_id){
				case "today":
					this.setDate(new Date());	
					break;
				case "add":
					this.$$("delete").hide();
					
					this.define("editEvent",false);
					
					this.$$("schEditEvent").show();
					
					var d = dhx.Date.add(new Date(),1,"hour");
					var start = new Date(d.setMinutes(0));
					var end = dhx.Date.add(start,1,"hour");
					this.$$("schEditForm").setValues({start_date:start,end_date:end,text:"",notes:""});
					break;
				case "prev":
					this.setDate(null, -1, "day");
					break;
		    	case "next":
		    		this.setDate(null, 1, "day");
		    		break;
		    	case "edit":
					this.$$("delete").show();
					this.define("editEvent",true);
					this.$$("schEditEvent").show();
					break;
				case "back":
					this.$$("schViews").back();
					break;
				case "cancel":
					/*if(!this._settings.editEvent)
						this.remove(this.getCursor());*/
					this.$$("schViews").back();
					break;
				case "save":
					if(this.$$("schEditForm").validate()){
						if(!this._settings.editEvent){
							var data = this.$$("schEditForm").getValues();
							data.id = dhx.uid();
							this.add(data);
							this.setCursor(data.id);
						} else {
							this.$$("schEditForm").save();
						}
						dhx.dp(this).save();
						this.setDate();
						this.$$("schViews").back();
					}
					break;
				case "delete":
					this.remove(this.getCursor());
					this.$$("schViews").back(2);
					break;
				default:
					//do nothing
					break;
			}		
		});
		this.attachEvent("onAfterTabClick", function(id, button){
			this.$$(button).show();
		});

	},
	readonly_setter:function(val){
		if (val)
			this.$$("add").hide();
			this.$$("edit").hide();
		return val;
	},
	/*removes "No events" background*/
	_clearNoEventsStyle:function(){
		if(this.dataCount())
			this._viewobj.className = this._viewobj.className.replace(RegExp(this.type.cssNoEvents, "g"),"");
		else 
			this._viewobj.className += " "+this.type.cssNoEvents;
	}
}, dhx.IdSpace, dhx.DataLoader, dhx.ui.layout, dhx.EventSystem, dhx.Settings);

