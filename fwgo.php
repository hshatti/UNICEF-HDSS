<?php
  include_once './conn.php';
  include_once './definitions.php';
  include_once './dataset.php';

function forkCriteria($sqlqry,$criteria){
    $orderpos=stripos($sqlqry,' group by ');
    if ($orderpos===false) $orderpos=stripos($sqlqry,' order by ');
    if($orderpos!==false) {
        $sqlqryNoOrder=substr($sqlqry,0, $orderpos);
        $sqlqryOrder=substr($sqlqry,$orderpos);
    }
    if (stripos($sqlqry,' where ')===false)
        $qrystmt='%s where %s';
    else
        $qrystmt='%s and (%s)';
    return(sprintf($qrystmt,$orderpos===false?$sqlqry:$sqlqryNoOrder,$criteria).$sqlqryOrder);
}
$maxLookupRecords=100;
$act=addslashes($_POST['act']);
$tbl=decrypt(addslashes($_POST['tbl']));
$sqlqry=decrypt(addslashes($_POST['qry']));
$key=decrypt($_POST['key']);
//if (array_search(strtolower($tbl),$tbls)===false) {// no need for this anymore ,injection is impossible, all data exchange is encrypted
//    echo json_encode(['status'=>false,'data'=>sprintf('table[%s] doesn\'t exist.',$tbl)]);    
//    exit();
//}
// echo $key;//debug
if ($key==''||in_array($act,['i','l'])) $key='false'; // case of adding new records
$qry=new Dataset($dblink, isSqlQuery($tbl)? forkCriteria($tbl, $key):sprintf('select * from %s where %s',$tbl,$key));
$qry->open();
//print_r($qry->SQL);exit;//debug
foreach ($qry->Fields as $field) $fields[$field->name]=$field;


$par=$_POST['par']; // TODO: next enhancement to use filters and validate parameters array

$refint=$dbrelations[$tbl];
$controltype=$dbControlsType[$tbl];
$detail=$dbMasterDetail[$tbl];
if (!isset($controltype)) $controltype=[];
if (!isset($refint)) $refint=[];

if (isset($refint)) $look= new Dataset($dblink);
if (isset($detail)) {$dtl= new Dataset($dblink);if(!isset($look)) $look= new Dataset($dblink);}
if (act=='v') {
    $keys= addslashes(filter_input(INPUT_POST,'keys',FILTER_SANITIZE_STRING));
    $w=[];foreach ($keys as $a=>$b) $w[]=sprintf('%s=%s',QuotedStr($a,'`'),QuotedStr($b));
    $key= array_keys($qry->Keys)[count($qry->Keys)-1];
    $defval=$tabledefaults[$tbl][$key];
    if(!isset($defval)) exit();
    if (isSqlQuery($defval)) {
        $defval=$defval.join(' and ',$w);
        $q->execute($defval);//make sure next value is purly resulted from the sql command
        echo json_encode(['status'=>true,'data'=>[$q->DirectFields[0],$q->DirectValues[0]]]);
    } else {
        echo ['status'=>['data'=>$defval]];
    }
    
}
elseif($act=='l'){
    $term= (filter_input(INPUT_POST,'term',FILTER_SANITIZE_STRING));
    $keys= addslashes(filter_input(INPUT_POST,'keys',FILTER_SANITIZE_STRING));
    $dtltable= decrypt(addslashes(filter_input(INPUT_POST,'dtltable',FILTER_SANITIZE_STRING)));
    if($dtltable!='') {
        $relation=$dbrelations[$dtltable];
        foreach($relation as $name=>$data){
            if($data['reftable']==$tbl){
               $flds=$data['fields']; 
            };
        }
    }
// just doublechecking for string, no injection hanki panki
    $where=[sprintf('%s like %s',$masters[$tbl][0],QuotedStr('%'.$term.'%'))];
    if ($keys!==''){
        foreach ($keys as $key=>$val) $where[]=sprintf('%s=%s',$key,QuotedStr($val));
    }
    if ($term!==false){
       $qry->Close();
       
       $qry->SQL= isSqlQuery($tbl)?forkCriteria($tbl,join(' and ',$where)):sprintf('select * from %s where %s',$tbl,join(' and ',$where));
       $qry->Open();
       while ((!$qry->EOF())&&($qry->RecNo<$maxLookupRecords)){
           $ms=[];
           if ($flds) foreach($flds as $m=>$d) $ms[$m]=$qry->Values[$d];
           if ($flds)
               $response[]=['fields'=>$ms]+['label'=>$qry->Values[$masters[$tbl][0]]];
           else
               $response[]=['fields'=>$qry->KeyAssoc()]+['label'=>$qry->Values[$masters[$tbl][0]]];
           $qry->Next();
       }
       //$response[]=['status'=>$qry->SQL];
       echo json_encode($response);
    }
}
elseif ($act=='g'){
   //print_r($dbrelations);
   //print_r($refint);
   //echo '<div style="text-align: right;padding:4px"><button>Post</button><button>Cancel</button></div>';
   if (stripos($sqlqry, ' order by')!==false) $sqlqry= substr($sqlqry,0, stripos($sqlqry, ' order by'));
   $qry->Execute(stripos($sqlqry,' where ')!==false?$sqlqry.' and false':$sqlqry.' where false');
   $fieldLabels=[];
   foreach ($qry->DirectFields as $fld) $fieldLabels[$fld->orgtable.'.'.$fld->orgname]=$fld->name;
   echo '<br/><table class="pan-input">';
   //Check if there is a relation or a foreign key
   if (isset($refint)) {
       $lookupHidden=[];
        foreach ($refint as $lookName => $lookData) {// to categorise result just make sure the query returns 'category' column 
            $look->SQL = (isSqlQuery($lookData['reftable'])?$lookData['reftable']:'select * from ' . $lookData['reftable']);
            $look->Open();
            $localvals = [];
            foreach (array_keys($lookData['fields']) as $localfield) {
                if ($qry->RecordCount==0)//check if there is any default values if new record
                {
                    if (key_exists($tbl, $tabledefaults))
                        if (key_exists($localfield,$tabledefaults[$tbl]))
                                $localvals[]=$tabledefaults[$tbl][$localfield];
                }
                else 
                    $localvals[] = $qry->Values[$localfield];
            }
            $lookupHidden+=$lookData['fields'];
            if ($look->RecordCount<$maxLookupRecords) {
            }
            else if($look->LocateKey($localvals, array_values($lookData['fields']))){
                $resultval=$look->DirectValues[$lookData['result']];
            } else $resultval='';//else die('cannot find '. print_r($localvals,true));
            echo sprintf('<tr><td><label for="%s">%s</label></td><td>'
                    . '<%s class="dbcontrol" id="%s" data-lookupfields="%s" data-table="%s" onchange="uilookupchange(event)" %s>',
                    $lookName,$lookName,($look->RecordCount<$maxLookupRecords?'select':sprintf('input type="search" placeholder="Search for %s here" value="%s"', $lookName,htmlentities($resultval))),$lookName, join(',',array_keys($lookData['fields'])),encrypt(/*$look->SQL*/$lookData['reftable']),(array_intersect_key($qry->Keys,$lookData['fields'])==[]||$qry->RecordCount==0?'':/*'disabled'*/''));
            
            if($look->RecordCount<$maxLookupRecords) echo '<option data-lookupvalues="">[Select ...]</option>';
            while (!$look->EOF()&&$look->RecordCount<$maxLookupRecords) { 
                
                $foreignvals = [];
                foreach (array_values($lookData['fields']) as $foreignfield) {
                    $foreignvals[] = htmlentities($look->Values[$foreignfield]);
                }
                if ($localvals == $foreignvals)
                    printf('<option data-category="%s" data-lookupvalues="%s" selected>%s</option>',$look->Values['category'],join('||', $foreignvals) ,$look->Values[$lookData['result']]);
                else
                    printf('<option data-category="%s" data-lookupvalues="%s">%s</option>',$look->Values['category'],join('||', $foreignvals), $look->Values[$lookData['result']]);
                $look->Next();
            }
            echo ($look->RecordCount<$maxLookupRecords?'</select>':'').'</td></tr>';
            $look->close();
        }
    }
    // now go ahead with the rest of the columns
    
   foreach($qry->Fields as $field){
      $fieldattr=(array_key_exists($field->orgname, $controltype)?$controltype[$field->orgname]:'text');
      $defval=$tabledefaults[$tbl][$field->orgname];
      if (isset($defval)/*&&!isSqlQuery($defval)*/) $field->def=$defval;//else $field->def='';      
      switch($field->type){ 
         //TODO: render each type to appear differently starting from string type below
         case (MYSQLI_TYPE_CHAR):case (MYSQLI_TYPE_STRING):case (MYSQLI_TYPE_VAR_STRING):
              $dbcontrol=sprintf('<input %stype=%s class="dbcontrol" id="%s" value="%s" %s%s/>',
                    $field->def!==''?'placeholder="auto calculated if empty" ':'',
                    $fieldattr,
                    $field->orgname,
                    ($qry->RecordCount>0?$qry->Values[$field->name]:(isSqlQuery($field->def)?'':$field->def)),
                    (($qry->RecordCount>0)&&($field->flags & MYSQLI_PRI_KEY_FLAG)>0?/*' disabled'*/'':''),
                    (($field->flags & MYSQLI_NOT_NULL_FLAG)>0?' required':''));
               if ($fieldattr=='password') $dbcontrol.='&nbsp;&nbsp;&nbsp; Retype '.$dbcontrol;
            break;
         case (MYSQLI_TYPE_DATE):case (MYSQLI_TYPE_NEWDATE):case (MYSQLI_TYPE_DATETIME):
              $dbcontrol=sprintf('<input %stype="%s" class="dbcontrol datepicker" id="%s" value="%s" %s%s/>',
                    $field->def!==''?'placeholder="auto calculated if empty" ':'',
                    $fieldattr,
                    $field->orgname,
                    ($qry->RecordCount>0?$qry->Values[$field->name]:(isSqlQuery($field->def)?'':$field->def)),
                    (($qry->RecordCount>0)&&($field->flags & MYSQLI_PRI_KEY_FLAG)>0?/*' disabled'*/'':''),
                    (($field->flags & MYSQLI_NOT_NULL_FLAG)>0?' required':''));

            break;
         case (MYSQLI_TYPE_JSON):
             $dbcontrol='';
            break; 
         default:
            $dbcontrol=
                 sprintf('<input %stype="%s" class="dbcontrol" id="%s" value="%s" %s%s/>',
                    $field->def!==''?'placeholder="auto calculated if empty" ':'',
                    $fieldattr,
                    $field->orgname,
                    ($qry->RecordCount>0?$qry->Values[$field->name]:(isSqlQuery($field->def)?'':$field->def)),
                    (($qry->RecordCount>0)&&($field->flags & MYSQLI_PRI_KEY_FLAG)>0?/*' disabled'*/'':''),
                    ((($field->flags & MYSQLI_NOT_NULL_FLAG)>0)&&!isSqlQuery($field->def)?' required':''));
 
      }
      if ($dbcontrol!=='') printf('<tr %s><td><label for="%s">%s</label></td><td>%s</td></tr>' ,
              (array_key_exists($field->name,$lookupHidden)||$controltype[$field->orgname]=='hidden'? 'hidden':''),
              $field->orgname,
              (key_exists($field->table.'.'.$field->name, $fieldLabels)?(stripos($fieldLabels[$field->table.'.'.$field->name],'#')===false?$fieldLabels[$field->table.'.'.$field->name]:$field->name):$field->name),
              $dbcontrol);
   }
   echo '</table><br/>';
//check if there is any detail table related
   if (isset($detail)&&$qry->RecordCount>0){
       foreach($detail as $strDtl=>$val) {
         $joinlist=[];
         $joinvals=[];
         $listkeyfields=[];
         for($i=0;$i<count(array_keys($dbrelations[$strDtl]));$i++){
            if (isSqlQuery($val['list']))
            {
                $qry->Execute(forkCriteria($val['list'],'false'));
                $list=$qry->DirectFields[0]->orgtable;
            } else $list=$val['list'];
            if ($dbrelations[$strDtl][array_keys($dbrelations[$strDtl])[$i]]['reftable']==$list)
            { 
              foreach($dbrelations[$strDtl][array_keys($dbrelations[$strDtl])[$i]]['fields'] as $b=>$a){
                $listkeyfields[]=$a;
                $joinlist[]=sprintf('b.%s=a.%s',$b,$a);
              }
            }
         }
         foreach($val['fields'] as $m=>$d) $joinvals[]=sprintf('b.%s=%s',$d, QuotedStr($qry->Values[$m]));
         $dtl->SQL=sprintf('select a.%s,a.%s,b.%s %s from %s a left join %s b on (%s and %s)',join(',a.',$val['display']),join(',a.',$listkeyfields),join(',b.',$val['fields']),($val['columns']?',b.':'').join(',b.',(array) $val['columns']),(isSqlQuery($val['list'])?'('.$val['list'].')':$val['list']),$strDtl,join(' and ',$joinlist),join(' and ',$joinvals));
         //echo $dtl->SQL;
         $dtl->Open();
         printf('<fieldset data-table="%s">',encrypt($strDtl));
         printf('<legend>%s</legend><input placeholder="Filter" type="text" style="width:80%%; margin: 4px" oninput="filterfieldset(event);"/><div class="checkList"><div class="selected"></div>',$val['label']);
         while (!$dtl->EOF()){
           $display='';
           $addcolumns='';
           foreach($val['display'] as $field) $display.=$dtl->Values[$field].' ';
           $display=trim($display);
           $dtlvals=[];
           foreach($listkeyfields as $i=>$listkeyfield) $dtlvals[]=$dtl->Values[count($val['display'])+$i] ;//$dtlvals[]=$dtl->Values[$listkeyfield];
           foreach($val['fields'] as $masterfield=>$dtlfield) $dtlvals[]=$qry->Values[$masterfield];
           foreach((array) $val['columns'] as $column) {$addcolumns.=sprintf('<input type="text" id="%s" value="%s" placeholder="%s" style="float:right"/>',$column,$dtl->Values[$column],$column);}
           printf('<div class="checkElement"><input type="checkbox" value="%s" %s/>%s%s</div>',join('||',$dtlvals),($dtl->Values[array_values($val['fields'])[0]]?' checked':''),$display,$addcolumns) ;  
           $dtl->Next();
         }
         echo '</div></fieldset>';
         echo '<script>'
               . '$(".checkElement :checkbox").change(function(){if (this.checked) $(this).parent().appendTo($("fieldset div.selected")).find("[type=text]").show();else {$(this).parent().insertAfter($(".checkList>div.selected:eq(0)")).find("[type=text]").hide();}});' 
               . '$(".checkElement :checkbox").each(function(i,el){if (el.checked) $(el).parent().appendTo($("fieldset div.selected")).find("[type=text]").show()});'
         . '</script>';
       }
   }
    
} elseif ($act=='u') {
    $ids=$par[0];$vals=$par[1];
    if (count($ids)==count($vals)){
        foreach($vals as $k=>$val) if(strcmp($val,$qry->Values[$ids[$k]])!==0){//if there was a change in the field then include it in the update array,, otherwise ignore
            if(strcasecmp($controltype[$ids[$k]],'password')===0) $val= base64_encode(hex2bin(md5($val.strtolower($qry->Values['username'])))) ;
            $defval='';
            if (key_exists($ids[$k],(array)$tabledefaults[$tbl])) $defval=$tabledefaults[$tbl][$ids[$k]];
            if (isSqlQuery($defval)&&$val==''){
               for ($i=0;$i<count($ids);$i++) $defval=str_replace(':'.$ids[$i], QuotedStr($vals[$i]), $defval);//replace with values
               $qry->execute($defval);
               $defval=$qry->DirectValues[0];/// make auto calc procedure here
            }
            
            $sqlvals[$k]=($val==''?(($defval!='')?$defval:'null'):QuotedStr($val));
            $vals[$k]=($val==''?(($defval!='')?$defval:null):$val);
            $sqlset[]=$ids[$k].'='.$sqlvals[$k];
        }
        if(array_diff(array_keys($qry->Keys), $ids)!=[]) echo json_encode(['status'=>false,'data'=>'some record key info were not provided']);
        else// to be enhanced later
          if (array_diff($ids,array_intersect($ids, array_keys($fields)) )==[]){ 
            try { // real fieldnames no bullshift!@#
                //print_r(sprintf('update %s set %s where %s',$tbl,join(',',$sqlset),$key));exit;//debug
                if (isset($sqlset)) $q->Execute(sprintf('update %s set %s where %s',$tbl,join(',',$sqlset),$key));
                $r1=$q->Affected;
                // update detailed data if any below
                for ($i=2;$i<count($par);$i++){
                    $strDtl=decrypt($par[$i]['table']);
                    $dtlfields=[];
                    if (isSqlQuery($detail[$strDtl]['list'])) {
                        $qry->Execute(forkCriteria($detail[$strDtl]['list'],'false'));
                        $list=$qry->DirectFields[0]->orgtable;
                    } else 
                        $list=$detail[$strDtl]['list'];
                    for($l=0;$l<count(array_keys($dbrelations[$strDtl]));$l++){
                        if($dbrelations[$strDtl][array_keys($dbrelations[$strDtl])[$l]]['reftable']==$list){
                            foreach($dbrelations[$strDtl][array_keys($dbrelations[$strDtl])[$l]]['fields'] as $b=>$a)
                              $dtlfields[]=$b;
                        }
                    }
                    $strValues=$par[$i]['values'];
                    if ($strDtl){
                       $aWhere=[];$aRemove=[];
                       foreach($detail[$strDtl]['fields'] as $masfield=>$dtlfield) {
                           if (!in_array($dtlfield, $dtlfields)) {
                                 $dtlfields[]=$dtlfield;
                           }
                           else {$aRemove[]=count($dtlfields);}// remove dublicated columns othewise it will trigger SQL exception
                           $aWhere[]=$dtlfield.'='. QuotedStr($qry->Values[$masfield]);
                       }
                       $dtlfields=array_merge($dtlfields, (array)$detail[$strDtl]['columns']);//echo print_r($dtlfields);
                       if ($strValues){ 
                           foreach($strValues as $j=>$strValue){
                               $strValue=explode('||',$strValue);//print_r($strValue);
                               foreach($aRemove as $l) array_splice($strValue, $l,1);
                               foreach ($strValue as $k => $value) {$strValue[$k]=$value==''?'null':QuotedStr($value);}
                               $strValues[$j]='('.join(',',$strValue).')';
                               }
                       }
                       $q->Execute(sprintf('delete from %s where %s',$strDtl,join(' and ',$aWhere)));    
                       $r2=$q->Affected;
                       if($strValues) {$r3=$q->Execute(sprintf('insert into %s(%s) values %s',$strDtl,join(',',$dtlfields),join(',',$strValues)));$r3=$q->Affected;}    
                    }
                };
                if (isset($sqlset)&&$r1>0) {
                    foreach ($ids as $k=>$FieldName) {$qry->Values[$FieldName]=$vals[$k];$values[$FieldName]=$vals[$k];} //<--- hereeee!!!!!!
                    //$data= '<td hidden>'.$qry->strWhereKeys().'</td>';
                    $keyvals= $qry->KeyAssoc();
                    $qry->SQL= forkCriteria($sqlqry, 'false');
                    $qry->Table=$tbl;
                    $qry->Open();
                    //echo json_encode($qry->Keys);
                    
                    if (array_keys($qry->Keys)===array_keys($keyvals)){
                        foreach ($qry->Keys as $key=>$field){
                            if ($field->orgtable==$tbl){
                                $strKeyWhere.=' and '.sprintf('%s.%s',$field->table,$field->orgname).'='. QuotedStr($keyvals[$key]);
                            }
                        }
                    } else {
                        foreach ($qry->Keys as $key=>$field){
                            if ($field->orgtable==$tbl){
                                $strKeyWhere.=' and '.sprintf('%s.%s',$field->table,$field->orgname).(is_null($values[$key])?' is null':'='. QuotedStr($values[$key]));
                            }
                        }
                    }  
                    
                    $strKeyWhere=substr($strKeyWhere,4);//echo forkCriteria($sqlqry,$strKeyWhere);exit;
                    $qry->SQL= forkCriteria($sqlqry,$strKeyWhere);
                    $qry->Open();
                    $r=new HithReport($qry);
                    $r->editable=true;
                    //$data=json_encode($qry->Fields).' ,'.$qry->SQL;
                    $data=$r->PrintData(true);
                    echo json_encode(['status'=>true,'data'=>$data]);
                } else echo json_encode(['status'=>false,'data'=>$data]);
            } catch(Exception $e) {
                if ($_SESSION['authflag']==31) 
                    echo json_encode(['status'=>false,'data'=> strip_tags($e->getMessage())]);
                else
                    echo json_encode(['status'=>false,'data'=>'Error saving data, please make sure all fields are filled correctly.']);
                $q->Rollback();
            }
          }
          else echo json_encode(['status'=>false,'data'=>"No record changed.\n [no injection allowed!!.]"]);  
    }

} elseif ($act=='d') {
         $q->Execute(sprintf('delete from %s where %s',$tbl,$key));
         echo $q->Affected;
} elseif ($act=='i')
{
    $ids=$par[0];$vals=$par[1];
    
    if (count($ids)==count($vals)){
        foreach($vals as $k=>$val) {
            if(strcasecmp($controltype[$ids[$k]],'password')===0) $val= base64_encode(hex2bin(md5($val.strtolower($qry->Values['username'])))) ;
            $defval='';
            if (key_exists($ids[$k],(array)$tabledefaults[$tbl])) $defval=$tabledefaults[$tbl][$ids[$k]];
            if (isSqlQuery($defval)&&$val==''){
               for ($i=0;$i<count($ids);$i++) $defval=str_replace(':'.$ids[$i], QuotedStr($vals[$i]), $defval);//replace with values
                //do autocalc 
               $qry->execute($defval);
               $defval=$qry->DirectValues[0];/// make auto calc procedure here
            }
            $sqlvals[$k]=($val==''?(($defval!='')?$defval:'null'):QuotedStr($val));
            $vals[$k]=($val==''?(($defval!='')?$defval:null):$val);
        }
        if(array_diff(array_keys($qry->Keys), $ids)!=[]) 
                echo json_encode(['status'=>false,'data'=>'some record key info were not provided']);
        else// to be enhanced later
        if (array_diff($ids,array_intersect($ids, array_keys($fields)) )==[]) {try { // real fieldnames no bullshift!@#
            $q->Execute(sprintf('insert into %s(%s) values(%s)',$tbl,join(',',$ids),join(',',$sqlvals)));
            $r1=$q->Affected;
            //insert any detailed data if any below
//            for ($i=2;$i<count($par);$i++){
//                    $strDtl=decrypt($par[$i]['table']);
//                    $dtlfields=[];
//                    for($l=0;$l<count(array_keys($dbrelations[$strDtl]));$l++){
//                        if($dbrelations[$strDtl][array_keys($dbrelations[$strDtl])[$l]]['reftable']==$detail[$strDtl]['list']){
//                            foreach($dbrelations[$strDtl][array_keys($dbrelations[$strDtl])[$l]]['fields'] as $b=>$a)
//                              $dtlfields[]=$b;
//                        }
//                    }
//                    $strValues=$par[$i]['values'];
//                    if ($strDtl){
//                       if ($strValues) 
//                           foreach($strValues as $j=>$strValue){
//                               $strValue=explode('||',$strValue);
//                               foreach ($strValue as $k => $value) {$strValue[$k]=QuotedStr($value);}
//                               $strValues[$j]='('.join(',',$strValue).')';
//                           };
//                       foreach($detail[$strDtl]['fields'] as $masfield=>$dtlfield) $dtlfields[]=$dtlfield;
//                       if($strValues) {$r3=$q->Execute(sprintf('insert into %s(%s) values %s',$strDtl,join(',',$dtlfields),join(',',$strValues)));$r3=$q->Affected;}    
//                    }
//                };
            if ($r1>0) {
                foreach ($ids as $k=>$FieldName) {$qry->Values[$FieldName]= $vals[$k];$values[$FieldName]=$vals[$k];}
                //$data['key']=$qry->strWhereKeys();
                $keyvals= $qry->KeyAssoc();
                $qry->SQL= forkCriteria($sqlqry,'false');
                $qry->Table=$tbl;
                $qry->Open();
                //print_r($values);// debug
                //echo json_encode($qry->Keys);
                if (array_keys($qry->Keys)===array_keys($keyvals)){
                        foreach ($qry->Keys as $key=>$field){
                            if ($field->orgtable==$tbl){
                                $strKeyWhere.=' and '.sprintf('%s.%s',$field->table,$field->orgname).'='. QuotedStr($keyvals[$key]);
                            }
                        }
                } else {
                    foreach ($qry->Keys as $key=>$field){
                        if ($field->orgtable==$tbl){
                            $strKeyWhere.=' and '.sprintf('%s.%s',$field->table,$field->orgname).(is_null($values[$key])?' is null':'='. QuotedStr($values[$key]));
                        }
                    }
                }
                $strKeyWhere=substr($strKeyWhere,4);
                // echo forkCriteria($sqlqry,$strKeyWhere);// debug
                $qry->SQL= forkCriteria($sqlqry,$strKeyWhere);
                $qry->Open();
                $r=new HithReport($qry);
                $r->editable=true;
                //$data=json_encode($qry->Fields).' ,'.$qry->SQL;
                $data=$r->PrintData(true);
                echo json_encode(['status'=>true,'data'=>$data]);
            } else echo json_encode(['status'=>false,'data'=>$data]);
        } catch(Exception $exp) {
                if ($_SESSION['authflag']==31) 
                    echo json_encode(['status'=>false,'data'=> strip_tags($exp->getMessage())]);
                else {
                  echo json_encode(['status'=>false,'data'=>'Error saving data, please make sure all fields are filled correctly.']);
                
                }
                $q->Rollback();
        }} 
    }
}

$qry->close();
if($look) $look->Close();
if($dtl) $dtl->Close();
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>