<?php
include_once './conn.php';
include_once './geoPHP/geoPHP.inc';
//if ($_SESSION['username']=='') {
//    header('Location: index.php');
//    exit;
//}
//$countM=0;
$colortags=['#0000ff','#008080','#00ff00','#808000','#ff0000','#800080'];

function int_crop($int,$min,$max){
    return min([max([$int,$min]),$max]);
}

function colorBright($color,$adj){
  $r=intval('0x'.substr($color,1,2));
  $g=intval('0x'.substr($color,3,2));
  $b=intval('0x'.substr($color,5,2));
  $r1=255-$r;
  $g1=255-$g;
  $b1=255-$b;
  $r1*=1-$adj;
  $g1*=1-$adj;
  $b1*=1-$adj;  
  $r1=int_crop($r1, 0, 255);
  $g1=int_crop($g1, 0, 255);
  $b1=int_crop($b1, 0, 255);
  return sprintf('#%02x%02x%02x',255-$r1,255-$g1,255-$b1);
}
function doPoint($p,$BBox,$scale){
  return [round($scale*($p[0]-$BBox['minx']),0),round($scale*($BBox['maxy']-$p[1]),0)];  
}

function doPoly($p,$BBox,$scale){
    global $countM;
    if (is_array($p))
        if (is_array($p[0])){
          $countM++;
          foreach ($p as $i=>$v) 
                  $r.=($countM===1?'M':($i==0?'':'L')).doPoly($v,$BBox,$scale).($countM===1?'Z':'');
          $countM--;
          return $r;
        }
        else {
            return round($scale*($p[0]-$BBox['minx']),0).','.round($scale*($BBox['maxy']-$p[1]),0);
        };
}
function drawLayer($polygons,$scale,&$BBox){
    $BBox=['minx'=>180,'miny'=>180,'maxx'=>-180,'maxy'=>-180];
    try {  foreach ($polygons as $k=>$polygon){
            if(is_int($k)&&$polygon[0]!==null) {
                $geo=geoPHP::load($polygon[0]);
                $geos[($polygon['id']?$polygon['id']:$k)]=$geo;
                $BBox['minx']=min($geo->getBBox()['minx']-0.1,$BBox['minx']);
                $BBox['miny']=min($geo->getBBox()['miny']-0.1,$BBox['miny']);
                $BBox['maxx']=max($geo->getBBox()['maxx']+0.1,$BBox['maxx']);
                $BBox['maxy']=max($geo->getBBox()['maxy']+0.1,$BBox['maxy']);
            }
        }
    } catch (Exception $e){
        echo $e->getTraceAsString();
    }
    $attr='';
    foreach ($polygons as $k=>$v){if (!is_int($k)) $attr.=sprintf(' %s="%s"',$k,$v);}
    $r.=sprintf('<g%s>',$attr);
    foreach($geos as $pcode=>$geo){
        $r.= '<path id="'.$pcode.'" d="';
        $r.=doPoly($geo->asArray(),$BBox,$scale);
        $r.='"/>';
    }
    $r.='</g>';
    return $r;
    
}

function polarToCartesian($centerX, $centerY, $radius, $angleInDegrees) {
  $angleInRadians = ($angleInDegrees-90) * pi() / 180.0;
  return [
    'x'=> $centerX + ($radius * cos($angleInRadians)),
    'y'=> $centerY + ($radius * sin($angleInRadians))
  ];
}

function describeArc($x, $y, $radius, $startAngle, $endAngle){
    $start = polarToCartesian($x, $y, $radius, $endAngle);
    $end = polarToCartesian($x, $y, $radius, $startAngle);
    $largeArcFlag = $endAngle - $startAngle <= 180 ? "0" : "1";
    $d = "M".join(',',[$start['x'], $start['y']])."A".join(',',[$radius, $radius, 0, $largeArcFlag, 0, $end['x'], $end['y']]);
    return $d;       
}


function drawGraph($type,$data,$style,$scale,$BBox,$layerName=null){ //$element is associative array containing [pcode=>'c1001' ,values=>[10]]  , $options is associative array=['stroke'=>['white'],'stroke-width'=1,'fill'=['whitesmoke']]
  $r=$layerName===null?'<g':sprintf('<g id="%s" ',$layerName);
  if (isset($style)) foreach ($style as $n=>$v) $r.=sprintf(' %s="%s"',$n,$v);
  $r.='>';
  switch (strtolower($type)) {
      case 'dot': case 'circle':{
          foreach($data as $point) {
              $attr='';
              $pos=doPoint($point,$BBox,$scale);
              $values= array_splice($point, 2);
              foreach ($values as $k=>$v) $attr.=sprintf(' %s="%s"',$k,$v);
              $r.=sprintf('<circle cx="%s" cy="%s"%s/>',$pos[0],$pos[1],$attr);
          }
          break;
      }
      case 'pie':{
          foreach($data as $point) {
              $pos=doPoint($point,$BBox,$scale);
              $values= array_splice($point,2);
              $total=array_sum($values);
              $r.=sprintf('<path d="%s"/>', describeArc($pos[0], $pos[1], sqrt($total),0, 359.9999));
          }
          break;
      }
      case 'text':{
         foreach($data as $point) {
            $attr='';
            $pos=doPoint($point,$BBox,$scale);
            $values= array_splice($point,2);
            foreach ($values as $k=>$v) if (strcasecmp('text', $k)===0) $text=$v;else $attr.=sprintf(' %s="%s"',$k,$v);
            $r.=sprintf('<text x="%s" y="%s"%s>%s</text>',$pos[0],$pos[1],$attr,$text);
          }
        break;  
      }

      default:
          break;
  }
  return $r.'</g>';  
}

function drawMap($polygroup,$scale,$options=null)
{   
    
    $r='<!-- Generator: HDSS SVG Engine v0.8 ; Credit: Haitham Shatti <hshatti@unicef.org>;<haitham.shatti@gmail.com>  --><!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
    foreach($polygroup as $polygons) 
        $layer.= drawLayer($polygons, $scale,$BBox);
    if (isset($options['width'])) $w=$options['width'];else $w=800;
    if (isset($options['height'])) $h=$options['height'];else $h=600;
    $r.=sprintf('<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="%d %d %d %d" xml:space="preserve" width="%d" height="%d">',0,0,round($scale*($BBox['maxx']-$BBox['minx'])),round($scale*($BBox['maxy']-$BBox['miny'])),$w,$h);
    $r.=$layer;
    if ($options!==null) {
        foreach ($options as $key=>$option){
            if ($key==='title')
                $r.=sprintf('<text fill="gray" id="title" x="%d" y="50" font-size="42px" font-family="sans-serif" text-anchor="middle">%s</text>',round($scale*($BBox['maxx']-$BBox['minx'])/2),$options['title']);
            else
                $r.=drawGraph($option['type'],$option['data'],$option['style'],$scale,$BBox,$option['name']);
        }
    }
    //$r.=sprintf('<rect fill="none" stroke="silver" stroke-width="1" x="%d" y="%d" width="%d" height="%d"/>',0,0,$scale*($BBox['maxx']-$BBox['minx']),$scale*($BBox['maxy']-$BBox['miny']));
    $r.= '</svg>';
    return($r);
}
?>


