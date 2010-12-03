<?php
/**
 * Clip
 *
 * @copyright  (c) Clip Team
 * @link       http://code.zikula.org/clip/
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package    Clip
 * @subpackage Export_Formatter
 */

/**
 * Export XML Formatter class.
 */
class Clip_Export_Formatter_XML
{
    protected $writer;

    /**
     * Insert the header.
     *
     * @return string
     */
    public function insertHeader()
    {
        $this->writer = new XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writer->setIndent(4);
        $this->writer->startElement('clip');

        return '';
    }

    /**
     * Insert a separator.
     *
     * @return string
     */
    public function insertSeparator()
    {
        return '';
    }

    /**
     * Insert the footer.
     *
     * @return string
     */
    public function insertFooter()
    {
        $this->writer->endElement();
        $this->writer->endDocument();

        return $this->writer->outputMemory();
    }

    /**
     * Formats a section.
     *
     * @param Clip_Export_Section $section Section to format.
     *
     * @return string
     */
    public function formatSection(Clip_Export_Section $section)
    {
        $xml = '';

        do {
            $data = $section->execute();

            if ($data) {
                $this->writer->startElement($section->getName());
                foreach ($data as $record) {
                    $this->writer->startElement($section->getRowname());
                    foreach ($record as $key => $value) {
                        $this->writer->writeElement($key, $value);
                    }
                    $this->writer->endElement();
                }
                $this->writer->endElement();
            }
        } while ($data);

        return $xml;
    }
}
