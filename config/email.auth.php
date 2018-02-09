<?php
/******************************************************************************************************************
 * Able / config / email.auth.php
 * Settings for the Email class ( Based on SwiftMailer )
 *
 * Used in:
 * framework/email.php
 *****************************************************************************************************************/

global $CONSTANTS;
$CONSTANTS = array_join_unique($CONSTANTS, [
  'EMAIL_AUTH_SERVER'                =>    'smtp.gmail.com',
  'EMAIL_AUTH_PORT'                  =>    465,
  'EMAIL_AUTH_CONNECTION'            =>    'ssl',
  'EMAIL_AUTH_ADDRESS'               =>    'your@gmail.com',
  'EMAIL_AUTH_PASSWORD'              =>    decode_string('your_encoded_password'),
  'EMAIL_COMPLIANCE_ERROR'           =>    0
]);
?>
