/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function logout(){
    $.post('conn.php',{logout:1},function(data,status){location.reload()});
}
function changePassword(){
    frmChangePass=$('<div>').html('<script src="js/jquery/phpjs.md5.js"></script>Password &nbsp;<input class="input" type="password"> &nbsp;&nbsp; ReEnter <input class="input" type="password">').appendTo($('body')).dialog({modal:true,
        width:420,
        title:'Change Password',
        buttons:{
            Ok:function(e){
//                console.log('submitting',{hash:md5($(this).find('input:eq(0)').val())});
                if ($(this).find('input:eq(0)').val()!==$(this).find('input:eq(1)').val()){
                    
                    alert('Password doesn\'t match');
                    return;
                }
                $.post('conn.php',{hash:md5($(this).find('input:eq(0)').val()+strUser)},function(data,status){
                   alert(data);
                   console.log(data);     
                });
                $(this).dialog('close')}, 
            Cancel:function(e){$(this).dialog('close')}
        },
        close:function(e){$(this).dialog('destroy').remove()}
    });
}

function openNav() {
    s=$("#sidenav").width();
    if (s===0){
      $("#sidenav").width(250);
      $("#mainpage").css('margin-left',250);
    }
    else {
      $("#sidenav").width(0);
      $("#mainpage").css('margin-left',0);      
    }
   // $("#wait").show().children(".loader").hide();
    
}

function closeNav() {
    $("#sidenav")[0].style.width = 0;
    $("#mainpage")[0].style.marginLeft = 0;
   // $("#wait").hide().children(".loader").show();
}

