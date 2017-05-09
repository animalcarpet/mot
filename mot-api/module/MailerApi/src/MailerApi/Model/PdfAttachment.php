<?php
/**
 * Created by PhpStorm.
 * User: wojciechh
 * Date: 03/09/15
 * Time: 13:27.
 */

namespace MailerApi\Model;

class PdfAttachment extends Attachment
{
    /**
     * PdfAttachment constructor.
     *
     * @param $content
     * @param $filename
     */
    public function __construct($content, $filename)
    {
        $realFilename = $filename;
        if (substr_compare($filename, '.pdf', strlen($filename) - 4, 4) === 0) {
            $realFilename = $filename.'.pdf';
        }
        parent::__construct($content, 'application/pdf', $realFilename);
    }
}
