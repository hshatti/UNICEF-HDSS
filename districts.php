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
if ($_SESSION['username']=='') {
    header('Location: index.php');
    exit;
}
?>        
        
    </head>
    <body><div id="wait"><div class="loader"></div></div><div id="mainpage">
        <header><div id="sidenavshow" onclick="openNav()">&#9776; </div>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/><div class="page-title">Districts</div>
            <div class="logout-form">
                <span class="fa fa-fw fa-2x fa-user-circle"></span> <span class="user-label"><?php echo htmlentities($fullname);?></span>
                <div class="user-menu">
                    <a ref="#" onclick="changePassword()">Change Password</a>
                    <a ref="#" onclick="logout()">Logout</a>
                </div>
            </div>
        </header>
            <div w3-include-html="sidenav.php"></div>
        <div class="page-container"><?PHP
$q=new Dataset($dblink);
$q->Table= 'fwdistrict'; //sprintf(sqlBrowseActivities, $_SESSION['username'],$_SESSION['authflag']);  
$q->SQL=sprintf('select g.Description as "#g" ,d.* from fwdistrict d join fwgovernorates g on g.Id=d.GovernorateId');
$q->Open();
$r=new HithReport($q);
//$r=new Table($q);
//$r->editable=true;
$r->Width='100%';
//$r->CheckList=true;
//$r->PageRows=50;
//$r->draw();
$r->DoGrid();
$q->close();
?></div>
        <footer>
                UNICEF Syria country office /IM
        </footer></div>
    </body>
    <script>
      $(document).ready(function(){
        $('#tblResult').holdOn();
        sidenav=$('div[w3-include-html]');
        $.get(sidenav.attr('w3-include-html'),function(data,statu){
                sidenav.html(data);
                $('#tblResult').holdOn('destroy');
        });
        initgraph();
      });
    </script>
</html>