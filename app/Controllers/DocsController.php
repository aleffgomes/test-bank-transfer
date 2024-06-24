<?php

namespace App\Controllers;
use CodeIgniter\RESTful\ResourceController;

class DocsController extends ResourceController
{
    public function docsJson()
    {
        $json = file_get_contents(FCPATH . 'openapi.json');

        return $this->respond(json_decode($json), 200);
    }

    public function docs()
    {
        return view('swagger-ui');
    }
}
