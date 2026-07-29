	window.jsPDF = window.jspdf.jsPDF;

	function getElement (nodeName){
		// Source HTMLElement or a string containing HTML.
		var node = document.querySelector(nodeName);
		var elementHTML = node.cloneNode (true);
		elementHTML.style.width="700px";
/*
		//const reg=/([^\x00-\x7F]\s)\(([\x00-\x7F\s]+)\)/g;
		const reg=/([^\x00-\x7F]\s*)\((\s*[\x00-\x7F]+\s*)\)/g;
		const rpl ='$1\)$2\(';
		elementHTML.innerHTML = elementHTML.innerHTML.replaceAll (reg, rpl);
		const reg2 = /([^\x00-\x7F]+\s*<span>)\(([\x00-\x7F]+)\)</g;
		const rpl2 = '$1\)$2\(<';
		elementHTML.innerHTML = elementHTML.innerHTML.replaceAll (reg2, rpl2);
*/
		return elementHTML;
	}
	
	function savePDF_orig(orientation /*'p' for portrait 'l' for landscape*/)
	{
		var doc = new jsPDF(orientation, 'mm', 'a4');
		var page_height = (orientation == 'p')? 297: 210;
		var page_width = (orientation == 'l')? 297: 210;
		
		doc.addFont("fonts/tahoma.ttf", "Tahoma", "normal");
		//doc.addFont ("https://www.choicescale.ir/wp-content/plugins/psyco-tests/assets/fonts/Vazirmat-Light.woff2","Vazirmatn","normal");
		doc.html(getElement ("#pdfcontent1"), {
			x: 30,//125,
			y: 15,
		//	width: page_width, /*target width in the PDF document*/
		//	windowWidth: 600, //window width in CSS pixels
			callback: function(doc) {
					doc.html(getElement ("#pdfcontent2"), {
						// width: 170, /*target width in the PDF document*/
						// windowWidth: 650, //window width in CSS pixels
						x: 30,//125,
						y: page_height + 5,
						callback: function(doc) {
								doc.html(getElement ("#pdfcontent3"), {
									x: 30,//125,
									y: 2 * page_height + 15,
									callback: function(doc) {
													doc.save('HomogeneityPackage.pdf');
												}
								});
						}
					});
			},
		});
	}

function savePDF(orientation) {
var doc = new jsPDF(orientation, 'mm', 'a4');

// Assuming page_height is defined correctly
var page_height = doc.internal.pageSize.getHeight();

doc.addFont("fonts/tahoma.ttf", "Tahoma", "normal");

// Ensure getElement is defined and correctly gets the element
doc.html(getElement("#pdfcontent1"), {
x: 30,
y: 15,
width: 180, // Set the width of the content
windowWidth: 1080, // Set the window width for html2canvas
callback: function(doc) {
doc.html(getElement("#pdfcontent2"), {
x: 30,
y: page_height + 5,
width: 180, // Set the width of the content
windowWidth: 1080, // Set the window width for html2canvas
callback: function(doc) {
doc.html(getElement("#pdfcontent3"), {
x: 30,
y: 2 * page_height + 15,
width: 180, // Set the width of the content
windowWidth: 1080, // Set the window width for html2canvas
callback: function(doc) {
doc.save('HomogeneityPackage.pdf');
}
});
}
});
},
});
}
	// Function to convert image URL to Base64 string
	function toDataURL(url, callback) {
		var xhr = new XMLHttpRequest();
		xhr.onload = function() {
			var reader = new FileReader();
			reader.onloadend = function() {
				callback(reader.result);
			};
			reader.readAsDataURL(xhr.response);
		};
		xhr.open('GET', url);
		xhr.responseType = 'blob';
		xhr.send();
	}

	// Export chart and add to PDF
	Highcharts.chart('container').exportChart({
			type: 'image/png'
		}, function(data) {
			// Convert image URL to Base64
			toDataURL(data, function(dataUrl) {
				// Create a jsPDF instance
				var pdf = new jsPDF();

				// Add image to PDF
				pdf.addImage(dataUrl, 'PNG', 15, 40, 180, 160);

				// Save the PDF
				pdf.save('report.pdf');
			});
	});