<?php

namespace App\Libraries;

class FaceRecognition
{
    /**
     * Calculates the Cosine Similarity between two numeric arrays.
     * 
     * @param array $vector1 The first embedding array.
     * @param array $vector2 The second embedding array.
     * @return float The cosine similarity score (between -1 and 1).
     */
    public static function cosineSimilarity(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new \InvalidArgumentException("Vectors must be of the same length.");
        }

        $dotProduct = 0.0;
        $magnitude1 = 0.0;
        $magnitude2 = 0.0;

        foreach ($vector1 as $i => $val1) {
            $val2 = $vector2[$i];
            
            $dotProduct += $val1 * $val2;
            $magnitude1 += $val1 * $val1;
            $magnitude2 += $val2 * $val2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0; // Prevent division by zero
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}
