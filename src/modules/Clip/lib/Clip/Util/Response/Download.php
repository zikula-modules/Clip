<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Util_Response
 */

/**
 * Download response class.
 */
class Clip_Util_Response_Download extends Zikula_Response_Ajax_AbstractBase
{
    /**
     * Constructor.
     *
     * @param string $payload Payload data.
     */
    public function __construct($filepath, $filename)
    {
        $this->payload  = $filepath;
        $this->filename = $filename;
    }

    /**
     * Convert class to string.
     *
     * @return string
     */
    public function __toString()
    {
        if (headers_sent()) {
            return 'Headers already sent.';
        }

        //header($this->createHttpResponseHeader());
        if (ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
        }

        $mimetypes = array(
            'pdf'   => 'application/pdf',
            'exe'   => 'application/octet-stream',
            'zip'   => 'application/zip',
            'doc'   => 'application/msword',
            'xls'   => 'application/vnd.ms-excel',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'gif'   => 'image/gif',
            'png'   => 'image/png',
            'jpeg'  => 'image/jpg',
            'gif'   => 'image/jpg',
            'txt'   => 'text/plain',
            'xml'   => 'text/xml',

            'kml'   => 'application/vnd.google-earth.kml+xml',
            'kmz'   => 'application/vnd.google-earth.kmz',
            'c'     => 'text/plain',
            'c++'   => 'text/plain',
            'list'  => 'text/plain',
            'log'   => 'text/plain',
            'lst'   => 'text/plain',
            'm'     => 'text/plain',
            'mar'   => 'text/plain',
            'pas'   => 'text/pascal',
            'pl'    => 'text/x-script.perl',
            'py'    => 'text/x-script.phyton',
            'rexx'  => 'text/x-script.rexx',
            'rtx'   => 'text/richtext',
            's'     => 'text/x-asm',
            'scm'   => 'text/x-script.scheme',
            'sdml'  => 'text/plain',
            'sgm'   => 'text/x-sgml',
            'sgml'  => 'text/x-sgml',
            'sh'    => 'text/x-script.sh',
            'talk'  => 'text/x-speech',
            'tcl'   => 'text/x-script.tcl',
            'tcsh'  => 'text/x-script.tcsh',
            'tsv'   => 'text/tab-separated-values',
            'zsh'   => 'text/x-script.zsh'
        );

        $extension = FileUtil::getExtension($this->filename);

        if (isset($mimetypes[$extension])) {
            $mimetype = $mimetypes[$extension];
        } else {
            $mimetype = 'application/force-download';
        }

        // taken from http://php.net/manual/en/function.header.php#102175
        header("Pragma: public"); // required
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false); // required for certain browsers
        header("Content-Type: $mimetype");
        header("Content-Disposition: attachment; filename=\"".$this->filename."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($this->payload));

        ob_clean();
        flush();

        readfile($this->payload);
    }
}
