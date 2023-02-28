var docsyardContentId = ''; 
var btnText = ''; 
var docsyardPanel = document.createElement('div');
var docsyardtogbtn = document.createElement('button');
function showDocksyard(docsyardContentId) {
    if (docsyardPanel.style.display === 'none') {
        docsyardPanel.style.display = 'block';
        var iframe = document.createElement('iframe');
        iframe.setAttribute('style', 'border: 3px solid #f1f1f1;height:500px;width:400px;');
        iframe.src = 'http://docsyard.devapp/embed/'+docsyardContentId.toString();
        docsyardPanel.appendChild(iframe);
    } else {
        docsyardPanel.style.display = 'none';
        docsyardPanel.innerHTML = '';
    }
}

function loadDocksyard(docsyardContentId, btnText) {
	var docsyardContentId = docsyardContentId;
	var btnText = btnText;
	if (btnText == '' || btnText == undefined) {
		btnText = 'Docsyard';
	}

	if (docsyardContentId !== '') {	
		docsyardtogbtn.innerHTML = btnText;
		docsyardtogbtn.setAttribute('style', 'background-color: #555;color: white;padding: 5px 10px;border: none;cursor: pointer;opacity: 0.8;position: fixed;bottom: 10px;right: 15px;width: auto;');
		docsyardtogbtn.setAttribute('onclick', 'showDocksyard("'+docsyardContentId+'")');
		docsyardPanel.setAttribute('id', 'docsyardPanel');
		docsyardPanel.setAttribute('style', 'position: fixed;bottom: 60px;right: 15px;z-index: 9999999;');
		docsyardPanel.style.display = 'none';
		document.body.appendChild(docsyardtogbtn);
		document.body.appendChild(docsyardPanel);
	}
}
//# sourceMappingURL=../source-maps/embed-docsyard-app.src.js.map
