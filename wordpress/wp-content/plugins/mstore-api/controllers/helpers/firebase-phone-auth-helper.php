<?php

class FirebasePhoneAuthHelper
{
    public function verify_id_token($id_token){
        $splitToken = explode(".", $id_token);
        $headerBase64 = $splitToken[0]; // Header is always the index 0
        $decodedHeader = json_decode(urldecode(base64_decode($headerBase64)), true);

        if (!isset($decodedHeader['alg']) || $decodedHeader['alg'] != 'RS256') {
            return false;
        }

        $public_keys = $this->get_public_keys();
        if (!isset($decodedHeader['kid']) || !in_array($decodedHeader['kid'], $public_keys)) {
            return false;
        }

        $payloadBase64 = $splitToken[1]; // Payload is always the index 1
        $decodedPayload = json_decode(urldecode(base64_decode($payloadBase64)), true);

        if(!FirebaseMessageHelper::is_file_existed()){
            return new WP_Error(400, "Firebase private key file is not found", array('status' => 400));
        }
        $file_name = FirebaseMessageHelper::get_file_name();
        $file_path = FirebaseMessageHelper::get_config_file_path($file_name);
        $fileContent = file_get_contents($file_path);
        $json = json_decode($fileContent, true);
        $projectId = $json['project_id'];

        if (!isset($decodedPayload['aud']) || $decodedPayload['aud'] !== $projectId) {
            return false;
        }
        
        if (!isset($decodedPayload['iss']) || $decodedPayload['iss'] !== 'https://securetoken.google.com/'.$projectId) {
            return false;
        }

        return isset($decodedPayload['phone_number']) ? trim($decodedPayload['phone_number']) : false;
    }

    private function get_public_keys()
    {
            $response = wp_remote_get("https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com");
            $statusCode = wp_remote_retrieve_response_code($response);
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result, true);
            if($statusCode != 200 && is_array($result) && isset($result['error'])){
                return new WP_Error(400, $result['error']['message'], array('status' => 400));
            }
            return array_keys($result);
    }
}


?>