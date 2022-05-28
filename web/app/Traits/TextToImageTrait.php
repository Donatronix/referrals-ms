<?php

namespace App\Traits;

trait TextToImageTrait
{

    private $img;

    /**
     * Create image from text
     *
     * @param string text to convert into image
     * @param int font size of text
     * @param int width of the image
     * @param int height of the image
     */
    public function createImage($text, $fontSize = 20, $imgWidth = 400, $imgHeight = 80): self
    {
        //text font path
        $font = resource_path('fonts/arial.ttf');

        //create the image
        $this->img = imagecreatetruecolor($imgWidth, $imgHeight);

        //create some colors
        $white = imagecolorallocate($this->img, 255, 255, 255);
        $grey = imagecolorallocate($this->img, 128, 128, 128);
        $black = imagecolorallocate($this->img, 0, 0, 0);
        imagefilledrectangle($this->img, 0, 0, $imgWidth - 1, $imgHeight - 1, $white);

        //break lines
        $splitText = explode("\\n", $text);
        $lines = count($splitText);

        foreach ($splitText as $txt) {
            $textBox = imagettfbbox($fontSize, 45, $font, $txt);
            $textWidth = abs(max($textBox[2], $textBox[4]));
            $textHeight = abs(max($textBox[5], $textBox[7]));
            $x = (imagesx($this->img) - $textWidth) / 2;
            $y = ((imagesy($this->img) + $textHeight) / 2) - ($lines - 2) * $textHeight;
            $lines = $lines - 1;

            //add some shadow to the text
            imagettftext($this->img, $fontSize, 45, $x, $y, $grey, $font, $txt);

            //add the text
            imagettftext($this->img, $fontSize, 45, $x, $y, $black, $font, $txt);
        }
        return $this;
    }

    /**
     * Display image
     */
    public function showImage(): string
    {
        $img = imagepng($this->img);

        return 'data:image/png' . ';base64,' . base64_encode($img);
    }

    /**
     * Save image as png format
     *
     * @param string file name to save
     * @param string location to save image file
     */
    public function saveAsPng($fileName = 'text-image', $location = '')
    {
        $fileName = $fileName . ".png";
        $fileName = !empty($location) ? $location . $fileName : $fileName;
        return imagepng($this->img, $fileName);
    }

    /**
     * Save image as jpg format
     *
     * @param string file name to save
     * @param string location to save image file
     */
    public function saveAsJpg($fileName = 'text-image', $location = '')
    {
        $fileName = $fileName . ".jpg";
        $fileName = !empty($location) ? $location . $fileName : $fileName;
        return imagejpeg($this->img, $fileName);
    }
}
