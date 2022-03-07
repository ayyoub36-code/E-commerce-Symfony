<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class MailJet
{

    private $api_key = '2d939812257e9a42291f9556f8f36ad6';
    private $api_key_secret = '6fa019718a8d57d44cc422f28b8e6551';


    public function send($to_email, $to_name, $subject, $content)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "ayyoub36@hotmail.fr",
                        'Name' => "La Boutique Marocaine"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3698117,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}
