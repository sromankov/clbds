<?php

namespace Classes\Response;

/**
 * Static methods for JSON or CSV response instances creation
 * Class Response
 * @package Classes\Response
 */
class Response extends BaseResponse
{
    /**
     * Creates Json response instance by static call
     *
     * @param array $data
     * @param int $status
     * @return JsonResponse
     */
    static function json(array $data, $status = 200)
    {
        return  new JsonResponse($data, $status);
    }

    /**
     * Creates CSV response instance by static call
     *
     * @param array $data
     * @param int $status
     * @return CsvResponse
     */
    static function csv(array $data, $status = 200)
    {
        return  new CsvResponse($data, $status);
    }
}
