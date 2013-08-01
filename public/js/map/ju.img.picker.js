/**
 * Make a an image work as a image upload
 * 
 * The image must be wrapped inside a div with a specific width and
 * height
 * 
 * $(document).ready(function(){
 *     $("#theImage").juImgPicker({
 *         maxFileSize:'<?php echo ini_get('upload_max_filesize'); ?>'
 *     });
 *});
 */
(function( $ ) { // JQuery wrapper
	
	// JQuery plugin
    $.fn.juImgPicker = function(option) 
    {
    	var args = arguments;
    	return this.each(function () 
    	{
    		var $this = $(this);
    	    var obj = $this.data('instance');
    	    var options = typeof option == 'object' && option;
    		if (!obj)
    			$this.data('instance', (obj = new JuImgPicker(this, options)));
    		if (typeof option == 'string')
    			obj[option].apply(obj, $(args).slice(1));
    	});
    }
    
    // Default options
    $.fn.juImgPicker.defaults = {
    	uploadName:'uploaded-image',
    	maxFileSize:'2m',
    	maxMemSize:36700160,// TODO: Make this load from php setting
    	acceptTypes:["image/gif","image/jpg","image/png","image/jpeg"],
    	i18n: {
    		tooBig:'The image is too big',
    		tooBigPixels:'Your image is %w wide and %h tall. Please reduce the dimentions of the image',
    		invalidType:'Please choose a valid image',
    		upload:'Upload',
    		plainMessage:'You are about to upload <br/><strong>%s</strong>',
    		noPreviewSupport:"You're browser does not support HTML5 preview images. Please consider using a modern browser."
    	}
    }
    
    /**
     * Constructor of the JuImgUpload instance
     */
    var JuImgPicker = function (image, options) 
    {
    	var self = this;
		this.domNode = image;
		this.image = $(image);
		this.options = $.extend(true, {}, $.fn.juImgPicker.defaults, options);
		this.init();
    }
    
    /**
     * Init function creates all the required dom elements
     */
    JuImgPicker.prototype.init = function() 
    {
    	// TODO: Make this ratio be dynamic instead of fixed
    	var self = this;
    	var ratio = 300/200;
    	var h = parseInt(this.image.parent().width() * 200 / 300);
    	this.image.parent().css({overflow:'hidden', height:h});
    	$(window).resize(function(){
    		var ratio = 300/200;
        	var h = parseInt(self.image.parent().width() * 200 / 300);
        	self.image.parent().css({height:h});
    	});
    	var position = this.image.parent().css('position');
    	if (position != 'fixed' && position != 'absolute' && position != 'relative')
    		this.image.parent().css({position:'relative'});
    	this.getPlainPreviewer().hide();
    	this.getImagePreviewer().hide();
    	this.getUploadButton();
    	this.getToolbar().hide();
    	this.getMaxFileSize();
    	this.getInputCrop();
    }
    
    /**
     * Get the max file size in bytes
     */
    JuImgPicker.prototype.getMaxFileSize = function() 
    {
    	if (!this.maxFileSize)
    	{
    		this.maxFileSize = this.options.maxFileSize.toUpperCase();
    		if (this.maxFileSize.indexOf("M") > 0)
    			this.maxFileSize = parseInt(this.maxFileSize.split("M")[0]) * 1024 * 1024;
    		else if (this.maxFileSize.indexOf("K") > 0)
    			this.maxFileSize = parseInt(this.maxFileSize.split("K")[0]) * 1024;
    		else
    			this.maxFileSize = parseInt(this.maxFileSize);
    		if (this.maxFileSize <= 0 || isNaN(this.maxFileSize))
    			this.maxFileSize = 2 * 1024 * 1024;
    	}
    	return this.maxFileSize;
    }
    
    /**
     * Return the upload button
     */
    JuImgPicker.prototype.getUploadButton = function() 
    {
    	if (!this.uploadButton)
    	{
    		var self = this;
    		this.uploadButton = $("<div class='btn btn-inverse btn-small' />")
    			.css({
    				position:'absolute',
	    			right: '5px',
	    			bottom:'5px',
	    			margin:0,
	    			overflow:'hidden',
	    			zIndex:90
    			})
    			.append($("<i class='icon-upload icon-white'/>"))
    			.append("&nbsp;" + this.options.i18n.upload)
    			.appendTo(this.image.parent());
    		this.getInputFile();
    	}
    	return this.uploadButton;
    }
    
    /**
     * Return the input type="file" element
     */
    JuImgPicker.prototype.getInputFile = function() 
    {
    	if (!this.inputFile)
    	{
    		var self = this;
    		this.inputFile = $("<input type='file' name='" + this.options.uploadName + "' />")
    			.attr('accept', this.options.acceptTypes.toString())
    			.css({
    				position:'absolute',
	    			top: 0,
	    			right: 0,
	    			left:'auto',
	    			top:0,
	    			margin:0,
	    			height:'200px',
	    			lineHeight:'200px',
	    			fontSize:'200px',
	    			opacity:0,
	    			cursor:'pointer'
    			})
    			.change(function(e){
    				self._onFileSelected(e);
    			})
    			.appendTo(this.getUploadButton());
    	}
    	return this.inputFile;
    }
    
    /**
     * Return the input type="hidden" element that will have the 
     * crop information
     */
    JuImgPicker.prototype.getInputCrop = function() 
    {
    	if (!this.inputCrop)
    	{
    		var self = this;
    		this.inputCrop = $("<input type='hidden' name='" + this.options.uploadName + "_cropinfo' />")
    			.css({
    				position:'absolute',
	    			top: 'auto',
	    			right: 0,
	    			left:0,
	    			bottom:30,
	    			width:'100%',
	    			margin:0,
	    			opacity:0.6,
	    			zIndex:90
    			})
    			.appendTo(this.image.parent());
    	}
    	return this.inputCrop;
    }
    
    /**
     * Create the buttons to resize and crop the image
     */
    JuImgPicker.prototype.getToolbar = function() 
    {
    	if (!this.toolbar)
    	{
	    	var self = this;
	    	var w = 38;
	    	var h = 28;
	    	var p = 5;
	    	var opacityOn = 0.9;
	    	var opacityOff = 0.3;
	    	this.toolbar = $("<div/>")
	    		.css({
	    			position:'absolute',
	    			left:0,top:0,right:0,bottom:0,
	    			zIndex:70,
	    			cursor:'move'
	    		})
	    		.appendTo(this.image.parent())
	    		.bind('DOMMouseScroll', function(e){
				     if(e.originalEvent.detail > 0) {
				    	 self.zoomOut();
				     }else {
				    	 self.zoomIn();
				     }
				     return false;
				 })
				 //IE, Opera, Safari
				 .bind('mousewheel', function(e){
				     if(e.originalEvent.wheelDelta < 0) {
				         self.zoomOut();
				     }else {
				    	 self.zoomIn();
				     }
				     return false;
				 })
				 .bind('mousedown', function(e){ self.startDrag(e); })
				 .bind('mousemove', function(e){ self.doDrag(e); })
				 .bind('mouseup', function(){ self.stopDrag(); })
				 .bind('mouseleave', function(){ self.stopDrag(); });
	    	
	    	this.btnZoomOut = $("<button type='button' class='btn btn-small'/>")
				.append($("<i class='icon-zoom-out'/>"))
				.css({
					position:'absolute',
	    			top:0,left:0,
	    			opacity:opacityOff
				})
				.click(function(){
					self.zoomOut();
				})
				.hover(
					function(){$(this).css('opacity',opacityOn);}, 
					function(){$(this).css('opacity',opacityOff);}
				)
				.appendTo(this.toolbar);
	    	
	    	this.btnMoveUp = $("<button type='button' class='btn btn-small'/>")
				.append($("<i class='icon-arrow-up'/>"))
				.css({
					position:'absolute',
	    			top:0,left:w,
	    			opacity:opacityOff
				})
				.click(function(){
					self.moveDown();
				})
				.hover(
					function(){$(this).css('opacity',opacityOn);}, 
					function(){$(this).css('opacity',opacityOff);}
				)
				.appendTo(this.toolbar);
	    	
	    	this.btnZoomIn = $("<button type='button' class='btn btn-small'/>")
	    		.append($("<i class='icon-zoom-in'/>"))
	    		.css({
	    			position:'absolute',
	    			top:0,left:2*w,
	    			opacity:opacityOff
	    		})
	    		.click(function(){
	    			self.zoomIn();
	    		})
				.hover(
					function(){$(this).css('opacity',opacityOn);}, 
					function(){$(this).css('opacity',opacityOff);}
				)
	    		.appendTo(this.toolbar);
	    	
	    	this.btnMoveLeft = $("<button type='button' class='btn btn-small'/>")
				.append($("<i class='icon-arrow-left'/>"))
				.css({
					position:'absolute',
	    			top:h,left:0,
	    			opacity:opacityOff
				})
				.click(function(){
					self.moveRight();
				})
				.hover(
					function(){$(this).css('opacity',opacityOn);}, 
					function(){$(this).css('opacity',opacityOff);}
				)
				.appendTo(this.toolbar);
	    	
	    	this.btnMoveDown = $("<button type='button' class='btn btn-small'/>")
				.append($("<i class='icon-arrow-down'/>"))
				.css({
					position:'absolute',
	    			top:h,left:w,
	    			opacity:opacityOff
				})
				.click(function(){
					self.moveUp();
				})
				.hover(
					function(){$(this).css('opacity',opacityOn);}, 
					function(){$(this).css('opacity',opacityOff);}
				)
				.appendTo(this.toolbar);
	    	
	    	this.btnMoveRight = $("<button type='button' class='btn btn-small'/>")
				.append($("<i class='icon-arrow-right'/>"))
				.css({
					position:'absolute',
	    			top:h,left:2*w,
	    			opacity:opacityOff
				})
				.click(function(){
					self.moveLeft();
				})
				.hover(
					function(){$(this).css('opacity',opacityOn);}, 
					function(){$(this).css('opacity',opacityOff);}
				)
				.appendTo(this.toolbar);
    	}
    	return this.toolbar;
    }
    
    /**
     * Determine if a specific type is allowed
     */
    JuImgPicker.prototype.isAcceptedType = function ( type ) 
	{
		type = type.toLowerCase();
		for (var i in this.options.acceptTypes)
		{
			if (this.options.acceptTypes[i].toLowerCase() == type)
				return true;
		}
		return false;
	}
    
    /**
     * Process the selected file
     */
    JuImgPicker.prototype._onFileSelected = function(e)
    {
    	// Get the image from HTML5 or fallback to default 
		var file = null;
		var domInput = this.getInputFile()[0];
		if (domInput.files)
		{
			if (domInput.files.length > 0)
				file = domInput.files[0];
		}
		else
		{
			var fullFileName = this.getInputFile().val();
			var indexOfSlash = fullFileName.lastIndexOf("/");
			if (indexOfSlash < 0)
				indexOfSlash = fullFileName.lastIndexOf("\\");
			if (indexOfSlash > 0)
			{
				var fileName = fullFileName.substring(indexOfSlash+1);
				var extension = fileName.substring(fileName.lastIndexOf(".")+1).toLowerCase();
				var mimeType = "image/" + extension;
				
				file = {
					size:0,
					name:fileName,
					type:mimeType
				};
			}
		}
		
		// Check file is a valid image
		if (file == null)
		{
			this.getImagePreviewer().hide();
	    	this.getPlainPreviewer().hide();
	    	this.getToolbar().hide();
	    	this.getInputCrop().val("");
	    	this.image.show();
	    	this.getInputFile().remove();
	    	this.inputFile = null;
	    	this.getInputFile();

			this.getImagePreviewer().remove();
			this.imagePreviewer = null;
			this.getImagePreviewer();
			return;
		}
		if (!this.isAcceptedType(file.type))
			return this.showError(this.options.i18n.invalidType);
		if (file.size > this.maxFileSize)
			return this.showError(this.options.i18n.tooBig);
		
		// Process with default parser
		if (typeof FileReader == "undefined")
		{
			this.getImagePreviewer().hide();
			this.getPlainPreviewer().html(
				this.options.i18n.plainMessage.replace("%s", file.name) +
				"<br/><br/>" +
				this.options.i18n.noPreviewSupport
			).show();
			return;
		}
		
		// Process the image previewer
		var img = this.getImagePreviewer();
		var reader = new FileReader();
		reader.onload = (function (theFile) {
			return function (e) {
				img.attr('src', e.target.result);
			}
		}(file));
		reader.readAsDataURL(file);
    }
    
    /**
     * Process the selected file
     */
    JuImgPicker.prototype.showError = function(msg)
    {
    	this.image.css({zIndex:49});
    	this.getToolbar().hide();
    	this.getImagePreviewer().hide();
    	this.getPlainPreviewer().html(msg).show();
    }
    
    /**
     * Return the image where the preview will be shown
     */
    JuImgPicker.prototype.getImagePreviewer = function()
    {
    	if (!this.imagePreviewer)
    	{
    		var self = this;
    		this.imagePreviewer = $("<img/>")
				.css({
					position:'absolute',
					left:0,top:0,
					'max-width':'none',
					'max-height':'none',
					'min-width':0,
					'min-height':0,
					zIndex:50
				})
				.appendTo(this.image.parent())
				.load(function(){
					$(this).css({width:'auto',height:'auto'});
					self.previewOriginalWidth = this.width;
					self.previewOriginalHeight = this.height;
					if (self.previewOriginalWidth <= 0 || self.previewOriginalHeight <= 0)
					{
						self.previewOriginalWidth = self.image.parent().width();
						self.previewOriginalHeight = self.image.parent().height();
					}
					if (self.previewOriginalWidth * self.previewOriginalHeight * 4 >= self.options.maxMemSize)
					{
						self.showError(self.options.i18n.tooBigPixels.replace("%w", self.previewOriginalWidth).replace("%h", self.previewOriginalHeight));
					}
					else
						self.showImagePreviewer();
				});
    	}
    	return this.imagePreviewer;
    }
    
    /**
     * Return a simple div element to preview information
     * when no HTML5 supported
     */
    JuImgPicker.prototype.getPlainPreviewer = function()
    {
    	if (!this.plainPreviewer)
    	{
    		this.plainPreviewer = $("<div/>")
    			.css({
    				position:'absolute',
    				left:0,top:0,right:0,bottom:0,
    				backgroundColor:"#fff",
    				opacity:0.95,
    				zIndex:50,
    				textAlign:'center',
    				padding:'8px'
    			})
    			.appendTo(this.image.parent());
    	}
    	return this.plainPreviewer;
    }
    
    /**
     * Return the image where the preview will be shown
     */
    JuImgPicker.prototype.showImagePreviewer = function()
    {
    	// Reset the view
    	this.getPlainPreviewer().hide();
    	this.getImagePreviewer().show();
    	this.image.hide();
    	this.getToolbar().show();
    	
    	// Make the image fit the container
    	var ow = this.previewOriginalWidth;
    	var oh = this.previewOriginalHeight;
    	var cw = this.image.parent().width();
    	var ch = this.image.parent().height();
    	var nw = cw;
		var nh = Math.ceil((nw * oh) / ow);
		if (nh < ch)
		{
			nh = ch;
			nw = Math.ceil((nh * ow) / oh);
		}
    	var image = this.getImagePreviewer();
    	image.css({
    		width:nw,
    		height:nh,
    		left:0,
    		top:0
    	});
    	this.updateCropInfo();
    }
    
    /**
     * Zoom in the preview image
     */
    JuImgPicker.prototype.zoomIn = function()
    {
    	var img = this.getImagePreviewer();
    	var w = img.width();
    	var h = img.height();
    	var newW = w + 20;
    	if (newW >= 2000)
    		newW = 2000;
    	var newH = Math.ceil(newW * h / w);
    	img.css({
    		width:newW,
    		height:newH
    	});
    	this.updateCropInfo();
    }
    
    /**
     * Zoom out the preview image
     */
    JuImgPicker.prototype.zoomOut = function()
    {
    	var img = this.getImagePreviewer();
    	var w = img.width();
    	var h = img.height();
    	var l = parseInt(img.css('left'));
    	var t = parseInt(img.css('top'));
    	var newW = w - 20;
    	var newH = Math.ceil(newW * h / w);
    	if (newW <= this.image.parent().width() || newH <= this.image.parent().height())
    	{
    		newW = this.image.parent().width();
    		newH = Math.ceil(newW * h / w);
    		if (newH < this.image.parent().height())
    		{
    			newH = this.image.parent().height();
    			newW = Math.ceil(newH * w / h);
    		}
    	}
    	if (l > 0) 
    		l = 0;
    	if (l + newW < this.image.parent().width())
    		l = this.image.parent().width() - newW;

    	if (t > 0) 
    		t = 0;
    	if (t + newH < this.image.parent().height())
    		t = this.image.parent().height() - newH;
    	
    	img.css({
    		width:newW,
    		height:newH,
    		left:l,
    		top:t
    	});
    	this.updateCropInfo();
    }
    
    /**
     * Move the image to the left
     */
    JuImgPicker.prototype.moveLeft = function(delta)
    {
    	delta = delta || 10;
    	var img = this.getImagePreviewer();
    	var left = parseInt(img.css('left'));
    	var width = img.width();
    	var minLeft = 0 - (width - img.parent().width());
    	var newLeft = left - delta;
    	if (newLeft <= minLeft)
    		newLeft = minLeft;
    	img.css('left', newLeft);
    	this.updateCropInfo();
    }
    
    /**
     * Move the image to the right
     */
    JuImgPicker.prototype.moveRight = function(delta)
    {
    	delta = delta || 10;
    	var img = this.getImagePreviewer();
    	var left = parseInt(img.css('left'));
    	var width = img.width();
    	var maxLeft = 0;
    	var newLeft = left + delta;
    	if (newLeft >= maxLeft)
    		newLeft = maxLeft;
    	img.css('left', newLeft);
    	this.updateCropInfo();
    }
    
    /**
     * Move the preview image up
     */
    JuImgPicker.prototype.moveUp = function(delta)
    {
    	delta = delta || 10;
    	var img = this.getImagePreviewer();
    	var top = parseInt(img.css('top'));
    	var height = img.height();
    	var minTop = 0 - (height - img.parent().height());
    	var newTop = top - delta;
    	if (newTop <= minTop)
    		newTop = minTop;
    	img.css('top', newTop);
    	this.updateCropInfo();
    }
    
    /**
     * Move the preview image down
     */
    JuImgPicker.prototype.moveDown = function(delta)
    {
    	delta = delta || 10;
    	var img = this.getImagePreviewer();
    	var top = parseInt(img.css('top'));
    	var height = img.height();
    	var maxTop = 0;
    	var newTop = top + delta;
    	if (newTop >= maxTop)
    		newTop = maxTop;
    	img.css('top', newTop);
    	this.updateCropInfo();
    }
    
    /**
     * Begin starting drag
     */
    JuImgPicker.prototype.startDrag = function(e)
	{
		this.dragging = true;
		this.dragStart = {
			x:e.offsetX,
			y:e.offsetY,
			imgX:parseInt(this.getImagePreviewer().css('left')),
			imgY:parseInt(this.getImagePreviewer().css('top')),
		};
	}
	
    /**
     * Update the image while dragging
     */
    JuImgPicker.prototype.doDrag = function(e)
	{
		if (!this.dragging) return;
    	var img = this.getImagePreviewer();
		var diffX = this.dragStart.x - e.offsetX;
		var diffY = this.dragStart.y - e.offsetY;
		var left = this.dragStart.imgX - diffX;
		var top = this.dragStart.imgY - diffY;
		if (left > 0) left = 0;
    	if (left + img.width() < this.image.parent().width())
    		left = this.image.parent().width() - img.width();
    	if (top > 0) top = 0;
	    if (top + img.height() < this.image.parent().height())
	    	top = this.image.parent().height() - img.height();
	    img.css({
	    	left:left,
	    	top:top
	    });
    	this.updateCropInfo();
	}
	
    /**
     * Stop the drag
     */
    JuImgPicker.prototype.stopDrag = function()
	{
		if (!this.dragging) return;
		this.dragging = false;
    	this.updateCropInfo();
	}
    
    /**
     * Update the crop information for the image
     */
    JuImgPicker.prototype.updateCropInfo = function()
    {
    	var img = this.getImagePreviewer();
    	var pieces = [
    	    '"l":' + parseInt(img.css('left'))*-1, 
    		'"t":' + parseInt(img.css('top'))*-1, 
    		'"w":' + img.width(), 
    		'"h":' + img.height(),
    		'"cw":' + img.parent().width(), 
    		'"ch":' + img.parent().height()
    	];
    	this.getInputCrop().val("{" + pieces.toString() + "}");
    }

// End JQuery wrapper
}( jQuery ));