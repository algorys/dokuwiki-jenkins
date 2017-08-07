<?php
/*
 * Jenkins API
 * @author Algorys
 */
class DokuwikiJenkins {
    public $client;
    public $url;

    function __construct($data) {
        $this->client = curl_init();
        $this->url = $data['protocol'].'://'.$data['user'].':'.$data['token'].'@'.$data['url'];
    }

    function request($url, $build = false) {
        if ($build)
            $url_srv = $this->url . $url . '/api/json';
        else
            $url_srv = $this->url . $url . '/lastBuild/api/json';

        curl_setopt($this->client, CURLOPT_URL, $url_srv);
        curl_setopt($this->client, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($this->client, CURLOPT_RETURNTRANSFER, true);

        $answer = curl_exec($this->client);
        $answer_decoded = json_decode($answer, true);

        return $answer_decoded;
    }

    function getJobURLRequest($job) {
        $job_request = explode('/', $job);

        $job_url = '';
        foreach ($job_request as $key => $job_part) {
            $job_url .= '/job/'.$job_part;
        }

        return $job_url;
    }
}
