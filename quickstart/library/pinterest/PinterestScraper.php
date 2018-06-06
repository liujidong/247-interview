<?php
class PinterestScraper {
    
//input : /pin/277886239480319878/
//output : 277886239480319878
    private static function getPinIdFromPinUrl($pin_url) {
        $parts = explode('/', $pin_url);
        return isset($parts[2]) ? $parts[2] : '';
    }    
    
    private static function _getApiReturnedSource($url) {
        $headers = array(
            'X-Requested-With:XMLHttpRequest'
        );
        return http_method2($url, true, $method = 'GET', $cookie_file = '', $postfields = array(), $headers);
    }

    private static function _getPageHtml($url) {
        return http_method2($url);
    }

    private static function _getBoardsApiUrl($username) {
        $fields = array(
            "options" => array(),
            "module" => array(
                "name" => "UserBoards",
                "options" => array(
                    "username" => $username,
                    "secret_board_count" => 0
                ),
                "append" => false,
                "errorStrategy" => 0
            ),
            "context" => array(
                "app_version" => "6acf968"
            )
        );
        return $GLOBALS['pinterest_config']->api->resource->endpoint.'?data='.json_encode($fields);
    }
    
    private static function _getPinsApiUrl($board_id) {
        $fields = array(
            "options" => array(
                "board_id" => $board_id,
                "access" => array()
            ),
            "module" => array(
                "name" => "Grid",
                "options" => array(
                    "scrollable" => true,
                    "show_grid_footer" => true,
                    "item_options" => array(
                        "show_rich_title" => false,
                        "squish_giraffe_pins" => false,
                        "show_board" => false,
                        "show_via" => false,
                        "show_pinner" => false,
                        "show_pinned_from" => true
                    ),
                    "layout" => "variable_height",
                ),
                "append" => false,
                "errorStrategy" => 1
            ),
            "context" => array(
                "app_version" => "6acf968"
            )
        );
        return $GLOBALS['pinterest_config']->api->boardfeedresource->endpoint.'?data='.json_encode($fields);        
    }

    private static function _getPinInfoUrl($pin_id) {
        return substitute($GLOBALS['pinterest_config']->api->getPinInfo->endpoint, array('pin_id' => $pin_id));        
    }

    public static function parse_boards($boardsource) {
        
        global $pinterest_config;
        $get_borads_url = $pinterest_config->api->accountfeed->resource->endpoint;
        $next_page_url = '';
        $boards = array();

        if($boardsource['module']['tree']['resource']['name'] === 'ProfileBoardsResource') {
            $options = $boardsource['module']['tree']['resource']['options'];
            foreach($boardsource['module']['tree']['children'] as $i => $node) {
                $boards[$i]['id'] =  $node['data']['id'];
            }
        } else {
            $options = $boardsource['module']['tree']['children'][0]['resource']['options'];
            if(isset($boardsource['module']['tree']['children'][0]['children'][0]['children']) && is_array($boardsource['module']['tree']['children'][0]['children'][0]['children'])) {
                foreach($boardsource['module']['tree']['children'][0]['children'][0]['children'] as $i => $node) {
                   $boards[$i]['id'] =  $node['data']['id'];
                }               
            }
        }

        if(!empty($options['bookmarks']) && $options['bookmarks'][0] !== '-end-') {

            $data = array(
                    "options" => $options,
                    "module" => array(
                        "name" => "GridItems",
                        "options" => array(
                            "scrollable" => true,
                            "show_grid_footer" => false,
                            "centered" => true,
                            "reflow_all" => true,
                            "virtualize" => true,
                            "item_options" => array(
                                "show_board_context" => false
                            ),
                            "layout" => "fixed_height"
                        ),
                        "append" => true,
                        "errorStrategy" => 1
                    ,
                    "context" => array(
                        "app_version" => "b640966"
                    )
                )     
            );
            $next_page_url = $get_borads_url.'?data='.json_encode($data);
            
        }
        
        $html = $boardsource['module']['html'];
        try {
            $board_dom = new Zend_Dom_Query();              
            $dom = new Zend_Dom_Query($html);
            foreach($dom->query('.item') as $i => $board_item) {
                $board_dom->setDocumentHtml($board_item->C14N());
                //$boards[$i]['id'] = '';
                $board_url = '';
                foreach($board_dom->query('.boardLinkWrapper') as $board_link_wrapper) {
                    $board_url = $board_link_wrapper->getAttribute('href');
                    $boards[$i]['url'] = $board_url;
                }
                $boardname = get_innerHTML($board_dom->query('.boardName'));
                if(preg_match('#^/(.*)/(.*)/$#', $board_url, $matches)) {
                    $boards[$i]['name'] = $matches[2];
                }

                $boards[$i]['thumbnails'] = array();
                foreach($board_dom->query('.boardThumbs li img') as $img_elem) {
                    $boards[$i]['thumbnails'][] = $img_elem->getAttribute('src');
                }      
                $boards[$i]['stats']['pins_count'] = intval2(str_replace(' pins', '', get_innerHTML($board_dom->query('.boardPinCount'))));  

                foreach($board_dom->query('.boardCoverWrapper img') as $boardCover) {
                    $boards[$i]['cover'] = $boardCover->getAttribute('src');  
                }
                array_unshift($boards[$i]['thumbnails'], $boards[$i]['cover']);
            } 
        } catch (Exception $e){
            return array(
                'next_page_url' => $next_page_url,
                'boards' => $boards
            );
        }
        return array(
            'next_page_url' => $next_page_url,
            'boards' => $boards
        );
    }    
    
    private static function _getAccountPageUrl($username) {
        return substitute($GLOBALS['pinterest_config']->api->getboards->endpoint, array('pinterest_username' => $username));            
    }

    public static function parse_pinterest_account_page($html) {
        $user_info = array();
        $dom = new Zend_Dom_Query($html);
        $user_name = '';
        
        foreach($dom->query('link[rel="canonical"]') as $username_elem) {
            $account_url = $username_elem->getAttribute('href');  
            $username_boardname = getPinterestUsernameBoardnameFromUrl($account_url);
            $user_info['full_name'] = $username_boardname['username'];          
            $user_name = $user_info['full_name'];
        }   
        foreach($dom->query('.userProfileImage img') as $img_elem) {
            $user_info['image_large_url'] = $img_elem->getAttribute('src');  
        }   
        $local_elem = get_innerHTML($dom->query('.userProfileHeaderLocationWrapper'));    
        $name_parts = explode('>', $local_elem);
        $user_info['location'] = isset($name_parts[1])?trim($name_parts[1]):'';        
        foreach($dom->query('.websiteWrapper a') as $website_elem) {
            $user_info['website'] = $website_elem->getAttribute('href');  
        } 
        foreach($dom->query('.twitterWrapper a') as $twitter_elem) {
            $user_info['twitter_link'] = $twitter_elem->getAttribute('href');  
        }  
        foreach($dom->query('.facebookWrapper a') as $facebook_elem) {
            $user_info['facebook_link'] = $facebook_elem->getAttribute('href');  
        }
        $user_info['about'] = get_innerHTML($dom->query('.userProfileHeaderBio')); 
        $user_info['stats']['boards_count'] = parse_count_from_str(get_innerHTML($dom->query('.BoardCount span')));
        $user_info['stats']['pins_count'] = parse_count_from_str(get_innerHTML($dom->query(".userStats a[href='/$user_name/pins/']")));
        $user_info['stats']['likes_count'] = parse_count_from_str(get_innerHTML($dom->query("a[href='/$user_name/likes/']")));             
        $user_info['stats']['followers_count'] = parse_count_from_str(get_innerHTML($dom->query('.FollowerCount  span')));
        $user_info['stats']['following_count'] = parse_count_from_str(get_innerHTML($dom->query("a[href='/$user_name/following/']")));       
        return $user_info;
    }
    
//Array(
//    [name] => mooreaseal
//    [image_url] => http://media-cache-ec3.pinimg.com/avatars/mooreaseal-1360104749_140.jpg
//    [location] => Seattle, WA
//    [website] => http://www.moorea-seal.com/
//    [twitter] => http://twitter.com/mooreaseal
//    [facebook] => http://www.facebook.com/mooreaashleyseal
//    [about] => I believe in working hard and being kind  :)  I'm a jewelry designer &amp; own an online women's retail site, donating to Non-Profits with every sale.
//    [boards] => 106
//    [pins] => 20,672
//    [likes] => 50
//    [followers] => 857,868
//    [following] => 279
//)
    public static function getAccountInfo($username) {
        $user_info = array();
        $user_borads_url = PinterestScraper::_getAccountPageUrl($username);
        if($html = PinterestScraper::_getPageHtml($user_borads_url)) {
            $user_info = PinterestScraper::parse_pinterest_account_page($html);
        }
        return $user_info;
    }

//output:
//Array(
//  'borads' => Array (
//    [0] => Array (
//            [id] =>
//            [url] => /pintics/the-cuteness-overflow/
//            [name] => The Cuteness Overflow
//            [thumbnails] => Array
//                (
//                    [0] => http://media-cache-ec3.pinimg.com/216x146/75/48/18/754818731bda974f3f12225eea4eb88c.jpg
//                    [1] => http://media-cache-ak2.pinimg.com/45x45/9f/63/90/9f6390b22bb469232bffe4171261e48b.jpg
//                    [2] => http://media-cache-ec3.pinimg.com/45x45/57/80/50/5780500e22e01ecdfa9c97adc1082a1f.jpg
//                    [3] => http://media-cache-ak2.pinimg.com/45x45/b8/d9/13/b8d91363ef9300c1f063880748c55b27.jpg
//                    [4] => http://media-cache-ec3.pinimg.com/45x45/45/e7/e9/45e7e9763e90f7f23cc6e136a781a6c3.jpg
//                )
//
//            [stats] => Array
//                (
//                    [pins_count] => 39
//                )
//
//            [cover] => http://media-cache-ec3.pinimg.com/216x146/75/48/18/754818731bda974f3f12225eea4eb88c.jpg
//        )
//     [1] => Array ...   
//    )    
//    'next_page_url' =>  http://pinterest.com/resource/ProfileBoardsResource/get/?data .....
//)    
    public static function getBoards($username, $next_page_url = '') {
        
        $resource_url = empty($next_page_url)? PinterestScraper::_getBoardsApiUrl($username) : $next_page_url;
        $boards_resource = PinterestScraper::_getApiReturnedSource($resource_url);
        $response = PinterestScraper::parse_boards($boards_resource);

        return array(
            'next_page_url' => $response['next_page_url'],            
            'boards' => $response['boards']
        );
    }

    public static function parsePinsPage($pinsource) {

        global $pinterest_config;
        $get_pins_url = $pinterest_config->api->boardfeedresource->endpoint;
        $next_page_url = '';
        $pins = array();
        
        if($pinsource['module']['tree']['resource']['name'] === 'BoardFeedResource') {
            $options = $pinsource['module']['tree']['resource']['options'];
        } else {
            $options = $pinsource['module']['tree']['children'][0]['resource']['options'];  
        }

        if(!empty($options['bookmarks']) && $options['bookmarks'][0] !== '-end-') {
            $data = array(
                "options" => $options,
                "module" => array(
                    "name" => "GridItems",
                    "options" => array(
                        "scrollable" => true,
                        "show_grid_footer" => true,
                        "centered" => true,
                        "reflow_all" => true,
                        "virtualize" => true,
                        "item_options" => array(
                            "show_rich_title" => false,
                            "squish_giraffe_pins" => false,
                            "show_board" => false,
                            "show_via" => false,
                            "show_pinner" =>false,
                            "show_pinned_from" =>true
                        ),
                        "layout" => "variable_height"
                    ),
                    "append" => true,
                    "errorStrategy" => 1
                ),
                "context" => array( 
                    "app_version" => "b640966"
                )        
            );
            $next_page_url = $get_pins_url.'?data='.json_encode($data);
        }   

        $html = $pinsource['module']['html'];      
        try {   
            $dom = new Zend_Dom_Query($html);
            $pin_dom = new Zend_Dom_Query();   
            $pin_id = '';
            foreach($dom->query('.pinWrapper') as $i => $pin_item) {
                $pin_dom->setDocumentHtml($pin_item->C14N());
                foreach($pin_dom->query('.pinImageWrapper') as $pin_link_wrapper) {
                    $pins[$i]['url'] = $pin_link_wrapper->getAttribute('href');  
                    $pins[$i]['id'] = PinterestScraper::getPinIdFromPinUrl($pins[$i]['url']);
                    $pin_id = $pins[$i]['id'];
                }
                $pins[$i]['domain'] = get_innerHTML($pin_dom->query('.pinDomain'));
                foreach($pin_dom->query('.pinImg') as $pin_img_elem) {
                    $pins[$i]['image_url'] = $pin_img_elem->getAttribute('src'); 
                    $pins[$i] = array_merge($pins[$i], get_image_url_from_exist_image($pins[$i]['image_url']));
                }            
                $pins[$i]['description'] = strip_tags(get_innerHTML($pin_dom->query('.pinDescription')));         
                $pins[$i]['domain'] = get_innerHTML($pin_dom->query('.pinDomain'));
                $pins[$i]['counts']['likes'] = parse_count_from_str(get_innerHTML($dom->query("a[href='/pin/$pin_id/likes/']"))); 
                $pins[$i]['counts']['repins'] = parse_count_from_str(get_innerHTML($dom->query("a[href='/pin/$pin_id/repins/']")));                    
            }
        } catch (Exception $e){
            return array(
                'next_page_url' => $next_page_url,
                'pins' => $pins
            );            
        }    
        
        return array(
            'next_page_url' => $next_page_url,
            'pins' => $pins
        );
    }   
    
//output:
//Array(
//  'pins' => Array (
//    [0] => Array (
//        [url] => /pin/277252920783020793/
//        [id] => 277252920783020793
//        [domain] => redbookmag.com
//        [image_url] => http://media-cache-ak0.pinimg.com/236x/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [image_45] => http://media-cache-ak0.pinimg.com/45x45/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [image_70] => http://media-cache-ak0.pinimg.com/70x/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [image_192] => http://media-cache-ak0.pinimg.com/192x/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [image_236] => http://media-cache-ak0.pinimg.com/236x/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [image_550] => http://media-cache-ak0.pinimg.com/550x/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [image_736] => http://media-cache-ak0.pinimg.com/736x/89/e5/13/89e5130b4b3d5ae22869561e7db0356f.jpg
//        [description] => 22 Low-Alcohol Cocktails for All-Day Sipping
//        [counts] => Array
//            (
//                [likes] => 1
//                [repins] => 5
//            )
//
//        )
//     [1] ....  
//   )      
//   'next_page_url' => http://pinterest.com/resource/BoardFeedResource/get/?data  ......
//
//    
    public static function getPins($board_id, $next_page_url = '') {
        
        $pins_url = empty($next_page_url)? PinterestScraper::_getPinsApiUrl($board_id) : $next_page_url;      
        $pins_resource = PinterestScraper::_getApiReturnedSource($pins_url);
        $response = PinterestScraper::parsePinsPage($pins_resource);
        return array(
            'next_page_url' => $response['next_page_url'],
            'pins' => $response['pins']
        );       
    }

    public static function parsePinInfoPage($html) {
        $dom = new Zend_Dom_Query($html);
        $pin_info = array();
        $image_url = '';
        $pin_info['is_video'] = 0;
        $pin_info['is_slideshare'] = 0;
        $pin_info['is_image'] = 0;        
        $pin_info['repin'] = get_innerHTML($dom->query('.repinLike .primary.IncrementingNavigateButton .buttonText'));
        $pin_info['like'] = get_innerHTML($dom->query('.repinLike .IncrementingNavigateButton.like .buttonText'));
        foreach($dom->query('.repinLike .website') as $website_elem) {
            $pin_info['source'] = $website_elem->getAttribute('href');  
        }        
        $pin_info['domain'] = get_innerHTML($dom->query('.Domain .domainLinkWrapper .domainName'));
        $pin_info['description'] = get_innerHTML($dom->query('.commentDescriptionContent'));
        $time_sting= str_replace('•', '',get_innerHTML($dom->query('.pinDescription .commentDescriptionTimeAgo')));
        $pin_info['created_at'] = date('Y-m-d',strtotime2($time_sting));  
        foreach($dom->query('.imageContainer .pinImage') as $img_elem) {
            $pin_info['image_url'] = $img_elem->getAttribute('src'); 
            $pin_info['is_image'] = 1;     
            $image_url = $pin_info['image_url'];
        }
        foreach($dom->query('.vimeo') as $vimeo_elem) {
            $pin_info['is_video'] = 1;
        }   
        foreach($dom->query('.youtube') as $vimeo_elem) {
            $pin_info['is_video'] = 1;
        }           
        foreach($dom->query('.slideshare') as $slideshare_elem) {
            $pin_info['is_slideshare'] = 1;
        }           
        $pin_info = array_merge($pin_info, get_image_url_from_exist_image($image_url));
        return $pin_info;
    }
    
//output:
//    Array(
//    [repin] => 124
//    [like] => 33
//    [source] => http://www.harpersbazaar.com/fashion/fashion-articles/street-style-paris-couture-fall-2013-72
//    [domain] => harpersbazaar.com
//    [description] => Paris.
//    [created_at] => 2013-07-04
//    [image_url] => http://media-cache-ak3.pinimg.com/736x/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//    [is_video] => 0
//    [image_45] => http://media-cache-ak3.pinimg.com/45x45/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//    [image_70] => http://media-cache-ak3.pinimg.com/70x/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//    [image_192] => http://media-cache-ak3.pinimg.com/192x/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//    [image_236] => http://media-cache-ak3.pinimg.com/236x/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//    [image_550] => http://media-cache-ak3.pinimg.com/550x/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//    [image_736] => http://media-cache-ak3.pinimg.com/736x/85/ab/25/85ab25979315e2f5741c49756836c002.jpg
//  )
    public static function getPinInfo($pin_id) {
        $pin_info = array();
        $pin_info_url = PinterestScraper::_getPinInfoUrl($pin_id);
        if($html = PinterestScraper::_getPageHtml($pin_info_url)) {
            $pin_info = PinterestScraper::parsePinInfoPage($html);            
        } else {
            $pin_info = array('http_code' => 404);
        }
        return $pin_info;
    }
    
    private static function _getBoardPageUrl($board_id) {
        return substitute($GLOBALS['pinterest_config']->api->getPins->endpoint, array('board_id' => $board_id));   
    }
    
    public static function parseBoardPage($html) {

        $board_info = array();
        $user_info =array();
        $user_name = '';
        $board_name = '';
        $dom = new Zend_Dom_Query($html);
        
        foreach($dom->query('.thumbImageWrapper img') as $img_elem) {
            $user_info['avatar'] = $img_elem->getAttribute('src');  
        }        
        $user_info['fullname'] = get_innerHTML($dom->query('.fullname'));  
        foreach($dom->query('meta[property="og:url"]') as $username_elem) {
            $account_url = $username_elem->getAttribute('content');  
            $username_boardname = getPinterestUsernameBoardnameFromUrl($account_url);
            $user_info['username'] = $username_boardname['username'];       
            $user_name = $user_info['username'];
            $board_info['name'] = $username_boardname['boardname'];
            $board_name = $board_info['name'];
        } 
        $board_info['description'] = get_innerHTML($dom->query('.description')); 
        $board_info['pins'] = parse_count_from_str(get_innerHTML($dom->query("a[href='/$user_name/$board_name/pins/']")));
        $board_info['followers'] = parse_count_from_str(get_innerHTML($dom->query("a[href='/$user_name/$board_name/followers/']")));        

        return array(
            'user' => $user_info,
            'board' => $board_info            
        );
    }
//Array(
//    [user] => Array
//        (
//            [avater_image_url] => http://media-cache-ec1.pinimg.com/avatars/mooreaseal-1360104749_30.jpg
//            [fullname] => Moorea Seal
//            [username] => mooreaseal
//        )
//
//    [board] => Array
//        (
//            [name] => clothing
//            [description] => pretty clothes for pretty ladies.
//            [pins] => 3,130
//            [followers] => 769,686
//        )
//
//)
    public static function getBoardInfo($board_id) {
        $board_info = array();
        $pins_url = PinterestScraper::_getBoardPageUrl($board_id);
        if($html = PinterestScraper::_getPageHtml($pins_url)) {
            $board_info = PinterestScraper::parseBoardPage($html);            
        }
        return $board_info;        
    }
}
