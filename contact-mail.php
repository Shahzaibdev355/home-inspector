<?php
$apiKey = "0c84dc1545994be9a2beabfd1420ba8b";

function get_geolocation($apiKey, $ip, $lang = "en", $fields = "*", $excludes = "")
{
    $url = "https://api.ipgeolocation.io/ipgeo?apiKey=" . $apiKey . "&ip=" . $ip . "&lang=" . $lang . "&fields=" . $fields . "&excludes=" . $excludes;
    
    // Use cURL for better consistency
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phoneno = $_POST['phoneno'];
    $message = $_POST['message'];

    // Get geolocation information using cURL
    $ip = !empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
    $location = get_geolocation($apiKey, $ip);
    $decodedLocation = json_decode($location, true);

    $ip = $decodedLocation['ip'];
    $state_prov = $decodedLocation['state_prov'];
    $city = $decodedLocation['city'];
    $zipcode = $decodedLocation['zipcode'];

    $info = '<b>IP Address: </b>' . $ip . '<b> State: </b>' . $state_prov . '<b> City: </b>' . $city . '<b> Zip Code: </b>' . $zipcode;

    // Validate reCAPTCHA using cURL
    $recaptchaSecretKey = '6LcfizskAAAAAC0dL8-7uWkQoDW_P8foSNKKJWVp';  // Replace with your actual reCAPTCHA secret key
    $recaptchaResponse = $_POST['g-recaptcha-response'];

    $recaptchaVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaVerifyData = [
        'secret' => $recaptchaSecretKey,
        'response' => $recaptchaResponse,
        'remoteip' => $_SERVER['REMOTE_ADDR'],
    ];

    $cURL = curl_init();
    curl_setopt($cURL, CURLOPT_URL, $recaptchaVerifyUrl);
    curl_setopt($cURL, CURLOPT_POST, true);
    curl_setopt($cURL, CURLOPT_POSTFIELDS, $recaptchaVerifyData);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);

    $recaptchaVerification = json_decode(curl_exec($cURL));

    if (!$recaptchaVerification->success) {
        // reCAPTCHA verification failed
        echo 'reCAPTCHA verification failed. Please try again.';
        exit;
    }

    // Email content
    $to = array('help@1800homeinspector.com', 'updates@lazerwebsites.com'); // Replace with your array of email addresses
    $subject = 'HOMERITE REAL ESTATE INSPECTIONS - Contact Form';

    $body = '<h1>HOMERITE REAL ESTATE INSPECTIONS - Contact Form</h1>';
    $body .= '<p>You received a message from <b>' . $name . ' </b></p>';
    $body .= '<p><b>Email: </b>' . $email . ' </p>';
    $body .= '<p><b>Phone: </b>' . $phoneno . ' </p>';
    $body .= '<p><b>Message: </b>' . $message . ' </p>';
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
