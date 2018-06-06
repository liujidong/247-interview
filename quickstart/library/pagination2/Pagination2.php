<?php

class Pagination2 {

    protected $pagination = null;
    private $link = null;
    private $extra_params = array();

    public function __construct() {
        if(!$this->pagination)
            $this->pagination = new pagination();
        $url_parse_array = parse_url($_SERVER['REQUEST_URI']); 
        $this->link =  $url_parse_array['path'];      
    }

    public function pagination($total_rows, $rows_per_page, $page_num, $extra_params = array()) {

        if($total_rows < 1 || $rows_per_page < 1 || $page_num < 1) {
            return '';
        }
        
        $this->extra_params = $extra_params;       
        
        $page_array = $this->pagination->calculate_pages($total_rows, $rows_per_page, $page_num);
        $current = $page_array['current'];
        $previous = $page_array['previous'];
        $next = $page_array['next'];
        $last = $page_array['last'];
        $pages = $page_array['pages'];
        $first_page = $pages[0];
        $last_page = end($pages);
        
        if($first_page === $last_page) {
            return '';
        }
        
        $html = '<ul class="pagination" style="display:inline-block;">';

        if($previous === 1 && $current === 1) {
            $html .= "<li class='arrow unavailable'><a>Prev</a></li>";            
        } else {
            $html .= "<li class='arrow'><a href=".$this->get_page_link($previous).">Prev</a></li>";
        }

        if(!in_array(1, $pages)) {
            if ($current == 1) {
                $html .= "<li class='current'><a href=".$this->get_page_link(1).">1</a></li>";                
            } else {
                $html .= "<li><a href=".$this->get_page_link(1).">1</a></li>";                
            }
            if (2 < $first_page) {
                $html .= "<li class='unavailable'><a href=''>&hellip;</a></li>";                
            }
        }
        
        foreach ($pages as $page) {
            
            if ($current == $page) {
                $html .= "<li class='current'><a href=".$this->get_page_link($page).">$page</a></li>";                
            } else {
                $html .= "<li><a href=".$this->get_page_link($page).">$page</a></li>";                
            }
        }
 
        if($last > $last_page +1) {
            $html .= "<li class='unavailable'><a href=''>&hellip;</a></li>";
        }
        
        if(!in_array($last, $pages)) {
            if ($current == $last) {
                $html .= "<li class='current'><a href=".$this->get_page_link($last).">$last</a></li>";                
            } else {
                $html .= "<li><a href=".$this->get_page_link($last).">$last</a></li>";                
            }            
        }

        if($next == $last_page) {
            $html .= "<li class='arrow unavailable'><a>Next</a></li>";            
        } else {
            $html .= "<li class='arrow'><a href=".$this->get_page_link($next).">Next</a></li>";
        }
        
        $html .= '</ul>';

        return $html;        
    }    
    
    private function get_page_link($page_num) {
        $query = array_merge($this->extra_params, array('page'=>$page_num));
        $query_str = http_build_query($query);
        $query_str = $this->link.'?'.$query_str;     
        return $query_str;
    }
    
    
}
