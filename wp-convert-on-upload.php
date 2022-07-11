<?php

/**

 * @package heic-upload

 */

/*

Plugin Name: WPConvert-On-Upload

Description: Converts files on upload.

Version: 0.1

Author: Patrick G

License: MIT

*/

add_action('wp_handle_upload', 'custom_upload_filter');
function custom_upload_filter( $imagedata ) {
    $filetype = wp_check_filetype($imagedata['file']);
    if($filetype['type'] == 'image/heic'){
        $image_path = $imagedata['file'];
        $im = new Imagick();
        $im->readImage($image_path);
        $im->setImageFormat("jpg");
        file_put_contents($image_path, $im);
        $newPath = preg_replace("/\.heic$/", ".jpg", $image_path);
        $newUrl = preg_replace("/\.heic$/", ".jpg", $imagedata['url']);
        rename($image_path, $newPath);
        $imagedata['file'] = $newPath;
        $imagedata['url'] = $newUrl;
        $imagedata['type'] = 'image/jpeg';

        return $imagedata;
    }
    else{
        return $imagedata;
    }
}
