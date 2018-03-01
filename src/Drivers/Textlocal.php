<?php
namespace Tzsk\Sms\Drivers;

use GuzzleHttp\Client;
use Tzsk\Sms\Contract\MasterDriver;

class Textlocal extends MasterDriver
{
    /**
     * Textlocal Settings.
     *
     * @var object|null
     */
    protected $settings = null;

    /**
     * Http Client.
     *
     * @var Client|null
     */
    protected $client = null;

    /**
     * Construct the class with the relevant settings.
     *
     * SendSmsInterface constructor.
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->settings = (object) $settings;
        $this->client = new Client();
    }

    /**
     * Send text message and return response.
     *
     * @return mixed
     */
    public function send()
    {
        $numbers = implode(",", $this->recipients);

        $response = $this->client->request("POST", $this->settings->url, [
            "form_params" => [
                "username" => $this->settings->username,
                "hash" => $this->settings->hash,
                "numbers" => $numbers,
                "sender" => urlencode($this->settings->sender),
                "message" => $this->body,
            ],
        ]);

        $data = $this->getResponseData($response);

        return (object) $data;
    }

    /**
     * Get the response data.
     *
     * @param  object $response
     * @return array|object
     */
    protected function getResponseData($response)
    {
        if ($response->getStatusCode() != 200) {
            return (object) ["status" => false, "message" => "Request Error. " . $response->getReasonPhrase()];
        }

        $data = json_decode((string) $response->getBody(), true);

        if ($data["status"] != "success") {
            return (object) ["status" => false, "message" => "Something went wrong.", "data" => $data];
        }

        return $data;
    }
}
