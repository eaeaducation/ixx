<?php
/**
 * User: Jasmine2
 * Date: 2017-8-19 13:22
 * Email: youjingqiang@gmail.com
 * Copyright (c) Guangzhou Zhishen Data Service co,. Ltd
 */

use Endroid\QrCode\QrCode as ParentQRcode;

class QRCode extends ParentQRcode
{
    protected $logoPath;

    /**
     * @param string $logoPath
     * 重写Logo方法,使支持网络图片
     */
    public function setLogoPath($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    public function getLogoPath()
    {
        return $this->logoPath;
    }

    public static function createQRCodeString($text, $width = 120, $logo = null, $logo_width = 30)
    {
        $qrCode = new self($text);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setSize($width);
        $qrCode->setMargin(6);
        if ($logo !== null) {
            $qrCode->setLogoPath($logo);
            $qrCode->setLogoWidth($logo_width);
        }
        return base64_encode($qrCode->writeString());
    }
}