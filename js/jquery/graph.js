
//function autoSpan(value){
//    l=value.toString().length;
////    for 
//} 

function roundMillion(n,style){
    if (style)
    {
        style.decimals=style.decimals||0;
        style.millionText=style.millionText||'m';
        style.kiloText=style.kiloText||'k';
        n=Number(n);
        if (n>999999)
           return ((n/1000000).toFixed(style.decimals)).toLocaleString()+style.millionText;
        else if (style)
            if (style.kilos&&(n>999))
                return ((n/1000).toFixed(style.decimals)).toLocaleString()+style.kiloText;
            else return n.toLocaleString();
        else return n.toLocaleString();
    } else return n;
}

function alignMaxTicks(maxVal,minTicks){
//    if (minTicks===undefined) minTicks=3;
    if (maxVal.toString().length>2){
        r=alignMaxTicks(parseInt(maxVal.toString().substr(0,2))+1,minTicks);
        return [r[0]*Math.pow(10,(maxVal.toString().length-2)),r[1]];
    } else {
    v=(Math.ceil(maxVal));
    vr=[];
    for (i=0;i<minTicks;i++) vr.push(0);
    l=minTicks;
    for (i=0;i<=l;i++) vr.push(Math.ceil(v/vr.length)*vr.length);
//    console.log(vr);
    r=Math.min(vr[l],vr[l+1],vr[l+2],vr[l+3]);
    p=vr.indexOf(r);
    return [r,p];
    }
}

Raphael.fn.xAxis=function(){
    
}

Raphael.fn.yAxis=function(){
    
}

Raphael.fn.pieChart = function (options) {
    // cx, cy, r, values, labeltype, labels, stroke,slicecolors,labelcolor
    var paper = this,
        rad = Math.PI / 180,
        chart = this.set();
    options.cx=options.cx||this.width/2;options.cy=options.cy||this.height/2;
    options.r=options.r||Math.min(this.width,this.height)*0.4;
    function sector(cx, cy, r, startAngle, endAngle, params) {
        if (endAngle===360){
            endAngle=359.99999;
        }
        var x1 = options.cx + options.r * Math.cos(-startAngle * rad),
            x2 = options.cx + options.r * Math.cos(-endAngle * rad),
            y1 = options.cy + options.r * Math.sin(-startAngle * rad),
            y2 = options.cy + options.r * Math.sin(-endAngle * rad);
            arc=paper.path(["M", options.cx, options.cy, "L", x1, y1, "A", options.r, options.r, 0, +(endAngle - startAngle > 180), 0, x2, y2, "z"]);
            arc.attr(params);
            return arc;
    }
    options.slicecolors=options.slicecolors||[];
    options.labels=options.labels||[];
    options.decimals=options.decimals||0;
    var angle = 0,
        total = 0,
        start = 0,
        process = function (j) {
            var value = options.values[j],lbls=[],
                angleplus = 360 * value / total;
                lbls[j] = (options.labels.length===options.values.length&&options.compoundLabels?options.labels[j]+': ':'') +(options['label-type']?'%'+Math.round(100*(Math.pow(10,options.decimals) * value / total))/Math.pow(10,options.decimals):Number(value).toLocaleString());
                if (options.labels.length!==options.values.length) options.labels.push(options.values[j]);
            popangle = angle + (angleplus / 2);
                if (options.slicecolors.length!==options.values.length) {
                    var color = Raphael.hsb(start,start>1?0.5:1, start>1?0.4:0.8),
                    bcolor = Raphael.hsb(start,start>1?0.5:1, start>1?0.8:1);
                    options.slicecolors.push(bcolor);
                } else {
                    color= options.slicecolors[start % options.slicecolors.length];bcolor=color;
                }
            if (options.labelcolor==undefined) options.labelcolor="white";
            options['label-r']=options['label-r']||(options.r*0.65);
            var ms = 500,
                p = sector(options.cx, options.cy, options.r, angle, angle + angleplus, {fill: angle+"-" + bcolor + "-" + color, "stroke": options.stroke||'none', "stroke-width" : options['stroke-width']||1}),
                txt = paper.text(options.cx + (options['label-r']) * Math.cos(-popangle * rad), options.cy + (options['label-r']) * Math.sin(-popangle * rad), lbls[j]).attr({fill: options.labelcolor/*bcolor*/, stroke: "none"/*, opacity: .2*/, "font-size": 12});
            p.mouseover(function () {
                p.stop().animate({transform: "s1.1 1.1 " + options.cx + " " + options.cy}, ms, "elastic");
//                txt.stop().animate({opacity: 1}, ms, "elastic");
            }).mouseout(function () {
                p.stop().animate({transform: ""}, ms, "elastic");
//                txt.stop().animate({opacity: 0.2}, ms);
            }).click(options.click);p.node.index=j;p.node.label=options.labels[j];p.node.value=options.values[j];
            angle += angleplus;
            chart.push(p);
            chart.push(txt);
//            chart.attr({'text-anchor':'middle'})
            if (options.slicecolors.length!==options.values.length) start += .15;else start++;
        };
    for (var i = 0, ii = options.values.length; i < ii; i++) {
        total += options.values[i];
    }
    for (i = 0; i < ii; i++) {
        process(i);
    }
    if (options.drawLegend) this.drawLegend({click:options.click,legendLabels:options.labels,colors:options.slicecolors});
    return chart;
};

Raphael.fn.vector = function (vectors,vals,labels,stroke,strokewidth,fill,colorcount,font) {
   this.setViewBox(100,200,3400,2400);
   if(!stroke) stroke="black";
   if(!strokewidth) strokewidth=1;
   if(!fill) fill="gray";
   if (!colorcount) colorcount=5;
   clr=Raphael.color(fill);
   MaxVal=Math.max(Math.max.apply(Math,vals[0].data));
   for (i=0;i<vectors.length;i++) {
        fillcolor=Raphael.hsl(clr.h,clr.s,1-(clr.l*vals[0].data[i]/MaxVal));
        map=this.path(vectors[i]).attr({"stroke":stroke,"stroke-width":strokewidth,"fill":fillcolor}).toBack();
        d=map.getBBox();
        if (!font) font={"font-face":"fantasy","font-size":70};
        txt=this.text(d.x+d.width/2,d.y+d.height/2,labels[i].toLocaleString()).attr(font);
        //fillcolor=Raphael.getColor();
        
   }    
};

Raphael.fn.drawLegend = function(params){
                params.defaultStyle=params.defaultStyle||window.getComputedStyle(this.canvas);
                this.setStart();
                lp={x:0,width:0};//legend item position
                for (i=0;i<params.legendLabels.length;i++) { //drawing legend
                    lp.x+=lp.width;
                    lp=this.text(lp.x,'1em',params.legendLabels[i]).attr({font:params.defaultStyle.font,'text-anchor':'start'}).getBBox();
//                    lp.x+=2+lp.width;
                    lp=this.rect(2+lp.x+lp.width,lp.cy/2,lp.cy,lp.cy).attr({fill:params.colors[i],stroke:'none'}).click(params.click).getBBox();
                    lp.x+=lp.cy+6;
                }
                legend=this.setFinish();
                legend.translate((this.canvas.clientWidth-legend.getBBox().width)/2,0);
    
}

Raphael.fn.lines = function (params) {
                defaultStyle=window.getComputedStyle(this.canvas);
                console.log(defaultStyle.fontSize);
                if (params.minTicks) minTicks=params.minTicks;else minTicks=3;
                Raphael.getColor.reset();
                if (params.left==undefined) var left=40;else left=params.left;
                if (params.top==undefined) var top=40;else top=params.top;
                horizonal=params.horizonal;
                barWidth=params.barWidth;
                colors=params.colors;
                if (params.bottom) buttom=params.bottom ;else bottom=60;
                if (params.right) right=params.right ;else right=40;
                if(params.areas) areas=params.areas;else areas=0;
                maxVal=0;
                if (params.textAngleX) textAngleX=params.textAngleX;else textAngleX=0;
                if (params.textAngleY) textAngleY=params.textAngleY;else textAngleY=0;
                series=params.data;
                //start legends 
                if (!colors){
                    Raphael.getColor.reset();colors=[];  
                    for (serie in series) {
                        colors.push(Raphael.getColor());
                    }
                }
                legendLabels=Object.keys(series);
                this.drawLegend({legendLabels:legendLabels,colors:colors})
                //draw legend;
                stack=params.stack;
                if (params.labels==undefined) {
                    labels=[];
                    for(serie in series){
                        for (i=0; i<series[serie].length; i++) labels[i]=i;
                        break;
                    }
                } else labels=params.labels;
                if (stack) 
                    {
                       a=[];
                       for (serie in series)
                          if (typeof(series[serie])=='object') 
                            for (j=0 ;j<series[serie].length;j++)
                               {a[j]=(a[j]?a[j]:0)+Number(series[serie][j]);}
                       maxVal=Math.max.apply(Math,a);
                    }
                else for (serie in series){
                  if (typeof(series[serie])=='object') {maxVal=Math.max(Math.max.apply(Math,series[serie]),maxVal);}
                }
                var path= [];
                
                if (params.w==undefined) w=this.canvas.clientWidth-left-right;else w=params.w;
                if (params.h==undefined) h=this.canvas.clientHeight-top-bottom;else h=params.h;
                this.setStart();
                if (params.drawAxis){
                    if (params.drawAxis.x) this.path(['M',left,top+h,'L',left+w,top+h]);
                    if (params.drawAxis.y) this.path(['M',left,top,'L',left,top+h]);
                } else {
                    this.path(['M',left,top+h,'L',left+w,top+h]);
                    this.path(['M',left,top,'L',left,top+h]);
                }
                axis=this.setFinish();
                if (params.rulerSpan) {
                    rulerSpan=params.rulerSpan ;
                    valuelines=Math.ceil(h/(rulerSpan));
                }else {
                    ticks=alignMaxTicks(maxVal,minTicks);
                    maxVal=ticks[0];
                    valuelines=ticks[1];
                    rulerSpan=Math.round(h/valuelines);
                }                
                w=Math.round(w),h=Math.round(h);textMaxSizeX=0;textMaxSizeY=0;
                columnWidth =w / (labels.length-1);
// draw value text   (Y Axis)             
                this.setStart();
                for (var i = 0;i<=valuelines; i++) {
                    path = path.concat(["M", left, h+top-(i * rulerSpan) , "H", left + w ]);// horizonal lines
                    textLeft=left-6;
                    textTop=top+h-(i*rulerSpan);
                    txt=this.text(textLeft,textTop,roundMillion(i*maxVal/valuelines,params.roundingStyle));// text of the values (Y Axis)
                    txt.attr({font:defaultStyle.font,"text-anchor":"end"}).rotate(textAngleY,left-4,top+h-(i*rulerSpan));
                    txt.node.classList='graph-value';
                    textMaxSizeY=Math.max(textMaxSizeY,txt.attr("font-size"));
                }
                textValues=this.setFinish();
// draw vertical values (X Axis)
                this.setStart();
                for (i =0; i<labels.length; i++) {
                    textLeft=Math.round(left+2+(columnWidth*i));    
                    txt=this.text(textLeft,4,(labels?labels[i]:i)).attr({font:defaultStyle.font});txt.node.classList='graph-label'; // text of the labels (X Axis)
                    textMaxSizeX=Math.max(textMaxSizeX,txt.getBBox().width);
//                    console.log(txt.getBBox().width);
                }
                textLabels=this.setFinish();
                textLabels.attr({y:top+h+textLabels.getBBox().y2});
                if (textMaxSizeX>=columnWidth&&!textAngleX)
                    for (i=0; i<textLabels.length; i++){
                        b=textLabels[i].getBBox();
                        textLabels[i].attr({'text-anchor':'start'}).rotate(30,b.cx,b.cy);
                    }
                else
                    for (i=0; i<textLabels.length; i++){
                        b=textLabels[i].getBBox();
                        textLabels[i].attr({'text-anchor':textAngleX==0?'middle':textAngleX>0?'start':'end'}).rotate(textAngleX,b.cx,b.cy);
                    }
                //draw grid 
                grid=this.path().attr({"path":path,"stroke":"silver","stroke-width":0.5}).node.classList='graph-grid';
// draw the line path
                if (typeof(series=='object')) series.length=Object.keys(series).length;j=0;
                markers=this.set();
                for (serie in series) if (Array.isArray(series[serie]))
                {
                    if (stack&&(j>0)){ //start of serie if stacked
                        lastVals[1]=vals[1];
                        vals[1]-=Math.round(series[serie][0]*h/maxVal);
                        
                    }
                    else {
                        vals=[];lastVals=[];  //start of the serie if not stacked
                        vals.push(left,Math.round(top+h-series[serie][0]*h/maxVal));//console.log(vals);
                        if (stack) lastVals.push(left,top+h); // j==0
                    }
                    for (i=1;i<series[serie].length;i++) {//try not to overlap areas if stacked
                        if (stack&&(j>0)){
                            lastVals[(2*i)+1]=vals[(2*i)+1];
                            vals[(2*i)+1]-=Math.round(series[serie][i]*h/maxVal);
                        }
                        else {
                            vals.push(Math.round(left+(i*w/(series[serie].length-1))),Math.round(top+h-(series[serie][i]*h/maxVal)));
                            if (stack) lastVals.push(Math.round(left+(i*w/(series[serie].length-1))),Math.round(top+h)); // j==0
                        }
                    }
                    if(colors) {colors.push(colors[0]);color=colors.shift();}// else color=Raphael.getColor();
                    d='M'+vals.slice(0,2).join(',')+'L'+vals.slice(2).join(',');
                    lines=this.path(d).attr({stroke:color});
                    for (i=0;i<series[serie].length;i++){
                        marker=this.circle(vals[i*2],vals[i*2+1],3);
                        //console.log(marker);
                        marker.node.classList='graph-marker';
                        marker.node.dataset.label=labels[i];
                        marker.node.dataset.value=series[serie][i];
                        marker.node.dataset.serie=serie;
                        markers.push(marker);
                    }
                    lines.attr(params.lines?params.lines:null);
                    lines.node.classList='graph-line';
// draw the filled path
                    
                    if (areas) {
                            val2=[];
                            if(stack) for (i=series[serie].length;i>0;i--) val2.push(lastVals[(2*i)-2],lastVals[(2*i)-1]) ;
                            else val2.push(left+w,top+h,left,top+h);
                            d+=','+val2.join(',')+'z';
//                            console.log(d);
                            filled=this.path(d).attr({stroke:"none",fill:color,"stroke-linejoin":"round",opacity:0.3});
                            filled.attr(params.areas?params.areas:null);
                            filled.node.classList='graph-area';
                    }
                    j++;
                }
                markers.toFront().attr(params.marker?params.markers:{'stroke':'#666',fill:'white','stroke-width':1});
            };

Raphael.fn.bars = function (params) {
//                console.log('canvas',this.canvas);// this will return the SVG element
                defaultStyle=window.getComputedStyle(this.canvas);
                Raphael.getColor.reset();
                
                if (params.minTicks) minTicks=params.minTicks;else minTicks=3;
                if (params.left==undefined) var left=40;else left=params.left;
                if (params.top==undefined) var top=40;else top=params.top;
                horizonal=params.horizonal;
                barWidth=params.barWidth;
                colors=params.colors;
                if (params.bottom) buttom=params.bottom ;else bottom=60;
                if (params.right) right=params.right ;else right=40;                maxVal=0;
                if (params.textAngleX) textAngleX=params.textAngleX;else textAngleX=0;
                if (params.textAngleY) textAngleY=params.textAngleY;else textAngleY=0;
                series=params.data;
                if (!colors){
                    Raphael.getColor.reset();colors=[];
                    for (serie in series) {
                        colors.push(Raphael.getColor());
                    }
                }
                //start legends 
                legendLabels=Object.keys(series);
                //draw legend;
                this.drawLegend({legendLabels:legendLabels,colors:colors});
                stack=params.stack;
                if (params.w==undefined) w=this.canvas.clientWidth-left-right;else w=params.w;
                if (params.h==undefined) h=this.canvas.clientHeight-top-bottom;else h=params.h;
                w=Math.round(w),h=Math.round(h);textMaxSizeX=0;
                if (params.labels==undefined) {
                    labels=[];
                    for(serie in series){
                        for (i in series[serie]) labels[i]=i;
                        break;
                    }
                } else labels=params.labels;
                if (stack) 
                    { 
                       a=[];
                       for (serie in series)
                          if (typeof(series[serie])=='object') 
                            for (j in labels)
                               {a[j]=Number(a[j]||0)+(Number(series[serie][j]||0));}
                       maxVal=Math.max.apply(Math,a);
                    }
                else 
                    for (i=0;i<labels.length;i++){
                        for (serie in series) if (typeof(series[serie])==='object') {
                            if (series[serie][i]===undefined) 
                                series[serie][i]=0;
                            maxVal=Math.max(maxVal,series[serie][i])
                        }
                    }
                var path= [];
                if (params.rulerSpan) {
                    rulerSpan=params.rulerSpan ;
                    valuelines=(Math.floor(horizonal?w:h)/(rulerSpan));
                }else {
                    ticks=alignMaxTicks(maxVal,minTicks);
                    maxVal=ticks[0];
                    valuelines=ticks[1];
                    rulerSpan=Math.round(Math.floor(horizonal?w:h)/valuelines);
                }
                this.setStart();
                if (params.drawAxis){
                    if (params.drawAxis.x) this.path(['M',left,top+h,'L',left+w,top+h]);
                    if (params.drawAxis.y) this.path(['M',left,top,'L',left,top+h]);
                } else {
                    this.path(['M',left,top+h,'L',left+w,top+h]);
                    this.path(['M',left,top,'L',left,top+h]);
                }
                axis=this.setFinish();
                axis.attr({'stroke-width':1,stroke:'black'});
                columnWidth = (params.horizonal?h:w) / (labels.length);
                this.setStart();
                if (horizonal) {
                    for (var i=0;i<=valuelines+1;i++){
                        path=path.concat("M",left +(rulerSpan*(i)),top,"V",top+h);//
                        textLeft=left+(i*rulerSpan);
                        textTop=top+h+8;
                        txt=this.text(textLeft,textTop,roundMillion(i*maxVal/valuelines,params.roundingStyle))// text of the values (X Axis)//
                        txt.attr({font:defaultStyle.font,"text-anchor":textAngleX===0?"middle":"end"}).rotate(textAngleX,textLeft,textTop);// adjest text and font 
                        textMaxSizeX=Math.max(textMaxSizeX,txt.attr("textHeight"));
                    }
                }
                else {
                    for (var i = 0;i<=valuelines; i++) {
                        path = path.concat(["M", left, h+top-(rulerSpan*(i)) , "H", left + w ]);// horisonal lines
                        textLeft=left-4;
                        textTop=top+h-(i*rulerSpan);
                        txt=this.text(textLeft,textTop,roundMillion(i*maxVal/valuelines,params.roundingStyle));// text of the values (Y Axis)
                        txt.attr({font:defaultStyle.font,"text-anchor":"end"}).rotate(textAngleY,left-4,top+h-(i*rulerSpan));// adjest text and font 
                        textMaxSizeX=Math.max(textMaxSizeX,txt.attr("font-size"));
                    }
                }
                axisValues=this.setFinish();
                //draw grid 
                grid=this.path().attr({"path":path,"stroke":"silver","stroke-width":0.5});
// draw the Bars
                if (barWidth==null) barWidth= columnWidth*0.7;
                a=[];j=0;
//                if(typeof(series)=='object') series.length=Object.keys(series).length;
                for (serie in series)
                { 
                    if(typeof(series)!='object') continue;
                    colors.push(colors[0]);color=colors.shift();
                    vals=[];
                    if (horizonal)  for (i in series[serie]) {
                        if (!a[i]) a[i]=0;
                        rect=this.rect(Math.floor(a[i]*w/maxVal)+left,top+Math.round((stack?0:j)*barWidth/(stack?1:Object.keys(series).length))+(columnWidth-barWidth)/2+columnWidth*i,Math.ceil(series[serie][i]*w/maxVal),barWidth/(stack?1:Object.keys(series).length));
                        rect.attr({"stroke":"none","fill":color}).click(params.click);
                        rect.node.label=labels?labels[i]:i;rect.node.serie=serie;rect.node.value=Number(series[serie][i]);
                        if (stack||Object.keys(series).length===1) a[i]=a[i]+Number(series[serie][i]);
                    } 
                    else for (i in series[serie]) {
                        if (!a[i]) a[i]=0;
                        rect=this.rect(left+((stack?0:j)*barWidth)/(stack?1:Object.keys(series).length)+(columnWidth-barWidth)/2+columnWidth*i,top+h-Math.floor((series[serie][i]+a[i])*h/maxVal),barWidth/(stack?1:Object.keys(series).length),Math.ceil(series[serie][i]*h/maxVal));
                        rect.attr({"stroke":"none","fill":color}).click(params.click);
                        rect.node.label=labels?labels[i]:i;rect.node.serie=serie;rect.node.value=Number(series[serie][i]);
                        if (stack||Object.keys(series).length===1) a[i]=a[i]+Number(series[serie][i]);
                    }
                    j++;
                }
// draw labels 
                if(horizonal) for (i =0; i<labels.length; i++) {
                    textLeft=left-6;
                    textTop=Math.round(top+columnWidth*(0.5+i));
                    this.text(textLeft,textTop,(labels?labels[i]:i)).attr({font:defaultStyle.font,"text-anchor":"end"}).rotate(textAngleY,textLeft,textTop); // text of the labels (Y Axis)
                    this.setStart();
                    if (stack||Object.keys(series).length===1) if(params.showValues){
                        textLeft=left+2+a[i]*w/maxVal;
                        txt=this.text(textLeft,textTop,roundMillion(a[i],params.roundingStyle)).attr({font:defaultStyle.font,"text-anchor":"start"}); // text of the labels (Y Axis)
                    }
                    this.setFinish().translate(0,2);
                } else for (i =0; i<labels.length; i++) {                    
                    textLeft=Math.round(left+columnWidth*(0.5+i));    
                    textTop=top+h+10;
                    this.text(textLeft,textTop,(labels?labels[i]:i)).attr({font:defaultStyle.font,"text-anchor":(textAngleX!=0?"end":"middle")}).rotate(textAngleX,textLeft,textTop); // text of the labels (X Axis)
                    this.setStart();
                    if (stack||Object.keys(series).length===1) if(params.showValues){
                        textTop=top+h -a[i]*h/maxVal;
                        txt=this.text(textLeft,textTop,roundMillion(a[i],params.roundingStyle)).attr({font:defaultStyle.font,"text-anchor":(textAngleX!=0?"end":"middle")}); // text of the labels (X Axis)
                    }
                    this.setFinish().translate(0,-txt.getBBox().height/2);
                }
                
            };     