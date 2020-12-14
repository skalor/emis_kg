// QRCODE reader Copyright 2011 Lazar Laszlo
// http://www.webqr.com

var gCtx = null;
var gCanvas = null;
var c=0;
var stype=0;
var gUM=false;
var webkit=false;
var moz=false;
var v=null;
var deviceAccess = false;

var vidhtml = '<video id="v" autoplay></video>';

function initCanvas(w,h)
{
    gCanvas = document.getElementById("qr-canvas");
    gCanvas.style.width = w + "px";
    gCanvas.style.height = h + "px";
    gCanvas.width = w;
    gCanvas.height = h;
    gCtx = gCanvas.getContext("2d");
    gCtx.clearRect(0, 0, w, h);
}


function captureToCanvas() {
    if(stype!=1)
        return;
    if(gUM)
    {
        try {
            gCtx.drawImage(v,0,0);
            try {
                qrcode.decode();
                setTimeout(captureToCanvas, 500);
            }
            catch(e){       
                console.log(e);
                setTimeout(captureToCanvas, 500);
            }
        }
        catch(e){       
                console.log(e);
                setTimeout(captureToCanvas, 500);
        }
    }
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function read(a)
{
    a = decodeUTF16(a);
    if (!a)
        return;
    if (!vibrate(a)) {
        return;
    }
    var html="";
    if(a.indexOf("http://") === 0 || a.indexOf("https://") === 0) {
        html += "<a target='_blank' href='" + a + "'>" + a + "</a><br>";
        setTimeout(function (a) {
            location.href = a;
        }, 100, a);
    }
    else {
        html += "<b>" + htmlEntities(a) + "</b><br><br>";
    }
    document.getElementById("result").innerHTML=html;
}

function decodeUTF16(a) {
    try {
        a = a.replace(/(\n|\\n)/g, '\\n');
        a = a.split("").map(function(ch) { return "%"+ch.charCodeAt(0).toString(16); }).join("");
        a = decodeURIComponent(a);
        a = a.replace(/(\n|\\n)/g, '\n');
        return a;
    }
    catch (e) {
        console.log(e);
        return false;
    }

}

function vibrate(reason) {
    if (!window) {
        return false;
    }

    if (!window.navigator) {
        return false;
    }

    if (!window.navigator.vibrate) {
        return false;
    }

    if (window.lastVibrateReason === reason) {
        return false;
    }

    window.lastVibrateReason = reason;

    window.navigator.vibrate(100);

    return true;
}

function isCanvasSupported(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}
function success(stream) 
{
    if (!deviceAccess) {
        alert('После получения доступа к камере, нужно обновить страницу.');
        return location.reload();
    }
    document.getElementById('qrScanVideo').style.display = 'block';
    if (!!document.body.scrollIntoView) {
        document.getElementById('qrScanVideo').scrollIntoView();
    }
    v.srcObject = stream;
    v.play();

    gUM=true;
    setTimeout(captureToCanvas, 500);
}
		
function stop(error)
{
    document.getElementById('qrScanVideo').style.display = 'none';
    document.getElementById("result").innerHTML = '';
    gUM=false;
    window.lastVibrateReason = null;
    if (!!v) {
        stype = 0;
        v.pause();
    }
    return;
}

function load()
{
	if(isCanvasSupported() && window.File && window.FileReader)
	{
		initCanvas(800, 600);
		qrcode.callback = read;
		document.getElementById("mainbody").style.display="inline";
        //setwebcam();
	}
	else
	{
		document.getElementById("mainbody").style.display="inline";
		document.getElementById("mainbody").innerHTML='<p id="mp1">QR code scanner for HTML5 capable browsers</p><br>'+
        '<br><p id="mp2">sorry your browser is not supported</p><br><br>'+
        '<p id="mp1">try <a href="http://www.mozilla.com/firefox"><img src="firefox.png"/></a> or <a href="http://chrome.google.com"><img src="chrome_logo.gif"/></a> or <a href="http://www.opera.com"><img src="Opera-logo.png"/></a></p>';
	}
}

function setwebcam()
{
    if (gUM) {
        return stop('');
    }
	var options = true;
	if(navigator.mediaDevices && navigator.mediaDevices.enumerateDevices)
	{
		try{
			navigator.mediaDevices.enumerateDevices()
			.then(function(devices) {
			  devices.forEach(function(device) {
				if (device.kind === 'videoinput') {
				  if(device.label.toLowerCase().search("back") >-1)
					options={'deviceId': {'exact':device.deviceId}, 'facingMode':'environment'} ;
				  if (!!device.label)
                    deviceAccess = true;
				}
				console.log(device.kind + ": " + device.label +" id = " + device.deviceId);
			  });
			  setwebcam2(options);
			});
		}
		catch(e)
		{
			console.log(e);
		}
	}
	else{
		console.log("no navigator.mediaDevices.enumerateDevices" );
		setwebcam2(options);
	}
	
}

function setwebcam2(options)
{
	console.log(options);
    if (!document.getElementById("result").innerHTML)
	    document.getElementById("result").innerHTML="- сканируем -";
    if(stype==1)
    {
        setTimeout(captureToCanvas, 500);    
        return;
    }
    var n=navigator;
    document.getElementById("outdiv").innerHTML = vidhtml;
    v=document.getElementById("v");


    if(n.mediaDevices.getUserMedia)
    {
        n.mediaDevices.getUserMedia({video: options, audio: false}).
            then(function(stream){
                success(stream);
            }).catch(function(err){
                stop(err);
            });
    }
    else
    if(n.getUserMedia)
	{
		webkit=true;
        n.getUserMedia({video: options, audio: false}, success, stop);
	}
    else
    if(n.webkitGetUserMedia)
    {
        webkit=true;
        n.webkitGetUserMedia({video:options, audio: false}, success, stop);
    }

    stype=1;
    setTimeout(captureToCanvas, 500);
}

