<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

use App\Services\LINEBotTiny;

class LineBotTinyController extends Controller
{
    private $lineBotTiny;

    public function __construct(){
        $this->lineBotTiny = new LINEBotTiny(config('line_bot.token'), config('line_bot.secret'));
    }

    public function echo(Request $request) {
        Log::channel('requests')->debug("Request Parameters: " . json_encode($request->all()));

        $validEvents = $this->lineBotTiny->parseEvents();

        foreach ($validEvents as $event) {
            $replyToken = data_get($event, 'replyToken');
            $inputText = data_get($event, 'message.text');

            if($replyToken && $inputText) {
                $replyTexts = [
                    [
                        'type' => 'text',
                        'text' => 'Message Received',
                    ],
                    [
                        'type' => 'text',
                        'text' => $inputText,
                    ]
                ];

                $this->lineBotTiny->replyMessage([
                    'replyToken' => $replyToken,
                    'messages' => $replyTexts,
                ]);
            }
        };

        return response('HTTP_OK', Response::HTTP_OK);
    }
}
