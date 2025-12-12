<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload PDF for eSign</title>
</head>
<body>
    <h1>Upload PDF for eSign</h1>

    <!-- Form for Uploading PDF -->
    <form id="esignForm" method="POST" enctype="multipart/form-data">
        @csrf
        <label for="sign_name">Signer Name:</label>
        <input type="text" name="sign_name" id="sign_name" required>
        <br><br>

        <label for="pdf_file">Select PDF File:</label>
        <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf" required>
        <br><br>

        <button type="submit">Upload and Sign PDF</button>
    </form>

    <div id="preview_pdf"></div>
    <input type="hidden" id="filePath" name="filePath"/>
    <input type="hidden" id="fileName" name="fileName"/>
    <input type="hidden" id="signingReason" name="signingReason" maxlength="20" />
    <input type="hidden" id="signingLocation" name="signingLocation" maxlength="20" />
    <input type="hidden" id="stampingX" name="stampingX" maxlength="20" x-model="sign_x" />
    <input type="hidden" id="stampingY" name="stampingY" maxlength="20" x-model="sign_y" />
    <input type="hidden" id="tsaURL" name="tsaURL" value="" maxlength="100" style="width: 400px;" />
    <input type="hidden" id="timeServerURL" name="timeServerURL" value="https://basundhara.assam.gov.in/dscapi/getServerTime" maxlength="100" style="width: 400px;" />

    <form action="https://es-staging.cdac.in/esignlevel2/2.1/form/signdoc" method="post" id="adhaar_sign_form">
        <input type="hidden" id="eSignRequest" name="eSignRequest" />
        <input type="hidden" id="aspTxnID" name="aspTxnID"   />
        <input type="hidden" id="Content-Type" name="Content-Type" value="application/xml" />
    </form>
    <div id="preview_proposal_for_sign"></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.10.111/pdf.worker.min.js"></script>

<script>

$(document).on('submit','#esignForm',function(e){
    e.preventDefault();

    let formData = new FormData(this);
    
    $.ajax({
        url: "{{ route('esign.upload') }}",
        type: "POST",
        data: formData,
        dataType : 'json',
        cache : false,
        processData: false,
        contentType:false,
        
        success:function(res){
            console.log("response: ", res);
            $("#filePath").val(res.data.file_path);
            $("#fileName").val(res.data.file_name);
            loadPreviewForSign(res.data.base64Pdf)
            return false;

            $("#eSignRequest").val(res.data.esign_request);
            $("#aspTxnID").val(res.data.txn_id);
            $("#adhaar_sign_form").submit();
            return false;
            let data = res;
            
            // auto submit to CDAC
            let form = $('<form>', {
                action: 'https://esignservice.cdac.in/esign2.1/2.1/form/signdoc',
                method: 'POST',
                target: '_self'
            });

            $('<input>').attr({
                type: 'hidden',
                name: 'msg',
                value: data.esign_request
            }).appendTo(form);

            $('body').append(form);

            form.submit();
        },

        error:function(xhr, status, error){
            console.log('Error: ' + error.message);
            $('#response').html(`<p style="color:red">${xhr.responseText}</p>`);
        }
    });
});

function loadPreviewForSign(base64Pdf) {
    // Convert base64 → binary → Uint8Array
    const binary = atob(base64Pdf);
    const len = binary.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    // Load into PDF.js
    pdfjsLib.getDocument({ data: bytes }).promise.then(function(pdfDoc) {
        pdfDoc.getPage(pdfDoc.numPages).then(function(page) {

            let scale = 1;
            let viewport = page.getViewport({ scale });

            let canvas = document.createElement('canvas');
            let ctx = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            page.render({
                canvasContext: ctx,
                viewport: viewport
            }).promise.then(() => {
                console.log("PDF rendered");
            });

            document.getElementById('preview_proposal_for_sign').innerHTML = "";
            document.getElementById('preview_proposal_for_sign').appendChild(canvas);
        });
    });
}

$(document).ready(function() {
    document.getElementById('preview_proposal_for_sign').addEventListener('click', function(event) {
        initAdhaarSign();
    });
});

function initAdhaarSign() {
    var data = {};
    data._token = "{{ csrf_token() }}";
    data.sign_name = 'nc_proposal';
    data.sign_x = 50;
    data.sign_y = 50;
    data.user = "user01";
    data.filePath = $("#filePath").val();
    data.fileName = $("#fileName").val();
    data.co_note = "Demarcation Document Signing";
    $.ajax({
        url: "{{ route('esign.process') }}",
        method: "POST",
        async: true,
        dataType: 'json',
        data: data,
        success: function(data) {
            if(data.data.txn_id){
                $("#eSignRequest").val(data.data.esign_request);
                $("#aspTxnID").val(data.data.txn_id);
                $("#adhaar_sign_form").submit();
            }else{
                alert('error');
            }
        }
    });
}

</script>


</body>
</html>
