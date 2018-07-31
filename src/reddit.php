<?php

class Reddit {

    private $client_id;
    private $client_secret;
    private $username;
    private $password;

    private $access_token;

    public function __construct(string $client_id, string $client_secret, string $username, string $password) {
        $this->client_id = $client_id;
        $this->client_secret  = $client_secret;
        $this->username = $username;
        $this->password = $password;

        $this->getToken();
    }

    public function postText(string $subreddit, string $title, string $content)  {
        $headers = [
            'Authorization: bearer ' . $this->access_token,
            'Content-Type: application/json; charset=utf-8',
            'User-Agent: WWIIOrders by /u/tustin25'
        ];

        $data = [
            "sr" => $subreddit,
            "text" => $content,
            "title" => $title,
            "kind" => "self",
            "api_type" => "json"
        ];

        $ch = curl_init("https://oauth.reddit.com/api/submit");

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        $data = json_decode($response);

        if (!$data['success']) {
            throw new Exception("error");
        }
    }

    private function getToken() {
        $data = [
            "grant_type" => "password",
            "username" => $this->username,
            "password" => $this->password,
        ];
        
        $ch = curl_init("https://www.reddit.com/api/v1/access_token");

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->client_id:$this->client_secret");

        $response = curl_exec($ch);

        $data = json_decode($response);

        $this->access_token = $data->access_token;
    }
}