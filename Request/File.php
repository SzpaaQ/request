<?php


/**
 * @author Łukasz Szpak ( info@dev-bot.pl )
 * */

class File
{


    /**
     * constructor
     * @param array file from $_FILES array
     * */
    public function __construct($file)
    {
        $this->name = $file['name'];
        $this->size = $file['size'];
        $this->temp = $file['tmp_name'];
        $this->err  = $file['error'];
        $this->extension = $this->getExtension();
        $this->file = $file;
    }
    /**
     * gets file extension
     * @return string extension of file - it only return last part of extension (eg if it's tar.gz file it will return "gz")
     * */
    public function getExtension()
    {
        if (isset($this->extension) && $this->extension !== false) {
            return $this->extension;
        }
        $ex = explode('.', $this->name);
        $this->extension = array_pop($ex);
        $this-> basename = implode('.', $ex);
        return $this->extension;
    }
    /**
     * get original file name
     * @param bool extension - ifdefault true, if false it will return filename without extension
     * @return string file name
     * */
    public function getName($extension = true)
    {
        return $extension === false ? $this->basename : $this->name;
    }

    /**
     * return size of uploaded file
     * @param string $type unit default - kilobytes
     * allowed types:
     * KB: Kilobytes
     * MB: Megabytes
     * GB: Gigabytes
     * TB: Terabytes
     * bit: bites
     * @return int size  of file
     * */
    public function getSize($type = 'KB')
    {
        $units = array(
            'bit' => 0.125,
            'KB' => 1024,
            'MB' => 1048576,
            'GB' => 1073741824,
            'TB' => 1099511627776
        );
        $type = isset($units[$type]) ? $units[$type] : 1;
        return $this->size * $type;
    }

    /**
     * get uploaded file name without extension
     * @return string filename without extension
     * */
    public function getBasename()
    {
        return $this->basename;
    }

    /**
     * moves uploaded file to target directory, It will make a directory if it doesn't exist
     * @param string $name target name
     * @param int chmod - default 0755
     * @return bool true if succed or false if error
     * */
    public function move(string $name, $chmod = 0655)
    {
        $ex = explode('/', $name);
        $name = array_pop($ex);
        $directory = self::TargetDir(implode('/', $ex), $chmod);
        $target = $directory .'/'. $name;
        if (!move_uploaded_file($this->temp, $target) || !chmod($target, $chmod)) {
            return false;
        }
        $this->temp = $target;
        return file_exists($target) ? true : false;
    }


    /**
     * creates directory if not exists and return target directory
     * @param string $directory target directory
     * @param int chmod - default 0755
     * @return string target directory
     * */
    public static function TargetDir(string $directory, $chmod = 0755)
    {
        $path = '';
        $ex = explode('/', trim($directory));
        $i = 0;
        while ($ex) {
            if (!$catalogue = array_shift($ex)) {
                continue;
            }
            $path .= '/'.$catalogue;
            /* open_basedir fix for lite server */
            if ($path == '/home') {
                continue;
            }
            if (!file_exists($path)) {
                mkdir($path, $chmod);
            }
        }
        return $path .'/';
    }

    /**
     * get type of file
     * @return string type of file
     * */
    public function getMimeType()
    {
        if (isset($this->type) && $this->type) {
            return $this->type;
        }
        if (class_exists('finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $this->type = $finfo->file($this->temp);
        } else {
            $this->type = mime_content_type($this->temp);
        }
        return $this->type;
    }

    /**
     * return first part of mime type
     * for jpg image returns "image" for php file return text
     * @return string type of file
     * */
    public function getType()
    {
        return explode('/', $this->getMimeType())[0];
    }
}
