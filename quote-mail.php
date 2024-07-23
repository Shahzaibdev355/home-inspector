<?php
$apiKey = "0c84dc1545994be9a2beabfd1420ba8b";

function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "")
{
    $url = "https://api.ipgeolocation.io/ipgeo?apiKey=" . $apiKey . "&ip=" . $ip . "&lang=" . $lang . "&fields=" . $fields . "&excludes=" . $excludes;
    $cURL = curl_init();

    curl_setopt($cURL, CURLOPT_URL, $url);
    curl_setopt($cURL, CURLOPT_HTTPGET, true);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));

    return curl_exec($cURL);
}

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$location = get_geolocation($apiKey, $ip);
$decodedLocation = json_decode($location, true);

$ip = $decodedLocation['ip'];
$state_prov = $decodedLocation['state_prov'];
$city = $decodedLocation['city'];
$zipcode = $decodedLocation['zipcode'];

$info = '<b>IP Address: </b>' . $ip . '<b> State: </b>' . $state_prov . '<b> City: </b>' . $city . '<b> Zip Code: </b>' . $zipcode;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phoneno = $_POST['phoneno'];
    $footage = $_POST['footage'];
    $propertyaddress = $_POST['propertyaddress'];
    $checkforradon = isset($_POST['checkforradon']) ? implode(', ', $_POST['checkforradon']) : '';
    $property = isset($_POST['property']) ? implode(', ', $_POST['property']) : '';
    $contact = isset($_POST['contact']) ? implode(', ', $_POST['contact']) : '';
    $date = $_POST['date'];
    $referred = $_POST['referred'];
    $referredemail = $_POST['referredemail'];
    $message = $_POST['message'];

    // Validate reCAPTCHA  
    $recaptchaSecretKey = '6LcfizskAAAAAC0dL8-7uWkQoDW_P8foSNKKJWVp';  // Replace with your actual reCAPTCHA secret key
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $recaptchaVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaVerifyData = [
        'secret' => $recaptchaSecretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    ];

    $recaptchaVerifyUrl .= '?' . http_build_query($recaptchaVerifyData);
    $recaptchaVerification = json_decode(file_get_contents($recaptchaVerifyUrl));

    if (!$recaptchaVerification->success) {
        // reCAPTCHA verification failed
        echo 'reCAPTCHA verification failed. Please try again.';
        exit;
    }

    // Email content
    $to = array('help@1800homeinspector.com', 'updates@lazerwebsites.com'); // Replace with your array of email addresses
    $subject = 'HOMERITE REAL ESTATE INSPECTIONS - Request Quote Form';

    $body = '<h1>HOMERITE REAL ESTATE INSPECTIONS - Quote Form</h1>';
    $body .= '<p>You received an Email from <b>' . $name . ' </b></p>';
    $body .= '<p>=================================================================================</p>';
    $body .= '<p><b>Email: </b>' . $email . ' </p>';
    $body .= '<p><b>Phone: </b>' . $phoneno . ' </p>';
    $body .= '<p><b>Square Footage: </b>' . $footage . ' </p>';
    $body .= '<p><b>Property Address: </b>' . $propertyaddress . ' </p>';
    $body .= '<p><b>Do you want to test for unhealthy levels of Radon Gas?: </b>' . $checkforradon . ' </p>';
    $body .= '<p><b>Does the property have a Private Well and Septic System?: </b>' . $property . ' </p>';
    $body .= '<p><b>Please contact me: </b>' . $contact . ' </p>';
    $body .= '<p><b>Inspection(s) Must be Completed By This Date According To My Contract: </b>' . $date . ' </p>';
    $body .= '<p><b>Referred By: </b>' . $referred . ' </p>';
    $body .= '<p><b>Referrals Email: </b>' . $referredemail . ' </p>';
    $body .= '<p><b>Questions/Concerns: </b>' . $message . ' </p>';
    $body .= '<p>=================================================================================</p>';
    $body .= '<p><b>Information of user: </b>' . $info . ' </p>';
    $body .= '<p>=================================================================================</p>';
    

    // Set headers for HTML content
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

    // Loop through each email address and send the email
    foreach ($to as $recipient) {
        if (mail($recipient, $subject, $body, $headers)) {
            echo 'Email sent to ' . $recipient . ' successfully.';
        } else {
            echo 'Failed to send the email to ' . $recipient . '.';
        }
    }
}
?>
