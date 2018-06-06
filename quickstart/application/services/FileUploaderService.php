<?php

class FileUploaderService extends BaseService {

    private function _toBytes($str) {
        $val = trim($str);
        $last = strtolower($str [strlen($str) - 1]);
        switch ($last) {
            case 'g' :
                $val *= 1024;
            case 'm' :
                $val *= 1024;
            case 'k' :
                $val *= 1024;
        }
        return $val;
    }

    private function _save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()) {
            return false;
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        return true;
    }

    public function getName() {
        return strtolower($_GET ['filename']);
    }

    public function getSize() {
        if (isset($_SERVER ["CONTENT_LENGTH"])) {
            return (int) $_SERVER ["CONTENT_LENGTH"];
        } else {
            throw new Exception('Getting content length is not supported.');
        }
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    public function handleUpload() {
        global $fileuploader_config, $amazonconfig;
        $stores_folder = $amazonconfig->s3->stores_folder;
        $postSize = $this->_toBytes(ini_get('post_max_size'));
        $uploadSize = $this->_toBytes(ini_get('upload_max_filesize'));
        $store_id = $this->params['store_id'];

        if (!is_writable($this->params ['uploadDirectory'])) {
            $this->errnos ['DIR_ERROR'] = 1;
        }

        try {
            $size = $this->getSize();
        } catch (Exception $e) {
            $this->errnos ['CONTENT_LENGTH_ERROR'] = 1;
            return;
        }

        if ($size == 0) {
            $this->errnos ['FILE_EMPTY_ERROR'] = 1;
            return;
        }

        if ($size > $this->params ['sizeLimit'] || $size > $postSize || $size > $uploadSize) {
            $this->errnos ['FILE_SIZE_ERROR'] = 1;
            return;
        }

        $pathinfo = pathinfo($this->getName());
        $filename = $pathinfo ['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo ['extension'];

        if ($this->params ['allowedExtensions'] && !in_array(strtolower($ext), $this->params ['allowedExtensions'])) {
            $these = implode(', ', $this->params ['allowedExtensions']);
            $this->errnos [FILE_EXT_ERROR] = 1;
            return;
        }

        if (!$this->params ['replaceOldFile']) {
            /// don't overwrite previous files that were uploaded
            while (file_exists($this->params ['uploadDirectory'] . $filename . '.' . $ext)) {
                $filename .= rand(10, 99);
            }
        }
        $filename = $this->params['filename'];
        $full_filename = $this->params['uploadDirectory'] . $filename . '.' . $ext;

        mkdir2($this->params['uploadDirectory'], 755);

        $bucket_name = $fileuploader_config->store_bucket;
        if ($this->_save($full_filename)) {
            if (isset($this->params['uploadType']) && $this->params['uploadType'] == 'csv_importer') {
                $csv_folder = $amazonconfig->s3->csv_folder;
                $dst_file = "$csv_folder/$store_id/$filename" . '.' . $ext;
                upload_file_to_s3($full_filename, $bucket_name, $dst_file, S3::ACL_AUTHENTICATED_READ);
                $this->response['csv_file_path'] = $dst_file;
            } else {
                $dst_image = $this->params['uploadDirectory'] . $filename . '.' . 'jpg';
                $src_image = $full_filename;
                //image convert
                if (image_to_jpg($src_image, $dst_image, $ext)) {
                    imagerotate2($dst_image);
                    $this->response = array('success' => true, 'full_filename' => $full_filename);
                    //S3 upload part

                    $src_file = $dst_image;
                    $parts = explode('/', $src_file);
                    $file_name = array_pop($parts);
                    $dst_file = "/$bucket_name/$stores_folder/$store_id/$file_name";
                    $return = upload_image($dst_file, $src_file).'?v='.uniqid();
                    if ($return != false) {
                        $this->response['s3_upload_return'] = $return;
                        if (isset($this->params['account_dbobj'])) {
                            $account_dbobj = $this->params['account_dbobj'];
                            $store = new Store($account_dbobj);
                            $store->findOne('id=' . $store_id);
                            $store->setLogo($return);
                            $store->save();
                        } else if (isset($this->params['store_dbobj'])) {
                            $pic = new Picture($this->params['store_dbobj']);
                            $pic->findOne("id=" . $this->params['pic_id']);
                            $pic->setUrl($return);
                            $pic->save();
                            $this->response['pic_id'] = $this->params['pic_id'];
                        }
                    } else {
                        $this->errnos['FILE_UPLOAD_ERROR'] = 1;
                    }
                } else {
                    $this->errnos['IMAGE_CONVERT_ERROR'] = 1;
                }
            }
        } else {
            $this->errnos['FILE_UPLOAD_ERROR'] = 1;
            return;
        }
    }

}