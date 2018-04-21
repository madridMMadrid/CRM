<?php
/**
 * @author: Odaxelangia odaxelangia.naxagi@gmail.com
 */

namespace App;

/**
 * Class UrlShortener
 *
 * @package Core
 */
class UrlShortener
{

    protected $chars = '123456789BCDFGHJKLMNPQRSTVWXYZbcdfghjkmnpqrstvwxyz';
    protected $link;
    private $key;

    /**
     * UrlShortener constructor.
     */
    public function __construct()
    {
        $this->key = mt_rand(1111111, 9999999);
        $this->link = '';
    }

    /**
     * @param int $id
     * @return string
     */
    public function generateShortUrl(): string
    {
        $count = mb_strlen($this->chars);
        $intval = $this->key;
        for ($i = 0; $i < 5; $i++) {
            $last = $intval % $count;
            $intval = ($intval - $last) / $count;
            $this->link .= $this->chars[$last];
        }
        return $this->link;
    }
}
