<?php
/*
Plugin Name: MarkovWP
Description: Wrap any content in [markov] tags and 3 more paragraphs of text will be automatically generated for the post.
Version: 1
Author: Brad Parbs
Author URI: http://bradparbs.com.
*/

add_shortcode( 'markov', 'markovwp_shortcode' );
// markov generator settings
function markovwp_shortcode( $atts, $content = null ) {
    $length = 2000;
    $text = $content;
    $order = 4;

    $text = strip_tags($content, '<p>');
    $markov_table = markovwp_generate_markov_table($text, $order);
    $markov = markovwp_generate_markov_text($length, $markov_table, $order);
    if (get_magic_quotes_gpc()) $markov = stripslashes($markov);
    return  $content . $markov;
}

function markovwp_generate_markov_table($text, $look_forward) {
    $table = array();
    
    //walk through the text and make the index table
    for ($i = 0; $i < strlen($text); $i++) {
        $char = substr($text, $i, $look_forward);
        if (!isset($table[$char])) $table[$char] = array();
    }              
    
    // walk the array again and count the numbers
    for ($i = 0; $i < (strlen($text) - $look_forward); $i++) {
        $char_index = substr($text, $i, $look_forward);
        $char_count = substr($text, $i+$look_forward, $look_forward);
        
        if (isset($table[$char_index][$char_count])) {
            $table[$char_index][$char_count]++;
        } else {
            $table[$char_index][$char_count] = 1;
        }                
    } 

    return $table;
}

function markovwp_generate_markov_text($length, $table, $look_forward) {
    // get first character
    $char = array_rand($table);
    $o = $char;

    for ($i = 0; $i < ($length / $look_forward); $i++) {
        $newchar = markovwp_return_weighted_char($table[$char]);            
        
        if ($newchar) {
            $char = $newchar;
            $o .= $newchar;
        } else {       
            $char = array_rand($table);
        }
    }
    
    return $o;
}
    

function markovwp_return_weighted_char($array) {
    if (!$array) return false;
    
    $total = array_sum($array);
    $rand  = mt_rand(1, $total);
    foreach ($array as $item => $weight) {
        if ($rand <= $weight) return $item;
        $rand -= $weight;
    }
}