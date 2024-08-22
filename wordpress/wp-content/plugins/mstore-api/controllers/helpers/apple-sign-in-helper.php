<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AppleSignInHelper
{
    public static function generate_secret_key($bundle_id, $team_id){
        $file_name = get_option("mstore_apple_sign_in_file_name");
        $file_path =  FlutterAppleSignInUtils::get_config_file_path($file_name);

        $key_id = get_option("mstore_apple_sign_in_key_id");
        $private_key = file_get_contents($file_path);
        $current_time = time();
        $payload = [
            'iss' => $team_id,
            'aud' => 'https://appleid.apple.com',
            'sub' => $bundle_id,
            'iat' => $current_time,
            'exp' => $current_time + 86400 * 180
        ];
        
        $headers = [
            'alg' => 'ES256',
            'kid' => $key_id
        ];
        
        return JWT::encode($payload, $private_key, 'ES256', null, $headers);
    }

    public static function generate_token($bundle_id, $team_id, $code){
        // Apple's token endpoint URL
        $tokenEndpoint = 'https://appleid.apple.com/auth/token';

        // Prepare the request data
        $requestData = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $bundle_id,
            'client_secret' => AppleSignInHelper::generate_secret_key($bundle_id,$team_id),
        );

        // Set cURL options
        $ch = curl_init($tokenEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
            curl_close($ch);
            return false;
        }

        // Close cURL
        curl_close($ch);

        // Decode the JSON response
        $data = json_decode($response, true);

        if(isset($data['error_description'])){
            return new WP_Error($data['error_description']);
        }
        if (isset($data['id_token'])) {
            return $data['id_token'];
        } else {
            return false;
        }
    }
}


?>