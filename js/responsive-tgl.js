$(document).ready(function(){
    $( "#toggle-div" ).hide();

    $(".tgl-button").click(function(){
        $("#toggle-div").slideToggle();
    });
});
