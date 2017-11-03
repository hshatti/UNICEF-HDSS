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
    <body><div id="wait"><div class="loader"></div></div><div id="mainpage">
        <header><div id="sidenavshow" onclick="openNav()">&#9776; </div>
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/><div class="page-title">Items</div>
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
$q->Table= 'fwitems'; //sprintf(sqlBrowseActivities, $_SESSION['username'],$_SESSION['authflag']); 
$q->SQL=sprintf('select * from fwitems where SectorId in (0,%s)',join(',',$_SESSION['sectors']));
//alert($q->SQL);
//$q->SQL=sprintf('select a4.Location as "#g" ,s.Admin4Id ,s.SiteId,s.SiteType, s.Description,s.AltDesc, s.Catchment from fwsites s join fwadmin4 a4 on a4.Id=s.Admin4Id where s.CountryId=%s',QuotedStr($_SESSION['CountryId']));
$q->Open();
$r=new HithReport($q);
//$r=new Table($q);
$r->editable=$_SESSION['authflag']>2;
$r->Width='100%';
//$r->CheckList=true;
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
        sidenav=$('div[w3-include-html]');
        $.get(sidenav.attr('w3-include-html'),function(data,status){
            sidenav.html(data);
            $('#wait').fadeOut('fast');
        });
        initgraph();
      });
    </script>
</html>