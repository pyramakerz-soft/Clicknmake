<?php

namespace App\Services;

use GuzzleHttp\Client;

class CloudConverter
{
    protected $apiSecret;
    protected $client;

    public function __construct()
    {
        $this->apiSecret = env('CONVERT_API_SECRET');
        $this->client = new Client([
            'base_uri' => 'https://v2.convertapi.com/',
        ]);
    }

    public function convertToImages($inputPath, $ext)
    {
        $format = match ($ext) {
            'pdf'     => 'pdf/to/png',
            'ppt',
            'pptx'    => 'pptx/to/png',
            'doc',
            'docx'    => 'docx/to/png',
            default   => null,
        };

        if (!$format) return null;

        $response = $this->client->request('POST', "convert/$format?Secret={$this->apiSecret}", [
            'multipart' => [
                [
                    'name'     => 'File',
                    'contents' => fopen($inputPath, 'r'),
                ],
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['Files'] ?? null;
    }
}
