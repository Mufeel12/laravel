var element = document.querySelector('iframe[src^="https://adilo.bigcommand.io/"]');

function insertAfter(referenceNode, newNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

var sticky = false;
var pause = false;
var status = false;

window.addEventListener('message', function (e) {
    sticky = e.data.sticy;
    pause = e.data.pause;
    status = e.data.status
    console.log(e.data, '----');
})

document.addEventListener('DOMContentLoaded', function() {
    const style = getComputedStyle(element)
    console.log(style, 'style');
    var div = document.createElement('div');
    div.setAttribute('id', 'iframeOverlay');
    div.style.width = element.clientWidth+'px';
    div.style.height = element.clientHeight+'px';
    // div.style.width = style.width+'px';
    // div.style.height = style.height+'px';
    div.style.background = "#000000de";
    div.style.position = style.position;
    div.style.left = style.left;
    div.style.right = style.right;
    div.style.bottom = style.bottom;
    div.style.top = style.top;
    div.style.left = style.left;
    div.style.display = "none";
    insertAfter(element, div);
}, false);

window.addEventListener('scroll', function(e) {

    // window.addEventListener('message', function (e) {
    //    sticky = e.data.sticy;
    //    pause = e.data.pause;
    //    status = e.data.status
    // })

    var h = element;
    var wn = element.contentWindow;
    var overlay = document.getElementById('iframeOverlay');
    var observer = new IntersectionObserver(function(entries) {
        // console.log(entries);
        if(entries[0]['isIntersecting'] === true) {
            if(entries[0]['intersectionRatio'] === 1){
                // console.log('Target is fully visible in screen');
            }
            else if(entries[0]['intersectionRatio'] > 0.5){
                // console.log('More than 50% of target is visible in screen');
                element.removeAttribute("style");
                overlay.style.display = 'none';
                if(pause && !sticky){
                    wn.postMessage({'visibility': "true"}, element.getAttribute('src'));
                }
            }
            else{
                // console.log(sticky, 'sticky',status, '!status', pause);
                if(sticky){
                        if(status == 'false'){
                            h.style.position = "fixed";
                            h.style.right = '20px';
                            h.style.top = '20px';
                            h.style.left = 'auto';
                            h.style.bottom = 'auto';
                            h.style.height = '170px';
                            h.style.width = '300px';
                            overlay.style.display = 'block';
                            wn.postMessage({'visibility': "true"}, element.getAttribute('src'));
                        }else{
                            wn.postMessage({'visibility': "false"}, element.getAttribute('src'));
                        }
                }else{
                    if(pause){
                        wn.postMessage({'visibility': "false"}, element.getAttribute('src'));
                    }
                }
                // console.log('Less than 50% of target is visible in screen');
            }
        }
        else {
            // console.log('Target is not visible in screen');
        }
    }, { threshold: [0, 0.5, 1] });
    observer.observe(element);

    var i = document.querySelector("#iframeOverlay");

    var observer1 = new IntersectionObserver(function(entries1) {
        // console.log(entries);
        if(entries1[0]['isIntersecting'] === true) {
            if(entries1[0]['intersectionRatio'] === 1){

                // console.log('Target is fully visible in screen ----------------');
            }
            else if(entries1[0]['intersectionRatio'] > 0.5){
                element.removeAttribute("style");
            }
            else{
                // console.log('Less than 50% of target is visible in screen ----------------');
            }
        }
        else {
            // console.log('----------------');
        }
    }, { threshold: [0, 0.5, 1] });
    observer1.observe(i);
})