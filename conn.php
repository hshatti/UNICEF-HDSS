<?php

                        function alert($msg){echo '<script>alert("'.$msg.'");</script>';}
                        function writelog($data,$thefile='../log/log'){
                            $thefile.=time().'.txt';
                            if (!file_exists('../log')) mkdir('../log');
                            file_put_contents($thefile, $data,FILE_APPEND);
                        }
                        
                        $dbserver='p:localhost';
                        $dbname='hdss';
                        $dbpass='hdssview';
                        $dbuser='hdssview';
                        $dbusersuper='hdss';
                        $dbpasssuper='hdss';
                        date_default_timezone_set('UTC');
                        if (session_status()!=PHP_SESSION_ACTIVE) session_start();
                        if ($_SESSION['authflag']<2){
                          $dblink=mysqli_connect($dbserver,$dbuser, $dbpass,$dbname);
                        }
                        else 
                        {
                            $dblink=mysqli_connect($dbserver,$dbusersuper, $dbpasssuper,$dbname);
                        }
                        if (!$dblink) throw new Exception('Unable to connect /'. mysqli_error());
                        //if (!isset($_SESSION['username'])&&!isset($_POST['un'])) {print_r($_SERVER);$isGuest=true;return;}else $isGuest=false;
                    
                        mysqli_autocommit($dblink, FALSE);
                        include_once './dataset.php';           
                        //$_SESSION['tbls']=$tbls;
                        if (!isset($_SESSION['openssl_iv'])) 
                            $_SESSION['openssl_iv']= openssl_random_pseudo_bytes(8);
                        $lasttime=$_SESSION['time'];
                        $q=new Dataset($dblink);
                        $q->Execute("select * from fwvars where ID='YOB'");
                        $YOB=$q->DirectValues['Val'];
                        $_SESSION['YOB']=$YOB;
                        $_SESSION['time']=time();
                        //if ($_SESSION['redirecting']) return;
//                        if(isset($lasttime)) echo '<script>alert("logged at ['.$lasttime.']");</script>';
                        $un=$_POST['un'];
                        $ps=$_POST['ps'];
                        $logout=$_POST['logout'];

                        if(isset($logout)) {
                            mysqli_close($dblink);
                            session_unset();
                            session_destroy();
                            header('Location: index.php');
                            exit();
                        }
                        elseif (isset($un)&&isset($ps)){
                            //echo $un;echo '<br>';echo $ps; echo '<br>----<br>';
                            $un=  mysqli_escape_string($dblink,$un);
                            $ps= mysqli_escape_string($dblink,$ps);
                            
                            $r=mysqli_query($dblink,"select * from fwusers where upper(username)=upper('$un')");
                            if (!$r) {
                                // 'table does not exist';
                                throw new Exception('wazzafak???!!!');
                            }
                            if (mysqli_num_rows($r)>0){
                                $row=  mysqli_fetch_assoc($r);
                                if (  $row['password']== base64_encode(hex2bin($ps))) {
                                    $_SESSION['time']=time();$sectors=[];
                                    $_SESSION['username']=  addslashes($un);
                                    $_SESSION['fullname']=($row['FullName']==''?$un:$row['FullName']);
                                    $_SESSION['authflag']=$row['flag'];
                                    $_SESSION['CountryId']=$row['CountryId'];
                                    $q->SQL='show tables';
                                    $q->Open();
                                    while (!$q->EOF()) {$tbls[]=strtolower($q->Values[0]);$q->Next();}
                                    $q->SQL=sprintf('select * from fwsectors s join fwusersector u on u.SectorId=s.SectorId and username=%s',QuotedStr($_SESSION['username']));
                                    $q->Open();
                                    while (!$q->EOF()) {$sectors[]=$q->Values['SectorId'];$q->next();}
                                    $_SESSION['sectors']=$sectors;
                                    $fullname=$_SESSION['fullname'];
                                    if ($_SESSION['authflag']>1) { // change to a db supperuser if allowed
                                        mysqli_close($dblink);
                                        $dblink=mysqli_connect($dbserver,$dbusersuper, $dbpasssuper,$dbname);
                                    }
                                    session_commit();
                                    if ($_SESSION['authflag']==1) header('Location: ./main.php');
                                    else header('Location: ./activities.php');
                                    exit();
                                } else {
                                    $_SESSION['msg']='Wrong User/Password';
                                    //echo '<script>alert("wrong password in '.$lasttime.'");</script>';
                                    session_commit();
                                    header('Location: ./index.php');
                                    exit();
                                };
                            } else {
                                $_SESSION['msg']='Wrong User/Password';
                                //echo '<script>alert("user not found in '.$lasttime.'");</script>';
                                session_commit();
                                header('Location: ./index.php');
                                exit();
                                
                            }
                            
                        } elseif (isset($lasttime)){
                            if (0)//((time() - $lasttime)>(60*30)) // if session was idle more than 30 Minuts
                            {
                                //session_unset();
                                $_SESSION['msg']='session expired!';
                                header('Location: ./index.php');
                                exit();
                            } else {  //echo '<script>alert("logged in '.date('d/m/Y',$lasttime).'");</script>';

                                //header('Location: ./main.php');
                                $_SESSION['time']=time();
                                $fullname=$_SESSION['fullname'];
//                                $q->Execute("select * from fwvars where ID='YOB'");
//                                $YOB=$q->DirectValues['Val'];
//                                $_SESSION['YOB']=$YOB;
                                $q->SQL='show tables';
                                $q->Open();
                                while (!$q->EOF()) {$tbls[]=strtolower($q->Values[0]);$q->Next();}
                                $q->SQL=sprintf('select * from fwsectors s join fwusersector u on u.SectorId=s.SectorId and username=%s',QuotedStr($_SESSION['username']));
                                $q->Open();$sectors=[];
                                while (!$q->EOF()) {$sectors[]=$q->Values['SectorId'];$q->next();}
                                $_SESSION['sectors']=$sectors;
                                $q->Execute(sprintf('select * from fwusers where username=%s',QuotedStr($_SESSION['username'])));
                                $_SESSION['authflag']=$q->DirectValues['flag'];
                                $_SESSION['CountryId']=$q->DirectValues['CountryId'];
                            }
                            
                        }
                        else{
                            
                               // if (stripos(debug_backtrace()[1]['file'],'index')===false)
                                $_SESSION['redirecting']=true;
                               // header('Location: ./index.php');
                                return;
                        }    
                        //$_SESSION['name']='kokoo';
                        /*echo 'sessions : <strong>'.  print_r($_SESSION,true).'</strong><hr/>';
                        echo 'cookies : <strong>'.  print_r($_COOKIE,true).'</strong><hr/>';
                        echo 'requests : <strong>'.  print_r($_REQUEST,true).'</strong><hr/>';
                        echo 'server : <strong>'.  print_r($_SERVER,true).'</strong><hr/>';
                        echo 'ENV : <strong>'.  print_r($_ENV,true).'</strong><hr/>';
                        echo 'GET : <strong>'.  print_r($_GET,true).'</strong><hr/>';
                        echo 'POST : <strong>'.  print_r($_POST,true).'</strong><hr/>';
                        echo 'Files : <strong>'.  print_r($_FILES,true).'</strong><hr/>';
                        echo 'requests : <strong>'.  print_r($_REQUEST,true).'</strong><br/>';
                        */
                        //mysqli_set_charset($dblink,'windows-1256' );
                        
function encrypt($data) {
   return openssl_encrypt($data, 'des3', session_id(),0, $_SESSION['openssl_iv']);
}
function decrypt($data) {
   return openssl_decrypt($data, 'des3', session_id(),0,  $_SESSION['openssl_iv']);
}
function QuotedStr($str,$quote='\''){
    global $dblink;
    return($quote. mysqli_real_escape_string($dblink,trim($str)).$quote);
}
function Insert(&$str,$strToInsert,$start){
    $str= substr_replace($str, $strToInsert, $start,0);
    return($str);
}

function Delete(&$str,$start,$length){
    $str=substr_replace($str, '', $start, $length);
    return($str);
}

function printf_array($format, $arr) 
{ 
    return call_user_func_array('printf', array_merge((array)$format, $arr)); 
} 
function sprintf_array($format, $arr) 
{ 
    return call_user_func_array('sprintf', array_merge((array)$format, $arr)); 
}

function isSqlQuery($sql){
    return (stripos($sql,'select ')!==false);
}
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
