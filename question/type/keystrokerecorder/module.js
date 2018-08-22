document.onload = function() {
  console.log("loaded");
  let iframe = document.getElementById("biometric-iframe");
  iframe.contentWindow.postMessage(document.querySelector("moodle-user-info").innerHtml, '*');
}
