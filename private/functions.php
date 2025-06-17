<?php
function resize_image($src, $dest, $max_width, $max_height) {
    [$width, $height, $type] = getimagesize($src);
    $ratio = min($max_width/$width, $max_height/$height);
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);

    $dst_img = imagecreatetruecolor($new_width, $new_height);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $src_img = imagecreatefromjpeg($src);
            break;
        case IMAGETYPE_PNG:
            $src_img = imagecreatefrompng($src);
            break;
        default:
            return false;
    }

    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    imagejpeg($dst_img, $dest);
    imagedestroy($src_img);
    imagedestroy($dst_img);
    return true;
}
?>
