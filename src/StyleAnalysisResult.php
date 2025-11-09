<?php

namespace Dotxdd\ImageStylist;

/**
 * A read-only Data Transfer Object (DTO) to hold the structured
 * results from the AI style analysis.
 */
class StyleAnalysisResult
{
    /**
     * @param string $objectiveDescription A neutral, factual description of the product.
     * @param string $styleAnalysis A personalized analysis comparing the item to the user's style.
     * @param bool $isStyleMatch A recommendation of whether the item is a match (true) or not (false).
     * @param string|null $outfitSuggestion An outfit suggestion if the item is a style match.
     * @param string $occasionAnalysis An analysis of what occasions the item is suitable for.
     */
    public function __construct(
        public readonly string $objectiveDescription,
        public readonly string $styleAnalysis,
        public readonly bool $isStyleMatch,
        public readonly ?string $outfitSuggestion,
        public readonly string $occasionAnalysis
    ) {}
}