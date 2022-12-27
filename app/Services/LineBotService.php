<?php

namespace App\Services;

// Laravel
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

// LineBot
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

// Exception
use LINE\LINEBot\Exception\InvalidSignatureException;
use LINE\LINEBot\Exception\InvalidEventRequestException;

// MessageEvents
use LINE\LINEBot\Event\MessageEvent\TextMessage;

// MessageBuilders
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineBotService
{
    public $lineBot;

    public function __construct() {
        $httpClient = new CurlHTTPClient(config('line_bot.token'));
        $this->lineBot = new LINEBot($httpClient, ['channelSecret' => config('line_bot.secret')]);
    }

    public function getValidEvents(Request $request) {
        try {
            return $this->lineBot->parseEventRequest(
                $request->getContent(),
                $request->header('X_LINE_SIGNATURE')
            );
        } catch (LINEBot\Exception\InvalidSignatureException $exception){
            abort(400, $exception->getMessage());
        } catch (LINEBot\Exception\InvalidEventRequestException $exception){
            abort(400, $exception->getMessage());
        }
    }

    public function isTextEvent($event) {
        return $event instanceof TextMessage;
    }

    public function replyTexts($replyToken, array $replyTexts) {
        $response = call_user_func_array([$this->lineBot, 'replyText'], array_merge([$replyToken], $replyTexts));

        if (!$response->isSucceeded()) {
            $errorMessage = 'Reply Message Failed, HttpStatus: ' . $response->getHTTPStatus() . ', RawBody: ' . $response->getRawBody();
            Log::channel('debug')->debug($errorMessage);
            abort(400, $errorMessage);
        }
    }
}