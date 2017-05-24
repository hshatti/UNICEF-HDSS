/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/* global txt */
if ($&& typeof $.widget=='function') // am i jQuery?
  $( function() {
    $.widget( "custom.catcomplete",$.ui.autocomplete, {
       _create:function(){ 
         this._super();//calling parent class _create methode
         this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
       },
      _renderMenu: function( ul, items ) {
        var that = this,
          currentCategory = "";
        $.each( items, function( index, item ) {
          var li;
          if ( item.category != currentCategory&&item.category) {
            ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
            currentCategory = item.category;
          }
          li = that._renderItemData( ul, item );
          if ( item.category ) {
            li.attr( "aria-label", item.category + " : " + item.label );
          }
        });
      }
    }),
    $.widget( "custom.combobox", {
      _create: function() {
        this.wrapper = $( "<div>" )
          .addClass( "custom-combobox" )
          .insertAfter( this.element );
        this.element.hide();
        this._createAutocomplete();
        this._createShowAllButton();
        
      },
      _createAutocomplete: function() {
        var selected = this.element.children( ":selected" ),
          value = selected.val() ? selected.text() : "";
 
        this.input = $( "<input>" )
          .prop('disabled',this.element.prop('disabled'))
          .prop('required',this.element.prop('required'))
          .appendTo( this.wrapper )
          .val( value )
          .attr( "title", "" )
          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
          .catcomplete({
            delay: 0,
            minLength: 0,
            source: $.proxy( this, "_source" ),
            select: function(){$(this).parent().prev().trigger('change');}
          })
          .tooltip({
            classes: {
              "ui-tooltip": "ui-state-highlight"
            }
          });
        this.element.on("change",function(){$(this).next().children().val(this.value);});
        this._on( this.input, {
          catcompleteselect: function( event, ui ) {
            //console.log('autocompleteselect: ',ui);
            ui.item.option.selected = true;
            this._trigger( "select", event, {
              item: ui.item.option
            });
          },
 
          catcompletechange: "_removeIfInvalid"
        });
      },
 
      _createShowAllButton: function() {
        var input = this.input,
          wasOpen = false;
 
        $( "<a>" )
          .prop('disabled',this.element.prop('disabled'))
          .attr( "tabIndex", -1 )
          .attr( "title", "Show All Items" )
          .tooltip()
          .appendTo( this.wrapper )
          .button({
            icons: {
              primary: "ui-icon-triangle-1-s"
            },
            text: false
          })
          .removeClass( "ui-corner-all" )
          .addClass( "custom-combobox-toggle ui-corner-right" )
          .on( "mousedown", function() {
            wasOpen = input.catcomplete( "widget" ).is( ":visible" );
          })
          .on( "click", function() {
            input.trigger( "focus" );
 
            // Close if already visible
            if ( wasOpen ) {
              return;
            }
 
            // Pass empty string as value to search for, displaying all results
            input.catcomplete( "search", "" );
          });
          
      },
      _source: function( request, response ) {
        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
        //this.element.html('');
        response( this.element.children( "option" ).map(function() {
          var text = $( this ).text(),cat = $(this).data('category');
          if ( this.value && ( !request.term || matcher.test(text) ) )
            return {
              category:cat,//text.toUpperCase().substr(0,1),
              label: text,
              value: text,
              option: this
            };
        }) );
      },
 
      _removeIfInvalid: function( event, ui ) {
 
        // Selected an item, nothing to do
        if ( ui.item ) {
          return;
        }
        // Search for a match (case-insensitive)
        var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
        this.element.children( "option" ).each(function() {
          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
            this.selected = valid = true;
            return false;
          }
        });
        // Found a match, nothing to do
        if ( valid ) {
          return;
        }
        // Remove invalid value
        this.input
          .val( "" )
          .attr( "title", value + " didn't match any item" )
          .tooltip( "open" );
        this.element.val( "" );
        this._delay(function() {
          this.input.tooltip( "close" ).attr( "title", "" );
        }, 2500 );
        this.input.catcomplete( "instance" ).term = "";
      },
 
      _destroy: function() {
        this.wrapper.remove();
        this.element.show();
      }
    });
  });

function getValues(e){
    v=[];
    for (i=0;i<e.length;i++){
      v[v.length]=e[i].value;  
    }
    return v;
}


function getContents(e,nullIfEmpty){

    v=[];
    for (i=0;i<e.length;i++){
      v[v.length]=nullIfEmpty?null:e[i].textContent;  
    }
    return v;    
}
function getProps(e,p){
      v=[];
    for (i=0;i<e.length;i++){
      v[v.length]=e[i][p];  
    }
    return v;   
}

function filterfieldset(event){
    s=event.target.value;
    $(event.target).next().children('div.checkElement').each(function(i,el){el.hidden=(el.textContent.toLowerCase().indexOf(s.toLowerCase())<0);});
}

function uilookupchange(e){
   //console.log('select:',e.target);
  a=$(e.target).data('lookupfields').toString().split(',');
  if (e.target.tagName=='INPUT'){ 
        return;
  }
  v=$(e.target.selectedOptions[0]).data('lookupvalues').toString().split('||');
  a.forEach(function(e,i){
      $('.dbcontrol#'+e.replace(/ /g,'\\ ')).val(v[i]);
  });
  $('select.dbcontrol[data-lookupfields]').each(function(i,el){
        if(e.target!==el){
          b=$(el).data('lookupfields').split(',');
          c=arrayIntersect(a,b);console.log(a,b);
          if (c.length>0&&c.length+1===a.length) {
              vl=[];
              for (j=0;j<c.length;j++) vl.push(v[j]);
              $(el).find("option[data-lookupvalues='"+vl.join('||').replace(/'/g,"\\'")+"']")[0].selected=true;
              $(el).trigger("change");
          };
        };
  });
  
}

function doDelRecord(a){
    $('#frmEdit').html('<div hidden>'+$(a).parent().children('td:hidden').text()+'</div><h2>This will delete the selected record, Are you sure?</h2>');  
    $(a).parent().children().addClass('marked');
    $('#frmEdit').dialog({title:'Delete Record',modal:true,close:function(){$(a).parent().children().removeClass('marked');$(this).empty().dialog('destroy');},buttons:{ 
          Yes:function(){
            $.post('./fwgo.php',{tbl:$(a).closest('table').data('table'),act:'d',key:$(a).parent().children('td:hidden').text()},function (data,status){
                  //console.log(data);
                  if (data>0) {
                      $(a).parent().remove();
                      //alert(data+' Record deleted.');
                  }
              });
              $(this).dialog("close");
          } , No:function(){
              //$(a).parent().children().removeClass('marked');
              $(this).dialog("close");
          } 
      }
    });
  $('#frmEdit').dialog("open");
  $('.ui-dialog-buttonpane').css({"border-width":"0 0 1px 0",background:"#444"}).insertBefore('.ui-dialog-content');
  
}

function DoExcelUpload(tbl){ 
    var fileInput = document.querySelector('#excelfile');
    var xhr = new XMLHttpRequest();
    $('#uploadprog').show();
    console.log('loading file');
    xhr.open('POST', 'import.php');
    xhr.upload.onloadend=function loadEnd(e) {
      // $('#wait').show();
    };
    xhr.upload.onprogress = function(e) 
    {
        $('#uploadprog').attr('max',e.total);
        $('#uploadprog').val(e.loaded);

    };
    xhr.onload = function()
    {
      fileInput.value='';
      $('#uploadprog').hide();
      if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
        {
          
          $('#ImpResult').html(xhr.responseText);
          //$('#wait').hide();
        }
    };
    // upload success
    var form = new FormData();
    form.append('excelfile', fileInput.files[0]);
    if (tbl) form.append('tbl', tbl);
    xhr.send(form);
}

function doImport(tbl,el){
    var fileInput = document.querySelector('#excelfile');
    var xhr = new XMLHttpRequest();
    $('#uploadprog').show();
    console.log('loading file');
    xhr.open('POST', 'import.php');
    xhr.upload.onloadend=function loadEnd(e) {
       $('#wait').show();
    };
    xhr.upload.onprogress = function(e) 
    {
        $('#uploadprog').attr('max',e.total);
        $('#uploadprog').val(e.loaded);

    };
    xhr.onload = function()
    {
      fileInput.value='';
      $('#uploadprog').hide();
      if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
        {
          
          if (el) $(el).html(xhr.responseText);
          else return(xhr.responseText);
          $('#frmEdit').dialog('close');
          $('#wait').hide();
        }
    };
    // upload success
    var form = new FormData();
    form.append('excelfile', fileInput.files[0]);
    if (tbl) form.append('tbl', tbl);
    xhr.send(form);
}

function doImportPost(e){
   vals=[];
   $('#tblResult tr.data:selected').each(function(i,el){
       vals[vals.length]=getContents($(el).find('td:not(:last)'));
   });
   if (vals.length!=0)
     $.post('import.php',{tbl:$(e.target).data('table'),vals:JSON.stringify(vals),ac:'postimport'},function(data,status){
        if (status='success') {
            res=JSON.parse(data);
            if (res.status){
              location.reload();
              console.log(data);
            }                
            else {
              console.log(data);}
        } 
     });
   else alert('Please select one row at least to import.');
}
function doImportCancel(){
    location.reload();
}
function doPort(a){
    tbl=$(a).closest('table').data('table');
    $('#frmEdit').html(
            '<div id="ImpResult"><h4>Import file</h4><input id="excelfile" type="file" onchange="doImport(\''+ tbl+'\',\'.page-container\');"><progress id="uploadprog" style="display:none"></progress></div><h4> or </h4><div><form method="GET" action="export.php"><input name="tbl" value="'+tbl+'" type="hidden"><input value="Export" type="submit"><select name="fmt"><option value="Excel2007">Excel (*.xlsx)</option><option value="Excel5">Excel5 (*.xls)</option><option value="CSV">CSV (*.csv)</option></select></form></div>');
    $('#frmEdit').dialog({title:'Import/Export',modal:true,autoOpen:false,buttons:null});
    $('#frmEdit').dialog('open');
    
}
function doNewRecord(a){
    $.post('./fwgo.php',{act:'g',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'),par:''},function (data,status) {
       $('#frmEdit').html(data);
       $('.pan-input select').combobox();
//       $('.pan-input select')
       $('.pan-input input[type=search]').catcomplete({
            source:function(request,response){
               $.post('fwgo.php',{act:'l',tbl:this.element.data('table'),dtltable:$(a).closest('table').data('table'),term:request.term},function(data,status){
                 if (status=='success') { 
//                       console.log(data);
                       rec=JSON.parse(data);
                       response(rec);
//                       console.log(rec);
                 }
               });
            },
            select:function(data,i){
                for (var f in i.item.fields)
                    $('.pan-input #'+f).val(i.item.fields[f]);
            }
        }).blur(function(){if (this.value=='') this.getAttribute('data-lookupfields').split(',').forEach(function(f){$('.pan-input #'+f).val('');});});
       $('.pan-input input.datepicker').datepicker({showOn:"button",buttonImageOnly:false/*,buttonImage:"images/calendar.gif"*/,dateFormat:'yy-mm-dd'});
       $('#frmEdit').dialog("open");
    });
    $('#frmEdit').dialog({title:'New Record',autoOpen:false,width:'auto',modal:true,close:function(){$(this).empty().dialog('destroy');},buttons:
                {
                    Save:function(){
                        pwd=$(this).find('.dbcontrol[type=password]').removeClass('ui-state-error');
                        if (pwd.length>1){
                            pwd[1].id='';
                            if(pwd[0].value!=pwd[1].value) {
                              pwd.addClass('ui-state-error').attr("title",'Passwords doesn\'t match.').tooltip().trigger('focus');
                              return;
                            }
                        }
                        req=$(this).find('[required]').removeClass('ui-state-error');
                        if (req.length>0) for (i=0;i<req.length;i++) if ((!req[i].parentElement.parentElement.hidden&&req[i].style.display!='none')&&req[i].value==''){
                            //console.log(req[i]);
                            $(req[i]).addClass('ui-state-error').attr("title",'Field is required!').tooltip().trigger('focus');
                            return;
                        };
                        ids=[];vals=[];dtls=[];
                        $(this).find('.dbcontrol').each(function(i,e){
                            if (!e.hasAttribute('data-lookupfields')&&e.id!='') {ids[ids.length]=e.id;vals[vals.length]=e.value};
                        });
                        $(this).find('fieldset').each(function(i,e){dtl={table:$(e).data('table'),values:getValues($(e).find(':checked'))};dtls.push(dtl);});
                        $.post('/fwgo.php',{act:'i',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'), par:[ids,vals].concat(dtls)},function(data,status){
                            try {rec=JSON.parse(data);} catch (e){ console.log(e.message+'/'+data);}
                            if (rec.status){
                                //location.reload();// ajaxing below instead
                                newTR='<tr class="data">'+rec.data+'</tr>';
                                lasttr=$(a).closest('thead').next();
                                if (lasttr.find('tr').length>0) lasttr.children('tr.data:last').after(newTR);// if found a tbody containing tr elements
                                else {  //nope! i got an orphan header
                                    $(a).closest('thead').after('<tbody>'+newTR+'</tbody>');
                                }
                                $('#frmEdit').dialog("close");
                            }
                            else {
                                $('#frmEdit').prev().find('button:eq(0)').addClass('ui-state-error').attr('title',rec.data).tooltip().tooltip('open');
                                //throw new Error(rec.data); // TODO: translte DB Errsor here into a friendly messages
                            };
                        });
                         
                    },
                    Cancel:function(){
                        $(this).dialog("close");  
                    }
                }
    });
    $('.ui-dialog-buttonpane').css({"border-width":"0 0 1px 0",background:"#444"}).insertBefore('.ui-dialog-content');
    //$('#frmEdit').dialog("open");

}
function doEditRecord(a){
    //console.log($(a).closest('table').data('table'));
    $.post('./fwgo.php',{act:'g',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'),key:$(a).parent().children('td:hidden').text(),par:''},function (data,status) {
       $('#frmEdit').html(data);
       $('.pan-input select').combobox();
       $('.pan-input input[type=search]').catcomplete({
            source:function(request,response){
               $.post('fwgo.php',{act:'l',tbl:this.element.data('table'),dtltable:$(a).closest('table').data('table'),term:request.term},function(data,status){
                 if (status=='success') { 
                       rec=JSON.parse(data);
                       response(rec); 
                 }
               });
            },
            select:function(data,i){
                for (var f in i.item.fields)
                    $('.pan-input #'+f).val(i.item.fields[f]);
            }
        }).blur(function(e){if (e.target.value==='') this.getAttribute('data-lookupfields').split(',').forEach(function(f){$('.pan-input #'+f).val('');});});
       $('.pan-input input.datepicker').datepicker({showOn:"button",buttonImageOnly:false/*,buttonImage:"images/calendar.gif"*/,dateFormat:'yy-mm-dd'});
       $('#frmEdit').dialog("open");
    });
    $(a).parent().children().addClass('marked');
    $('#frmEdit').dialog({title:'Edit Record',width:'auto',autoOpen:false,close:function(){$(a).parent().children().removeClass('marked');$(this).empty().dialog('destroy');},modal:true,buttons:{
            Save:function(){
                pwd=$(this).find('.dbcontrol[type=password]').removeClass('ui-state-error');
                if (pwd.length>1){
                    pwd[1].id='';
                    if(pwd[0].value!=pwd[1].value) {
                      pwd.addClass('ui-state-error').attr("title",'Passwords doesn\'t match.').tooltip().trigger('focus');
                      return;
                    }
                }
                req=$(this).find('[required]').removeClass('ui-state-error');
                if (req.length>0) for (i=0;i<req.length;i++) if ((!req[i].parentElement.parentElement.hidden&&req[i].style.display!='none')&&req[i].value==''){
                    //console.log(req[i]);
                    $(req[i]).addClass('ui-state-error').attr("title",'Field is required!').tooltip().trigger('focus');
                    return;
                };
                ids=[];vals=[];dtls=[];
                $(this).find('.dbcontrol').each(function(i,e){if (!e.hasAttribute('data-lookupfields')&&e.id!='') {ids[ids.length]=e.id;vals[vals.length]=e.value;}});
                $(this).find('fieldset').each(function(i,e){
                    checked=$(e).find(':checked');
                    var dtl={table:$(e).data('table'),values:getValues(checked)};
                    $(e).find('.selected .checkElement').each(function(ii,ee){
                        eee=$(ee).find('[type=text]');
                        if (eee.length>0) dtl.values[ii]+='||'+getValues(eee).join('||');
                    })
                    dtls.push(dtl);
                });
                $.post('/fwgo.php',{act:'u',tbl:$(a).closest('table').data('table'),qry:$(a).closest('table').data('qry'),key:$(a).parent().children('td:hidden').text(), par:[ids,vals].concat(dtls)},function(data,status){
                    try {rec=JSON.parse(data);} catch (e){ console.log(e.message+'/'+data);}
                    if (rec.status){
//                        f=getContents($(a).closest('table').find('th.tblNew:eq(0)').prev().prevAll());
//                        v='';
//                        for (i=0;i<f.length;i++) if (rec.data.fields[f[i]]) v=rec.data.fields[f[i]].value+v;
//                        v='<td hidden>'+rec.data.key+'</td>'+v;
                        $(a).parent().html(rec.data/*v+'<td class="tblEdit" onclick="doEditRecord(this);">Edit</td><td class="tblDelete" onclick="doDelRecord(this);">Del</td>'*/);
                        $('#frmEdit').dialog("close");
                    }
                    else {
                        if (rec.data) $('#frmEdit').prev().find('button:eq(0)').addClass('ui-state-error').attr('title',rec.data).tooltip().tooltip('open');
                        else $('#frmEdit').dialog("close");
                    };
                });
                //$(a).parent().children().removeClass('marked');
                  
            },
            Cancel:function(){
              //$(a).parent().children().removeClass('marked');
              $(this).dialog("close");  
            }
    }});
    $('.ui-dialog-buttonpane').css({"border-width":"0 0 1px 0",background:"#444"}).insertBefore('.ui-dialog-content');
    //$('#frmEdit').dialog("open");
}
var isMouseDown = false;
var startRowIndex = null;
var startCellIndex = null;

function selectableTable(table){
    
    function selectTo(cell) {
        var row = cell.parent('.data');    
        var cellIndex = cell.index();
        var rowIndex = row.index();
        var rowStart, rowEnd, cellStart, cellEnd;
        if (rowIndex < startRowIndex) {
            rowStart = rowIndex;
            rowEnd = startRowIndex;
        } else {
            rowStart = startRowIndex;
            rowEnd = rowIndex;
        }
        if (cellIndex < startCellIndex) {
            cellStart = cellIndex;
            cellEnd = startCellIndex;
        } else {
            cellStart = startCellIndex;
            cellEnd = cellIndex;
        }        
        for (var i = rowStart; i <= rowEnd; i++) {
            var rowCells = cell.parent().parent().find("tr.data").eq(i).find("td");
            for (var j = cellStart; j <= cellEnd; j++) {
                rowCells.eq(j).addClass("ui-selected");
            }        
        }
    }
    table.disableSelection().find("td").mousedown(function (e) {
        isMouseDown = true;
        var cell = $(this);
        table.find(".ui-selected").removeClass("ui-selected"); // deselect everything
        if (e.shiftKey) {
            selectTo(cell);                
        } else {
            cell.addClass("ui-selected");
            startCellIndex = cell.index();
            startRowIndex = cell.parent('.data').index();
        }
        //return false; // prevent text selection
    })
    .mouseover(function () {
        if (!isMouseDown) return;
        table.find(".ui-selected").removeClass("ui-selected");
        selectTo($(this));
    })
    .bind("selectstart", function () {
        //return false;
    });

    $(document).mouseup(function () {
        isMouseDown = false;
    });  

}
function arrayIntersect(a,b){
    return a.filter(function(e){return (b.indexOf(e)!==-1);});
}

function initgraph() {
//                        w=$('div#wait');
//                        w.show();
                        //console.log('initing graph...');
//                        console.log('koko');
//                        $('table#tblResult').selectable({cancel:':hidden',filter:'td'});
//                        console.log('kaka');
                        selectableTable($('.dbgrid .selectable'));
                        p=$('div#params').text();
                        if (p=='') {
                            //w.fadeOut(); 
                            return;
                        };
                        var params=JSON.parse($('div#params').text());
                        if (params.groups) for (var i=0;i<params.groups.length;i++){
                          $('.grp'+i).css(params.groups[i].options);
                        }
                        //console.log('checking groups...');
                        $('.dbgrid tr.grpheader td').prop('colspan',params.colspan);
                        $('.dbgrid').css({width:params.tablewidth});
                        $('.dbgrid td.num').each(function(i,e) { if(!isNaN(e.innerHTML)&&(e.innerHTML!='')) e.innerHTML=Number(e.innerHTML).toLocaleString();});
                        //$('.dbgrid tr.data:even').css({'background':'#f0f0f0'}); // /*'linear-gradient(whitesmoke,lightgray)'*/
                        $('.dbgrid th').resizable({handles:'e'}); //columns can be resized
                        //console.log('headers are resizable...');
                        if (params.progs!=null) for (var i=0;i<params.progs.length;i++)
                          $('.dbgrid tr.data :nth-child('+params.progs[i].col+')>div').each(function(){ 
                            $(this).progressbar({value: Number($(this).attr('value'))} ).children('.progress-label').css( { position: 'absolute', width: '100%', 'text-shadow': '1px 1px 1px #fff'}).text($(this).attr('value')+'%');
                            if(params.progs[i].options) $(this).progressbar().find('.ui-progressbar-value').css(params.progs[i].options);
                          });
                        if (params.tools!=null) for (var i=0;i<params.tools.length;i++){
                          toolHeader=$('.dbgrid tr th:nth-child('+params.tools[i].col+')');
                          toolData=$('.dbgrid tr.data :nth-child('+params.tools[i].col+')>div');
                          toolData.each(function(){ 
                            $(this).parent().prev().attr('title',' ').tooltip({track:true,content:'<b><u>'+toolHeader[0].textContent+'</u></b><br/>'+this.innerHTML,position:{my:'center bottom'}});
                            //if(params.tools[i].options) $(this).find('.tooltip').css(params.tools[i].options);
                          });
                          toolData.parent().remove();
                          toolHeader.remove();
                        }
                        $('#tblResult .grpfooter').each(function(i,e){if (e.textContent==='') e.hidden=true;});//hide summary if no contents  
                        //console.log('footers rendered...');
                        //  ***** Line Analysis *****
                        if (params.anls!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.anls.length;i++){
                            gAnlDivs = $(this).find('tr.data td:nth-child('+params.anls[i].col+')>div');
                            if (params.anls[i].options.labels!=null) 
                              var gAnlLabels = $(this).find('tr.data td:nth-child('+params.anls[i].options.labels+')');
                            gRowCount= gAnlDivs.length;
                            vals = [], labels = [];data=[];
                            for (j=0;j<gAnlDivs.length;j++){
                              data[j]=[];
                              data[j]=gAnlDivs[j].textContent.toString().split(' ');
                              for (k=0;k<data[j].length;k++){ 
                                if (!vals[k]) {
                                  vals[k]=[];vals[k].data=[];}
                                vals[k].data[j]=data[j][k];
                              }
                            }
                            if (gAnlLabels) for (j=0;j<gAnlLabels.length;j++)
                              labels[j]=gAnlLabels[j].textContent;
                            gAnlDivs.parent('td:gt(0)').remove();
                            gAnl=gAnlDivs.first().parent().attr('rowspan',gRowCount).children();
                            gAnl.each(function(){
                                this.textContent='';
                                Raphael($(this)[0],370,270).analyse(vals,labels,params.anls[i].options.line,params.anls[i].options.stack,params.anls[i].options.color);
                            });
                            delete gAnl;delete gAnlDivs;delete gAnlLabels;delete vals;delete Labels; delete gRowCount;
                          }
                        });
                        //console.log('line graphs rendered if any');
                        // ***** Vector Graph (Maps)*****
                        
                        if (params.vecs!=null) for (i=0;i<params.vecs.length;i++) $('.dbgrid th:nth-child('+params.vecs[i].options.vectors+')').hide();
                        //console.log('trying vectors... ');
                        if (params.vecs!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.vecs.length;i++){
                            gVecDivs = $(this).find('tr.data td:nth-child('+params.vecs[i].col+')>div');
                            if (params.vecs[i].options.labels!=null) 
                              var gVecLabels = $(this).find('tr.data td:nth-child('+params.vecs[i].options.labels+')');
                            $(this).find('td:nth-child('+params.vecs[i].options.vectors+')').hide();
                            if (params.vecs[i].options.vectors!=null) 
                              var gVecVectors = $(this).find('tr.data td:nth-child('+params.vecs[i].options.vectors+')');
                            gRowCount= gVecDivs.length;
                            vals = [], labels = [];data=[];vectors=[];
                            for (j=0;j<gVecDivs.length;j++){
                              data[j]=[];
                              data[j]=gVecDivs[j].textContent.toString().split(' ');
                              for (k=0;k<data[j].length;k++){ 
                                if (!vals[k]) {
                                  vals[k]=[];vals[k].data=[];}
                                vals[k].data[j]=data[j][k];
                              }
                            }
                            if (gVecLabels) for (j=0;j<gVecLabels.length;j++)
                              labels[j]=gVecLabels[j].textContent;
                            if (gVecVectors) for (j=0;j<gVecVectors.length;j++)
                              vectors[j]=gVecVectors[j].textContent;
                            gVecDivs.parent('td:gt(0)').remove();
                            gVecVectors.remove();
                            gVec=gVecDivs.first().parent().attr('rowspan',gRowCount).children();
                            if (!params.vecs[i].options.width) params.vecs[i].options.width=500;
                            if (!params.vecs[i].options.height) params.vecs[i].options.height=380;
                            console.log('plotting...');
                            gVec.each(function(){
                                this.textContent='';
                                Raphael($(this)[0],params.vecs[i].options.width,params.vecs[i].options.height).vector(vectors,vals,labels,params.vecs[i].options.stroke,params.vecs[i].options.strokewidth,params.vecs[i].options.fill,null,params.vecs[i].options.font);
                            });
                            console.log('plotted');
                            delete gVec;delete gVecDivs;delete gVecLabels;delete vals;delete Labels; delete gRowCount;delete vectors;
                          }
                        });
                        //console.log('vectors plotted if any...')
                        //  ***** Bar Analysis *****
                        if (params.bars!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.bars.length;i++){
                            gBarDivs = $(this).find('tr.data td:nth-child('+params.bars[i].col+')>div');
                            if (params.bars[i].options.labels!=null) 
                              var gBarLabels = $(this).find('tr.data td:nth-child('+params.bars[i].options.labels+')');
                            gRowCount= gBarDivs.length;
                            vals=[];labels=[];data=[];
                            for (j=0;j<gBarDivs.length;j++){
                              data[j]=[];
                              data[j]=gBarDivs[j].textContent.toString().split(' ');
                              for (k=0;k<data[j].length;k++){ 
                                if (!vals[k]) {
                                  vals[k]=[];vals[k].data=[];}
                                vals[k].data[j]=data[j][k];
                              }
                            }  
                            if (gBarLabels) for (j=0;j<gBarLabels.length;j++)
                              labels[j]=gBarLabels[j].textContent;
                            gBarDivs.parent('td:gt(0)').remove();
                            gBar=gBarDivs.first().parent().attr('rowspan',gRowCount).children();
                            gBar.each(function(){
                                this.textContent='';
                                Raphael(this,370,270).bars(vals,labels,params.bars[i].options.horizonal,params.bars[i].options.stack,params.bars[i].options.color);
                            });
                            delete gBar;delete gBarDivs;delete gBarLabels;delete vals;delete labels; delete gRowCount;
                          }
                        });
                        // ***** handle pie *****
                        if (params.pies!=null) $('.dbgrid tbody').each(function(){
                          for (var i=0;i<params.pies.length;i++){
                            gPieDivs = $(this).find('tr.data td:nth-child('+params.pies[i].col+')>div');
                            //console.log(JSON.stringify(params.pies[i]));
                            if (params.pies[i].options.labels!=null) 
                              var gPieLabels = $(this).find('tr.data td:nth-child('+params.pies[i].options.labels+')');
                            gRowCount= gPieDivs.length;
                            vals = [], labels = [];
                            for (j=0;j<gPieDivs.length;j++)
                              vals[j]=Number(gPieDivs[j].getAttribute('value'));
                            if (gPieLabels) for (j=0;j<gPieLabels.length;j++)
                              labels[j]=gPieLabels[j].textContent;
                            // else labels=vals;
                            gPieDivs.parent('td:gt(0)').remove();
                            gPie=gPieDivs.first().parent().attr('rowspan',gRowCount).children();
                            gPie.each(function(){
                                Raphael($(this)[0],370,270).pieChart(180,130,60,vals, params.pies[i].options.type, labels,'#fff',null/*['skyblue','magenta','yellow','purple','cyan','maroon','orange','gray','green']*/);
                            });
                            delete gPie;delete gPieDivs;delete gPieLabels;delete vals;delete labels; delete gRowCount;
                          }
                        });
                        //$('div#wait').fadeOut('fast');

                    
}
//process export
function fnExcelReport(table,name) { 
$('#wait').show();
 var uri = 'data:application/vnd.ms-excel;base64,'
, template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
, base64 = function(s) { return window.btoa(unescape(encodeURIComponent(s))) }
, format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; }) }
if (!table.nodeType) table = $(table)[0];
var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}
var ua = window.navigator.userAgent;
var msie = ua.indexOf("MSIE "); 
if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){      // If Internet Explorer
    txtArea1.document.open("txt/html","replace");
    txtArea1.document.write(format(template, ctx));
    txtArea1.document.close();
    txtArea1.focus(); 
    txtArea1.document.execCommand("SaveAs",true,"Export.xls");
}
else                 //other browser not tested on IE 11
    window.location.href = uri + base64(format(template, ctx));
    $('#wait').fadeOut('fast');
}