<?php

class KeywordService {
    private static $keywords = [
        'quote' => 'quote request',
        'status' => 'status update', 
        'budget' => 'budget inquiry',
        'urgent' => 'urgent request',
        'timeline' => 'timeline inquiry',
        'estimate' => 'estimate request'
    ];
    
    public static function detect($message) {
        $found = [];
        $lowerMessage = strtolower(trim($message));
        
        foreach (self::$keywords as $keyword => $description) {
            if (strpos($lowerMessage, $keyword) !== false) {
                $found[] = $keyword;
            }
        }
        
        return $found;
    }
    
}
