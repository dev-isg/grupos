/**
 * Create a google map picker
 */
(function( $ ) { // JQuery wrapper
	
	// JQuery plugin
    $.fn.juGoogleMap = function(option) 
    {
    	var args = arguments;
    	return this.each(function () 
    	{
    		var $this = $(this);
    	    var obj = $this.data('instance');
    	    var options = typeof option == 'object' && option;
    		if (!obj)
    			$this.data('instance', (obj = new JuGoogleMap(this, options)));
    		if (typeof option == 'string')
    			obj[option].apply(obj, $(args).slice(1));
    	});
    }
    
    // Default options
    $.fn.juGoogleMap.defaults = {
    	height:300,
    	editable:false,
    	allow:{
    		search:true,
    		locator:true,
    		route:true
    	},
    	clickSetsMarker:true,
    	i18n:{
    		overlayTitle:'Click to activate the map',
    		btnLocatorTitle:'View my location',
    		btnCenterMarker:'View the selected address',
    		btnRouteTitle:'Get a route from your location to your destination',
    		search:'Search...',
    		noMarker:'You must select a location by clicking on the map'
    	},
    	city:'',
    	center:{
    		lat:0,
    		lng:0
    	},
    	zoom:2,
    	zoomMarker:15,
    	//marker:{		// Set this in order to start with a marker
    	//  lat:0,
    	//  lng:0,
    	//  address:'',
    	//  addressRef:''
    	//},
    	markerOptions:{
			icon:'/img/ping.png',
    		height:40,
    		tooltip:null
    	},
    	locationOptions:{
    		icon:'/inc100/joinnus/img/current-location.gif',
    		optimized:false, // To let gif be animated
    		height:30
    	},
    	dataBound:null,
    	//dataBound:{
    	//	lat:'#mapLocationLat',
    	//	lng:'#mapLocationLon',
    	//	city:'#cityId',
    	//	address:'#address',
    	//	addressRef:'#addressReference'
    	//},
    	css:{
    		overlay:{
    			backgroundColor:'#fff',
    			opacity:0.4
    		},
    		tooltip:{
    			backgroundColor:'#000',
    			color:'#fff',
    			border:'1px solid #fff',
    			padding:'5px 10px',
    			opacity:0.95,
    			'font-size':'12px',
    			'line-height':'14px',
    			'text-align':'center',
    			'white-space':'nowrap',
    		}
    	}
    }
    
    /**
     * Constructor of the JuGoogleMap instance
     */
    var JuGoogleMap = function (element, options) 
    {
    	var self = this;
		this.domNode = element;
		this.element = $(element);
		this.options = $.extend(true, {}, $.fn.juGoogleMap.defaults, options);
		this.initDom();
		if (!window.google) {
			console.error("Please load google maps first");
			return;
		}
		this.init();
    }
    
    /**
     * Function to initialize all of the dom elements that do not
     * require the google map to be initialized
     */
    JuGoogleMap.prototype.initDom = function() 
    {
    	this.element.css({position:'relative'});
    	this.getOverlay(); 
    	this.getMapElement();
    }
    
    /**
     * Function to initialize all of the dom elements that require
     * the google map
     */
    JuGoogleMap.prototype.init = function() 
    {
    	var self = this;
    	
    	// Init map components
    	this.getMap();
    	this.getGeoCoder();
    	this.getToolbar();
    	this.getBtnCenterMarker();
    	this.getBtnLocator();
    	this.getBtnRoute();
    	this.getSearchBox();
    	this.initDataBound();
    	this._populateFromDataBound();
    	
    	// Disable not used buttons in toolbar
    	if (!this.options.allow.search)
    	{
    		this.searchBox.hide();
    		this.searchBox.appendTo(this.element);
    	}
    	if (!this.options.allow.locator)
    		this.btnLocator.hide();
    	if (!this.options.allow.route)
    		this.btnRoute.hide();
    	
    	// Set the marker if specified
    	if (this.options.marker)
    	{
    		this.setMarker(
    			this.options.marker.lat, 
				this.options.marker.lng, 
				this.options.marker.address, 
				this.options.marker.addressRef
    		);
    	}
    	else if (this.options.editable && !this.marker) 
    	{
    		// If in edit mode and no marker set then set marker to default location
    		// and until then or a marker is picked show an error message
    		this.btnCenterMarker.removeClass('btn-inverse').addClass('btn-danger')
    			.attr('title', this.options.i18n.noMarker)
    			.tooltip('destroy').tooltip({placement:'bottom', container:'body'});
    		this.refreshLocation(false, function(){
    			if (!self.marker)
    			{
    				self.setMarker(self.location.position.jb, 
    					self.location.position.kb, null, null);
    				self.map.setCenter(self.location.position);
					if (self.map.getZoom() < self.options.zoomMarker)
						self.map.setZoom(self.options.zoomMarker);
    			}
    		});
    	}
    	else if (this.options.editable && this.marker) {
    		// Disable click to set marker if the marker was set databound
    		//this.options.clickSetsMarker = false;
    	}
    	this.setEditable(this.options.editable);
    }
    
    /**
     * Get the center used to create the map
     */
    JuGoogleMap.prototype.getDefaultCenter = function() 
    {
    	if (this.options.marker)
    		return new google.maps.LatLng(this.options.marker.lat, this.options.marker.lng);
    	else 
    		return new google.maps.LatLng(this.options.center.lat, this.options.center.lng);
    }
    
    /**
     * Get the center used to create the map
     */
    JuGoogleMap.prototype.getDefaultZoom = function() 
    {
    	if (this.options.marker)
    		return this.options.zoomMarker;
    	else 
    		return this.options.zoom;
    }
    
    /**
     * Get the center used to create the map
     */
    JuGoogleMap.prototype.getGeoCoder = function() 
    {
    	if (!this.geoCoder)
    		this.geoCoder = new google.maps.Geocoder();
    	return this.geoCoder;
    }
    
    /**
     * Get the center used to create the map
     */
    JuGoogleMap.prototype.setEditable = function(value) 
    {
		this.options.editable = value;
    	if (this.options.editable)
    	{
    		this.getSearchBox().show().appendTo(this.getToolbar());
    		if (this.marker)
    			this.marker.setDraggable(true);
    	}
    	else
    	{
    		this.getSearchBox().hide().appendTo(this.element);
    		if (this.marker)
    			this.marker.setDraggable(false);
    	}
    }
    
    /**
     * Get the overlay used to prevent unwanted zooming on the map
     * until the user clicks the overlay.
     * 
     * It automatically binds methods to the overlay click and
     * the element leave in order to show and hide the overlay
     */
    JuGoogleMap.prototype.getOverlay = function() 
    {
    	var self = this;
    	if (!this.overlay)
    	{
	    	this.overlay = $("<div class='ju-google-map-overlay'/>")
				.css($.extend(true, {}, this.options.css.overlay, {
					position:'absolute',
					left:0, top:0, right:0, bottom:0,
					zIndex:30,
					cursor:'pointer'
				}))
				.attr('title', this.options.i18n.overlayTitle)
				.click(function(){
					$(this).fadeOut();
					self.element.focus();
				})
				.appendTo(this.element);
	    	
	    	// Bind to element methods
	    	this.element.blur(function(){ self.overlay.fadeIn(); });
			this.element.mouseleave(function(){ self.overlay.fadeIn(); });
    	}
    	return this.overlay;
    }
    
    /**
     * Get the div element where the google map will be rendered
     */
    JuGoogleMap.prototype.getMapElement = function()
    {
    	var self = this;
    	if (!this.mapElement)
    	{
    		// Override default height if the element has a height
        	if (this.element.height())
    			this.options.height = this.element.height();
        	
    		this.mapElement = $("<div/>")
				.css({
					position:'relative',
					height:this.options.height
				})
				.appendTo(this.element);
    	}
    	return this.mapElement;
    }
    
    /**
     * Get the toolbar where all of the buttons will be added.
     */
    JuGoogleMap.prototype.getToolbar = function()
    {
    	var self = this;
    	if (!this.toolbar)
    	{
    		this.toolbar = $("<div class='input-append input-prepend ju-google-map-toolbar'/>")
				.css({
					position:'absolute',
					left:5, top:5, right:5,
					zIndex:40
				})
				.appendTo(this.element);
    	}
    	return this.toolbar;
    }
    
    /**
     * Return the locator button
     */
    JuGoogleMap.prototype.getBtnLocator = function()
    {
    	var self = this;
    	if (!this.btnLocator)
    	{
    		this.btnLocator = $("<button type='button' class='btn btn-inverse'/>")
				.append($("<i class='icon-eye-open icon-white'/>"))
				.click(function(){
					$(this).tooltip('hide');
					self.refreshLocation(true);
				})
				.attr('title', this.options.i18n.btnLocatorTitle)
				.appendTo(this.getToolbar())
				.tooltip({
					placement:'bottom',
					container:'body'
				});
    	}
    	return this.btnLocator;
    }
    
    /**
     * Return the center marker button
     */
    JuGoogleMap.prototype.getBtnCenterMarker = function()
    {
    	var self = this;
    	if (!this.btnCenterMarker)
    	{
    		this.btnCenterMarker = $("<button type='button' class='btn btn-inverse'/>")
				.append($("<i class='icon-map-marker icon-white'/>"))
				.click(function(){
					$(this).tooltip('hide');
					self.centerMarker();
				})
				.attr('title', this.options.i18n.btnCenterMarker)
				.appendTo(this.getToolbar())
				.tooltip({
					placement:'bottom',
					container:'body'
				});
    	}
    	return this.btnCenterMarker;
    }
    
    /**
     * Return the route button
     */
    JuGoogleMap.prototype.getBtnRoute = function()
    {
    	var self = this;
    	if (!this.btnRoute)
    	{
    		this.btnRoute = $("<button type='button' class='btn btn-inverse'/>")
				.append($("<i class='icon-road icon-white'/>"))
				.click(function(){
					$(this).tooltip('hide');
					self.calculateRoute();
				})
				.attr('title', this.options.i18n.btnRouteTitle)
				.appendTo(this.getToolbar())
				.tooltip({
					placement:'bottom',
					container:'body'
				});
    	}
    	return this.btnRoute;
    }
    
    /**
     * Return the search box
     */
    JuGoogleMap.prototype.getSearchBox = function()
    {
    	var self = this;
    	if (!this.searchBox)
    	{
    		this.searchBoxResults = [];
    		this.searchBox = $("<input type='text' autocomplete='off' />")
				.attr('placeholder', this.options.i18n.search)
				.css({
					position:'absolute',
					display:'block',
					width:'auto',
					left:110, right:0,top:0
				})
				.appendTo(this.getToolbar())
				.typeahead({
					items:20,
					minLength:3,
					matcher:function(e){ return true; },
					updater:function(item)
					{
						for (var i=0, place; place = self.searchBoxResults[i]; i++)
						{
							if (place.formatted_address == item)
							{
								self._onPlaceFound(place);
								return item;
							}
						}
						return "";
					},
					source:function(query, process)
					{
						var searchString = query + (($.trim(self.options.city) != "") ?  (", " + self.options.city) : "");
						var url = "http://maps.googleapis.com/maps/api/geocode/json?address=" + encodeURIComponent(searchString) + "&sensor=true";
						if (self.searchRequest)
						{
							self.searchRequest.abort();
							self.searchRequest = null;
						}

						self.searchRequest = $.ajax({
							url:url,
							dataType:'json',
							success:function(response){
								if (response.status == "OK")
								{
									var typeahead = [];
									self.searchBoxResults = [];
									for (var i=0, place; place = response.results[i]; i++)
									{
										self.searchBoxResults.push(place);
										typeahead.push(place.formatted_address); 
									}
									process(typeahead);
								}
							},
							complete:function(){
								self.searchRequest = null;
							}
						});
					}
				});
    	}
    	return this.searchBox;
    }
    
    /**
     * Return the google map instance
     */
    JuGoogleMap.prototype.getMap = function()
    {
    	var self = this;
    	if (!this.map && window.google)
    	{
    		google.maps.visualRefresh = true;
    		var mapOptions = {
				center:this.getDefaultCenter(),
	    		zoom:this.getDefaultZoom(),
	    		mapTypeId:google.maps.MapTypeId.ROADMAP,
	    		mapTypeControl: false
			};
			this.map = new google.maps.Map(this.getMapElement()[0], mapOptions);
			google.maps.event.addListener(this.map, 'click', function(e){
				self._onMapClicked(e);
			});
    	}
    	return this.map;
    }
    
    /**
     * Set the current marker by specifying it's options
     */
    JuGoogleMap.prototype.setMarker = function(lat, lng, address, addressRef)
    {
    	var self = this;
    	if (!this.marker)
		{
			var options = $.extend(true, {}, {}, this.options.markerOptions);
			options.position = new google.maps.LatLng(lat, lng);
			this.marker = new google.maps.Marker(options);
			this.marker.setMap(this.map);
			this.markerTooltip = this.getMarkerTooltip();
			google.maps.event.addListener(this.marker, 'dragend', function(){
				self.setMarker(self.marker.position.jb, self.marker.position.kb, null, self.marker.addressRef);
			});
			
			if (this.options.editable)
				this.marker.setDraggable(true);
			
			this._onMarkerAssigned();
		}
		else
		{
			if (this.marker.position.jb != lat || this.marker.position.kb != lng)
				this.marker.setPosition(new google.maps.LatLng(lat, lng));
		}
    	
    	this.marker.address = address;
    	this.marker.addressRef = addressRef;
    	
		if (!this.marker.address)
			this.updateMarkerAddress();
		
		this._onMarkerChanged();
    }
    
    /**
     * Return the tooltip to show over the marker
     */
    JuGoogleMap.prototype.getMarkerTooltip = function()
    {
    	var self = this;
    	if (!this.markerTooltip)
		{
    		var overlay = function(marker, height)
    	    {
    	    	this.marker = marker;
    	    	this.height = height;
    	    	this.node = null;
    	    	this.setMap(marker.getMap());
    	    }
    	    
    	    overlay.prototype = new google.maps.OverlayView();
    	    overlay.prototype.onAdd = function() {
    	    	var node = $('<div/>')
        			.addClass('ju-google-map-tooltip')
        			.css($.extend(true, {}, self.options.css.tooltip, {
        				position:'absolute',
					}));
    	    	if (!self.options.marker)
    	    		node.addClass('hide');
    	    	this.node = node[0];
    	    	var panes = this.getPanes();
    	    	panes.overlayLayer.appendChild(this.node);
    	    }
    	    overlay.prototype.draw = function() {
    	    	var overlayProjection = this.getProjection();
    	    	if (overlayProjection)
    	    	{
    		    	var p = overlayProjection.fromLatLngToDivPixel(this.marker.position);
    		    	var node = $(this.node);
    		    	var html = this.marker.address;
    		    	if ($.trim(this.marker.addressRef) != "")
    		    		html += "<br/><small>(" + this.marker.addressRef + ")</small>";
    		    	node.html(html);
    		    	var w = node.outerWidth();
    		    	var h = node.outerHeight();
    		    	node.css({
    		    		left: (p.x - w/2) + "px",
    		    		top: (p.y - h - this.height) + "px",
    		    	});
    	    	}
    	    }
    	    overlay.prototype.onRemove = function() {
    	    	$(this.node).remove();
    	    	this.node = null;
    	    }
    	    this.markerTooltip = new overlay(this.marker, this.options.markerOptions.height);
    	    google.maps.event.addListener(this.marker, 'click', function() 
    		{
    	    	$(self.markerTooltip.node).fadeToggle();
    		});
		}
    	return this.markerTooltip;
    }
    
    /**
     * Center the marker if one exists
     */
    JuGoogleMap.prototype.centerMarker = function()
    {
    	if (this.marker)
    		this.map.setCenter(this.marker.position);
    }
    
    /**
     * Refresh the user current location
     */
    JuGoogleMap.prototype.refreshLocation = function(center, onDone)
    {
    	if (!navigator.geolocation)
			return;
    	
    	var self = this;
		this.getBtnLocator().attr('disabled', 'disabled');
		navigator.geolocation.getCurrentPosition(
			function(pos) 
			{
				self.getBtnLocator().removeAttr('disabled');
				var point = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
				
				if (!self.location)
				{
					var options = $.extend(true, {}, {}, self.options.locationOptions);
					options.position = point;
					self.location = new google.maps.Marker(options);
					self.location.setMap(self.map);
				}
				else
					self.location.setPosition(point);
				
				if (center)
				{
					self.map.setCenter(point);
					if (self.map.getZoom() < self.options.zoomMarker)
						self.map.setZoom(self.options.zoomMarker);
				}
				if (onDone)
					onDone.call(self);
			}, 
			function()
			{
				self.getBtnLocator().removeAttr('disabled');
				self.getBtnRoute().removeAttr('disabled');
				if (self.location)
		    	{
		    		self.location.setMap(null);
		    		self.location = null;
		    	}
			}
		);
    }
    
    /**
     * Calculates the route from the user location to the marker. If the
     * location is already set then just calculate the route. Otherwise call
     * refreshLocation with onDone this calculateRoute
     */
    JuGoogleMap.prototype.calculateRoute = function()
    {
    	if (!this.marker) return;
		this.getBtnRoute().attr('disabled', 'disabled');
    	if (!this.location)
    		this.refreshLocation(false, this.calculateRoute);
    	else
    	{
    		if (this.directionsService == null)
    			this.directionsService = new google.maps.DirectionsService();
    		if (this.directionsDisplay == null)
    		{
    			this.directionsDisplay = new google.maps.DirectionsRenderer();
    			this.directionsDisplay.setMap(this.map);
    		}
    		var request = {
    			origin: this.location.position,
    			destination: this.marker.position,
    			travelMode: google.maps.DirectionsTravelMode.DRIVING
    		};
    		var directionsDisplay = this.directionsDisplay;
    		var self = this;
    		this.directionsService.route(request, function(response, status) {
    			if (status == google.maps.DirectionsStatus.OK) {
    				self.directionsDisplay.setDirections(response);
    			}
    			self.getBtnRoute().removeAttr('disabled');
    		});
    	}
    }
    
    /**
     * Attempts to find the address of the marker and on success 
     * sets the address correctly
     */
    JuGoogleMap.prototype.updateMarkerAddress = function()
    {
    	if (!this.marker)
    		return;
    	
    	var self = this;
		this.getGeoCoder().geocode({'latLng': this.marker.position}, function(results, status) 
		{
			if (status == google.maps.GeocoderStatus.OK && results[0]) 
			{
				self.setMarker(self.marker.position.jb, self.marker.position.kb, 
						results[0].formatted_address, self.marker.addressRef);
		    }
		});
    }
    
    /**
     * Init databound to add bidirectional refresh
     */
    JuGoogleMap.prototype.initDataBound = function()
    {
    	if (!this.options.dataBound) return;
    	var self = this;
    	
    	if (this.options.dataBound.lat)
    		$(this.options.dataBound.lat).change(function(){self._populateFromDataBound()});
    	if (this.options.dataBound.lng)
    		$(this.options.dataBound.lng).change(function(){self._populateFromDataBound()});
    	if (this.options.dataBound.address)
    		$(this.options.dataBound.address).change(function(){self._populateFromDataBound()});
    	if (this.options.dataBound.city)
    		$(this.options.dataBound.city).change(function(){self._populateFromDataBound()});
    	if (this.options.dataBound.addressRef)
    		$(this.options.dataBound.addressRef).change(function(){self._populateFromDataBound()});
    }
    
    /**
     * If DataBound is defined then from input => map
     */
    JuGoogleMap.prototype._populateFromDataBound = function()
    {
    	if (!this.options.dataBound) return;
    	var lat = (this.marker) ? this.marker.position.jb : null;        
    	var lng = (this.marker) ? this.marker.position.kb : null;
    	var address = (this.marker) ? this.marker.address : null;
    	var addressRef = (this.marker) ? this.marker.addressRef : null;
    	if (this.options.dataBound.lat)
    		lat = parseFloat($(this.options.dataBound.lat).val());
    	if (this.options.dataBound.lng)
    		lng = parseFloat($(this.options.dataBound.lng).val());
    	if (this.options.dataBound.address)
    		address = $(this.options.dataBound.address).val();
    	if (this.options.dataBound.addressRef)
    		addressRef = $(this.options.dataBound.addressRef).val();
    	if (this.options.dataBound.city)
    	{
    		var cityNode = $(this.options.dataBound.city)[0];
    		if (cityNode)
    		{
    			if (cityNode.nodeName.toUpperCase() == "SELECT")
    				this.options.city = $(cityNode).find('option:selected').first().text();
    			else
    				this.options.city = $(cityNode).val();
    		}
    	}

    	if (isNaN(lat) || isNaN(lng))
    	{
    		lat = lng = null;
    	}
    	
    	if (lat != null && lng != null)
    	{
    		var center = !this.marker;
    		this.setMarker(lat, lng, address, addressRef);
    		if (center)
    		{
    			this.getMap().setCenter(this.marker.position);
    	    	this.getMap().setZoom(this.options.zoomMarker);
    		}
    	}
    }
    
    /**
     * If DataBound is defined then from map => input
     */
    JuGoogleMap.prototype._populateToDataBound = function()
    {
    	if (!this.options.dataBound) return;	
    	if (this.options.dataBound.lat)
    		$(this.options.dataBound.lat).val(this.marker?this.marker.position.jb:"");
    	if (this.options.dataBound.lng)
    		$(this.options.dataBound.lng).val(this.marker?this.marker.position.kb:"");
    	if (this.options.dataBound.address)
    		$(this.options.dataBound.address).val(this.marker?this.marker.address:"");
    	if (this.options.dataBound.addressRef)
    		$(this.options.dataBound.addressRef).val(this.marker?this.marker.addressRef:"");
    }
    
    /**
     * When a place is found in a search call this method by passing the 
     * place found. It should update the marker and center the map on this new place
     */
    JuGoogleMap.prototype._onPlaceFound = function(place)
    {
    	this.setMarker(place.geometry.location.lat, place.geometry.location.lng, 
				place.formatted_address, null);
    	this.getMap().setCenter(this.marker.position);
    	this.getMap().setZoom(this.options.zoomMarker);
    }
    
    /**
     * When the user clicks on the map. It receives the clicked position. 
     * It should change the marker or set a new one
     */
    JuGoogleMap.prototype._onMapClicked = function(pos)
    {
    	if (this.options.editable && this.options.clickSetsMarker)
    		this.setMarker(pos.latLng.jb, pos.latLng.kb, null, (this.marker) ? this.marker.addressRef : null);
    }
    
    /**
     * Called when the marker has been assigned. It should disable 
     * the error message for the marker REQUIRED
     */
    JuGoogleMap.prototype._onMarkerAssigned = function()
    {
    	this.btnCenterMarker.addClass('btn-inverse').removeClass('btn-danger')
			.attr('title', this.options.i18n.btnCenterMarker)
			.tooltip('destroy').tooltip({placement:'bottom', container:'body'});
    }
    
    /**
     * Called when the marker has changed it should update the 
     * tooltip and the form fields if necessary
     */
    JuGoogleMap.prototype._onMarkerChanged = function()
    {
    	this.getMarkerTooltip().draw();
    	this._populateToDataBound();
    }

// End JQuery wrapper
}( jQuery ));