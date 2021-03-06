<?php
// Assume mail was not sent
$mailSent = false;
// Assume the input contains nothing suspect
$suspect = false;
// Regular expression to search for suspect phrases
$pattern = '/Content-type:|Bcc:|Cc:/i';

// Recursive function that checks for suspect phrases
// Third argument is passed by reference
function isSuspect($value, $pattern, &$suspect) {
  if (is_array($value)) {
    foreach ($value as $item) {
      isSuspect($item, $pattern, $suspect);
    }
  } else {
    if (preg_match($pattern, $value)) {
      $suspect = true;
    }
  }
}

//CHeck the $_POST array for suspect phrases
isSuspect($_POST, $pattern, $suspect);

//Process the form only if no suspect phrases are found
if (!$suspect) :
// Check that required fields have been filled in
// and reassign expected elements to simple variables

foreach ($_POST as $key => $value) {
  $value = is_array($value) ? $value : trim($value);
  if (empty($value) && in_array($key, $required)) {
    $missing[] = $key;
    $$key = '';
  } elseif (in_array($key, $expected)) {
    $$key = $value;
  }
}
// Validate user's email
if (!$missing || !empty($email)) {
  $validEmail = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
  if ($validEmail) {
    $headers[] = "Reply-to: $validEmail";
  } else {
    $errors['email'] = true;
  }

  // If no errors, create headers and message body
  if (!$errors && !$missing) {
    $headers = implode("\r\n", $headers);
    // Initialize message
    $message = '';
    foreach ($expected as $field) {
      if (isset($$field) && !empty($$field)) {
        $val = $$field;
      } else {
        $val = 'Not selected';
      }
      // If an array, expand to a coma-separated string
      if (is_array($val)) {
        $val = implode(', ', $val);
      }
      // Replace underscore with spaces
      $field = str_replace('_', ' ' , $field);
      $message .= ucfirst($field) . ": $val\r\n\r\n";

    }

    $message = wordwrap($message, 70);
    $mailSent = true;
    // $mailSent = mail($to, $subject, $message, $head, $authorized);
    if (!$mailSent) {
      $errors['mailFail'] = true;

    }
  }
}

endif;
 ?>
