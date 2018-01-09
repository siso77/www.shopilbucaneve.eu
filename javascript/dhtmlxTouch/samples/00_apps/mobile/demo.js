function correctDemo(config){
	if (dhx.env.isIE)
		alert("Sorry, this demo doesn't work in Internet Explorer")
	if (dhx.env.mobile || document.location.hash=="#menu"){
		document.body.innerHTML="";
		if (config){
			delete config.container;
			if (document.location.hash=="#menu")
				config.rows.splice(0,1)
		}
	}
}
