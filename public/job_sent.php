<?php

$servername = "172.18.0.2";
$username = "root";
$password = "1qaz!QAZ";
$dbname = "ai_blitz";
$authorizationToken = 'TF89xmo81k0ME8sIV9oFdtSFH29X2W';
$url="https://blitz2apistage.pindustries.co/api/v1/internal/object-service/visit/product/detection";
function sendProductDetectionRequest($url, $visitId, $productList, $authorizationToken) {
    $curl = curl_init();

    $postData = json_encode(array(
        "visitId" => $visitId,
        "productList" => $productList
    ));

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $authorizationToken
        ),
    ));

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        curl_close($curl);
        throw new Exception("cURL Error: $error_msg");
    }

    curl_close($curl);
    
    return $response;
}



try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT distinct(va.visit_id)
    FROM visits_ai va LEFT JOIN visits_ai_results var ON va.id = var.visits_ai_id  LEFT JOIN henkel h ON var.`code` COLLATE utf8mb4_unicode_ci = h.`code`
    LEFT JOIN error_description err ON va.`status` = err.`error_number`
    WHERE va.visit_id = 34480 AND  va.sent = 0 AND created_at_sent is NULL  AND h.product_id IS NOT NULL;";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Set the resulting array to associative
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);

    foreach($stmt->fetchAll() as $row) {
        sleep(1);
        echo "visit_id: " . $row["visit_id"]. "\n";
        $visitId = $row["visit_id"];


        $sql1 = "SELECT h.product_id , va.category_id, var.count,var.`code`,va.visit_id     
        FROM visits_ai va LEFT JOIN visits_ai_results var ON va.id = var.visits_ai_id  
        LEFT JOIN henkel h ON var.`code` COLLATE utf8mb4_unicode_ci = h.`code`     
        LEFT JOIN error_description err ON va.`status` = err.`error_number`     
        WHERE va.visit_id = $visitId COLLATE utf8mb4_unicode_ci AND va.sent = 0 AND created_at_sent is NULL  AND h.product_id IS NOT NULL;";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->execute();

        // Set the resulting array to associative
        $resultDetects = $stmt1->setFetchMode(PDO::FETCH_ASSOC);
        foreach($stmt1->fetchAll() as $row1) {
            $productList[] = [
                "productId" => $row1["product_id"],
                "count" => $row1["count"]
            ]; 
        }
        $response = sendProductDetectionRequest($url, $visitId, $productList, $authorizationToken);
        // Decode the JSON response into a PHP associative array
        $responseArray = json_decode($response, true);
        // Check if decoding was successful and if the status is 200
        if (is_array($responseArray) && isset($responseArray['status'])) {
           if ($responseArray['status'] == 200) {
               echo "Request was successful. Status: 200\n";
               // UPDATE  sent field to 1 and created_at_sent
               // Proceed with handling the successful response
           } else {
             echo "Request failed. Status: " . $responseArray['status'] . "\n";
             echo "Message: " . $responseArray['message'] . "\n";
             // Handle the error or failure case
           }
        } else {
            echo "Invalid response received or JSON decoding failed.\n";
          }


        die("ok");
    }
    
}
catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null; // Close the connection
?>




 

