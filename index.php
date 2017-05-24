<!DOCTYPE html>
<html lang="EN-GB">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link href="css/fa/css/font-awesome.css" rel="stylesheet">
        <link href="css/base/jquery-ui.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <link href="css/un.css" rel="stylesheet">
        <link href="js/graphs.css" rel="stylesheet">
        <script src="js/jquery/jquery.js"></script>
        <script src="js/jquery/jquery-ui.js"></script>
        <script src="js/jquery/phpjs.md5.js"></script>
        <script src="js/dataset.js"></script>
        <script src="js/graphs.js"></script>
        <style> 
            body{min-width:1250px;padding:80px 0 40px 0;text-align: center; font-family:"Century Gothic",sans-serif;
                 /*background: #ddd;*/
                 color:#333} 
            .row::after {content: "";clear: both; display: table}
            [class*="col-"] {float: left;padding: 15px;}
            .col-1 {width: 8.33%;}
            .col-2 {width: 16.66%;}
            .col-3 {width: 25%;}
            .col-4 {width: 33.33%;}
            .col-5 {width: 41.66%;}
            .col-6 {width: 50%;}
            .col-7 {width: 58.33%;}
            .col-8 {width: 66.66%;}
            .col-9 {width: 75%;}
            .col-10 {width: 83.33%;}
            .col-11 {width: 91.66%;}
            .col-12 {width: 100%;}
            .modalContainer {position: absolute;left:0;right:0;top:120px;margin:auto;z-index:4;opacity: 0.7}
            .modalContainer:hover {opacity:1}
            span {line-height: 1;vertical-align: middle}
            .login-label    {text-align: right; display: inline-block;width:80px;padding:4px}
            .middle-place   {text-align: center}
            #frmLogin       {padding:12px 4px;display:none;color:black;background: #e6e6e6;border-radius: 4px; width:400px;margin:auto;z-index: 4}
/*            #frmLogin:hover {transition: 0.3s }*/
            #frmLogin input {margin: 4px;width:250px}
            .msg            {color: red}
            #pass,#un {padding: 8px;border: silver solid 1px;border-radius: 4px }
            .tabSections {list-style-type: none; background: gray;margin:auto; width:100%;position:fixed;top:46px}
            .tabSections li {float:left ;}
            .x1-5p {font-size: 1.5em}
            .tabSections a {text-decoration: none;color:white;display:inline-block;padding:0px 16px 4px 12px;border-top:transparent solid 4px;transition: 0.2s}
            .tabSections a:hover {background-color:darkgray;border-top: #0cf solid 4px}
            .tabSections a:active {border-color:silver }
            .ind-group {border-radius:6px;
                        margin:32px 12px 16px 16px;
                        width:400px;
                       background: #eee;
                       box-shadow: #888 2px 2px 2px 1px;
                       float:right;text-align: left}
            .title {border-radius: 4px 4px 0 0;line-height:2; background: #0bf;padding:6px;font-size: 24px;margin: 8px 4px 0 4px;color:white}
            .ind {padding:8px;right:0}
            /*.ind:not(:last-child){border:dashed white;border-width:0 0 1px}*/
            .dbgrid {color:white}
            .tabActive {background: #666}
            .geo-plot {float:left;overflow: auto;border-radius:6px;overflow:hidden;
                      /*margin:8px 8px 24px 8px;*/
            }
            .geo-point {position: absolute;opacity:0.5;border-radius: 50%;background: dodgerblue;width:6px;height:6px;z-index: 2}
            .geo-point:hover {border:solid white 1px;opacity:1}
            .geo-tooltip{opacity:1;background: lightyellow;border:solid yellow 1px;color:black;text-align: left}
            
        </style>
    <title>UNICEF Humanitarian Decision Support System</title>
    </head>
    <body><?php include_once './conn.php';include_once './dataset.php';
include_once './designindicator.php';$_SESSION['redirecting']=false;?>
        <header>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/>
            <button id="btnLogin" class="logout-button logout-form" onclick="btnLoginClick(event);">Login</button>
        </header>
<div id="wait"><div class="loader"></div></div>
        
        <div class="modalContainer"><div id="frmLogin">
                <div class="msg"></div>
                    <form autocomplete="off" name="login" action="conn.php" method="POST" onsubmit="return DoSubmit();">
                        <div><div class="login-label">User </div><input type="text" name="un" id="un"/></div>
                        <div><div class="login-label">Password </div><input type="password" name="pass" id="pass"/></div>
                            <input type="hidden" name="ps" id="ps">
                            <div><input style="width:60px" class="btnNormal" type="submit" value="Sign In"/> &nbsp; &nbsp;<input style="width:60px" type="reset" class="btnNormal" onclick="$('#frmLogin').fadeOut('fast');return false" value="Cancel"></div>
                    </form>
            </div>
        </div>
        <ul class="tabSections">
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-family"></span>All</a></li>
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-health"></span>Health</a></li>
            <li><a onclick="tabClick(event)" class="tabActive" href="#"><span class="un fa-fw fa-2x un-nutrition"></span>Nutrition</a></li>
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-education"></span>Education</a></li>
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-protection"></span>Child Protection</a></li>
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-water-tap"></span>WASH</a></li>
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-box"></span>NFI</a></li>
            <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-family-protect"></span>ERL</a></li>
        </ul>
<div id="panSection"><div class="geo-plot"></div><div class="ind-group"></div></div>
<footer>Humanitarian Decision Support System &COPY; UNICEF </footer>        
        <script>
            function countryToColor(country){
                switch (country) {
                    case 'Amman':
                        return 'limegreen';
                        break;
                    case 'Gaziantep':
                        return 'tomato';
                        break;
                       
                    default: return 'dodgerblue'; 
                }
            }
            function tabClick(e){
                d=Date.now();
                console.log('fetching','00:00');
                    $('.tabSections a').removeClass('tabActive');
                    a=(e.target.tagName=='A'?$(e.target).addClass('tabActive'):$(e.target).parent().addClass('tabActive'));
                    icon=(e.target.tagName=='SPAN'?$(e.target):$(e.target).find('span'))
                    $('#wait').fadeIn('fast');
                    $.ajax({url:'fetchdata.php',data:{section:a.text()},success:function(data,status){
                 console.log('got data',Date.now()-d);
                 $('.ind-group').html(data);
                       if (a.text().search(/all/i)==-1) $('<span>').addClass(icon[0].className).css({float:'right'}).appendTo('.ind-group .title').parent().css('overflow','auto');
                       params=$('#params').text();
                       if (!params) return ;else geoPoint=JSON.parse(params);
                       map=$('.geo-plot');
                       map.find('.geo-point').remove();
                console.log('rendering',Date.now()-d);
                       geoPoint.forEach(function(e,i){
                         p=geoToMap(e.long,e.lat);
                         $('<span>').addClass('geo-point').appendTo(map).css({background:countryToColor(e.CountryId),left:p.x+map.position().left,top:p.y+map.position().top})
                                 //.attr({title:'Activities: '+e.Activities+"\nLocation: "+e.Location})
                                         .attr('title',' ').tooltip({
                                     content:"<b>Location:</b> "+e.Location+'<br><br><hr><b>Activities:</b> <br>'+e.Activities,
                                     classes:{"ui-tooltip": "geo-tooltip"/*"ui-state-highlight"*/}
                                });
                       });
                       console.log('All set',Date.now()-d);
                       $('#wait').fadeOut('fast');
                       //console.log(geoPoint);
                       initgraphs();
                    }});
            }
            function btnLoginClick(e){
                $('#frmLogin').fadeToggle('fast');
            }
            function DoSubmit(){
               document.forms['login']['ps'].value=md5(document.forms['login']['pass'].value+document.forms['login']['un'].value.toLowerCase());
               delete(document.forms['login']['pass'].value);
               return true;
           };
           geoRef={topRight:{x:42.2345,y:37.3157},bottomLeft:{x:35.6346,y:32.68643}}
           mapRef={topRight:{x:752,y:23},bottomLeft:{x:17,y:538}}
           geoWidth=geoRef.topRight.x-geoRef.bottomLeft.x;
           geoHeight=geoRef.topRight.y-geoRef.bottomLeft.y;
           mapWidth=mapRef.topRight.x-mapRef.bottomLeft.x;
           mapHeight=mapRef.bottomLeft.y-mapRef.topRight.y;
           function geoToMap(long,lat){
               return ({x:mapRef.bottomLeft.x+((long-geoRef.bottomLeft.x)*mapWidth/geoWidth),
                   y:mapRef.bottomLeft.y-((lat-geoRef.bottomLeft.y)*mapHeight/geoHeight)});
           }
           function mapToGeo(x,y){
               return ({
                   x:geoRef.bottomLeft.x+(x-mapRef.bottomLeft.x)*(geoWidth/mapWidth),
                   y:geoRef.bottomLeft.y+ (mapRef.bottomLeft.y-y)*geoHeight/mapHeight});
           }
           
           $(document).ready(function(){
               //$('#wait').fadeOut('fast');
               $('.geo-plot').load('./img/syr-district.svg',function(data,status){
                   svgMap=$('.geo-plot svg').attr({width:800,height:600})[0];
                   svgMap.setAttribute('viewBox',"330 40 300 600");
                   $(svgMap).find('path').attr({stroke:'white',fill:'silver','stroke-width':1});
                   //$(svgMap).before('<span style"background:dodgerblue">&nbsp;&nbsp;&nbsp;</span><span> Damascus &nbsp;&nbsp;</span><span style"background:limegreen">&nbsp;&nbsp;&nbsp;</span><span> Amman</span><span style"background:tomato">&nbsp;&nbsp;&nbsp;</span><span> Gaziantep</span>');
//                   $(map).mousedown(function(e){
//                      tt=mapToGeo(e.offsetX,e.offsetY);
//                      console.log(geoToMap(tt.x,tt.y));
//                   });
               });
//               $.ajaxStart(function(){});
//               $.ajaxStart(function(){});
               
               $('.tabActive').trigger('click');
               
        <?php 
          if (session_status()!=PHP_SESSION_ACTIVE) session_start();
          if (isset($_SESSION['msg'])) {
              echo '$(".msg").text("'.$_SESSION['msg'].'");';
              echo "$('#frmLogin').show();";
              
              if ($_SESSION['msg']=='session expired!')
              {
                  session_unset();
                  session_destroy();
              }
              unset($_SESSION['msg']);
          }

        ?>
            });
        </script>
</body>
</html>
