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
            /*.BrowseActivities {background: lightgray;border-radius: 6px;margin:auto;left: 4px;right:4px}
            .dbgrid th, .dbgrid td {padding: 2px; box-shadow: inset 1px 1px 0 white}*/
            
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
            <img class="hlogo" src="./img/UNICEF_logo_white.png"/>
            <form action="conn.php" method="POST" class="logout-form">
                <input type="hidden" name="logout" value="1"/>
                Welcome <span class="user-label"><?php echo htmlentities($fullname);?></span> <input type="submit" class="logout-button" value="Logout" />
            </form>
        </header>
            <div w3-include-html="sidenav.php"></div>
        <div class="page-container"><?PHP
$q=new Dataset($dblink);
$q->Table='fwactivitylist';
$q->SQL=sprintf('select c.Description as \'#gOutcome\','
        . 'o.Description \'#gOutput\',al.YOB,al.ProgramId,al.OutcomeId,al.OutputId,al.ActivityId, al.ActivityCode, al.Description,al.AltDesc from fwactivitylist al '
        . ' join fwOutputs o on (o.yob=al.yob and o.ProgramId=al.ProgramId and o.OutcomeId=al.OutcomeId and o.OutputId=al.OutputId)'
        . ' join fwOutcomes c on (c.YOB=al.YOB and c.ProgramId=al.ProgramId and c.OutcomeId=al.OutcomeId) where al.YOB=%s and al.SectorId in (%s)',$_SESSION['YOB'], join(',',(array) $_SESSION['sectors']));  
$q->Open();
$r=new HithReport($q);
//$r=new Table ($q);
$r->editable=$_SESSION['authflag']>2;
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
            sidenav=$('div[w3-include-html]');
            $.get(sidenav.attr('w3-include-html'),function(data,status){
                sidenav.html(data);
                $('#wait').fadeOut('fast');
            });
            initgraph();
        });
    </script>
</html>