# AI Image Stylist

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

An AI-powered PHP library to describe and analyze clothing items from images, designed specifically to assist visually impaired users in making informed fashion choices.

This tool provides not just a neutral description of a product, but also a personalized style analysis, outfit suggestions, and occasion recommendations, making online shopping more accessible and intuitive.

## Features

-   **Multi-Image Analysis**: Gathers details from multiple product images for a comprehensive description.
-   **Personalized Style Matching**: Compares an item against a user's personal style profile.
-   **Structured Data Output**: Returns a clean, easy-to-use PHP object (`StyleAnalysisResult`) with distinct data points.
-   **Outfit & Occasion Suggestions**: Provides actionable advice on how to wear an item and where.
-   **Provider-Agnostic**: Can be configured to work with any OpenAI-compatible API, including commercial services like OpenAI and free, local models via Ollama.
-   **Multi-language Support**: Delivers analysis in the user's preferred language.

## Installation

You can install the package via Composer:

```bash
composer require dotxdd/image-stylist
```

## How to Use

The library is designed to be straightforward. Here is a basic example of how to use it with the OpenAI API.

### 1. Initialization

First, initialize the `ImageStylistService`. You need to provide your API key, the API endpoint, and the model name.

```php
<?php

require 'vendor/autoload.php';

use Dotxdd\ImageStylist\ImageStylistService;
use Dotxdd\ImageStylist\StyleAnalysisResult;

// --- Configuration for OpenAI ---
$config = [
    'api_key' => 'YOUR_OPENAI_API_KEY',
    'endpoint' => 'https://api.openai.com/v1/chat/completions',
    'model' => 'gpt-4o',
];

$service = new ImageStylistService(
    apiKey: $config['api_key'],
    apiEndpoint: $config['endpoint'],
    model: $config['model']
);
```

### 2. Performing the Analysis

Prepare your data and call the `getStyleAnalysis` method.

```php
try {
    // A description of the user's personal style
    $userStyleProfile = "I prefer an elegant and business-casual style. I mainly wear muted colors like navy, grey, and white. I like well-tailored blazers and simple trousers.";

    // An array of one or more image URLs of the product
    $imageUrls = [
        'https://.../image-of-a-blazer-front.jpg',
        'https://.../image-of-a-blazer-detail.jpg',
    ];

    // Call the service and get a structured result object
    /** @var StyleAnalysisResult $result */
    $result = $service->getStyleAnalysis($imageUrls, $userStyleProfile, 'en'); // 'en' for English

    // --- 3. Using the Results ---
    
    if ($result->isStyleMatch) {
        echo "✅ This item is a likely match for your style.\n";
    } else {
        echo "⚠️ This item is likely not a match for your style.\n";
    }

    echo "Description: " . $result->objectiveDescription . "\n";
    echo "Style Analysis: " . $result->styleAnalysis . "\n";
    echo "Outfit Suggestion: " . ($result->outfitSuggestion ?? 'None provided.') . "\n";
    echo "Occasion: " . $result->occasionAnalysis . "\n";

} catch (\Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}
```

### Using with Ollama (Free, Local)

This library works seamlessly with local models via Ollama. Simply change the configuration when initializing the service:

```php
// --- Configuration for a local Ollama server ---
$config = [
    'api_key' => 'ollama', // Not required, can be any string
    'endpoint' => 'http://localhost:11434/api/generate', // Use the /api/generate endpoint
    'model' => 'llava', // The name of your local multimodal model
];

$service = new ImageStylistService(
    apiKey: $config['api_key'],
    apiEndpoint: $config['endpoint'],
    model: $config['model']
);
```

---

## Jak Używać (Polish Guide)

Ta biblioteka została stworzona, aby pomagać osobom niewidomym i słabowidzącym w dokonywaniu świadomych wyborów modowych podczas zakupów online.

### Instalacja

Zainstaluj paczkę za pomocą Composer:

```bash
composer require dotxdd/image-stylist
```

### Przykład Użycia

Poniższy przykład pokazuje, jak uzyskać analizę produktu, używając API OpenAI.

```php
<?php

require 'vendor/autoload.php';

use Dotxdd\ImageStylist\ImageStylistService;
use Dotxdd\ImageStylist\StyleAnalysisResult;

// 1. Konfiguracja serwisu
$service = new ImageStylistService(
    apiKey: 'TWOJ_KLUCZ_API_OPENAI',
    apiEndpoint: 'https://api.openai.com/v1/chat/completions',
    model: 'gpt-4o'
);

// 2. Przygotowanie danych
$profilStyluUzytkownika = "Preferuję styl elegancki i biznesowy. Noszę głównie stonowane kolory, takie jak granat, szarość i biel.";
$adresyUrlZdjec = [ 'https://.../zdjecie-produktu.jpg' ];

// 3. Wywołanie analizy (w języku polskim)
$wynik = $service->getStyleAnalysis($adresyUrlZdjec, $profilStyluUzytkownika, 'pl');

// 4. Użycie wyników
echo "Rekomendacja: " . ($wynik->isStyleMatch ? 'Pasuje' : 'Nie pasuje') . "\n";
echo "Opis Obiektywny: " . $wynik->objectiveDescription . "\n";
echo "Sugestia Stroju: " . ($wynik->outfitSuggestion ?? 'Brak') . "\n";
```

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE.md) file for details.