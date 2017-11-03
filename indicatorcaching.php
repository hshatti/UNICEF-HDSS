<?php
include_once './conn.php';
include_once './dataset.php';
include_once './rsgrid.php';
include_once './definitions.php';
if ($_SESSION['username']=='') {
    header('Location: index.php');
    exit;
}
if ($_SESSION['authflag']<3) exit();

$act=$_POST['act'];
if ($act=='summary'){
  $indicatorid=strstr($_POST['indicator'],' ',true);
  if (is_numeric($indicatorid)) $indicatorid=intval($indicatorid);else exit();
  define('sqlIndicators',sprintf(' select 
    Program `#g {"background":"#08f"}`,
    Indicator `#g`,
    GroupName,
    ReportingMonth,
    Sum(a.Calculated) Calculated,
    Sum(b.Value) Published,
    %1$s `-`
    from (select 
      i.YOB, i.ProgramId, i.Program, i.IndicatorId, concat(i.IndicatorId,\' \',im.Description) Indicator, im.GroupName, i.CountryId, i.Admin4Id, i.Admin5Id, i.SiteId, i.ReportingMonth, i.UpdatedAt, sum(qty) Calculated    
    from
      (select
        id.YOB, id.IndicatorId, ab.ProgramId, p.Description Program, CountryId, Admin4id, if(id.IndicatorId>1,Admin5Id,0) Admin5id, if(id.IndicatorId>1,SiteId,0) SiteId, PartnerId, grp1, grp2, hasDisability, monthname(updatedat) ReportingMonth, month(UpdatedAt) UpdatedAt,
        
        if(id.IndicatorId=%5$d , :indicator%5$d , null) qty 
      from
        fwIndicatorDtl id join fwActivityBeneficiaries ab using (ActivityCode)
        join fwPrograms p on (p.YOB=ab.YOB and p.ProgramId=ab.ProgramId)
      where 
        id.YOB=%2$d and ab.CountryId =%3$s and p.SectorId in (%4$s) and id.IndicatorId=%5$d and ab.isNew=1
      group by 
        id.IndicatorId, ab.CountryId, Admin4Id,
        if(id.IndicatorId>1,Admin5Id,0), if(id.IndicatorId>1,SiteId,0), if(id.ProgramId=5 and id.IndicatorId>1,PartnerId,0), month(UpdatedAt), grp1, grp2, hasDisability
      ) i 
    join fwIndicatorMaster im on (im.YOB=i.YOB and im.IndicatorId=i.IndicatorId) 
    group by 
      i.YOB, i.IndicatorId, i.CountryId, i.Admin4Id, i.Admin5Id, i.SiteId, i.UpdatedAt
    ) a left outer join fwCachedIndicators b using (YOB,IndicatorId,CountryId,Admin4Id,Admin5Id,siteid,UpdatedAt)
    group by 
      ProgramId, Indicator,
      GroupName, UpdatedAt',
                'concat(\'<button id="\',if(sum(b.value) is null,\'btnPublish\',\'btnUnPublish\'),\'" style="width:100px" onclick="btnPublish(event)" data-month="\',a.UpdatedAt,\'" data-group-name="\',a.GroupName,\'" data-indicator="\',a.Indicator,\'" data-indicatorId="\',a.IndicatorId,\'" class="btnNormal">\',if(sum(b.value) is null,\'Publish\',\'UnPublish\'),\'</button>\')',
                $_SESSION['YOB'], QuotedStr($_SESSION['CountryId']), join(',',$_SESSION['sectors']),$indicatorid));
    $q=new Dataset($dblink);
    $qry=forkIndicators(sqlIndicators);
    $q->SQL=$qry;
    $q->Open();
    $g=new HithReport($q);
    $g->DataHotTrackCSS='hottrack';
    $g->DoGrid();
//    if ($_SESSION['authflag']==31) echo '<script> console.log('.quotedStr($qry).');</script>';
    exit();
} elseif($act=='publish'){
    $month=$_POST['month'];
    $indicatorid= strstr($_POST['indicator'],' ',true);
    if (is_numeric($indicatorid)) $indicatorid=intval($indicatorid);
    else exit();
//    if(!is_int($indicatorid)) die('incorrect parameters. '.$indicatorid);
    $qry=forkIndicators(sprintf('insert into fwcachedindicators 
    select i.YOB, i.IndicatorId, i.CountryId, i.Admin4Id, i.Admin5Id, i.SiteId, i.PartnerId, null, %4$s UpdatedAt , sum(qty) Qty
    from
        (
        select
            id.YOB, id.IndicatorId, id.ProgramId, CountryId, Admin4id,
            if(ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\'),0,ab.Admin5Id) Admin5Id,
            if(ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\'),0,ab.SiteId) SiteId,
            if(not ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\') and id.IndicatorId<>305 ,0,ab.PartnerId) PartnerId,
            grp1, grp2, hasDisability, :indicator%1$d qty 
        from
            fwIndicatorDtl id join fwActivityBeneficiaries ab using (ActivityCode)
        where 
            id.YOB=%2$d and ab.CountryId =%3$s and month(ab.updatedat)<=%4$s and id.indicatorid=%5$d and ab.isNew=1
        group by 
            id.IndicatorId, ab.CountryId, Admin4Id, 
            if(ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\'),0,ab.Admin5Id), 
            if(ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\'),0,ab.SiteId),
            if(not ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\') and id.IndicatorId<>305 ,0,ab.PartnerId),
            grp1, grp2, hasDisability
        union all
        select
            id.YOB, id.IndicatorId, id.ProgramId, CountryId, Admin4id, if(id.IndicatorId>1 and id.IndicatorId<>305,ab.Admin5Id ,0) Admin5Id, if(id.IndicatorId>1 and id.IndicatorId<>305,ab.SiteId,0) SiteId, if(id.ProgramId=5 and id.IndicatorId>1 and id.IndicatorId<>305,PartnerId,0) PartnerId, grp1, grp2, hasDisability, -( :indicator%1$d ) qty 
        from
            fwIndicatorDtl id join fwActivityBeneficiaries ab using (ActivityCode)
        where 
            id.YOB=%2$d and ab.CountryId =%3$s and month(ab.updatedat)<%4$s and id.indicatorid=%5$d and ab.isNew=1
        group by 
            id.IndicatorId, ab.CountryId, Admin4Id, 
            if(ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\'),0,ab.Admin5Id), 
            if(ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\'),0,ab.SiteId),
            if(not ab.ActivityCode in (\'2017.5.1.1.2\',\'2017.5.1.1.1\',\'2017.5.1.1.3\',\'2017.5.1.1.4\',\'2017.5.1.1.5\',\'2017.5.1.1.6\',\'2017.5.1.1.7\',\'2017.5.1.1.8\',\'2017.5.1.1.9\',\'2017.5.1.1.10\',\'2017.5.1.1.11\') and id.IndicatorId<>305 ,0,ab.PartnerId),
            grp1, grp2, hasDisability
        ) i 
    join fwIndicatorMaster im using (YOB,IndicatorId) 
    join fwPrograms p on (p.YOB=i.YOB and p.ProgramId=i.ProgramId)
    left outer join fwCachedIndicators c on (c.YOB=i.YOB and c.IndicatorId=i.IndicatorId and c.CountryId=i.CountryId and c.UpdatedAt=%4$s and c.Admin4Id=i.Admin4Id and c.Admin5Id=i.Admin5Id)
    where
        p.SectorId in (%6$s)
    group by 
        i.YOB, i.IndicatorId, i.CountryId, i.Admin4Id, i.Admin5Id, i.SiteId, i.PartnerId
    ' 
           ,$indicatorid,
           $_SESSION['YOB'], 
           QuotedStr($_SESSION['CountryId']),
           QuotedStr($month),
           $indicatorid,
           join(',',$_SESSION['sectors'])));
    try {$q->Execute($qry);}
    catch (Exception $err){
       $errStr=$err->getMessage() .' / '. $err->getTraceAsString(); 
    }
    $q->SQL=sprintf('select m.GroupName,monthname(str_to_date(i.Updatedat,\'%%m\')) ReportMonth, format(sum(i.value),0) Value ,format(sum(i.value),0) Published,concat(\'<button id="btnUnPublish" onclick="btnPublish(event)" style="width:100px" class="btnNormal" data-indicator="\',i.IndicatorId,\' \',m.Description,\'" data-month="\',i.UpdatedAt,\'" data-group-name="\',m.GroupName,\'" data-indicatorid="\',i.IndicatorId,\'">UnPublish</button>\') button from '
           . 'fwcachedindicators i join fwIndicatorMaster m using(YOB,IndicatorId) where yob=%d and CountryId=%s and indicatorid=%d and Updatedat=%s group by i.YOB,i.IndicatorId,i.UpdatedAt,i.CountryId',
           $_SESSION['YOB'],QuotedStr($_SESSION['CountryId']),$indicatorid, QuotedStr($month));
   $q->open();
   $r=sprintf('<td>%s</td><td>%s</td><td class="num">%s</td><td class="num">%s</td><td>%s</td>',$q->Values['GroupName'],$q->Values['ReportMonth'],$q->Values['Value'],$q->Values['Published'],$q->Values['button']);
   echo json_encode(['qry'=>$_SESSION['authflag']==31?'$qry':'','r'=>$r,'err'=>$_SESSION['authflag']==31?$errStr:'']);
   exit();
} elseif($act=='unpublish'){
   $month=$_POST['month'];
   $indicatorid=strstr($_POST['indicator'],' ',true);
   if (is_numeric($indicatorid)) $indicatorid=intval($indicatorid);else exit();
   $qry=sprintf('delete from fwcachedindicators where YOB=%d and IndicatorId=%d and UpdatedAt=%s and CountryId=%s',$_SESSION['YOB'],$indicatorid, QuotedStr($month), QuotedStr($_SESSION['CountryId']));
   $q->Execute($qry);
   $q->SQL= forkIndicators(sprintf('select 
      im.GroupName,
      i.ReportingMonth,
      format(sum(qty),0) Computed,
      concat(\'<button id="btnPublish" onclick="btnPublish(event)" style="width:100px" class="btnNormal" data-month="\',i.ReportMonth,\'" data-group-name="\',im.GroupName,\'" data-indicator="\',i.IndicatorId,\' \',im.Description,\'" data-indicatorid="\',i.IndicatorId,\'">Publish</button>\') button
    from
      (select
        id.YOB,
        id.IndicatorId,
        id.ProgramId,
        CountryId,
        Admin4id,Admin5Id,SiteId,
        PartnerId,
        grp1,
        grp2,
        hasDisability,
        monthname(UpdatedAt) ReportingMonth,
        month(UpdatedAt) ReportMonth,
        :indicator%d  qty 
      from
        fwIndicatorDtl id join fwActivityBeneficiaries ab using (ActivityCode)
      where 
        id.YOB=%d
        and ab.CountryId =%s
        and month(ab.updatedat)=%s
        and id.indicatorid=%d
        and ab.isNew=1
      group by 
        id.IndicatorId,
        ab.CountryId,
        Admin4Id,
        if(id.IndicatorId>1,Admin5Id,0),
        if(id.IndicatorId>1,SiteId,0),
        if(id.ProgramId=5 and id.IndicatorId>1,PartnerId,0),
        month(UpdatedAt),
        grp1,
        grp2,
        hasDisability
      ) i 
    join fwIndicatorMaster im using (YOB,IndicatorId) 
    join fwPrograms p on (p.YOB=i.YOB and p.ProgramId=i.ProgramId)
    left outer join fwCachedIndicators c on (c.YOB=i.YOB and c.IndicatorId=i.IndicatorId and c.CountryId=i.CountryId and c.UpdatedAt=i.ReportMonth and c.Admin4Id=i.Admin4Id and c.Admin5Id=i.Admin5Id)
    where
      p.SectorId in (%s)
    group by 
      i.YOB,
      i.IndicatorId,
      i.CountryId,
      i.ReportingMonth' 
           ,$indicatorid,
           $_SESSION['YOB'], 
           QuotedStr($_SESSION['CountryId']),
           QuotedStr($month),
           $indicatorid,
           join(',',$_SESSION['sectors'])));
   $q->Open();
   $r= sprintf('<td>%s</td><td>%s</td><td class="num">%s</td><td class="num"></td><td>%s</td>',$q->Values['GroupName'],$q->Values['ReportingMonth'],$q->Values['Computed'],$q->Values['button']);
   echo json_encode(['qry'=>'$qry','r'=>$r]);
   
   exit();
}

?><!DOCTYPE html>
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
//$q->SQL=sprintf('select * from fwPrograms where yob=%d and SectorId in (%s)',$_SESSION['YOB'],join(',',$_SESSION['sectors']));
//$q->Open();
//if (isset($section)){
//  $Program=$q->LocateKey([$_SESSION['YOB'],$section],['YOB','Description']);
//  if ($Program!==false){
//      $ProgramId=$Program['ProgramId'];
//      // start postig
//  }  
//}
//else { // no section submitted
//  $selectSection='<select>';
//  while (!$q->EOF()){
//      $selectSection.=sprintf('<option value="%d">%s</option>',$q->Values['ProgramId'],$q->Values['Description']);
//      $q->Next();
//  } 
//  $selectSection.='</select>';
//}

?>        
        
    </head>
    <body>  <div id="frmConfirm" hidden></div>
        <!--<div id="wait" style="display:none"><div class="loader"></div></div>-->
        <div id="mainpage">
        <header><div id="sidenavshow" onclick="openNav()">&#9776; </div>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/><div class="page-title">Published Figures</div>
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
        <div class="page-container">
            <span class="label">Choose Indictor </span><select id="cmbIndicator" onchange="indicatorChange(event)">
                <?php 
                $q->SQL=sprintf('select p.Description Program, im.IndicatorId,im.Description Indicator from fwIndicatorMaster im join fwPrograms p using(yob,ProgramId) where yob=%s and p.SectorId in (%s) order by IndicatorId',$_SESSION['YOB'],join(',',$_SESSION['sectors']));
                $q->open();
                while (!$q->EOF()){
                    printf('<option data-indicatorid="%1$d">%d %s</option>',$q->Values['IndicatorId'],$q->Values['Indicator']);
                    $q->Next();
                }
                ?>
            </select> <button id="btnPublishAll" class="btnNormal" onclick="btnPublishAll(event)">Publish All</button><br><br>
<?PHP
//    echo '<table>';
//    while (!$q->EOF()){
//        printf('<tr><td>%s</td><td>%s</td><td class="num">%d</td><td class="num">%s</td></tr>',$q->Values['Section'],$q->Values['Indicator'],$q->Values['ReportingMonth'], number_format($q->Values['Value']));
//        $q->Next();  
//    }
//    echo '</table>';  
 
?><div id="indTable"></div></div></div>
        <footer>
                UNICEF Syria country office /IM
        </footer>
    </body>
    <script>   
        function indicatorChange(e){
            $('#indTable').holdOn();
            $.post('./indicatorcaching.php',{act:'summary',indicator:e.target.value},function(data,status){
                    $('#indTable table').remove();
                    $('#indTable #params').remove();
                    $('#indTable').prepend(data);
                    initgraph();
                    selectableTable($('#tblResult'));
                    $('#indTable').holdOn('destroy');
            });            
        }
        function btnPublish(e){ 
                $(e.target).closest('tr').find('td').addClass('marked');
                act=e.target.textContent.toLowerCase();
                mon=$(e.target).closest('tr').find('td:eq(1)').text();
                $('#frmConfirm').html('<b>'+e.target.textContent+' ['+mon+'] data of </b><div style="padding:4px;background:whitesmoke;font-style:italic">'+e.target.dataset.indicator+'</div>');
                $('#frmConfirm').dialog({modal:true,title:e.target.dataset.groupName,
                    buttons:
                    {  
                        Yes:function(){
                            $(e.target).append('<span class="fa fa-spinner fa-spin">');
                            e.target.disabled=true;
                            $.post('./indicatorcaching.php',{act:act,month:e.target.dataset.month,indicator:e.target.dataset.indicator},function(data,status){
//                                e.target.className='btnNormal';
                                e.target.disabled=false;
                                data=JSON.parse(data);
                                if(data.err) console.log(data.err);
                                console.log(data.qry);
                                $(e.target).closest('tr').html(data.r);   
                            });
                            $('#frmConfirm').dialog('close');
                        },
                        No:function(){$('#frmConfirm').dialog('close');},
                    },
                    close:function(){$(e.target).closest('tr').find('td').removeClass('marked');$('#frmConfirm').dialog('destroy');}
                });
        }
        function publishAll(p){
            $.post('./indicatorcaching.php',{act:'publish',month:p.month,indicator:p.indicator},function(data,status){
                data=JSON.parse(data);
                if(data.err) console.log(data.err);
                console.log(data.qry);
                if (p.done) p.done(data);
            });
        }
        function btnPublishAll(e){
                $('#frmConfirm').html('<b>Publish all data of </b><div style="padding:4px;background:whitesmoke;font-style:italic">'+$('#cmbIndicator').val()+'</div>');
                $('#frmConfirm').dialog({modal:true,title:'Publish All',
                    buttons:
                    {  
                        Yes:function(){
                            $(e.target).append('<span class="fa fa-spinner fa-spin">');
                            e.target.disabled=true;
                            var progress=$('button#btnPublish').each(function(i,el){
                                if ($(el).text().toLowerCase()==='publish'){  
                                    $(el).append('<span class="fa fa-spinner fa-spin">');el.disabled=true;
                                    publishAll({
                                        month:el.dataset.month,
                                        indicator:$('#cmbIndicator').val(),
                                        done:function(data){
                                            $(el).closest('tr').html(data.r);
                                            if (i+1===progress) {e.target.disabled=false;};
                                        }
                                    });
                                };
                            }).length;
                            if (progress===0)e.target.disabled=false;
                            $(e.target).children().last().remove();
                            $('#frmConfirm').dialog('close');
                        },
                        No:function(){$('#frmConfirm').dialog('close');},
                    },
                    close:function(){$('#frmConfirm').dialog('destroy');}
                });
        }
        
        $(document).ready(function(){
            $('body').holdOn();
            sidenav=$('div[w3-include-html]');
                $.get(sidenav.attr('w3-include-html'),function(data,status){
                    sidenav.html(data);
                    $('#cmbIndicator').trigger('change');
                    $('#cmbIndicator').combobox();
                    $('body').holdOn('destroy');
                });

//            $('<div style="text-align:center;position:absolute;background:whitesmoke;height:100%;left:0;top:0"><span style="position:absolute;top:50%;width:1em;margin:-0.5em" class="fa fa-spinner fa-4x fa-pulse"></span></div>').appendTo('div.margin-4').outerWidth($('div.margin-4').innerWidth());
        });
    </script>
</html>