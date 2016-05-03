$(document).ready(function(){
  var display_size = $( window ).width();
  if(display_size <= 768){
    $( "#toggle-div" ).hide();
  }
    $(".tgl-button").click(function(){
        $("#toggle-div").slideToggle();
    });
});
