<?php
        include_once './conn.php';
        include_once './definitions.php';
        include_once './dataset.php';
if ($_SESSION['username']=='') {
    header('Location: index.php');
    exit;
}
  $sitetypes=$_POST['sitetypes'];
  //$SectorId= $_POST['sectorid'];
  $ProgramId= $_POST['programid'];
  $download=$_GET['download'];
  $triggerfile='../getexcel';
  $errorfile='../geterror';
  $timeout=40;
  //foreach ($sitetypes as $i=>$sitetype)$sitetypes[$i]= QuotedStr($sitetype);
        //echo "<pre>please wait ....\n"  ;   
  //printf('%s %s <br>',$SectorId,$sitetypes);
  $q->Execute(sprintf('select ProgramId from fwprograms where YOB=%s and Description=%s',$_SESSION['YOB'], QuotedStr($ProgramId)));
  $ProgramId=$q->DirectValues[0];
  if (isset($ProgramId)&&isset($sitetypes)) {
      //$output=[];
      if (file_exists($triggerfile)){
          $success='Please wait';
      }
      else 
      {
          $tmpfile= tempnam(sys_get_temp_dir(), '4ws'); 
          $getexcel=sprintf("o=%s.xlsm\nc=%s\ny=%s\ns=%s\n%s\np=%s", 
                  $tmpfile,$_SESSION['CountryId'],$_SESSION['YOB'],/*join(',',$sectors)*/ $SectorId,
                  isset($sitetypes)?'t='.$sitetypes.'':'',$ProgramId);
          file_put_contents($triggerfile, $getexcel);
          while (file_exists($triggerfile)&&($i<$timeout)) {
              sleep(1);$i++;
          }
          if (file_exists($triggerfile)) unlink($triggerfile);
          if (file_exists($errorfile)) {
            unlink($errorfile);
            $success='Error';
          }
          else $success=$i<$timeout?'[DONE]':'Time Out';
          $_SESSION['fileready']=$tmpfile;
          
      }
      echo json_encode(['log'=>$success,'file'=>'4ws']);
      exit();
  }
  //echo $cmd.'</pre>';
  elseif ($download){  
//      echo $download;
//      exit();
      header('Content-Type: application/octet-stream');
      header(sprintf('Content-Disposition: attachment; filename="Smart 4Ws %s-%s.xlsm"',$_SESSION['CountryId'],$_SESSION['username']));
      header('Content-Transfer-Encoding: binary');
      header('Cache-Control: max-age=0, must-revalidate');
    //   If you're serving to IE 9, then the following may be needed
    //  header('Cache-Control: max-age=1');
    //
    //// If you're serving to IE over SSL, then the following may be needed
      header ('Expires: 0'); // Date in the past
    //  header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
    //  header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
      header ('Pragma: no-cache'); // HTTP/1.0
      copy($_SESSION['fileready'].'.xlsm', 'php://output');
      exit();
  }
  
?>

<fieldset id="sitetypes" style="width:400px">
    <legend>Include Site Types</legend>
    <?php 
      $q->SQL='select Description from fwsitetypes';
      $q->Open();
       while (!$q->EOF()){
           printf('<input type="checkbox" value="%s" checked>%s<br/>',$q->Values[0],$q->Values[0]);
           $q->Next();
       }
    ?>
</fieldset>
<br>
Sector : <select id="programs">
    <?php 
      $q->SQL='select p.Description from fwprograms p join fwusersector u on u.SectorId=p.SectorId where p.YOB='.$_SESSION['YOB'].' and u.username='.QuotedStr($_SESSION['username']);
      $q->Open(); 
      while (!$q->EOF()){
           printf('<option>%s</option>',$q->Values[0]);
           $q->Next();
       }
    ?>
</select><br><br>
<button id="btnGen" onclick="doGenerateTemplate(event);" class="btnNormal">Generate Template</button>
<script>
  function doGenerateTemplate(e){
      if ($('#sitetypes :checked').length==0){
          return;
      }
      if (e.target.innerHTML==='Download 4Ws')
      {
            console.log($(e.target).data('file'));
            location.href=$(e.target).data('file');
            return;
      }
      e.target.innerHTML='Generating, hold on...';
      e.target.disabled=true;
      $.post('fwgentemplate.php',{sitetypes:getValues($('#sitetypes :checkbox:checked')).join(','),programid:$('select#programs').val()},function(data,status){
        e.target.disabled=false;
        res=JSON.parse(data);
        console.log(res.log);
        if (res.log.includes('DONE')) {
           e.target.innerHTML='Download 4Ws'; 
        } else {console.log(res.log);e.target.innerHTML='Something went wrong,Retry?';};
        $(e.target).data('file','fwgentemplate.php?download='+res.file);
      });
  } 
</script>
