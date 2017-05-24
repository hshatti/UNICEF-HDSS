/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
  var angleInRadians = (angleInDegrees-90) * Math.PI / 180.0;
  return {
    x: centerX + (radius * Math.cos(angleInRadians)),
    y: centerY + (radius * Math.sin(angleInRadians))
  };
}

function describeArc(x, y, radius, startAngle, endAngle){
    var start = polarToCartesian(x, y, radius, endAngle);
    var end = polarToCartesian(x, y, radius, startAngle);
    var largeArcFlag = endAngle - startAngle <= 180 ? "0" : "1";
    var d = [
        "M", start.x, start.y, 
        "A", radius, radius, 0, largeArcFlag, 0, end.x, end.y
    ].join(" ");
    return d;       
}


function initgraphs(){
uriNS="http://www.w3.org/2000/svg";
donuts=document.querySelectorAll('div.donut[percent]');

for (i=0;i<donuts.length;i++){
    var donut=donuts[i];
    svg=document.createElementNS(uriNS,"svg");
    g=document.createElementNS(uriNS,'g');
    arc=document.createElementNS(uriNS,'path');
    circle=document.createElementNS(uriNS,'path');
    text=document.createElementNS(uriNS,'text');
    v=donut.getAttribute('percent');
    caption=donut.getAttribute('text');
    if (caption==null) text.innerHTML=v+'<tspan>%</tspan>';else text.innerHTML=caption;
    donut.appendChild(svg);w=svg.clientWidth;h=svg.clientHeight;
    text.setAttribute('x',w/2);
    text.setAttribute('y',h/2);
    angle=v*360/100;r=Math.min(w,h);r*=0.8;
    transition=1000; 
    frames=75; 
    stepms=transition/frames;   
    step=angle/frames;     
    an=0;
    console.log(step);
    arc.setAttribute('class','arc');
    circle.setAttribute('class','circle');
    g.appendChild(arc);g.appendChild(circle);svg.appendChild(g);
    svg.appendChild(text);
    function drawArc(step,w,h,r){
      an=Math.min(an+step,angle);
      va=Math.floor(v*an/angle);
      if (caption==null) text.innerHTML=va+'<tspan>%</tspan>'; 
      arc.setAttribute('d',   describeArc(w/2,h/2,r/2,0   ,an)); 
      circle.setAttribute('d',describeArc(w/2,h/2,r/2,an,360));
//      console.log(step,w,h,r);
      if (an<angle)
          setTimeout(drawArc,stepms,step,w,h,r);
    }
    drawArc(step,w,h,r);

//    console.log(donut.outerHTML,angle);
}

  gauges=document.querySelectorAll('.bar');
  //gauges.forEach(function(item)
    for(j=0;j<gauges.length;j++)    
    {
        bars=[];
        var item=gauges[j];
        barValues=(item.getAttribute('data-value')).split(' ');
        barTotal=0;barLeft=0;numTotal=0;
        
        m=Number(item.getAttribute('data-max').replace(',',''));
        div=document.createElement('div');
        div.className='stack';
        item.appendChild(div);
        l=document.createElement('span');
        r=document.createElement('span');
        div.appendChild(l);
        div.appendChild(r);
        pointer=document.createElement('div');
        console.log(pointer);
//        barValues.forEach(function(b,i)
        for (i=0;i<barValues.length;i++)
        {
            b=barValues[i];
            bars.push(document.createElement('div'));
            bars[i].className='pos';
            div.appendChild(bars[i]);
            barWidth=Number(b.replace(',',''))*100/m;
            bars[i].style.width='0px';
            bars[i].style.left='0px';
            bars[i].style.width=(Number(b.replace(',',''))*100/m)+'%';
            //console.log('b:',Number(b.replace(',',''))*100/m+'%',' /m:',m);
            bars[i].style.left=barLeft+'%';
            barLeft=barLeft+barWidth; 
            barTotal=barTotal+barWidth;
            numTotal=numTotal+Number(b.replace(',',''));
        }
        //);
        l.className='num l';
        r.className='num r';
        div.appendChild(pointer);
        pointer.className='pointer';pointer.style.left='0px';
        
        r.textContent=(m>999999?((m/100000).toFixed()/10).toLocaleString()+' m':m.toLocaleString());
        l.textContent=(numTotal>999999?((numTotal/100000).toFixed()/10).toLocaleString()+' m':numTotal.toLocaleString());
        //setTimeout(function(){
           //clearTimeout(timer);
           pointer.style.left=barTotal+'%';
        //},100);
    }
   // ); 
}
