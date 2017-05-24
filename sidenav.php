<?php
include_once 'conn.php';
?>
<div id="sidenav" class="sidenav">
  <a href="javascript:void(0)" class="closeNav" onclick="closeNav()">&times;</a>
  <input type=search class="input-search" placeholder="Search">
  <div class="dropdown">
      <div class="dropbtn" onclick="location.href='./program.php'">Program</div>&Gt;
      <div class="dropdown-content">
        <a href="outcomes.php">Outcomes</a>
        <a href="outputs.php">Outputs</a>
        <a href="activities.php">Activities</a>
        <a href="items.php">Items</a>
      </div>
  </div>
  <div class="dropdown">
      <div class="dropbtn" onclick="location.href='./indicators.php'">Indicators</div>&Gt;
      <div class="dropdown-content">
        <a href="indicators.php">Design</a>
        <a href="indicatordtl.php">By Activity</a>
      </div>
  </div><div class="dropdown">
      <div class="dropbtn">Locations</div>&Gt;
      <div class="dropdown-content">
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
      <div class="dropbtn">Spreadsheet Files</div>&Gt;
      <div class="dropdown-content">
        <a href="fwimport.php">[Smart 4Ws] Import file</a>
        <a href="#" onclick="$('.page-container').load('fwgentemplate.php');">[Smart 4Ws] Download Template</a>
      </div>
  </div>

  <a href="partners.php">Partners</a>
  <div class="dropdown">
      <div class="dropbtn">User Rights</div>&Gt;
      <div class="dropdown-content">
          <a href="users.php">Users</a>
          <a href="accessgroups.php">Access Groups</a>
      </div>
  </div>
  <div class="dropdown">
      <div class="dropbtn">Reports</div>&Gt;
      <div class="dropdown-content">
          <a href="reportgroups.php">Report group</a>
          <a href="main.php">Reports</a>
      </div>
  </div>
</div>
