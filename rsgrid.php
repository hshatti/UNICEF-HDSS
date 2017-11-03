<?php
include_once './conn.php';
include_once './dataset.php';
include_once './definitions.php';
include_once './designindicator.php';


function ParseParamForm($dblink,$strSQL,$noPrint=false,$repname='',$params=null){
    $k=0;
    $slRep=array();
    //$strSQL=str_replace(["\n","\r"], [' ',' '], $strSQL);
    $strSQL1=strtolower($strSQL);
    $strstart=strpos(str_replace(["\r","\n"],[' ',' '],$strSQL1),' from ')+6;
    if ($strstart<strlen($strSQL)) do {
        $i=strpos($strSQL,'{',$strstart);
        $j=strpos($strSQL,'}',$strstart);
        if ($j>$i){
            $slRep[]= substr($strSQL,$i+1,$j-$i-1);
            $strSQL=substr_replace($strSQL,':'.$k.':',$i, $j-$i+1);
        }
    $k++;
    }
    while ($j>$i);
    $slRepCount=count($slRep);
    if (!$noPrint) for ($i=0;$i<$slRepCount;$i++){
        $line=$slRep[$i];
        $line=str_ireplace(":yob", $_SESSION['YOB'], $line);
        $line=str_ireplace(":authflag", $_SESSION['authflag'], $line);
        $line=str_ireplace(":countryid", QuotedStr($_SESSION['CountryId']), $line);
        $line=str_ireplace(":sectors", join(',',$_SESSION['sectors']), $line);
        $line=str_ireplace(":username", QuotedStr($_SESSION['username']), $line);
        try {
            switch (strtolower($slRep[$i][0])) {
                case 's':
                    delete($line,0,1);
                    $label=strstr($line,'@',TRUE);
                    if ($repname!='') $r=$repname ; else $r=rand();
                    echo "<label  style=\"float:left;color:#0070c0;font:bold 1em 'Roboto',sans-serif\">$label</label>";
                    echo "<select class=\"ComboBox\" name=\"sel\" style=\"min-width : 150px\">";
                    $label=strstr($line,'@');
                    Delete($label, 0, 1);
                    $useKey=$label[0]=='@';
                    if ($useKey) delete($label,0,1);
                    if (strPos(strtoupper($label),'SELECT')===0){
                        if (!isset($l)) $l=new dataset($dblink);
                        $l->Close();
                        $l->SQL=$label;
                        $l->Open();
                        while (!$l->EOF()){
                            $item=$l->Values[0];
                            if ($useKey) $key=strstr($item,' ',true);else $key=$item;
                            echo "<option value=\"$key\"".($params[$i]===$item||$key.' '.$params[$i]===$item?' selected':'').">$item</option>";
                            $l->Next();
                        }
                    }else{
                        $label=$label.'|';
                        while(strstr($label,'|',true)){
                          $item=strstr($label,'|',true);
                          if ($useKey) $key=strstr($item,' ',true);else $key=$item;
                          echo  "<option value=\"$key\"".($params[$i]===$item||$key.' '.$params[$i]===$item?' selected':'').">$item</option>";
                          Delete($label,0,  strpos($label, '|')+1);
                        }
                    }
                    echo '</select>';

                    break;
                case 'd':
                    delete($line,0,1);
                    $label=strstr($line,'@',TRUE);
                    echo "<label style=\"float:left;color:#0070c0;font:bold 1em 'Roboto',sans-serif\">$label</label><div class=\"DateRange\" style=\"text-align: right\">";
                    $label=strstr($line,'@');
                    Delete($label, 0, 1);
                    if (strtoupper($label)=='YTD'){
                        $fr=date('1/1/Y');
                        $to=date('m/d/Y');
                        echo "from <input type=\"text\" class=\"dateFrom\" style=\"width : 100px\" value=\"$fr\"><br/>";
                        echo "to <input type=\"text\" class=\"dateTo\" style=\"width : 100px\" value=\"$to\">";
                    }
                    elseif(strtoupper($label)=='MTD'){
                        $fr=date('m/1/Y');
                        $to=date('m/d/Y');
                        echo "from <input type=\"text\" class=\"dateFrom\" style=\"width : 100px\" value=\"$fr\"><br/>";
                        echo "to <input type=\"text\" class=\"dateTo\" style=\"width : 100px\" value=\"$to\">";
                    }

                    echo '</div>';

                    break;
                case 'c':
                    delete($line,0,1);
                    $label=strstr($line,'@',TRUE);
                    echo '<fieldset class="CheckList" style="border: black 1px solid;text-align: left; max-height:350px; overflow:auto">';
                    echo "<legend>$label</legend>";
                    echo "<input type=\"checkbox\" id=\"SelectAll\"/>[Select All]<br/><hr>";
                    $label=strstr($line,'@');
                    Delete($label, 0, 1);
                    $useKey=$label[0]=='@';
                    if ($useKey) delete($label,0,1);
                    if (strPos(strtoupper($label),'SELECT')===0){
                        if (!isset($l)) $l=new dataset($dblink);
                        $l->Close();
                        $l->SQL=$label;
                        $l->Open();
                        while (!$l->EOF()){
                            $item=$l->Values[0];
                            if ($useKey) $key=strstr($item,' ',true);else $key=$item;
                            echo "<input class=\"CheckBox\" type=\"checkbox\" checked value=\"$key\"/><span>$item</span><br/>";
                            $l->Next();
                    }
                    }else{
                        $label=$label.'|';
                        while(strstr($label,'|',true)){
                          $item=strstr($label,'|',true);
                          if ($useKey) $key=strstr($item,' ',true);else $key=$item;
                          echo  "<input class=\"CheckBox\" type=\"checkbox\" checked value=\"$key\"/><span>$item</span><br/>";
                          Delete($label,0,  strpos($label, '|')+1);
                        }
                    }
                    echo '</fieldset>';

                    break;
                case 'n':

                    break;
                default:

                    break;
            }
            
        }
        catch (Exception $e) {
          if ($_SESSION['authflag']==31) echo $e->getTraceAsString();  
        }
        echo "<br/>";
    }
    return(array($slRep,$strSQL));
}



//if (!isset($q)) $q=new Dataset($dblink);
$rep =  $_POST['rep'];
$rep=  addslashes($rep);
$isdesign=$_POST['des'];
if (!isset($dsRep)) $dsRep=new Dataset($dblink);
$par=$_POST['par'];
$savesql=$_POST['sql'];
$repnew=$_POST['repnew'];
$repdefault=$_POST['repdefault'];
$repdefault=addslashes($repdefault);
if ($repdefault) $repdefault='T';
$repdel=$_POST['repdel'];
if (isset($_POST['rep'])) {// params to request a specific report 
//    print_r($_POST); //TODO :Debug params here;
    $dsRep->SQL="select * from genrep where repname='$rep' and YOB=$YOB";
}    
else  $dsRep->SQL="select * from genrep where def='T'";// otherwise choose default report
$dsRep->Open();
if ($dsRep->RecordCount==0){
   $dsRep->SQL="select * from genrep where YOB=$YOB";
   $dsRep->Open();
}
if ($isdesign) 
{     // when adding new report                    
  $authflag=$_SESSION['authflag'];
  //echo $authflag.'<br>';
  if ($authflag>2)// Country SuperUser or sysAdmin  
    if (isset($repdel)) { // deleting report?
        $dsRep->Execute("delete from genrep where YOB='$YOB' and repname='$rep'");
        echo json_encode(array('msg'=>'['.$dsRep->Affected.'] Report deleted.'));
    }
    elseif (isset($repnew)) try //inserting report?
    {
        $dsRep->Execute("update genrep set Def=null where Def='T'");
        $repnew=  addcslashes($repnew,"'");
        $dsRep->Execute("insert into genrep(YOB,RepName,Def,SQLText,Owner) values ($YOB,'$repnew','T','  from ',"+QuotedStr($_SESSION['username'])+")");
//        $dsRep->Execute(sprintf("replace into fwreportsector values(%d,%d,%s,%s)",
//                0,$YOB,QuotedStr($_SESSION['CountryId']),QuotedStr($repnew))); 
        echo json_encode(array('msg'=>'['.$dsRep->Affected.'] Report added.'));
    } catch (Exception $e) {
        
        echo json_encode(array('msg'=>'['.$e->getMessage().'] Report added.'));  
    }
    elseif(isset($savesql)){ //when updating report SQL
        if(strtolower($repdefault)=='t')
          $dsRep->Execute("update genrep set Def=null where Def='T'");
        $s="update genrep set SQLText='".addcslashes($savesql,"'")."',Def='$repdefault' where Repname='$rep' and YOB=$YOB";
        $dsRep->Execute($s);
        $dsRep->Commit();
        ParseParamForm($dblink, $savesql);
    } 
    else //getter, get report sql design
      echo json_encode (array( 'def'=>$dsRep->Values['Def'],'sql'=>$dsRep->Values['SQLText']));
    
} else 
{
    $a=  ParseParamForm($dblink, $dsRep->Values['SQLText'],true);
    $sqltext=$a[1];
    $slRep=$a[0];
    for ($i=0;$i<count($slRep);$i++)
      switch ($slRep[$i][0]){
       case'd':
          $fr=  addslashes($par[$i][0]);
          $to=  addslashes($par[$i][1]);
          $val="'$fr' and '$to'";
            $sqltext=str_replace(":$i:", $val, $sqltext);
         break;
      case 'c':
          for ($j=0;$j<count($par[$i]);$j++)
             $par[$i][$j]=  addslashes ($par[$i][$j]);
          if (isset($par[$i])) $val=  "'".implode("','", $par[$i])."'";
          $sqltext=str_replace(":$i:", $val, $sqltext);
         break;
      case 's':
          $val= "'".addslashes($par[$i])."'";
          $sqltext=str_replace(":$i:", $val, $sqltext);
         break;
      case 'n':
        $fr=  addslashes($par[$i][0]);
        $to=  addslashes($par[$i][1]);
        $val="'$fr' and '$to'";
        $sqltext=str_replace(":$i:", $val, $sqltext);
          break;
      default:;
        
    }
//echo "<p>$sqltext</p>";
    $sqltext=str_ireplace(":yob", $YOB.' ', $sqltext);
    $sqltext=str_ireplace(":countryid", QuotedStr($_SESSION['CountryId']).' ', $sqltext);
    $sqltext=str_ireplace(":authflag", QuotedStr($_SESSION['authflag']).' ', $sqltext);
    $sqltext=str_ireplace(":sectors", join(',',$_SESSION['sectors']), $sqltext);
    $sqltext=str_ireplace(":username", QuotedStr($_SESSION['username']).' ', $sqltext);
    $q->SQL= forkIndicators($sqltext);
    if ($_SESSION['authflag']==31) {
        writelog($q->SQL);
    }
    if (isset($par)){
        $q->Open();
        $grid = new HithReport($q);
        $grid->DataHotTrackCSS='hottrack';
        
        $grid->DoGrid();
        //dbgrid($q,false);
//    echo '<script>'; // Mofify SQL
//    echo '$("div#design textarea").html("'.  strtr($dsRep->Values['SQLText'],array("\n"=>'\n',"\r"=>'\r','"'=>'\"')).'").data("'.addslashes($rep).'")';
//    echo "</script>";
    
    }
}
?>