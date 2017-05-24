<?php
include_once './conn.php';
//global $YOB;
//global $sectors
if (!isset($_SESSION['username']))    return;
define('sqlBrowseActivities', 'SELECT
 al.Description as "#gActivity",
 p.Description Partner,se.Description,al.section,
 coalesce(Concat(g.Description, " ," ,d.Description, " ," ,a3.Description, " ," ,a4.Description, " ," ,s.Description),
 Concat(g.Description, " ," ,d.Description, " ," ,a3.Description, " ," ,a4.Description),
 Concat(g.Description, " ," ,d.Description, " ," ,a3.Description),
 Concat(g.Description, " ," ,d.Description),
 Concat(g.Description))as Location,
 sum(ab.QTY) as Reached,
 cast(max(ab.UpdatedAt) as Date) as "Last Period",
 count(distinct ab.updatedat) as "Number Of Reports"
FROM fwactivitybeneficiaries ab
LEFT JOIN fwActivityList al on al.YOB=ab.YOB and al.PcrId=ab.PcrId and al.`InterventionId`=ab.`InterventionId` and al.`OutputId`=ab.`OutputId` and al.`ActivityId`=ab.`ActivityId`
left join fwSectors se on se.Description=al.section
JOIN fwusergroupdtl ud ON ud.groupid=se.groupid
LEFT JOIN fwpartners p ON p.Partner=ab.Partner
LEFT JOIN fwgovernorates g on g.`Id`=ab.`GovernorateId`
LEFT JOIN fwdistrict d on d.`GovernorateId`=ab.`GovernorateId` and d.`Id`=ab.`DistrictId`
LEFT JOIN fwAdmin3 a3 on a3.`GovernorateId`=ab.`GovernorateId` and a3.`DistrictId`=ab.`DistrictId` and a3.`Id`=ab.`Admin3Id`
LEFT JOIN fwAdmin4 a4 on a4.`GovernorateId`=ab.`GovernorateId` and a4.`DistrictId`=ab.`DistrictId` and a4.`Admin3Id`=ab.`Admin3Id` and a4.`Id`=ab.`Admin4Id`
LEFT JOIN fwsites s on s.`Admin4Id`=ab.`Admin4Id` and s.`SiteId`=ab.SiteId
WHERE ud.username=\'%s\' or %s=31
GROUP BY 
 ab.YOB, 
 al.Description,
 p.Partner,
 ab.GovernorateId, 
 ab.DistrictId, 
 ab.Admin3Id, 
 ab.Admin4Id, 
 s.Description
');

define('sqlImport','insert into fwactivitybeneficiaries(YOB,CountryId,PartnerId,ProgramId,OutcomeId,OutputId,ActivityId,
 GovernorateId,DistrictId,Admin3Id,Admin4Id,Admin5Id,SiteId,Coverage,ActivityDate,UpdatedAt,ModalityId,AreaStatus,isNew,hasDisability,grp1,grp2,qty,DOE,UserId)
select 
  al.YOB,i.CountryId,p.PartnerId,al.ProgramId,al.OutcomeId,al.OutputId,al.ActivityId,
  a4.GovernorateId,a4.DistrictId,a4.Admin3Id,a4.Id Admin4Id,coalesce(a5.Id ,0) admin5Id,coalesce(s.SiteId,0) SiteId,i.Coverage,i.ActivityDate,i.ReportingDate,m.ModalityId,i.AreaStatus,i.isNew,i.hasDisability,u1.grp grp1,u2.grp grp2,if(al.isSum=1,sum(i.qty),max(i.qty)),now(),%s
from
 fwImport i 
  left join fwAdmin4 a4 on a4.location=i.location
  left join fwAdmin5 a5 on a5.Location=i.Neighborhood
  left join fwSites s on s.Admin4Id=a4.Id and s.Refname=i.Site
  left join fwActivityList al on al.YOB=i.YOB and al.Description=i.Activity
  left join fwPartners p on p.CountryId=i.CountryId and p.Description=i.Partner
  left join fwModality m on m.Description=i.Modality
  left join fwUOMGrp u1 on u1.Description=i.grp1 or u1.Token=i.grp1
  left join fwUOMGrp u2 on u2.Description=i.grp2 or u2.Token=i.grp2
  left join fwActivityBeneficiaries ab on ab.YOB=al.YOB and ab.countryid=i.countryid and ab.partnerId=p.partnerId and ab.ProgramId=al.ProgramId and ab.OutcomeId=al.OutcomeId and ab.outputid=al.outputid and ab.activityid=al.activityid 
            and ab.GovernorateId=a4.GovernorateId and ab.DistrictId=a4.DistrictId and ab.Admin3Id=a4.Admin3Id and ab.Admin4Id=a4.Id and ab.Admin5Id=coalesce(a5.Id,0) and ab.SiteId=coalesce(s.SiteId,0) and ab.ActivityDate=i.ActivityDate and ab.UpdatedAt=i.ReportingDate and ab.isNew=i.isNew and ab.hasDisability=i.hasDisability and ab.grp1=u1.grp and ab.grp2=u2.grp
where ab.UpdatedAt is null and al.SectorId=%d and i.sessionid=%s
group by
  al.YOB,i.CountryId,p.PartnerId,al.ProgramId,al.OutcomeId,al.OutputId,al.ActivityId,
  a4.GovernorateId,a4.DistrictId,a4.Admin3Id,a4.Id,a5.id,s.Siteid,i.ActivityDate,i.ReportingDate,i.grp1,i.grp2,m.ModalityId,i.isNew,i.hasDisability');
define('sqlDeleteSection','delete ab from fwActivityBeneficiaries ab join fwActivityList al on al.YOB=ab.YOB and al.ProgramId=ab.ProgramId and al.OutcomeId=ab.OutcomeId and al.OutputId=ab.OutputId and al.ActivityId=ab.ActivityId where ab.YOB=%d and ab.CountryId=%s and al.SectorId=%d');

define ('sqlWASHAddressIntoSite','insert into fwSites(Admin4Id,SiteId,SiteType,Description,RefName)
select b.id,@rw:=@rw+1,b.SiteType,left(b.Address,127),left(b.RefName,127) from (select distinct a4.id, \'Other\' as SiteType,i.Address, trim(Concat(i.Address,\', \',LEFT(i.Location,POSITION(\',\' in i.location)-1)) ) as RefName
from 
  fwImport i left join
  fwActivityList al on al.Description=i.activity left join
  fwAdmin4 a4 on a4.Location=i.location
where al.Section=\'WASH\' and address<>\'\' and not exists (select * from fwSites s where s.RefName=trim(Concat(i.Address,\', \',LEFT(i.Location,POSITION(\',\' in i.location)-1))))) b');

define ('sqlWASHActivitiesApplySites','');
$tables=array('fwactivities','fwactivitybenefeciaries','fwactivitybenlist','fwactivityitems','fwactivitylist','fwadmin3','fwadmin4','fwdistrict','fwgovernerates',
      'fwindicatordtl','fwindicatormaster','fwirs','fwitems','fwpartners','fwpcrs','fwreportgroup','fwsectors','fwsghtrreport','fwsites','fwuomgrp','fwusergroupdtl','fwusers','fwvars','genrep');

$fwactivities=0;
$fwactivitybenefeciaries=1;
$fwactivitybenlist=2;
$fwactivityitems=3;
$fwactivitylist=4;
$fwadmin3=5;
$fwadmin4=6;
$fwdistrict=7;
$fwgovernerates=8;
$fwindicatordtl=9;
$fwindicatormaster=10;
$fwirs=11;
$fwitems=12;
$fwpartners=13;
$fwpcrs=14;
$fwreportgroup=15;
$fwsectors=16;
$fwsghtrreport=17;
$fwsites=18;
$fwuomgrp=19;
$fwusergroupdtl=20;
$fwusers=21;
$fwvars=22;
$genrep=23;


//$q->close();
//$q->SQL=sprinf('select table_name,column_name,referenced_table_name,referenced_column_name from information_schema.KEY_COLUMN_USAGE where table_schema=%s and not referenced_table_name is null', QuotedStr($dbname));
//$q->open();
//
//while (!$q->EOF) {
//   if (!array_key_exists($q->Values['TABLE-NAME'], $dbrelations)) $dbrelations['TABLE_NAME']= null ; 
//   
//}


$dbrelations=[
  'fwusergroups'=>[
      'Access Right'=>[
          'reftable'=>'fwuserroles',
          'result'=>'Description',
          'fields'=>[
              'AuthFlag'=>'AuthId'
          ]
      ]
  ],
  'fwitems'=>[
    'Sector'=>[
        'reftable'=>'fwsectors',
        'result'=>'Description',
        'fields'=>[
            'SectorId'=>'SectorId'
        ]
    ]  
  ],  
  'fwprograms'=>[
      'Sector'=>[
          'reftable'=>'fwsectors',
          'result'=>'Description',
          'fields'=>[
              'SectorId'=>'SectorId'
          ]
      ]
  ],
  'fwindicatordtl'=>[
      'Indicator'=>[
          'reftable'=>sprintf('select * from fwindicatormaster where YOB=%s',$_SESSION['YOB']),
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'IndicatorId'=>'IndicatorId'
          ]
      ],
      'Activity'=>[
          'reftable'=>'fwactivitylist',//sprintf('select * from fwactivitylist where YOB=%s',$_SESSION['YOB']),
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId',
              'OutcomeId'=>'OutcomeId',
              'OutputId'=>'OutputId',
              'ActivityId'=>'ActivityId'
          ]
      ]
  ],
  'fwusersector'=>[
      'Sector'=>[
          'reftable'=>'fwsectors',
          'result'=>'Description',
          'fields'=>[
              'SectorId'=>'SectorId'
          ]
      ]
      
  ],
  'fwreportsector'=>[//table
     'Sector'=>[//lookup caption to shows
         'reftable'=>'fwsectors',//reference table
         'result'=>'Description',//result field
         'fields'=>[//keyfields=>lookupfields on reference table
             'SectorId'=>'SectorId'
         ]
     ],
     'Report'=>[
         'reftable'=>'genrep',
         'result'=>'RepName',
         'fields'=>[
             'YOB'=>'YOB',
             'ReportName'=>'RepName'
         ]
     ],
     'Country'=>[
         'reftable'=>'fwcountries',
         'result'=>'CountryId',
         'fields'=>[
             'CountryId'=>'CountryId'
         ]
     ]
  ],
  'fwusers'=>[
      'Role'=>[
        'reftable'=>'select * from fwuserroles where AuthId<='.$_SESSION['authflag'],
        'result'=>'Description',
        'fields'=>[
            'flag'=>'AuthId'
        ]
      ],
      'Country office'=>[
        'reftable'=>sprintf('select * from fwcountries where CountryId=%s%s', QuotedStr($_SESSION['CountryId']),31==$_SESSION['authflag']?' or true':''),
        'result'=>'Description',
        'fields'=>[
            'CountryId'=>'CountryId'
        ]    
      ],
  'User Rights Group'=>[
      'reftable'=>'fwusergroups',
      'result'=>'Description',
      'fields'=>[
          'GroupId'=>'GroupId'
      ]
    ]
  ],
  'fwactivitylist'=>[
      'Program'=>[
          'reftable'=>sprintf('select * from fwprograms where YOB=%d and SectorId in (%s)',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),//select op.ProgramId,op.OutcomeId,op.OutputId, oc.Description as category,op.Description from fwOutputs op join fwOutcomes oc on (oc.YOB=op.YOB and oc.ProgramId=op.ProgramId and oc.OutcomeId=op.OutcomeId)',
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId'
          ]
      ],
      'Outcome'=>[
          'reftable'=>sprintf('select * from fwoutcomes o where YOB=%s and exists(select * from fwprograms p where p.YOB=o.YOB and p.ProgramId=o.ProgramId and p.SectorId in (%s))',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId',
              'OutcomeId'=>'OutcomeId'
          ]
      ],
      'Output'=>[
          'reftable'=>sprintf('select * from fwoutputs o where o.YOB=%d and exists(select * from fwprograms p where p.YOB=o.YOB and p.ProgramId=o.ProgramId and p.SectorId in (%s))',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),//select op.ProgramId,op.OutcomeId,op.OutputId, oc.Description as category,op.Description from fwOutputs op join fwOutcomes oc on (oc.YOB=op.YOB and oc.ProgramId=op.ProgramId and oc.OutcomeId=op.OutcomeId)',
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId',
              'OutcomeId'=>'OutcomeId',
              'OutputId'=>'OutputId'
          ]
      ],
      'SUM on duplication?<br>(MAX otherwise)'=>[
          'reftable'=>'fwyn',
          'result'=>'Description',
          'fields'=>[
              'isSum'=>'Id'
          ]
      ]
  ],
  'fwoutputs'=>[
      'Program'=>[
          'reftable'=>sprintf('select * from fwprograms where YOB=%d and SectorId in (%s)',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),//select op.ProgramId,op.OutcomeId,op.OutputId, oc.Description as category,op.Description from fwOutputs op join fwOutcomes oc on (oc.YOB=op.YOB and oc.ProgramId=op.ProgramId and oc.OutcomeId=op.OutcomeId)',
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId'
          ]
      ],
      'Outcome'=>[
          'reftable'=>sprintf('select * from fwoutcomes o where YOB=%s and exists(select * from fwprograms p where p.YOB=o.YOB and p.ProgramId=o.ProgramId and p.SectorId in (%s))',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId',
              'OutcomeId'=>'OutcomeId'
          ]
      ]
  ],
  'fwoutcomes'=>[
      'Programme'=>[
          'reftable'=>sprintf('select YOB,ProgramId,p.SectorId, s.Description category,p.Description from fwprograms p join fwsectors s on s.SectorId=p.SectorId where YOB=%d and p.SectorId in (%s) order by p.SectorId',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
          'result'=>'Description',
          'fields'=>[
              'YOB'=>'YOB',
              'ProgramId'=>'ProgramId'
          ]
      ]
  ],
  'fwsites'=>[
      'Community'=>[
          'reftable'=>'fwadmin4',
          'result'=>'Location',
          'fields'=>[
              'Admin4Id'=>'Id'
          ]
       ],
       'Site Type'=>[
           'reftable'=>'fwsitetypes',
           'result'=>'Description',
           'fields'=>[
               'SiteType'=>'Description'
          ]
       ]
  ],
  'fwadmin4'=>[
      'SubDistrict'=>[
          'reftable'=>'fwadmin3',
          'result'=>'Description',
          'fields'=>[
              'GovernorateId'=>'GovernorateId',
              'DistrictId'=>'DistrictId',
              'Admin3Id'=>'Id'
          ]
      ]
  ],
  'fwadmin3'=>[
      'District'=>[
          'reftable'=>'fwdistrict',
          'result'=>'Description',
          'fields'=>[
              'GovernorateId'=>'GovernorateId',
              'DistrictId'=>'DistrictId'
          ]
      ]
  ],
  'fwdistrict'=>[
      'Governorate'=>[
          'reftable'=>'fwgovernorates',
          'result'=>'Description',
          'fields'=>[
              'GovernorateId'=>'GovernorateId'              
          ]
      ]
  ],
  'fwactivitybenlist'=>[
        'Activity'=>[
            'reftable'=>'fwactiviylist',
            'result'=>'Description',
            'fields'=>[
                'YOB'=>'YOB',
                'ProgramId'=>'ProgramId',
                'OutcomeId'=>'OutcomeId',
                'OutputId'=>'OutputId',
                'ActivityId'=>'ActivityId'
            ]
        ],
        'Category'=>[
            'reftable'=>'fwuomcategories',
            'result'=>'Category',
            'fields'=>[
               'grp1'=>'grp1Id',
               'grp2'=>'grp2Id'
            ]
        ]
    ],
    'fwpartners'=>[
        'Country Office'=>[
            'reftable'=>sprintf('select * from fwcountries c where exists (select * from fwusers u where u.CountryId=c.CountryId and u.username=%s)', QuotedStr($_SESSION['username'])),
            'result'=>'Description',
            'fields'=>[
                'CountryId'=>'CountryId'
            ]
        ]
    ],
    'fwlocationstatus'=>[
       'Community'=>[
           'reftable'=>'fwadmin4',
           'result'=>'Location',
           'fields'=>[
               'Admin4Id'=>'Id'
           ]
         
       ],
       'Neighborhood'=>[
           'reftable'=>'fwadmin5',
           'result'=>'Location',
           'fields'=>[
               'Admin4Id'=>'Admin4Id',
               'Admin5Id'=>'Id'
           ]
       ],
       'Access Status'=>[
           'reftable'=>'fwareastatus',
           'result'=>'Description',
           'fields'=>[
               'status'=>'status'
           ]
       ]
    ]
];
$dbMasterDetail=[
    'fwindicatormaster'=>[
        'fwindicatordtl'=>[
            'label'=>'Activity',
            'list'=>sprintf('select * from fwactivitylist where YOB=%s and SectorId in (%s)',$_SESSION['YOB'],join(',',(array) $_SESSION['sectors'])),
            'display'=>['Description'],
            'fields'=>[
                'YOB'=>'YOB',
                'IndicatorId'=>'IndicatorId'
            ]
            //,'columns'=>['Target']   //<-- example if we need to add further fields in details selection , omit if not needed
        ]
    ],
    'genrep'=>[
        'fwreportsector'=>[
            'label'=>'Sector',
            'list'=>'fwsectors',
            'display'=>['Description'],
            'fields'=>[
                'YOB'=>'YOB',
                'RepName'=>'ReportName'
            ]
        ]
    ],
    'fwsectors'=>[
        'fwreportsector'=>[
            'label'=>'Report set',
            'list'=>'select * from genrep where YOB='.$_SESSION['YOB'],
            'display'=>['RepName'],
            'fields'=>[
                'SectorId'=>'SectorId'
            ]
        ]
    ],
    'fwusers'=>[//master table
        'fwusersector'=>[//detail table
            'label'=>'Sectors',//choosing check list caption
            'list'=>'fwsectors',//choose check list from table
            'display'=>['Description'],//displayed fields to check
            'fields'=>[//masterfield=>detailfield    (from the detail table)
                'username'=>'username'
            ]
        ]
    ],
    'fwactivitylist'=>[
        'fwactivitybenlist'=>[
            'label'=>'Category',
            'list'=>'fwuomcategories',
            'display'=>['Category'],
            'fields'=>[
                'YOB'=>'YOB',
                'ProgramId'=>'ProgramId',
                'OutcomeId'=>'OutcomeId',
                'OutputId'=>'OutputId',
                'ActivityId'=>'ActivityId'
                ]
        ]
    ]
];
$dbControlsType=[
    'fwusergroups'=>[
      'AuthFlag'=>'hidden',
      'SectorId'=>'hidden'
    ],
    'fwusers'=>[
        'password'=>'password',
        'flag'=>'hidden',
        'GroupId'=>'hidden'
    ],
    'fwgovernorates'=>[
        'Polygon'=>'hidden'
    ],
    'fwitems'=>[
        'Sector'=>'hidden'
    ],
    'fwactivitylist'=>[
        'YOB'=>'hidden',
        'ProgramId'=>'hidden',
        'OutcomeId'=>'hidden',
        'OutputId'=>'hidden',
        'flag'=>'hidden',
        'WhatUOM'=>'hidden',
        'SectorId'=>'hidden',
        'ReportFreq'=>'hidden'
    ],
    'fwoutputs'=>[
      'YOB'=>'hidden',
      'ProgramId'=>'hidden',
      'OutcomeId'=>'hidden'
    ],
    'fwoutcomes'=>[
      'YOB'=>'hidden',
      'ProgramId'=>'hidden'
    ],
    'fwsites'=>[
        'CountryId'=>'hidden',
        'Admin5Id'=>'hidden'
    ],
    'fwlocationstatus'=>[
        'Admin4Id'=>'hidden',
        'Admin5Id'=>'hidden'
    ],
    'genrep'=>[
        'SQLText'=>'hidden',
        'conf'=>'hidden'
    ]
];

function masters(){
    global $dbrelations;
    foreach ($dbrelations as $dtltbl=>$relation){
        foreach($relation as $a=>$b){
           // if (!isSqlQuery($b['reftable'])) 
                if (!in_array($b['result'],(Array) $lookupResults[$b['reftable']]))
                    $lookupResults[$b['reftable']][]=$b['result'];
        }
    }
    return($lookupResults);
}

$masters=masters();
//echo '<script>console.log("'. addcslashes(print_r($masters,true),"\r\n").'");</script>';

$tabledefaults=[// values to set if the field is null
    'fwlocationstatus'=>[
        'Admin5Id'=>0
    ],
    'fwsites'=>[
        'CountryId'=> $_SESSION['CountryId']
    ],
    'fwusers'=>[
        'CountryId'=>$_SESSION['CountryId']
    ],
    'fwactivitylist'=>[
        'ActivityId'=>'select coalesce(max(ActivityId),0)+1 ActivityId from '
        . 'fwactivitylist where YOB=:YOB and ProgramId=:ProgramId and OutcomeId=:OutcomeId and OutputId=:OutputId'
    ],
    'fwpartners'=>[
        'PartnerId'=>'select coalesce(max(PartnerId),0)+1 PartnerId from fwpartners where CountryId=:CountryId'
    ]
];

$fwdefaultvalues =[// this is only for smart 4Ws import, for table defaults check $tabledefaults
    'YOB'=>$_SESSION['YOB'],
    'Modality'=>QuotedStr('Regular Program'),
    'Agency'=>QuotedStr('UNICEF'),  //Temp
    'CountryId'=>QuotedStr($_SESSION['CountryId']),
    'isNew'=>1,
    'hasDisability'=>0,
    'Coverage'=>4,
    'AreaStatus'=>0
];
$YOB=$_SESSION['YOB'];
$fwmapvalues=[// this is only for smart 4Ws import for table defaults check $tabledefaults
    'isNew'         =>['Yes'=>0,'No'=>1,''=>1],
    'Coverage'      =>['Admin4'=>4,'Admin3'=>3,'Admin2'=>2,'Admin1'=>1,''=>4],
    'hasDisability' =>['Yes'=>1,'No'=>0,''=>0]
    ,'ReportingDate'=>['Jan'=>QuotedStr($YOB.'-01-31'),'Feb'=>QuotedStr($YOB.'-02-28'),
        'Mar'=>QuotedStr($YOB.'-03-31'),'Apr'=>QuotedStr($YOB.'-04-30'),'May'=>QuotedStr($YOB.'-05-31'),
        'Jun'=>QuotedStr($YOB.'-06-30'),'Jul'=>QuotedStr($YOB.'-07-31'),'Aug'=>QuotedStr($YOB.'-08-31'),
        'Sep'=>QuotedStr($YOB.'-09-30'),'Oct'=>QuotedStr($YOB.'-10-31'),
        'Nov'=>QuotedStr($YOB.'-11-30'),'Dec'=>QuotedStr($YOB.'-12-31')],
    'AreaStatus'=>['Accessible'=>0,'Yes'=>1,'HTR'=>1,'No'=>0,'BSG'=>2,''=>0]
];

$dbexport=[
    'fwactivitylist'=>sprintf('select * from fwactivitylist where YOB=%d and SectorId in (%s)',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
    'fwoutcomes'=>sprintf('select * from fwoutcomes o where YOB=%s and exists(select * from fwprograms p where p.YOB=o.YOB and p.ProgramId=o.ProgramId and p.SectorId in (%s))',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
    'fwoutputs'=>sprintf('select * from fwoutputs o where o.YOB=%d and exists(select * from fwprograms p where p.YOB=o.YOB and p.ProgramId=o.ProgramId and p.SectorId in (%s))',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
    'fwprograms'=>sprintf('select * from fwprograms where YOB=%d and SectorId in (%s)',$_SESSION['YOB'],join(',',$_SESSION['sectors'])),
    'fwpartners'=>sprintf('select * from fwpartners where CountryId=%s', QuotedStr($_SESSION['CountryId'])),
    'fwsites'=>sprintf('select * from fwsites where CountryId=%s',QuotedStr($_SESSION['CountryId'])),
    'fwitems'=>sprintf('select * from fwitems where SectorId in (%s)',join(',',$_SESSION['sectors']))
];

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>