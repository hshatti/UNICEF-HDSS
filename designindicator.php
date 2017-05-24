<?php
include_once 'conn.php';
include_once 'dataset.php';

$tbl= decrypt(addslashes($_POST['tbl']));
$key= decrypt(addslashes($_POST['key']));
$act=$_POST['act'];
$data= ($_POST['data']);
function takeSum($items){
  return (''.join(" + ", $items).'');
}  
function takeMax($items){
  return ('greatest('.join(",", $items).')');
} 
function takeMin($items){
  return ('least('.join(",", $items).')');
}
function takeCode($code){
  global $takeCodeCol;
  global $q;
  $q->execute(sprintf('select * from fwactivitylist where ActivityCode=%s', QuotedStr($code)));
  //print_r($q->DirectValues);
  return ($q->DirectValues['isSum']==1?'sum':'max').'(if(ActivityCode='.QuotedStr($code).','.$takeCodeCol.',0))';
}  


function operToSQL($oper,$column='qty') {
  $activityCodes=(array) $oper->a;
  $activityOpers=(array) $oper->i;
  global $takeCodeCol;
  if (!isset($takeCodeCol)) $takeCodeCol=$column;
  if(isset($activityCodes)) $sqlActivityArray=array_map('takeCode',$activityCodes);
  if(isset($activityOpers)) $sqlOperArray=array_map('operToSQL',$activityOpers);// recursive map to self function
  //echo '<pre>';
  $sqlArray=array_merge($sqlActivityArray,$sqlOperArray);
  //echo '</pre>';
  switch ($oper->opr) {
      case 'sum':
          $return= takeSum($sqlArray);
          break;
      case 'max':
          $return= takeMax($sqlArray);
          break;
      case 'min':
          $return= takeMin($sqlArray);
          break;
      default:
          $return= takeSum($sqlArray);
          break;
  }
  //unset($GLOBALS['col']);// just in case to avoid conflicts with possible other variables in the same name
  return $return;
}

function getIndicator($IndicatorId){
    
    global $q;
    $q->SQL=sprintf('select * from fwindicatormaster where yob=%d and IndicatorId=%d',$_SESSION['YOB'],intval($IndicatorId));
    $q->open();
    return operToSQL(json_decode($q->Values['calcOper']));
}

function forkIndicators($sqlText){
   $indicatorKeyword=':indicator';
   $a1= stripos($sqlText, $indicatorKeyword);
   if ($a1!==false) $a2=stripos(str_replace(["\n","\r"], [' ',' '],$sqlText),' ',$a1);
   while ($a1!==false) { 
       $b=substr($sqlText, $a1+ strlen($indicatorKeyword),$a2+1-($a1+ strlen($indicatorKeyword)));
       $c=getIndicator($b);if (trim($c)=='') $c=' null ';
       $sqlText= str_ireplace($indicatorKeyword.$b, $c, $sqlText);
       $a1= stripos($sqlText, $indicatorKeyword);
       if ($a1!==false) $a2=stripos(str_replace(["\n","\r"], [' ',' '],$sqlText),' ',$a1);
   }
   return $sqlText;
}

if($act=='g'){
    $q->execute(sprintf('select * from fwindicatormaster where %s',$key));
    $q->SQL=sprintf('select i.ActivityCode,al.Description from (select YOB,ActivityCode from fwindicatordtl where %s) i join fwActivityList al using (YOB,ActivityCode)',$key);
    $q->open();$a=[];
    while (!$q->EOF()){
        array_push($a, ["code"=>$q->Values[0],"activity"=>$q->Values[1]]);
        $q->Next();
    }
    echo json_encode(['calcOper'=> (json_decode($q->DirectValues['calcOper'])),'activities'=>$a]);
} elseif($act=='u'){
    $q->execute(sprintf('update fwindicatormaster set calcOper=%s where %s', QuotedStr(json_encode($data)),$key));
    printf('[%d] rows affected',$q->Affected);
}
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

