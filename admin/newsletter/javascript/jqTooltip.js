/**
 * zudolab jqTooltip
 *
 * @version    1
 * @copyright    (c)2008 Takeshi Takatsudo (http://zudolab.net/)
 * @license    MIT (http://www.opensource.org/licenses/mit-license.php)
 */

(function($){

	var IE6 = $.browser.msie && $.browser.version=="6.0";
	
	/* vars for current tip info */
	
	var currentTriggerElem = null;
	var currentTipElem = null;
	var currentBgIframe = null;
	var currentTipOffsetX = null;
	var currentTipOffsetY = null;
	var currentTipPosition = null;
	var currentTipCSSWidth = null;
	var currentTipCSSHeight = null;
	var currentWidthShrinked = false;
	var currentSingleTip = null;
	var isAnyTipVisible = false;
	var showTipTimer = null;
	
	jqTooltip = function(s)
	{
		/* settings */
		
		this.selector = s.selector;
		this.behavior = s.behavior ? s.behavior : "chaseHover"; // chaseHover or click
		this.separator_title = s.separator_title ? s.separator_title : " --- ";
		this.separator_br = s.separator_br ? s.separator_br : " -- ";
		this.delay = s.delay ? s.delay : "100";
		this.offsetX = s.offsetX ? s.offsetX : 20;
		this.offsetY = s.offsetY ? s.offsetY : -8;
		this.tipPosition = s.tipPosition ? s.tipPosition : "right"; // right or left
		this.width = s.width ? s.width : "auto"; // css length value
		this.height = s.height ? s.height : "auto"; // css length value
		this.opacity = s.opacity ? s.opacity : null; // 0 <= n <= 1
		this.iframeLayer = s.iframeLayer ? s.iframeLayer : false;
		this.iframeSrc =  s.iframeSrc ? s.iframeSrc : "javascript:false;";
		this.singleTip = s.singleTip ? s.singleTip : false;
		this.showURL = s.showURL ? s.showURL : false;
		this.maxWidth = s.maxWidth ? s.maxWidth : null; // int
		this.keepNaturalWidth = s.keepNaturalWidth ? s.keepNaturalWidth : false;
		this.tipClass = s.tipClass ? s.tipClass : null;
		this.tipHTML = s.tipHTML ? s.tipHTML :
			(function(){
				var html="";
				html+= '<div class="tipContainer">';
				html+= '<p class="title">$TITLE$</p>';
				html+= '<p class="main">$MAIN$</p>';
				html+= '<p class="URL">$URL$</p>';
				html+= '<p class="close"><span>close</span></p>';
				html+= '</div>';
				return html;
			})();
		
		/* vars for tipSet info */
		
		this.elemSets = [];
		this.tipBorderWTop = 0;
		this.tipBorderWBottom = 0;
		this.tipBorderWRight = 0;
		this.tipBorderWLeft = 0;
		
		/* exec setup when onload */
		var self = this;
		$(function(){ self.setup(); });
	}
	
	/**
	 *  setup
	 */
		jqTooltip.prototype.setup = function()
		{
			this.prepareElemSets();
			this.setEvents();
		}
		
	/**
	 *  prepareElemSets
	 */
		jqTooltip.prototype.prepareElemSets = function()
		{
			var self = this;
			var $triggerElems = $(self.selector);
			for(var i=0,triggerElem; triggerElem=$triggerElems[i]; i++){
				var $triggerElem = $(triggerElem);
				var tipText = getTipText(triggerElem);
				if(!tipText) continue;
				
				/* generate tipHTML */
				
				var $tipHTML = generateTipHTML(tipText);
				$("body").append($tipHTML);
				
				/* generate bgIframe */
				
				var $bgIframe = null;
				if(self.iframeLayer && IE6){
					$bgIframe = generateBgIframe();
					$("body").append($bgIframe);
				}
				
				/*
				 * caliculate naturalWidth
				 * - if maxWidth was specified, naturalWidth = maxWidth.
				 */
				var naturalWidth = $tipHTML.innerWidth();
				var widthShrinked = false;
				if(self.width=="auto"){
					if(self.maxWidth && naturalWidth>self.maxWidth){
						widthShrinked = true;
						naturalWidth = self.maxWidth;
					}
					if(self.keepNaturalWidth && !widthShrinked){
						$tipHTML.width(naturalWidth);
						if($bgIframe) $bgIframe.width(naturalWidth);
					}
				}
				
				/* create elemSet */
				
				self.elemSets.push({
					triggerElem: triggerElem,
					tipContainerElem: $tipHTML.get(0),
					bgIframe: $bgIframe ? $bgIframe.get(0) : null,
					closeButton: $tipHTML.find(".close").eq(0).get(0),
					naturalWidth: naturalWidth,
					widthShrinked: widthShrinked
				});
			}
			
			/* caliculate border width for bgiframe */
			
			if(self.elemSets.length>0 && self.iframeLayer && IE6){
				var $tip = $(self.elemSets[0].tipContainerElem);
				self.tipBorderWTop = getBorderWidthNum($tip, "border-top-width");
				self.tipBorderWBottom = getBorderWidthNum($tip, "border-bottom-width");
				self.tipBorderWRight = getBorderWidthNum($tip, "border-right-width");
				self.tipBorderWLeft = getBorderWidthNum($tip, "border-left-width");
			}
			
			/* append bgiframe into the tipHTML. This is for IE6 only */
			
			function generateBgIframe(){
				var html =
					'<iframe class="bgIframe" frameborder="0" tabindex="-1" src="'+self.iframeSrc+'"'+
						' style="display:none;position:absolute;filter:Alpha(Opacity=\'0\')'+
					'"/>';
				return $(html);
			}
			
			/* get title value and return tipText object */
			
			function getTipText(elem){
				var $elem = $(elem);
				var titleVal = $elem.attr("title");
				var URL = elem.href ? $elem.attr("href") : null;
				if(!titleVal && (!self.showURL || (self.showURL && !URL))) return null;
				var titleText,mainText = null;
				if(titleVal){
					$elem.attr("title","").attr("alt",""); // disable browser native tooltip
					
					/* separate title and body */
					if(titleVal.indexOf(self.separator_title)>-1){
						var splittedTexts = titleVal.split(self.separator_title);
						titleText = splittedTexts[0];
						mainText = splittedTexts[1];
					}else{
						mainText = titleVal;
					}
					
					/* replace br */
					function replaceBr(text){
						text = text.replace((new RegExp(self.separator_br,"g")),"<br />");
						return text;
					}
					if(titleText) titleText = replaceBr(titleText);
					if(mainText) mainText = replaceBr(mainText);
				}
				return {
					title: titleText,
					main: mainText,
					URL: URL
				};
			}
			
			/* generate tipHTML from tipText. return the HTML as jQuery object */
			
			function generateTipHTML(tipText){
				var tipHTML = self.tipHTML;
				var tipClass = self.tipClass;
				
				/* insert texts into the tipHTML */
				if(tipText.title) tipHTML = tipHTML.replace("$TITLE$",tipText.title);
				if(tipText.main) tipHTML = tipHTML.replace("$MAIN$",tipText.main);
				if(self.showURL) tipHTML = tipHTML.replace("$URL$",tipText.URL);
				
				var $tipHTML = $(tipHTML);
				
				/* remove elements if they were not specified */
				if(!tipText.title) $tipHTML.find(".title").remove();
				if(!tipText.main) $tipHTML.find(".main").remove();
				if(!self.showURL || !tipText.URL) $tipHTML.find(".URL").remove();
				if(self.behavior!="click") $tipHTML.find(".close").remove();
				
				/* tipClass, opacity, width, height features */
				if(tipClass) $tipHTML.addClass(tipClass);
				if(self.opacity) $tipHTML.css("opacity", self.opacity);
				$tipHTML
					.css("width",self.width)
					.css("height",self.height);
					
				return $tipHTML;
			}
		}
		
	/**
	 *  setEvents
	 */
		jqTooltip.prototype.setEvents = function()
		{
			var self = this;
			for(var i=0,elemSet; elemSet=self.elemSets[i]; i++){
				triggerElem = elemSet.triggerElem;
				switch(self.behavior){
					case "click":
						setClickEvent(triggerElem);
						break;
					case "chaseHover":
						setChaseHoverEvent(triggerElem);
						break;
					default:
						break;
				}
			}
			function setClickEvent(triggerElem){
				$(triggerElem).click(function(evt){
					var elemSet = self.getAssociatedElemSetFromTriggerElem(triggerElem);
					setCurrentTipInfo(elemSet);
					updateTipPos(evt);
					if(currentSingleTip){
						self.closeAllTips();
						$(elemSet.tipContainerElem).css("display","block");
					}else{
						$(elemSet.tipContainerElem).appendTo($(document.body)).css("display","block"); // replace z-index.
					}
					if(elemSet.bgIframe) $(elemSet.bgIframe).css("display","block");
				});
				var elemSet = self.getAssociatedElemSetFromTriggerElem(triggerElem);
				$(elemSet.closeButton).click(hideAssociatedTipFromCloseButton);
			}
			function setChaseHoverEvent(triggerElem){
				$(triggerElem).hover(function(evt){
					enableChaseHover(triggerElem,evt);
				},function(){
					disableChaseHover();
				});
			}
			function setCurrentTipInfo(elemSet){
				currentTriggerElem = elemSet.triggerElem;
				currentTipElem = elemSet.tipContainerElem;
				currentBgIframe = elemSet.bgIframe;
				currentTipNaturalWidth = elemSet.naturalWidth;
				currentTipOffsetX = self.offsetX;
				currentTipOffsetY = self.offsetY;
				currentTipRight = self.right;
				currentTipPosition = self.tipPosition;
				currentTipCSSWidth = self.width;
				currentTipCSSHeight = self.height;
				currentWidthShrinked = elemSet.widthShrinked;
				currentSingleTip = self.singleTip;
				currentTipBorderWTop = self.tipBorderWTop;
				currentTipBorderWBottom = self.tipBorderWBottom;
				currentTipBorderWRight = self.tipBorderWRight;
				currentTipBorderWLeft = self.tipBorderWLeft;
			}
			function enableChaseHover(triggerElem,evt)
			{
				var elemSet = self.getAssociatedElemSetFromTriggerElem(triggerElem);
				setCurrentTipInfo(elemSet);
				if(showTipTimer){
					// cancel other tipTimers
					clearInterval(showTipTimer);
					showTipTimer = null;
				}
				$(document.body).mousemove(updateTipPos);
				showTipTimer = setTimeout(function(){
					if(currentBgIframe) $(currentBgIframe).css("display","block");
					$(currentTipElem).css("display","block");
					showTipTimer = null;
					$(elemSet.tipContainerElem).appendTo($(document.body)); // replace z-index.
				},self.delay);
			}
			function updateTipPos(evt)
			{
				var $tip = $(currentTipElem);
				var $bgIframe = currentBgIframe ? $(currentBgIframe) : null;
				var tipNaturalWidth = currentTipNaturalWidth;
				var CSSWidth = currentTipCSSWidth;
				var viewport = getViewportInfo();
				var mousePosX = evt.pageX;
				var mousePosY = evt.pageY;
				var offsetX = currentTipOffsetX;
				var offsetY = currentTipOffsetY;
				var CSSLeft,CSSRight,CSSTop;
				var widthShrinked = currentWidthShrinked;
				
				/* 
				 * caliculate width.
				 * if maxWidth was set and naturalWidth was shrinked,
				 * set maxWidth as width to avoid too wide tip
				 */
				var computedTipWidth = $tip.innerWidth();
				var computedTipHeight = $tip.innerHeight();
				if(widthShrinked && computedTipWidth>tipNaturalWidth){
					$tip.width(tipNaturalWidth);
					computedTipWidth = $tip.innerWidth();
				}
				
				/* 
				 * caliculate CSSLeft or CSSRight.
				 * invert direction of tip if the tip make the browser appear scroll-x
				 */
				var fixedWidth = 
					CSSWidth!="auto" || 
					widthShrinked || 
					(CSSWidth=="auto" && self.keepNaturalWidth);
				
				switch(currentTipPosition){
					case "right":
						if(fixedWidth && (viewport.width+viewport.scrollLeft<mousePosX+computedTipWidth+offsetX+5)){
							// if tip occured the overflow-x, change it to left side.
							// but, if maxWidth was not set or swapped tip was too wide, ignore this.
							if(mousePosX>computedTipWidth+offsetX){
								CSSRight = viewport.width-mousePosX+offsetX;
							}else{
								CSSLeft = mousePosX+offsetX;
							}
						}else{
							CSSLeft = mousePosX+offsetX;
						}
						break;
					case "left":
						if(fixedWidth && (mousePosX<computedTipWidth+offsetX+5)){
							// if tip occured the overflow-x, change it to right side.
							// but, if maxWidth was not set or swapped tip was too wide, ignore this.
							if(viewport.width<mousePosX+computedTipWidth+offsetX){
								CSSRight = viewport.width-mousePosX+offsetX;
							}else{
								CSSLeft = mousePosX+offsetX;
							}
						}else{
							CSSRight = viewport.width-mousePosX+offsetX;
						}
						break;
					default:
						break;
				}
				
				/* 
				 * caliculate CSSTop.
				 * adjust cssTop if the tip was over the window
				 */
				if(offsetY<0 && mousePosY-viewport.scrollTop+offsetY<0){
					/* if top was over the window, avoid it */
					if(offsetX>0){
						CSSTop = viewport.scrollTop;
					}else{
						CSSTop = mousePosY+offsetY;
					}
				}else if(mousePosY+computedTipHeight+offsetY>viewport.scrollTop+viewport.height){
					/* if bottom was over the window, avoid it */
					if(offsetX>0){
						CSSTop = viewport.scrollTop+viewport.height-computedTipHeight;
					}else{
						CSSTop = mousePosY+offsetY;
					}
				}else{
					CSSTop = mousePosY+offsetY;
					/* if the tip content was too much and hidden, avoid it */ 
					if(CSSTop+computedTipHeight>viewport.scrollTop+viewport.height){
						CSSTop = viewport.scrollTop;
					}
				}
				
				/* set tip position */
				
				$tip.css("top",CSSTop);
				if(CSSLeft) $tip.css({ "right":"auto", "left":CSSLeft });
				if(CSSRight) $tip.css({ "left":"auto", "right":CSSRight });
				if($bgIframe){
					$bgIframe.css("top",CSSTop)
					if(CSSLeft) $bgIframe.css({ "right":"auto", "left":CSSLeft });
					if(CSSRight) $bgIframe.css({ "left":"auto", "right":CSSRight });
					$bgIframe.width($tip.innerWidth()+self.tipBorderWRight+self.tipBorderWLeft); // add border width
					$bgIframe.height($tip.innerHeight()+self.tipBorderWTop+self.tipBorderWBottom); // add border width
				}
			}
			function disableChaseHover()
			{
				if(showTipTimer){
					// if the showTimer was in progress, cancel this.
					clearInterval(showTipTimer);
					showTipTimer = null;
				}else{
					// else, hide the tip
					$(currentTipElem).css("display","none");
					if(currentBgIframe) $(currentBgIframe).css("display","none");
				}
				$(document.body).unbind("mousemove",updateTipPos);
				releaseCurrentTipInfo();
			}
			function hideAssociatedTipFromCloseButton(){
				var closeButton = this;
				var elemSet = self.getAssociatedElemSetFromCloseButton(closeButton);
				$(elemSet.tipContainerElem).css("display","none");
				if(elemSet.bgIframe) $(elemSet.bgIframe).css("display","none");
				releaseCurrentTipInfo();
			}
		}
		
	/**
	 *  elemSet getter functions
	 */
		jqTooltip.prototype.getAssociatedElemSetFromCloseButton = function(closeButton)
		{
			for(var i=0,elemSet; elemSet=this.elemSets[i]; i++){
				if(elemSet.closeButton==closeButton) return elemSet;
			}
		}
		jqTooltip.prototype.getAssociatedElemSetFromTriggerElem = function(triggerElem)
		{
			for(var i=0,elemSet; elemSet=this.elemSets[i]; i++){
				if(elemSet.triggerElem==triggerElem) return elemSet;
			}
		}
		
	/**
	 * closeAllTips
	 * - close all associated tip containers for multi click tips
	 */
		jqTooltip.prototype.closeAllTips = function()
		{
			if(this.behavior!="click") return false;
			$(this.elemSets).each(function(){
				$(this.tipContainerElem).css("display","none");
			});
		}
		
	/**
	 *  misc
	 */
	 	/*
	 	 * border width getter
	 	 * - IE returns border width value as "medium" or something so need to return zero if get NaN
	 	 */
		function getBorderWidthNum($elem,prop){
			var num = $elem.css(prop).replace(/px/,"")*1;
			if(+num==+num) return num;
			else return 0;
		}
		
		/* release current tip info */
		
		function releaseCurrentTipInfo(){
			currentTriggerElem = null;
			currentTipElem = null;
			currentBgIframe = null;
			currentTipNaturalWidth = null;
			currentTipOffsetY = null;
			currentTipOffsetX = null;
			currentTipPosition = null;
			currentTipCSSWidth = null;
			currentTipCSSHeight = null;
			currentWidthShrinked = false;
			currentSingleTip = null;
		}
		
		/* get viewport info */
		
		function getViewportInfo(){
			return {
				scrollLeft: $(window).scrollLeft(),
				scrollTop: $(window).scrollTop(),
				width: $(document.body).width(),
				height: $(window).height()
			};
		}
		
})(jQuery);

