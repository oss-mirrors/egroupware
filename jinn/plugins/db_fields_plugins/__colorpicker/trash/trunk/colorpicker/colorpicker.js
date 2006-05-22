var r=0;
var g=0; // RGB
var b=0;

var h=0;
var s=0; // Hue / Sat / Lum
var l=0;

function switchMode(mode){  // Switches modes in colorpicker
	switch(mode){
		case "1": // Pallet-based choise
			document.getElementById("Palette").style.visibility="visible";
			document.getElementById("Free").style.visibility="hidden";
			document.getElementById("Image").style.visibility="hidden";
			document.getElementById("tab1").style.borderBottomColor="#ccc";
			document.getElementById("tab2").style.borderBottomColor="#eee";
			document.getElementById("tab3").style.borderBottomColor="#eee";
			document.getElementById("mouseover").style.visibility="visible";
			break;
		case "2": // Free color choise
			document.getElementById("Palette").style.visibility="hidden";
			document.getElementById("Free").style.visibility="visible";
			document.getElementById("Image").style.visibility="hidden";
			document.getElementById("tab1").style.borderBottomColor="#eee";
			document.getElementById("tab2").style.borderBottomColor="ccc";
			document.getElementById("tab3").style.borderBottomColor="#eee";
			document.getElementById("mouseover").style.visibility="visible";
			break;
		case "3": default: // Image choise
			document.getElementById("Palette").style.visibility="hidden";
			document.getElementById("Free").style.visibility="hidden";
			document.getElementById("Image").style.visibility="visible";
			document.getElementById("tab1").style.borderBottomColor="#eee";
			document.getElementById("tab2").style.borderBottomColor="#eee";
			document.getElementById("tab3").style.borderBottomColor="#ccc";
			document.getElementById("mouseover").style.visibility="hidden";
			break;
	}
}

function calcHSL(){ // Calculate the RGB with HSL as input
	check(); // no illegal stuff as inputs
	h=document.getElementById('H').value ? document.getElementById('H').value : 0;
	s=document.getElementById('S').value ? document.getElementById('S').value : 0;
	l=document.getElementById('L').value ? document.getElementById('L').value : 0;

	hsltorgb(h*.01,s*.01,l*.01); // HSL takes 0..1 as values not 0..100

	document.getElementById('R').value=r;
	document.getElementById('G').value=g;
	document.getElementById('B').value=b;
	
	t =hex(r);
	t+=hex(g);
	t+=hex(b);
	t="#"+t; // nice #hexcol number 
	
	document.getElementById('hex').value=t;
	document.getElementById('curColor').style.backgroundColor = t;
	slideplot();
}

function calcRGB(){ // Calculate the HSL with RGB as input
	check(); // no illegal stuff as inputs
	r=document.getElementById('R').value ? document.getElementById('R').value : 0;
	g=document.getElementById('G').value ? document.getElementById('G').value : 0;
	b=document.getElementById('B').value ? document.getElementById('B').value : 0;

	rgbtohsl(r,g,b); // RGB takes 0..255 as values
	
	document.getElementById('H').value=h;
	document.getElementById('S').value=s;
	document.getElementById('L').value=l;
	
	t =hex(r);
	t+=hex(g);
	t+=hex(b);
	t="#"+t; // nice #hexcol number 
	
	document.getElementById('hex').value=t;
	document.getElementById('curColor').style.backgroundColor = t;
	slideplot();
}

function check(){ // Check HSL and RGB inputs on illegal chars
	h=document.getElementById('H').value ? document.getElementById('H').value : 0;
	s=document.getElementById('S').value ? document.getElementById('S').value : 0;
	l=document.getElementById('L').value ? document.getElementById('L').value : 0;
	if(h>99){ // Hue 100 == 0 hence 99 as maximum value
		document.getElementById('H').value = 99;
		h=99;
	} else if (h<0){
		document.getElementById('H').value = 0;
		h=0;
	}
	if(s>100){
		document.getElementById('S').value = 100;
		s=100;
	} else if (s<0){
		document.getElementById('S').value = 0;
		s=0;
	}
	if(l>100){
		document.getElementById('L').value = 100;
		l=100;
	} else if (l<0){
		document.getElementById('L').value = 0;
		l=0;
	}
	r=document.getElementById('R').value ? document.getElementById('R').value : 0;
	g=document.getElementById('G').value ? document.getElementById('G').value : 0;
	b=document.getElementById('B').value ? document.getElementById('B').value : 0;
	if(r>255){
		document.getElementById('R').value=255;
		r=255;
	} else if (r<0){
		document.getElementById('R').value=0;
		r=0;
	}
		if(g>255){
		document.getElementById('G').value=255;
		g=255;
	} else if (g<0){
		document.getElementById('G').value=0;
		g=0;
	}	if(b>255){
		document.getElementById('B').value=255;
		b=255;
	} else if (b<0){
		document.getElementById('B').value=0;
		b=0;
	}
}

function slideplot(){ // Plot the colourfade next to the big gradient
	document.getElementById('slideplot').innerHTML="";
	var h=document.getElementById('H').value ? document.getElementById('H').value : 0;
	var s=document.getElementById('S').value ? document.getElementById('S').value : 0;
	var q=4;
	str="";
	for(l=100;l>0;l-=q){
			hsltorgb(h*.01,s*.01,l*.01);
			t =hex(r);
			t+=hex(g);
			t+=hex(b);
			t="#"+t;
			str+="<tr><td style=\"background-color: "+t+"; width: 16px; height: 7px;\" onmousedown=\"sel("+h+","+s+","+l+");\" onmouseover=\"mo('"+t+"');\"></td></tr>";
	}
	str+="<tr><td style=\"background-color: #000; width: 16px; height: 34px;\" onmousedown=\"sel("+h+","+s+",0);\"></td></tr>";
	document.getElementById('slideplot').innerHTML="<table id=\"generatedSlideplot\" cellspacing=\"0\" cellpadding=\"0\">"+str+"</table>";
	document.getElementById('generatedSlideplot').style.cursor="crosshair";
	document.getElementById('slideplot').style.cursor="default";
}


function plot(){ // The Main BIG gradient
	var h=document.getElementById('H').value ? document.getElementById('H').value : 0;
	var s=document.getElementById('S').value ? document.getElementById('S').value : 0;
	var l=document.getElementById('L').value ? document.getElementById('L').value : 0;
	var q=4; // quality c.q. resolution
	str="";
	for(s=100;s>0;s-=q){
		str+="<tr>";
		for(h=0;h<100;h+=(q/2)){
			hsltorgb(h*.01,s*.01,l*.01);
			t =hex(r);
			t+=hex(g);
			t+=hex(b);
			t="#"+t;
			str+="<td style=\"width: 4px; height: 8px; background-color: "+t+";\" onmousedown=\"sel("+h+","+s+","+l+");\" onmouseover=\"mo('"+t+"');\"></td>";
		}
		str=str+"</tr>";
	}
	str+="<tr><td style=\"width: 200px; height: 4px; background-color: #fff;\" onmousedown=\"sel(0,0,100);\" colspan=\"50\"></td>";

	document.getElementById('gradient').innerHTML="<table id=\"generatedGradient\" cellspacing=\"0\" cellpadding=\"0\" style=\"\">"+str+"</table>";
	document.getElementById('generatedGradient').style.cursor="crosshair";
	document.getElementById('gradient').style.cursor="default";
}

function sel(h,s,l){
	document.getElementById('H').value=h;
	document.getElementById('S').value=s;
	document.getElementById('L').value=l;
	calcHSL();
}
function sel2(val){
	document.getElementById("hex").value=val;
	calcHex();
}
function hsltorgb(h,s,l){
	if ( s == 0 ) {
             // achromatic (grey)  
		  r = l;
		  g = l;
		  b = l;
       } else {
		sector = Math.floor( 6 * h ); 
		f = 6*h - sector;
		m = l;
		p = l * ( 1 - s);
		q = l * ( 1 - s * f );
		t = l * ( 1 - s * ( 1 - f ) );
		switch( sector ){
			case 0:
				r = m;
				g = t;
				b = p;
				break;
			case 1:
				r = q;
				g = m;
				b = p;
				break;               
			case 2:
				r = p;
				g = m;
				b = t;
				break;               
			case 3:
				r = p;
				g = q;
				b = m;
				break;
			case 4:
				r = t;
				g = p;
				b = m;
				break; 
			default:     
				r = m;
				g = p;
				b = q;
				break; 
		} 
	 }
	 r=Math.round(r*255);
	 g=Math.round(g*255);
	 b=Math.round(b*255);
	 return;
 } 
 
function rgbtohsl(r,g,b){
		
	tr = r/255;
	tg = g/255;
	tb = b/255;
	
	pmin = Math.min(Math.min(tr,tg),tb);
	pmax = Math.max(Math.max(tr,tg),tb);
	// Lightness
	l = (pmin+pmax)/2;
	l = Math.round(l*100);
	
	if (pmin == pmax) {
		h = 0;
		s = 0;
	}
	else {
		// Saturation
		if (l<0.5) {
			s = (pmax-pmin)/(pmax+pmin);
			}
		else {
			s = (pmax-pmin)/(2-pmax-pmin);
		}
		s = Math.round(s*100);
//		alert(s);
	
		// Hue
		var delta = pmax-pmin;
		if (pmax == tr)
			h = (tg-tb)/delta;
		else if (pmax == tg)
			h = 2+(tb-tr)/delta;
		else if (pmax == tb)
			h = 4+(tr-tg)/delta;
		h = h*60;
		if (h<0)
			h+=360;
		h = h/360*100;
		h = Math.round(h);
	}
/*		
document.getElementById("debug").value+="r: "+r+"\n";
document.getElementById("debug").value+="g: "+g+"\n";
document.getElementById("debug").value+="b: "+b+"\n";
document.getElementById("debug").value+="h: "+h+"\n";
document.getElementById("debug").value+="s: "+s+"\n";
document.getElementById("debug").value+="l: "+l+"\n"+"\n";
*/
		return;
}
	 

function hex(val) {
    var digits = new Array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f')
    if(val < 16) return("0"+digits[val]);
    var prefix = '' + Math.floor(val / 16);
    var suffix = val - prefix * 16;
    if (prefix > 16) return(hex(prefix) + digits[suffix]);
    return(digits[prefix] + digits[suffix]);
}

function mo(colObj){ // mo is MouseOver
	document.getElementById("mouseover").style.backgroundColor=colObj;
}

function calcHex(){
	h=document.getElementById("hex").value;
	if(h.length==7){
		if(h.charAt(0)=="#"){
			r=parseInt(h.substring(1,3),16);			
			g=parseInt(h.substring(3,5),16);			
			b=parseInt(h.substring(5,7),16);
			if(r>=0 && r<=255 && g>=0 && g<=255 && b>=0 && b<=255){ 
				document.getElementById("R").value=r;
				document.getElementById("G").value=g;
				document.getElementById("B").value=b;
				calcRGB();
			}
		}
	}
}

function submitter(obj){
	document.getElementById("form_curColor").value=document.getElementById("hex").value;
	document.getElementById(obj).submit();
}

function doeQuit(){
	var fieldid = parent.document.getElementById("fieldid").value;
	opener.document.getElementById(fieldid).value=document.getElementById("hex").value;
	window.close();
}

