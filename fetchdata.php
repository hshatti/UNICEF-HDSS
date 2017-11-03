<?php
    $_SESSION['redirecting']=true;
    include_once './conn.php';
    include_once './dataset.php';
    include_once './designindicator.php';
    $section=QuotedStr($_GET['section']);
    $act=$_GET['act'];
    $ProgramId=$q->execute(sprintf('select * from fwprograms where yob=%d and Description=%s',$_SESSION['YOB'],$section))['ProgramId'];
    define('sqlMapAdmin1','select pcode, Description, st_AsText(Polygon) Polygon, st_y(st_centroid(Polygon)) Latitude, st_x(st_centroid(Polygon)) Longitude from fwGovernorates');
    define('sqlMapAdmin2','select pcode, Description, st_AsText(Polygon) Polygon, st_y(st_centroid(Polygon)) Latitude, st_x(st_centroid(Polygon)) Longitude from fwDistrict');
    define('sqlLastUpdate','select max(UpdatedAt) `Month` from fwcachedindicators');
    $lastUpdate=$monthName[$q->Execute(sqlLastUpdate)['Month']];
    define('sqlByGovernorate','select 
  CountryId,
  g.Description Governorate,
  Count(Admin4Id) Communities,
  sum(beneficiaries) Beneficiaries
from 
  (
select 
  CountryId,
  Admin4Id,
  max(beneficiaries) beneficiaries 
from 
  (
        select 
          CountryId,
 	  Admin4Id,
  	  IndicatorId,
  	  if(IndicatorId=305,max(value),sum(value)) Beneficiaries
	from 
  	  fwcachedindicators c 
          join fwIndicatorMaster im using(yob,indicatorId)
	where 
  	  c.YOB=%s 
          and c.IndicatorId>300 
          and %s
	group by 
 	  CountryId ,
 	  Admin4Id,
  	  IndicatorId
) c 
group by 
  CountryId,
  Admin4Id
) c join fwadmin4 a4 on (a4.id=c.Admin4Id) join fwGovernorates g on (g.id=a4.GovernorateId)
group by 
  g.Description,
  CountryId order by 4');
    
    define('sqlOverallByCountry',
'select 
  CountryId,
  Count(Admin4Id) Communities,
  sum(beneficiaries) Beneficiaries
from 
  (
select 
  CountryId,
  Admin4Id,
  max(beneficiaries) beneficiaries 
from 
  (
   select 
     CountryId,
 	  Admin4Id,
  	  IndicatorId,
  	  sum(value) Beneficiaries
	from 
  	  fwcachedindicators c 
          join fwIndicatorMaster im using(yob,indicatorId)
	where 
  	  c.IndicatorId>300 
          and %s
	group by 
 	  CountryId ,
 	  Admin4Id,
  	  IndicatorId
) c
group by 
  CountryId,
  Admin4Id
) c
group by 
  CountryId');
    define('sqlMapReachBySection','select ab.CountryId,a4.PCODE,a4.Longitude,a4.Latitude,a4.Location
        -- , group_concat(al.Description) Activities  
        from fwactivitybeneficiaries ab join fwadmin4 a4 on(a4.Id=ab.Admin4Id) join fwprograms p using (yob,programid) join fwactivitylist al using (yob,ActivityCode) 
                 where ab.yob=%d and %s
                 group by ab.CountryId,a4.PCODE');
    define('sqlSectionReachByLocation',sprintf(
            'select CountryId,a4.Location,a4.Longitude,a4.Latitude,p.Description Program ,group_concat(distinct al.Description) Activities 
            from
              fwactivitybeneficiaries ab 
              join fwadmin4 a4 on (a4.Id=ab.Admin4id)
              join fwactivitylist al on (al.ActivityCode=ab.ActivityCode)
            where 
              ab.YOB=%d 
              and ab.ProgramId=%d
            group by CountryId,a4.Location,a4.Longitude,a4.Latitude',
            $_SESSION['YOB'],$ProgramId));

    define(sqlHPMBySector2,sprintf(
            'select 
	yob,
        p.Description Section,
	countryid,
	programid,
	indicatorid,
	Indicator,
	Target,
	sum(if(CountryId=\'Damascus\',value,0)) Damascus,
	sum(if(CountryId=\'Amman\',value,0)) Amman,
	sum(if(CountryId=\'Gaziantep\',value,0)) Gaziantep
	 
from (
    select
      yob,
		countryid,
		ProgramId,
		GroupName,
		indicatorid,
		im.Description Indicator,
		partnerid,
		admin4id,
		max(im.Target) Target,
                sum(value) value 
    from fwcachedindicators c 
    join fwindicatormaster im using (yob,IndicatorId)
    group by 
    yob,countryid,GroupName,indicatorid,partnerid,admin4id
    ) i
    join fwprograms p using (yob,programid)
where
      YOB=%s
      and GroupName=\'HPM\'
      and i.ProgramId=%d
    group by i.yob,i.indicatorid',
            $_SESSION['YOB'],$ProgramId)
     );
    $qry= new Dataset($dblink);
    
//      echo '<pre>'.$qry->SQL.'</pre>';
    if ($act==='hpm') 
    {
        try {
        if (strtolower($section)!="'all'") {
           $qry->SQL= sqlHPMBySector2;
           $qry->Open();
       
//       echo '<div class="ind-group">';
           while (!$qry->EOF()){
               if($qry->Values['Section']!=$grpsection) {
                   printf('<div class="title"><div class="xsmall">HPM indicators 2017</div>%s</div>',$qry->Values['Section']);
                   $grpsection=$qry->Values['Section'];
               }
               printf('<div class="ind"><div>%s %s</div><br/><div class="bar" data-value="%d %d %d" data-max="%d"></div></div>',
                        $qry->Values['IndicatorId'],$qry->Values['Indicator'],$qry->Values['Damascus'],$qry->Values['Amman'],$qry->Values['Gaziantep'],$qry->Values['Target']);
               $qry->Next();
           }
           
       }
       
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();exit();
        }
    } 
    elseif ($act==='overall') {
       $qry->SQL= sprintf(sqlOverallByCountry,$section==='\'All\''?'1':'im.Description='.$section);
       $qry->open();
       $reach=[];$beneficiaries=0;$communities=0;
       while (!$qry->EOF()){
           $reach[$qry->Values['CountryId']]=['beneficiaries'=>$qry->Values['Beneficiaries'],'communities'=>$qry->Values['Communities']];
           $beneficiaries+=$qry->Values['Beneficiaries'];
           $communities+=$qry->Values['Communities'];
           $qry->Next();
       }// format community number
       $html=sprintf('<div class="box" data-class="un-%s fa-4x" data-caption="Estimated people reached in <span class=\'box-value\'>%s</span> communities from January - %s %d" value="%d"></div>',$_GET['section']==='All'?'family':strtolower(str_replace(' ','-',$_GET['section'])), number_format($communities),$lastUpdate,$_SESSION['YOB'],$beneficiaries);
       echo json_encode(['html'=>$html,'reach'=>$reach]);
    }
    elseif ($act==='bygov') {
       $qry->SQL= sprintf(sqlByGovernorate,$_SESSION['YOB'],$section==='\'All\''?'1':'im.Description='.$section);
       $qry->open();
       $reach=[];$govs=[];
       while (!$qry->EOF()){
           if (!in_array($qry->Values['Governorate'],$govs)) 
                   $govs[]=$qry->Values['Governorate'];
           $reach[$qry->Values['CountryId']][array_search($qry->Values['Governorate'],$govs)]=$qry->Values['Beneficiaries'];
           $qry->Next();
       }
//       $html=sprintf('<div class="box" data-class="un-%s" data-caption="Estimated people reached in <span class=\'box-value\'>%d</span> communities as of %s %d" value="%d"></div>',$_GET['section']==='All'?'family':strtolower(str_replace(' ','-',$_GET['section'])),$communities,$lastUpdate,$_SESSION['YOB'],$beneficiaries);
       echo json_encode(['govs'=>$govs,'reach'=>$reach]);
    }
    elseif ($act==='location') {
        $pcode=$_GET['pcode'];
        $qry->SQL=sprintf('select distinct Location, Al.Description Activity from fwactivitybeneficiaries ab join fwActivityList al using (ActivityCode) join fwAdmin4 a4 on (a4.Id=ab.Admin4Id) where ab.YOB=%d and %s and a4.PCODE=%s order by ab.ProgramId',$_SESSION['YOB'],$ProgramId==0?'true':'ab.programid='.$ProgramId, QuotedStr($pcode));
        //echo $qry->SQL;
        $qry->open();
        while (!$qry->EOF()){
          $r[]=$qry->Values['Activity'];
          $qry->Next();
        }
        
        //if ($qry->RecordCount>0)
            echo json_encode(['msg'=>'','location'=>$qry->Values['Location'],'activities'=>$r]);
    }
    elseif($act==='draw'){
        include_once './geo.php';
       // header('Content-Type: image/svg+xml');
        if (isset($section)) {
            if (isset($_GET['fileext'])) $fileext=strtolower($_GET['fileext']);else $fileext=false;
    //        $section=$_POST['section'][0];
            $q->SQL=sprintf(sqlMapReachBySection,$_SESSION['YOB'], (strcasecmp($section,'\'all\'')===0)?'1':'p.Description='.$section);
            $q->open();$dam=[];$gaz=[];$amm=[];
            while  (!$q->EOF()){
                if ($q->Values['CountryId']==='Damascus')  
                    $dam[]=[$q->Values['Longitude'],$q->Values['Latitude'],'r'=>5,'id'=>$q->Values['PCODE'],'opacity'=>0.5];
                elseif ($q->Values['CountryId']==='Amman')  
                    $amm[]=[$q->Values['Longitude'],$q->Values['Latitude'],'r'=>5,'id'=>$q->Values['PCODE'],'opacity'=>0.5];
                elseif ($q->Values['CountryId']==='Gaziantep')  
                    $gaz[]=[$q->Values['Longitude'],$q->Values['Latitude'],'r'=>5,'id'=>$q->Values['PCODE'],'opacity'=>0.5];
                $q->next();
            }
        }

        $q->SQL=sqlMapAdmin1;
        $q->open();
        while (!$q->EOF()) {
            $admin1[]=[$q->Values['Polygon'],'id'=>$q->Values['pcode']];
            $Admin1Labels[]=[$q->Values['Longitude'],$q->Values['Latitude'],'text'=>$q->Values['Description']];
            $q->next();
        }

        $q->SQL=sqlMapAdmin2;
        $q->open();
        while (!$q->EOF()) {
            $admin2[]=[$q->Values['Polygon'],'id'=>$q->Values['pcode']];
            $Admin2Labels[]=[$q->Values['Longitude'],$q->Values['Latitude'],'text'=>$q->Values['Description']];
            $q->next();
        }
        $admin1+=['id'=>'admin1','stroke'=>'white','stroke-width'=>4,'fill'=>'lightgray'];    
        $admin2+=['id'=>'admin2','stroke'=>'white','stroke-dasharray'=>'10,10','stroke-width'=>1,'fill'=>'none'];    
        $options[]=['name'=>'a2labels','type'=>'text','data'=>$Admin2Labels,'style'=>['fill'=>'#666','stroke'=>'none','text-anchor'=>'middle','style'=>'font-size:28px;font-family:\'Tw Cen MT\',sans-serif']];
        $options[]=['name'=>'color-damascus','type'=>'dot','data'=>$dam,'style'=>['fill'=>'dodgerblue']];
        $options[]=['name'=>'color-gaziantep','type'=>'dot','data'=>$gaz,'style'=>['fill'=>'Tomato']];
        $options[]=['name'=>'color-amman','type'=>'dot','data'=>$amm,'style'=>['fill'=>'limegreen']];
        $options[]=['name'=>'a1labels','type'=>'text','data'=>$Admin1Labels,'style'=>['fill'=>'#333','stroke'=>'#fff','stroke-width'=>0.5,'text-anchor'=>'middle','style'=>'font-size:50px;font-weight:bold;font-family:\'Tw Cen MT\',sans-serif']];
        $options['title']=sprintf('%s reached locations by WoS January - %s %s',$_GET['section'],$lastUpdate,$_SESSION['YOB']);// add also Syrian arab republic
        $opiions['width']=800;
        $options['height']=600;
        $polygroup=[$admin1,$admin2];
        $msg=$options;

    //    echo drawMap($map,300,$options);
        if ($fileext==='.svg') 
            {header('Content-Type: image/svg+xml');echo drawMap($polygroup,300,$options);}
        else echo json_encode(['msg'=>$msg,'svg'=>drawMap($polygroup,300,$options)]);
    }
    elseif ($act==='drawlayers'||$_POST['act']==='drawlayers'){
        $maps=$_POST['map'];
        if (is_array($maps)){
            foreach($maps as $map){
                if (in_array(strtolower($map),['admin1','admin2','admin3','admin4']))
                { 
                    $q->SQL=sprintf('select pcode,st_AsText(Polygon) Polygon from fw%s',$map);
                    $q->open();$layer=[];
                    while (!$q->EOF()){
                        $layer[]=[$q->Values['Polygon'],'id'=>$q->Values[0]];
                        $q->next();
                    }
                    $layer+=['id'=>$map,'fill'=>'white','stroke'=>'gray'];
                    $polygroup[]=$layer;
                }
            }
            include_once './geo.php';
            echo drawMap($polygroup,300);
        }
    }

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

