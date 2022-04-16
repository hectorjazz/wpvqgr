<?php

class WPVQGR_Snippets
{
    /**
     * Slugify a $text
     * @param  [type] $text [description]
     * @return [type]       [description]
     */
    public static function slugify($text, $noTiret=false)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        if ($noTiret) {
            $text = str_replace('-', '', $text);
        }

        return $text;
    }

    /**
     * Get information about the picture by using it ID
     * @param  [type] $attachment_id [description]
     * @return [type]                [description]
     */
    public static function wpGetAttachment( $attachment_id ) 
    {
        $attachment = get_post( $attachment_id );

        if (!is_object($attachment)) {
            return NULL;
        }

        return array(
            'alt'           =>  get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
            'caption'       =>  $attachment->post_excerpt,
            'description'   =>  $attachment->post_content,
            'href'          =>  get_permalink( $attachment->ID ),
            'src'           =>  $attachment->guid,
            'title'         =>  $attachment->post_title
        );
    }

    public static function shuffle_with_seed(&$items, $seed)
    {
        @mt_srand($seed);
        for ($i = count($items) - 1; $i > 0; $i--)
        {
            $j = @mt_rand(0, $i);
            $tmp = $items[$i];
            $items[$i] = $items[$j];
            $items[$j] = $tmp;
        }
    }

    /**
     * Return information about how to organize answer's columns
     * [
     *     displayPicture   =>  true|false
     *     size             =>  1..12
     * ]
     * @param  [type] $question_data [description]
     * @return [type]                [description]
     */
    public static function getSmartColumnsSize($question_data, $forceColumsCount = NULL)
    {        
        // Get answers
        $answers = isset($question_data['wpvqgr_quiz_questions_answers']) ? $question_data['wpvqgr_quiz_questions_answers'] : [];

        // Display picture or not? (aka : if there is at least 1 picture, ie one ID > 0)
        $_sumPictureIds = intval(array_sum(array_map(function($item) {  return $item['wpvqgr_quiz_questions_answers_picture']; }, $answers)));
        $displayPicture = ($_sumPictureIds != 0);

        // Smart guessing
        if ($forceColumsCount == NULL)
        {
            // No picture = no columns
            if ($_sumPictureIds == 0) {
                $mdColumnSize = 12;
                $xsColumnSize = 12;
            } 
            // Picture (%4, %3, %2)
            else {
                $mdColumnSize = (count($answers)%2 == 0) ? 6 : 4;
                $xsColumnSize = (count($answers)%3 == 0) ? 12 : 6;
            }
        }
        // Forced
        else
        {
            // Be sure about $forceColumnsCount
            $forceColumsCount = ($forceColumsCount > 4 || $forceColumsCount <= 0) ? 2 : $forceColumsCount;
            // Force bootstrap col-*
            $mdColumnSize = 12 / intval($forceColumsCount);
            $xsColumnSize = ($mdColumnSize == 3) ? 12 : 6;
        }

        return array(
            'displayPicture'    => $displayPicture,
            'md-size'           => $mdColumnSize,
            'xs-size'           => $xsColumnSize,
        );
    }

    /**
     * Is Facebook ?
     */
    public static function isFacebookBot()
    {  
        return (strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit") !== false || 
            strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") !== false);
    }  

    /**
     * Is VK ?
     */
    public static function isVKBot()
    {  
        return (stripos($_SERVER["HTTP_USER_AGENT"], "vkShare") !== false);
    }  
}