<?php

namespace App\Traits;

trait TextToImageTrait
{

    /**
     * Create image from text
     *
     * @param string text to convert into image
     * @param int font size of text
     * @param int width of the image
     * @param int height of the image
     */
    public function createImage($text, $imgWidth = 300, $imgHeight = 150): string
    {
        //text font path
        $font = resource_path('fonts/arial.ttf');

        $font_size = 24;
        $font_angle = 0;

        //create the image
        $img1 = imagecreate($imgWidth, $imgHeight);

        //create some colors
        $white = imagecolorallocate($img1, 255, 255, 255);
        $grey = imagecolorallocate($img1, 128, 128, 128);
        $black = imagecolorallocate($img1, 0, 0, 0);
        imagefilledrectangle($img1, 0, 0, $imgWidth, $imgHeight, $white);


        $text_size = imagettfbbox($font_size, $font_angle, $font, $text);
        $text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
        $text_height = max([$text_size[5], $text_size[7]]) - min([$text_size[1], $text_size[3]]);

        //break lines
        $centerX = CEIL(($imgWidth - $text_width) / 2);
        $centerX = max($centerX, 0);
        $centerY = CEIL(($imgHeight - $text_height) / 2);
        $centerY = max($centerY, 0);
        imagettftext($img1, $font_size, $font_angle, $centerX, $centerY, $black, $font, $text);

        $img = imagepng($img1);

        return base64_encode($img);
    }

}
