<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

//keeps current user data
final class WOOCS_STORAGE
{

    public $type = 'session'; //session, transient, cookie
    private $user_ip = null;
    private $transient_key = null;

    public function __construct($type = '')
    {
        if (!empty($type))
        {
            $this->type = $type;
        }

        if ($this->type == 'session')
        {
            if (!session_id())
            {
                @session_start();
            }
        }
        
        $this->user_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        $this->transient_key = md5($this->user_ip);
    }

    public function set_val($key, $value)
    {
        $value=sanitize_text_field(esc_html($value));
        //***
        switch ($this->type)
        {
            case 'session':
                $_SESSION[$key] = $value;
                break;
            case 'transient':
                $data = get_transient($this->transient_key);
                if (!is_array($data))
                {
                    $data = array();
                }
                $data[$key] = $value;
                set_transient($this->transient_key, $data, 1 * 24 * 3600); //1 day
                break;
            case 'cookie':
                setcookie($key, $value, time() + 1 * 24 * 3600); //1 day
                break;

            default:
                break;
        }
    }

    public function get_val($key)
    {
        $value = NULL;
        switch ($this->type)
        {
            case 'session':
                if ($this->is_isset($key))
                {
                    $value = $_SESSION[$key];
                }
                break;
            case 'transient':
                $data = get_transient($this->transient_key);
                if (!is_array($data))
                {
                    $data = array();
                }
                if (isset($data[$key]))
                {
                    $value = $data[$key];
                }
                break;
            case 'cookie':
                if ($this->is_isset($key))
                {
                    $value = $_COOKIE[$key];
                }
                break;

            default:
                break;
        }

        return sanitize_text_field(esc_html($value));
    }

    public function is_isset($key)
    {
        $isset = false;
        switch ($this->type)
        {
            case 'session':
                $isset = isset($_SESSION[$key]);
                break;
            case 'transient':
                $isset = (bool) $this->get_val($key);
                break;
            case 'cookie':
                $isset = isset($_COOKIE[$key]);
                break;

            default:
                break;
        }

        return $isset;
    }

}
