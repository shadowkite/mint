<?php

namespace Mint;

class Node {
    private $url = 'https://bchd3.prompt.cash:2053/';

    private function send($url, $data) {
        // curl -X POST-H  "accept: application/json" -H  "Content-Type: application/json" -d "{  \"address\": \"bitcoincash:qrvz86esvnum3vh6zttnvzkn6hn4fma4lcc6v0ycll\",  \"include_mempool\": true,  \"include_token_metadata\": true}"
        $json = str_replace("\"", "\\\"", json_encode($data));
        $command = "curl -s -X POST ".$this->url . $url . " -H  \"accept: application/json\" -H \"Content-Type: application/json\" -d \"" . $json ."\"";

        exec($command, $output);

        $result = json_decode($output[0]);
        return $result;
    }

    public function getBalance($address) {
        $results = $this->send('v1/GetAddressUnspentOutputs', [
            'address' => $address,
            'include_mempool' => true,
            'include_token_metadata' => true,
        ]);
        $total = 0;
        foreach($results->outputs as $result) {
            if($result->slp_token) {
                continue;
            }
            $total += $result->value;
        }
        return $total;
    }
}