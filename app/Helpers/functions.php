<?php
function formatPhoneNumber($phoneNumber)
{
    // 254724606690

    // Remove any non-numeric characters from the phone number
    $cleanedNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

    // Check if the cleaned number starts with "0"
    if (strpos($cleanedNumber, '0') === 0) {
        // Remove the leading "0" and add the country code
        $formattedNumber = '254' . substr($cleanedNumber, 1);
    } else {
        // The number is already in international format
        $formattedNumber = $cleanedNumber;
    }

    return $formattedNumber;
}
