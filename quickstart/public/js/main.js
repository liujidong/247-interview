// main.js

var clip = new ZeroClipboard( $('.zeroclipboard-button'), {   
  moviePath: "/img/ZeroClipboard.swf"
} );

clip.on( 'load', function(client) {
//   alert( "movie is loaded" );
} );

clip.on( 'complete', function(client, args) {
    alert('Text has been copied to the clipboard');
} );

clip.on( 'mouseover', function(client) {
//   alert("mouse over");
} );

clip.on( 'mouseout', function(client) {
  // alert("mouse out");
} );

clip.on( 'mousedown', function(client) {

  // alert("mouse down");
} );

clip.on( 'mouseup', function(client) {
  // alert("mouse up");
} );