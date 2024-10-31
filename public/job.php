<?php
$servername = "172.18.0.2";
$username = "root";
$password = "1qaz!QAZ";
$dbname = "ai_blitz";


function makeApiRequest($url, $visitId, $authorizationToken) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('visitId' => $visitId),
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $authorizationToken,
            'Accept: application/json'
        ),
    ));

    $response = curl_exec($curl);

    // Check for errors
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        throw new Exception("cURL Error: $error_msg");
    }

    curl_close($curl);

    return $response;
}




$url = 'http://blitz2.pindustries.co:3000/aiapi/api/v1/ai-result';
$authorizationToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiYjhlZTEyMGMyZTZmMjQxOGYwZDZmMzQ0NzU4MTJlZjMxNzlhNmM1MWZkODRkY2UwOWQwYzM3NTczNzJhNzIxYWU3MTQ0M2UxMDNkYTRlOTAiLCJpYXQiOjE3MDczOTY4MzcuMzkyNzY4LCJuYmYiOjE3MDczOTY4MzcuMzkyNzc2LCJleHAiOjE3MzkwMTkyMzYuODM4NjMxLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.ZKvoR3wzcpfQcMXLa4p5jV03vkSOaYF32HtMKVzFaLLCVoth_OykDHmahSSm63Q5wx2IavGYa2kVPykmdm6c0YdkbsbBdoIb7NeWFJOGorG8Xybqo2fKwti9HhLOpBMFOYdGrXfHl_YTUPc94qLrTrMv84SPjxzeC01zfR5azB28RIi2Wp9_4AQsI_sagnhRNy6-BB10yvxTEjwRX2nZKS5ynvmzXLslwVWSgeCRDuVaJ9oVLipYPcv-9FNWe1d-K9nhnzr38LTvJfAKhNb9YtfZ5wcYTy0OObwbcDogoK-6wcBKS3wVhqj5c8Y8jY_Ac8UG8cFSXVJTQTUj_5fyW8_yF46RZMUERONnZIJvqC4Rh1UGp-gd8fFHFxRaE-02HBxEuv0M1O5WwEHnNq22iwv7lBdK7X9jVmyJip6hmfLdCQblQsioOUH4qiumFGD59rtRJgBmA2lgJc6NBh3XBtJwYzbC7YuUvRDnexwdDnkbexFSzvJCKfmw_ayuxFWVBL-EhyOJjXFvn6F3DcUDrmebsg2O3N7K8jgHIUDe6TudUeOaYXfouU7IzYOu4bmae9sa4yYcOpK9OG_nsqxLxrQu74TAHVV0WQKLMfbS_af8Vl4yJ59jCU567CPlRDW3bgaL5uKI0hTbjTZPhmazL2yEfLYyCSyuNoXC2A2hzVs';


try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT distinct(visit_id)  FROM visits_ai where status = 616 ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Set the resulting array to associative
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

    foreach($stmt->fetchAll() as $row) {
        sleep(1);
        echo date('Y-m-d H:i:s')." visit_id: " . $row["visit_id"]. "\n";
        $visitId = $row["visit_id"];
        $response = makeApiRequest($url, $visitId, $authorizationToken);
    }
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null; // Close the connection
?>
