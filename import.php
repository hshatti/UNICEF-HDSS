<?php
  include_once './conn.php';
  include_once './dataset.php';
  include_once './definitions.php';
  include_once './phpexcel/Classes/PHPExcel.php';
  $tbl= decrypt(filter_input(INPUT_POST, 'tbl',FILTER_SANITIZE_STRING));
//  $ignorDuplication= decrypt(filter_input(INPUT_POST, 'igdup',FILTER_SANITIZE_STRING))==1;
//  $ignorPop= decrypt(filter_input(INPUT_POST, 'igpop',FILTER_SANITIZE_STRING))==1;
//  $ignorHTR= decrypt(filter_input(INPUT_POST, 'igHTR',FILTER_SANITIZE_STRING))==1;
  $un=$_SESSION['username'];
  $XLEOL='_x000D_';
  
  if($tbl) 
    try  { 
    if ($_POST['ac']=='postimport' && isset($_POST['vals'])){
        $vals=json_decode($_POST['vals']);
        //print_r($vals);
        $sqlvals=[];
        foreach ($vals as $val){
          $sqlvals[]=join(',',array_map(function($e){if ($e=="") return('null');else return(QuotedStr($e));}, $val));
        }
        try {
            $q->execute(sprintf('replace into %s values %s' ,$tbl,'('.join('),(',$sqlvals).')'));
            $response=['status'=>true,'data'=>sprintf('[%d] records were affected.',$q->Affected)];
        }
        catch (Exception $e){
           $response=['status'=>false,'data'=>$e->getMessage()];
        }
        echo json_encode($response);
        exit();
    }
    elseif(isset($_FILES['excelfile'])) {
      $q->Close();
      $q->Table='';
      $q->SQL='select * from '.$tbl;
      $q->Open();
      $fields=[];
      foreach ($q->Fields as $fieldname=>$field) $fields[]=$field->name;
      $xlWorkingSheet='Worksheet';
      $objReader=PHPExcel_IOFactory::createReaderForFile($_FILES['excelfile']['tmp_name']);
      $objReader->setLoadSheetsOnly($xlWorkingSheet);
      $objExcel = $objReader->load($_FILES['excelfile']['tmp_name']);
      $objSheet=$objExcel->setActiveSheetIndexByName($xlWorkingSheet);
      if (!$objSheet) $objSheet=$objExcel->setActiveSheetIndex(0);
      $xlMaxCols=$objSheet->getHighestDataColumn();
      $xlMaxRows=$objSheet->getHighestDataRow();
      $headers=[];
      $xlMaxCols++;//Last Column in the Sheet plus one
      for ($i='A';$i!=$xlMaxCols;$i++){
          $headers[]=$objSheet->getCell($i.'1')->getValue();
      }
      if (array_diff($fields, $headers)!==[]){
          die('<div class="pan-import error")><div><h2>The file and target headers does not match.</h2></div><button onclick="doImportCancel()">Go Back</button></div>' );
      }
      else {
        $q->execute('drop temporary table if exists fwtmp');
        $q->execute(sprintf('create temporary table fwtmp as select * from %s where false',$tbl));
        //$objSheet=new PHPExcel_Worksheet;
        for ($row=2;$row<=$xlMaxRows;$row++){
            $vals=[];
            for ($col='A';$col!=$xlMaxCols;$col++){
                $objCell=$objSheet->getCell($col.$row);
                $val=trim($objCell->getValue());
                $val=($val!==''?QuotedStr($val):(key_exists($fields[count($vals)],(array)$tabledefaults[$tbl])?$tabledefaults[$tbl][$fields[count($vals)]]:'null'));
                if ($val!=='null')
                    if (stripos($objCell->getStyle()->getNumberFormat()->getFormatCode(),'yy')!==false)//check if date format
                            $val="date_add('1899-12-30', interval ". $objCell->getValue()." day)";
                $vals[]= $val;
            }
            $q->execute(sprintf('insert into fwtmp (%s) values (%s)',join(',',$fields),join(',',$vals)));
        }
        $q->Commit();
        $q->Close();
        $q->SQL='select * from fwtmp';
        $q->Open();
        printf('<div class="pan-import info"><div><h2>Importing table [%s]</h2></div ><button onclick="doImportCancel()">Cancel</button><button data-table="%s" onclick="doImportPost(event)">Import selected</button></div>', substr($tbl,2),encrypt($tbl));
        $grid=new Table($q);
        $grid->CheckList=True;
        $grid->draw();
        echo '<script> </script>';
      }
      exit();
    }
  }
    catch (Exception $e){
        echo '<div class="error">Something went wrong, the import file should be in the correct format.</div>';
        if ($_SESSION['authflag']!=31) exit;
        echo '<pre>';
        echo $e->getMessage();
        echo '</pre>';
        exit();
    };
  
  $importmap=[];
  $valuesign='#';
  $valuesep='|';
  $qtyvalue=[];
  $keys=0;

  //$YOB=$_SESSION['YOB'];
  $sqlfields='';
  $xlWorkingSheet='Activities';
  $SourceCols=['YOB','Hub','Partner','Activity','Location','Neighborhood','Site','Coverage Level','End Date',    'Reporting Month','Beneficiaries reached before?','Modality','IS HtR','With Disability?','Address'];
  $TargetCols=['YOB','CountryId','Partner','Activity','Location','Neighborhood','Site','Coverage'      ,'ActivityDate','ReportingDate',  'isNew',                        'Modality','AreaStatus','hasDisability',  'Address'];
  $KeyHeaders=['YOB','CountryId','Partner','Activity','Location','Neighborhood','Site','ActivityDate','ReportingDate','isNew','hasDisability','grp1','grp2'];
  $ConvoySourceCols=['YOB','Convoy#','No Trucks','Partner','Modality','Location','Neighborjood','Crossline','End date','Reporting Month','Items','Quantity Planned','Quantity Denied','Reason of Denial','Total'];
  $ConvoyTargetCols=['YOB','No',     'Trucks','Partner',   'Modality','Location','Neighborhood','isCrossline','ActivityDate','ReportingDate','Items','QtyPlanned'  ,'QtyDenied'     ,'DenialReason'     ,'Qty'];
  $ConvoyKeyHeaders=[];
  $fullimport=$_POST['fullimport']=='true';
             
try {
    if (isset($_FILES['excelfile'])){
          //echo $_FILES['excelfile']['name'];
//          $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
//          $cacheSettings = array( 'memoryCacheSize' => '128MB');
//          PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);
          $objReader= PHPExcel_IOFactory::createReaderForFile($_FILES['excelfile']['tmp_name']);
          $objReader->setLoadSheetsOnly([$xlWorkingSheet]);
          $objExcel = $objReader->load($_FILES['excelfile']['tmp_name']);
          $objSheet=$objExcel->setActiveSheetIndexByName($xlWorkingSheet);
          $xlActivityCols=$objSheet->getHighestDataColumn();
          $xlActivityRows=$objSheet->getHighestDataRow();
          $keys=[];
          $xlActivityCols++;//Last Column in the Sheet plus one
          for ($i='A';$i!=$xlActivityCols;$i++){
              $headervalue=$objSheet->getCell($i.'1')->getValue();
              $headervalue=stristr($headervalue."\n","\n",true);
              $headervalue=stristr($headervalue.$XLEOL,$XLEOL,true);
              //echo $headervalue;
              if (stristr($headervalue,'convoy')) {
                $xlFileIsConvoys=true;
                $SourceCols=$ConvoySourceCols;$TargetCols=$ConvoyTargetCols;
              }
              if (stristr($headervalue,'status')) $xlActivityStatus=$i;             
              //echo '<br/>'.stristr($headervalue.$XLEOL,$XLEOL,true).': <br/>';//temp
              for ($j=0  ;$j<count($SourceCols);$j++){
                if(strcmp($headervalue,$SourceCols[$j])===0){
                  $importmap[$TargetCols[$j]]=$i;
                  if (array_search($TargetCols[$j],$KeyHeaders)!==false)
                          $keys[]=$TargetCols[$j];
                } elseif (strpos($headervalue,$valuesign)===0){
                   delete($headervalue, 0, strlen($valuesign)); 
                   //if ($qtyvalue==[]) $keys+=2;// TODO: temprorary, we need to check if grp contains two uoms for validation here?
                   $qtyvalue[$headervalue]=$i;
                   $s=explode($valuesep,$headervalue);
                   for ($ii=0;$ii<count($s);$ii++) $s[$ii]=trim($s[$ii]);
                   //echo '<pre>select count(*) from fwuomgrp where description in ('.QuotedStr($s[0]).','.QuotedStr($s[1]).') or Token in ('.QuotedStr($s[0]).','.QuotedStr($s[1]).')</pre>';
                   $q->Execute('select count(*) from fwuomgrp where description in ('.QuotedStr($s[0]).','.QuotedStr($s[1]).') or Token in ('.QuotedStr($s[0]).','.QuotedStr($s[1]).')');
                   //echo $q->DirectValues[0];
                   if($q->DirectValues[0]!=2) {
                     echo "<strong>ERROR:</strong> one class at least is not recognized in column [<b>$i</b>] value: [<b>".$headervalue."</b>]";
                     exit();
                   }
                }
              }
          //$objExcelWriter = PHPExcel_IOFactory::createWriter($objExcel,'HTML');
          //$objExcelWriter->save('./excel.html');
          }
          $sqlfields=  join(',', array_keys($importmap));
          $sqlvals='';$v=[];
          if ($xlFileIsConvoys){
            $q->Execute('
                create temporary table fwTmpConvoys (
                  YOB            smallint,
                  No             smallint,
                  Trucks         smallint,
                  Partner        varchar(127),
                  Modality       varchar(31),
                  Location       varchar(255),
                  Neighborhood   varchar(255),
                  Site           varchar(255),
                  isCrossline    boolean,
                  ActivityDate   Date,
                  ReportingDate  Date,
                  Items          varchar(255),
                  QtyPlanned     decimal(11,4),
                  QtyDenied      decimal(11,4),
                  DenialReason   varchar(255),
                  Qty           integer,
                  xlRowNum      integer)'); 
          echo '<strong>fwTmpConvoys was Created filling '.$xlActivityRows.' rows</strong>';//'YOB','No',     'Trucks','Partner',   'Modality','Location','Neighborhood','isCrossline','ActivityDate','ReportingDate','Items','QtyPlanned'  ,'QtyDenied'     ,'DeniaReason'     ,'Qty'
            $q->AutoCommit=false;
            try {
                for ($row=2;$row<=$xlActivityRows;$row++){
                foreach ($importmap as $field=>$col){
                   //from insertion statement
                   //echo 'Getting '.$col.$row.'<br/>';
                   $objCell=$objSheet->getCell($col.$row);
                   if ($field=='isCrossline')
                       $v[]=trim(strstr(strval($objCell->getValue()).'\\','\\',true))!='No'?1:0;
                   else     
                   { $v[]= iconv('utf-8','windows-1256', $objCell->isFormula()?QuotedStr($objCell->getOldCalculatedValue()):(stripos($objCell->getStyle()->getNumberFormat()->getFormatCode(),'yy')!==false?"date_add('1899-12-30', interval ". $objCell->getValue()." day)":QuotedStr(trim(strstr(strval($objCell->getValue()).'\\','\\',true)))));
                      if ($v[count($v)-1]=='\'\'') $v[count($v)-1]='NULL';
                   
                   }
                
                }
                //submit row in database
                $s='insert into fwTmpConvoys('.$sqlfields.',xlRowNum) values('.join(',',$v).','.$row.')';
                $v=[];
                $q->Execute($s);}
            } catch (Exception $e){
               echo $e->getMessage().'<br/>row ['.$row.']';
               $q->Rollback();
               echo '<br/>Transaction rolledback!.';
               exit();
            }
            $q->Commit();
            $q->SQL='select * from fwTmpConvoys';
            $q->Open();
            $t=new Table($q);
            $t->Draw();
          }
          elseif ((count($keys)+2)==count($KeyHeaders)){  // start importing 4Ws here
            $q->AutoCommit=false;
            $q->Execute('delete from fwImport where username='.QuotedStr($un));
            $q->Commit();
            if ($_SESSION['authflag']==31) printf('flushing import buffer[%d] <br>',$q->Affected);
            try {$rr=0;
              for ($row=2;$row<=$xlActivityRows;$row++){
                if ($xlActivityStatus && stristr($objSheet->getCell($xlActivityStatus.$row)->getValue(),'complete')===false)  continue;
                foreach ($importmap as $field=>$col){
                   //from insertion statement // the case we need to map values to other values
                   //echo 'Getting '.$col.$row.'<br/>';
                   $objCell=$objSheet->getCell($col.$row);
                   if (in_array($field, array_keys($fwmapvalues))){
                       $cell=trim(strstr(strval($objCell->getValue())."\\","\\",true));
                       $v[]=$fwmapvalues[$field][$cell];
                   }
                   else  {   
                       $cell=/*iconv('utf-8','windows-1256', */ $objCell->isFormula()?QuotedStr($objCell->getOldCalculatedValue()):(stripos($objCell->getStyle()->getNumberFormat()->getFormatCode(),'yy')!==false?"date_add('1899-12-30', interval ". $objCell->getValue()." day)":QuotedStr(trim(strstr(strval($objCell->getValue()).'\\','\\',true)))) /*) */;
                       $v[]= $cell=="''"&& key_exists($field, $fwdefaultvalues)?
                               $fwdefaultvalues[$field]:$cell;
                   }
                }
                //submit row to database
                foreach ($qtyvalue as $grpstr=>$col) {
                  $grps=  explode($valuesep,$grpstr);
                  for ($ii=0;$ii<count($grps);$ii++) $s[$ii]=trim($grps[$ii]);
                  $qty=$objSheet->getCell($col.$row)->getValue();
                  $s='insert into fwImport(sessionid,username,'.$sqlfields.',grp1,grp2,qty,xlRowNum) values('.QuotedStr(session_id()).','.QuotedStr($un).','.join(',',$v).','.QuotedStr($grps[0]).','.QuotedStr($grps[1]).','.$qty.','.$row.')';
                  if (is_numeric($qty)&&($qty>0)) {$rr++;$q->Execute($s);}
                }
                $v=[];
                //if (($row%100)==0) echo date('h:i:s').'<br/>';
              }
            } catch (Exception $e){
               echo $e->getMessage().'<br/>row ['.$row.']';
               $q->Rollback();
               echo '<br/>Transaction rolledback!.';
               exit();
            }
            $q->Commit();   
            //echo $_SESSION['CountryId'].' is your country <br>';
            $q->close;
            $q->SQL=sprintf("select distinct
  'Error!' Error,if(p.Description is null,i.Partner,null) Partner, if(al.Description is null,i.Activity,null) Activity,if(a4.Description is null,i.Location,null) Location,if(a5.RefName is null, i.Neighborhood,null) Neighborhood,if(s.Description is null, i.Site,null) Site,i.xlRowNum as ExcelRow
from 
  fwImport i 
  left join fwAdmin4 a4 on a4.location=i.location
  left join fwAdmin5 a5 on a5.Location=i.Neighborhood
  left join fwSites s on s.RefName=i.Site
  left join fwActivityList al on al.YOB=i.YOB and al.Description=i.Activity
  left join fwPartners p on p.CountryId=i.CountryId and  p.Description=i.Partner
  left join fwActivityBeneficiaries ab on ab.YOB=al.YOB and ab.CountryId=p.CountryId and ab.PartnerId=p.PartnerId and ab.ProgramId=al.ProgramId and ab.OutcomeId=al.OutcomeId and ab.outputid=al.outputid and ab.activityid=al.activityid
            and ab.GovernorateId=a4.GovernorateId and ab.DistrictId=a4.DistrictId and ab.Admin3Id=a4.Admin3Id and ab.Admin4Id=a4.Id and ab.Admin5Id=a5.Id and ab.SiteId=s.SiteId
where
  (al.Description is null or a4.Location is null or p.Description is null or (i.Neighborhood<>'' and a5.Location is null) or (i.Site<>'' and s.Description is null))
and sessionid=%s and username=%s", QuotedStr(session_id()),QuotedStr($_SESSION['username']));
            //printf('<br>[%d] rows processed',$rr);
            $q->Open();
            if($q->RecordCount>0){
                $t=new Table($q);
                $t->draw();
                echo "<script>$('#tblResult td').css({background:'whitesmoke',color:'red'});</script>";
                return;
            }
            $q->SQL=sprintf(
              'select 
                `YOB` ,
                `CountryId` ,
                `Partner` ,
                `Activity` ,
                `Location` ,
                `Neighborhood` ,
                `Site` ,
                `ActivityDate` ,
                `ReportingDate` ,
                count(distinct xlRowNum ) `Duplications`,
                group_concat(distinct xlRowNum,\' \') `Duplicated rows`
              from fwimport
              where 
                sessionid =%s and username=%s
              group by
                `sessionid`,
                `username`,
                `YOB` ,
                `CountryId` ,
                `Partner` ,
                `Activity` ,
                `Location` ,
                `Neighborhood` ,
                `Site` ,
                `ActivityDate` ,
                `ReportingDate` ,
                `isNew` ,
                `hasDisability` /*,
                `grp1` ,
                `grp2` */
                having count(distinct xlRowNum)>1', QuotedStr(session_id()),QuotedStr($_SESSION['username']));
            $q->Open();
            if($q->RecordCount>1) {
               echo '<div id="panDuplicat">';
               echo '<div class="error" style="padding:16px;font-weight:bold">Please check duplication of records below. <button onclick="btnIgnorClick(event)" style="float:right" class="btnNormal">Ignor and continue</button></div><br/>';
               $t=new Table($q);
               //$t->CSS='width:1024px';
               $t->draw();
               $gotDuplications=$q->RecordCount;
               echo '</div>';
               //return;
               
            }
            
            $q->Close();
            $q->SQL='select al.SectorId,i.Activity,cast(sum(if(coalesce(ab.UpdatedAt,\'2000-1-1\')<i.ReportingDate,i.qty,0)) as decimal) Beneficiaries,max(ab.UpdatedAt) `Last Update Date`,max(i.ReportingDate) `New Update Date`
                     from fwImport i 
                     left join fwAdmin4 a4 on a4.Location=i.location
                     left join fwAdmin5 a5 on a5.Location=i.Neighborhood
                     left join fwSites s on s.Description=i.Site and s.CountryId=i.CountryId and s.Admin4Id=a4.Id 
                     left join fwActivityList al on al.YOB=i.YOB and al.Description=i.Activity
                     left join fwPartners p on p.CountryId=i.CountryId and p.Description=i.Partner
                     left join fwModality m on m.Description=i.Modality
                     left join fwUOMGrp u1 on u1.Description=i.grp1 or u1.Token=i.grp1
                     left join fwUOMGrp u2 on u2.Description=i.grp2 or u2.Token=i.grp2
                     left join fwActivityBeneficiaries ab on ab.YOB=al.YOB and ab.CountryId=i.CountryId and ab.PartnerId=p.PartnerId and ab.ProgramId=al.Programid and ab.outcomeid=al.Outcomeid and ab.outputid=al.outputid and ab.activityid=al.activityid 
                                and ab.GovernorateId=a4.GovernorateId and ab.DistrictId=a4.DistrictId and ab.Admin3Id=a4.Admin3Id and ab.Admin4Id=a4.Id and ab.Admin5Id=coalesce(a5.Id,0) and ab.SiteId=coalesce(s.SiteId,0) and ab.isNew=i.isNew and ab.hasDisability=i.hasDisability and ab.ActivityDate=i.ActivityDate and ab.UpdatedAt=i.ReportingDate and ab.grp1=u1.grp and ab.grp2=u2.grp
                     where i.SessionId='.QuotedStr(session_id()).' and i.username='.QuotedStr($_SESSION['username']).
                    ' group by SectorId,Activity';
            $q->Open();
            echo '<div id="panResult">';
            $t=new Table($q);
            $t->draw();
            echo '<br/>';
            echo '<select id="cmbSection" name="section" onload=onchange="cmbSectionChange(this);" onchange="cmbSectionChange(this);">';
            $q->Close();
            $q->SQL='select distinct s.Description from 
                        fwUsers u join fwusersector us on us.username=u.username 
                        join fwSectors s on s.SectorId=us.SectorId
                        where us.username='.QuotedStr($un).' or ('.$_SESSION['authflag'].'=31 and '.QuotedStr($un).'=\'admin\')';
            $q->Open();
            while (!$q->EOF()){
                echo '<option>'.$q->Values[0].'</option>';
                $q->Next();
            }
            echo '</select> Full Import?(delete all the previous section entries?)<input type="checkbox" id="chkFullImport"> <button onclick="doPost(this)">Post</button>';
            echo '</div>';// for panResult <div>
            echo '<script>';
            if ($gotDuplications){
                echo '$("#panResult").hide();';
                echo '$("panResult").show();';
            }
            echo 'function btnIgnorClick(event){
              if (!confirm("Duplicate record can cause some inconsistent results, are you sure you want to continue?")) return;
              $("#panDuplicat").hide();
              if ($("#panHTR").length==0) $("#panResult").show();
            }';
            echo ' function doPost(sender){
                  $.post(\'import.php\',{ac:\'postimport\',section:$(\'#cmbSection\').val(),full:$(\'#chkFullImport\')[0].checked},function(data,status,xhr){
                    $(\'#ImpResult\').html(data);
                  });
                }
                function cmbSectionChange(cmb){
                    $(\'#tblResult td\').css({background:\'none\'});
                    $(\'#tblResult tr td:nth-child(1):contains(\'+cmb.value+\')\').css({background:\'lightgreen\'}).siblings().css({background:\'lightgreen\'});
                }
                cmbSectionChange($(\'#cmbSection\')[0]);
                </script>';
            
                
            }
          else echo '<b>Incompatible file, must have at least two values and columns !.</b> ['.join('],[', array_diff ($KeyHeaders, $keys)).']<br/>';
          
              
         // print_r($importmap);
  } 
  else 
  {
          if(isset($_POST['ac'])&&isset($_POST['section'])&&isset($_POST['full'])) {
              $ac=$_POST['ac'];$full=$_POST['full'];$section=$_POST['section'];
              
          };

          switch ($ac) {
             case 'postimport':{
                     try {
                         
                        $q->AutoCommit=false;
                        $SectorId=$q->Execute(sprintf('select SectorId from fwsectors where Description=%s',QuotedStr($section)))[0];
                        if($full=='true') 
                        {
                            $q->Execute(sprintf(sqlDeleteSection,$YOB, QuotedStr($_SESSION['CountryId']),$SectorId));
                            $q->Commit();
                            printf('[%d] rows were deleted.<br>',$q->Affected);
                        }
//                        if($section=='WASH'){
//                            $q->Execute('set @rw=(select max(SiteId) from fwSites)');
//                            $q->Execute(sqlWASHAddressIntoSite);
//                            echo 'WASH Only workaround - ['.$q->Affected.'] Sites added from Addresses<br/>';
//                        };
                        //echo 'Chosen sector is ['.$section.'] id='.$SectorId;
                        $q->Execute(sprintf(sqlImport,  QuotedStr($un),$SectorId, QuotedStr(session_id())));
                        $q->Commit();
                        $affected=$q->Affected;
                        $q->Execute(sprintf('select max(xlRowNum) from fwimport where sessionid=%s',QuotedStr(session_id())));
                        echo '['.$q->DirectValues[0].'] rows were processed /['.$affected.'] transactions inserted';
                     } catch (Exception $exc) {
                         echo $exc->getMessage();
                         $q->Rollback();
                         echo '<br/>Transaction Rollback!.';
                         exit();
                         
                     }
                 }
                 break;
             default:
                 break;
            }
      }
} catch (Exception $e){
    echo '<div class="error">Something went wrong, the import file should be in the correct format.</div>';
    if ($_SESSION['authflag']!=31) exit;
    echo '<pre>';
    echo $e->getTraceAsString();
    echo '</pre>';
};
//function testFormula($sheet,$cell) {
//    $formulaValue = $sheet->getCell($cell)->getValue();
//    echo 'Formula Value is' , $formulaValue , PHP_EOL;
//    $expectedValue = $sheet->getCell($cell)->getOldCalculatedValue();
//    echo 'Expected Value is '  , 
//          ((!is_null($expectedValue)) ? 
//              $expectedValue : 
//              'UNKNOWN'
//          ) , PHP_EOL;
//
//    $calculate = false;
//    try {
//        $tokens = PHPExcel_Calculation::getInstance()->parseFormula($formulaValue,$sheet->getCell($cell));
//        echo 'Parser Stack :-' , PHP_EOL;
//        print_r($tokens);
//        echo PHP_EOL;
//        $calculate = true;
//    } catch (Exception $e) {
//        echo 'PARSER ERROR: ' , $e->getMessage() , PHP_EOL;
//
//        echo 'Parser Stack :-' , PHP_EOL;
//        print_r($tokens);
//        echo PHP_EOL;
//    }
//
//    if ($calculate) {
//        try {
//            $cellValue = $sheet->getCell($cell)->getCalculatedValue();
//            echo 'Calculated Value is ' , $cellValue , PHP_EOL;
//
//            echo 'Evaluation Log:' , PHP_EOL;
//            print_r(PHPExcel_Calculation::getInstance()->debugLog);
//            echo PHP_EOL;
//        } catch (Exception $e) {
//            echo 'CALCULATION ENGINE ERROR: ' , $e->getMessage() , PHP_EOL;
//
//            echo 'Evaluation Log:' , PHP_EOL;
//            print_r(PHPExcel_Calculation::getInstance()->debugLog);
//            echo PHP_EOL;
//        }
//    }
//}


//PHPExcel_Calculation::getInstance()->writeDebugLog = true;
//testFormula($objSheet, 'J2')     
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<script>
var valuecols=$('#importresult tr:nth-child(1) th');
function importcheck(obj){
  a=$(obj).parent().parent().children('td').css("background",(obj.checked?"lightgreen":""));
  b=[];
  for (i=0;i<a.length-1;i++) b.push(a[i].textContent);
}
</script>