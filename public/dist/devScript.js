function insertAfter(e,t){e.parentNode.insertBefore(t,e.nextSibling)}var element=document.querySelector('iframe[src^="https://adilo.bigcommand.io/"]'),sticky=!1,pause=!1,status=!1;window.addEventListener("message",function(e){sticky=e.data.sticy,pause=e.data.pause,status=e.data.status}),document.addEventListener("DOMContentLoaded",function(){const e=getComputedStyle(element);var t=document.createElement("div");t.setAttribute("id","iframeOverlay"),t.style.width=element.clientWidth+"px",t.style.height=element.clientHeight+"px",t.style.background="#000000de",t.style.position=e.position,t.style.left=e.left,t.style.right=e.right,t.style.bottom=e.bottom,t.style.top=e.top,t.style.left=e.left,t.style.display="none",insertAfter(element,t)},!1),window.addEventListener("scroll",function(e){var t=element,s=element.contentWindow,i=document.getElementById("iframeOverlay");new IntersectionObserver(function(e){!0===e[0].isIntersecting&&(1===e[0].intersectionRatio||(e[0].intersectionRatio>.5?(element.removeAttribute("style"),i.style.display="none",pause&&!sticky&&s.postMessage({visibility:"true"},element.getAttribute("src"))):sticky?"false"==status?(t.style.position="fixed",t.style.right="20px",t.style.top="20px",t.style.left="auto",t.style.bottom="auto",t.style.height="170px",t.style.width="300px",i.style.display="block",s.postMessage({visibility:"true"},element.getAttribute("src"))):s.postMessage({visibility:"false"},element.getAttribute("src")):pause&&s.postMessage({visibility:"false"},element.getAttribute("src"))))},{threshold:[0,.5,1]}).observe(element);var n=document.querySelector("#iframeOverlay");new IntersectionObserver(function(e){!0===e[0].isIntersecting&&(1===e[0].intersectionRatio||e[0].intersectionRatio>.5&&element.removeAttribute("style"))},{threshold:[0,.5,1]}).observe(n)});