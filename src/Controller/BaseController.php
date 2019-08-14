<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use React\Http\Response;

class BaseController
{
    /**
     * @param int $code
     * @param string $data
     * @return Response
     */
    public function binResponse(int $code, string $data) : Response
    {
        return $this->response($code, $data, 'application/octet-stream');
    }

    /**
     * @param int $code
     * @param array $data
     * @return Response
     */
    public function jsonResponse(int $code, array $data) : Response
    {
        return $this->response($code, json_encode($data, JSON_FORCE_OBJECT), 'application/json');
    }

    /**
     * @param int $code
     * @param string $text
     * @return Response
     */
    public function htmlResponse(int $code, string $text) : Response
    {
        return $this->response($code, $text, 'text/html');
    }

    /**
     * @param int $code
     * @param string $text
     * @param string $md5
     * @return Response
     */
    public function textResponse(int $code, string $text, string $md5 = '') : Response
    {
        return $this->response($code, $text, 'text/plain', $md5);
    }

    /**
     * @param int $code
     * @param string $text
     * @return Response
     */
    public function jpegResponse(int $code, string $text) : Response
    {
        return $this->response($code, $text, 'image/jpeg');
    }

    /**
     * @param int $code
     * @param string $text
     * @param string $contentType
     * @param string $md5
     * @return Response
     */
    public function response(int $code, string $text, string $contentType, string $md5 = '')
    {
        return new Response($code, ['Content-Type' => $contentType, 'md5' => $md5], $text);
    }
}
