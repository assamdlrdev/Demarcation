<?php

namespace App\Http\Controllers\Api\esign;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AadhaarSignProcess;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Imagick;
use TCPDF;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;


class AadhaarSignController extends Controller implements AadhaarSignProcess
{
    public $SIGN_NAME;
    public $SIGN_COR_X = 200;
    public $SIGN_COR_Y = 200;
    public $PDF_PATH;
    public $FILE_NAME_WO_EXT;
    public $APPLICATION_NO;
    public $SIGN_USER_NAME;
    public $SIGN;
    public $DIST_CODE;
    public $SUBDIV_CODE;
    public $CIR_CODE;
    public $USER_CODE;
    public $NC_MAP_ID;
    public $NC_PROPOSAL_CASE_NO_ARR;

    function __construct()
    {
        Lang::setLocale('assamese');
        $this->setConfig();
    }
    public function setConfig()
    {
        $this->SIGN_NAME = '';
        $this->SIGN_COR_X = 200;
        $this->SIGN_COR_Y = 200;
        $this->USER_CODE = "";
        $this->DIST_CODE = "";
        $this->SUBDIV_CODE = "";
        $this->CIR_CODE = "";
        $user_desig_code = "";
    }

    public function showUploadForm()
    {
        return view('upload_pdf');
    }

    // Handle the uploaded file and sign the PDF
    public function uploadAndSignPdf(Request $request)
    {
        // Validate the uploaded file and input fields
        $request->validate([
            'pdf_file' => 'required|mimes:pdf|max:10240',  // Max file size 10MB
            'sign_name' => 'required|string|max:255'
        ]);

        // Get the uploaded file and signer name
        $pdfFile = $request->file('pdf_file');
        $signName = $request->input('sign_name');

        // Store the uploaded PDF temporarily
        // $pdfPath = $pdfFile->storeAs('temp', 'unsigned_' . time() . '.pdf');
        // $unsignedFilePath = storage_path('app/' . $pdfPath);

        // Set the path for the signed PDF
        // $signedFilePath = storage_path('app/temp/signed_' . time() . '.pdf');
        // if (!file_exists(storage_path('app/temp'))) {
        //     mkdir(storage_path('app/temp'), 0777, true);
        // }
        $filename = 'unsigned-' . pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME) . '.pdf';
        $path = $pdfFile->storeAs(
            'uploads/temp',
            $filename,
            'public'
        );

        $file_path = storage_path('app/public/uploads/temp/'.$filename);
        header("Content-Type: application/pdf");
        $pdfContent = file_get_contents($file_path);
        $data['base64Pdf'] = base64_encode($pdfContent);
        $data['file_path'] = $file_path;
        $data['file_name'] = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);

        // Update the properties with the uploaded file's information
        $this->PDF_PATH = storage_path('app/public/uploads/temp/'.$filename);
        $this->FILE_NAME_WO_EXT = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
        $this->SIGN_NAME = $signName;
        
        // Call the signing process method
        return $this->esignProcess($request);

        // Return the signed PDF file for download
        // return response()->download(storage_path('app/public/uploads/temp/'.$filename));
        // return response()->download(storage_path('app/public/uploads/temp/'.$filename))->deleteFileAfterSend(true);
    }

    public function esignProcess(Request $request)
    {
        // echo $this->PDF_PATH; exit;
        $o_pref = $request->o_pref;
        $sign_name = $request->sign_name;
        $user = $request->user;
        $this->PDF_PATH = $request->filePath;
        $this->FILE_NAME_WO_EXT = $request->fileName;
        $this->APPLICATION_NO = 'APP' . date('YmdHis');
        $ESIGN_TMP_DIR = base_path('esign/tmp/');
        $PRIVATEKEY = base_path('esign/cert/dlrs_private_key_prod.pem');

        $getParams = [
            'sign_name' => $sign_name,
            'dist_code' => $this->DIST_CODE,
            'subdiv_code' => $this->SUBDIV_CODE,
            'cir_code' => $this->CIR_CODE,
            'user_code' => $this->USER_CODE,
            'application_no' => $this->APPLICATION_NO,
            'date_of_sign' => date('Y-m-d'),
            'time_of_sign' => date('H:i:s'),
            'file_name_wo_ext' => $this->FILE_NAME_WO_EXT,
            'file_path' => $this->PDF_PATH,
            'o_pref' => $o_pref,
            'auth_sess' => json_encode(Session::all()),
        ];
        $getParams_encoded = urlencode(base64_encode(openssl_encrypt(json_encode($getParams), "AES-128-CTR", "singleENCRYPT", 0, "1234567893032221")));

        // IMAGICK
        $imagick = new \Imagick();
        $imagick->setBackgroundColor(new \ImagickPixel('transparent'));
        // $imagick->setResolution(288, 288);
        $imagick->setResolution(288, 288);
        $imagick->readImage($this->PDF_PATH); //$imagick can read pdf and image too

        //get file name only w/o ext.
        $imageWidth = $imagick->getImageWidth();
        $imageHeight = $imagick->getImageHeight();

        $num_pages = $imagick->getNumberImages();

        $dpi = 288;
        $widthMM = ($imageWidth / $dpi) * 25.4;
        $heightMM = ($imageHeight / $dpi) * 25.4;

        // Convert PDF pages to images
        for ($i = 0; $i < $num_pages; $i++) {
            $imagick->setIteratorIndex($i);
            $imagick->setImageFormat('jpeg');
            $imagick->stripImage();
            $imagick->writeImage($ESIGN_TMP_DIR . 'unsigned-' . $this->FILE_NAME_WO_EXT . '-' . $i . '.jpg');
        }
        $imagick->destroy();


        if($o_pref == 'L') {
            $pdf = new TCPDF($o_pref, PDF_UNIT, array($widthMM, $heightMM), true, 'UTF-8', false);
        }
        else {
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array($widthMM, $heightMM), true, 'UTF-8', false);
        }


        // TCPDF
        // set certificate file
        $info = array();
        for ($i = 0; $i < $num_pages; $i++) {
            // set document signature
            $pdf->my_set_sign('', '', '', '', 2, $info); //custom function TCPDF  library tcpdf.php
            $pdf->AddPage();
            $pdf->Image($ESIGN_TMP_DIR . 'unsigned-' . $this->FILE_NAME_WO_EXT . '-' . $i . '.jpg');
            // $pdf->SetFont('times', '', 8);
            $pdf->setCellPaddings(0, 0, 0, 0);
            $bMargin = 0;
            // $auto_page_break = $pdf->getAutoPageBreak();
            // $pdf->SetAutoPageBreak(false, 0);
            $pdf->setPageMark();
            // $pdf->setCellPaddings(0, 0, 0, 0);
            $pdfPageCount = $i + 1;
            $pdf->setPage(($pdfPageCount), true);

            $font_size = 8;
            $pdf->SetFont('freeserif', '', $font_size, '', false);
            
            if($o_pref == 'L') {
                $pdf->setSignatureAppearance(65, 160, 35, 17, $num_pages, $this->SIGN_USER_NAME); //X,Y,Width,Height
                $pdf->MultiCell(35, 10, $this->SIGN, 0, '', 0, 1, 92, 160, true); //8th and 9th params are co ordinate of x and y resp
            }
            else {
                $pdf->setSignatureAppearance(3, 255, 35, 17, $num_pages, $this->SIGN_USER_NAME); //X,Y,Width,Height
                $pdf->MultiCell(35, 10, $this->SIGN, 0, '', 0, 1, 30, 255, true); //8th and 9th params are co ordinate of x and y resp
            }
        }
        $doc_path = storage_path('app/public/uploads/temp/'. 'unsigned-' .$this->FILE_NAME_WO_EXT. '.pdf');
        $file = $pdf->my_output($doc_path, 'F'); //F-Force download, S-Source buffer returns binary, reffer my_output function from tcpdf.php file
        $pdf_byte_range = $pdf->pdf_byte_range;
        $pdf->_destroy();
        //FILE_HASH
        $file_hash = hash_file('sha256', $doc_path);
        //after pdf done using images, delete that temp images from folder.
        for ($i = 0; $i < $num_pages; $i++) {
            unlink($ESIGN_TMP_DIR . 'unsigned-' . $this->FILE_NAME_WO_EXT . '-' . $i . '.jpg'); //remove images after PDF generated/converted from temp folder
        }

        //DOC PREPARATION AND SAVE
        $doc = new \DOMDocument();
        //randome number gerator rand(1,9)
        $txn = rand(111111111111, 999999999999) . '----' . $pdf_byte_range; //$pdf_byte_range signiture space location
        $ts = date('Y-m-d\TH:i:s');
        // $doc_info = FILE_NAME;
        // $responseUrl = url('esign-response'.'?param='.$getParams_encoded);
        $responseUrl = url('esign-response?param='.$getParams_encoded);
        $xmlstr = '<Esign AuthMode="1" aspId="DLRA-900" ekycId="" ekycIdType="A" responseSigType="pkcs7" responseUrl="' . $responseUrl .'" sc="y" ts="' . $ts . '" txn="' . $txn . '" ver="2.1"><Docs><InputHash docInfo="' . $txn . '" hashAlgorithm="SHA256" id="1">' . $file_hash . '</InputHash></Docs></Esign>';
        $doc->loadXML($xmlstr); //parser
        // Create a new Security object 
        $objDSig = new \RobRichards\XMLSecLibs\XMLSecurityDSig();
        // Use the c14n exclusive canonicalization
        $objDSig->setCanonicalMethod(\RobRichards\XMLSecLibs\XMLSecurityDSig::C14N);
        // Sign using SHA-256
        $objDSig->addReference(
            $doc,
            \RobRichards\XMLSecLibs\XMLSecurityDSig::SHA1,
            array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
            array('force_uri' => true)
        );
        // Create a new (private) Security key
        $objKey = new \RobRichards\XMLSecLibs\XMLSecurityKey(\RobRichards\XMLSecLibs\XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
        //If key has a passphrase, set it using
        $objKey->passphrase = '';
        // Load the private key
        $objKey->loadKey($PRIVATEKEY, TRUE);
        // Sign the XML file
        $objDSig->sign($objKey);
        // Append the signature to the XML
        $objDSig->appendSignature($doc->documentElement);
        $signXML = $doc->saveXML();
        $signXML = str_replace('<?xml version="1.0"?>', '', $signXML);
        ob_end_clean();
        $data['esign_request'] = $signXML;
        $data['txn_id'] = $txn;
        $data['esign_url'] = 'https://esignservice.cdac.in/esign2.1/2.1/form/signdoc';
        // return view('aadhaar_success', ['data' => '']);
        // echo "<pre/>";
        // print_r($data); exit;


        return response()->json([
            'status' => 200,
            'data' => $data
        ]);

    }

    public function esignResponse(Request $request)
    {
        // Decode the 'param' parameter passed in the GET request
        $getParams = $request->query('param');
        $params = json_decode(openssl_decrypt(base64_decode(urldecode($getParams)), "AES-128-CTR", "singleENCRYPT", 0, '1234567893032221'));

        $unsigned_file_path = storage_path('app/public/uploads/temp/'. 'unsigned-' .$params->file_name_wo_ext. '.pdf');
        $signed_file_path = $params->file_path;
        $errMsg = "";

        // Load and parse the XML response
        $xmlData = simplexml_load_string($request->input('eSignResponse'));
        
        if ($xmlData["@attributes"]["errCode"] != 'NA') {
            $errCode = $xmlData["@attributes"]["errCode"];
            $errMsg = isset($xmlData["@attributes"]["errMsg"]) ? $xmlData["@attributes"]["errMsg"] : 'eSign Request Cancelled.[#' . $errCode . ']';
        }

        if ($errMsg) {
            Log::error('Invalid signed PDF: ' . $errMsg);
            return view('esign.error');  // Assuming you have an error view in Laravel
        } else {
            // Read the unsigned file
            $unsigned_file = file_get_contents($unsigned_file_path);

            if ($unsigned_file === false) {
                Log::error('Could not save the signed file in server');
                return view('esign.error');  // Show error view
            }

            $txn = $xmlData["@attributes"]["txn"];
            $txn_array = explode('----', $txn);
            $pdf_byte_range = $txn_array[1];

            $pkcs7 = (array) $xmlData['Signatures'];
            $pkcs7_value = $pkcs7['DocSignature'];
            $cer_value = $xmlData['UserX509Certificate'];

            // Convert the certificate into PEM format
            $beginpem = "-----BEGIN CERTIFICATE-----\n";
            $endpem = "-----END CERTIFICATE-----\n";
            $pemdata = $beginpem . trim($cer_value) . "\n" . $endpem;

            $cert_data = openssl_x509_parse($pemdata);

            // Initialize TCPDF
            if ($params->o_pref != '') {
                $pdf = new TCPDF($params->o_pref, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            } else {
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            }

            // Output the signed PDF to the specified file path
            $file = $pdf->my_output($signed_file_path, 'F', $unsigned_file, $cer_value, $pkcs7_value, true, $pdf_byte_range);
            $pdf->_destroy();

            // Remove the unsigned file after signing
            if (file_exists($unsigned_file_path)) {
                unlink($unsigned_file_path);
            }            

            \Log::info("ESIGN CALLBACK", [
                'url' => url()->current(),
                'method' => $request->method(),
                'all' => $request->all(),
            ]);

            // Redirect to success function
            $this->esignSuccess($params, $xmlData);
        }
    }

    // Success handler function (assuming this is part of your logic)
    // public function esignSuccess($params, $xmlData)
    // {
    //     // Handle success logic (e.g., update database, send notifications)
    //     // Example:
    //     Log::info('eSign success for transaction ' . $xmlData['@attributes']['txn']);
        
    //     // You can redirect to a success view or return a JSON response
    //     return view('esign.success', ['params' => $params, 'data' => $xmlData]);
    // }

    public function esignSuccess($params, $xmlData)
    {
        $sign_name = $params->sign_name;

        $dist_code = $params->dist_code;
        $subdiv_code = $params->subdiv_code;
        $cir_code = $params->cir_code;
        $proposal_no = $params->proposal_no;
        $user_code = $params->user_code;
        $co_note = $params->co_note;
        $to_user = $params->to_user;
        $case_nos_arr = (array) $params->case_nos_arr;

        $auth_sess = json_decode($params->auth_sess, true);        
        $mainfile = file_get_contents($params->file_path);
        $pdfbase = base64_encode($mainfile);
        if ($pdfbase) {                
            // $data['file_path'] = base_url()  . 'index.php/nc_village_v2/NcVillageDcController/viewUploadedMap?id=' . $nc_map_id;
            // $data['file_path'] = false;
            return view('aadhaar_success', $data);
        } else {
            log_message("error", 'could not fetch the signed map');
            return view('aadhaar_error', ['data' => $data]);
        }
        
    }
}
