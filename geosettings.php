<?php
include_once './geo.php';
define('sqlMapAdmin1','select pcode, Description, st_AsText(Polygon) Polygon, st_y(st_centroid(Polygon)) Latitude, st_x(st_centroid(Polygon)) Longitude from fwGovernorates');
define('sqlMapAdmin2','select pcode, Description, st_AsText(Polygon) Polygon, st_y(st_centroid(Polygon)) Latitude, st_x(st_centroid(Polygon)) Longitude from fwDistrict');
define('sqlMapReachBySection','select ab.CountryId,a4.PCODE,a4.Longitude,a4.Latitude,a4.Location, group_concat(al.Description) Activities  from fwactivitybeneficiaries ab join fwadmin4 a4 on(a4.Id=ab.Admin4Id) join fwprograms p using (yob,programid) join fwactivitylist al using (yob,ActivityCode) 
                 where ab.yob=%d and %s
                 group by ab.CountryId,a4.PCODE');
//print_r($_POST);exit;
if ($_POST['act']=='post'&&isset($_POST['field'])){
    $geofile=$_SESSION['geofile'];
    $geoJSON= json_decode(file_get_contents($geofile),JSON_OBJECT_AS_ARRAY);
    $geoFeatures=$geoJSON['features'];
    $i=0;
    foreach ($geoFeatures as $geoFeature){
        $geoPCODE= $geoFeature['properties'][$_POST['field']];
        $geo=geoPHP::load(json_encode($geoFeature),'geojson');
        if (strlen($geoPCODE)==8){ // it's a subdistrict
            $table='fwadmin3';
        }elseif(strlen($geoPCODE)==6){// it's a district
            $table='fwdistrict';
        }elseif(strlen($geoPCODE)==4){// it's a governorate
            $table='fwgovernorates';
        }elseif(strlen($geoPCODE)==5){//it's a pcode
            switch (strtoupper(substr($geoPCODE,0,1))){
            case 'C':
               $table='fwadmin4';
               break;
            case 'N':
               $table='fwadmin5';
               break;
            }
        }
        if(isset($table)) {
            if ($table!='fwadmin4')
                $q->execute(sprintf('update %s set Polygon=ST_geomFromText(%s) where pcode=%s',$table,QuotedStr($geo->out('wkt')),QuotedStr($geoPCODE)));
            else
                $q->execute(sprintf('update %s set Polygon=ST_geomFromText(%s) where pcode=%s',$table,QuotedStr($geo->out('wkt')),QuotedStr($geoPCODE)));
            $i=$i+$q->Affected;
        }
    //    printf('<div class="message info">[%s] executed </div>',sprintf('update %s set Coordinate=%s where pcode=%s',$table,$geo->out('wkt'),QuotedStr($geoPCODE)));

    }
    printf('<div class="message info">[%d] records were updated </div>',$i);
    
}
elseif ($_POST['act']=='upload'&&count($_FILES)>0) {
    $geofile=$_FILES['geofile']['tmp_name'];

    $_SESSION['geofile']= tempnam(sys_get_temp_dir(), '').'.'.pathinfo($_FILES['geofile']['name'],PATHINFO_EXTENSION);
//    echo '<pre>';
//        print_r($_FILES);
//    echo 'from: '.$geofile."\n to: ".$_SESSION['geofile'];
//    echo '</pre>';
    copy($geofile,$_SESSION['geofile']);
    $geoJSON= json_decode(file_get_contents($geofile));
    $geoFeatures=$geoJSON->features;
    $geoFeature=$geoFeatures[0];
    $geoProperties=$geoFeature->properties;
    echo 'Match PCODE Field<br/><select id="pcodeField">';
    foreach ($geoProperties as $field=>$value){
        echo '<option>'.$field.'</option>';
    }
    echo '</select><br/><br/><button onclick="applyGeometry()" class="btnNormal">Apply Geometry</button>';
}
if($_POST!==[])
    exit();

?>
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
?>        
    </head>
    <body>
<!--        <div id="wait"><div class="loader">
        </div></div>-->
        <div id="mainpage">
        <header><div id="sidenavshow" onclick="openNav()">&#9776; </div>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/><div class="page-title">Geo Settings</div>
            <div class="logout-form">
                <span class="fa fa-fw fa-2x fa-user-circle"></span> <span class="user-label"><?php echo htmlentities($fullname);?></span>
                <div class="user-menu">
                    <a ref="#" onclick="changePassword()">Change Password</a>
                    <a ref="#" onclick="logout()">Logout</a>
                </div>
            </div>        </header>
            <div w3-include-html="sidenav.php"></div>
        <div class="page-container">
            <!--<form name="geofile" id="geofile" method="post" action="<?php echo __FILE__;?>">-->
            <input type="file" id="jsonfile" accept=".geojson" onchange="doGeoUpdate()"/>
            <!--</form>-->
            <select id="cmbAdmin">
                <option>Admin1</option>
                <option>Admin2</option>
                <option>Admin3</option>
                <option>Admin4</option>
            </select> <button id="btnRenderMap" class="btnNormal" onclick="btnRenderMap()">Render Map</button><br><progress hidden></progress>
<?PHP
//$q=new Dataset($dblink);
//$q->Table= 'fwitems'; //sprintf(sqlBrowseActivities, $_SESSION['username'],$_SESSION['authflag']); 
//$q->SQL=sprintf('select * from fwitems where SectorId in (0,%s)',join(',',$_SESSION['sectors']));
//alert($q->SQL);
//$q->SQL=sprintf('select a4.Location as "#g" ,s.Admin4Id ,s.SiteId,s.SiteType, s.Description,s.AltDesc, s.Catchment from fwsites s join fwadmin4 a4 on a4.Id=s.Admin4Id where s.CountryId=%s',QuotedStr($_SESSION['CountryId']));
//$q->Open();
//$r=new HithReport($q);
//$r=new Table($q);
//$r->editable=$_SESSION['authflag']>2;
//$r->Width='100%';
//$r->CheckList=true;
//$r->draw();
//$r->DoGrid();
//$q->close();
?><br/><br/><div id="pcodeColumn"></div></div>
        <footer>
                UNICEF Syria country office /IM
        </footer></div>
    </body>
    <script>
      function btnRenderMap(){
          $.post('fetchdata.php',{act:'drawlayers',map:[$('#cmbAdmin').val()]},function(data,status){
                console.log(data);
                $('#pcodeColumn').html(data)//.holdOn('destroy');  
          });
      }
      function applyGeometry(){
        $.post('geosettings.php',{act:'post',field:$('#pcodeField').val()},function(data,status){
            $('#pcodeColumn').html(data);
        });  
      }  
      function doGeoUpdate(){
        
        xhr = new XMLHttpRequest();
        xhr.open('POST','geosettings.php');
        xhr.upload.onprogress=function(p){
            prog=$('progress[hidden]')[0];
            prog.max=p.total;
            prog.value=p.loaded;
        }
        xhr.onload=function(){
          $('progress').hide();
          $('#geofile input[type=file]').val('');
          if (xhr.readyState==4 && xhr.status==200 ){
             $('#pcodeColumn').html(xhr.responseText);
          }  
        }
        frm=new FormData();
        frm.append('act','upload');
        frm.append('geofile',document.querySelector('#jsonfile').files[0]);
//       frm.forEach(function(a,b,c){console.log(a,b,c);});
        xhr.send(frm);
        $('progress').show(); 
      }
      $(document).ready(function(){
        $('#tblResult').holdOn();
        sidenav=$('div[w3-include-html]');
        $.get(sidenav.attr('w3-include-html'),function(data,status){
            sidenav.html(data);
            $('#tblResult').holdOn('destroy');
        });
        initgraph();
      });
    </script>
</html>