<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

use App\Services\LineBotService;

class LineBotController extends Controller
{
    private $lineBotService;

    public function __construct(LineBotService $lineBotService){
        $this->lineBotService = $lineBotService;
    }

    public function echo(Request $request) {
        Log::channel('requests')->debug("Request Parameters: " . json_encode($request->all()));

        $validEvents = $this->lineBotService->getValidEvents($request);

        foreach ($validEvents as $event) {
            if ($this->lineBotService->isTextEvent($event)) {
                $replyTexts = [
                    'Message Received:',
                    $event->getText(),
                ];

                $this->lineBotService->replyTexts($event->getReplyToken(), $replyTexts);
            }
        }

        return response('HTTP_OK', Response::HTTP_OK);
    }
}
