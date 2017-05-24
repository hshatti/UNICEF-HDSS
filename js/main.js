/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


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



Array.prototype.SumArray = function (arr) {
    var sum = [];
    if (arr != null && this.length == arr.length) {
        for (var i = 0; i < arr.length; i++) {
            sum.push(this[i] + arr[i]);
        }
    }

    return sum;
}