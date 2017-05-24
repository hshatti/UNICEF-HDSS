<!DOCTYPE html><html>

<head>  
        <title>4Ws Decision Support</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link href="js/jquery/jquery-ui.min.css" rel="stylesheet">
        <link href="css/dataset.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet">
        <!-- <link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">-->
        
        <style> 
            .sidenav {width:0}
            #mainpage {margin-left: 0}
            .dbgrid td a {text-decoration: none;color:darkblue}
        </style>
        <script src="js/jquery/jquery.js"></script>
        <script src="js/jquery/jquery-ui.js"></script>
        <script src="js/main.js"></script>
        <script src="js/jquery/raphael-min.js"></script>
        <script src="js/jquery/graph.js"></script>
        <script src="js/dataset.js"></script>
        <script src="/js/cm/codemirror.js"></script>
<?PHP 
include_once './conn.php';
include_once './dataset.php';
include_once './rsgrid.php';
include_once './definitions.php';
if ($_SESSION['username']=='') {
    header('Location: index.php');
    exit;
}
?>  
        
</head>
    <body><div id="wait"><div class="loader"></div></div><div id="mainpage">
        <header>
            <div id="sidenavshow" onclick="openNav()">&#9776; </div>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/>
            <form action="conn.php" method="POST" class="logout-form">
                <input type="hidden" name="logout" value="1"/>
                Welcome <span class="user-label"><?php echo htmlentities($fullname);?></span> <input type="submit" class="logout-button" value="Logout" />
            </form></header>
            <div w3-include-html="sidenav.php"></div>

        <div class="page-container">
            <div class="left-menu">
            <?php
              $un=$_SESSION['username'];
              $rep=  $_GET['rep'];
              $par=  $_GET['par'];
              if (!isset($dsReps)) 
                  $dsReps= new dataset($dblink,'SELECT DISTINCT r.RepName, r.SQLText,r.conf,r.Def FROM genrep r 
JOIN fwreportsector rs ON rs.YOB=r.YOB and rs.reportname=r.RepName 
JOIN fwusersector us ON (us.sectorId=rs.sectorid AND us.username=\''.$un.'\') or (rs.sectorid=0) or ('.$_SESSION['authflag'].'=31) where r.YOB in (0,'.$YOB.') and coalesce(rs.CountryId,\'\') in ('.QuotedStr($_SESSION['CountryId']).',\'\')');
              $dsReps->Open();
              while (!$dsReps->EOF()){
                  if(isset($rep))
                  {
                      if ($dsReps->Values['RepName']==$rep)
                          echo '<h3 class="def">';
                      else echo "<h3>";
                  }
                  elseif (($dsReps->Values['Def']==='T')||($dsReps->RecNo===$dsReps->RecordCount-1))
                  {
                      if (!$def) echo '<h3 class="def">';
                      else echo "<h3>";
                      $def=true;
                  
                  }
                  else echo "<h3>";
                  echo $dsReps->Values['RepName'];
                  echo '</h3>';
                  echo '<div class="rep" style="text-align: right">';
                  if (!isset($par)||($dsReps->Values['RepName']!=$rep)) 
                      ParseParamForm($dblink,$dsReps->Values['SQLText']);
                  else 
                      ParseParamForm($dblink,$dsReps->Values['SQLText'],false,null,$par);
                  echo '<br/><div style="text-align:right"><button id="run" name="run">Run</button><button>|</button></div><ul class="menuRun"><li onclick="fnExcelReport(\'.dbgrid\',\'Export\');">Export(.xls)</li><li onclick="fnExcelReport(\'.dbgrid\',\'Export\');">Export(.csv)</li></ul>';
                  echo '</div>';
                  $dsReps->Next();
              }
              ?> 
          </div>
            <div><a href="#" target=""></a>
           <div id="uppertabs">
                <ul class="ui-tabs-nav">
                  <li><a href="#dbgrid">Report </a></li>
                  <!-- <li><a href="#map" >Map </a></li> -->
                  <li><a href="#design" >Design </a></li>
                </ul>
                <div id="dbgrid" name="dbgrid"></div>
                <div id="design">
                    <button id="btnRepNew">New Report</button> |
                    <button id="btnRepEdit" disabled>Edit Report</button> |
                    <button id="btnRepDel">Delete Report</button> |
                    Default Report <input style="vertical-align: middle" type="checkbox" id="chkRepDefault" />
                    <br/><br/><textarea id="sqlcode" ></textarea><br/>
                    <button id="btnSQLSave">Save SQL</button>
                </div>
                
                <!--<div id="map"><button onclick="ZoomIn();">+</button><button onclick="ZoomOut();">-</button><br/>
                     <img id="imgMap" style="">
                </div> -->
                <div id="result"></div><br/><br/><br/><br/>
           </div>
        </div>
            <footer class="page-footer">Copyright UNICEF Syria country office /IM</footer>
        </div>
        <script>
        $('document').ready( function(){ //some housekeeping has to be done below
            
            sidenav=$('div[w3-include-html]');
            $.get(sidenav.attr('w3-include-html'),function(data,statu){
                sidenav.html(data);
                $('#wait').fadeOut('fast');
            });
            var editor = CodeMirror.fromTextArea('sqlcode', {
                //height: "450px",
                parserfile: "parsesql.js",
                stylesheet: "js/cm/sqlcolors.css",
                path: "js/cm/",
                textWrapping: false
            });
            
            $('#uppertabs').tabs();
            $('button').button();
            $('.left-menu').accordion({
              activate: function(event, ui){          
                if (ui) $.post('rsgrid.php', {rep:ui.newHeader.text(),des:1} ,function(data,status){ 
                    if(data) {
                        var params=JSON.parse(data);
                        //console.log(data);    
    //                    if (params.sql) {
                        $('div#design textarea').text(params.sql);
                        editor.setCode(params.sql);
                        $('div#design button#btnSQLSave').val(ui.newHeader.text());
                        $('div#design button#btnRepNew').val(ui.newHeader.text());
                        $('div#design button#btnRepDel').val(ui.newHeader.text());
                        $('div#design #chkRepDefault')[0].checked=(params.def=='T');

                      } else  { 
                          console.log('hiding some tabs.');
                          $('[href="#design"]').hide();
                          $('[href="#import"]').hide();
                      }
                });
              }, create : function(event,ui){
                if (ui) $.post('rsgrid.php', {rep:ui.header.text(),des:1},function(data,status){
                          if (data) {
                          var params=JSON.parse(data); 
                              //console.log(data);
                          //if (params.sql){
                          $('div#design textarea').text(params.sql);
                          //console.log(editor.options);
                          //editor.options.content=params.sql;
                          if (ui.header.hasClass('def')){
                              setTimeout(function(){editor.setCode(params.sql);},500);
                              ui.header.next().find('button#run').trigger('click');
                          }
                          else ui.header.prevObject.filter('.def').trigger('click').next().find('button#run').trigger('click');
                          //
                          $('div#design button#btnSQLSave').val(ui.header.text());
                          $('div#design button#btnRepNew').val(ui.header.text());
                          $('div#design button#btnRepDel').val(ui.header.text());
          //                $('div#design #chkRepDefault').val(ui.header.text());
                          $('div#design #chkRepDefault')[0].checked=(params.def=='T');
                      } else  {
                          $('[href="#design"]').hide();
                          $('[href="#import"]').hide();
                         // console.log("not admin");
                      }
                });
              }
            });
            $('.left-menu').resizable({handles:'e'});
            $('button#run').button().click(function(){
                var x=$(this).parent().parent().children('.ComboBox,.CheckList,.DateRange');
                var a=[];
                for (var i=0;i<x.length;i++){
                  switch(x[i].className){
                    case 'ComboBox':
                        a[a.length]=x[i].value;
                        break;
                    case 'CheckList':
                        {
                            var b=[];
                            var y=$(x[i]).children('.CheckBox:checked');
                            for (var j=0;j<y.length;j++)
                                if (y[j].checked) b[b.length]=y[j].value;
                            a[a.length]=b;
                        }
                        break;
                    case 'DateRange':
                        {
                          //  console.log('date range found');
                            var b=[];
                            b[0]=$(x[i]).children('input.dateFrom').val();
                            b[1]=$(x[i]).children('input.dateTo').val();
                            a[a.length]=b;
                        }
                        break;
                    default:    
                  }  
                }
                $.post('rsgrid.php', {rep:$(this).parent().parent().prev().text(),par:a},function(data,status){
                    //alert(String(data).substr(0,4).toLowerCase());
                    if (String(data).substr(0,4).toLowerCase()==='<!do') //usually the case of error this tag appeaers
                      document.write(data);
                    else {
                        $('div#dbgrid').html(data);initgraph();
                        
                        selectableTable($('.dbgrid'));
                    }
                    //console.log(data);
                });
            }).next().button({text:false,icons: {primary: "ui-icon-triangle-1-s"}})
              .click(function (){
                var menu=$(this).parent().next().show().position({my: "right top",at: "right bottom",of: this});
                $(document).one('click',function(){menu.hide();});
                return false;
              }).parent().buttonset().next().hide().menu().css({cursor:'pointer'});
            $('.ComboBox').combobox();
            $('input#SelectAll').click(function() {
              $(this).nextAll(':checkbox').prop('checked',$(this).prop('checked'));
            });
            $('.DateRange input').datepicker();
            $('.DateRange input').datepicker("option", "dateFormat",'yy-mm-dd');
            $('#btnSQLSave').click(function(e){
              //alert($('div#design textarea').val());
              $.post('rsgrid.php',{rep:$(this).val(),sql:editor.getCode()/*$('div#design textarea').val()*/,repdefault:$('#chkRepDefault')[0].checked,des:1},function(data,status){
                //params=JSON.parse(data);  
                  //$(e.target).parent().prevAll().remove();
                  //$(e.target).closest('h3').prepend(data);
                  $('h3.ui-state-active').next().children().not('.ui-controlgroup,.menuRun').remove();
                  repform=$('h3.ui-state-active').next().prepend(data);
                  repform.find('.ComboBox').combobox();
                  repform.find('input#SelectAll').click(function() {
                    $(this).nextAll(':checkbox').prop('checked',$(this).prop('checked'));
                  });
                  $('.DateRange input').datepicker();
                  $('.DateRange input').datepicker("option", "dateFormat",'yy-mm-dd');
              }); 
            });
            $('#btnRepNew').click(function(){
               var repname=prompt('Report name','New Report');
               if (repname) {
               $.post('rsgrid.php',{rep:$(this).val(),repnew:repname,des:1},function(data,status) {
                    params=JSON.parse(data);
                    alert(params.msg);
                    location.reload();
               });    
               }
            //$.post('rsgrid.php',{rep:$(this).val(),repnew:$('div#design textarea').val(),des:1}); 
            });
            $('#btnRepEdit').click(function(){
                   document.URL.href='./main.php';
              //$.post('rsgrid.php',{rep:$(this).val(),sql:$('div#design textarea').val(),des:1}); 
            });
            $('#btnRepDel').click(function(){
              if(confirm('are you sure?'))
              {
                $.post('rsgrid.php',{rep:$(this).val(),repdel:1,des:1},function(data,status) {
                   params=JSON.parse(data);
                   alert(params.msg);
                   location.reload();
                }) ;
              }
            //$.post('rsgrid.php',{rep:$(this).val(),sql:$('div#design textarea').val(),des:1}); 
            });
        });
        $(document).ajaxSend(function(event, jqXHR,ajaxOptions){
            w=$('div.loader'); 
            //w.text('Processing...');
            $('div#wait').show();              
        });
        $(document).ajaxSuccess(function(event, jqXHR,ajaxOptions){
              $('div#wait').hide();
              $('.rep').css('height','auto');
        });
        
    </script>
    
        </div><br/><br/><br/><br/><br/><br/></body>
</html>
 