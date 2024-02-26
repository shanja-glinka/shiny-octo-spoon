<?php

namespace app\utils;


use \Exception;

class FilesUpload
{

    public static $phpFileUploadErrors = [
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    ];

    /**
     * @param int $code
     * @return null|string
     */
    public static function decodeFileUploadError(int $code): ?string
    {
        return FilesUpload::$phpFileUploadErrors[$code];
    }

    /**
     * Undocumented function
     *
     * @param string $fileVarName
     * @param string $uploaddir
     * @return array
     */
    public static function upload(string $fileVarName, string $uploaddir): array
    {
        $filenames = [];

        if (!isset($_FILES[$fileVarName])) {
            return $filenames;
        }


        if (is_string($_FILES[$fileVarName]['name'])) {
            $uploadfile = FilesUpload::singleUpload($fileVarName, $uploaddir);

            if ($uploadfile !== null) {
                $filenames[] = $uploadfile;
            }
        } else {
            $filenames = FilesUpload::multiUpload($fileVarName, $uploaddir);
        }

        return $filenames;
    }


    /**
     * @param string $fileVarName
     * @param string $uploaddir
     * @return array
     */
    public static function multiUpload(string $fileVarName, string $uploaddir): array
    {
        $filenames = [];
        $count = count($_FILES[$fileVarName]['name']);

        for ($i = 0; $i < $count; $i++) {

            $filename = $_FILES[$fileVarName]['name'][$i];
            $filetmpname = $_FILES[$fileVarName]['tmp_name'][$i];
            $error = $_FILES[$fileVarName]['error'][$i];

            if ($error > 0) {
                throw new Exception(self::decodeFileUploadError($error), 500);
            }

            $uploadname = $uploaddir . uniqid('', true) . '.' . $filename;

            if (!move_uploaded_file($filetmpname, $uploadname)) {
                continue;
            }

            $filenames[] = $uploadname;
        }

        return $filenames;
    }

    /**
     * @param string $fileVarName
     * @param string $uploaddir
     * @return string|null
     */
    public static function singleUpload(string $fileVarName, string $uploaddir): ?string
    {
        $filename = $_FILES[$fileVarName]['name'];
        $filetmpname = $_FILES[$fileVarName]['tmp_name'];
        $error = $_FILES[$fileVarName]['error'];

        if ($error > 0) {
            throw new Exception(self::decodeFileUploadError($error), 500);
        }

        $uploadname = $uploaddir . uniqid('', true) . '.' . $filename;

        if (!move_uploaded_file($filetmpname, $uploadname)) {
            return null;
        }

        return $uploadname;
    }
}
