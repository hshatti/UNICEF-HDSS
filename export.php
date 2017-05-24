<?php
  include_once './conn.php';
  include_once './dataset.php';
  include_once './definitions.php';
  include_once './phpexcel/Classes/PHPExcel.php';
  if ($_SESSION['username']=='') {
    header('Location: index.php');
    exit;
}
  
  $un=$_SESSION['username'];
  $YOB=$_SESSION['YOB'];
  $authflag=$_SESSION['YOB'];
//          $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
//          $cacheSettings = array( 'memoryCacheSize' => '8MB');
//          PHPExcel_Settings::setCacheStorageMethod($cacheMethod,$cacheSettings);

  $objExcel = new PHPExcel();
  $objExcel->getProperties()->setCreator("Haitham Shatti")
//							 ->setLastModifiedBy("HDSS")
//							 ->setTitle("Export")
//							 ->setSubject("HDSS")
//							 ->setDescription("")
//							 ->setKeywords("")
//							 ->setCategory("")
          ;
  
  $tbl= decrypt(filter_input(INPUT_GET,'tbl',FILTER_SANITIZE_STRING));
  if ($tbl==''/*&&isset($_GET['tbl'])*/){
      http_response_code (404);
      die();
  }
//  if ($tbl=='')
//    $tbl= decrypt(filter_input(INPUT_GET,'tbl',FILTER_SANITIZE_STRING));
  $fmt= filter_input(INPUT_GET,'fmt',FILTER_SANITIZE_STRING);
  $ActiveSheet=$objExcel->setActiveSheetIndex(0);
  $q->close();
  $q->SQL=key_exists($tbl,$dbexport)?$dbexport[$tbl]:sprintf('select * from %s',$tbl);
  $q->open();
  $col='A';
//echo '<pre>';
  for ($i=0;$i<$q->FieldCount;$i++){
    $ActiveSheet->setCellValue($col.'1',$q->Fields[$i]->name);
    $ActiveSheet->getStyle($col.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $ActiveSheet->getStyle($col.'1')->getFill()->getStartColor()->setARGB('FF0088FF');
    $ActiveSheet->getStyle($col.'1')->getFont()->getColor()->setARGB('FFFFFFFF');
    $ActiveSheet->getStyle($col.'1')->getFont()->setBold(true);
    $col++;
  }

//echo '</pre>';

  while (!$q->EOF()){
      $col='A';
      for ($i=0;$i<$q->FieldCount;$i++){
      $ActiveSheet->setCellValue($col.($q->RecNo+2),$q->Values[$i]);
//      $ActiveSheet->getStyle($col.($q->RecNo+2))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
//      $ActiveSheet->getStyle($col.($q->RecNo+2))->getFill()->getStartColor()->setARGB(($q->RecNo % 2 >0 ?'FFDCE6F1':'FFFFFFFF'));
//      $ActiveSheet->getStyle($col.($q->RecNo+2))->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
//      $ActiveSheet->getStyle($col.($q->RecNo+2))->getBorders()->getAllBorders()->getColor()->setARGB('FF0088FF');
      $col++;
    }
    $q->Next();
  }
  for ($c='A';$c!=$col;$c++) {
      $ActiveSheet->getColumnDimension($c)->setAutoSize(true);
  }
  $ActiveSheet->setAutoFilter($ActiveSheet->calculateWorksheetDimension());
  
  
  $fmtext=['Excel2007'=>'xlsx','Excel5'=>'xls','CSV'=>'csv','CSV'=>'csv'];
  
  if ($fmt=='PDF'){
        $ActiveSheet->setShowGridlines(false);
        $rendererName = PHPExcel_Settings::PDF_RENDERER_DOMPDF;
        $rendererLibrary = 'domPDF0.6.0beta3';
        $rendererLibraryPath = '/php/libraries/PDF/' . $rendererLibrary;

        if (!PHPExcel_Settings::setPdfRenderer(
		$rendererName,
		$rendererLibraryPath
	)) die ('Something went wrong rendering PDF file.');
  }
  
  header('Content-Type: application/octet-stream');
  header(sprintf('Content-Disposition: attachment; filename="%s.%s"',substr($tbl,2),$fmtext[$fmt]));
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

$objWriter = PHPExcel_IOFactory::createWriter($objExcel, $fmt);
$objWriter->save('php://output');
exit();
?>


  