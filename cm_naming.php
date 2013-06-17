<?php
/*
Plugin Name: cM Naming - Title Sorting
Description: Modify queries ( wp_query ) to ignore English prefixes (a, an, the) in sorting.  Only applys to sorting by title with native wordpress functions.
Version: 0.0.1
Author: Chase C. Miller
Author Email: chasecmiller@gmail.com
*/
if (!defined('ABSPATH')) { return; }

class cm_naming {
	private $bParse = false;
	function __construct() { 
//		add_action( 'pre_get_posts', array(&$this, 'handlePreQuery'));
//		add_action('posts_selection', array(&$this, 'test'));
		add_filter( 'posts_where', array(&$this, 'handlePostsWhere'), 10, 2);
		add_filter('posts_orderby', array(&$this, 'handlePostsOrderBy'));
	}
	public function handlePostsOrderBy($sOrder = null) {
		global $wpdb;
		if (!$this->bParse) { return $sOrder; }
		$sOrderBy = stripos($sOrder, ' asc') ? ' asc' : ' desc';
		
		$sField = $wpdb->posts.'.post_title';
//		return $sOrder;
		$sOrder = 'IF(LEFT('.$sField.',2) = "A ",
			SUBSTRING('.$sField.' FROM 3),
			IF(LEFT('.$sField.',3) = "An ",
				SUBSTRING('.$sField.' FROM 4),
			IF(LEFT('.$sField.',4) = "The ",
				SUBSTRING('.$sField.' FROM 5),
			'.$sField.')))'.$sOrderBy;
//				 echo $sOrder;
		return $sOrder;
		
	}
	function handlePostsWhere( $where, &$oQuery) {
		global $wpdb;
		if (!is_object($oQuery) || !isset($oQuery->query) || !is_array($oQuery->query) || !isset($oQuery->query['orderby']) || ($oQuery->query['orderby'] != 'title')) {
			return $where;
		}
		$this->bParse = true;
		return $where;
	}
}
new cm_naming();

?>
