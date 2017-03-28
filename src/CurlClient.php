<?php

namespace Kooyara\RecommenderSystem;

class CurlClient {

    /**
     * @param string $method
     * @param string $url
     * @param array|NULL $headers
     * @param array|NULL $data
     * @param bool $encode
     * @return mixed
     */
    public function call(
        string $method,
        string $url,
        array $headers = NULL,
        array $data = NULL,
        bool $encode = FALSE
    ) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$method);

        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if ($data) {
            if (in_array($method, ['POST', 'PUT'])) {
                if ($encode) {
                    $dataString = json_encode($data);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $dataString);
                } else {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
            } else {
                $url = $url . '?' . http_build_query($data);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $result = curl_exec($curl);

        return $result;
    }
}