<?php

class FlutterBaseController
{
    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function sendError($code, $message, $statusCode)
    {
        return new WP_Error($code, $message, array('status' => $statusCode));
    }

    public function checkApiPermission()
    {
        return isPurchaseCodeVerified();
    }

    /**
     * Send invalid plugin error.
     * 
     * @param string $message Error message.
     */
    public function send_invalid_plugin_error($message)
    {
        return $this->sendError("invalid_plugin", $message, 403);
    }
}
