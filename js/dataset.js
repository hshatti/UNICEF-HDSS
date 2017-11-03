/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/* global txt, documnet */
function rgbStr(r, g, b) {
        return "#" + (16777216 | b | (g << 8) | (r << 16)).toString(16).slice(1);
}
function rgbtoString() {
    return this.hex;
}
function packageRGB(r, g, b, o) {
        r *= 255;
        g *= 255;
        b *= 255;
        var rgb = {
            r: r,
            g: g,
            b: b,
            hex: rgbStr(r, g, b),
            toString: rgbtoString
        };
        rgb.opacity = o;
        return rgb;
    };
function hsb2rgb(h, s, v, o)  {
        if (typeof(h)=="object" && "h" in h && "s" in h && "b" in h) {
            v = h.b;
            s = h.s;
            o = h.o;
            h = h.h;
        }
        h *= 360;
        var R, G, B, X, C;
        h = (h % 360) / 60;
        C = v * s;
        X = C * (1 - Math.abs(h % 2 - 1));
        R = G = B = v - C;

        h = ~~h;
        R += [C, X, 0, 0, X, C][h];
        G += [X, C, C, X, 0, 0][h];
        B += [0, 0, X, C, C, X][h];
        return packageRGB(R, G, B, o);
    };
function getNextColor(st1,value){
    if (st1===undefined) return {h:0,s:1,b:0.75}
    var st = st1;
            rgb = this.hsb2rgb(st.h, st.s, st.b);
        st.h += .075;
        if (st.h > 1) {
            st.h = 0;
            st.s -= .2;
            st.s <= 0 && (st1 = {h: 0, s: 1, b: st.b});
        }
        return st;
}

//Array.prototype.sumArray = function (arr) {
//    var sum = [];
//    if (arr != null && this.length == arr.length) {
//        for (var i = 0; i < arr.length; i++) {
//            sum.push(this[i] + arr[i]);
//        }
//    }
//    return sum;
//};
var sum = function (a,b){ return Number(a)+Number(b);},
max = function (a,b){ return Math.max(Number(a),Number(b));},
min = function (a,b){ return Math.min(Number(a),Number(b));};

function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
  var angleInRadians = (angleInDegrees-90) * Math.PI / 180.0;
  return {
    x: centerX + (radius * Math.cos(angleInRadians)),
    y: centerY + (radius * Math.sin(angleInRadians))
  };
}

function describeArc(x, y, radius, startAngle, endAngle,line){
    var start = polarToCartesian(x, y, radius, endAngle);
    var end = polarToCartesian(x, y, radius, startAngle);
    var largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";
    var d = (line?'L':'M')+[ start.x, start.y].join(",")+"A"+[radius, radius, 0, largeArcFlag, 0, end.x, end.y].join(",");
    return d;       
}

if ($&& typeof $.widget=='function') // am i jQuery?
  $( function() {
        $.widget( "custom.donut", {
       _create:function(){
            xmlNS='http://www.w3.org/2000/svg';
            this.svg=$(document.createElementNS(xmlNS,'svg'));
            this.g=$(document.createElementNS(xmlNS,'g'));
//            this.arc=$(document.createElementNS(xmlNS,'path'));
            this.arcs=[];
            this.circle=$(document.createElementNS(xmlNS,'path'));
            if (this.options.caption) ;else this.options.caption=this.element.attr('text');
            if (this.options.radius!==undefined) this.r=this.options.radius;else this.r=100;
            this.txt=$(document.createElementNS(xmlNS,'text')).css({alignmentBaseline:'middle',textAnchor:'middle',font:'normal '+this.r*0.42+'px Candara,sans-serif',fill:'#888'});
            if (this.options.percents===undefined){
                this.options.percents=this.element.attr('percents').split(' ');
                this.options.percent=this.options.percents.reduce(sum);
            } else {
                this.options.percent=this.options.percents.reduce(sum);
            }
            for (i in this.options.percents) 
                this.arcs.push($(document.createElementNS(xmlNS,'path')));
            if (this.options.caption) this.txt.html(this.options.caption);
            this.element.append(this.svg);
            this.angles=[];
            this.angle=this.options.percent*360/100;
            for (i in this.options.percents) this.angles.push(this.options.percents[i]*360/100) ;
            transition=1000; 
            fps=50; 
            stepms=transition/fps;   
            step=this.angle/fps;     
            //var an=0;
            //arc.addClass('arc');
            if (this.options.strokeWidth===undefined) this.options.strokeWidth=this.r*0.12;
            if (this.options.circle){
               this.options.circle.strokeWidth=this.options.strokeWidth;
               this.circle.css(this.options.circle);
            }
            else this.circle.css({stroke:'#034'});
            hsb={h:-0.075,s:1,b:0.75};
            if (this.options.colors===undefined) {
                this.options.colors=[];
                for (i in this.arcs) {
                    hsb=getNextColor(hsb);
                    this.options.colors.push(hsb2rgb(hsb.h,hsb.s,hsb.b).hex);
                }
            }

            if (this.options.arc) {
                this.options.arc.strokeWidth=this.options.strokeWidth;
            }
            else this.g.css({strokeWidth:this.options.strokeWidth,fill:'none'});
            for (arc in this.arcs){
                if (this.options.arc) this.arcs[arc].css(this.options.arc);
                this.arcs[arc].css('stroke',this.options.colors[arc]);
            }
            this.svg.width(this.r+parseInt(this.options.strokeWidth));
            this.svg.height(this.r+parseInt(this.options.strokeWidth));
            this.w=this.svg.width();this.h=this.svg.height();
            this.txt.attr({x:this.w/2,y:this.h/2});
            this.g.append(this.circle);
            for (i in this.arcs) this.g.append(this.arcs[i]);
            this.svg.append(this.g);
            this.svg.append(this.txt);
            this.an=0;
            this.ans=[];for (i in this.angles) this.ans.push(0);
            this.anim=setInterval(function (step,that){
              if (that.options.animate) {
                  that.an=Math.min(that.an+step,that.angle);
                  for (i in that.angles) that.ans[i]=Math.min(that.ans[i]+step,that.angles[i]);
              }
              else {
                  that.an=that.angle;
                  that.ans=that.angles;
                  va=that.options.percent;
//                  debug;
              }
              if (that.an<that.angle) va=Math.floor(that.options.percent*that.an/that.angle);
//              console.log('va',va,'/percent',that.options.percent,that.an,that.angle);
              if (that.options.caption!==undefined); 
              else  {
                  that.txt.empty().append(va).append($(document.createElementNS(xmlNS,'tspan')).append('%').css({alignmentBaseline:'hanging',font:'normal '+that.r*0.2+'px Candara'}));
              }
              var sliceStart=0;
              for (arc in that.arcs) {
                  that.arcs[arc].attr('d', (that.options.arcSlice?'M'+[that.w/2,that.h/2].join(','):'')+  describeArc(that.w/2,that.h/2,that.r/2,sliceStart   ,sliceStart+that.ans[arc],that.options.arcSlice)+(that.options.arcSlice?'z':''));
                  sliceStart=sliceStart+that.ans[arc];
              }
              that.circle.attr('d',describeArc(that.w/2,that.h/2,that.r/2,0,359.99)+'z');
              if (that.an==that.angle) {
                  clearInterval(that.anim);
                  
                };
//                console.log(that.arcs);
            },stepms,step,this);
       },
       percents:function(va){
              if (va==undefined) return(this.options.percents);
              this.options.percents=va;
              this.option.percent=va.reduce(sum);
//              console.log(va);
              this.angles=[];
              for (i in this.options.percents) this.angles.push(Math.min(this.options.percents[i]*360/100,359.9999)) ;
              this.ans=this.angles;
              this.angle= Math.min(this.options.percent*360/100,359.9999);
              if (this.options.caption) {
                  this.txt.html(this.options.caption);
              }
              else 
                  this.txt.html(va+'<tspan style="alignment-baseline:hanging;font:normal '+this.r*0.2+'px Candara">%</tspan>'); 
              var sliceStart=0;
              for (arc in this.arcs) {
                  this.arcs[arc].attr('d', (this.options.arcSlice?'M'+[this.w/2,this.h/2].join(','):'')+  describeArc(this.w/2,this.h/2,this.r/2,sliceStart   ,sliceStart+this.ans[arc],this.options.arcSlice)+(this.options.arcSlice?'z':''));
                  sliceStart=sliceStart+this.ans[arc];
              }
              this.circle.attr('d',describeArc(this.w/2,this.h/2,this.r/2,0,359.99)+'z');
              },
       _destroy:function(){
         this.svg.remove();
       },
        _setOptions:function(options){
            this.percents(options.percents);
            this._super( options );
        }
    }),
//    $.widget('custom.bars',{
//        _create:function(){
//                defaultStyle=window.getComputedStyle(this.canvas);
//                Raphael.getColor.reset();
//                
//                if (params.minTicks) minTicks=params.minTicks;else minTicks=3;
//                if (params.left==undefined) var left=40;else left=params.left;
//                if (params.top==undefined) var top=40;else top=params.top;
//                horizonal=params.horizonal;
//                barWidth=params.barWidth;
//                colors=params.colors;
//                if (params.bottom) buttom=params.bottom ;else bottom=60;
//                if (params.right) right=params.right ;else right=40;                maxVal=0;
//                if (params.textAngleX) textAngleX=params.textAngleX;else textAngleX=0;
//                if (params.textAngleY) textAngleY=params.textAngleY;else textAngleY=0;
//                series=params.data;
//                if (!colors){
//                    hsb={h:-0.075,s:1,b:0.75};colors=[];
//                    for (serie in series) {
//                        colors.push(getNextColor(hsb).hex);
//                    }
//                }
//                //start legends 
//                legendLabels=Object.keys(series);
//                //draw legend;
//                this.drawLegend({legendLabels:legendLabels,colors:colors});
//                stack=params.stack;
//                if (params.w==undefined) w=this.canvas.clientWidth-left-right;else w=params.w;
//                if (params.h==undefined) h=this.canvas.clientHeight-top-bottom;else h=params.h;
//                w=Math.round(w),h=Math.round(h);textMaxSizeX=0;
//                if (params.labels==undefined) {
//                    labels=[];
//                    for(serie in series){
//                        for (i=0; i<series[serie].length; i++) labels[i]=i;
//                        break;
//                    }
//                } else labels=params.labels;
//                if (stack) 
//                    {
//                       a=[];
//                       for (serie in series)
//                          if (typeof(series[serie])=='object') 
//                            for (j=0 ;j<series[serie].length;j++)
//                               {a[j]=(a[j]?a[j]:0)+Number(series[serie][j]);}
//                       maxVal=Math.max.apply(Math,a);
//                    }
//                else for (serie in series){
//                  if (typeof(series[serie])=='object') {maxVal=Math.max(Math.max.apply(Math,series[serie]),maxVal);}
//                }
//                var path= [];
//                if (params.rulerSpan) {
//                    rulerSpan=params.rulerSpan ;
//                    valuelines=(Math.floor(horizonal?w:h)/(rulerSpan));
//                }else {
//                    ticks=alignMaxTicks(maxVal,minTicks);
//                    maxVal=ticks[0];
//                    valuelines=ticks[1];
//                    rulerSpan=Math.round(Math.floor(horizonal?w:h)/valuelines);
//                }
//                this.setStart();
//                if (params.drawAxis){
//                    if (params.drawAxis.x) this.path(['M',left,top+h,'L',left+w,top+h]);
//                    if (params.drawAxis.y) this.path(['M',left,top,'L',left,top+h]);
//                } else {
//                    this.path(['M',left,top+h,'L',left+w,top+h]);
//                    this.path(['M',left,top,'L',left,top+h]);
//                }
//                axis=this.setFinish();
//                axis.attr({'stroke-width':1,stroke:'black'});
//                columnWidth = (params.horizonal?h:w) / (labels.length);
//                this.setStart();
//                if (horizonal) {
//                    for (var i=0;i<=valuelines+1;i++){
//                        path=path.concat("M",left +(rulerSpan*(i)),top,"V",top+h);//
//                        textLeft=left+(i*rulerSpan);
//                        textTop=top+h+8;
//                        txt=this.text(textLeft,textTop,roundMillion(i*maxVal/valuelines,params.roundingStyle))// text of the values (X Axis)//
//                        txt.attr({font:defaultStyle.font,"text-anchor":textAngleX===0?"middle":"end"}).rotate(textAngleX,textLeft,textTop);// adjest text and font 
//                        textMaxSizeX=Math.max(textMaxSizeX,txt.attr("textHeight"));
//                    }
//                }
//                else {
//                    for (var i = 0;i<=valuelines; i++) {
//                        path = path.concat(["M", left, h+top-(rulerSpan*(i)) , "H", left + w ]);// horisonal lines
//                        textLeft=left-4;
//                        textTop=top+h-(i*rulerSpan);
//                        txt=this.text(textLeft,textTop,roundMillion(i*maxVal/valuelines,params.roundingStyle));// text of the values (Y Axis)
//                        txt.attr({font:defaultStyle.font,"text-anchor":"end"}).rotate(textAngleY,left-4,top+h-(i*rulerSpan));// adjest text and font 
//                        textMaxSizeX=Math.max(textMaxSizeX,txt.attr("font-size"));
//                    }
//                }
//                axisValues=this.setFinish();
//                //draw grid 
//                grid=this.path().attr({"path":path,"stroke":"silver","stroke-width":0.5});
//// draw the Bars
//                if (barWidth==null) barWidth= columnWidth*0.7;
//                a=[];j=0;
////                if(typeof(series)=='object') series.length=Object.keys(series).length;
//                for (serie in series)
//                { 
//                    if(typeof(series)!='object') continue;
//                    colors.push(colors[0]);color=colors.shift();
//                    vals=[];
//                    if (horizonal)  for (i=0;i<series[serie].length;i++) {
//                        if (!a[i]) a[i]=0;
//                        rect=this.rect(Math.floor(a[i]*w/maxVal)+left,top+Math.round((stack?0:j)*barWidth/(stack?1:Object.keys(series).length))+(columnWidth-barWidth)/2+columnWidth*i,Math.ceil(series[serie][i]*w/maxVal),barWidth/(stack?1:Object.keys(series).length));
//                        rect.attr({"stroke":"none","fill":color});
//                        if (stack||Object.keys(series).length===1) a[i]=a[i]+series[serie][i];
//                    } 
//                    else for (i=0;i<series[serie].length;i++) {
//                        if (!a[i]) a[i]=0;
//                        rect=this.rect(left+((stack?0:j)*barWidth)/(stack?1:Object.keys(series).length)+(columnWidth-barWidth)/2+columnWidth*i,top+h-Math.floor((series[serie][i]+a[i])*h/maxVal),barWidth/(stack?1:Object.keys(series).length),Math.ceil(series[serie][i]*h/maxVal));
//                        rect.attr({"stroke":"none","fill":color});
//                        if (stack||Object.keys(series).length===1) a[i]=a[i]+series[serie][i];
//                    }
//                    j++;
//                }
//// draw labels 
//                if(horizonal) for (i =0; i<labels.length; i++) {
//                    textLeft=left-6;
//                    textTop=Math.round(top+columnWidth*(0.5+i));
//                    this.text(textLeft,textTop,(labels?labels[i]:i)).attr({font:defaultStyle.font,"text-anchor":"end"}).rotate(textAngleY,textLeft,textTop); // text of the labels (Y Axis)
//                    this.setStart();
//                    if (stack||Object.keys(series).length===1) if(params.showValues){
//                        textLeft=left+2+a[i]*w/maxVal;
//                        txt=this.text(textLeft,textTop,roundMillion(a[i],params.roundingStyle)).attr({font:defaultStyle.font,"text-anchor":"start"}); // text of the labels (Y Axis)
//                    }
//                    this.setFinish().translate(0,2);
//                } else for (i =0; i<labels.length; i++) {                    
//                    textLeft=Math.round(left+columnWidth*(0.5+i));    
//                    textTop=top+h+10;
//                    this.text(textLeft,textTop,(labels?labels[i]:i)).attr({font:defaultStyle.font,"text-anchor":(textAngleX!=0?"end":"middle")}).rotate(textAngleX,textLeft,textTop); // text of the labels (X Axis)
//                    this.setStart();
//                    if (stack||Object.keys(series).length===1) if(params.showValues){
//                        textTop=top+h -a[i]*h/maxVal;
//                        txt=this.text(textLeft,textTop,roundMillion(a[i],params.roundingStyle)).attr({font:defaultStyle.font,"text-anchor":(textAngleX!=0?"end":"middle")}); // text of the labels (X Axis)
//                    }
//                    this.setFinish().translate(0,-txt.getBBox().height/2);
//                }
//            }
//        }
//    ),
    $.widget("custom.bar",{
        _create:function(){
//            console.log(this.element);
            var barValues=this.options.values||this.element.attr('data-value').toString().split(' ');
            this.bars=[];
            this.numTotal=barValues.reduce(sum);
            barTotal=0;barLeft=0;
            //this.numTotal=0;
            this.numMax=this.options.max||Number(this.element.data('max').toString().replace(',',''));
            m=Math.max(this.numMax,this.numTotal);
            this.r=$('<span>');
            //if (this.numMax<this.numTotal){
            this.filler=$('<div class="filler">');
            if (this.options.labels){
                if(this.options.labels.value) this.filler.html('<b><span style="top:-1.2em;color:#333">'+this.options.labels.value+'</span></b>');
                if (this.options.labels.max) this.filler.append('<b><span style="right:0;top:-1.2em;color:#333">'+this.options.labels.max+'</span></b>');
            }
            if (this.numMax<this.numTotal) {
                this.filler.width(this.element.width()*100*((this.numMax)/this.numTotal)/$(document).width()+'%');
                this.r.css('right',100*((this.numTotal-this.numMax)/this.numTotal)+'%');
                this.element.css({background:'white'});
            }
            else {
                this.filler.width(this.element[0].style.width);
            }
            this.filler.css({position:'absolute',background:'#444'});
            this.element.append(this.filler);
          //  } else this.element.html('<span style="top:-1.2em;color:#333">Reach</span><span style="right:0;top:-1.2em;color:#333">Target</span>');
            this.div=$('<div>');
            this.div.addClass('stack');
            this.element.append(this.div);
            this.l=$('<span>');
            this.pointer=$('<div>');
            this.pointerCaption=$('<div>');
            this.pointerCaption[0].className='pointer pointer-caption';
            this.l[0].className='num l';
            this.r[0].className='num r';
            this.div.append(this.l);
            this.div.append(this.r);
            this.r.text(this.numMax>999999?Number((this.numMax/1000000).toFixed(this.options.decimals)).toLocaleString()+' m':this.numMax.toLocaleString());
            for (i=0;i<barValues.length;i++)
            {
                b=barValues[i];
                this.bars.push($('<div>'));
                this.bars[i].addClass('pos');
                this.div.append(this.bars[i]);
                barWidth=Number(b.replace(',',''))*100/m;//console.log(barWidth+'%');
                setTimeout(function(bar,b,m,barWidth){bar.css({width:barWidth+'%'});},1,this.bars[i],b,m,barWidth);
    //            this.bars[i].style.width=(Number(b.replace(',',''))*100/m)+'%';
                barLeft=barLeft+barWidth; 
                barTotal=barTotal+barWidth;
                //this.numTotal=this.numTotal+Number(b.replace(',',''));
            }
    //        pointerCaption.innerHTML=Number(1000*numTotal/m).toFixed()/10+'%';
            this.options.decimals=this.options.decimals||0;
            if(this.options.pointer){
                this.pointer.addClass('pointer');
                this.div.append(this.pointer);
                this.div.append(this.pointerCaption);
                this.pointerCaption.css({left:-this.pointerCaption.width()/2+'px'});
            }
            var i=0;
            this.s=setInterval(
                    function(that,l,m,pointer,pointerCaption,div){
                        if (pointer) 
                            if(div.width()-pointer.position().left<46)
                            {
                              pointerCaption.css({position:'absolute',left:'',right:'-2px'});
                            } 
                        numTotalCount=m*(pointer.position().left)/div.width();
                        l.text(numTotalCount>999999?((numTotalCount/1000000).toFixed(that.options.decimals)).toLocaleString()+' m':Number(numTotalCount.toFixed(that.options.decimals)).toLocaleString());
                        if (pointer) pointerCaption.html(Number(100*numTotalCount/that.numMax).toFixed(that.options.decimals)+'%');
                        i++;
                        if (numTotalCount>=that.numTotal||i>20){
                            clearInterval(that.s);
                            if(pointer) pointerCaption.html(Number(100*that.numTotal/that.numMax).toFixed(that.options.decimals)+'%');
                            l.text(that.numTotal>999999?Number((that.numTotal/1000000).toFixed(that.options.decimals)).toLocaleString()+' m':that.numTotal.toLocaleString());
                        }
                    } ,50,this,this.l,m,this.pointer,this.pointerCaption,this.element);
            //l.textContent=(numTotal>999999?((numTotal/100000).toFixed()/10).toLocaleString()+' m':numTotal.toLocaleString());
            
        },
        value:function(va){
            this.numTotal=va.reduce(sum);
            m=Math.max(this.numTotal,this.numMax);
            barTotal=0;barLeft=0;
            for (i=0;i<va.length;i++)
            {
                b=va[i];
                if(this.bars[i]===undefined) {
                    this.bars.push($('<div>'));
                    this.bars[i].addClass('pos');
                    this.div.append(this.bars[i]);
                }
                barWidth=Number(b.toString().replace(',',''))*100/m;//console.log(barWidth+'%');
                setTimeout(function(bar,b,m,barWidth){bar.css({width:barWidth+'%'});},1,this.bars[i],b,this.m,barWidth);
    //            bars[i].style.width=(Number(b.replace(',',''))*100/m)+'%';
                barLeft=barLeft+barWidth; 
                barTotal=barTotal+barWidth;
                //this.numTotal=this.numTotal+Number(b.replace(',',''));
            }
            var i=0;
            this.s=setInterval(
                    function(that,l,m,pointer,pointerCaption,div){
                        numTotalCount=m*(pointer.position().left)/div.width();
                        l.text(numTotalCount>999999?((numTotalCount/1000000).toFixed(that.options.decimals)).toLocaleString()+' m':Number(numTotalCount.toFixed(that.options.decimals)).toLocaleString());
                        if (pointer) pointerCaption.html(Number(100*numTotalCount/that.numMax).toFixed(that.options.decimals)+'%');
                        i++;
                        if (numTotalCount>=that.numTotal||i>20){
                            clearInterval(that.s);
                            if(pointer) {
                                pointerCaption.html(Number(100*that.numTotal/that.numMax).toFixed(that.options.decimals)+'%');
                                if((div.width()-pointer.position().left)<46){
                                    pointerCaption.css({position:'absolute',left:'',right:'-2px'});
                                }
                                else 
                                  pointerCaption.css({left:-pointerCaption.width()/2+'px',position:'',right:''});
                            }
                            l.text(that.numTotal>999999?Number((that.numTotal/1000000).toFixed(that.options.decimals)).toLocaleString()+' m':that.numTotal.toLocaleString());
                        }
                        if (pointer) 
                            if(div.width()-pointer.position().left<46){
                                pointerCaption.css({position:'absolute',left:'',right:'-2px'});
                            }
                            else 
                              pointerCaption.css({left:-pointerCaption.width()/2+'px',position:'',right:''});
                        console.log(div.width()-pointer.position().left);
                    } ,50,this,this.l,m,this.pointer,this.pointerCaption,this.element);

            },
        _destroy:function(){this.element.empty();}
    }),
    $.widget( "custom.holdOn", {
       _create:function(){
           if (this.options.waitElem===undefined) this.options.waitElem=$('<div class="loader">').donut({percents:[0],caption:' ',strokeWidth:1,arcSlice:1,circle:{stroke:'#f3f3f3',fill:'none'},arc:{fill:'#def'},colors:['#3498db']});
           if (this.options.text===undefined) this.options.text=$('<span class="loader-text">');
           this.options.text.html('<div>Loading...</div><span style="color:#3498db;font:bold 28px \'Candara\',sans-serif;line-height:2em;"></span>%');
           this.loader=$('<div id="wait" style="display:none">').append(this.options.waitElem).append(this.options.text);
           this.loader.appendTo(this.element)/*.width(Math.max(this.element.width(),600))*/;
    //       this.loader.height(Math.max(this.element.height(),600));
    //       this.loader.offset(this.element.offset());
           this.loader.fadeIn('fast');
           this.caption=this.options.text.find('span');
           this.progress=0;
           this.t=10;
           this.f=function(that){
               if (that.progress<100) that.progress++;
               else clearTimeout(that.progTimer);
               that.options.waitElem.donut({percents:[that.progress]});
               that.caption.text(that.progress);
               that.t*=1.06;
               that.progTimer=setTimeout(that.f,that.t,that);
           }
           this.progTimer=setTimeout(this.f,this.t,this);
       }, 
       _destroy:function(){
           clearTimeout(this.progTimer);
//           this.options.text.find('div').text('Rendering');
           this.progTimer=setInterval(function(that){
               if (that.progress<100) that.progress=Math.min(that.progress+2,100);
               that.caption.text(that.progress);
               that.options.waitElem.donut({percents:[that.progress]});
               if (that.progress>90) {
                    that.loader.fadeOut(function(){
                       $(this).remove();
//                       console.log('loaded');
                    });
                    clearInterval(that.progTimer);
                }
           },
           10,this);
           
       }
    }),
        $.widget( "custom.box", {
       _create:function(){
            duration=1500;
            box=this.element.addClass('box box-round shadow');
            ease=$('<div>').css({opacity:0,width:'0px',height:'1px',transition:(duration/1000)+'s'}).appendTo(box);
            this.started=setTimeout(function(ease,that){
                ease.css({width:'100%'});
                clearTimeout(that.started);
            },1,ease,this);
            un=$('<div>').addClass('un fa-2x fa-fw').addClass(box.data('class'));
            v=Number(box.attr('value'));
            val=$('<div>').addClass('box-value').text(0);
            cap=$('<div>').addClass('box-caption xsmall').html(box.data('caption'));
            box.append(un).append(val).append(cap);
             
            var framerate=20;
            timeinterval=duration/framerate;
            var frame=0;
            this.timer=setInterval(function(that,val,v,ease){
                if (frame<framerate){val.text(Math.round(v*ease.width()/that.element.width()).toLocaleString());}
                else{clearInterval(that.timer);val.text(v.toLocaleString());}
                frame++;
                
            },timeinterval,this,val,v,ease);
        }
       , 
       _destroy:function(){
                   $(this).remove();
           
       }
    }),
    $.widget( "custom.catcomplete",$.ui.autocomplete, {
       _create:function(){ 
         this._super();//calling parent class _create methode
         this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
       },
      _renderMenu: function( ul, items ) {
        var that = this,
          currentCategory = "";
        $.each( items, function( index, item ) {
          var li;
          if ( item.category != currentCategory&&item.category) {
            ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
            currentCategory = item.category;
          }
          li = that._renderItemData( ul, item );
          if ( item.category ) {
            li.attr( "aria-label", item.category + " : " + item.label );
          }
        });
      }
    }),
    $.widget( "custom.combobox", {
      _create: function() {
        this.wrapper = $( "<div>" )
          .addClass( "custom-combobox" )
          .insertAfter( this.element );
        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
        
      },
      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";
        this.input = $( "<input>" )
          .css('width',Math.max(this.element.width()/2,180))
          .prop('disabled',this.element.prop('disabled'))
          .prop('required',this.element.prop('required'))
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .catcomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" ),
//            select: function(){$(this).parent().prev().trigger('select');},
            select: function(){$(this).parent().prev().trigger('change');}
          })
          .tooltip({
            classes: {
              "ui-tooltip": "ui-state-highlight"
            }
          });
        this.element.on("change",function(){$(this).next().children().val(this.value);});
        this._on( this.input, {
          catcompleteselect: function( event, ui ) {
            //console.log('autocompleteselect: ',ui);
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
//            this._trigger( "change", event, {
//              item: ui.item.option
//            });
          },
 
          catcompletechange: "_removeIfInvalid"
        });
      },
 
      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;
 
        $( "<a>" )
          .prop('disabled',this.element.prop('disabled'))
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-combobox-toggle ui-corner-right" )
          .on( "mousedown", function() {
            wasOpen = input.catcomplete( "widget" ).is( ":visible" );
          })
          .on( "click", function() {
            input.trigger( "focus" );
 
            // Close if already visible
            if ( wasOpen ) {
              return;
            }
 
            // Pass empty string as value to search for, displaying all results
            input.catcomplete( "search", "" );
          });
          
      },
      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        //this.element.html('');
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text(),cat = $(this).data('category');
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              category:cat,//text.toUpperCase().substr(0,1),
              label: text,
              value: text,
              option: this
            };
        }) );
      },
 
      _removeIfInvalid: function( event, ui ) {
 
        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }
        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });
        // Found a match, nothing to do
        if ( valid ) {
          return;
        }
        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " didn't match any item" )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.catcomplete( "instance" ).term = "";
      },
 
      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });
  });

function getValues(e){
    v=[];
    for (i=0;i<e.length;i++){
      v.push(e[i].value);  
    }
    return v;
}


function getContents(e,nullIfEmpty){

    v=[];
    for (k=0;k<e.length;k++){
      v.push(nullIfEmpty?null:e[k].textContent);  
    }
    return v;    
}
function getProps(e,p){
      v=[];
    for (i=0;i<e.length;i++){
      v.push(e[i][p]);  
    }
    return v;   
}

function filterfieldset(event){
    s=event.target.value;
    $(event.target).next().children('div.checkElement').each(function(i,el){el.hidden=(el.textContent.toLowerCase().indexOf(s.toLowerCase())<0);});
}

function uilookupchange(e){
   //console.log('select:',e.target);
  a=$(e.target).data('lookupfields').toString().split(',');
  if (e.target.tagName=='INPUT'){ 
        return;
  }
  v=$(e.target.selectedOptions[0]).data('lookupvalues').toString().split('||');
  a.forEach(function(e,i){
      $('.dbcontrol#'+e.replace(/ /g,'\\ ')).val(v[i]);
  });
  $('select.dbcontrol[data-lookupfields]').each(function(i,el){
        if(e.target!==el){
          b=$(el).data('lookupfields').split(',');
          c=arrayIntersect(a,b);
          if (c.length>0&&c.length+1===a.length) {
              vl=[];
              for (j=0;j<c.length;j++) vl.push(v[j]);
              $(el).find("option[data-lookupvalues='"+vl.join('||').replace(/'/g,"\\'")+"']")[0].selected=true;
              $(el).trigger("change");
          };
        };
  });
  
}

function doDelRecord(a){
    $('#frmEdit').html('<div hidden>'+$(a).parent().children('td:hidden').text()+'</div><h2>This will delete the selected record, Are you sure?</h2>');  
    $(a).parent().children().addClass('marked');
    $('#frmEdit').dialog({title:'Delete Record',modal:true,close:function(){$(a).parent().children().removeClass('marked');$(this).empty().dialog('destroy');},buttons:{ 
          Yes:function(){
            $.post('./fwgo.php',{tbl:$(a).closest('table').data('table'),act:'d',key:$(a).parent().children('td:hidden').text()},function (data,status){
                  //console.log(data);
                  if (data>0) {
                      $(a).parent().remove();
                      //alert(data+' Record deleted.');
                  }
              });
              $(this).dialog("close");
          } , No:function(){
              //$(a).parent().children().removeClass('marked');
              $(this).dialog("close");
          } 
      }
    });
  $('#frmEdit').dialog("open");
  $('.ui-dialog-buttonpane').css({"border-width":"0 0 1px 0",background:"#444"}).insertBefore('.ui-dialog-content');
  
}

function DoExcelUpload(tbl){ 
    var fileInput = document.querySelector('#excelfile');
    var xhr = new XMLHttpRequest();
    $('#uploadprog').show();
    console.log('loading file');
    xhr.open('POST', 'import.php');
    xhr.upload.onloadend=function loadEnd(e) {
      // $('#wait').show();
    };
    xhr.upload.onprogress = function(e) 
    {
        $('#uploadprog').attr('max',e.total);
        $('#uploadprog').val(e.loaded);

    };
    xhr.onload = function(e)
    {

      fileInput.value='';
      $('#uploadprog').hide();
      if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
        {
          //document.getElementById('ImpResult').innerHTML=xhr.responseText;
          
          if(xhr.responseText!='') // for some reason IE is posting a second empty reponse after successful process this is a workaround.
              $('#ImpResult').html(xhr.responseText);
          else console.log('empty response from IE why?!!!!');
          
            //$('#wait').hide();
        }
    };
    // upload success
    var form = new FormData();
    form.append('excelfile', fileInput.files[0]);
    if (tbl) form.append('tbl', tbl);
    xhr.send(form);
}

function doImport(tbl,el){
    var fileInput = document.querySelector('#excelfile');
    var xhr = new XMLHttpRequest();
    $('#uploadprog').show();
    console.log('loading file');
    xhr.open('POST', 'import.php');
    xhr.upload.onloadend=function loadEnd(e) {
       $('#wait').show();
    };
    xhr.upload.onprogress = function(e) 
    {
        $('#uploadprog').attr('max',e.total);
        $('#uploadprog').val(e.loaded);

    };
    xhr.onload = function()
    {
      fileInput.value='';
      $('#uploadprog').hide();
      if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
        {
          
          if (el) $(el).html(xhr.responseText);
          else return(xhr.responseText);
          $('#frmEdit').dialog('close');
          $('#wait').hide();
        }
    };
    // upload success
    var form = new FormData();
    form.append('excelfile', fileInput.files[0]);
    if (tbl) form.append('tbl', tbl);
    xhr.send(form);
}

function doImportPost(e){
   vals=[];
   $('#tblResult tr.data:selected').each(function(i,el){
       vals[vals.length]=getContents($(el).find('td:not(:last)'));
   });
   if (vals.length!=0)
     $.post('import.php',{tbl:$(e.target).data('table'),vals:JSON.stringify(vals),ac:'postimport'},function(data,status){
        if (status='success') {
            res=JSON.parse(data);
            if (res.status){
              location.reload();
              console.log(data);
            }                
            else {
              console.log(data);}
        } 
     });
   else alert('Please select one row at least to import.');
}
function doImportCancel(){
    location.reload();
}
function doPort(a){
    tbl=$(a).closest('table').data('table');
    $('#frmEdit').html(
            '<div id="ImpResult"><h4>Import file</h4><input id="excelfile" type="file" onchange="doImport(\''+ tbl+'\',\'.page-container\');"><progress id="uploadprog" style="display:none"></progress></div><h4> or </h4><div><form method="GET" action="export.php"><input name="tbl" value="'+tbl+'" type="hidden"><input value="Export" type="submit"><select name="fmt"><option value="Excel2007">Excel (*.xlsx)</option><option value="Excel5">Excel5 (*.xls)</option><option value="CSV">CSV (*.csv)</option></select></form></div>');
    $('#frmEdit').dialog({title:'Import/Export',modal:true,autoOpen:false,buttons:null});
    $('#frmEdit').dialog('open');
    
}
function doNewRecord(a){
    $.post('./fwgo.php',{act:'g',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'),par:''},function (data,status) {
       $('#frmEdit').html(data);
       $('.pan-input select').combobox();
//       $('.pan-input select')
       $('.pan-input input[type=search]').catcomplete({
            source:function(request,response){
               $.post('fwgo.php',{act:'l',tbl:this.element.data('table'),dtltable:$(a).closest('table').data('table'),term:request.term},function(data,status){
                 if (status=='success') { 
//                       console.log(data);
                       rec=JSON.parse(data);
                       response(rec);
//                       console.log(rec);
                 }
               });
            },
            select:function(data,i){
                for (var f in i.item.fields)
                    $('.pan-input #'+f).val(i.item.fields[f]);
            }
        }).blur(function(){if (this.value=='') this.getAttribute('data-lookupfields').split(',').forEach(function(f){$('.pan-input #'+f).val('');});});
       $('.pan-input input.datepicker').datepicker({showOn:"button",buttonImageOnly:false/*,buttonImage:"images/calendar.gif"*/,dateFormat:'yy-mm-dd'});
       $('#frmEdit').dialog("open");
    });
    $('#frmEdit').dialog({title:'New Record',autoOpen:false,width:'auto',modal:true,close:function(){$(this).empty().dialog('destroy');},buttons:
                {
                    Save:function(){
                        pwd=$(this).find('.dbcontrol[type=password]').removeClass('ui-state-error');
                        if (pwd.length>1){
                            pwd[1].id='';
                            if(pwd[0].value!=pwd[1].value) {
                              pwd.addClass('ui-state-error').attr("title",'Passwords doesn\'t match.').tooltip().trigger('focus');
                              return;
                            }
                        }
                        req=$(this).find('[required]').removeClass('ui-state-error');
                        if (req.length>0) for (i=0;i<req.length;i++) if ((!req[i].parentElement.parentElement.hidden&&req[i].style.display!='none')&&req[i].value==''){
                            //console.log(req[i]);
                            $(req[i]).addClass('ui-state-error').attr("title",'Field is required!').tooltip().trigger('focus');
                            return;
                        };
                        ids=[];vals=[];dtls=[];
                        $(this).find('.dbcontrol').each(function(i,e){
                            if (!e.hasAttribute('data-lookupfields')&&e.id!='') {ids[ids.length]=e.id;vals[vals.length]=e.value};
                        });
                        $(this).find('fieldset').each(function(i,e){dtl={table:$(e).data('table'),values:getValues($(e).find(':checked'))};dtls.push(dtl);});
                        $.post('/fwgo.php',{act:'i',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'), par:[ids,vals].concat(dtls)},function(data,status){
                            try {rec=JSON.parse(data);} catch (e){ console.log(e.message+'/'+data);}
                            if (rec.status){
                                //location.reload();// ajaxing below instead
                                newTR='<tr class="data">'+rec.data+'</tr>';
                                lasttr=$(a).closest('thead').next();
                                if (lasttr.find('tr').length>0) lasttr.children('tr.data:last').after(newTR);// if found a tbody containing tr elements
                                else {  //nope! i got an orphan header
                                    $(a).closest('thead').after('<tbody>'+newTR+'</tbody>');
                                }
                                $('#frmEdit').dialog("close");
                            }
                            else {
                                $('#frmEdit').prev().find('button:eq(0)').addClass('ui-state-error').attr('title',rec.data).tooltip().tooltip('open');
                                //throw new Error(rec.data); // TODO: translte DB Errsor here into a friendly messages
                            };
                        });
                         
                    },
                    Cancel:function(){
                        $(this).dialog("close");  
                    }
                }
    });
    $('.ui-dialog-buttonpane').css({"border-width":"0 0 1px 0",background:"#444"}).insertBefore('.ui-dialog-content');
    //$('#frmEdit').dialog("open");

}
function doEditRecord(a){
    //console.log($(a).closest('table').data('table'));
    $.post('./fwgo.php',{act:'g',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'),key:$(a).parent().children('td:hidden').text(),par:''},function (data,status) {
       $('#frmEdit').html(data);
       $('.pan-input select').combobox();
       $('.pan-input input[type=search]').catcomplete({
            source:function(request,response){
               $.post('fwgo.php',{act:'l',tbl:this.element.data('table'),dtltable:$(a).closest('table').data('table'),term:request.term},function(data,status){
                 if (status=='success') { 
                       rec=JSON.parse(data);
                       response(rec); 
                 }
               });
            },
            select:function(data,i){
                for (var f in i.item.fields)
                    $('.pan-input #'+f).val(i.item.fields[f]);
            }
        }).blur(function(e){if (e.target.value==='') this.getAttribute('data-lookupfields').split(',').forEach(function(f){$('.pan-input #'+f).val('');});});
       $('.pan-input input.datepicker').datepicker({showOn:"button",buttonImageOnly:false/*,buttonImage:"images/calendar.gif"*/,dateFormat:'yy-mm-dd'});
       $('#frmEdit').dialog("open");
    });
    $(a).parent().children().addClass('marked');
    $('#frmEdit').dialog({title:'Edit Record',width:'auto',autoOpen:false,close:function(){$(a).parent().children().removeClass('marked');$(this).empty().dialog('destroy');},modal:true,buttons:{
            Save:function(){
                pwd=$(this).find('.dbcontrol[type=password]').removeClass('ui-state-error');
                if (pwd.length>1){
                    pwd[1].id='';
                    if(pwd[0].value!=pwd[1].value) {
                      pwd.addClass('ui-state-error').attr("title",'Passwords doesn\'t match.').tooltip().trigger('focus');
                      return;
                    }
                }
                req=$(this).find('[required]').removeClass('ui-state-error');
                if (req.length>0) for (i=0;i<req.length;i++) if ((!req[i].parentElement.parentElement.hidden&&req[i].style.display!='none')&&req[i].value==''){
                    //console.log(req[i]);
                    $(req[i]).addClass('ui-state-error').attr("title",'Field is required!').tooltip().trigger('focus');
                    return;
                };
                ids=[];vals=[];dtls=[];
                $(this).find('.dbcontrol').each(function(i,e){if (!e.hasAttribute('data-lookupfields')&&e.id!='') {ids[ids.length]=e.id;vals[vals.length]=e.value;}});
                $(this).find('fieldset').each(function(i,e){
                    checked=$(e).find(':checked');
                    var dtl={table:$(e).data('table'),values:getValues(checked)};
                    $(e).find('.selected .checkElement').each(function(ii,ee){
                        eee=$(ee).find('[type=text]');
                        if (eee.length>0) dtl.values[ii]+='||'+getValues(eee).join('||');
                    })
                    dtls.push(dtl);
                });
                $.post('/fwgo.php',{act:'u',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'),key:$(a).parent().children('td:hidden').text(), par:[ids,vals].concat(dtls)},function(data,status){
                    try {rec=JSON.parse(data);} catch (e){ console.log(e.message+'/'+data);}
                    if (rec.status){
//                        f=getContents($(a).closest('table').find('th.tblNew:eq(0)').prev().prevAll());
//                        v='';
//                        for (i=0;i<f.length;i++) if (rec.data.fields[f[i]]) v=rec.data.fields[f[i]].value+v;
//                        v='<td hidden>'+rec.data.key+'</td>'+v;
                        $(a).parent().html(rec.data/*v+'<td class="tblEdit" onclick="doEditRecord(this);">Edit</td><td class="tblDelete" onclick="doDelRecord(this);">Del</td>'*/);
                        $('#frmEdit').dialog("close");
                    }
                    else {
                        if (rec.data) $('#frmEdit').prev().find('button:eq(0)').addClass('ui-state-error').attr('title',rec.data).tooltip().tooltip('open');
                        else $('#frmEdit').dialog("close");
                    };
                });
                //$(a).parent().children().removeClass('marked');
                  
            },
            Cancel:function(){
              //$(a).parent().children().removeClass('marked');
              $(this).dialog("close");  
            }
    }});
    $('.ui-dialog-buttonpane').css({"border-width":"0 0 1px 0",background:"#444"}).insertBefore('.ui-dialog-content');
    //$('#frmEdit').dialog("open");
}
var isMouseDown = false;
var startRowIndex = null;
var startCellIndex = null;

function selectableTable(table){
    
    function selectTo(cell) {
        var row = cell.parent('.data');    
        var cellIndex = cell.index();
        var rowIndex = row.index();
        var rowStart, rowEnd, cellStart, cellEnd;
        if (rowIndex < startRowIndex) {
            rowStart = rowIndex;
            rowEnd = startRowIndex;
        } else {
            rowStart = startRowIndex;
            rowEnd = rowIndex;
        }
        if (cellIndex < startCellIndex) {
            cellStart = cellIndex;
            cellEnd = startCellIndex;
        } else {
            cellStart = startCellIndex;
            cellEnd = cellIndex;
        }        
        for (var i = rowStart; i <= rowEnd; i++) {
            var rowCells = cell.parent().parent().find("tr.data").eq(i).find("td");
            for (var j = cellStart; j <= cellEnd; j++) {
                rowCells.eq(j).addClass("ui-selected");
            }        
        }
        var v=0;
        var a=0;
        table.find('.ui-selected').map(function(i,c){t=parseFloat(c.textContent.replace(/,/g,''));if(t) v=v+t;if (c.textContent!=='')a++;return c});
        if (a>1) {
            table.attr('title','COUNT='+a+'\nSUM='+v.toLocaleString());
        } else table.removeAttr('title');
    }
    table.disableSelection().find("td").mousedown(function (e) {
        isMouseDown = true;
        var cell = $(this);
        table.find(".ui-selected").removeClass("ui-selected"); // deselect everything
        table.removeAttr('title');
        if (e.shiftKey) {
            selectTo(cell);                
        } else {
            cell.addClass("ui-selected");
            startCellIndex = cell.index();
            startRowIndex = cell.parent('.data').index();
        }
        //return false; // prevent text selection
    })
    .mouseover(function () {
        if (!isMouseDown) return;
        table.find(".ui-selected").removeClass("ui-selected");
        selectTo($(this));
    })
    .bind("selectstart", function () {
        //return false;
    });
    $(document).mouseup(function () {
        isMouseDown = false;
    }).keydown(function(e) {
        if (typeof(clipboard)==='undefined') return; 
            if (e.ctrlKey&&e.key==='c'){
            selected=table.find(".ui-selected").parent();
            var textCopied='';
            if (selected.length>0) console.log('Ctrl-C Pressed',selected.length);
            for (i=0;i<selected.length;i++){
                textCopied=textCopied+getContents($(selected[i]).find('.ui-selected')).join('\t')+(i<selected.length-1?'\n':'');
            }
            clipboard.copy({'text/plain':textCopied});
        }
    });

}
function arrayIntersect(a,b){
    return a.filter(function(e){return (b.indexOf(e)!==-1);});
}

function initgraph() {
//                        w=$('div#wait');
//                        w.show();
                        //console.log('initing graph...');
//                        console.log('koko');
//                        $('table#tblResult').selectable({cancel:':hidden',filter:'td'});
//                        console.log('kaka');
                        selectableTable($('.dbgrid .selectable'));
                        p=$('div#params').text();
                        if (p=='') {
                            //w.fadeOut(); 
                            return;
                        };
                        var params=JSON.parse($('div#params').text());
                        if (params.groups) for (var i=0;i<params.groups.length;i++){
                          $('.grp'+i).css(params.groups[i].options);
                        }
                        //console.log('checking groups...');
                        $('.dbgrid tr.grpheader td').prop('colspan',params.colspan);
                        $('.dbgrid').css({width:params.tablewidth});
                        $('.dbgrid td.num').each(function(i,e) { if(!isNaN(e.innerHTML)&&(e.innerHTML!='')) e.innerHTML=Number(e.innerHTML).toLocaleString();});
                        //$('.dbgrid tr.data:even').css({'background':'#f0f0f0'}); // /*'linear-gradient(whitesmoke,lightgray)'*/
                        $('.dbgrid th').resizable({handles:'e'}); //columns can be resized
                        //console.log('headers are resizable...');
                        if (params.progs!=null) for (var i=0;i<params.progs.length;i++)
                          $('.dbgrid tr.data :nth-child('+params.progs[i].col+')>div').each(function(){ 
                            $(this).progressbar({value: Number($(this).attr('value'))} ).children('.progress-label').css( { position: 'absolute', width: '100%', 'text-shadow': '1px 1px 1px #fff'}).text($(this).attr('value')+'%');
                            if(params.progs[i].options) $(this).progressbar().find('.ui-progressbar-value').css(params.progs[i].options);
                          });
                        if (params.tools!=null) for (var i=0;i<params.tools.length;i++){
                          toolHeader=$('.dbgrid tr th:nth-child('+params.tools[i].col+')');
                          toolData=$('.dbgrid tr.data :nth-child('+params.tools[i].col+')>div');
                          toolData.each(function(){ 
                            $(this).parent().prev().attr('title',' ').tooltip({track:true,content:'<b><u>'+toolHeader[0].textContent+'</u></b><br/>'+this.innerHTML,position:{my:'center bottom'}});
                            //if(params.tools[i].options) $(this).find('.tooltip').css(params.tools[i].options);
                          });
                          toolData.parent().remove();
                          toolHeader.remove();
                        }
                        $('#tblResult .grpfooter').each(function(i,e){if (e.textContent==='') e.hidden=true;});//hide summary if no contents  
                        //console.log('footers rendered...');
                        //  ***** Line Analysis *****
                        if (params.anls!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.anls.length;i++){
                            gAnlDivs = $(this).find('tr.data td:nth-child('+params.anls[i].col+')>div');
                            if (params.anls[i].options.labels!=null) 
                              var gAnlLabels = $(this).find('tr.data td:nth-child('+params.anls[i].options.labels+')');
                            gRowCount= gAnlDivs.length;
                            vals = [], labels = [];data=[];
                            for (j=0;j<gAnlDivs.length;j++){
                              data[j]=[];
                              data[j]=gAnlDivs[j].textContent.toString().split(' ');
                              for (k=0;k<data[j].length;k++){ 
                                if (!vals[k]) {
                                  vals[k]=[];vals[k].data=[];}
                                vals[k].data[j]=data[j][k];
                              }
                            }
                            if (gAnlLabels) for (j=0;j<gAnlLabels.length;j++)
                              labels[j]=gAnlLabels[j].textContent;
                            gAnlDivs.parent('td:gt(0)').remove();
                            gAnl=gAnlDivs.first().parent().attr('rowspan',gRowCount).children();
                            gAnl.each(function(){
                                this.textContent='';
                                Raphael($(this)[0],370,270).analyse(vals,labels,params.anls[i].options.line,params.anls[i].options.stack,params.anls[i].options.color);
                            });
                            delete gAnl;delete gAnlDivs;delete gAnlLabels;delete vals;delete Labels; delete gRowCount;
                          }
                        });
                        //console.log('line graphs rendered if any');
                        // ***** Vector Graph (Maps)*****
                        
                        if (params.vecs!=null) for (i=0;i<params.vecs.length;i++) $('.dbgrid th:nth-child('+params.vecs[i].options.vectors+')').hide();
                        //console.log('trying vectors... ');
                        if (params.vecs!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.vecs.length;i++){
                            gVecDivs = $(this).find('tr.data td:nth-child('+params.vecs[i].col+')>div');
                            if (params.vecs[i].options.labels!=null) 
                              var gVecLabels = $(this).find('tr.data td:nth-child('+params.vecs[i].options.labels+')');
                            $(this).find('td:nth-child('+params.vecs[i].options.vectors+')').hide();
                            if (params.vecs[i].options.vectors!=null) 
                              var gVecVectors = $(this).find('tr.data td:nth-child('+params.vecs[i].options.vectors+')');
                            gRowCount= gVecDivs.length;
                            vals = [], labels = [];data=[];vectors=[];
                            for (j=0;j<gVecDivs.length;j++){
                              data[j]=[];
                              data[j]=gVecDivs[j].textContent.toString().split(' ');
                              for (k=0;k<data[j].length;k++){ 
                                if (!vals[k]) {
                                  vals[k]=[];vals[k].data=[];}
                                vals[k].data[j]=data[j][k];
                              }
                            }
                            if (gVecLabels) for (j=0;j<gVecLabels.length;j++)
                              labels[j]=gVecLabels[j].textContent;
                            if (gVecVectors) for (j=0;j<gVecVectors.length;j++)
                              vectors[j]=gVecVectors[j].textContent;
                            gVecDivs.parent('td:gt(0)').remove();
                            gVecVectors.remove();
                            gVec=gVecDivs.first().parent().attr('rowspan',gRowCount).children();
                            if (!params.vecs[i].options.width) params.vecs[i].options.width=500;
                            if (!params.vecs[i].options.height) params.vecs[i].options.height=380;
                            console.log('plotting...');
                            gVec.each(function(){
                                this.textContent='';
                                Raphael($(this)[0],params.vecs[i].options.width,params.vecs[i].options.height).vector(vectors,vals,labels,params.vecs[i].options.stroke,params.vecs[i].options.strokewidth,params.vecs[i].options.fill,null,params.vecs[i].options.font);
                            });
                            console.log('plotted');
                            delete gVec;delete gVecDivs;delete gVecLabels;delete vals;delete Labels; delete gRowCount;delete vectors;
                          }
                        });
                        //console.log('vectors plotted if any...')
                        //  ***** Bar Analysis *****
                        if (params.bars!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.bars.length;i++){
                            gBarDivs = $(this).find('tr.data td:nth-child('+params.bars[i].col+')>div');
                            if (params.bars[i].options.labels!=null) 
                              var gBarLabels = $(this).find('tr.data td:nth-child('+params.bars[i].options.labels+')');
                            gRowCount= gBarDivs.length;
                            vals=[];labels=[];data=[];
                            for (j=0;j<gBarDivs.length;j++){
                              data[j]=[];
                              data[j]=gBarDivs[j].textContent.toString().split(' ');
                              for (k=0;k<data[j].length;k++){ 
                                if (!vals[k]) {
                                  vals[k]=[];vals[k].data=[];}
                                vals[k].data[j]=data[j][k];
                              }
                            }  
                            if (gBarLabels) for (j=0;j<gBarLabels.length;j++)
                              labels[j]=gBarLabels[j].textContent;
                            gBarDivs.parent('td:gt(0)').remove();
                            gBar=gBarDivs.first().parent().attr('rowspan',gRowCount).children();
                            gBar.each(function(){
                                this.textContent='';
                                Raphael(this,370,270).bars(vals,labels,params.bars[i].options.horizonal,params.bars[i].options.stack,params.bars[i].options.color);
                            });
                            delete gBar;delete gBarDivs;delete gBarLabels;delete vals;delete labels; delete gRowCount;
                          }
                        });
                        // ***** handle pie *****
                        if (params.pies!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.pies.length;i++){
                            gPieDivs = $(this).find('tr.data td:nth-child('+params.pies[i].col+')>div');
                            //console.log(JSON.stringify(params.pies[i]));
                            if (params.pies[i].options.labels!=null) 
                              var gPieLabels = $(this).find('tr.data td:nth-child('+params.pies[i].options.labels+')');
                            gRowCount= gPieDivs.length;
                            vals = [], labels = [];
                            for (j=0;j<gPieDivs.length;j++)
                              vals[j]=Number(gPieDivs[j].getAttribute('value'));
                            if (gPieLabels) for (j=0;j<gPieLabels.length;j++)
                              labels[j]=gPieLabels[j].textContent;
                            // else labels=vals;
                            gPieDivs.parent('td:gt(0)').remove();
                            gPie=gPieDivs.first().parent().attr('rowspan',gRowCount).children();
                            gPie.each(function(){
                                Raphael($(this)[0],370,270).pieChart(180,130,60,vals, params.pies[i].options.type, labels,'#fff',null/*['skyblue','magenta','yellow','purple','cyan','maroon','orange','gray','green']*/);
                            });
                            delete gPie;delete gPieDivs;delete gPieLabels;delete vals;delete labels; delete gRowCount;
                          }
                        });
                        //$('div#wait').fadeOut('fast');

                    
}
//process export
function base64(s) { return window.btoa(unescape(encodeURIComponent(s))) }

function fnExcelReport(table,name) { 
$('#wait').show();
 var uri = 'data:application/vnd.ms-excel;base64,'
, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>' 
, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
if (!table.nodeType) table = $(table)[0];
var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
var ua = window.navigator.userAgent;
var msie = ua.indexOf("MSIE "); 
if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){      // If Internet Explorer
    txtArea1.document.open("txt/html","replace");
    txtArea1.document.write(format(template, ctx));
    txtArea1.document.close();
    txtArea1.focus(); 
    txtArea1.document.execCommand("SaveAs",true,"Export.xls");
}
else                 //other browser not tested on IE 11
    window.location.href = uri + base64(format(template, ctx));
    $('#wait').fadeOut('fast');
}