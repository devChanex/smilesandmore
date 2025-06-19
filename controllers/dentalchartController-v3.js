let currentTooth = null;
let currentClientId = null;
let penColor = 'red';

document.querySelectorAll('input[name="penColor"]').forEach(input => {
    input.addEventListener('change', () => penColor = input.value);
});

document.body.addEventListener('click', function (e) {
    // Make sure it’s a .tooth img
    if (e.target && e.target.matches('.tooth img')) {
        const toothElement = e.target.closest('.tooth');
        currentTooth = toothElement.dataset.tooth;
        currentClientId = toothElement.dataset.clientid;
        currentRemarks = toothElement.dataset.remarks;
        document.getElementById("remarkSelect").value = currentRemarks;

        document.getElementById('toothImage').src = e.target.src;
        clearSVGRegions();

        const modal = new bootstrap.Modal(document.getElementById('drawingModal'));
        modal.show();
    }
});


function clearSVGRegions() {
    ["top", "bottom", "left", "right", "center"].forEach(id => {
        document.getElementById(id).setAttribute("fill", "transparent");
    });
}

["top", "bottom", "left", "right", "center"].forEach(id => {
    document.getElementById(id).addEventListener("click", () => {
        document.getElementById(id).setAttribute("fill", penColor);
    });
});

function saveRegion() {
    const svgNode = document.getElementById("svg-wrapper");
    var clientId = document.getElementById("clientid").value;
    var remarks = document.getElementById("remarkSelect").value;
    bootstrap.Modal.getInstance(document.getElementById('drawingModal')).hide();
    html2canvas(svgNode, {
        scale: 1, // Prevent excessive upscaling
        useCORS: true // If you're serving cross-origin images
    }).then(canvas => {
        const imageData = canvas.toDataURL("image/png");

        const formData = new FormData();
        formData.append("tooth", currentTooth);
        formData.append("image", imageData);
        formData.append("clientid", clientId);
        formData.append("remarks", remarks);

        fetch("dentalcharts/save_remarks.php", {
            method: "POST",
            body: formData
        }).then(res => res.json())
            .then(data => {
                if (data.status === "success") {

                    getclientdentalChart();
                }
            });
    });


}

function resetDrawingModal() {
    // Reset pen color to red
    document.querySelector('input[name="penColor"][value="red"]').checked = true;

    // Reset remarks dropdown
    document.getElementById('remarkSelect').value = "-";

    // Reset tooth image to default
    const toothImage = document.getElementById('toothImage');
    toothImage.src = "dentalcharts/tooth_1.png";

    // Clear SVG (if any marks were added via JS drawing)
    const svg = document.getElementById('svgOverlay');
    Array.from(svg.children).forEach(shape => {
        shape.setAttribute('fill', 'transparent');
    });
}




