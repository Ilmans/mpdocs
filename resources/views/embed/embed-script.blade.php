var docsyardPanel = document.createElement('div');
var docsyardToggleBtn = document.createElement('button');
window.docsyardIsAlreadyOpened = false;

// Minimize and maximize docsyard panel
function minimizeMaximizePanel() {
    var docsyardIframe = document.getElementById("docsyardPanelIframe"),
        minimizeMaximizeBtn = document.getElementById('lwMinimizeMaximizeBtn');

        if (docsyardIframe.style.height == '500px') {
            docsyardIframe.style.height = '0px';
             docsyardIframe.style.display = 'none';
            minimizeMaximizeBtn.innerHTML = '<strong title="Maximize">&#8599</strong>';
        } else if (docsyardIframe.style.height == '0px') {
            docsyardIframe.style.height = '500px';
             docsyardIframe.style.display = 'block';
            minimizeMaximizeBtn.innerHTML = '<strong title="Minimize">&#8601</strong>';
        }
}

function showBackgroundColor(btn) {
    btn.style.backgroundColor = '#e2e3e3';
}

function hideBackgroundColor(btn) {
    btn.style.backgroundColor = '#ffffff';
}

function showDocsyard(projectSlug, version, articleSlug) {
console.log(window.docsyardIsAlreadyOpened);
    if(window.docsyardIsAlreadyOpened) {
        return minimizeMaximizePanel();
    } else {
        var iframeUrl = '';

        window.docsyardIsAlreadyOpened = true;

        if (articleSlug) {
            iframeUrl = "<?= url('embed') ?>"+"/"+projectSlug.toString()+"/"+version.toString()+"/"+articleSlug.toString();
        } else if (version) {
            iframeUrl = "<?= url('embed') ?>"+"/"+projectSlug.toString()+"/"+version.toString();
        } else if (projectSlug) {
            iframeUrl = "<?= url('embed') ?>"+"/"+projectSlug.toString();
        }

        var minimizeButton = document.createElement('button');
        minimizeButton.setAttribute('id', 'lwMinimizeMaximizeBtn');
        minimizeButton.setAttribute('style', 'position: absolute; margin-left: 350px; border: none; padding: 5px 10px; margin-top: 10px;cursor: pointer; opacity: 0.8; background-color: #ffffff;');
        minimizeButton.innerHTML = '<strong title="Minimize">&#8601</strong>';
        minimizeButton.setAttribute('onclick', 'minimizeMaximizePanel()');
        minimizeButton.setAttribute('onmouseover', 'showBackgroundColor(this)');
        minimizeButton.setAttribute('onmouseout', 'hideBackgroundColor(this)');
        docsyardPanel.append(minimizeButton);

        if (docsyardPanel.style.display === 'none') {
            docsyardPanel.style.display = 'block';
            var iframe = document.createElement('iframe');
            iframe.setAttribute('id', 'docsyardPanelIframe');
            iframe.setAttribute('style', 'border: 1px solid #f1f1f1;height:500px;width:400px;transition: height 0.1s linear;');
            iframe.src = iframeUrl;
            docsyardPanel.appendChild(iframe);
        } else {
            docsyardPanel.style.display = 'none';
            docsyardPanel.innerHTML = '';
        }
    }
}

var Docsyard = {
    load : function(projectSlug, version, articleSlug, buttonName) {

        var btnText = buttonName ? buttonName : 'Docsyard';

        var existingButton = document.getElementById("docsyardButton");
        if(existingButton){
            docsyardToggleBtn = existingButton;
        }

        if (projectSlug !== '') {    
            if(!existingButton) {
                docsyardToggleBtn.innerHTML = btnText;
                docsyardToggleBtn.setAttribute('style', 'background-color: #555;color: white;padding: 5px 10px;border: none;cursor: pointer;opacity: 0.8;position: fixed;bottom: 10px;right: 15px;width: auto;');
            }
            
            docsyardToggleBtn.setAttribute('onclick', 'showDocsyard('+JSON.stringify(projectSlug)+', '+JSON.stringify(version)+', '+JSON.stringify(articleSlug)+')');
            docsyardPanel.setAttribute('id', 'docsyardPanel');
            docsyardPanel.setAttribute('style', 'position: fixed;bottom: 60px;right: 15px;z-index: 9999999; background-color: #fff;');
            docsyardPanel.style.display = 'none';
            //docsyardPanel.insertBefore(minimizeButton, docsyardPanel.firstChild);

            document.body.appendChild(docsyardToggleBtn);
            document.body.appendChild(docsyardPanel);
        }
    }  
};