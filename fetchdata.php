<?php
    $_SESSION['redirecting']=true;
    include_once './conn.php';
    include_once './dataset.php';
    include_once './designindicator.php';
    $section=QuotedStr($_GET['section']);
    define('sqlHPMBySector',sprintf('
select p.Description Section, im.IndicatorId,im.Description Indicator,
  sum(if(i.CountryId=\'Damascus\',qty,0)) `Damascus`,
  sum(if(i.CountryId=\'Amman\',qty,0)) `Amman`,
  sum(if(i.CountryId=\'Gaziantep\',qty,0)) `Gaziantep`,
  im.Target 
from
  (select
    id.YOB,
    id.IndicatorId,
    id.ProgramId,
    CountryId,
    Admin4id,Admin5Id,
    PartnerId,
    grp1,
    grp2,
    hasDisability,
    case id.IndicatorId
      when 1 then :indicator1 
      when 2 then :indicator2 
      when 3 then :indicator3 
      when 4 then :indicator4 
      when 5 then :indicator5 
      when 6 then :indicator6 
      when 7 then :indicator7 
      when 8 then :indicator8 
      when 9 then :indicator9 
      when 10 then :indicator10 
      when 11 then :indicator11 
      when 12 then :indicator12 
      when 13 then :indicator13 
      when 14 then :indicator14 
      when 15 then :indicator15 
      when 16 then :indicator16 
      when 17 then :indicator17 
      when 18 then :indicator18 
      when 19 then :indicator19 
      when 20 then :indicator20 
      when 21 then :indicator21 
      when 22 then :indicator22 
      when 23 then :indicator23 
      when 24 then :indicator24 
      when 25 then :indicator25 
      when 26 then :indicator26 
      when 27 then :indicator27 
      when 28 then :indicator28 
      when 29 then :indicator29 
      when 30 then :indicator30 
      when 31 then :indicator31 
      when 32 then :indicator32 
      when 33 then :indicator33 
      when 34 then :indicator34 
      when 35 then :indicator35 
    end qty 
  from
    fwIndicatorDtl id join fwActivityBeneficiaries ab using (ActivityCode)
  where 
    id.YOB=%d
    /* and ab.CountryId in ({cHUB@select CountryId from fwCountries}) */
    and isNew=1
  group by 
    id.IndicatorId,
    CountryId,
    Admin4Id,
    if(id.IndicatorId>1,Admin5Id,0),if(id.IndicatorId>1,SiteId,0),if(id.ProgramId=5 and id.IndicatorId>1,PartnerId,0),
    grp1,
    grp2,
    hasDisability
  ) i 
join fwIndicatorMaster im using (YOB,IndicatorId) 
join fwPrograms p on (p.YOB=i.YOB and p.ProgramId=i.ProgramId) 
where
  im.GroupName=\'HPM\' 
  and p.Description=%s
group by 
  i.YOB,
  i.IndicatorId',$_SESSION['YOB'],$section));
    define('sqlSectionReachByLocation',sprintf(
            'select CountryId,a4.Location,a4.Longitude,a4.Latitude,p.Description Program ,group_concat(distinct al.Description) Activities 
            from
              fwactivitybeneficiaries ab 
              join fwprograms p using (yob,programid)
              join fwadmin4 a4 on (a4.Id=ab.Admin4id)
              join fwactivitylist al on (al.ActivityCode=ab.ActivityCode)
            where 
              ab.YOB=%d 
              and p.Description=%s
            group by CountryId,a4.Location,a4.Longitude,a4.Latitude',$_SESSION['YOB'],$section));

    $qry= new Dataset($dblink);
    
//      echo '<pre>'.$qry->SQL.'</pre>';
      try {
       if (strtolower($section)!="'all'") {
           $qry->SQL= forkIndicators(sqlHPMBySector);
           $qry->Open();
       
//       echo '<div class="ind-group">';
           while (!$qry->EOF()){
               if($qry->Values['Section']!=$grpsection) {
                   printf('<div class="title">%s</div>',$qry->Values['Section']);
                   $grpsection=$qry->Values['Section'];
               }
               printf('<div class="ind"><div>%s %s</div><div class="bar" data-value="%d %d %d" data-max="%d"></div></div>',
                        $qry->Values['IndicatorId'],$qry->Values['Indicator'],$qry->Values['Damascus'],$qry->Values['Amman'],$qry->Values['Gaziantep'],$qry->Values['Target']);
               $qry->Next();
           }
           
           $qry->SQL=forkIndicators(sqlSectionReachByLocation);
           $qry->Open();
           $geoPoint=[];
           while (!$qry->EOF()){
               $geoPoint[]=['CountryId'=>$qry->Values['CountryId'],'Location'=>$qry->Values['Location'],'long'=>$qry->Values['Longitude'],'lat'=>$qry->Values['Latitude'],'Activities'=>$qry->Values['Activities']];
               $qry->Next();
           }
           printf( '<div id="params" hidden>%s</div>', json_encode($geoPoint)); 
       }
//       echo '</div>';
       //$grid=new HithReport($qry);
       
} catch (Exception $exc) {
    echo $exc->getTraceAsString();exit();
}


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

