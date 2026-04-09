<?php

namespace App\Controller;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChatController extends AbstractController
{
    public function __construct(
        private readonly AgentInterface $default,
    ) {
    }

    #[Route('/chat', name: 'chat', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chat/index.html.twig');
    }

    #[Route('/chat/api', name: 'chat_api', methods: ['POST'])]
    public function api(Request $request): JsonResponse
    {
        $payload = $request->toArray();
        $inputMessages = $payload['messages'] ?? [];

        if (empty($inputMessages)) {
            return $this->json(['error' => 'Aucun message envoyé.'], 400);
        }

        $messages = new MessageBag();
        foreach ($inputMessages as $msg) {
            match ($msg['role'] ?? '') {
                'user' => $messages->add(Message::ofUser($msg['content'])),
                'assistant' => $messages->add(Message::ofAssistant($msg['content'])),
                default => null,
            };
        }

        $result = $this->default->call($messages);

        return $this->json([
            'response' => $result->getContent(),
        ]);
    }
}
