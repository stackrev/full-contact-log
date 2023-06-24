<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */
/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts()
{
  wp_enqueue_style(
    'hello-elementor-child-style',
    get_stylesheet_directory_uri() . '/style.css',
    [
      'hello-elementor-theme-style',
    ],
    '1.0.0'
  );
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts');

function send_traffic($type)
{
  if (!$_COOKIE['fullcontact_pid'])
    return;
  $pid = $_COOKIE['fullcontact_pid'];
  $current_time = date('Y-m-d H:i:s');

  $curl = curl_init();

  curl_setopt_array(
    $curl,
    array(
      CURLOPT_URL => '147.182.133.115:5000/api/data',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => '{
    "pid": "' . $pid . '",
    "date": "' . $current_time . '",
    "type": "' . $type . '"
  }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer lnz8g5J26bwq8AW60MzpfUYa6LUo5xntd'
      ),
    )
  );

  $response = curl_exec($curl);

  curl_close($curl);
}

function set_user_pid_cookie($username)
{
  $user = get_user_by('login', $username);
  if ($user) {
    // The user's email address.
    $email = $user->user_email;
    $correct_email = preg_replace('/%40/', '@', $email);

    // Use FullContact's API to get the PID for this user's email address.
    $curl = curl_init();

    curl_setopt_array(
      $curl,
      array(
        CURLOPT_URL => 'https://api.fullcontact.com/v3/identity.resolve',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
			"email": "' . $correct_email . '"
		}',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          'Authorization: Bearer lnz8g5J26bwq8AW60MzpfUYa6LUo5xnt'
        ),
      )
    );

    $response = curl_exec($curl);
    curl_close($curl);
    // 		echo $response;	

    // 		$decode = urldecode($response);
// 		$json = json_decode($decode);
    // Set the cookie for 30 days.
    setcookie('fullcontact_pid', $response, time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN, is_ssl());

    send_traffic("logged_in");
  }
}
add_action('wp_login', 'set_user_pid_cookie');


function send_logout_traffic($username)
{
  send_traffic("logged_out");
}
add_action('wp_logout', 'send_logout_traffic');

function execute_once_on_visit()
{
  static $executed = false; // Static variable to track execution

  if (!$executed && !isset($_SESSION['script_executed']) && !isset($_COOKIE['script_executed'])) {
    // Set the static variable to true after executing your code
    $executed = true;
    // Set session variable to track execution
    $_SESSION['script_executed'] = true;
    // Set a cookie to track execution (optional)
    setcookie('script_executed', 'true', time() + 60, '/');
    // Your PHP script goes here...
    send_traffic("visited");
  }
}
add_action('init', 'execute_once_on_visit');