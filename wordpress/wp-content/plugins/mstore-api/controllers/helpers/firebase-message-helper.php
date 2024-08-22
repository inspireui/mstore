<?php
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseMessageHelper
{
    static $folder_path = 'flutter_firebase';

    public static function create_config_folder(){
        $uploads_dir = wp_upload_dir();
        $folder = trailingslashit($uploads_dir["basedir"]) . FirebaseMessageHelper::$folder_path;
        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }
    }

    public static function get_config_file_url(){
        $file_name = FirebaseMessageHelper::get_file_name();
        $filePath =  FirebaseMessageHelper::get_config_file_path($file_name);
        $uploads_dir = wp_upload_dir();
        $p_path = FirebaseMessageHelper::$folder_path;
        $folder = trailingslashit($uploads_dir["baseurl"]) . $p_path;
        return trailingslashit($folder) . $file_name;
    }

    public static function get_config_file_path($file_name){
        $path = FirebaseMessageHelper::$folder_path;
        $uploads_dir = wp_upload_dir();
        $folder = trailingslashit($uploads_dir["basedir"]) . $path;
        $folder_path = realpath($folder);

        return trailingslashit($folder_path). $file_name;
    }

    public static function upload_file_by_admin($file_to_upload) {
        $file_name = $file_to_upload['name'];
        $source      = $file_to_upload['tmp_name'];
        $fileContent = file_get_contents($source);
  
        $json = json_decode($fileContent, true);
        if(!$json){
            return 'Invalid json file';
        }
        $access_token = FirebaseMessageHelper::get_access_token($json);
        if($access_token){
            wp_upload_bits($file_name, null, $fileContent); 
            $destination = FirebaseMessageHelper::get_config_file_path($file_name);
            FirebaseMessageHelper::create_config_folder();
            move_uploaded_file($source, $destination);
            update_option("mstore_firebase_file_name", $file_name);
            return null;
        }else{
            return 'You need to upload Firebase private key file';
        }
    }

    public static function get_file_name(){
        $file_name = get_option("mstore_firebase_file_name");
        return $file_name;
    }

    public static function is_file_existed(){
        $file_name = FirebaseMessageHelper::get_file_name();
        $filePath =  FirebaseMessageHelper::get_config_file_path($file_name);
        return isset($file_name) && strlen($file_name) > 0 && file_exists($filePath);
    }

    public static function delete_config_file($nonce){
        if (wp_verify_nonce($nonce, 'delete_config_firebase_file')) {
            $file_name = FirebaseMessageHelper::get_file_name();
            $filePath =  FirebaseMessageHelper::get_config_file_path($file_name);
            unlink($filePath);
            update_option("mstore_firebase_file_name", "");
        }
    }

    public static function get_access_token($config){
        $sa = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/firebase.messaging',
            $config,
        );
        $token = $sa->fetchAuthToken();
    
        $access_token = $token['access_token'];
        return $access_token;
    }

    public static function push_notification($title, $message, $deviceToken)
    {
        if(!FirebaseMessageHelper::is_file_existed()){
            return new WP_Error(404, "Firebase private key file is not found", array('status' => 404));
        }
        $file_name = FirebaseMessageHelper::get_file_name();
        $file_path = FirebaseMessageHelper::get_config_file_path($file_name);
        $fileContent = file_get_contents($file_path);
        $json = json_decode($fileContent, true);
        $projectId = $json['project_id'];
        if (!empty($projectId)) {
            $access_token = FirebaseMessageHelper::get_access_token($json);
            $body = [
                "message" => [
                    "token" => $deviceToken,
                    "notification" => [
                        "title" => $title, 
                        "body" => $message
                    ],
                    "data" => [
                        "title" => $title, 
                        "body" => $message
                    ],
                    "android" => [
                        "notification" => [
                            "click_action" => "FLUTTER_NOTIFICATION_CLICK",
                            "default_sound"=> true
                        ]
                    ],
                    "apns" => [
                        "headers"=>[
                            "apns-priority" => "10"
                        ], 
                        "payload"=>[
                            "aps" => [
                                "default_sound"=> true
                            ],
                        ],
                    ],
                ],
            ];
            $headers = ["Authorization" => "Bearer ".$access_token, 'Content-Type' => 'application/json; charset=utf-8'];
            $response = wp_remote_post("https://fcm.googleapis.com/v1/projects/".$projectId."/messages:send", ["headers" => $headers, "body" => json_encode($body)]);
            $statusCode = wp_remote_retrieve_response_code($response);
            $result = wp_remote_retrieve_body($response);
            $result = json_decode($result, true);
            if($statusCode != 200 && is_array($result) && isset($result['error'])){
                return new WP_Error(400, $result['error']['message'], array('status' => 400));
            }
            return $statusCode == 200;
        }
        return false;
    }
}


?>