<?php

// This file is an example of how to use the library.
// To run it, you need to first run 'composer install' in this directory.
// You also need to create a .env file with your OPENAI_API_KEY.

require 'vendor/autoload.php';

use Dotxdd\ImageStylist\ImageStylistService;
use Dotxdd\ImageStylist\StyleAnalysisResult;

// A simple way to load environment variables without a full framework
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// --- CONFIGURATION ---
$config = [
    'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
    'endpoint' => 'https://api.openai.com/v1/chat/completions',
    'model' => 'gpt-4o',
];

if (empty($config['api_key'])) {
    die("Error: OPENAI_API_KEY is not set. Please create a .env file in this directory.\n");
}

echo "--- [AI Image Stylist Library Example] ---\n";

try {
    // --- 1. Initialize the service ---
    $service = new ImageStylistService(
        apiKey: $config['api_key'],
        apiEndpoint: $config['endpoint'],
        model: $config['model']
    );

    // --- 2. Prepare the data for analysis ---
    $userStyleProfile = "I prefer an elegant and business-casual style. I mainly wear muted colors like navy, grey, and white. I like well-tailored blazers and simple trousers. I avoid bright colors and sportswear.";
    $imageUrls = [
            'https://images.unsplash.com/photo-1521223890158-f9f7c3d5d504?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=680' 
    ];

    echo "Analyzing " . count($imageUrls) . " images...\n";

    // --- 3. Call the service to get the structured result ---
    /** @var StyleAnalysisResult $result */
    $result = $service->getStyleAnalysis($imageUrls, $userStyleProfile, 'en');

    // --- 4. Use the data from the result object ---
    echo "\n--- [Full Style Analysis from AI] ---\n";

    if ($result->isStyleMatch) {
        echo "✅ Recommendation: This item is a likely MATCH for your style.\n";
    } else {
        echo "⚠️ Recommendation: This item is likely NOT a match for your style.\n";
    }

    echo "\nObjective Description:\n";
    echo $result->objectiveDescription . "\n";

    echo "\nStyle Analysis:\n";
    echo $result->styleAnalysis . "\n";
    
    echo "\nOutfit Suggestion:\n";
    echo $result->outfitSuggestion ?? 'None provided.' . "\n";
    
    echo "\nOccasion Analysis:\n";
    echo $result->occasionAnalysis . "\n";

    echo "\n--- [Test Completed Successfully] ---\n";

} catch (\Exception $e) {
    echo "\n--- [An Error Occurred] ---\n";
    echo $e->getMessage() . "\n";
}