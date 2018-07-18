<?php

namespace Classes\Response;

/**
 * Csv extension of the Response
 *
 * Class CsvResponse
 * @package Classes\Response
 */
class CsvResponse extends BaseResponse
{
    /**
     * Some csv-format parameters
     */
    const FIELDS_DELIMITER = ";";
    const LINES_DELIMITER = "\n";

    /**
     * CsvResponse constructor. Sets CSV-specific and allowing file download header lines
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Description', 'CSV content');
        $this->headers->set('Content-Type', 'application/octet-stream');
        $this->headers->set('Content-Transfer-Encoding', 'binary');
        $this->headers->set('Connection', 'Keep-Alive');
        $this->headers->set('Expires', '0');
        $this->headers->set('Content-Disposition', 'attachment; filename=data.csv');
        $this->headers->set('Pragma', 'public');
    }

    /**
     * Buils CSV-formated response body with header line
     * @param array $content
     * @return $this
     */
    public function setContent($content)
    {
        $csv = $this->getHeaderLine($content) . $this->getLines($content);
        $this->headers->set('Content-Length', strlen($csv));

        return parent::setContent($csv);
    }

    /**
     * Extracts columns headers from data
     *
     * @param array $data
     * @return string
     */
    protected function getHeaderLine($data)
    {
        $list = Hit::getOperations();
        $record = ['country'];
        foreach ($list as $key => $value) {
            $record[] = $key;
        }
        return implode(self::FIELDS_DELIMITER, $record) . self::LINES_DELIMITER;
    }

    /**
     * Extracts columns content from data
     *
     * @param array $data
     * @return string
     */
    protected function getLines($data)
    {
        $lines = '';

        foreach ($data as $key => $values) {
            $record = [$key];
            foreach ($values as $value) {
                $record[] = $value;
            }

            $lines .= implode(self::FIELDS_DELIMITER, $record) . self::LINES_DELIMITER;
        }

        return $lines;
    }
}
