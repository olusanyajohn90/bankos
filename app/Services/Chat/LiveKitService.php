<?php

namespace App\Services\Chat;

use Agence104\LiveKit\AccessToken;
use Agence104\LiveKit\AccessTokenOptions;
use Agence104\LiveKit\VideoGrant;

class LiveKitService
{
    private string $apiKey;
    private string $apiSecret;
    private string $host;

    public function __construct()
    {
        $this->apiKey = config('services.livekit.api_key');
        $this->apiSecret = config('services.livekit.api_secret');
        $this->host = config('services.livekit.host');
    }

    public function generateToken(string $roomName, string $participantIdentity, string $participantName): string
    {
        $tokenOptions = (new AccessTokenOptions())
            ->setIdentity($participantIdentity)
            ->setName($participantName);

        $videoGrant = (new VideoGrant())
            ->setRoomJoin(true)
            ->setRoom($roomName);

        $token = (new AccessToken($this->apiKey, $this->apiSecret))
            ->init($tokenOptions)
            ->setGrant($videoGrant)
            ->toJwt();

        return $token;
    }

    public function createRoom(string $roomName): void
    {
        // Room is auto-created when first participant joins with LiveKit
        // This method exists for explicit room creation if needed
    }

    public function getWsUrl(): string
    {
        // Convert http(s) to ws(s)
        return str_replace(['http://', 'https://'], ['ws://', 'wss://'], $this->host);
    }
}
