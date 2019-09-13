<?php

/**
 * @author Łukasz Szpak ( info@dev-bot.pl )
 * */

namespace App;

class Request
{
    /**
     *
     * return $_GET or $_POST value
     * values give in _POST request have higher priority
     * @param string key
     * @param string type
     * Types:
     * - INT
     * - STRING
     * - ARRAY
     * @return mixed value or false if key doesnt exists
     * */
    public function get(string $key, string $type = null)
    {
        $value = isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : false);
        if ($value === false) {
            return false;
        }
        if ($type === null) {
            return $value;
        }
        switch (strtolower($type)) {
            case 'string':
                return (string) $value;
            break;
            case 'int':
                return is_numeric($value) ? (int) $value : false;
            break;

            case 'array':

                if (is_object($value) || is_array($value)) {
                    return (array) $value;
                }
                $value = json_decode($value);
                if (json_last_error() == JSON_ERROR_NONE) {
                    return (array) $value;
                } else {
                    return array();
                }
            break;
            case 'object':
                if (is_object($value) || is_array($value)) {
                    return (object) $value;
                }
                $value = json_decode($value);
                if (json_last_error() == JSON_ERROR_NONE) {
                    return (object) $value;
                } else {
                    return (object) array();
                }
            break;
        }
        return $value;
    }

    /**
     * checks if is post request
     * @return bool true if is post request or false
     *
     * */
    public function isPost()
    {
        return (bool) ($_SERVER['REQUEST_METHOD'] === 'POST');
    }

    /**
     * get post value
     * @param string _POST key
     * @param string type
     * Types:
     * - INT
     * - STRING
     * - ARRAY
     * @example $request->getPost('key', 'STRING');
     */
    public function getPost(string $key, string $type = null)
    {
        $value =  isset($_POST[$key]) ? $_POST[$key] : false;

        if ($value === false) {
            return false;
        }
        if ($type === null) {
            return $value;
        }
        $result = $value;
        switch (strtolower($type)) {
            case 'string':
               return (string) $value;
            break;
            case 'int':
                if (is_numeric($value)) {
                    return (int) $value;
                } else {
                    return false;
                }
            break;
            case 'array':
                if (is_object($value) || is_array($value)) {
                    return (array) $value;
                } else {
                    return array();
                }
            break;
            case 'object':
                if (is_object($value) || is_array($value)) {
                    return (object) $value;
                } else {
                    return (object) array();
                }
            break;
        }
    }

    /**
     * Detect an AJAX request
     *
     * @return bool true if is ajax request or false
     */
    public function isAjax()
    {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
        )
            ? true
            : false;
    }

    /**
     * Check if form includes files
     * @param string $name file input name
     * @return boolean
     * */
    public function hasFiles(string $name = null)
    {
        return $name !== null
            ? (
                isset($_FILES[$name]) && $_FILES[$name]
                ? true
                : false
            )
            : isset($_FILES) && $_FILES
                ? true
                : false;
    }


    /**
     * get uploaded files
     * @param string $name file input name
     * @return array each file as object \App\Request\File
     * */

    public function getFiles(string $name = null)
    {
        $array = array();
        $files = $_FILES;
        foreach ($files as $k => $v) {
            if (is_array($v['name'])) {
                $file = array();
                foreach ($v as $ku => $f) {
                    foreach ($f as $i => $s) {
                        $file[$i][$ku] = $s;
                    }
                }
                foreach ($file as $ff) {
                    $array[] =  new \App\Request\File($ff);
                }
                continue;
            }
            if ((isset($v['error']) && $v['error'] !== UPLOAD_ERR_OK) || ($name && $k != $name)) {
                continue;
            }
            $array[] = new \App\Request\File($v);
        }
        return $array;
    }
}
