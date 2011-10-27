/**
 * This JS handles the drag & drop and XHR upload Functionality 
 */

// Singelton FileRegistry instance
fileRegistry = new function(){
	this._files = new Array();
	this._imageIdMap = new Array();
	this._totalSize = 0;
	this._loadinqQueue = 0;
	this._isReading = false;
	this._readPos = 0;
	// Add File and read DataUrl for Display
	this.addFile = function(file){
		if(this._totalSize == 0){
			$('#imageDropBoxStatus').stop().animate({opacity:0.0},600);
			$('#uploadButton').stop().animate({visibility:'visible'},600);
		}
		for (var i = 0; i < this._files.length; i++) {
			if (this._files[i].name == file.name) {
				showPopupError('File ' + file.name + ' already queued for upload!');
				return false;
			}
		}
		this._totalSize += file.size;
		this._files.push(file);
		this.updateStatusDisplay();
		this.toggleLoader(++this._loadinqQueue);
		this.readFile();
		return this;
	};
	// Read files into memory, one after the other
	this.readFile = function (){
		if (this._isReading || !this._files[this._readPos] )
			return false;
		var reader = new FileReader();
		reader.onloadend = fileRegistry.readDataUrlComplete;
		reader.readAsDataURL(this._files[this._readPos]);
		this._isReading = true;
		return this._readPos;
	};
	// Add image to the display
	this.readDataUrlComplete = function(e)
	{
		var result = e.currentTarget.result;
		var domImgId = addImageFromUrl("#imageDropBox", result, fileRegistry.imageAddComplete);
		//"this" in this context is a FileReader Object
		fileRegistry._imageIdMap[domImgId] = fileRegistry._readPos; 
		fileRegistry.toggleLoader(--fileRegistry._loadinqQueue);
		fileRegistry._isReading = false;
		if (fileRegistry._readPos != fileRegistry.length-1) {
			fileRegistry._readPos++;
			fileRegistry.readFile();
		}
	};
	// Adds the click handler
	this.imageAddComplete= function(imageId) {
		$('#image'+imageId).click(function (){
			removeTagWithFade('#image'+imageId);
			fileRegistry.removeFileAtIndex(fileRegistry._imageIdMap['#image'+imageId]);
			fileRegistry.updateStatusDisplay();
		});
	};
	// remove the file at given index
	this.removeFileAtIndex = function(index) {
		var file = this._files.splice(index,1);
		this._totalSize -= file[0].size;
		if(this._totalSize == 0){
			$('#imageDropBoxStatus').stop().animate({opacity:1.0},1000);
		}
		for (var c in this._imageIdMap){
			if (this._imageIdMap[c] > index){
				this._imageIdMap[c]--;
			}
			if (this._imageIdMap[c] == index){
				delete this._imageIdMap[c];
			}
		}
	};
	// Remove all images from the registry
	this.purgeFiles = function(){
		this._files = new Array();
		this._totalSize = 0;
	};
	//Update info in the status box
	this.updateStatusDisplay = function()
	{
		var sizeString = "";
		if ( this._totalSize > 1000000000 ){
			sizeString = Math.floor(this._totalSize / 100000000) + ',' + 
						 Math.round((this._totalSize % 100000000)/1000000) + " MB";
		} else if ( this._totalSize > 1000000){
			sizeString = Math.floor(this._totalSize / 1000000) + ',' + 
						 Math.round((this._totalSize % 1000000)/10000) + " MB"; 
		} else if ( this._totalSize > 1000 ){
			sizeString = Math.round(this._totalSize / 1000) + " KB"; 
		} else {
			sizeString = this._totalSize + " Bytes"; 
		};
		$('#fileSize').html(sizeString);
		$('#fileNum').html(this._files.length);
	};
	// Toggle loader image
	this.toggleLoader = function(queueFileNum) {
		if ( queueFileNum > 0 ) {
			$("#loaderBack").show(100);
		} else if ( queueFileNum == 0 && $("#loaderBack") ) {
			$("#loaderBack").hide(100);
		}
	};
		
	this.length = function(){
		return this._files.length;
	};
	
};

//Class to add drag&drop functionality to element
DropHandler = function(element){
	this.cancelEvent = function(evt){
		if (evt.preventDefault)
			evt.preventDefault();
	};
	this.drop = function(e){
		e.preventDefault();
		for (var i = 0; i<e.dataTransfer.files.length; i++) {
			var file = e.dataTransfer.files[i];
			if (file.type.substring(0,5)=="image") {
				fileRegistry.addFile(file);
			} else {
				showPopupError(file.name + ' doesn\'t appear to be an image!');
			}
		}
	};
	
	element.ondrop= this.drop;
	element.ondragover = this.cancelEvent;
	element.ondragenter = this.cancelEvent;
	return this;
};

//Handler class for Progress bars
ProgressHandler = function (element){
	this._progressBar = element;
	this._percentage  = 0;
	var progress_width = $(this._progressBar).width();
	this._increment = Math.ceil(progress_width/100);
	this.setPercentage = function(number)
	{
		if (number < 0 )
			number = 0;
		else if (number > 100)
			number = 100;
		this._percentage = number;
		$(this._progressBar).children('.progressBarInner').animate({ width: this._percentage*this._increment}, 100);
	};
	
	this.reset = function() {
		this._percentage = 0;
		$(this._progressBar).children('.progressBarInner').width(this._percentage*this._increment);
	};
};

//Make pb1 a progress bar
var pFile = new ProgressHandler(document.getElementById("pb1"));
var pTotal = new ProgressHandler(document.getElementById("pb2"));

// Handler for the upload Process
uploadHandler = new function() {
	this._fileCounter = 0;
	this._fileProgress = pFile;
	this._totalProgress = pTotal;
	// Mehtod to start the uploads
	this.startUpload = function()
	{
		uploadHandler._fileProgress.reset();
		if (uploadHandler._fileCounter == 0)
			uploadHandler._fileCounter = fileRegistry.length(); 
		uploadHandler._totalProgress.setPercentage((1-fileRegistry.length/uploadHandler._fileCounter)*100);
		if (fileRegistry.length() <= 0)
			return;
		var xhr = new XMLHttpRequest();
		xhr.upload.onprogress =  uploadHandler.onprogressHandler;
		xhr.onload = uploadHandler.completeHandler;
		xhr.open('POST','/image/upload-xhr');
		xhr.setRequestHeader("Content-Type", "application/octet-stream");
		xhr.setRequestHeader('X-Filename', fileRegistry._files[0].name);
		xhr.send(fileRegistry._files[0]);
	};
	
	this.completeHandler = function(e)
	{
		console.log(e.result);
		removeTagWithFade();
		fileRegistry.removeFileAtIndex(0);
		if (fileRegistry.length() > 0)
			uploadHandler.startUpload();
	};
	
	this.onprogressHandler = function(e)
	{
		uploadHandler._fileProgress.setPercentage(e.loaded/e.total * 100);
	};
};

//make object a dropzone
new DropHandler(document.getElementById("imageDropBoxStatus"));
var dropZone = new DropHandler(document.getElementById("imageDropBox"));

//attach drop click function
document.getElementById("imageDropBox").onclick = uploadHandler.startUpload;