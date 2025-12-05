<?php

/**
 * Math Captcha Helper - Simple anti-spam protection
 * 
 * Generates a simple math question (e.g., "3 + 5 = ?")
 * and stores the answer in session for validation.
 */

if (! function_exists('captcha_generate')) {
    /**
     * Generate a new math captcha question
     * 
     * @return array ['question' => '3 + 5', 'answer' => 8]
     */
    function captcha_generate(): array
    {
        $operators = ['+', '-'];
        $operator = $operators[array_rand($operators)];
        
        // Generate numbers based on operator
        if ($operator === '+') {
            $num1 = random_int(1, 10);
            $num2 = random_int(1, 10);
            $answer = $num1 + $num2;
        } else {
            // For subtraction, ensure positive result
            $num1 = random_int(5, 15);
            $num2 = random_int(1, $num1 - 1);
            $answer = $num1 - $num2;
        }
        
        $question = "{$num1} {$operator} {$num2}";
        
        // Store answer in session
        session()->set('captcha_answer', $answer);
        session()->set('captcha_time', time());
        
        return [
            'question' => $question,
            'answer'   => $answer,
        ];
    }
}

if (! function_exists('captcha_verify')) {
    /**
     * Verify the captcha answer
     * 
     * @param mixed $userAnswer The answer submitted by user
     * @param int $maxAge Maximum age in seconds (default 10 minutes)
     * @return bool True if valid, false otherwise
     */
    function captcha_verify($userAnswer, int $maxAge = 600): bool
    {
        $session = session();
        
        $storedAnswer = $session->get('captcha_answer');
        $captchaTime = $session->get('captcha_time');
        
        // Check if captcha exists
        if ($storedAnswer === null || $captchaTime === null) {
            return false;
        }
        
        // Check if captcha expired
        if ((time() - $captchaTime) > $maxAge) {
            captcha_clear();
            return false;
        }
        
        // Verify answer (allow string or int)
        $isValid = (int) $userAnswer === (int) $storedAnswer;
        
        // Clear captcha after verification attempt
        captcha_clear();
        
        return $isValid;
    }
}

if (! function_exists('captcha_clear')) {
    /**
     * Clear the captcha from session
     */
    function captcha_clear(): void
    {
        $session = session();
        $session->remove('captcha_answer');
        $session->remove('captcha_time');
    }
}

if (! function_exists('captcha_get_question')) {
    /**
     * Get current captcha question without regenerating
     * Returns null if no captcha exists
     * 
     * @return string|null
     */
    function captcha_get_question(): ?string
    {
        // We don't store the question, just regenerate for display
        // The answer is already in session
        if (session()->get('captcha_answer') !== null) {
            return null; // Should regenerate instead
        }
        return null;
    }
}
