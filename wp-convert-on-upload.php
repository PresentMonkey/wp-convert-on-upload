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

if ( ! function_exists( 'wp_crop_image' ) ) {
  include( ABSPATH . 'wp-admin/includes/image.php' );
}

add_action('wp_handle_upload', 'custom_upload_filter');
function custom_upload_filter( $imagedata ) {
    $filetype = wp_check_filetype($imagedata['file']);
    if($filetype['type'] == 'image/heic'){
        $image_path = $imagedata['file'];
        $newPath = preg_replace("/\.heic$/", ".jpg", $image_path);
        $newUrl = preg_replace("/\.heic$/", ".jpg", $imagedata['url']);
        $imagedata['file'] = $newPath;
        $imagedata['url'] = $newUrl;
        $imagedata['type'] = 'image/jpeg';
        //we do the actual convertion in an "event" to not block the main thread/loop
        wp_schedule_single_event(time(), 'convert_file', array($image_path, $newPath, $newUrl));
        return $imagedata;
    }
    else{
        return $imagedata;
    }
}

add_action('convert_file', 'convert', 10, 3);
function convert($oldpath, $newpath, $url){
        $im = new Imagick();
        $im->readImage($oldpath);
        $im->setImageFormat("jpg");
        file_put_contents($oldpath, $im);
        rename($oldpath, $newpath);
        $image_id = attachment_url_to_postid($url);
        $metadata = wp_generate_attachment_metadata($image_id, $newpath);
        wp_update_attachment_metadata($image_id, $metadata);
}
