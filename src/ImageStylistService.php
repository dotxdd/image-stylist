<?php

namespace Dotxdd\ImageStylist;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use UnexpectedValueException;

class ImageStylistService
{
    protected string $apiKey;
    protected string $apiEndpoint;
    protected string $model;
    protected Client $httpClient;

    public function __construct(string $apiKey, string $apiEndpoint, string $model, ?Client $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->apiEndpoint = $apiEndpoint;
        $this->model = $model;
        $this->httpClient = $httpClient ?? new Client();
    }

    public function getStyleAnalysis(array $imageUrls, string $userStyleProfile, string $language = 'en'): StyleAnalysisResult
    {
        if (empty($imageUrls)) {
            throw new InvalidArgumentException('The image URLs array cannot be empty.');
        }

        $payload = $this->buildProviderPayload($imageUrls, $userStyleProfile, $language);

        try {
            $isOllama = str_contains($this->apiEndpoint, 'ollama');

            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];

            if (!$isOllama) {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            $response = $this->httpClient->post($this->apiEndpoint, [
                'headers' => $headers,
                'json' => $payload,
                'timeout' => 180,
            ]);

            $responseBody = $response->getBody()->getContents();
            
            $rawDecodedJson = json_decode($responseBody, true);
            $jsonString = null;

            if ($isOllama && isset($rawDecodedJson['message']['content'])) {
                $jsonString = $rawDecodedJson['message']['content'];
            } elseif (!$isOllama && isset($rawDecodedJson['choices'][0]['message']['content'])) {
                $jsonString = $rawDecodedJson['choices'][0]['message']['content'];
            } else {
                throw new UnexpectedValueException('Could not find the content string in the API response.');
            }
            
            $finalData = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new UnexpectedValueException('Failed to decode the nested JSON from the API response. Error: ' . json_last_error_msg() . '. Raw content: ' . $jsonString);
            }

            $requiredKeys = ['objectiveDescription', 'styleAnalysis', 'isStyleMatch', 'outfitSuggestion', 'occasionAnalysis'];
            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $finalData)) {
                    throw new UnexpectedValueException("The final JSON data is missing the required key: '{$key}'.");
                }
            }

            return new StyleAnalysisResult(
                objectiveDescription: $finalData['objectiveDescription'],
                styleAnalysis: $finalData['styleAnalysis'],
                isStyleMatch: (bool) $finalData['isStyleMatch'],
                outfitSuggestion: $finalData['outfitSuggestion'],
                occasionAnalysis: $finalData['occasionAnalysis']
            );

        } catch (GuzzleException $e) {
            throw new \Exception('Failed to connect to the AI API endpoint: ' . $e->getMessage());
        }
    }
    
    private function buildProviderPayload(array $imageUrls, string $userStyleProfile, string $language): array
    {
        $prompt = $this->buildJsonPrompt($userStyleProfile, $language);

        if (str_contains($this->apiEndpoint, 'ollama')) {
            $base64Images = [];
            foreach ($imageUrls as $url) {
                try {
                    $imageData = file_get_contents($url);
                    if ($imageData === false) continue;
                    $base64Images[] = base64_encode($imageData);
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            return [
                'model' => $this->model,
                'prompt' => $prompt,
                'images' => $base64Images,
                'stream' => false,
                'format' => 'json'
            ];
        }

        $contentPayload = [['type' => 'text', 'text' => $prompt]];
        foreach ($imageUrls as $url) {
            $contentPayload[] = ['type' => 'image_url', 'image_url' => ['url' => $url]];
        }
        
        return [
            'model' => $this->model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $contentPayload,
                ],
            ],
            'max_tokens' => 800,
        ];
    }
    
    private function buildJsonPrompt(string $userStyleProfile, string $language): string
    {
        $languageInstruction = 'You MUST provide your entire JSON response, including all text values, in the following language: ' . $language . '.';
        
        $prompt = 'You are a fashion assistant for visually impaired users. You will be provided with several images of the same product, showing it from different angles or in different contexts. Synthesize the information from all images to create one cohesive analysis. ' . $languageInstruction . "\n";
        $prompt .= 'You MUST respond with a valid JSON object only, and nothing else. Do not include any introductory text or markdown formatting.' . "\n";
        $prompt .= 'The JSON object must have five specific keys:' . "\n";
        $prompt .= '1. "objectiveDescription": (string) A neutral, factual description of the item, combining details from all provided images.' . "\n";
        $prompt .= '2. "styleAnalysis": (string) A personalized comparison to the user\'s style, explaining in a friendly tone why it does or does not match.' . "\n";
        $prompt .= '3. "isStyleMatch": (boolean) A simple true or false based on your final recommendation.' . "\n";
        $prompt .= '4. "outfitSuggestion": (string or null) If the item is a style match (isStyleMatch is true), provide a brief suggestion for a complete outfit. If it is not a match, this key\'s value MUST be null.' . "\n";
        $prompt .= '5. "occasionAnalysis": (string) Briefly describe for what type of occasions this item would be appropriate (e.g., "casual wear, meetings with friends" or "formal events, business meetings").' . "\n\n";
        $prompt .= 'User\'s Style Profile: "' . $userStyleProfile . '"';

        return $prompt;
    }
}