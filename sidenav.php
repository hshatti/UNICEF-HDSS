<?php
include_once 'conn.php';
?>
<head>
       <link href="css/fa/css/font-awesome.min.css" rel="stylesheet" >
</head>
<div id="sidenav" class="sidenav">
  <a href="javascript:void(0)" class="closeNav" onclick="closeNav()">&times;</a>
  <input type=search class="input-search" placeholder="Search">
  <div class="dropdown">
      <div class="dropbtn" onclick="location.href='./program.php'"><span class="fa fa-fw fa-pull-left fa-sitemap"></span>Program</div>&Gt;
      <div class="dropdown-content shadow">
        <a href="outcomes.php">Outcomes</a>
        <a href="outputs.php">Outputs</a>
        <a href="activities.php">Activities</a>
        <a href="items.php">Items</a>
      </div>
  </div>
  <div class="dropdown">
      <div class="dropbtn" onclick="location.href='./indicators.php'"><span class="fa fa-fw fa-pull-left fa-dashboard"></span>Indicators</div>&Gt;
      <div class="dropdown-content shadow">
        <a href="indicators.php">Design</a>
        <a href="indicatordtl.php">By Activity</a>
      </div>
  </div><div class="dropdown">
      <div class="dropbtn"><span class="fa fa-fw fa-pull-left fa-map"></span>Locations</div>&Gt;
      <div class="dropdown-content shadow">
        <a href="governorates.php">Governorates</a>
        <a href="districts.php">Districts</a>
        <a href="subdistricts.php">Sub Districts</a>
        <a href="communities.php">Communities</a>
        <a href="neighbourhoods.php">Neighbourhoods</a>
        <a href="sites.php">Sites</a>
        <div><a href="locationstatus.php">Access Status</a></div>
      </div>
  </div>
  <div class="dropdown">
      <div class="dropbtn"><span class="fa fa-fw fa-pull-left fa-file-excel-o"></span>Spreadsheet Files</div>&Gt;
      <div class="dropdown-content shadow">
        <a href="fwimport.php">[Smart 4Ws] Import file</a>
        <a href="#" onclick="$('.page-container').load('fwgentemplate.php');">[Smart 4Ws] Download Template</a>
      </div>
  </div>

  <a href="partners.php"><span class="fa fa-fw fa-pull-left fa-handshake-o"></span>Partners</a>
  <div class="dropdown">
      <div class="dropbtn"><span class="fa fa-fw fa-pull-left fa-users"></span>User Rights</div>&Gt;
      <div class="dropdown-content shadow">
          <a href="users.php">Users</a>
          <a href="accessgroups.php">Access Groups</a>
      </div>
  </div>
  <div class="dropdown">
      <div class="dropbtn"><span class="fa fa-fw fa-pull-left fa-table"></span>Reports</div>&Gt;
      <div class="dropdown-content shadow">
          <a href="reportgroups.php">Report group</a>
          <?php if ($_SESSION['authflag']>2) echo '<a href="indicatorcaching.php">Published Figures</a>';?>
          <a href="main.php">Reports</a>
      </div>
  </div>
  <?php echo '<script> var strUserHash='. QuotedStr(encrypt(strtolower($_SESSION['username']))).', strUser='. QuotedStr(strtolower($_SESSION['username'])).';</script>'?>
</div>