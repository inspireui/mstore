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
        return get_option('mstore_purchase_code') === "1";
    }
}