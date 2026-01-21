<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Eazly_Lorem_Ipsum_Generator {
	private static $words = array(
		'lorem',
		'ipsum',
		'dolor',
		'sit',
		'amet',
		'consectetur',
		'adipiscing',
		'elit',
		'sed',
		'do',
		'eiusmod',
		'tempor',
		'incididunt',
		'ut',
		'labore',
		'et',
		'dolore',
		'magna',
		'aliqua',
		'enim',
		'ad',
		'minim',
		'veniam',
		'quis',
		'nostrud',
		'exercitation',
		'ullamco',
		'laboris',
		'nisi',
		'ut',
		'aliquip',
		'ex',
		'ea',
		'commodo',
		'consequat',
		'duis',
		'aute',
		'irure',
		'dolor',
		'in',
		'reprehenderit',
		'in',
		'voluptate',
		'velit',
		'esse',
		'cillum',
		'dolore',
		'eu',
		'fugiat',
		'nulla',
		'pariatur',
		'excepteur',
		'sint',
		'occaecat',
		'cupidatat',
		'non',
		'proident',
		'sunt',
		'in',
		'culpa',
		'qui',
		'officia',
		'deserunt',
		'mollit',
		'anim',
		'id',
		'est',
		'laborum',
	);

	private static $sentence_enders    = array( '.', '?', '!' );
	private static $sentence_modifiers = array( ',', ';', ':' );
	private static $quote_pairs        = array(
		array( '"', '"' ),
		array( "'", "'" ),
		array( '(', ')' ),
		array( '[', ']' ),
	);

	public static function generate_paragraphs( $count = 1 ) {
		$paragraphs = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$paragraphs[] = self::generate_paragraph();
		}
		return implode( "\n\n", $paragraphs );
	}

	private static function generate_paragraph() {
		$sentence_count = wp_rand( 3, 8 );
		$sentences      = array();

		for ( $i = 0; $i < $sentence_count; $i++ ) {
			$sentences[] = self::generate_sentence();
		}

		return implode( ' ', $sentences );
	}

	private static function generate_sentence() {
		$word_count           = wp_rand( 5, 20 );
		$sentence             = array();
		$last_was_punctuation = false;

		for ( $i = 0; $i < $word_count; $i++ ) {
			// Add word
			$word = self::get_random_word();

			// Occasionally add punctuation (but not if last was punctuation)
			if ( ! $last_was_punctuation && wp_rand( 1, 5 ) === 1 ) {
				if ( $i < $word_count - 1 ) {
					// Mid-sentence punctuation
					$word                .= self::get_random_mid_sentence_punctuation();
					$last_was_punctuation = true;
				}
			} else {
				$last_was_punctuation = false;
			}

			$sentence[] = $word;
		}

		// Capitalize first letter
		$sentence[0] = ucfirst( $sentence[0] );

		// Add ending punctuation
		$sentence[ count( $sentence ) - 1 ] .= self::get_random_sentence_ender();

		// Occasionally wrap in quotes
		if ( wp_rand( 1, 4 ) === 1 ) {
			$quotes                              = self::$quote_pairs[ array_rand( self::$quote_pairs ) ];
			$sentence[0]                         = $quotes[0] . $sentence[0];
			$sentence[ count( $sentence ) - 1 ] .= $quotes[1];
		}

		return implode( ' ', $sentence );
	}

	private static function get_random_word() {
		return self::$words[ array_rand( self::$words ) ];
	}

	private static function get_random_sentence_ender() {
		return self::$sentence_enders[ array_rand( self::$sentence_enders ) ];
	}

	private static function get_random_mid_sentence_punctuation() {
		$punctuation = self::$sentence_modifiers[ array_rand( self::$sentence_modifiers ) ];
		// 50% chance to add space after
		return wp_rand( 0, 1 ) ? $punctuation : $punctuation . ' ';
	}
}
