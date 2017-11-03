<!DOCTYPE html>
<html lang="EN-GB">
    <head><?php
    include_once './conn.php';
    //echo '<p>Session status: '. session_status().'</p>';
    include_once './dataset.php';
    include_once './designindicator.php';$_SESSION['redirecting']=false;
    ?>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=11">
        <link href="css/fa/css/font-awesome.css" rel="stylesheet">
        <link href="js/jquery/jquery-ui.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <link href="css/un.css" rel="stylesheet">
        <link href="js/graphs.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
        <script src="js/jquery/jquery.js"></script>
        <script src="js/jquery/jquery-ui.js"></script>
        <script src="js/jquery/raphael.min.js"></script>
        <script src="js/jquery/phpjs.md5.js"></script>
        <script src="js/dataset.js"></script>
        <script src="js/main.js"></script>
        <script src="js/clipboard.js"></script>
        <script src="js/jquery/graph.js"></script>
        <style> 
            body{min-width:1250px;padding:80px 0 40px 0;text-align: center; font-family:"Century Gothic",sans-serif;
                 /*background: #ddd;*/
                 color:#333} 
            .row::after {content: "";clear: both; display: table}
            .box {
                display:inline-block;
                width:130px;
                color:white;
                background:#007fff;
                padding:4px;margin:8px}
            .box-round {border-radius: 8px}
            .box-caption {padding:4px}
            .sidenav {width:0}
            #mainpage {margin-left: 0}
            .box-value {font-weight: bold;color:yellow;font-size:14px}
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
            #frmLogin       {
                padding:12px 4px;
                display:none;
                color:black;
                background: #e6e6e6;
                border-radius: 4px;
                width:400px;
                margin:auto;z-index: 4;
                }
/*            #frmLogin:hover {transition: 0.3s }*/
            #frmLogin input {margin: 4px;width:250px}
            .msg            {color: red}
            #pass,#un {padding: 8px;border: silver solid 1px;border-radius: 4px }
            .tabSections {list-style-type: none; background: gray;margin:auto; width:100%;position:fixed;top:46px;z-index: 1}
            .tabSections li {float:left ;}
            #panSection {overflow: auto}
            .x1-5p {font-size: 1.5em}
            .tabSections a {text-decoration: none;color:white;display:inline-block;padding:0px 16px 4px 12px;border-top:transparent solid 4px;transition: 0.2s}
            .tabSections a:hover {background-color:darkgray;border-top: #0cf solid 4px}
            .tabSections a:active {border-color:silver }
            .ind-group {border-radius:6px;
                        margin:32px 12px 16px 16px;
                        width:400px;
                       background: #eee;
                       float:right;text-align: left}
            .title {border-radius: 4px 4px 0 0;line-height:2; background: #0bf;padding:6px;font-size: 24px;margin: 8px 4px 0 4px;color:white}
            .small {font-size: 16px;line-height: 1}
            .xsmall {font-size: 14px;line-height: 1}
            .ind {padding:8px;right:0}
            /*.ind:not(:last-child){border:dashed white;border-width:0 0 1px}*/
            .dbgrid {color:white}
            .tabActive {background: #666}
            .geo-plot {
                float:left;overflow: auto;border-radius:6px;overflow:hidden;width:800px;
                /*height: 600px;*/
            }
            .geo-plot circle:hover {stroke:white;stroke-width:4px;opacity:1}
            .line-through {text-decoration: line-through}
            .geo-legend {cursor:default;user-select:none;position:absolute;left:700px;top:600px;width:100px;text-align: left;padding:8px}
            .geo-legend span:hover {font-weight: bold}
            .color-amman {background: limegreen!important}
            .color-gaziantep {background: tomato!important}
            .geo-point2 {display:inline-block;opacity:0.5;border-radius: 50%;background: dodgerblue;width:6px;height:6px}
            .geo-point2:hover {opacity:1}
            .geo-point {position: absolute;opacity:0.5;border-radius: 50%;background: dodgerblue;width:6px;height:6px}
            .geo-tooltip{opacity:1;background: lightyellow;border:solid yellow 1px;color:black;text-align: left}
            .btn-more {position:absolute;background: whitesmoke;width:28px;height:28px;transition: 1s}
            .btn-more:hover {background: lightgray;cursor: default}
            .btn-more:hover .btn-more-menu {display:block;transition:1s}
            .btn-more-menu {text-align: left;display:none;position:absolute;background: whitesmoke;margin-top:8px}
            .btn-more-action {border-top:solid 2px white;display:inline-block;width:120px;padding:8px;background: whitesmoke;cursor: default;text-decoration:none}
            .btn-more-action:hover {background: dodgerblue;color:white}
        </style>
    <title>UNICEF Humanitarian Decision Support System</title>
    </head>
    <body>
        <div id="mainpage"><header>

            <?php if ($un=$_SESSION['username']) {
                echo '<div id="sidenavshow" onclick="openNav()">&#9776; </div>';
                echo '<div class="logout-form"><span class="fa fa-fw fa-2x fa-user-circle"></span> <span class="user-label">';
                echo htmlentities($fullname);
                echo '</span><div class="user-menu"><a ref="#" onclick="changePassword()">Change Password</a><a ref="#" onclick="logout()">Logout</a></div></div> ';
            } 
            else {
                echo '<button id="btnLogin" class="logout-button logout-form" onclick="btnLoginClick(event);">Login</button>';
                
            }?>
           <img class="hlogo" src="./img/UNICEF_logo_white.png"/>
        </header>
        <div w3-include-html="sidenav.php"></div>

        
<div id="frmLogin" class="modalContainer">
        <div class="msg"></div>
            <form autocomplete="off" name="login" action="conn.php" method="POST" onsubmit="return DoSubmit();">
                <div><div class="login-label">User </div><input type="text" name="un" id="un"/></div>
                <div><div class="login-label">Password </div><input type="password" name="pass" id="pass"/></div>
                    <input type="hidden" name="ps" id="ps">
                    <div><input style="width:60px" class="btnNormal" type="submit" value="Sign In"/> &nbsp; &nbsp;<input style="width:60px" type="reset" class="btnNormal" onclick="$('#frmLogin').fadeOut('fast');return false" value="Cancel"></div>
            </form>
    </div>
</div>
        <!--<div class="page-container">-->
<ul class="tabSections">
    <li><a onclick="tabClick(event)" class="tabActive" href="#"><span class="un fa-fw fa-2x un-family"></span>All</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-health"></span>Health</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-nutrition"></span>Nutrition</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-education"></span>Education</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-protection"></span>Child Protection</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-water-tap"></span>WASH</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-box"></span>NFI</a></li>
    <li><a onclick="tabClick(event)" href="#"><span class="un fa-fw fa-2x un-family-protect"></span>ERL</a></li>
</ul>
<div id="panSection">
    <div class="geo-legend shadow">
        <div title="Toggle Visibility" onclick="toggleGeoPoint(event)"><div class="geo-point2 color-amman"></div> <span>Amman</span></div>
        <div title="Toggle Visibility" onclick="toggleGeoPoint(event)"><div class="geo-point2 color-damascus"></div> <span>Damascus</span></div>
        <div title="Toggle Visibility" onclick="toggleGeoPoint(event)"><div class="geo-point2 color-gaziantep"></div> <span>Gaziantep</span></div>
    </div>
    <div class="ind-group shadow"></div>
    <div class="geo-plot col-6">
        <div class="btn-more">...
            <div style="" class="btn-more-menu">
                <a class="btn-more-action" onclick="moreClick(event)">Copy map to clipboard</a>
                <a class="btn-more-action" onclick="downloadMap(event)">Download Map</a>
            </div>
        </div>
    </div>
     <div class="col-3 x1-5p" style="font-size:12px">
     <div class="box" data-class="un-water-tap" data-caption="WASH Estimated People that have been Reached" value="18029023">
     </div>
     <div class="box" data-class="un-health" data-caption="Health People reached<br><br>&nbsp;&nbsp;" value="10029023">
     </div>
    
</div></div>
         
    <!--</div>-->
<footer>Humanitarian Decision Support System &COPY; UNICEF </footer>        
        <script>
            
            function toggleGeoPoint(e){
                if ($(e.target).hasClass('geo-point2')){
                    $('g#'+e.target.classList[1]).fadeToggle();
                    $(e.target).next().toggleClass('line-through');
                } else if(e.target.tagName=='SPAN') { 
//                    console.log($('g#'+e.target.previousElementSibling.classList[1]));
                    $('g#'+e.target.previousElementSibling.classList[1]).fadeToggle();
                    $(e.target).toggleClass('line-through');
                }
            }
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
    function downloadMap(e){
        var svgData = $('.geo-plot svg')[0].outerHTML;
        var svgBlob = new Blob([svgData], {type:"image/svg+xml;charset=utf-8"});
        var svgUrl = URL.createObjectURL(svgBlob);
        var downloadLink = document.createElement("a");
        downloadLink.href = svgUrl;
        downloadLink.download = "Map.svg";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }        
    function moreClick(e){
                clipboard.copy({
                    'text/plain':$('.geo-plot svg')[0].outerHTML
                }).then(
                  function(){console.log("success");},
                  function(err){console.log("failure", err);}
                );
//                if(document.queryCommandSupported('copy')){ 
//                    var areaCopyPaste = document.querySelector('#areaCopyPaste');
//                    console.log(areaCopyPaste);
//                    svgText=$('.geo-plot svg')[0].outerHTML;
//                    areaCopyPaste.value=svgText;
//                //    areaCopyPaste.selectionEnd=0;
//                    areaCopyPaste.select();
//                  //  console.log(areaCopyPaste.selectionEnd-areaCopyPaste.selectionStart);
//                   // while (areaCopyPaste.selectionEnd-areaCopyPaste.selectionStart<2) console.log('.');
//                    try {
//                        var successful = document.execCommand('copy');
//                        var msg = successful ? 'successful' : 'unsuccessful';
//                        console.log('Copying text command was ' + msg);
//                    } catch (err) {
//                        console.log('Oops, unable to copy');
//                    }
//                }
//                else console.log('copy command is not supported!.')
            }
            function tabClick(e){
                var allSet=0,doneStep=4;
                d=Date.now();
                console.log('fetching','00:00');
                $('.tabSections a').removeClass('tabActive');
                var a=(e.target.tagName=='A'?$(e.target).addClass('tabActive'):$(e.target).parent().addClass('tabActive'));
                icon=(e.target.tagName=='SPAN'?$(e.target):$(e.target).find('span'))
                $('#panSection').holdOn();
                $('.col-3').empty();
                $.get('./fetchdata.php',{act:'draw',section:a.text()},function(data,status){
                    try {rec=JSON.parse(data);} catch(er){ $('#panSection').holdOn('destroy');return}
//                    if($('#panSection').holdOn('instance')) $('#panSection').holdOn('destroy');
//                    $('.geo-plot img').remove();
//                    g=$('<img>').prop('src','data:image/svg+xml;base64,'+base64(rec.svg));
//                    $('.geo-plot').append(g);
                    $('.geo-plot svg').remove();
                    $('.geo-plot').append(rec.svg);
                    allSet++;
                    if (allSet===doneStep) { 
                        pageRendered();
                    }
                   // $('.geo-plot circle[r]').tooltip('close');
                    $('.geo-plot circle[r]').attr('title','Click here to view details on this location').click(function(e){
                        if ($(e.target).tooltip('instance')===undefined) 
                            $(e.target).tooltip({classes:{'ui-tooltip':'geo-tooltip shadow'},content:'<div style="text-align:center;min-width:200px" class="fa fa-spinner fa-pulse"></div>'}).tooltip('open');
                            $.get('./fetchdata.php',{act:'location',pcode:this.id,section:a.text()},function(data,status){
                                rec=JSON.parse(data);
                                console.log('circle clicked',rec);
                                $(e.target).tooltip({content:'<b>Location :'+rec.location+'</b><hr/><b>Activities:</b><div>'+rec.activities.join('</div><div style="border-top:dashed 1px gray">')+'</div>', classes:{'ui-tooltip':'shadow geo-tooltip'}});
                            });
                    });
//                            console.log(rec.msg);
                   //svgMap.find('path').attr({stroke:'white',fill:'#ddd','stroke-width':1});
                });

                $.ajax({url:'fetchdata.php',data:{act:'hpm',section:a.text()},success:function(data,status){
//                   console.log('got hpm data size(byte):',data.length.toLocaleString(),'/tooks (seconds):',Date.now()-d);
                   //$('#panSection').holdOn('destroy');
//                   console.log(data);
                   $('.ind-group').html(data);
                   if (a.text().search(/all/i)==-1) $('<span>').addClass(icon[0].className).css({float:'right'}).appendTo('.ind-group .title').parent().css('overflow','auto');
//                           map=$('.geo-plot');
                   allSet++;
                   if (allSet===doneStep) {
                        pageRendered();
                    }
               }});
               $.get('./fetchdata.php',{act:'overall',section:a.text()},function(data,success){
                    rec=JSON.parse(data);
                    $('.col-3').prepend(rec.html);
                    allSet++;
                    if (allSet===doneStep) {
                        pageRendered(); 
                    }
               });
               $.get('./fetchdata.php',{act:'bygov',section:a.text()},function(data,success){
//                   console.log('Governorate Graph (byte)',data.length.toLocaleString(),'/tooks (seconds):',Date.now()-d);
                   rec=JSON.parse(data);
                   bygov=$('<div style="margin:8px 0 4px 0" id="bygov" class="bygov">');
                   $('.col-3').append(bygov);
                   countryIds=Object.keys(rec.reach);colors=[];
                   for (countryId=0;countryId<countryIds.length;countryId++) { 
                       colors.push(countryToColor(countryIds[countryId]));
                   }
                   if (colors.length>0) Raphael(document.getElementById('bygov'),400,400).bars({
                       roundingStyle:{kilos:0,millionText:' m',decimals:1}
                       ,stack:1
//                       ,minTicks:4
                       ,horizonal:1
                       ,data:rec.reach
                       ,labels:rec.govs
                       ,left:100,right:48
                       ,showValues:1
                       ,colors:colors
                   });
                   
                   allSet++;
                   if (allSet===doneStep) {
                       pageRendered();
                   } 
                  
               });
            }
            function btnLoginClick(e){
                $('#frmLogin').fadeToggle('fast');
            }
            function DoSubmit(){
               document.forms['login']['ps'].value=md5(document.forms['login']['pass'].value+document.forms['login']['un'].value.toLowerCase());
               delete(document.forms['login']['pass'].value);
               return true;
           };
           function pageRendered(){
                $('.bar').bar({pointer:1
                ,decimals:2}
                        );
                $('.box').box();
                $('#panSection').holdOn('destroy');
                console.log('done.');
           }
//           geoRef={topRight:{x:42.2345,y:37.3157},bottomLeft:{x:35.6346,y:32.68643}}
//           mapRef={topRight:{x:752,y:23},bottomLeft:{x:17,y:538}}
//           geoWidth=geoRef.topRight.x-geoRef.bottomLeft.x;
//           geoHeight=geoRef.topRight.y-geoRef.bottomLeft.y;
//           mapWidth=mapRef.topRight.x-mapRef.bottomLeft.x;
//           mapHeight=mapRef.bottomLeft.y-mapRef.topRight.y;
//           function geoToMap(long,lat){
//               return ({x:mapRef.bottomLeft.x+((long-geoRef.bottomLeft.x)*mapWidth/geoWidth),
//                   y:mapRef.bottomLeft.y-((lat-geoRef.bottomLeft.y)*mapHeight/geoHeight)});
//           }
//           function mapToGeo(x,y){
//               return ({
//                   x:geoRef.bottomLeft.x+(x-mapRef.bottomLeft.x)*(geoWidth/mapWidth),
//                   y:geoRef.bottomLeft.y+ (mapRef.bottomLeft.y-y)*geoHeight/mapHeight});
//           }
         
           $(document).ready(function(){
               //$('#wait').fadeOut('fast')
                console.log('loaded');
                sidenav=$('div[w3-include-html]');
                $.get(sidenav.attr('w3-include-html'),function(data,statu){
                    sidenav.html(data);
                });

               $('.tabActive').trigger('click');
//               if ($('#panSection').holdOn('instance')) $('#panSection').holdOn('destroy');
               
        <?php 
          //if (session_status()!=PHP_SESSION_ACTIVE) session_start();
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
