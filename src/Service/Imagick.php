<?php

namespace AVAllAC\PersistentBlockStorage\Service;

class Imagick
{
    /**
     * @param string $format
     * @param string $blobImage
     * @return string
     * @throws \ImagickException
     */
    public function thumb(string $format, string $blobImage) : string
    {
        $x_pos = strpos($format, 'x');
        $dash_pos = strpos($format, '-');
        $target_width = substr($format, 0, $x_pos);
        $target_height = substr($format, $x_pos + 1, $dash_pos - $x_pos - 1);
        $mode = substr($format, $dash_pos + 1);

        $new_width = $target_width;
        $new_height = $target_height;
        $image = new \Imagick();
        $image->readImageBlob($blobImage);
        $orig_height = $image->getImageHeight();
        $orig_width = $image->getImageWidth();

        if ($mode === "0") {
            if ($new_width != 0) {
                $new_height = $orig_height * $new_width / $orig_width;
                if (($new_height > $target_height) && ($target_height != 0)) {
                    $new_width = $orig_width * $target_height / $orig_height;
                    $new_height = $target_height;
                }
            } else {
                $new_width = $orig_width * $target_height / $orig_height;
                $new_height = $target_height;
            }
        } elseif ($mode === "2") {
            $desired_aspect = $target_width / $target_height;
            $orig_aspect = $orig_width / $orig_height;
            if ($desired_aspect > $orig_aspect) {
                $trim = $orig_height - ($orig_width / $desired_aspect);
                $image->cropImage($orig_width, $orig_height-$trim, 0, $trim/2);
            } else {
                $trim = $orig_width - ($orig_height * $desired_aspect);
                $image->cropImage($orig_width-$trim, $orig_height, $trim/2, 0);
            }
        }

        $image->resizeImage($new_width, $new_height, \imagick::FILTER_LANCZOS, 1);
        return (string)$image;
    }
}
