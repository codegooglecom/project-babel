String.prototype.parseColor=function(){
var _1="#";
if(this.slice(0,4)=="rgb("){
var _2=this.slice(4,this.length-1).split(",");
var i=0;
do{
_1+=parseInt(_2[i]).toColorPart();
}while(++i<3);
}else{
if(this.slice(0,1)=="#"){
if(this.length==4){
for(var i=1;i<4;i++){
_1+=(this.charAt(i)+this.charAt(i)).toLowerCase();
}
}
if(this.length==7){
_1=this.toLowerCase();
}
}
}
return (_1.length==7?_1:(arguments[0]||this));
};
Element.collectTextNodes=function(_4){
return $A($(_4).childNodes).collect(function(_5){
return (_5.nodeType==3?_5.nodeValue:(_5.hasChildNodes()?Element.collectTextNodes(_5):""));
}).flatten().join("");
};
Element.collectTextNodesIgnoreClass=function(_6,_7){
return $A($(_6).childNodes).collect(function(_8){
return (_8.nodeType==3?_8.nodeValue:((_8.hasChildNodes()&&!Element.hasClassName(_8,_7))?Element.collectTextNodes(_8):""));
}).flatten().join("");
};
Element.setStyle=function(_9,_a){
_9=$(_9);
for(k in _a){
_9.style[k.camelize()]=_a[k];
}
};
Element.setContentZoom=function(_b,_c){
Element.setStyle(_b,{fontSize:(_c/100)+"em"});
if(navigator.appVersion.indexOf("AppleWebKit")>0){
window.scrollBy(0,0);
}
};
Element.getOpacity=function(_d){
var _e;
if(_e=Element.getStyle(_d,"opacity")){
return parseFloat(_e);
}
if(_e=(Element.getStyle(_d,"filter")||"").match(/alpha\(opacity=(.*)\)/)){
if(_e[1]){
return parseFloat(_e[1])/100;
}
}
return 1;
};
Element.setOpacity=function(_f,_10){
_f=$(_f);
if(_10==1){
Element.setStyle(_f,{opacity:(/Gecko/.test(navigator.userAgent)&&!/Konqueror|Safari|KHTML/.test(navigator.userAgent))?0.999999:null});
if(/MSIE/.test(navigator.userAgent)){
Element.setStyle(_f,{filter:Element.getStyle(_f,"filter").replace(/alpha\([^\)]*\)/gi,"")});
}
}else{
if(_10<0.00001){
_10=0;
}
Element.setStyle(_f,{opacity:_10});
if(/MSIE/.test(navigator.userAgent)){
Element.setStyle(_f,{filter:Element.getStyle(_f,"filter").replace(/alpha\([^\)]*\)/gi,"")+"alpha(opacity="+_10*100+")"});
}
}
};
Element.getInlineOpacity=function(_11){
return $(_11).style.opacity||"";
};
Element.childrenWithClassName=function(_12,_13){
return $A($(_12).getElementsByTagName("*")).select(function(c){
return Element.hasClassName(c,_13);
});
};
Array.prototype.call=function(){
var _15=arguments;
this.each(function(f){
f.apply(this,_15);
});
};
var Effect={tagifyText:function(_17){
var _18="position:relative";
if(/MSIE/.test(navigator.userAgent)){
_18+=";zoom:1";
}
_17=$(_17);
$A(_17.childNodes).each(function(_19){
if(_19.nodeType==3){
_19.nodeValue.toArray().each(function(_1a){
_17.insertBefore(Builder.node("span",{style:_18},_1a==" "?String.fromCharCode(160):_1a),_19);
});
Element.remove(_19);
}
});
},multiple:function(_1b,_1c){
var _1d;
if(((typeof _1b=="object")||(typeof _1b=="function"))&&(_1b.length)){
_1d=_1b;
}else{
_1d=$(_1b).childNodes;
}
var _1e=Object.extend({speed:0.1,delay:0},arguments[2]||{});
var _1f=_1e.delay;
$A(_1d).each(function(_20,_21){
new _1c(_20,Object.extend(_1e,{delay:_21*_1e.speed+_1f}));
});
},PAIRS:{"slide":["SlideDown","SlideUp"],"blind":["BlindDown","BlindUp"],"appear":["Appear","Fade"]},toggle:function(_22,_23){
_22=$(_22);
_23=(_23||"appear").toLowerCase();
var _24=Object.extend({queue:{position:"end",scope:(_22.id||"global")}},arguments[2]||{});
Effect[Element.visible(_22)?Effect.PAIRS[_23][1]:Effect.PAIRS[_23][0]](_22,_24);
}};
var Effect2=Effect;
Effect.Transitions={};
Effect.Transitions.linear=function(pos){
return pos;
};
Effect.Transitions.sinoidal=function(pos){
return (-Math.cos(pos*Math.PI)/2)+0.5;
};
Effect.Transitions.reverse=function(pos){
return 1-pos;
};
Effect.Transitions.flicker=function(pos){
return ((-Math.cos(pos*Math.PI)/4)+0.75)+Math.random()/4;
};
Effect.Transitions.wobble=function(pos){
return (-Math.cos(pos*Math.PI*(9*pos))/2)+0.5;
};
Effect.Transitions.pulse=function(pos){
return (Math.floor(pos*10)%2==0?(pos*10-Math.floor(pos*10)):1-(pos*10-Math.floor(pos*10)));
};
Effect.Transitions.none=function(pos){
return 0;
};
Effect.Transitions.full=function(pos){
return 1;
};
Effect.ScopedQueue=Class.create();
Object.extend(Object.extend(Effect.ScopedQueue.prototype,Enumerable),{initialize:function(){
this.effects=[];
this.interval=null;
},_each:function(_2d){
this.effects._each(_2d);
},add:function(_2e){
var _2f=new Date().getTime();
var _30=(typeof _2e.options.queue=="string")?_2e.options.queue:_2e.options.queue.position;
switch(_30){
case "front":
this.effects.findAll(function(e){
return e.state=="idle";
}).each(function(e){
e.startOn+=_2e.finishOn;
e.finishOn+=_2e.finishOn;
});
break;
case "end":
_2f=this.effects.pluck("finishOn").max()||_2f;
break;
}
_2e.startOn+=_2f;
_2e.finishOn+=_2f;
this.effects.push(_2e);
if(!this.interval){
this.interval=setInterval(this.loop.bind(this),40);
}
},remove:function(_33){
this.effects=this.effects.reject(function(e){
return e==_33;
});
if(this.effects.length==0){
clearInterval(this.interval);
this.interval=null;
}
},loop:function(){
var _35=new Date().getTime();
this.effects.invoke("loop",_35);
}});
Effect.Queues={instances:$H(),get:function(_36){
if(typeof _36!="string"){
return _36;
}
if(!this.instances[_36]){
this.instances[_36]=new Effect.ScopedQueue();
}
return this.instances[_36];
}};
Effect.Queue=Effect.Queues.get("global");
Effect.DefaultOptions={transition:Effect.Transitions.sinoidal,duration:1,fps:25,sync:false,from:0,to:1,delay:0,queue:"parallel"};
Effect.Base=function(){
};
Effect.Base.prototype={position:null,start:function(_37){
this.options=Object.extend(Object.extend({},Effect.DefaultOptions),_37||{});
this.currentFrame=0;
this.state="idle";
this.startOn=this.options.delay*1000;
this.finishOn=this.startOn+(this.options.duration*1000);
this.event("beforeStart");
if(!this.options.sync){
Effect.Queues.get(typeof this.options.queue=="string"?"global":this.options.queue.scope).add(this);
}
},loop:function(_38){
if(_38>=this.startOn){
if(_38>=this.finishOn){
this.render(1);
this.cancel();
this.event("beforeFinish");
if(this.finish){
this.finish();
}
this.event("afterFinish");
return;
}
var pos=(_38-this.startOn)/(this.finishOn-this.startOn);
var _3a=Math.round(pos*this.options.fps*this.options.duration);
if(_3a>this.currentFrame){
this.render(pos);
this.currentFrame=_3a;
}
}
},render:function(pos){
if(this.state=="idle"){
this.state="running";
this.event("beforeSetup");
if(this.setup){
this.setup();
}
this.event("afterSetup");
}
if(this.state=="running"){
if(this.options.transition){
pos=this.options.transition(pos);
}
pos*=(this.options.to-this.options.from);
pos+=this.options.from;
this.position=pos;
this.event("beforeUpdate");
if(this.update){
this.update(pos);
}
this.event("afterUpdate");
}
},cancel:function(){
if(!this.options.sync){
Effect.Queues.get(typeof this.options.queue=="string"?"global":this.options.queue.scope).remove(this);
}
this.state="finished";
},event:function(_3c){
if(this.options[_3c+"Internal"]){
this.options[_3c+"Internal"](this);
}
if(this.options[_3c]){
this.options[_3c](this);
}
},inspect:function(){
return "#<Effect:"+$H(this).inspect()+",options:"+$H(this.options).inspect()+">";
}};
Effect.Parallel=Class.create();
Object.extend(Object.extend(Effect.Parallel.prototype,Effect.Base.prototype),{initialize:function(_3d){
this.effects=_3d||[];
this.start(arguments[1]);
},update:function(_3e){
this.effects.invoke("render",_3e);
},finish:function(_3f){
this.effects.each(function(_40){
_40.render(1);
_40.cancel();
_40.event("beforeFinish");
if(_40.finish){
_40.finish(_3f);
}
_40.event("afterFinish");
});
}});
Effect.Opacity=Class.create();
Object.extend(Object.extend(Effect.Opacity.prototype,Effect.Base.prototype),{initialize:function(_41){
this.element=$(_41);
if(/MSIE/.test(navigator.userAgent)&&(!this.element.hasLayout)){
Element.setStyle(this.element,{zoom:1});
}
var _42=Object.extend({from:Element.getOpacity(this.element)||0,to:1},arguments[1]||{});
this.start(_42);
},update:function(_43){
Element.setOpacity(this.element,_43);
}});
Effect.Move=Class.create();
Object.extend(Object.extend(Effect.Move.prototype,Effect.Base.prototype),{initialize:function(_44){
this.element=$(_44);
var _45=Object.extend({x:0,y:0,mode:"relative"},arguments[1]||{});
this.start(_45);
},setup:function(){
Element.makePositioned(this.element);
this.originalLeft=parseFloat(Element.getStyle(this.element,"left")||"0");
this.originalTop=parseFloat(Element.getStyle(this.element,"top")||"0");
if(this.options.mode=="absolute"){
this.options.x=this.options.x-this.originalLeft;
this.options.y=this.options.y-this.originalTop;
}
},update:function(_46){
Element.setStyle(this.element,{left:this.options.x*_46+this.originalLeft+"px",top:this.options.y*_46+this.originalTop+"px"});
}});
Effect.MoveBy=function(_47,_48,_49){
return new Effect.Move(_47,Object.extend({x:_49,y:_48},arguments[3]||{}));
};
Effect.Scale=Class.create();
Object.extend(Object.extend(Effect.Scale.prototype,Effect.Base.prototype),{initialize:function(_4a,_4b){
this.element=$(_4a);
var _4c=Object.extend({scaleX:true,scaleY:true,scaleContent:true,scaleFromCenter:false,scaleMode:"box",scaleFrom:100,scaleTo:_4b},arguments[2]||{});
this.start(_4c);
},setup:function(){
this.restoreAfterFinish=this.options.restoreAfterFinish||false;
this.elementPositioning=Element.getStyle(this.element,"position");
this.originalStyle={};
["top","left","width","height","fontSize"].each(function(k){
this.originalStyle[k]=this.element.style[k];
}.bind(this));
this.originalTop=this.element.offsetTop;
this.originalLeft=this.element.offsetLeft;
var _4e=Element.getStyle(this.element,"font-size")||"100%";
["em","px","%"].each(function(_4f){
if(_4e.indexOf(_4f)>0){
this.fontSize=parseFloat(_4e);
this.fontSizeType=_4f;
}
}.bind(this));
this.factor=(this.options.scaleTo-this.options.scaleFrom)/100;
this.dims=null;
if(this.options.scaleMode=="box"){
this.dims=[this.element.offsetHeight,this.element.offsetWidth];
}
if(/^content/.test(this.options.scaleMode)){
this.dims=[this.element.scrollHeight,this.element.scrollWidth];
}
if(!this.dims){
this.dims=[this.options.scaleMode.originalHeight,this.options.scaleMode.originalWidth];
}
},update:function(_50){
var _51=(this.options.scaleFrom/100)+(this.factor*_50);
if(this.options.scaleContent&&this.fontSize){
Element.setStyle(this.element,{fontSize:this.fontSize*_51+this.fontSizeType});
}
this.setDimensions(this.dims[0]*_51,this.dims[1]*_51);
},finish:function(_52){
if(this.restoreAfterFinish){
Element.setStyle(this.element,this.originalStyle);
}
},setDimensions:function(_53,_54){
var d={};
if(this.options.scaleX){
d.width=_54+"px";
}
if(this.options.scaleY){
d.height=_53+"px";
}
if(this.options.scaleFromCenter){
var _56=(_53-this.dims[0])/2;
var _57=(_54-this.dims[1])/2;
if(this.elementPositioning=="absolute"){
if(this.options.scaleY){
d.top=this.originalTop-_56+"px";
}
if(this.options.scaleX){
d.left=this.originalLeft-_57+"px";
}
}else{
if(this.options.scaleY){
d.top=-_56+"px";
}
if(this.options.scaleX){
d.left=-_57+"px";
}
}
}
Element.setStyle(this.element,d);
}});
Effect.Highlight=Class.create();
Object.extend(Object.extend(Effect.Highlight.prototype,Effect.Base.prototype),{initialize:function(_58){
this.element=$(_58);
var _59=Object.extend({startcolor:"#ffff99"},arguments[1]||{});
this.start(_59);
},setup:function(){
if(Element.getStyle(this.element,"display")=="none"){
this.cancel();
return;
}
this.oldStyle={backgroundImage:Element.getStyle(this.element,"background-image")};
Element.setStyle(this.element,{backgroundImage:"none"});
if(!this.options.endcolor){
this.options.endcolor=Element.getStyle(this.element,"background-color").parseColor("#ffffff");
}
if(!this.options.restorecolor){
this.options.restorecolor=Element.getStyle(this.element,"background-color");
}
this._base=$R(0,2).map(function(i){
return parseInt(this.options.startcolor.slice(i*2+1,i*2+3),16);
}.bind(this));
this._delta=$R(0,2).map(function(i){
return parseInt(this.options.endcolor.slice(i*2+1,i*2+3),16)-this._base[i];
}.bind(this));
},update:function(_5c){
Element.setStyle(this.element,{backgroundColor:$R(0,2).inject("#",function(m,v,i){
return m+(Math.round(this._base[i]+(this._delta[i]*_5c)).toColorPart());
}.bind(this))});
},finish:function(){
Element.setStyle(this.element,Object.extend(this.oldStyle,{backgroundColor:this.options.restorecolor}));
}});
Effect.ScrollTo=Class.create();
Object.extend(Object.extend(Effect.ScrollTo.prototype,Effect.Base.prototype),{initialize:function(_60){
this.element=$(_60);
this.start(arguments[1]||{});
},setup:function(){
Position.prepare();
var _61=Position.cumulativeOffset(this.element);
if(this.options.offset){
_61[1]+=this.options.offset;
}
var max=window.innerHeight?window.height-window.innerHeight:document.body.scrollHeight-(document.documentElement.clientHeight?document.documentElement.clientHeight:document.body.clientHeight);
this.scrollStart=Position.deltaY;
this.delta=(_61[1]>max?max:_61[1])-this.scrollStart;
},update:function(_63){
Position.prepare();
window.scrollTo(Position.deltaX,this.scrollStart+(_63*this.delta));
}});
Effect.Fade=function(_64){
var _65=Element.getInlineOpacity(_64);
var _66=Object.extend({from:Element.getOpacity(_64)||1,to:0,afterFinishInternal:function(_67){
with(Element){
if(_67.options.to!=0){
return;
}
hide(_67.element);
setStyle(_67.element,{opacity:_65});
}
}},arguments[1]||{});
return new Effect.Opacity(_64,_66);
};
Effect.Appear=function(_68){
var _69=Object.extend({from:(Element.getStyle(_68,"display")=="none"?0:Element.getOpacity(_68)||0),to:1,beforeSetup:function(_6a){
with(Element){
setOpacity(_6a.element,_6a.options.from);
show(_6a.element);
}
}},arguments[1]||{});
return new Effect.Opacity(_68,_69);
};
Effect.Puff=function(_6b){
_6b=$(_6b);
var _6c={opacity:Element.getInlineOpacity(_6b),position:Element.getStyle(_6b,"position")};
return new Effect.Parallel([new Effect.Scale(_6b,200,{sync:true,scaleFromCenter:true,scaleContent:true,restoreAfterFinish:true}),new Effect.Opacity(_6b,{sync:true,to:0})],Object.extend({duration:1,beforeSetupInternal:function(_6d){
with(Element){
setStyle(_6d.effects[0].element,{position:"absolute"});
}
},afterFinishInternal:function(_6e){
with(Element){
hide(_6e.effects[0].element);
setStyle(_6e.effects[0].element,_6c);
}
}},arguments[1]||{}));
};
Effect.BlindUp=function(_6f){
_6f=$(_6f);
Element.makeClipping(_6f);
return new Effect.Scale(_6f,0,Object.extend({scaleContent:false,scaleX:false,restoreAfterFinish:true,afterFinishInternal:function(_70){
with(Element){
[hide,undoClipping].call(_70.element);
}
}},arguments[1]||{}));
};
Effect.BlindDown=function(_71){
_71=$(_71);
var _72=Element.getStyle(_71,"height");
var _73=Element.getDimensions(_71);
return new Effect.Scale(_71,100,Object.extend({scaleContent:false,scaleX:false,scaleFrom:0,scaleMode:{originalHeight:_73.height,originalWidth:_73.width},restoreAfterFinish:true,afterSetup:function(_74){
with(Element){
makeClipping(_74.element);
setStyle(_74.element,{height:"0px"});
show(_74.element);
}
},afterFinishInternal:function(_75){
with(Element){
undoClipping(_75.element);
setStyle(_75.element,{height:_72});
}
}},arguments[1]||{}));
};
Effect.SwitchOff=function(_76){
_76=$(_76);
var _77=Element.getInlineOpacity(_76);
return new Effect.Appear(_76,{duration:0.4,from:0,transition:Effect.Transitions.flicker,afterFinishInternal:function(_78){
new Effect.Scale(_78.element,1,{duration:0.3,scaleFromCenter:true,scaleX:false,scaleContent:false,restoreAfterFinish:true,beforeSetup:function(_79){
with(Element){
[makePositioned,makeClipping].call(_79.element);
}
},afterFinishInternal:function(_7a){
with(Element){
[hide,undoClipping,undoPositioned].call(_7a.element);
setStyle(_7a.element,{opacity:_77});
}
}});
}});
};
Effect.DropOut=function(_7b){
_7b=$(_7b);
var _7c={top:Element.getStyle(_7b,"top"),left:Element.getStyle(_7b,"left"),opacity:Element.getInlineOpacity(_7b)};
return new Effect.Parallel([new Effect.Move(_7b,{x:0,y:100,sync:true}),new Effect.Opacity(_7b,{sync:true,to:0})],Object.extend({duration:0.5,beforeSetup:function(_7d){
with(Element){
makePositioned(_7d.effects[0].element);
}
},afterFinishInternal:function(_7e){
with(Element){
[hide,undoPositioned].call(_7e.effects[0].element);
setStyle(_7e.effects[0].element,_7c);
}
}},arguments[1]||{}));
};
Effect.Shake=function(_7f){
_7f=$(_7f);
var _80={top:Element.getStyle(_7f,"top"),left:Element.getStyle(_7f,"left")};
return new Effect.Move(_7f,{x:20,y:0,duration:0.05,afterFinishInternal:function(_81){
new Effect.Move(_81.element,{x:-40,y:0,duration:0.1,afterFinishInternal:function(_82){
new Effect.Move(_82.element,{x:40,y:0,duration:0.1,afterFinishInternal:function(_83){
new Effect.Move(_83.element,{x:-40,y:0,duration:0.1,afterFinishInternal:function(_84){
new Effect.Move(_84.element,{x:40,y:0,duration:0.1,afterFinishInternal:function(_85){
new Effect.Move(_85.element,{x:-20,y:0,duration:0.05,afterFinishInternal:function(_86){
with(Element){
undoPositioned(_86.element);
setStyle(_86.element,_80);
}
}});
}});
}});
}});
}});
}});
};
Effect.SlideDown=function(_87){
_87=$(_87);
Element.cleanWhitespace(_87);
var _88=Element.getStyle(_87.firstChild,"bottom");
var _89=Element.getDimensions(_87);
return new Effect.Scale(_87,100,Object.extend({scaleContent:false,scaleX:false,scaleFrom:0,scaleMode:{originalHeight:_89.height,originalWidth:_89.width},restoreAfterFinish:true,afterSetup:function(_8a){
with(Element){
makePositioned(_8a.element);
makePositioned(_8a.element.firstChild);
if(window.opera){
setStyle(_8a.element,{top:""});
}
makeClipping(_8a.element);
setStyle(_8a.element,{height:"0px"});
show(_87);
}
},afterUpdateInternal:function(_8b){
with(Element){
setStyle(_8b.element.firstChild,{bottom:(_8b.dims[0]-_8b.element.clientHeight)+"px"});
}
},afterFinishInternal:function(_8c){
with(Element){
undoClipping(_8c.element);
undoPositioned(_8c.element.firstChild);
undoPositioned(_8c.element);
setStyle(_8c.element.firstChild,{bottom:_88});
}
}},arguments[1]||{}));
};
Effect.SlideUp=function(_8d){
_8d=$(_8d);
Element.cleanWhitespace(_8d);
var _8e=Element.getStyle(_8d.firstChild,"bottom");
return new Effect.Scale(_8d,0,Object.extend({scaleContent:false,scaleX:false,scaleMode:"box",scaleFrom:100,restoreAfterFinish:true,beforeStartInternal:function(_8f){
with(Element){
makePositioned(_8f.element);
makePositioned(_8f.element.firstChild);
if(window.opera){
setStyle(_8f.element,{top:""});
}
makeClipping(_8f.element);
show(_8d);
}
},afterUpdateInternal:function(_90){
with(Element){
setStyle(_90.element.firstChild,{bottom:(_90.dims[0]-_90.element.clientHeight)+"px"});
}
},afterFinishInternal:function(_91){
with(Element){
[hide,undoClipping].call(_91.element);
undoPositioned(_91.element.firstChild);
undoPositioned(_91.element);
setStyle(_91.element.firstChild,{bottom:_8e});
}
}},arguments[1]||{}));
};
Effect.Squish=function(_92){
return new Effect.Scale(_92,window.opera?1:0,{restoreAfterFinish:true,beforeSetup:function(_93){
with(Element){
makeClipping(_93.element);
}
},afterFinishInternal:function(_94){
with(Element){
hide(_94.element);
undoClipping(_94.element);
}
}});
};
Effect.Grow=function(_95){
_95=$(_95);
var _96=Object.extend({direction:"center",moveTransistion:Effect.Transitions.sinoidal,scaleTransition:Effect.Transitions.sinoidal,opacityTransition:Effect.Transitions.full},arguments[1]||{});
var _97={top:_95.style.top,left:_95.style.left,height:_95.style.height,width:_95.style.width,opacity:Element.getInlineOpacity(_95)};
var _98=Element.getDimensions(_95);
var _99,initialMoveY;
var _9a,moveY;
switch(_96.direction){
case "top-left":
_99=initialMoveY=_9a=moveY=0;
break;
case "top-right":
_99=_98.width;
initialMoveY=moveY=0;
_9a=-_98.width;
break;
case "bottom-left":
_99=_9a=0;
initialMoveY=_98.height;
moveY=-_98.height;
break;
case "bottom-right":
_99=_98.width;
initialMoveY=_98.height;
_9a=-_98.width;
moveY=-_98.height;
break;
case "center":
_99=_98.width/2;
initialMoveY=_98.height/2;
_9a=-_98.width/2;
moveY=-_98.height/2;
break;
}
return new Effect.Move(_95,{x:_99,y:initialMoveY,duration:0.01,beforeSetup:function(_9b){
with(Element){
hide(_9b.element);
makeClipping(_9b.element);
makePositioned(_9b.element);
}
},afterFinishInternal:function(_9c){
new Effect.Parallel([new Effect.Opacity(_9c.element,{sync:true,to:1,from:0,transition:_96.opacityTransition}),new Effect.Move(_9c.element,{x:_9a,y:moveY,sync:true,transition:_96.moveTransition}),new Effect.Scale(_9c.element,100,{scaleMode:{originalHeight:_98.height,originalWidth:_98.width},sync:true,scaleFrom:window.opera?1:0,transition:_96.scaleTransition,restoreAfterFinish:true})],Object.extend({beforeSetup:function(_9d){
with(Element){
setStyle(_9d.effects[0].element,{height:"0px"});
show(_9d.effects[0].element);
}
},afterFinishInternal:function(_9e){
with(Element){
[undoClipping,undoPositioned].call(_9e.effects[0].element);
setStyle(_9e.effects[0].element,_97);
}
}},_96));
}});
};
Effect.Shrink=function(_9f){
_9f=$(_9f);
var _a0=Object.extend({direction:"center",moveTransistion:Effect.Transitions.sinoidal,scaleTransition:Effect.Transitions.sinoidal,opacityTransition:Effect.Transitions.none},arguments[1]||{});
var _a1={top:_9f.style.top,left:_9f.style.left,height:_9f.style.height,width:_9f.style.width,opacity:Element.getInlineOpacity(_9f)};
var _a2=Element.getDimensions(_9f);
var _a3,moveY;
switch(_a0.direction){
case "top-left":
_a3=moveY=0;
break;
case "top-right":
_a3=_a2.width;
moveY=0;
break;
case "bottom-left":
_a3=0;
moveY=_a2.height;
break;
case "bottom-right":
_a3=_a2.width;
moveY=_a2.height;
break;
case "center":
_a3=_a2.width/2;
moveY=_a2.height/2;
break;
}
return new Effect.Parallel([new Effect.Opacity(_9f,{sync:true,to:0,from:1,transition:_a0.opacityTransition}),new Effect.Scale(_9f,window.opera?1:0,{sync:true,transition:_a0.scaleTransition,restoreAfterFinish:true}),new Effect.Move(_9f,{x:_a3,y:moveY,sync:true,transition:_a0.moveTransition})],Object.extend({beforeStartInternal:function(_a4){
with(Element){
[makePositioned,makeClipping].call(_a4.effects[0].element);
}
},afterFinishInternal:function(_a5){
with(Element){
[hide,undoClipping,undoPositioned].call(_a5.effects[0].element);
setStyle(_a5.effects[0].element,_a1);
}
}},_a0));
};
Effect.Pulsate=function(_a6){
_a6=$(_a6);
var _a7=arguments[1]||{};
var _a8=Element.getInlineOpacity(_a6);
var _a9=_a7.transition||Effect.Transitions.sinoidal;
var _aa=function(pos){
return _a9(1-Effect.Transitions.pulse(pos));
};
_aa.bind(_a9);
return new Effect.Opacity(_a6,Object.extend(Object.extend({duration:3,from:0,afterFinishInternal:function(_ac){
Element.setStyle(_ac.element,{opacity:_a8});
}},_a7),{transition:_aa}));
};
Effect.Fold=function(_ad){
_ad=$(_ad);
var _ae={top:_ad.style.top,left:_ad.style.left,width:_ad.style.width,height:_ad.style.height};
Element.makeClipping(_ad);
return new Effect.Scale(_ad,5,Object.extend({scaleContent:false,scaleX:false,afterFinishInternal:function(_af){
new Effect.Scale(_ad,1,{scaleContent:false,scaleY:false,afterFinishInternal:function(_b0){
with(Element){
[hide,undoClipping].call(_b0.element);
setStyle(_b0.element,_ae);
}
}});
}},arguments[1]||{}));
};

