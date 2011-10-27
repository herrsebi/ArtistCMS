/**
 * 
 */

function deleteImage(id, callback){
	$.getJSON("/image/delete/id/" + id, callback);
}

function showPopupError(message){
	var lastErr = $('#statusBox .errorDiv').last();
	var errId = 0;
	if (lastErr.length){
		errId = Number(lastErr.attr('id').substring(5)) + 1;
	}
	$('#statusBox').append('\
	<div class="errorDiv" id="error'+ errId +'">\
	<h3>Oops, an error occurred:</h3>\
	<p>'+message+'</p>\
	</div>');
	$('#error'+errId).fadeIn(500).delay(5000).fadeOut(500);
}

function removeTagWithFade(tag)
{
	$(tag).fadeOut(500, function(event){
		$(tag).remove();
	});
}

function addImageFromUrl(parentTag, url, addedCompleteCallback){
	var lastImg = $(parentTag + " .imgDiv").last();
	var imageId = 0;
	if ( lastImg.length )
		imageId = Number(lastImg.attr('id').substring(5)) + 1;
	$(parentTag).append('\
	<div class="grid_2 imgDiv" id="image' + imageId +'">\
    <div class="imgHeader"></div>\
    <div class="imgContainer">\
     <canvas id="imageCanvas' + imageId +'" width="80" height="80"/>\
    </div>\
    <div class="imgFooter"></div>\
   </div>\
   ');
   var img = new Image();
   img.src = url;
   var context = document.getElementById("imageCanvas"+imageId).getContext("2d");
   img.onload = function() {
	   var sourceFormat = 0;
	   var sourceX = 0,sourceY = 0;
	   if (img.width > img.height) {
		   sourceFormat = img.height;
		   sourceX = (img.width - img.height)/2;
	   } else {
		   sourceFormat = img.width;
		   sourceY = (img.height - img.width)/2;
	   }
	   context.drawImage(img,sourceX,sourceY,sourceFormat,sourceFormat,0,0,80,80);
   };
   $('#image'+imageId).fadeIn(500);
   if (addedCompleteCallback){
	   addedCompleteCallback(imageId);
   }
   return '#image'+imageId;
}

