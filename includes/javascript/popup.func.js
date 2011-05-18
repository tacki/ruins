// Script by Thomas Stich
// http://www.stichpunkt.de/beitrag/popup.html
// use it if you like it
//
// <a href="html-or.jpg" onclick="return popup(this,123,456)" title="..."
// or
// <a href="html-or.jpg" onclick="return popup(this)" title="..."


var pop = null;

function popup(obj,w,h) {
  var url = (obj.getAttribute) ? obj.getAttribute('href') : obj.href;
  if (!url) return true;
  w = (w) ? w += 20 : 500;  // 500px*400px is the default size
  h = (h) ? h += 25 : 400;
  var args = 'width='+w+',height='+h+',resizable,scrollbars';
  pop = window.open(url,'',args);
  return (pop) ? false : true;
}

function popdown() {
  if (pop && !pop.closed) pop.close();
}
