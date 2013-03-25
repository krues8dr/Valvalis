<?php

/*
	class Pager
	
	Created by: 
	Bill Hunt (bill@krues8dr.com)
		
	Purpose: 
	Allows paging of items
	
	Change Log:
	08.20.05 - Created object.
*/
	
	class Pager {
		var $page = 1;
		var $item_count;
		var $item_limit = 25;
		
		// function Pager
		// object constructor
		function Pager($args = array()) {
			
			if(strlen($args['page'])) {
				$this->page = $args['page'];
			}
			
			if(strlen($args['item_count'])) {
				$this->item_count = $args['item_count'];
			}

			if(strlen($args['item_limit'])) {
				$this->item_limit = $args['item_limit'];
			}

		}
		
		// function nextPage
		// increments the current page.
		function nextPage() {
			return $this->setPage($this->page + 1);
		}
		
		// function prevPage
		// decrements the current page.
		function prevPage() {
			return $this->setPage($this->page - 1);
		}
		
		// function setPage
		// sets the current page to a given number.
		function setPage($page) {
			if(strlen($page) && (($page-1) * $this->item_limit < $this->item_count) && ($page >= 1)) {
				$this->page = $page;
				
				$return_val = $this->page;
			}
			else {
				$return_val = false;
			}
			
			return $return_val;
		}
		
		// function getPage
		// returns the current page.
		function getPage() {
			return $this->page;
		}
		
		// function setItemCount
		// sets the current item count
		function setItemCount($item_count) {
			$this->item_count = $item_count;
		}
		
		// function getItemCount
		// returns the current item count
		function getItemCount() {
			return $this->item_count;
		}
		
		function setItemLimit($item_limit) {
			$this->item_limit = $item_limit;
		}
		
		function getItemLimit() {
			return $this->item_limit;
		}
		
		function getLimit() {
			$low_val = ($this->page - 1) * $this->item_limit;
			$high_val = $this->page * $this->item_limit;
			$return_values = array(
				'begin' => $low_val, 
				'end' => $high_val, 
				'count' => $this->item_limit
			);

			return $return_values;
		}
		
		function getLastPage() {
			$last_page = ceil($this->getItemCount() / $this->getItemLimit());
			return $last_page;
		}
		
		function getPagingValues() {
			$current_page = $this->getPage();
			$next_page = $this->nextPage();
			
			if($next_page) {
				$this->prevPage();
			}
			
			$prev_page = $this->prevPage();
			
			$this->setPage($current_page);
			
			$return_values = array(
				'first' => 1,
				'prev' => $prev_page,
				'next' => $next_page,
				'last' => $this->getLastPage()
			);
			
			return $return_values;
		}
		
	}
	
	
?>