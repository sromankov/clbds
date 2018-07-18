<?php

namespace Classes\Response;

/**
 * JSON extension of the Response
 *
 * Class JsonResponse
 * @package Classes\Response
 */
class JsonResponse extends BaseResponse
{
    /**
     * JsonResponse constructor. Sets JSON-specific header lines
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     */
    public function __construct($content = '', $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, $headers);
        $this->headers->set('Content-Type', 'application/json');
    }

    /**
     * Encodes assoc. aray into the JSON body
     *
     * @param array $content
     * @return $this
     */
    public function setContent($content)
    {
        $json = json_encode($content, JSON_NUMERIC_CHECK);
        if (json_last_error () !== JSON_ERROR_NONE) {
            throw new Exception('Data can not be encoded');
        }

        return parent::setContent($json);
    }
}
