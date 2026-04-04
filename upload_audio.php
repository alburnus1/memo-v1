<?php

session_start();

header('Content-Type: application/json');

include 'config.php';



$apiKey = "19aa781dc1f7406ba58faece251dc77e"; 



// Si no hay sesión, no permitimos subir

if (!isset($_SESSION['user'])) {

    die(json_encode(["status" => "error", "msg" => "Sesión expirada"]));

}



if (isset($_FILES['audio'])) {

    $folder = "uploads/";

    if (!file_exists($folder)) { mkdir($folder, 0777, true); }

    

    $filename = time() . ".wav";

    $destination = $folder . $filename;



    if (move_uploaded_file($_FILES['audio']['tmp_name'], $destination)) {

        

        // 1. SUBIR A IA

        $ch = curl_init("https://api.assemblyai.com/v2/upload");

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($destination));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $apiKey", "Content-Type: application/octet-stream"]);

        $resUpload = json_decode(curl_exec($ch), true);



        if (isset($resUpload['upload_url'])) {

            // 2. PEDIR TRANSCRIPCIÓN

            $ch = curl_init("https://api.assemblyai.com/v2/transcript");

            curl_setopt($ch, CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $postData = json_encode([

                "audio_url" => $resUpload['upload_url'],

                "language_code" => "es",

                "speech_models" => ["universal-3-pro"] 

            ]);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: $apiKey", "Content-Type: application/json"]);

            $resT = json_decode(curl_exec($ch), true);

            

            if (isset($resT['id'])) {

                $tId = $resT['id'];

                $uid = $_SESSION['user']['id']; // IDENTIFICAMOS AL DUEÑO



                // GUARDAR CON EL ID DEL USUARIO

                $sql = "INSERT INTO consultas (usuario_id, diagnostico, indicaciones, medicamentos, proximo_control, audio_url) 

                        VALUES ($uid, 'Procesando...', '$tId', '[]', CURDATE(), '$destination')";

                $conn->query($sql);

                

                echo json_encode(["status" => "success"]);

                exit;

            }

        }

    }

}

echo json_encode(["status" => "error"]);