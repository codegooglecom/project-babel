var fileLoadingImage="/img/lb/loading.gif";
var fileBottomNavCloseImage="/img/lb/closelabel.gif";
var resizeSpeed=7;
var borderSize=10;
var imageArray=new Array;
var activeImage;
if(resizeSpeed>10){
resizeSpeed=10;
}
if(resizeSpeed<1){
resizeSpeed=1;
}
resizeDuration=(11-resizeSpeed)*0.15;
Object.extend(Element,{getWidth:function(_1){
_1=$(_1);
return _1.offsetWidth;
},setWidth:function(_2,w){
_2=$(_2);
_2.style.width=w+"px";
},setHeight:function(_4,h){
_4=$(_4);
_4.style.height=h+"px";
},setTop:function(_6,t){
_6=$(_6);
_6.style.top=t+"px";
},setSrc:function(_8,_9){
_8=$(_8);
_8.src=_9;
},setHref:function(_a,_b){
_a=$(_a);
_a.href=_b;
},setInnerHTML:function(_c,_d){
_c=$(_c);
_c.innerHTML=_d;
}});
Array.prototype.removeDuplicates=function(){
for(i=1;i<this.length;i++){
if(this[i][0]==this[i-1][0]){
this.splice(i,1);
}
}
};
Array.prototype.empty=function(){
for(i=0;i<=this.length;i++){
this.shift();
}
};
var Lightbox=Class.create();
Lightbox.prototype={initialize:function(){
if(!document.getElementsByTagName){
return;
}
var _e=document.getElementsByTagName("a");
for(var i=0;i<_e.length;i++){
var _10=_e[i];
var _11=String(_10.getAttribute("rel"));
if(_10.getAttribute("href")&&(_11.toLowerCase().match("lightbox"))){
_10.onclick=function(){
myLightbox.start(this);
return false;
};
}
}
var _12=document.getElementsByTagName("body").item(0);
var _13=document.createElement("div");
_13.setAttribute("id","overlay");
_13.style.display="none";
_13.onclick=function(){
myLightbox.end();
return false;
};
_12.appendChild(_13);
var _14=document.createElement("div");
_14.setAttribute("id","lightbox");
_14.style.display="none";
_12.appendChild(_14);
var _15=document.createElement("div");
_15.setAttribute("id","outerImageContainer");
_14.appendChild(_15);
var _16=document.createElement("div");
_16.setAttribute("id","imageContainer");
_15.appendChild(_16);
var _17=document.createElement("img");
_17.setAttribute("id","lightboxImage");
_16.appendChild(_17);
var _18=document.createElement("div");
_18.setAttribute("id","hoverNav");
_16.appendChild(_18);
var _19=document.createElement("a");
_19.setAttribute("id","prevLink");
_19.setAttribute("href","#");
_18.appendChild(_19);
var _1a=document.createElement("a");
_1a.setAttribute("id","nextLink");
_1a.setAttribute("href","#");
_18.appendChild(_1a);
var _1b=document.createElement("div");
_1b.setAttribute("id","loading");
_16.appendChild(_1b);
var _1c=document.createElement("a");
_1c.setAttribute("id","loadingLink");
_1c.setAttribute("href","#");
_1c.onclick=function(){
myLightbox.end();
return false;
};
_1b.appendChild(_1c);
var _1d=document.createElement("img");
_1d.setAttribute("src",fileLoadingImage);
_1c.appendChild(_1d);
var _1e=document.createElement("div");
_1e.setAttribute("id","imageDataContainer");
_1e.className="clearfix";
_14.appendChild(_1e);
var _1f=document.createElement("div");
_1f.setAttribute("id","imageData");
_1e.appendChild(_1f);
var _20=document.createElement("div");
_20.setAttribute("id","imageDetails");
_1f.appendChild(_20);
var _21=document.createElement("span");
_21.setAttribute("id","caption");
_20.appendChild(_21);
var _22=document.createElement("span");
_22.setAttribute("id","numberDisplay");
_20.appendChild(_22);
var _23=document.createElement("div");
_23.setAttribute("id","bottomNav");
_1f.appendChild(_23);
var _24=document.createElement("a");
_24.setAttribute("id","bottomNavClose");
_24.setAttribute("href","#");
_24.onclick=function(){
myLightbox.end();
return false;
};
_23.appendChild(_24);
var _25=document.createElement("img");
_25.setAttribute("src",fileBottomNavCloseImage);
_24.appendChild(_25);
},start:function(_26){
hideSelectBoxes();
var _27=getPageSize();
Element.setHeight("overlay",_27[1]);
new Effect.Appear("overlay",{duration:0.2,from:0,to:0.8});
imageArray=[];
imageNum=0;
if(!document.getElementsByTagName){
return;
}
var _28=document.getElementsByTagName("a");
if((_26.getAttribute("rel")=="lightbox")){
imageArray.push(new Array(_26.getAttribute("href"),_26.getAttribute("title")));
}else{
for(var i=0;i<_28.length;i++){
var _2a=_28[i];
if(_2a.getAttribute("href")&&(_2a.getAttribute("rel")==_26.getAttribute("rel"))){
imageArray.push(new Array(_2a.getAttribute("href"),_2a.getAttribute("title")));
}
}
imageArray.removeDuplicates();
while(imageArray[imageNum][0]!=_26.getAttribute("href")){
imageNum++;
}
}
var _27=getPageSize();
var _2b=getPageScroll();
var _2c=_2b[1]+(_27[3]/15);
Element.setTop("lightbox",_2c);
Element.show("lightbox");
this.changeImage(imageNum);
},changeImage:function(_2d){
activeImage=_2d;
Element.show("loading");
Element.hide("lightboxImage");
Element.hide("hoverNav");
Element.hide("prevLink");
Element.hide("nextLink");
Element.hide("imageDataContainer");
Element.hide("numberDisplay");
imgPreloader=new Image();
imgPreloader.onload=function(){
Element.setSrc("lightboxImage",imageArray[activeImage][0]);
myLightbox.resizeImageContainer(imgPreloader.width,imgPreloader.height);
};
imgPreloader.src=imageArray[activeImage][0];
},resizeImageContainer:function(_2e,_2f){
this.wCur=Element.getWidth("outerImageContainer");
this.hCur=Element.getHeight("outerImageContainer");
this.xScale=((_2e+(borderSize*2))/this.wCur)*100;
this.yScale=((_2f+(borderSize*2))/this.hCur)*100;
wDiff=(this.wCur-borderSize*2)-_2e;
hDiff=(this.hCur-borderSize*2)-_2f;
if(!(hDiff==0)){
new Effect.Scale("outerImageContainer",this.yScale,{scaleX:false,duration:resizeDuration,queue:"front"});
}
if(!(wDiff==0)){
new Effect.Scale("outerImageContainer",this.xScale,{scaleY:false,delay:resizeDuration,duration:resizeDuration});
}
if((hDiff==0)&&(wDiff==0)){
if(navigator.appVersion.indexOf("MSIE")!=-1){
pause(250);
}else{
pause(100);
}
}
Element.setHeight("prevLink",_2f);
Element.setHeight("nextLink",_2f);
Element.setWidth("imageDataContainer",_2e+(borderSize*2));
this.showImage();
},showImage:function(){
Element.hide("loading");
new Effect.Appear("lightboxImage",{duration:0.5,queue:"end",afterFinish:function(){
myLightbox.updateDetails();
}});
this.preloadNeighborImages();
},updateDetails:function(){
Element.show("caption");
Element.setInnerHTML("caption",imageArray[activeImage][1]);
if(imageArray.length>1){
Element.show("numberDisplay");
Element.setInnerHTML("numberDisplay","Image "+eval(activeImage+1)+" of "+imageArray.length);
}
new Effect.Parallel([new Effect.SlideDown("imageDataContainer",{sync:true,duration:resizeDuration+0.25,from:0,to:1}),new Effect.Appear("imageDataContainer",{sync:true,duration:1})],{duration:0.65,afterFinish:function(){
myLightbox.updateNav();
}});
},updateNav:function(){
Element.show("hoverNav");
if(activeImage!=0){
Element.show("prevLink");
document.getElementById("prevLink").onclick=function(){
myLightbox.changeImage(activeImage-1);
return false;
};
}
if(activeImage!=(imageArray.length-1)){
Element.show("nextLink");
document.getElementById("nextLink").onclick=function(){
myLightbox.changeImage(activeImage+1);
return false;
};
}
this.enableKeyboardNav();
},enableKeyboardNav:function(){
document.onkeydown=this.keyboardAction;
},disableKeyboardNav:function(){
document.onkeydown="";
},keyboardAction:function(e){
if(e==null){
keycode=event.keyCode;
}else{
keycode=e.which;
}
key=String.fromCharCode(keycode).toLowerCase();
if((key=="x")||(key=="o")||(key=="c")){
myLightbox.end();
}else{
if(key=="p"){
if(activeImage!=0){
myLightbox.disableKeyboardNav();
myLightbox.changeImage(activeImage-1);
}
}else{
if(key=="n"){
if(activeImage!=(imageArray.length-1)){
myLightbox.disableKeyboardNav();
myLightbox.changeImage(activeImage+1);
}
}
}
}
},preloadNeighborImages:function(){
if((imageArray.length-1)>activeImage){
preloadNextImage=new Image();
preloadNextImage.src=imageArray[activeImage+1][0];
}
if(activeImage>0){
preloadPrevImage=new Image();
preloadPrevImage.src=imageArray[activeImage-1][0];
}
},end:function(){
this.disableKeyboardNav();
Element.hide("lightbox");
new Effect.Fade("overlay",{duration:0.2});
showSelectBoxes();
}};
function getPageScroll(){
var _31;
if(self.pageYOffset){
_31=self.pageYOffset;
}else{
if(document.documentElement&&document.documentElement.scrollTop){
_31=document.documentElement.scrollTop;
}else{
if(document.body){
_31=document.body.scrollTop;
}
}
}
arrayPageScroll=new Array("",_31);
return arrayPageScroll;
}
function getPageSize(){
var _32,yScroll;
if(window.innerHeight&&window.scrollMaxY){
_32=document.body.scrollWidth;
yScroll=window.innerHeight+window.scrollMaxY;
}else{
if(document.body.scrollHeight>document.body.offsetHeight){
_32=document.body.scrollWidth;
yScroll=document.body.scrollHeight;
}else{
_32=document.body.offsetWidth;
yScroll=document.body.offsetHeight;
}
}
var _33,windowHeight;
if(self.innerHeight){
_33=self.innerWidth;
windowHeight=self.innerHeight;
}else{
if(document.documentElement&&document.documentElement.clientHeight){
_33=document.documentElement.clientWidth;
windowHeight=document.documentElement.clientHeight;
}else{
if(document.body){
_33=document.body.clientWidth;
windowHeight=document.body.clientHeight;
}
}
}
if(yScroll<windowHeight){
pageHeight=windowHeight;
}else{
pageHeight=yScroll;
}
if(_32<_33){
pageWidth=_33;
}else{
pageWidth=_32;
}
arrayPageSize=new Array(pageWidth,pageHeight,_33,windowHeight);
return arrayPageSize;
}
function getKey(e){
if(e==null){
keycode=event.keyCode;
}else{
keycode=e.which;
}
key=String.fromCharCode(keycode).toLowerCase();
if(key=="x"){
}
}
function listenKey(){
document.onkeypress=getKey;
}
function showSelectBoxes(){
selects=document.getElementsByTagName("select");
for(i=0;i!=selects.length;i++){
selects[i].style.visibility="visible";
}
}
function hideSelectBoxes(){
selects=document.getElementsByTagName("select");
for(i=0;i!=selects.length;i++){
selects[i].style.visibility="hidden";
}
}
function pause(_35){
var now=new Date();
var _37=now.getTime()+_35;
while(true){
now=new Date();
if(now.getTime()>_37){
return;
}
}
}
function initLightbox(){
myLightbox=new Lightbox();
}
Event.observe(window,"load",initLightbox,false);

