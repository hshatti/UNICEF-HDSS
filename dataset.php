<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dataset
 *
 * @author hshatti
 */
include_once './definitions.php';
include_once './conn.php';

function BetweenBrakets($str){
   $i=  strpos($str,'{');
   $j=  strrpos($str,'}',-1);
   if (($j>$i)&&$j&&$i) return Array(substr($str,0,$i),substr($str, $i+1, $j-$i-1));
   else return([$str]);
}

class HithReport {

              private $func;
              private $grp;//  constant('grpPrefix');
              private $sm;
              private $cnt;
              private $avg;
              private $max;
              private $min;
              private $first;
              private $last;
              private $prog;
              private $tool;
              private $lnk;
              private $pie;
              private $bar;
              private $anl;
              private $vec;
              public $PageRows;
              public $DataHotTrackCSS;
              public $Page;
              public $editable;
              private $SubRowNum=0;
              private $RowNum=0;
              //private $PieLabels=[];
              //private $PieValues=[];
              private $GroupNum=0;        
              private $groups=array(); 
              private $sums=array();
              private $maxs=array();
              private $mins=array();
              private $counts=array();
              private $firsts=array();
              private $lasts=array();
              private $lnks=array();
              public $Width;
              private $Dataset;

    function __construct(Dataset $Dataset=NULL) {
      $this->func='#';
      $this->grp=$this->func.'g';//  constant('grpPrefix');
      $this->sm=$this->func.'s';
      $this->cnt=$this->func.'c';
      $this->avg=$this->func.'a';
      $this->max=$this->func.'m';
      $this->min=$this->func.'n';
      $this->first=$this->func.'f';
      $this->last=$this->func.'l';
      $this->prog=$this->func.'r';
      $this->tool=$this->func.'t';
      $this->pie=$this->func.'p';
      $this->bar=$this->func.'b';
      $this->anl=$this->func.'a';
      $this->lnk=$this->func.'l';
      $this->vec=$this->func.'v';
      $this->Width='auto';
      if (isset($Dataset)) $this->Dataset=$Dataset;
    }
    public function ColumnTitles(){ 
             global $dbControlsType;
             global $dbrelations;                             
             echo '<thead><tr>';
             for ($j=0;$j<$this->Dataset->FieldCount;$j++){ 
               if(strpos($this->Dataset->Fields[$j]->name,$this->grp)!==0&&(in_array($dbControlsType[$this->Dataset->Table][$this->Dataset->Fields[$j]->orgname],['hidden','password'])===false)) { //column titles
                 $fldname=$this->Dataset->Fields[$j]->name;
                 $fldtitle=$fldname;      
                 if(strpos($fldname,$this->lnk)===0)
                 {
                     $this->lnks[$j]=  json_decode('{'.BetweenBrakets(substr($fldname,2))[1].'}');
                 }
                 if ($fldname[0]===$this->func) {
                     $fldtitle=  BetweenBrakets(substr($fldtitle,2))[0];
                 }    
                 echo '<th>'. $fldtitle.'</th>';
               }
             }  
             if ($this->editable) {
                if ($this->Dataset->RecNo==0||$this->Dataset->RecordCount==0) 
                    echo '<th class="tblPort grdbtn" onclick="doPort(this);">...';
                else 
                    echo '<th>';
                echo '</th><th class="tblNew grdbtn fa fa-file-o" onclick="doNewRecord(this);"></th>';
              }
              echo '</tr></thead>';
    }
    public function PrintGroup() {

        for ($i=0;$i<$this->Dataset->FieldCount;$i++) //for group headers band
                    if ( array_key_exists($this->Dataset->Fields[$i]->name, $this->groups) &&($this->groups[$this->Dataset->Fields[$i]->name]!==$this->Dataset->Values[$i])) {
                        
                          echo '<thead><tr class="grpheader grp'.$i.'">';//TODO: better move grp# to Id instead of class 
                          $fldname=substr($this->Dataset->Fields[$i]->name,2);
                          $fldname=  BetweenBrakets($fldname)[0];
                          echo '<td>'.$fldname.(trim($fldname)==''?'':' :').$this->Dataset->Values[$i].'</td>';
                          echo '</tr></thead>';
                          if ($i==count($this->groups)-1) 
                              $this->ColumnTitles ();// Column Titles
                          $this->groups[$this->Dataset->Fields[$i]->name]=$this->Dataset->Values[$i];
                          
                          for ($j=$i+1;$j<count($this->groups);$j++) $this->groups[array_keys($this->groups)[$j]]=null;// clear all groupings afterwards
                    }
    }
    public function PrintData($return=false){
                global $dbControlsType;
                global $dbrelations;
                if(!$return) $echo.= '<tr id="row'.$this->Dataset->RecNo.'" class="data">';// data band
                if ($this->editable) $echo.= '<td hidden>'.$this->Dataset->strWhereKeys().'</td>';
                for ($i=0;$i<$this->Dataset->FieldCount;$i++){
                    $fldname=$this->Dataset->Fields[$i]->name;
                    if ((strpos($fldname, $this->grp)!==0)&&(in_array($dbControlsType[$this->Dataset->Table][$this->Dataset->Fields[$i]->orgname],['hidden','password'])===false)) {  
                            if (($this->Dataset->Fields[$i]->type==MYSQLI_TYPE_NEWDECIMAL ||$this->Dataset->Fields[$i]->type==MYSQLI_TYPE_SHORT||$this->Dataset->Fields[$i]->type==MYSQLI_TYPE_LONGLONG||$this->Dataset->Fields[$i]->type==MYSQLI_TYPE_DOUBLE||$this->Dataset->Fields[$i]->type==MYSQLI_TYPE_FLOAT||$this->Dataset->Fields[$i]->type==MYSQLI_TYPE_LONG)&&strpos($fldname,$this->prog)!==0)
                                $echo.= '<td class="num'.($this->DataHotTrackCSS?' '.$this->DataHotTrackCSS:'').'">';
                            elseif ($this->Dataset->Fields[$i]->type==MYSQLI_TYPE_DATETIME)
                                $echo.= '<td class="date'.($this->DataHotTrackCSS?' '.$this->DataHotTrackCSS:'').'">';
                            else $echo.= '<td'.($this->DataHotTrackCSS?' class="'.$this->DataHotTrackCSS.'"':'').'>'; 
                            if (strpos($fldname,$this->tool)===0)
                                    $echo.= '<div class="tooltip">'.$this->Dataset->Values[$i].'</div>';
                            elseif (strpos($fldname,$this->prog)===0)
                                    $echo.= '<div class="prog" value="'.$this->Dataset->Values[$i].'"><div class="progress-label">'.$this->Dataset->Values[$i].'</div></div>';
                            elseif(strpos($fldname,$this->pie)===0)
                                    $echo.= '<div class="pie" value="'.$this->Dataset->Values[$i].'"></div>';
                            elseif(strpos($fldname,$this->anl)===0)
                                    $echo.= '<div class="anl">'.$this->Dataset->Values[$i].'</div>';
                            elseif(strpos($fldname,$this->bar)===0)
                                    $echo.= '<div class="bar">'.$this->Dataset->Values[$i].'</div>';
                            elseif(strpos($fldname,$this->vec)===0)
                                    $echo.= '<div class="vec">'.$this->Dataset->Values[$i].'</div>';
                            elseif(strpos($fldname,$this->lnk)===0){
                                $this->lnks[$i]->par[0]=$this->Dataset->Values[$i];
                                $echo.= '<a href="./main.php?'.http_build_query($this->lnks[$i]).'" target="_blank">'.$this->Dataset->Values[$i].'</a>';
                            }
                            else $echo.= $this->Dataset->Values[$i];
                            $echo.= '</td>';
                    }
                    if (array_key_exists($fldname, $this->sums)) foreach($this->groups as $key=>$val) $this->sums[$fldname][$key]+=$this->Dataset->Values[$i] ;
                    if (array_key_exists($fldname, $this->counts)) foreach($this->groups as $key=>$val) $this->counts[$fldname][$key]++ ;
                    if (array_key_exists($fldname, $this->maxs)) foreach($this->groups as $key=>$val) $this->maxs[$fldname][$key]=max($this->maxs[$fldname][$key],$this->Dataset->Values[$i]) ;
                    if (array_key_exists($fldname, $this->mins)) foreach($this->groups as $key=>$val) $this->mins[$fldname][$key]=min(isset($this->mins[$fldname][$key])?$this->mins[$fldname][$key]:chr(0xffff),$this->Dataset->Values[$i]) ;
                    if (array_key_exists($fldname, $this->firsts)) foreach($this->groups as $key=>$val) if (!isset($this->firsts[$fldname][$key])) $this->firsts[$fldname][$key]=$this->Dataset->Values[$i];
                    if (array_key_exists($fldname, $this->lasts)) foreach($this->groups as $key=>$val) $this->lasts[$fldname][$key]=$this->Dataset->Values[$i] ;
                    $this->SubRowNum++;
                }
                if ($this->editable) $echo.= '<td class="tblEdit grdbtn fa fa-edit" onclick="doEditRecord(this);"></td><td class="tblDelete grdbtn fa fa-trash-o" onclick="doDelRecord(this);"></td>' ;
                if (!$return) $echo.= '</tr>'."\n";
                if ($return) return($echo);else echo $echo;
    }
    public function PrintFooter(){
                global $dbControlsType;
                global $dbrelations;
                for ($i=$this->Dataset->FieldCount-1;$i>=0;$i--) //for group footers band
                    if ( array_key_exists($this->Dataset->Fields[$i]->name, $this->groups) &&($this->groups[$this->Dataset->Fields[$i]->name]!==$this->Dataset->Values[$i]||$this->Dataset->EOF())){
                          echo '<tr class="grpfooter">';
                          if ($i==0) $this->GroupNum++;
                          for ($j=0;$j<$this->Dataset->FieldCount;$j++){
                              $val=null;
                              if (array_key_exists($this->Dataset->Fields[$j]->name, $this->sums)) {
                                 $val=$this->sums[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name];
                                 $this->sums[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name]=null;
                              } elseif (array_key_exists($this->Dataset->Fields[$j]->name, $this->counts)) {  
                                 $val=$this->counts[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name];
                                 $this->counts[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name]=null;
                              } elseif (array_key_exists($this->Dataset->Fields[$j]->name, $this->maxs)) {   
                                 $val=$this->maxs[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name];
                                 $this->maxs[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name]=null;
                              } elseif (array_key_exists($this->Dataset->Fields[$j]->name, $this->mins)) {   
                                 $val=$this->mins[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name];
                                 $this->mins[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name]=null;
                              } elseif (array_key_exists($this->Dataset->Fields[$j]->name, $this->firsts)) {   
                                 $val=$this->firsts[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name];
                                 $this->firsts[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name]=null;
                              } elseif (array_key_exists($this->Dataset->Fields[$j]->name, $this->lasts)) {   
                                 $val=$this->lasts[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name];
                                 $this->lasts[$this->Dataset->Fields[$j]->name][$this->Dataset->Fields[$i]->name]=null;
                              }
                              if ((strpos($this->Dataset->Fields[$j]->name, $this->grp)!==0)&&(in_array($dbControlsType[$this->Dataset->Table][$this->Dataset->Fields[$j]->orgname],['hidden','password'])===false))
                                if ($this->Dataset->Fields[$j]->type==MYSQLI_TYPE_NEWDECIMAL ||$this->Dataset->Fields[$j]->type==MYSQLI_TYPE_SHORT ||$this->Dataset->Fields[$j]->type==MYSQLI_TYPE_FLOAT||$this->Dataset->Fields[$j]->type==MYSQLI_TYPE_DOUBLE||$this->Dataset->Fields[$j]->type==MYSQLI_TYPE_LONG||$this->Dataset->Fields[$j]->type==MYSQLI_TYPE_LONGLONG) 
                                {
                                   // $val=  number_format($val);
                                    echo '<td class="num">';
                                    if (strpos($this->Dataset->Fields[$j]->name,$this->prog)===0)
                                          /*  echo '<div class="prog" value="'.$val.'"></div>'*/;
                                    elseif (strpos($this->Dataset->Fields[$j]->name,$this->tool)!==false);
                                    elseif (strpos($this->Dataset->Fields[$j]->name,$this->pie)!==false);
                                    elseif (strpos($this->Dataset->Fields[$j]->name,$this->anl)!==false);
                                    elseif (strpos($this->Dataset->Fields[$j]->name,$this->bar)!==false);
                                    elseif (strpos($this->Dataset->Fields[$j]->name,$this->lnk)!==false);
                                    else echo $val;
                                    echo  '</td>';
                                }
                                elseif($this->Dataset->Fields[$j]->type==MYSQLI_TYPE_NEWDATE) 
                                    echo '<td class="date">'.$val.'</td>';
                                elseif(strpos($this->Dataset->Fields[$j]->name,$this->bar)!==false) echo '<td class="vec"></td>';
                                elseif(strpos($this->Dataset->Fields[$j]->name,$this->lnk)!==false);
                                else echo '<td>'.$val.'</td>';
                          }
                        if ($this->editable) echo '<td></td><td></td>';
                        echo '</tr>'."\n";// end of group footer
                        $this->SubRowNum=0;
                        // $this->PieValues=[];
                    }
    }
    public function DoGrid($id='tblResult') {
              $dataset=$this->Dataset;
              if (!$dataset->isActive()) throw new Exception('Query not active');
              $dataset->First();
            //  echo '<script src="./js/raphael-min.js"></script>';
            //  echo '<script src="./js/pie.js"></script>';
              if ($this->PageRows>0) $pages=Intval(ceil( $this->Dataset->RecordCount/$this->PageRows));
              $page= filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
              if (!isset($this->Page)) if (isset($page)) $this->Page=$page;else $this->Page=1;
              if ($this->Page<=$pages) $record=$this->PageRows*($this->Page-1);
              if ($this->editable) echo '<div id="frmEdit"></div>';
              echo '<table class="dbgrid" id="'.$id.'"'.(($this->editable)?' data-qry="'.encrypt($this->Dataset->SQL).'" data-table="'.encrypt($this->Dataset->Table):'').'">';
              $j=1; 
              for ($i=0;$i<$this->Dataset->FieldCount;$i++) {
                  $fldname=$this->Dataset->Fields[$i]->name;
                  if (strpos($fldname,$this->grp)===0){ 
                      $this->groups[$fldname]=null;
                      $groups[]['col']=$i;
                      $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                      $groups[count($groups)-1]['options']=$options ;
                  }    
                  else {
                      if (strpos($fldname,$this->pie)===0){
                        $pies[]['col']=$j;
                        $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                        $pies[count($pies)-1]['options']=$options ;
                      }  
                      if (strpos($fldname,$this->tool)===0){
                        $tools[]['col']=$j;
                        $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                        $tools[count($tools)-1]['options']=$options ;
                      }  
                      if (strpos($fldname,$this->anl)===0){
                        $anls[]['col']=$j;
                        $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                        $anls[count($anls)-1]['options']=$options ;
                      }
                      if (strpos($fldname,$this->bar)===0){
                        $bars[]['col']=$j;
                        $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                        $bars[count($bars)-1]['options']=$options ;
                      }
                      if (strpos($fldname,$this->prog)===0){
                        $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                        $progs[]['col']=$j;
                        $progs[count($progs)-1]['options']=$options;
                      }
                      if (strpos($fldname,$this->vec)===0){
                        $vecs[]['col']=$j;
                        $options=  json_decode('{'. BetweenBrakets($fldname)[1].'}');
                        $vecs[count($vecs)-1]['options']=$options ;
                      }
                      $j++;
                  }
              }  
              for ($i=0;$i<$dataset->FieldCount;$i++) {
                  $fldname=$dataset->Fields[$i]->name;
                  if (strpos($fldname,$this->sm)===0 || strpos($fldname,$this->avg)===0) $this->sums = array_merge($this->sums, array($fldname => null));
                  if (strpos($fldname,$this->cnt)===0 || strpos($fldname,$this->avg)===0) $this->counts = array_merge($this->counts, array($fldname => null));
                  if (strpos($fldname,$this->first)===0) $this->firsts = array_merge($this->firsts, array($fldname => null));
                  if (strpos($fldname,$this->last)===0) $this->lasts = array_merge($this->lasts, array($fldname => null));
                  if (strpos($fldname,$this->min)===0) $this->mins[$fldname] = array_merge($this->mins, array( $fldname => null));
                  if (strpos($fldname,$this->max)===0) $this->maxs = array_merge($this->maxs, array($fldname => null));
              }
              if ((count($this->groups)==0)||($this->Dataset->RecordCount==0))
                  $this->ColumnTitles();
              if ($this->Page<=$pages) $record=$this->PageRows*($this->Page-1);
              if($record>0) $dataset->Seek ($record);
              $row=0;
              while((!$dataset->EOF())&&($row<$this->PageRows||$this->PageRows==false)) {
                $this->PrintGroup();
                $this->PrintData();
                $dataset->Next();
                $this->PrintFooter();
                $row++;
              }
              echo '</table><br/>'."\n";
              $pagegroup=10;
              $pagespan= intval(($this->Page-1)/ $pagegroup);
              if ($pages>=$this->Page){
                echo '<div style="margin:auto;width:600px"><div id="pages" style="width:350px" data-page="'.$this->Page.'">Page';
                for ($i=($pagespan)*$pagegroup+1;$i<= min([($pagespan+1)*$pagegroup,$pages]);$i++) {
                  echo (($this->Page==$i)? " <span selected>$i</span>":sprintf(' <a href="%s?page=%d">%d</a>', filter_input(INPUT_SERVER, 'PHP_SCRIPT'),$i,$i));
                }
                echo '</div>';
                if ($pages>$pagegroup){
                    echo '<select style="padding:4px;border-radius:4px" id="pagespan" onchange="pagespanChange(event);">';
                    for ($i=0;$i<($pages/$pagegroup);$i++)
                      printf('<option data-min="%2$d" data-max="%3$d" %s>Page %d to %d</option>',
                              $i==($pagespan)?' selected':'',
                              ($i*$pagegroup)+1,
                              min([($i+1)*$pagegroup,$pages]));
                    echo '</select>';
                    echo '<script>function pagespanChange(e){sel=$("#pagespan :selected");p=$("#pages").data("page");a=\'\';for (i=sel.data("min");i<=sel.data("max");i++) a+=p==i?\'<span selected>\'+p+\'</span> \':\'<a href="?page=\'+i+\'">\'+i+\'</a> \';$("#pages").html("Page "+a);}</script>';
                    
                }
                echo '</div>';
              }
              echo '<div id="params" style="display: none">';
              $params['pies']=$pies;
              $params['tools']=$tools;
              $params['progs']=$progs;
              $params['groups']=$groups;
              $params['anls']=$anls;
              $params['bars']=$bars;
              $params['vecs']=$vecs;
              $params['colspan']=$dataset->FieldCount-count($this->groups)+($this->editable?2:0);
              $params['tablewidth']=$this->Width;
              echo json_encode($params,true);
              echo '</div>';
              echo '<iframe id="txtArea1" style="display:none"></iframe>';
              // TODO : [haitham] following script is temporary , this will be wrapped with in a js/css seperate files
              echo "";
       
    }
}

class Table {
    public $Dataset;
    public $CheckList;
    public $PageRows;
    public $Page;
    public $editable;
    public $className;
    public $CSS;
    
    
    
    public function __construct(Dataset $dataset=null){
        $this->Dataset=$dataset;
        $this->CheckList=false;
        
    }
    public function draw($id='tblResult'){
        global $dbControlsType;
        global $dbrelations;
        if ($this->editable) echo '<div id="frmEdit"></div>';
            if ($this->PageRows>0) $pages=Intval(ceil( $this->Dataset->RecordCount/$this->PageRows));
            $page= filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if (!isset($this->Page)) if (isset($page)) $this->Page=$page;else $this->Page=1;
            if ($this->Page<=$pages) $record=$this->PageRows*($this->Page-1);
            echo '<table id="'.$id.'"  data-table="'.encrypt($this->Dataset->Table).'" '.($this->CSS?'style="'.$this->CSS.'"':'').' '.($this->className?'class="'.$this->className.'"':'').'><tr>';//echo '<p>'.$pages.'/'.$page.'</p>';
            for ($i=0;$i<$this->Dataset->FieldCount;$i++) if(!in_array(strtolower($dbControlsType[$this->Dataset->Table][$this->Dataset->Fields[$i]->orgname]),['password','hidden'])) echo '<th>'.$this->Dataset->Fields[$i]->name.'</th>';
            if ($this->editable) echo '<th class="tblNew" onclick="doNewRecord(this);">New</th><th></th>';
            if ($this->CheckList) echo '<th title="Select All"><input type="checkbox" onchange="$(this).closest(\'table\').find(\'td input:checkbox\').prop(\'checked\',this.checked).trigger(\'change\');"/></th>';
            echo '</tr>';
            if($record>0) $this->Dataset->Seek ($record);
            while ((!$this->Dataset->EOF()&&($row<$this->PageRows||$this->PageRows==false))){
                echo '<tr class="data">';
                $row++;
                if ($this->editable) echo '<td hidden>'.$this->Dataset->strWhereKeys().'</td>';
                for ($i=0;$i<$this->Dataset->FieldCount;$i++) if(!in_array(strtolower($dbControlsType[$this->Dataset->Table][$this->Dataset->Fields[$i]->orgname]),['password','hidden'])) {
                  if (in_array($this->Dataset->Fields[$i]->type,[MYSQLI_TYPE_NEWDECIMAL,MYSQLI_TYPE_SHORT,MYSQLI_TYPE_LONGLONG,MYSQLI_TYPE_DOUBLE,MYSQLI_TYPE_FLOAT,MYSQLI_TYPE_LONG]))
                       echo '<td style="text-align:right">'.$this->Dataset->Values[$i].'</td>';
                  else echo '<td>'.$this->Dataset->Values[$i].'</td>';
                }
                if ($this->editable) echo '<td class="tblEdit" onclick="doEditRecord(this);">Edit</td><td class="tblDelete" onclick="doDelRecord(this);">Del</td>' ;
                if ($this->CheckList) echo '<td><input type="checkbox" onchange="$(event.target).closest(\'tr\')[0].selected=this.checked;"/></td>';
                echo '</tr>';
                $this->Dataset->Next();
            }
            echo '</table>';
            if ($pages>=$this->Page){
                echo '<div  id="pages">Page';
                for ($i=1;$i<=$pages;$i++) {
                  echo (($this->Page==$i)? " $i ":sprintf(' <a href="%s?page=%d">%d</a>  ', filter_input(INPUT_SERVER, 'PHP_SCRIPT'),$i,$i));
                }
                echo '</div>';
            }
    }
}

class Dataset {
    //put your code here
     
   
    private $qry;
    public $Fields;
    public $Values;
    public $link;
    public $SQL;
    public $RecNo;
    private $FBOF;
    private $FEOF;
    public $FieldCount;
    public $RecordCount;
    public $Keys;
    public $strKeys;
    public $Table;
    public $Affected;
    public $DirectAffected;
    public $DirectFields;
    public $DirectValues;
    public $DirectRecNo;
    public $AutoCommit;
    public function __construct($link,$sql=''){
        $this->FieldCount = 0;
        $this->RecordCount=0;
        $this->link=$link;
        $this->AutoCommit=true;
        $this->SQL=$sql;
    }
    public function LocateKey($vals,$keys=false){
        if (!$keys) $keys=array_keys($this->Keys);
        if (count($vals)>count($keys)) return false;
        $where=[];
        foreach ($keys as $i=>$key) {
            $where[]=$key.' = '. QuotedStr($vals[$i]);
        }
        $x=sprintf('select * from (%s) loc where %s',$this->SQL,join(' and ',$where));
        
        //die(print_r($this->execute($x),true));
        return $this->execute($x);
    }
    public function KeyValues(){
        $r=[];
        foreach ($this->Keys as $key){
            $r[]=$this->Values[$key->orgname];
        }
        return $r;
    }
    public function KeyAssoc(){
        $r=[];
        foreach ($this->Keys as $key=>$field){
            $r[$key]=$this->Values[$key];
        }
        return $r;
    }
    public function sqlFieldVal($field){
        if (!is_int($field)) {
            for ($i=0;$i<count($this->Fields);$i++)
                if ($field==$this->Fields[$i]->orgname) {
                    $fidx=$i;
                    break;
                }
        } else {$fidx=$field;}
        if (isset($fidx)){
          if (is_null($this->Values[$fidx])) return 'null' ;else
          switch ($this->Fields[$fidx]->type){
            case MYSQLI_TYPE_DATE:
            case MYSQLI_TYPE_DATETIME:
            case MYSQLI_TYPE_NEWDATE: return QuotedStr($this->Values[$fidx]);
                break;
            case MYSQLI_TYPE_TINY:
            case MYSQLI_TYPE_SHORT:
            case MYSQLI_TYPE_LONG: return $this->Values[$fidx];
                break;
            case MYSQLI_TYPE_DOUBLE:
            case MYSQLI_TYPE_FLOAT:
                return $this->Values[$fidx];
                break;
            case MYSQLI_TYPE_STRING:case MYSQLI_TYPE_CHAR:case MYSQLI_TYPE_VAR_STRING:
                return QuotedStr($this->Values[$fidx]);
                break;
            default:
                return QuotedStr($this->Values[$fidx]);
          } 
        }
    }
    
    public function strWhereKeys(){
      foreach($this->Keys as $key=>$val){
          //echo strval( $key);
          
          $strWhereKeys=$strWhereKeys.' and '.$key.(is_null($this->Values[$val->name])?' is null':'='.$this->sqlFieldVal($key));
      }
      return encrypt(substr($strWhereKeys,4)) ;
    }   
    public function isActive(){
//        echo 'isset='.isset($this->qry);
//        echo '<br> success='.($this->qry!=FALSE);
        return(isset($this->qry));
    }
    public function InsertRec($table,$values){
        $sqlvals='';
        foreach ($values as $value) $sqlvals=$sqlvals.',\''.addslashes($value).'\'';
        ltrim($sqlvals,",");
        $this->qry=mysqli_query($this->link, "insert into ".$table." values(".$sqlvals.")");
        if ($this->AutoCommit) mysqli_commit($this->link);
        return( mysqli_affected_rows($this->link));  
    }
    public function DeleteRec($table,$where){
       
        $this->qry=mysqli_query($this->link, "delete from ".$table." where ".$where);
        if ($this->AutoCommit) mysqli_commit($this->link);
        return(mysqli_affected_rows($this->link));  
        
    }
    public function Commit(){
        mysqli_commit($this->link);
    }
    public function Rollback(){
        mysqli_rollback($this->link);
    }
    public function Execute($sql=''){
        
        if ($sql=='') $sql=$this->SQL;
        $r=mysqli_query($this->link, $sql);
        if (!$r){
            $err=mysqli_error($this->link);
            mysqli_rollback($this->link);
            writelog(sprintf('ERROR [%s] SQL: %s',$err,$sql));
            throw new mysqli_sql_exception('<span style="color: red">'.$err.' <br/><code>{'.($_SESSION['authflag']==31?$sql:'').'}</code></span>');
        
        }
        $this->Affected=  mysqli_affected_rows($this->link);
        unset($this->DirectFields);
        unset($this->DirectValues);
        if ($this->AutoCommit) mysqli_commit($this->link);
        if (!is_bool($r)) {
            if(mysqli_num_rows($r)>0) 
              $this->DirectValues= mysqli_fetch_array($r);
            $this->DirectFields= mysqli_fetch_fields($r);
        }
       // echo "Resutl :".print_r($this->DirectValues);
        if (($r!==true)&&$r) mysqli_free_result($r);
        return $this->DirectValues;
    }
    public function BOF() {
        return ($this->FBOF);
    }
    public function EOF() {
        return ($this->FEOF);
        
    } 
    public function Next(){
       $val=mysqli_fetch_array($this->qry);
       if (isset($val)) foreach ($val as $key=>$value) $val[$key]=is_string($value)?iconv('utf-8', 'utf-8', $value):$value;
       if(!$val) $this->FEOF=True;
       else {
            $this->Values=$val;
            $this->RecNo++;
            $this->FBOF=false;
            $this->FEOF=false;
       }           
    }
    public function Seek($RecNo){
        if ($RecNo <$this->RecordCount){
            if ($RecNo>=0&&!mysqli_data_seek($this->qry, $RecNo))
                    throw new mysqli_sql_exception('Cannot seek Dataset1');
            $this->Values=mysqli_fetch_array($this->qry);
            if (isset($this->Values)) foreach ($this->Values as $key=>$value) $this->Values[$key]=is_string($value)?iconv('utf-8', 'utf-8', $value):$value;
            $this->RecNo=$RecNo;
            
        }
        if ($this->RecordCount==0){
            $this->FBOF=true;
            $this->FEOF=true;
        }
    }
    public function First(){
        $this->Seek(0);
        $this->FBOF=true;
    }
    public function Last(){
        $this->Seek($this->RecordCount-1);
        $this->FEOF=true;
//            $this->RecNo--;
//            mysqli_data_seek($this->qry, $this->RecNo-1);
//            $this->values=mysqli_fetch_array($this->qry);
//            if (isset($this->Values)) foreach ($this->Values as $key=>$value) $this->values[$key]=iconv('windows-1256', 'utf-8', $value);
            $this->FBOF=false;
            $this->FEOF=false;
            
    }
    public function Prior(){
        if ($this->RecNo >0) {
            $this->Seek($this->RecNo-1);
        } else $this->FBOF=true;
    }
    
    public function Refresh(){
        $bm=$this->RecNo;
        $this->Close();
        $this->Open($bm);
    }

    public function Close() {
        if (isset($this->qry)) {
            if (!is_bool($this->qry)) mysqli_free_result($this->qry);
            $this->qry=null;
        }
    }
    
    public function Open($Rec=0) {
        if (!$this->link) throw new mysqli_sql_exception('no database link specified');
        if (!$this->SQL&&isset($this->Table)) $this->SQL='select * from '.$this->Table;
        if (!$this->SQL) throw new mysqli_sql_exception('no SQL Query specified');
      //  echo 'SQL: '.$this->SQL;
        $sqls= explode(';', $this->SQL);
        for ($i=0;$i<count($sqls);$i++){
          $sql=$sqls[$i];  
          $this->Close();
          $this->qry = mysqli_query($this->link,$sql);
          if (!$this->qry) {
            echo "<code style=\"text-align:left;color: red\"><pre>".($_SESSION['authflag']==31?$sql:'')."</pre></code>";
            $err=mysqli_error($this->link);
            $this->Rollback();
            writelog(sprintf('ERROR [%s] SQL: [%s]',$err,$sql));
            throw new mysqli_sql_exception($err);
          }
        }
        //$this->Rollback();
        $this->RecordCount= mysqli_num_rows($this->qry);
//        if ($this->qry===true) 
//            $this->Affected= mysqli_affected_rows($this->link);
//        else
            $this->Affected=0;
        $this->FieldCount= mysqli_field_count($this->link);
        $this->FEOF=!$this->FieldCount>0;
        $this->FBOF=true;
//        while ($this->Fields[]= mysqli_fetch_field($this->qry));
//        array_pop($this->Fields);// last field is null terminted
        $this->Fields=mysqli_fetch_fields($this->qry);
        $this->strKeys='';
        $this->Keys=[];
        
        foreach($this->Fields as $field){ //if($this->Table==='fwindicatormaster') echo $field->orgname.':'.$field->flags.' '; 
            if ((strcasecmp($field->orgtable,$this->Table)==0||$this->Table=='')&&($field->flags & MYSQLI_PRI_KEY_FLAG)>0){
              $this->strKeys .= ','.$field->orgname;
              $this->Keys[$field->orgname]=$field;
            }
        }
        if ($this->Keys===[]){
            foreach($this->Fields as $field){
              if (strcasecmp($field->orgtable,$this->Table)==0||$this->Table==''){
                $this->strKeys .= ','.$field->orgname;
                $this->Keys[$field->orgname]=$field;
              }
            }
        }
      $this->strKeys = ltrim($this->strKeys,',');   
      //$this->Keys=  explode(',',$this->strKeys);

      if ($Rec < $this->RecordCount)
        $this->Seek($Rec);
      else $this->Seek($this->RecordCount-1);
    }
}
?>
