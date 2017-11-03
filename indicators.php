<!DOCTYPE html>
<html>
    <head>
        <title>HDSS</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link href="js/jquery/jquery-ui.css" rel="stylesheet">
        <!--<link href="css/bootstrap/bootstrap.min.css" rel="stylesheet">-->
        <link href="css/dataset.css" rel="stylesheet" >
        <link href="css/style.css" rel="stylesheet" >
        <style>
            .btnDesign {outline-style: none ;width:130px ;width:70px;background: silver; margin:2px;border-style: none;padding: 4px; border-radius: 4px}
            .btnDesign:hover {background: salmon;color: white}
            .btnDesign:active {box-shadow: dodgerblue 0 0 2px 1px}
            .title {cursor:default;border-radius:4px;display:block;height:20px;color:white;background: dodgerblue;padding:2px}
            .indOperation {border-radius:6px; margin:2px;float:left;min-width:200px;max-width:430px;min-height:150px;padding:4px;
                         background:white;border: solid silver 1px;overflow: auto}
            
            .indOperation select {float:right;margin:1px;border:none;background: linear-gradient(white,lightblue);border-radius: 4px}
            .close,.add {float:right;background: #333;border:solid silver 1px;border-radius: 4px;color: white;cursor: default;padding: 0 4px;margin:2px}
            .close:hover,.add:hover{background: silver}
            #frmDesign {display: none}
            #listActivities {border-radius:4px;padding:4px;background:lightgray ; float: left;width:200px;height: 99%;border: solid lightgray 1px;overflow: auto}
            .box {cursor: default;max-width:200px;border-radius:4px;color:white;background:linear-gradient(lightgreen,green);border: solid gray 1px;margin:2px;padding:4px;z-index: 1}
        </style>
        
        <script src="js/jquery/jquery.js"></script>
        <script src="js/jquery/jquery-ui.js"></script>
        <script src="js/main.js"></script>
        <script src="js/dataset.js"></script>
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
    <body>  
        <div id="wait"><div class="loader"></div></div>
        <div id="mainpage">
        <header><div id="sidenavshow" onclick="openNav()">&#9776; </div>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/><div class="page-title">Design Indicators</div>
             <div class="logout-form">
                <span class="fa fa-fw fa-2x fa-user-circle"></span> <span class="user-label"><?php echo htmlentities($fullname);?></span>
                <div class="user-menu">
                    <a ref="#" onclick="changePassword()">Change Password</a>
                    <a ref="#" onclick="logout()">Logout</a>
                </div>
            </div> 
        </header>
        <div id="frmDesign" title="Design Indicator"></div>
        <div w3-include-html="sidenav.php"></div>
        <div class="page-container"><?PHP
$q=new Dataset($dblink);
$q->Table= 'fwindicatormaster'; //sprintf(sqlBrowseActivities, $_SESSION['username'],$_SESSION['authflag']);  
$q->SQL=sprintf('select p.Description `#g`,i.YOB,i.IndicatorId,i.GroupName, i.Description,i.AltDesc,i.Target,count(d.ActivityCode) Activities %s from fwindicatormaster i'
        . ' left join fwindicatordtl d on d.yob=i.yob and d.IndicatorId=i.IndicatorId'
        . ' left join fwprograms p on p.YOB=d.YOB and p.ProgramId=d.ProgramId'
        . ' where i.YOB=%d group by i.YOB,i.IndicatorId',($_SESSION['authflag']>2?', \'<button class="btnDesign" onclick="btnDesignClick(event)">Design</button>\' ` `':''),$_SESSION['YOB']);
$q->Open();
$r=new HithReport($q);
//$r=new Table($q);
$r->editable=$_SESSION['authflag']>2;
$r->Width='100%';
//$r->CheckList=true;
//$r->PageRows=50;
//$r->draw();
$r->DoGrid();
$q->close();
?></div></div>
        <footer>
                UNICEF Syria country office /IM
        </footer>
    </body>
    <?php if ($_SESSION['authflag']<=2) goto skipJavaImplementation ?>
    <script>
      strOper='<div class="indOperation"><div class="title">Operation <div class="close" onclick="delOperation(event)">X</div><select><option value="sum" selected>sum</option><option value="max">max</option><option value="min">min</option></select></div><div></div></div>';
      dragOption={revert:"invalid",containment:'#frmDesign',cancel:"select,.close", helper: "clone",cursor: "move", greedy:true};
      function doDrop(event,ui){$(this).append(ui.draggable).css({height:'',width:''});}
      function delOperation(e){
         indOper=$(e.target).closest('.indOperation');
         $('#listActivities').append(indOper.find('.box'));
         indOper.remove();
      };
      function addOperation(e){
         return $(strOper).resizable().droppable({accept:'.box,.indOperation',drop: doDrop}).draggable(dragOption);
      }
      function btnDesignClick(e){
            listActivities=$('<div id="listActivities">').empty().droppable({accept:'.box',drop:doDrop}).append('<span class="title">Activities<span class="add" onclick="$(\'#frmDesign\').append(addOperation(event))">+</span></span>');
            var key=$(e.target).closest('tr').find(':hidden').text();
            function decodeOper(oper){
              var divOper=addOperation();
              divOper.find('select').val(oper.opr);
              if (oper.a) oper.a.forEach(function(e,i){divOper.append($('.box[value="'+e+'"]'));});
              if (oper.i) oper.i.forEach(function(e,i){divOper.append(decodeOper(e));});
              return divOper;
            }
            $.post('./designindicator.php',{act:'g',tbl:$(e.target).closest('table').data('table'),key:key},function(data,status){
               a=JSON.parse(data);
               a.activities.forEach(function(e,i){
                   listActivities.append('<div class="box" value="'+e.code+'">'+e.activity+'</div>');
                   $('#listActivities div').draggable(dragOption);
               });
               //console.log(a.calcOper,' /decoded: ',decodeOper(a.calcOper));
               if(a.calcOper) $('#frmDesign').append(decodeOper(a.calcOper));
            });
            trMarked=$(e.target).closest('tr').find('td').addClass('marked');
            frmDesign=$('#frmDesign').empty().append(listActivities);
            frmDesign.dialog({
                modal:true,
                autoOpen:false,
                width:800,
                height:600,
                //height:400,
                close:function(){
                    trMarked.removeClass('marked');
                    $(this).empty().dialog('destroy');//on dialog close
                },
                buttons:{
                    Save:function(){
//                      var oprOptions={sum:' + ',max:' greatst ',min:' least '};
                      function encodeOper(divOper){
                        var oper={a:[],i:[]};
                        oper.opr=divOper.find('select').val();  
                        divOper.children('.box,.indOperation').each(function(i,e){
                              if ($(e).hasClass('box')) oper.a.push(e.getAttribute('value'));
                              else {oper.i.push(encodeOper($(e)));} 
                        });
                        return oper;
                      }
                      indOper=$('#frmDesign>.indOperation');
                      if (indOper.length==1){
                        oper=encodeOper(indOper);
                        $.post('./designindicator.php',{act:'u',tbl:$(this).closest('table').data('table'),key:key,data:oper},function(data,status){
                           console.log(status,': ',data);
                        });
                      }
                        
                      
                      $(this).dialog("close");
                    },
                    Cancel:function(){
                        $(this).dialog("close");
                    }
                }
                
            }).dialog("open");
            //console.log('designing');
        };  
    </script> <?php skipJavaImplementation:?>
    <script>    
       $(document).ready(function(){
            sidenav=$('div[w3-include-html]');
            $.get(sidenav.attr('w3-include-html'),function(data,statu){
                sidenav.html(data);
                $('#wait').fadeOut('fast');
            });
            initgraph();
      });
    </script>
</html>