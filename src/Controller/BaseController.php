<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use React\Http\Response;

class BaseController
{
    public function jsonResponse(int $code, $data) : Response
    {
        return $this->response($code, json_encode($data), 'application/json');
    }

    public function textResponse(int $code, string $text) : Response
    {
        return $this->response($code, $text, 'text/plain');
    }

    public function jpegResponse(int $code, string $text) : Response
    {
        return $this->response($code, $text, 'image/jpeg');
    }

    public function response(int $code, string $text, string $contentType)
    {
        return new Response($code, ['Content-Type' => $contentType], $text);
    }
}
