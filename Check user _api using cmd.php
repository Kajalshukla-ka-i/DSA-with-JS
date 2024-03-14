<?php
require_once('config.php');
$db = new Config;

require_once('connection.php');
$conn = new Connection;
$Usersql = "SELECT * FROM users WHERE enable_api = 1   AND api_key != '' AND pid = 0 AND disabled = 0 AND id NOT IN (6, 118, 371, 150,1150)
AND users.username NOT LIKE '%DEMO%' AND users.username NOT LIKE '%TEST%'";
$user_result = $db->Query($Usersql);

$data = array(); // Initialize an array to store user and crawler details
$user_api_value = '';
echo "Do you want to provide a value for user_api_value? (yes/no): ";
$answer = trim(fgets(STDIN)); // Get user input

if ($answer === 'yes') {
    echo "Enter the value for user_api_value: ";
    $user_api_value = trim(fgets(STDIN)); // Get user input
}
if ($user_result) { // Check if the query was successful
    foreach ($user_result as $user) {
        $user_id = $user['id'];
        $user_api_key =  $user['api_key'];

        $crawlerSQL = "SELECT uc.*, c.title 
        FROM user_crawlers uc 
        INNER JOIN crawlers c ON uc.crawler_id = c.id
        WHERE uc.user_id = $user_id";

        $crawler_result = $db->Query($crawlerSQL);

        // Process crawler results for the current user
        if ($crawler_result) {
            // echo "Crawler results for user ID: $user_id <br>";
            foreach ($crawler_result as $web) {
                $crawler_id = $web['crawler_id'];
                $crawler_website = $web['title'];
                $data[] = array(
                    'user_id' => $user_id,
                    'api_key' => $user_api_key,
                    'crawler_id' => $crawler_id,
                    'crawler_website' => $crawler_website
                );
                $cmd = '';
                echo "Do you want to insert or update data? (insert/update): ";
                $action = trim(fgets(STDIN)); // Get user input

                if ($action === 'update') {
                    $cmd = "nohup php /home/bkpmysamm/public_html/API_Script/check_api.php  $user_api_value ";
                } elseif ($action === 'insert') {
                    $cmd = "nohup php /home/bkpmysamm/public_html/API_Script/insert_api.php $user_id $user_api_key $crawler_id $crawler_website $user_api_value ";
                } else {
                    // echo "Invalid action specified. Skipping.";
                    // continue; // Skip to the next iteration
                    $cmd = "nohup php /home/bkpmysamm/public_html/API_Script/check_api.php  $user_api_value ";
                }

                shell_exec($cmd);

                // $cmd = "nohup php /home/bkpmysamm/public_html/API_Script/check_api.php $user_id $user_api_key $crawler_id $crawler_website $user_api_value ";
                // shell_exec($cmd);
                // echo $cmd; //die;
            }
        } else {
            // echo "No crawler results found for user ID: $user_id <br>";
        }
    }
} else {
    echo "No users found with enable_api = 1 and non-empty api_key";
}

print_r($new);
// $json_data = json_encode($data);
