<?php
/*
Plugin Name: cM Naming - Title Sorting
Description: Modify queries ( wp_query ) to ignore English prefixes (a, an, the) in sorting.  Only applys to sorting by title with native wordpress functions.
Version: 0.0.2
Author: Chase C. Miller
Author Email: chasecmiller@gmail.com
*/
if (!defined('ABSPATH')) { return; }

class cm_naming {
	private $aSettings = array(
		'prefixes' => array(
			'A',
			'An',
			'The'
		)
	);
	function __construct() { 
		add_filter('posts_where', array(&$this, 'handlePostsWhere'), 10, 2);
		add_filter('posts_orderby', array(&$this, 'handlePostsOrderBy'));
	}
	public function handlePostsOrderBy($sOrder = null) {
		global $wpdb;
		if (!isset($this->aSettings['bParse']) || !$this->aSettings['bParse']) { return $sOrder; }
		if (!sizeof($this->aSettings['prefixes'])) { return $sOrder; }
		// Remove in following releases if supporting multiple queries.
		// Idea is to cache our query string.
		if (isset($this->aSettings['order'])) { return $this->aSettings['order']; }
		$sOrderBy = stripos($sOrder, ' asc') ? ' asc' : ' desc';
		$sField = $wpdb->posts.'.post_title';
		$sModifid = '';
		foreach($this->aSettings['prefixes'] as $sPrefix) {
			$i = strlen($sPrefix)+1;
			$sModified .= 'IF(LEFT('.$sField.','.$i.') = "'.$sPrefix.' ",
				SUBSTRING('.$sField.' FROM '.($i+1).'), ';
		}
		if (strlen($sModified) > 0) {
			$sModified .= $sField.')))'.$sOrderBy;
			$this->aSettings['order'] = $sModified;
			return $sModified;	
		}
	}
	function handlePostsWhere( $where, &$oQuery) {
		global $wpdb;
		if (!is_object($oQuery) || !isset($oQuery->query) || !is_array($oQuery->query) || !isset($oQuery->query['orderby']) || ($oQuery->query['orderby'] != 'title')) {
			return $where;
		}
		$this->aSettings['bParse'] = true;
		return $where;
	}
}
new cm_naming();

?>
