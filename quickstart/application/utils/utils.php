<?php
require_once("creditcard_util.php");

function dd($arr) {
    if(php_sapi_name() === 'cli') {
        print_r($arr);
        echo "\n";
    } else {
        print("<pre>".print_r($arr,true)."</pre>");
    }
}

function ddd() {
    $args = func_get_args();
    foreach($args as $arg) {
        dd($arg);
    }
}

function ddc($arr) {
    if(php_sapi_name() === 'cli') {
        print_r($arr);
        echo "\n";
    } else {
        $content = str_replace("'", "\\'", print_r($arr, TRUE));
        $content = str_replace("\n", "\\n", $content);
        $content = str_replace("\r", "", $content);
        print_r('<script type="text/javascript">');
        print_r("console.log('DEBUG:');\n");
        print_r("console.log('{$content}');\n");
        print_r('</script>');
    }
}

function dddd() {
    $args = func_get_args();
    foreach($args as $arg) {
        dd($arg);
    }
    die();
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function getStrIn($first, $last, $str) {
    $i = strpos($str, $first);
    $j = strpos($str, $last);
    return substr($str, $i+1, $j-$i-1);
}

function to_plural($str) {
    $plural = $str;
    $last_char = substr($str, -1);
    if($last_char === 's') {
        $plural = $str.'es';
    } else if($last_char === 'y') {
        $plural = substr($str, 0, strlen($str)-1).'ies';
    } else {
        $plural = $str.'s';
    }
    return $plural;
}

function from_camel_case($str) {
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    return preg_replace_callback('/([A-Z])/', $func, $str);
}

function to_camel_case($str, $capitalise_first_char = false) {
    if($capitalise_first_char) {
        $str[0] = strtoupper($str[0]);
    }
    $func = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z0-9])/', $func, $str);
}

function escape_array_values($array, $dbobj) {
    foreach($array as $key=>$val) {
        if($key !== 'created') {
            $array[$key] = "'".$dbobj->escape($val)."'";
        }
    }
    return $array;
}

function get_equ_str($array) {

    unset($array['id']);
    unset($array['created']);
    if(empty($array)) {
        return '';
    }
    
    $result_array = array();    
    foreach($array as $key=>$val) {
        $result_array[] = $key.'='.$val;
    }
    
    return ', ' . join(', ', $result_array);
}

function getSaveSql($fields, $table, $dbobj) {

    $fields['created'] = 'now()'; 
    
    $fields = escape_array_values($fields, $dbobj);
    $keys = array_keys($fields);
    $keys_joined = join(', ', $keys);
    $values = array_values($fields);
    $values_joined = join(', ', $values);
    $equ_str = get_equ_str($fields);

    $sql = "insert into $table($keys_joined) values($values_joined) on duplicate key update id= LAST_INSERT_ID(id) $equ_str";
    return $sql;
}

function validate($data, $type) {
    $result = false;
    if($type === 'email') {
        if(check_email($data)) {
            $result = true;
        }
    } else if($type === 'password') {
        if(check_password($data)) {
            $result = true;
        }
    } else if($type === 'url') {
        return check_url($data);
    } else if($type ===  'pinterest_username') {
        return check_pinterest_username($data);
    } else if($type === 'ga_account') {
        if(check_ga_account($data['email'], $data['password'])) {
            return true;
        }
        return false;
    } else if($type === 'pinterest_api_results_accounts_boards') {
        $result = check_pinterest_api_results_accounts_boards($data);
    } else if($type === 'pinterest_api_results_pins') {
        $result = check_pinterest_api_results_pins($data);
    } else if($type === 'pinterest_account_page') {
        $result = check_pinterest_account_page($data);
    } else if($type === 'pinterest_board_page') {
        $result = check_pinterest_board_page($data);
    } else if($type === 'subdomain') {
        $result = check_subdomain($data);
    } else if ($type === 'date') {
        $result = check_date($data);
    } else if($type === 'pinterest_image_url') {
        $result = is_pinterest_image_url($data);
    }


    return $result;
}

function check_date($date) {
    if(empty($date)) {
        return true;
    }
    if(!empty2(preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $date))){
        $endTime = strtotime2($date);
        $startTime = strtotime2(get_date());
        if($startTime > $endTime) {
            return false;
        }
        return (round(($endTime-$startTime)/3600/24) <= 90);
    }
    return false;
}

function check_subdomain($subdomain) {
    global $forbidden_subdomain;

    $subdomain = trim($subdomain);
    if(empty($subdomain)) {
        return false;
    }

    $pattern = '/^('.join('|', $forbidden_subdomain).')[0-9]{0,2}$/';
    $return = preg_match($pattern, $subdomain);

    if(empty($return)) {
        return true;
    } else {
        return false;
    }
}

function check_pinterest_account_page($html) {

    $dom = new Zend_Dom_Query($html);
    try{
        $name = get_innerHTML($dom->query('#ProfileHeader div div.content h1'));
    } catch(Exception $e) {
        return false;
    }

    if(empty($name)) {
        return false;
    } else {
        return true;
    }
}

function check_pinterest_board_page($html) {
    $dom = new Zend_Dom_Query($html);
    try {
        $name = get_innerHTML($dom->query('#BoardTitle h1 strong'));
    } catch(Exception $e) {
        return false;
    }
    if(empty($name)) {
        return false;
    } else {
        return true;
    }
}

function check_pinterest_api_results_accounts_boards($results) {
    if(!is_array($results)) {
        return false;
    }
    if(!isset($results['status']) || $results['status'] !== 'success') {
        return false;
    }
    if(!isset($results['user']['username'])) {
        return false;
    }
    if(!is_array($results['boards'])) {
        return false;
    }
    return true;
}

function check_pinterest_api_results_pins($results) {
    if(!is_array($results)) {
        echo "results is not array\n";
        return false;
    }
    if(!isset($results['status']) || $results['status'] !== 'success') {
        echo "status is not success\n";
        return false;
    }
    if(!isset($results['user']['username'])) {
        echo "username doesnt exist\n";
        return false;
    }
    if(!is_array($results['pins'])) {
        echo "pins is not array\n";
        return false;
    }
    return true;
}

function check_email($email)
{
    if(preg_match("/^([a-zA-Z0-9\._\+])+([a-zA-Z0-9\._-])@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",$email))
    {
        list($username,$domain)=split('@',$email);
        global $temporary_email_address_domains;
        if(APPLICATION_ENV == 'production' && in_array($domain, $temporary_email_address_domains)){
            return false;
        }
        if(!checkdnsrr($domain,'MX'))
            return false;
        return true;
    }
    return false;
}

function check_password($password) {
    $password = trim($password);
    if(strlen($password)>=6 && strlen($password)<=20) {
        return true;
    }
    return false;
}

function check_url($url) {
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function check_pinterest_username($pinterest_username) {

    $metrics = get_pinterest_account_metrics($pinterest_username);
    if(empty($metrics['boards']) && empty($metrics['pins']) && empty($metrics['likes']) && empty($metrics['followers']) && empty($metrics['following'])) {
        return false;
    } else {
        return true;
    }
}

function get_pinterest_account_metrics($username) {

    $base_url = "http://pinterest.com/$username/";
    $metrics = array(
        'boards' => 0,
        'pins' => 0,
        'likes' => 0,
        'followers' => 0,
        'following' => 0
    );
    $is_new_ui = false;

    $response = http_method($base_url);
    if($response['http_code'] !== 200) {
        return $metrics;
    }
    $html = $response['html'];
    $dom = new Zend_Dom_Query($html);
    foreach($dom->query('meta[property="og:url"]') as $url_elem) {
        $account_url = $url_elem->getAttribute('content');
        $username_boardname = getPinterestUsernameBoardnameFromUrl($account_url);
        $username = $username_boardname['username'];
    }
    if(!empty2(get_innerHTML($dom->query('.userStats')))) {
        $is_new_ui = true;
    }

    if($is_new_ui) {
        get_pinterest_account_metrics_newui($dom, $username, $metrics);
    } else {
        get_pinterest_account_metrics_oldui($dom, $username, $metrics);
    }
    return $metrics;
}

function get_pinterest_account_metrics_newui($dom, $username, &$metrics) {
    parse_account_metrics_from_newui($dom, $username, $metrics);
    if(empty($metrics['boards']) && empty($metrics['pins']) && empty($metrics['likes']) && empty($metrics['followers'])
            && empty($metrics['following'])) {
        $username = ucfirst($username);
        parse_account_metrics_from_newui($dom, $username, $metrics);
    }
}

function parse_account_metrics_from_newui($dom, $username, &$metrics) {
    $boards_elems = $dom->query(".userStats a[href='/$username/boards/'] span");
    $metrics['boards'] = parse_count_from_str(get_innerHTML($boards_elems));
    $pins_elems = $dom->query(".userStats a[href='/$username/pins/']");
    $metrics['pins'] = parse_count_from_str(get_innerHTML($pins_elems));
    $likes_elems = $dom->query(".userStats a[href='/$username/likes/']");
    $metrics['likes'] = parse_count_from_str(get_innerHTML($likes_elems));
    $followers_elems = $dom->query(".followersFollowingLinks a[href='/$username/followers/'] span");
    $metrics['followers'] = parse_count_from_str(get_innerHTML($followers_elems));
    $following_elems = $dom->query(".followersFollowingLinks a[href='/$username/following/']");
    $metrics['following'] = parse_count_from_str(get_innerHTML($following_elems));
}

function get_pinterest_account_metrics_oldui($dom, $username, &$metrics) {
    parse_account_metrics_from_oldui($dom, $username, $metrics);
    if(empty($metrics['boards']) && empty($metrics['pins']) && empty($metrics['likes']) && empty($metrics['followers'])
            && empty($metrics['following'])) {
        $username = ucfirst($username);
        parse_account_metrics_from_oldui($dom, $username, $metrics);
    }
}

function parse_account_metrics_from_oldui($dom, $username, &$metrics) {
    $boards_elems = $dom->query("#ContextBar ul li a[href='/$username/'] strong");
    $metrics['boards'] = get_innerHTML($boards_elems);
    $pins_elems = $dom->query("#ContextBar ul li a[href='/$username/pins/'] strong");
    $metrics['pins'] = get_innerHTML($pins_elems);
    $likes_elems = $dom->query("#ContextBar ul li a[href='/$username/pins/?filter=likes'] strong");
    $metrics['likes'] = get_innerHTML($likes_elems);
    $followers_elems = $dom->query("#ContextBar ul li a[href='/$username/followers/'] strong");
    $metrics['followers'] = get_innerHTML($followers_elems);
    $following_elems = $dom->query("#ContextBar ul li a[href='/$username/following/'] strong");
    $metrics['following'] = get_innerHTML($following_elems);
}

function parse_count_from_str($str) {
    $count = preg_replace("/(<[^<>]*?>|\D)/", '', $str);
    return is_numeric($count) ? $count : 0;
}

function http_request2($base_url, $params=array(),$cookiefile=NULL) {
    $ch = curl_init();
    $url = $base_url.http_build_query($params);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $user_agents = array(
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:11.0) Gecko/20100101 Firefox/11.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
        'Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.10.229 Version/11.62'
    );
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agents[rand(0, 3)]);
    //curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    if(!empty($cookiefile)){
        curl_setopt($ch, CURLOPT_COOKIEJAR, "{$cookiefile}");
        //curl_setopt($ch, CURLOPT_COOKIEFILE,"{$cookiefile}");
    }
    if(!curl_exec($ch)) {
        return false;
    }
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

function getPinterestUsernameBoardnameFromUrl($url) {
    $parts = parse_url($url);
    $path = $parts['path'];
    $items = explode('/', $path);
    $return = array('username'=>'', 'boardname'=>'');
    if(!empty($items[1])) {
        $return['username'] = $items[1];
        if(!empty($items[2])) {
            $return['boardname'] = $items[2];
        }
        return $return;
    } else {
        return false;
    }
}

function get_innerHTML($dom_results) {
    foreach($dom_results as $dom_result) {
        $children = $dom_result->childNodes;
        $innerHTML = '';
        foreach($children as $child) {
            $innerHTML .= $child->ownerDocument->saveXML( $child );
        }
        return $innerHTML;
    }
}

function intval2($i) {
    return intval(str_replace(',', '', $i));
}

function floatval2($f) {
    return floatval(str_replace(',', '', $f));
}

// to reduce the number of pages requested,
// we always scrape as much info as possible from a page
// miss board category, board followers
// return:
// array(
//  'user' => array(
//      'id'=>'',
//      'image_large_url'=>'http://media-cdn.pinterest.com/avatars/liangdev_1330801291_o.jpg',
//      "image_url": "http://d30opm7hsgivgh.cloudfront.net/avatars/liangdev_1330801291.jpg",
//      'username' => 'liangdev',
//      'website' => '',
//      'twitter_link' => '',
//      'is_following' => '',
//      "facebook_link": "http://www.facebook.com/profile.php?id=1693922445",
//      "location": "",
//      "full_name": "Liang Huang",
//      "stats" => array(
//          "followers_count"=> 1,
//          "likes_count"=> 1,
//          "pins_count"=> 5,
//          "following_count"=> 13,
//          "boards_count"=> 1
//      )
//  ),
//    'boards' => array(
//      0=> array(
//          "id": "80431612034053084",
//          "name": "Sparkling",
//          "category": "",
//          "is_collaborator": '',
//          "user_id": "",
//          "description": "",
//          "url": "/liangdev/sparkling/",
//          "is_following": '',
//          "thumbnails"=>array(
//              'http://media-cache.pinterest.com/upload/146085581632895322_JqdTilrz_b.jpg', ...
//          ),
//          'stats' => array(
//              'followers_count': 0,
//              'pins_count': 5
//          )
//      )
//
//
//    )
//
//
// )
// the consumer of the function should always check if a key/value pair exists
// test accounts:
// http://pinterest.com/janew/
// http://pinterest.com/liangdev/
// http://pinterest.com/pintics/

function parse_pinterest_account_page($html) {
    $return = array();

    $user = array();
    $boards = array();
    $user['id'] = '';
    try {
        $dom = new Zend_Dom_Query($html);
        foreach($dom->query('.ProfileImage img') as $profileimage_elem) {
            $user['image_large_url'] = $profileimage_elem->getAttribute('src');
            $user['image_url'] = getImageUrlFromImageLargeUrl($user['image_large_url']);
        }
        foreach($dom->query('meta[property="og:url"]') as $url_elem) {
//            $meta_username = $username_elem->getAttribute('content');
//            $names = explode('(', $meta_username);
//            $username = trim($names[sizeof($names)-1], ')');
//            $user['username'] = $username;
            $account_url = $url_elem->getAttribute('content');
            $username_boardname = getPinterestUsernameBoardnameFromUrl($account_url);
            $user['username'] = $username_boardname['username'];
            $username = $user['username'];
        }
        foreach($dom->query('#ProfileLinks li a.website') as $website_elem) {
            $user['website'] = $website_elem->getAttribute('href');
        }
        foreach($dom->query('#ProfileLinks li a.twitter') as $twitter_elem) {
            $user['twitter_link'] = $twitter_elem->getAttribute('href');
        }
        foreach($dom->query('#ProfileLinks li a.facebook') as $facebook_elem) {
            $user['facebook_link'] = $facebook_elem->getAttribute('href');
        }
        $user['location'] = trim(str_replace('<span class="icon location"/>', '', get_innerHTML($dom->query('#ProfileLocation'))));
        $user['full_name'] = get_innerHTML($dom->query('#ProfileHeader h1'));
        $user['about'] = get_innerHTML($dom->query('#ProfileHeader div.info p'));
        $user['stats'] = array();
        $boards_elems = $dom->query("#ContextBar ul li a[href='/$username/'] strong");
        $user['stats']['boards_count'] = intval2(get_innerHTML($boards_elems));
        $pins_elems = $dom->query("#ContextBar ul li a[href=\"/$username/pins/\"] strong");
        $user['stats']['pins_count'] = intval2(get_innerHTML($pins_elems));
        $likes_elems = $dom->query("#ContextBar ul li a[href='/$username/pins/?filter=likes'] strong");
        $user['stats']['likes_count'] = intval2(get_innerHTML($likes_elems));
        $followers_elems = $dom->query("#ContextBar ul li a[href='/$username/followers/'] strong");
        $user['stats']['followers_count'] = intval2(get_innerHTML($followers_elems));
        $following_elems = $dom->query("#ContextBar ul li a[href='/$username/following/'] strong");
        $user['stats']['following_count'] = intval2(get_innerHTML($following_elems));
        foreach($dom->query('#ColumnContainer li div.pinBoard') as $i=>$b_elem) {
            $b_elem_id = $b_elem->getAttribute('id');
            $boards[$i]['id'] = str_replace('board', '', $b_elem_id);
            $bname_elem = $dom->query('#'.$b_elem_id.' h3 a');
            $boards[$i]['name'] = get_innerHTML($bname_elem);
            foreach($bname_elem as $bname_e) {
                $boards[$i]['url'] = $bname_e->getAttribute('href');
            }
            // thumbnails -- #board284852813870170756 img
            $boards[$i]['thumbnails'] = array();
            foreach($dom->query('#'.$b_elem_id.' div.board img') as $img_elem) {
                $boards[$i]['thumbnails'][] = $img_elem->getAttribute('src');
            }
            $boards[$i]['stats'] = array();
            $boards[$i]['stats']['pins_count'] = intval2(str_replace(' pins', '', get_innerHTML($dom->query('#'.$b_elem_id.' h4'))));
        }

    } catch(Exception $e) {
        return false;
    }

    $return = array('user'=>$user, 'boards'=>$boards);

    return $return;
}

function getImageUrlFromImageLargeUrl($image_large_url) {
    $parts = pathinfo($image_large_url);
    $filename = $parts['filename'];
    $new_filename = str_replace('_o', '', $filename);
    return $parts['dirname'].'/'.$new_filename.'.'.$parts['extension'];
}

function getImageLargeUrlFromImageUrl($image_url) {
    $parts = pathinfo($image_url);
    $filename = $parts['filename'];
    $new_filename = $filename.'_o';
    return $parts['dirname'].'/'.$new_filename.'.'.$parts['extension'];
}

function get_pinterest_account_info($pinterest_username) {
    // get info from pinterest board page
    $info = array(
        'user' => array(),
        'boards' => array()
    );

    $pinterest_account_page = new PinterestAccountPage($pinterest_username);
    while ($output_boards = $pinterest_account_page->getNext()) {
        $info['boards'] = array_merge($info['boards'], $output_boards);
    }
    $info['user'] = $pinterest_account_page->getAccountInfo();

    return $info;
}

function get_pinterest_board_info($board_id, $next_page_url='') {
    $info = array(
        'pins' => array(),
        'next_page_url' => ''
    );

    $pinterest_board_page = new PinterestBoardPage($board_id);
    $pinterest_board_page->setNextPageUrl($next_page_url);
    if($pins = $pinterest_board_page->getNext()) {
        $info['pins'] = $pins;
        $info['next_page_url'] = $pinterest_board_page->getNextPageUrl();
    }

    return $info;
}

function getBoardCovers($thumbnails) {
    $covers = explode(',', $thumbnails);
    // the following codes will negatively implact the frontend performance
    // we should use a daemon to validate the url of covers
//    if(!url_exists($covers[0])) {
//        str_replace('_222.jpg', '_b.jpg', $covers[0]);
//    }
    return $covers;
}

// return:
// array(
//  'user'=> array(
//      'username' => 'liangdev',
//      'image_url' => 'http://media-cache4.pinterest.com/avatars/liangdev_1330801291.jpg',
//      'full_name' => 'Liang Huang',
//      'image_large_url' => 'http://media-cache4.pinterest.com/avatars/liangdev_1330801291_o.jpg'
//  ),
//  'board' => array(
//      'url' => '',
//       'stats'=>array(
//          'followers_count'=>1,
//          pins_count': 5
//       )
//  )
//  'pagination' => array('next'=>'http://pinterest.com/janew/happy/?page=2'),
//  'pins' => array(
//  '0'=> array(
//      'domain' => 'pintics.com',
//      'description' => 'panda',
//      'images' => array(),
//      'counts' => array('repins'=>0, 'comments'=>1, 'likes'=>2),
//      'id' => '80431543315387362',
//      'comments' => array(
//          'id' => asfsad,
//          'text' => asfsad,
//          user=>array('username'=>'', 'full_name'=>'', 'image_url'=>'', 'image_large_url'=>'')
//      ),
//      'is_repin': true,
//      'source': ...
//  )
//
//  )
// )
//http://pinterest.com/janew/happy/
function get_all_pins_from_pinterest_board_page($board_url){

    $return=array();
    $pins_count=0;
    $html=  http_request2($board_url);
    $dom = new Zend_Dom_Query($html);
    $page=0;

    foreach($dom->query('meta[property="pinterestapp:pins"]') as $pins_elem) {
            $pins_count = $pins_elem->getAttribute('content');
            echo "THIS BOARD HAS $pins_count PINS\n";
    }
    if($pins_count%50!=0){
        $page=$pins_count/50+1;
    }else {
        $page=$pins_count/50;
    }
    $page=$page>=20?20:$page;
    for($i=1;$i<=$page;$i++){
        $pins=array();
        echo "START PARSE PAGE: $i \n";
        //$board_url=$board_url."?page={$i}";
        $html=  http_request2($board_url."?page={$i}");
        if(!empty($html)){
            $pins=get_pins_from_pinterest_board_page($html);
            $return=  array_merge($return,$pins);
        }
    }

    return $return;
}
//pinterest_board_page
function get_pins_from_pinterest_board_page($html){

    $pins=array();
    $dom = new Zend_Dom_Query($html);

    foreach($dom->query('#ColumnContainer>div.pin') as $i=>$pin_elem) {
        $pins[$i]['id'] = $pin_elem->getAttribute('data-id');
        $domain_elems = $dom->query('div[data-id="'.$pins[$i]['id'].'"] div.attribution p a');
        $pins[$i]['domain'] = get_innerHTML($domain_elems);
        foreach($domain_elems as $domain_elem) {
            $pins[$i]['source'] = $domain_elem->getAttribute('href');
        }
        if(empty($pins[$i]['source'])) {
            $pins[$i]['is_repin'] = 0;
        } else {
            $pins[$i]['is_repin'] = 1;
        }
        $pins[$i]['description'] = get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.description'));
        $pins[$i]['images'] = array();
        foreach($dom->query('div[data-id="'.$pins[$i]['id'].'"] div.PinHolder a.PinImage img') as $pinimage_elem) {
            $pins[$i]['images']['board'] = $pinimage_elem->getAttribute('src');
            $pins[$i]['images']['mobile'] = getPinImagesMobileFromImagesBoard($pins[$i]['images']['board']);
            $pins[$i]['images']['closeup'] = getPinImagesCloseupFromImagesBoard($pins[$i]['images']['board']);
            $pins[$i]['images']['thumbnail'] = getPinImagesThumbnailFromImagesBoard($pins[$i]['images']['board']);
        }
        $pins[$i]['counts'] = array();
        $likes_text = trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.stats span.LikesCount')));
        $num_text1 = explode(' ', $likes_text);
        $pins[$i]['counts']['likes'] = $num_text1[0];
        $comments_text = trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.stats span.CommentsCount')));
        $num_text2 = explode(' ', $comments_text);
        $pins[$i]['counts']['comments'] = $num_text2[0];
        $repins_text = trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.stats span.RepinsCount')));
        $num_text3 = explode(' ', $repins_text);
        $pins[$i]['counts']['repins'] = $num_text3[0];

        // price
        $pins[$i]['price'] = floatval(trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] .price')), ' $'));
    }
    return $pins;
}
function parse_pinterest_board_page($html, $subpage=1) {
    $return = array();

    $user = array();
    $board = array();
    $board['stats'] = array();
    $pagination = array();
    $pins = array();

    try {

        $dom = new Zend_Dom_Query($html);

//        foreach($dom->query('meta[property="og:url"]') as $url_elem) {
//            $username_boardname =  getPinterestUsernameBoardnameFromUrl($url_elem->getAttribute('content'));
//            $user['username'] = $username_boardname['username'];
//            $boardname = $username_boardname['boardname'];
//            $board['url'] = 'http://pinterest.com/'.$user['username'].'/'.$boardname.'/';
//        }
//
//        foreach($dom->query('#BoardUserName a') as $userfullname_elem) {
//            $user['full_name'] = $userfullname_elem->getAttribute('title');
//        }
//
//        foreach($dom->query('#BoardUsers a.ImgLink img') as $userimg_elem) {
//            $user['image_url'] = $userimg_elem->getAttribute('src');
//            $user['image_large_url'] = getImageLargeUrlFromImageUrl($user['image_url']);
//        }
//
//        foreach($dom->query('meta[property="pinterestapp:pins"]') as $pins_elem) {
//            $board['stats']['pins_count'] = $pins_elem->getAttribute('content');
//        }
//
//        foreach($dom->query('meta[property="pinterestapp:followers"]') as $followers_elem) {
//            $board['stats']['followers_count'] = $followers_elem->getAttribute('content');
//        }
//        foreach($dom->query('meta[property="pinterestapp:category"]') as $category_elem) {
//            $board['category'] = $category_elem->getAttribute('content');
//        }
//        $board['description'] = get_innerHTML($dom->query('#BoardDescription'));

//        preg_match('/"currentPage": [0-9]+/', $html, $current_page_match);
//        if(!empty($current_page_match[0])) {
//            $var_val = explode(':', $current_page_match[0]);
//            $current_page = trim($var_val[1]);
//            if($current_page*50<$board['stats']['pins_count']) {
//                $next_page = $current_page+1;
//                $pagination['next'] = $board['url'].'?page='.$next_page;
//            } else {
//                if($current_page!=1) {
//                    $prev_page = $current_page--;
//                    $pagination['prev'] = $board['url'].'?page='.$prev_page;
//                }
//            }
//        }

        $pin_elems = $dom->query('#ColumnContainer>div.pin');
        $j = 0;
        foreach($pin_elems as $i=>$pin_elem) {
            if(intval2($i / 10) !== ($subpage-1)) {
                continue;
            }
            $pins[$j]['id'] = $pin_elem->getAttribute('data-id');
//            $domain_elems = $dom->query('div[data-id="'.$pins[$i]['id'].'"] div.attribution p a');
//            $pins[$i]['domain'] = get_innerHTML($domain_elems);
//            foreach($domain_elems as $domain_elem) {
//                $pins[$i]['source'] = $domain_elem->getAttribute('href');
//            }
//            if(empty($pins[$i]['source'])) {
//                $pins[$i]['is_repin'] = 0;
//            } else {
//                $pins[$i]['is_repin'] = 1;
//            }
            $pins[$j]['description'] = get_innerHTML($dom->query('div[data-id="'.$pins[$j]['id'].'"] p.description'));
            $pins[$j]['images'] = array();
            foreach($dom->query('div[data-id="'.$pins[$j]['id'].'"] div.PinHolder a.PinImage img') as $pinimage_elem) {
                $pins[$j]['images']['board'] = $pinimage_elem->getAttribute('src');
                $pins[$j]['images']['mobile'] = getPinImagesMobileFromImagesBoard($pins[$j]['images']['board']);
                $pins[$j]['images']['closeup'] = getPinImagesCloseupFromImagesBoard($pins[$j]['images']['board']);
                $pins[$j]['images']['thumbnail'] = getPinImagesThumbnailFromImagesBoard($pins[$j]['images']['board']);
            }
//            $pins[$i]['counts'] = array();
//            $likes_text = trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.stats span.LikesCount')));
//            $num_text1 = explode(' ', $likes_text);
//            $pins[$i]['counts']['likes'] = $num_text1[0];
//            $comments_text = trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.stats span.CommentsCount')));
//            $num_text2 = explode(' ', $comments_text);
//            $pins[$i]['counts']['comments'] = $num_text2[0];
//            $repins_text = trim(get_innerHTML($dom->query('div[data-id="'.$pins[$i]['id'].'"] p.stats span.RepinsCount')));
//            $num_text3 = explode(' ', $repins_text);
//            $pins[$i]['counts']['repins'] = $num_text3[0];

            // price
            $pins[$j]['price'] = floatval(trim(get_innerHTML($dom->query('div[data-id="'.$pins[$j]['id'].'"] .price')), ' $'));

            //echo "$i\n";
            $j++;

        }

    } catch(Exception $e) {

    }
    $return['user'] = $user;
    $return['board'] = $board;
    $return['pagination'] = $pagination;
    $return['pins'] = $pins;

    return $return;

}

function getPinImagesMobileFromImagesBoard($images_board) {
    $parts = pathinfo($images_board);
    $filename = $parts['filename'];
    $items = explode('_', $filename);
    $items[sizeof($items)-1] = 'f';
    $new_filename = join('_', $items);
    return $parts['dirname'].'/'.$new_filename.'.'.$parts['extension'];

}

function getPinImagesCloseupFromImagesBoard($images_board) {
    $parts = pathinfo($images_board);
    $filename = $parts['filename'];
    $items = explode('_', $filename);
    $items[sizeof($items)-1] = 'c';
    $new_filename = join('_', $items);
    return $parts['dirname'].'/'.$new_filename.'.'.$parts['extension'];
}

function getPinImagesThumbnailFromImagesBoard($images_board) {
    $parts = pathinfo($images_board);
    $filename = $parts['filename'];
    $items = explode('_', $filename);
    $items[sizeof($items)-1] = 't';
    $new_filename = join('_', $items);
    return $parts['dirname'].'/'.$new_filename.'.'.$parts['extension'];
}

function hmac_sha1($key, $data)
{
    // Adjust key to exactly 64 bytes
    if (strlen($key) > 64) {
            $key = str_pad(sha1($key, true), 64, chr(0));
    }
    if (strlen($key) < 64) {
            $key = str_pad($key, 64, chr(0));
    }

    // Outter and Inner pad
    $opad = str_repeat(chr(0x5C), 64);
    $ipad = str_repeat(chr(0x36), 64);

    // Xor key with opad & ipad
    for ($i = 0; $i < strlen($key); $i++) {
            $opad[$i] = $opad[$i] ^ $key[$i];
            $ipad[$i] = $ipad[$i] ^ $key[$i];
    }

    return sha1($opad.sha1($ipad.$data, true));
}

function createDistributedDB($host, $name, $user='root', $password='', $sqlfile) {
    //echo "start createUserHistoryStatDB: $host $name\n";
    $dbobj = new DBObj($host, 'information_schema', $user, $password);

    if($res = $dbobj->query("select * from SCHEMATA where SCHEMA_NAME='$name'")) {
        if($record = $dbobj->fetch_assoc($res)) {
           //echo "$name already exists, no need to be created\n";
           return true;
        }
    }

    // start createing user_history_stat schema
    $sql = "create database $name";
    //echo "createUserHistoryStatDB sql: $sql\n";
    if(!$dbobj->query($sql)) {
        return false;
    }
    $script_str = "mysql -h $host -u$user ";
    if(!empty($password)){
    	$script_str .= "-p$password";
    }

    $script_str .= " $name";
    $script_str = "/usr/bin/php "  . __DIR__ . "/../scripts/mustachize.php " . __DIR__ . "/../configs/$sqlfile | $script_str";
    exec($script_str);

    //echo "exec: "."mysql -h $host -u$user $name < ".__DIR__."/../configs/user_history_stat.sql\n";
    return true;
}

function createStoreDB($host, $store_id) {
    global $dbconfig;
    $name = getStoreDBName($store_id);
    $user = $dbconfig->store->user;
    $password = $dbconfig->store->password;

    $sqlfile = "store.sql";
    if(createDistributedDB($host, $name, $user, $password, $sqlfile)) {
        return true;
    } else {
        return false;
    }
}

function generateStoreSubdomain($user) {
    $rand = rand(100,9999);
    if(!empty2($first_name = sanitize_string($user->getFirstName()))) {
        return $first_name.$rand;
    } else if(!empty2($name = sanitize_string($user->getName()))) {
        return $name.$rand;
    } else {
        return get_email_username($user->getUserName()).$rand;
    }
}

function getUserTitle($user) {
    if(!empty2($first_name = sanitize_string($user['first_name']))) {
        return $first_name;
    } else if(!empty2($name = sanitize_string($user['last_name']))) {
        return $name;
    } else {
        return get_email_username($user['username']);
    }
}

function generateStoreName($user) {
    $suffix = "'s Store";
    if(!empty2($first_name = sanitize_string($user->getFirstName()))) {
        return $first_name.$suffix;
    } else if(!empty2($name = sanitize_string($user->getName()))) {
        return $name.$suffix;
    } else {
        return get_email_username($user->getUserName()).$suffix;
    }
}

function getStoreHost() {
    global $dbconfig;
    return $dbconfig->store->host;
}

function getStoreDBName($store_id) {
    global $dbconfig;
    $storedbname = $dbconfig->store->name;
    return $storedbname.'_'.$store_id;
}

function isPinterestAccountImported($pinterest_account) {

    $image_url = $pinterest_account->getImageUrl();
    $image_large_url = $pinterest_account->getImageLargeUrl();
    if(!empty($image_large_url) || !empty($image_url)) {
        Log::write(INFO, 'Pinterest account has been imported '.$pinterest_account->getId().' '.$pinterest_account->getUsername());
        return true;
    } else {
        Log::write(INFO, 'Pinterest account hasnt been imported '.$pinterest_account->getId().' '.$pinterest_account->getUsername());
        return false;
    }
}


function safeSerialize($str){
	return base64_encode(serialize($str));
}

function safeUnSerialize($str){
	return unserialize(base64_decode($str));
}

function getPagedBoardUrl($board, $page) {
    return 'http://pinterest.com'.$board->getUrl().'?page='.$page;
}

function getBoardPageSubPage($page) {
    $count = $page*PIN_NUM_PER_PAGE-1;
    $board_page = intval2($count/50)+1;
    $subpage = intval2(($count - ($board_page-1)*50)/PIN_NUM_PER_PAGE)+1;
    return array('page'=>$board_page, 'subpage'=>$subpage);
}

function get_instances_num($instance_name) {
    exec("ps aux|grep $instance_name", $output);
    return count($output);

}

function curl_post($url, $postfields=array(), $headers=array(), $auth=array()) {

    // Set the curl parameters.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
    curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
    if(!empty($auth['username']) && !empty($auth['password'])) {
        curl_setopt($ch, CURLOPT_USERPWD, $auth['username'].":".$auth["password"]);
    }
    return curl_exec($ch);
}

// input
// dst: /bucket_name/path/filename.png
// src: url or temp file path
function upload_image($dst, $src) {
    global $amazonconfig;
    $s3 = new S3($amazonconfig->api->access_key, $amazonconfig->api->secret);

    $parts = explode('/', $dst);
    $bucket = $parts[1];
    $path = str_replace("/$bucket/", '', $dst);
    $filename = $parts[sizeof($parts)-1];

    try{
        if(!$s3->putBucket($bucket, S3::ACL_PUBLIC_READ)) {
            return false;
        }

        if(filter_var($src, FILTER_VALIDATE_URL)){
            $newsrc = "/tmp/$filename";
            if(!file_put_contents($newsrc, file_get_contents($src))) {
                return false;
            }
            $src = $newsrc;
        }

        $server = $amazonconfig->api->s3->url;
        if ($s3->putObject($s3->inputFile($src, false), $bucket, $path, S3::ACL_PUBLIC_READ)) {
            return $server."/".$bucket."/".$path;
        }else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }

}
/**
 * Upload file to s3
 * Eg. upload_file_to_s3('/tmp/test.csv', 'shopinterest_jq', 'csv/29/store_29.csv', S3::ACL_AUTHENTICATED_READ);
 */
function upload_file_to_s3($src, $bucket, $path, $permission = S3::ACL_PUBLIC_READ){
    global $amazonconfig;
    $s3 = new S3($amazonconfig->api->access_key, $amazonconfig->api->secret);

    try{
        if(!$s3->putBucket($bucket, $permission)) {
            return false;
        }

        $server = $amazonconfig->api->s3->url;
        if ($s3->putObject($s3->inputFile($src, false), $bucket, $path, $permission)) {
            return $server."/".$bucket."/".$path;
        }else {
            return false;
        }
    } catch (Exception $e) {
        return false;
    }
}
/**
     Download file from s3 to local
     Eg. download_file_from_s3('shopinterest_test', 'csv/29/stores_29_csv.csv', '/tmp/stores_29_csv.csv');
 */
function download_file_from_s3($bucket, $file_path, $save_to){
    global $amazonconfig;
    $s3 = new S3($amazonconfig->api->access_key, $amazonconfig->api->secret);
    try{
        $s3->getObject($bucket,$file_path,$save_to);
    } catch (Exception $e) {
        return false;
    }
}

    /**
     * @param $src_file 'store/11/logo_11.jpg'
     * @return mixed false|array eg. array(4) { ["time"]=> int(1344387704)
     *     ["hash"]=> string(32) "5e9a6e4f730cf3390a499fcb448cbb70"
     *     ["type"]=> string(10) "image/jpeg"
     *     ["size"]=> int(143929) }
     */
function load_s3_image($src_file){
    global $amazonconfig, $fileuploader_config;
    $s3 = new S3($amazonconfig->api->access_key, $amazonconfig->api->secret);
    $bucket = $fileuploader_config->store_bucket;
    $res = $s3->getObjectInfo($bucket, $src_file, true);
    return $res;
}

function delete_image($dst){
    global $amazonconfig;
    $s3 = new S3($amazonconfig->api->access_key, $amazonconfig->api->secret);

    $parts = explode('/', $dst);
    $bucket = $parts[1];
    $path = str_replace("/$bucket/", '', $dst);

    return $s3->deleteObject($bucket, $path);

}

function get_conn($host, $user, $password) {
    if(!is_resource($conn = mysql_connect($host, $user, $password))) {
        die("ERROR: connect to $host\n");
    }
    return $conn;
}

function select_db($dbname, $conn) {
    if(!mysql_select_db($dbname, $conn)) {
        die("ERROR: select db $dbname\n");
    }
    return true;
}

// type: account, job or store
function createDBIfNotExist($type) {
    if($type !== 'account' && $type !== 'job' && $type !== 'store') {
        die("ERROR: createDBIfNotExist($type)\n");
    }

    global $dbconfig;
    // connect to the mysql host of account
    $host = $dbconfig->$type->host;
    $user = $dbconfig->$type->user;
    $password = $dbconfig->$type->password;
    $dbname = $dbconfig->$type->name;

    $conn = get_conn($host, $user, $password);

    // select the db of information_schema
    select_db('information_schema', $conn);

    // check if the account db exists
    if($res = mysql_query("select * from SCHEMATA where SCHEMA_NAME='$dbname'", $conn)) {
        if($record = mysql_fetch_assoc($res)) {
            echo "DONE: account db $dbname exists on $host\n";
            return false;
        } else {
            echo "DONE: account db $dbname does not exist on $host\n";
            //need to create the account db
            createDB($type, $conn);
            return true;
        }
    } else {
        die("ERROR: select * from SCHEMATA where SCHEMA_NAME='$dbname'");
    }

}

function createDB($type, $conn) {

    global $dbconfig;
    $host = $dbconfig->$type->host;
    $user = $dbconfig->$type->user;
    $password = $dbconfig->$type->password;
    $dbname = $dbconfig->$type->name;

    $sql = "create database $dbname";
    if(mysql_query($sql, $conn)) {
        echo "DB $dbname created\n";
    } else {
        die("ERROR: creating DB $dbname\n");
    }

    executeSQLByHost($host, $user, $password, $dbname, "$type.sql");

}

function getSchemaVersion($type) {
    $sqls = file_get_contents(__DIR__.'/../configs/'.$type.'.sql');
    preg_match("/[ ]*insert[ ]*into[ ]*[`]?version[`]?[ ]*values[ ]*\('(.*)'\)/", $sqls, $matches);
    return $matches[1];
}

function getDBVersion($type) {
    global $dbconfig;
    $host = $dbconfig->$type->host;
    $user = $dbconfig->$type->user;
    $password = $dbconfig->$type->password;
    $dbname = $dbconfig->$type->name;

    return getDBVersionByHost($host, $user, $password, $dbname);

}

function getDBVersionByHost($host, $user, $password, $dbname) {
    $conn = get_conn($host, $user, $password);
    select_db($dbname, $conn);

    if($res = mysql_query('select version from version', $conn)) {
        if($record = mysql_fetch_assoc($res)) {
            return $record['version'];
        }
    }
    die('ERROR: getting the version from the db type'.$type."\n");
}

function getAlterSQLFile($type, $version) {

    return "$type.v$version.sql";
}

function executeSQL($type, $sql) {
    global $dbconfig;
    $host = $dbconfig->$type->host;
    $user = $dbconfig->$type->user;
    $password = $dbconfig->$type->password;
    $dbname = $dbconfig->$type->name;

    executeSQLByHost($host, $user, $password, $dbname, $sql);

}

function executeSQLByHost($host, $user, $password, $dbname, $sqlfile) {
    $script_str = "mysql -h $host -u$user ";
    if(!empty($password)){
    	$script_str .= "-p$password";
    }

    $script_str .= " $dbname";
    $script_str = "php "  . __DIR__ . "/../scripts/mustachize.php " . __DIR__ . "/../configs/$sqlfile | $script_str";
    exec("sh -c \"$script_str\"", $output);
    echo "results on executing the sql file $script_str:\n";
    ddd($output);
}

function updateDBSchema($type) {
    global $dbconfig;
    $host = $dbconfig->$type->host;
    $user = $dbconfig->$type->user;
    $password = $dbconfig->$type->password;
    $dbname = $dbconfig->$type->name;

    $schema_version = intval2(getSchemaVersion($type));
    // getDBVersion
    $db_version = intval2(getDBVersion($type));

    echo "schema version: $schema_version db_version: $db_version\n";


    if($db_version === $schema_version) {
        die('DONE: no need to update the db type'.$type." because it is up to date\n");
    } else if($db_version > $schema_version) {
        die("ERROR: the db version is greater than the schema version for db type $type\n");
    } else if($db_version < $schema_version) {
        // update the db
        echo "DONE: ready to update the db schema for db type $type\n";

        $alter_sql_file = getAlterSQLFile($type, $schema_version);
        executeSQLByHost($host, $user, $password, $dbname, $alter_sql_file);
        echo "DONE: updated $dbname schema\n";

        // for store, we need to update every store databases beside the template store db
        if($type === 'store') {
            $account_dbobj = DBObj::getAccountDBObj();
            $store_ids = StoresMapper::getAllStoreIds($account_dbobj);
            foreach($store_ids as $store_id) {
                updateStoreDBSchema($store_id, $schema_version);
            }
        }


    }
}

function updateStoreDBSchema($store_id, $schema_version) {
    global $dbconfig;

    $store_user = $dbconfig->store->user;
    $store_password = $dbconfig->store->password;
    $store_dbname = $dbconfig->store->name.'_'.$store_id;

    $store = new Store(Dbobj::getAccountDBObj());
    $store->findOne('id='.$store_id);
    $store_host = $store->getHost();
    $db_version = intval2(getDBVersionByHost($store_host, $store_user, $store_password, $store_dbname));

    echo "schema version $schema_version db verson $db_version\n";

    if($db_version === $schema_version) {
        die('DONE: no need to update the store db store id is '.$store_id." because it is up to date\n");
    } else if($db_version > $schema_version) {
        die("ERROR: the db version is greater than the schema version for store db  store_id is $store_id\n");
    } else if($db_version < $schema_version) {
        // update the db
        echo "DONE: ready to update the db schema for store db store_id is $store_id \n";

        $alter_sql_file = getAlterSQLFile('store', $schema_version);
        executeSQLByHost($store_host, $store_user, $store_password, $store_dbname, $alter_sql_file);
        echo "DONE: updated the store $store_id schema\n";
    }

}

function getURL($path='') {
    if(php_sapi_name() === 'cli') {
        global $site_domain;
        return 'http://'.$site_domain.$path;
    } else {
        return 'http://'.$_SERVER['HTTP_HOST'].$path;
    }
}

function mkdir2($path, $mode = 755){
    if(!is_dir($path)){
        exec('mkdir -p '.$mode." ".$path, $output=array(), $results);
        if($results === 0) {
            return true;
        }
        return false;
        //chmod($path, $mode);
    }
    return true;
}

function getDBInfo($type){
    $DBInfo=array();
    global $dbconfig;
    $DBInfo['host'] = $dbconfig->$type->host;
    $DBInfo['user'] = $dbconfig->$type->user;
    $DBInfo['password'] = $dbconfig->$type->password;
    $DBInfo['name'] = $dbconfig->$type->name;
    return $DBInfo;
}

//find the newcreate file
//input /tmp/db
//output array{
//          [0] => /tmp/db/job-20130614001351.tar.gz
//          [1] => /tmp/db/job-20130614001410.tar.gz
//       }
function getFileByCreateTime($path, $limit=1){
    date_default_timezone_set('America/Los_Angeles');
    $output=array();
    exec("find ${path}/* -mtime -${limit}",$output,$results);
    return $output;
}

function backupdb($host, $username, $password, $dbname, $outputpath) {

    $timestamp = get_timestamp();
    if(!empty($password)) {
    	$password = "-p$password";
    }

    $sql_str = "echo 'show databases;' | mysql -u$username -h$host $password | grep -v 'mysql\|test\|information_schema\|Database\|performance_schema' | xargs mysqldump -uroot -h$host $password --databases";

    exec("$sql_str | gzip >$outputpath/${dbname}-${timestamp}.gz", $output=array(), $results);
    if($results === 0) {
        debug("BACKUPDB:$dbname ON:$host SUCCESS");
    } else {
        debug("BACKUPDB:$dbname ON:$host FAILED");
    }
}

function getStoreDBNameHostFromAccountDB(){
    global $dbconfig;
    $return=array();
    // connect to the mysql host of account
    $host = $dbconfig->account->host;
    $user = $dbconfig->account->user;
    $password = $dbconfig->account->password;
    $accountdbname = $dbconfig->account->name;

    $conn = get_conn($host, $user, $password);

    // select the db of information_schema
    select_db($accountdbname, $conn);

    if($res = mysql_query("select id , host from stores", $conn)) {
        while($record = mysql_fetch_assoc($res)) {
            $return[]=array(
                'id'=>$record['id'],
                'host'=>$record['host']
            );
        }
    } else {
        die("ERROR: select * from stores");
    }
    return $return;
}

function getSiteVersionInfo($site_versions) {

    if(php_sapi_name() === 'cli') {
        $site_version = isset($GLOBALS['site_version']) ? $GLOBALS['site_version'] : 1;
    }
    foreach($site_versions as $version => $info) {
            $site_domain = $info->domain;
            $site_merchant_url = $info->merchant_url;
        if((isset($_SERVER['SERVER_NAME']) && endsWith($_SERVER['SERVER_NAME'], $site_domain)) ||
            (isset($site_version) && $site_version == $version)) {
            return array($site_domain, $site_merchant_url, (int)$version);
        }
    }
}

function getCookieDomain(){
    global $site_domain;
    $domain = '.'.$site_domain;
    return $domain;
}

function getSiteDomain() {
    global $site_domain;
    $domain = $site_domain;
    if(php_sapi_name() === 'cli') {
        return $domain;
    } else {
        if($_SERVER['SERVER_PORT'] !== '80' && $_SERVER['SERVER_PORT'] !== '443') {
            $domain = $domain.':'.$_SERVER['SERVER_PORT'];
        }
        return $domain;
    }
}

function getSubdomain($site_domain=NULL) {
    if($site_domain === NULL) {
        $site_domain = getSiteDomain();
    }
    $site_domain = preg_replace("/:\d+$/", "", $site_domain);
    $http_host = preg_replace("/:\d+$/", "", $_SERVER['HTTP_HOST']);
    return trim(str_replace($site_domain, '', $http_host), '.');
}

function getSiteMerchantUrl($uri = '') {
    global $site_merchant_url;

    $url = getProtocol($uri).$site_merchant_url;
    if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] !== '80' && $_SERVER['SERVER_PORT'] !== '443') {
        $url = $url.':'.$_SERVER['SERVER_PORT'];
    }
    if(!empty($uri)) {
        $url = $url.$uri;
    }

    // remove /index at the end of url
    while (endsWith($url, '/index')) {
        $url = substr($url, 0, -6);
    }
    return $url;
}

function getSiteAssociateUrl($uri = '') {
    $url = 'http://salesnetwork.'.getSiteDomain();
    if(!empty($uri)) {
        $url = $url.$uri;
    }
    return $url;
}

function getSubdomainType($subdomain=NULL) {
    if($subdomain === NULL) {
        $subdomain = getSubdomain();
    }

    if($subdomain === '' || $subdomain === 'www') {
        return 'merchant';
    } else if ($subdomain === 'salesnetwork'){
        return 'salesnetwork';
    } else {
        return 'store';
    }
}

function get_url($subdomain = '', $uri = '', $params = array(), $protocol = '') {

    global $site_domain;
    if(substr_count($subdomain, ".") <= 1) $subdomain = 'www';
    if(!empty($subdomain)){
        $subdomain = $subdomain . ".";
    }
    $url = $subdomain . $site_domain;

    // remove /index at the end of url
    while (endsWith($uri, '/index')) {
        $uri = substr($uri, 0, -6);
    }

    if(empty($protocol)) {
        $protocol = '//';
    }

    $url = $protocol.$url;

    if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] !== '80' && $_SERVER['SERVER_PORT'] !== '443') {
        $url = $url.':'.$_SERVER['SERVER_PORT'];
    }

    $url = $url . $uri;
    if(!empty($params)) {
        $url = http_build_url2($url, $params);
    }
    return $url;
}

function get_url2($subdomain = '', $uri = '', $protocol = '') {

    global $site_domain;
    if(substr_count($subdomain, ".") <= 1) $subdomain = 'www';
    if(!empty($subdomain)){
        $subdomain = $subdomain . ".";
    }

    $url = $subdomain . $site_domain.$uri;

    if(empty($protocol)) {
        $protocol = '//';
    }

    $url = $protocol.$url;

    if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] !== '80' && $_SERVER['SERVER_PORT'] !== '443') {
        $url = $url.':'.$_SERVER['SERVER_PORT'];
    }

    return $url;
}

function array_append($arr1, $arr2) {
    foreach($arr2 as $key => $value) {
        $arr1[$key] = $value;
    }
    return $arr1;
}

function get_pagination_html($current_page, $total_page){
    $html = '';
    $pre_page = ( $current_page == 1 ) ? 1 : ( $current_page-1 ) ;
    $html .= "<li class='arrow unavailable'><a href='?page_num=".$pre_page."'>&laquo;</a></li>";

    if($total_page <= 5){ // pagination should be 1,2,3,4,5
        for($i=1;$i<=$total_page;$i++){
            $html .= "<li ";
            if($current_page == $i)
                $html .= "class='current'";
            $html .= "><a href='?page_num=$i'>$i</a></li>";
        }
    } else { // pagination will be shown by different cases
        if( $current_page < 4){
            for($i=1;$i<4;$i++){
                $html .= "<li ";
                if($current_page == $i)
                    $html .= "class='current'";
                $html .= "><a href='?page_num=$i'>$i</a></li>";
            }
            $html .= "<li class='unavailable'>&hellip;</li>";
            for($i=$total_page-2;$i<=$total_page;$i++){ // Like 1,2,3..7,8
               if( $i > 5 ) { // leave 2 or more numbers as ... , so the pagination
                   $html .= "<li><a href='?page_num=$i'>$i</a></li>";
               }
            }
        } else { // current_page >= 4
            for($i=1;$i<=3;$i++){ // Handle the first 3 pages
                if($i>($current_page-3)){
                    $html .= "<li class='unavailable'>&hellip;</li>";
                    break;
                }
                else
                    $html .= "<li><a href='?page_num=$i'>$i</a></li>";
            }

            if ( $current_page > ($total_page-3) ){ // Like 1...4.5.6
                for($i=$current_page;$i<=$total_page;$i++){
                    $html .= "<li ";
                    if($current_page == $i)
                        $html .= "class='current'";
                    $html .= "><a href='?page_num=$i'>$i</a></li>";
                }
            }
            else{
                $html .= "<li class='current'><a href='?page_num=$current_page'>$current_page</a></li>";
                $html .= "<li class='unavailable'>&hellip;</li>";
                for($i=$total_page-2;$i<=$total_page;$i++){ // Like 1...4...7
                    if($i>($total_page-$current_page+2))
                        $html .= "<li><a href='?page_num=$i'>$i</a></li>";
                }
            }
        }

    }
    if($current_page != $total_page){
        $next_page = $current_page + 1 ;
        $html .= "<li class='arrow'><a href='?page_num=".$next_page."'>&raquo;</a></li>";
    }
    return $html;
}

function http_build_url2($url, $fields) {
    return $url.'?'.http_build_query($fields);
}

function getServiceOrderPaymentRequestId($service_order_id) {
    return 'serviceorder-'.$service_order_id;
}

function getOrderPaymentRequestId($store_id, $order_id) {
    return 'order-'.$store_id.'-'.$order_id.'-'.uniqid();
}

function parseOrderPaymentRequestId($payment_request_id) {
    $parts = explode('-', $payment_request_id);
    if(sizeof($parts) !== 4 || $parts['0'] !== 'order') {
        return false;
    } else {
        $store_id = intval($parts[1]);
        $order_id = intval($parts[2]);
        if(empty($store_id) || empty($order_id)) {
            return false;
        } else {
            return array(
                'store_id' => $store_id,
                'order_id' => $order_id
            );
        }
    }
}

function paypal_logger($msg) {
    date_default_timezone_set('America/Los_Angeles');
    global $paypalconfig;
    $dir = $paypalconfig->log->dir;
    $file = $paypalconfig->log->filename;
    error_log(date('Y-m-d H:i:s')." ".$msg."\n", 3, $dir.'/'.$file);
}

function sendgrid_logger($msg) {
    date_default_timezone_set('America/Los_Angeles');
    global $sendgridconfig;
    $dir = $sendgridconfig->log->dir;
    $file = $sendgridconfig->log->filename;
    error_log(date('Y-m-d H:i:s')." ".$msg."\n", 3, $dir.'/'.$file);
}

function get_verification_code($id, $email) {
    return $id.'_'.md5($email);
}

function get_verification_url($id, $email) {
    return getURL().'/me/verify?code='.get_verification_code($id, $email);
}

function generate_password($length=6) {
    $chars = "abcdefghijkmnopqrstuvwxyz023456789";
    srand((double)microtime()*1000000);
    $i = 0;
    $pass = '' ;

    while ($i < $length) {
        $num = rand() % 33;
        $tmp = substr($chars, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}


// input:
// type: accounts, boards, pins
// id: pinterest_account_id or pinterest_board_id or pinterest_pin_id
// image_name: avatar (accounts), thumbnail (boards), pin (pins)
// image type:
// for avatar, type: empty or large
// for thumbnail, type: 0, 1, 2, 3, 4
// for pin, type: mobile, thumbnail, closeup, board
function get_pinterest_upload_dst($type, $id, $image_name, $image_type='') {

    // validation
    if($type === 'accounts') {
        if($image_name === 'avatar') {
            if($image_type !== '' && $image_type !== 'large') {
                return false;
            }
        } else {
            return false;
        }
    } else if($type === 'boards') {
        if($image_name === 'thumbnail') {
            if($image_type != 0 && $image_type != 1 && $image_type != 2 && $image_type != 3 && $image_type != 4) {
                return false;
            }
        } else {
            return false;
        }
    } else if($type === 'pins') {
        if($image_name === 'pin') {
            if($image_type !== 'mobile' && $image_type !== 'thumbnail' && $image_type !== 'closeup' && $image_type !== 'board') {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return false;
    }

    global $fileuploader_config, $amazonconfig;
    $bucket = $fileuploader_config->store_bucket;
    $pinterest_folder = $amazonconfig->s3->pinterest_folder;
    $dst = "/$bucket/$pinterest_folder/$type/$id/";
    $dst = $dst."$pinterest_folder"."_$type"."_$id"."_$image_name";
    if($image_type == '') {
        $dst = $dst.'.jpg';
    } else {
        $dst = $dst.'_'.$image_type.'.jpg';
    }
    return $dst;
}

function getBoardThumbnailNum($thumbnails){
    $num=1;
    $num = sizeof(explode(',', $thumbnails));
    return $num;
}
function get_thumbnails_upload_urls($pinterest_board_id, $k = 5){
    $dsts=array();
    for($i=0;$i<$k;$i++){
        $dsts[$i]=get_pinterest_image_url('boards',$pinterest_board_id,'thumbnail',$i);
//        if(!url_exists($dsts[$i])){
//            $dsts[$i]='';
//        }
    }
    return $dsts;
}
function get_pinterest_image_url($type, $id, $image_name, $image_type='') {
    global $amazonconfig;
    $base_url = $amazonconfig->api->s3->url;
    if(!$dst = get_pinterest_upload_dst($type, $id, $image_name, $image_type)) {
        return false;
    }
    return "$base_url$dst";
}

// input:
// image type: mobile, closeup, thumbnail, board or empty
function get_store_upload_dst($store_id, $picture_id, $image_type='') {

    // validation
    if($image_type !== 'mobile' && $image_type !== 'closeup' && $image_type !== 'thumbnail' &&
       $image_type !== 'board' && $image_type !== '') {
        return false;
    }

    global $fileuploader_config, $amazonconfig;
    $bucket = $fileuploader_config->store_bucket;
    $stores_folder = $amazonconfig->s3->stores_folder;
    $dst = "/$bucket/$stores_folder/$store_id/";
    $dst = $dst."$stores_folder"."_$store_id"."_$picture_id"."_pic";
    if(empty($image_type)) {
        $dst = $dst.'.jpg';
    } else {
        $dst = $dst.'_'.$image_type.'.jpg';
    }
    return $dst;

}

function get_store_image_url($store_id, $picture_id, $image_type='') {
    global $amazonconfig;
    $base_url = $amazonconfig->api->s3->url;
    if(!$dst = get_store_upload_dst($store_id, $picture_id, $image_type)) {
        return false;
    }
    return "$base_url$dst";
}

function get_current_year() {
    date_default_timezone_set('America/Los_Angeles');
    return date('Y');
}

function get_current_datetime() {
    date_default_timezone_set('America/Los_Angeles');
    return date('Y-m-d H:i:s');
}

function get_current_datetime_filename() {
    date_default_timezone_set('America/Los_Angeles');
    return date('Y-m-d-H-i-s');
}

function get_timestamp() {
    date_default_timezone_set('America/Los_Angeles');
    return date('YmdHis');
}

function get_date() {
    date_default_timezone_set('America/Los_Angeles');
    return date('Ymd');
}

function getNdaysago($n=7) {
    date_default_timezone_set('America/Los_Angeles');
    return date('Y-m-d', strtotime("-$n days"));
}

function image_to_jpg($src_image, $dst_image, $type, $quality = 100 ){
    try{
        if(strtoupper($type) == 'JPG')
            return true;
        else if(strtoupper($type) == 'PNG')
            $image = imagecreatefrompng($src_image);
        else if(strtoupper($type) == 'GIF')
            $image = imagecreatefromgif($src_image);
        else if(strtoupper($type) == 'JPEG')
            $image = imagecreatefromjpeg($src_image);

        imagejpeg($image, $dst_image, $quality);
        imagedestroy($image);
        return true;
    }catch (Exception $e){
        return false;
    }
}

function debug($msg) {
    if(APPLICATION_ENV !== 'production' || !empty($_REQUEST['debug'])) {
        error_log('[DEBUG]: '.$msg.' '.$_SERVER['SCRIPT_FILENAME']);
    }
}

function explode2($separator, $string) {
    $words = explode($separator, $string);
    $new_words = array();
    foreach($words as $word) {
        $word = strtolower(preg_replace("/[^a-zA-Z0-9\s]+/", "", $word));
        array_push($new_words, $word);
    }
    return $new_words;
}

// return
// array(
//     'tag_id1' => 'tag1'
//     'tag_id2' => 'tag2'
// )
function get_tags($str) {
    $tags = array();
    if(!empty($str)) {
        $id_tags = explode(',', $str);

        foreach($id_tags as $id_tag) {
            $parts = explode('-', $id_tag);
            $id = $parts[0];
            $tag = $parts[1];
            $tags[$id] = $tag;
        }
    }

    return $tags;
}

function get_store_logo_url($store_id) {
    global $amazonconfig, $fileuploader_config;
    $base_url = $amazonconfig->api->s3->url;
    $bucket = $fileuploader_config->store_bucket;
    return "$base_url/$bucket/stores/$store_id/stores_".$store_id."_logo.jpg";

}

function get_store_logo_upload_dst($store_id) {
    global $fileuploader_config;
    $bucket = $fileuploader_config->store_bucket;
    return "/$bucket/stores/$store_id/stores_".$store_id."_logo.jpg";

}

function url_exists($url) {
    if(@file_get_contents($url)) {
        return true;
    } else {
        return false;
    }
}

// get store logo from cache first
function get_store_logo($store_id) {
    global $shopinterest_config, $redis;
    $default_url = $shopinterest_config->store->logo->default;
    $logo_url = $redis->get("store:$store_id:converted_logo");
    if(url_exists($logo_url)) {
        return $logo_url;
    } else {
        return $default_url;
    }

}

function default_store_logo($store_logo) {
    if(!empty($store_logo)) {
        return $store_logo;
    } else {
        global $shopinterest_config;
        return $shopinterest_config->store->logo->default;
    }
}

function get_fulfillment_status($status) {
    if($status == PROCESSED) {
        return 'PROCESSED';
    } else if($status == PROCESSING) {
        return 'PROCESSING';
    } else if($status == CREATED) {
        return 'CREATED';
    } else if($status == SHIPPED) {
        return 'SHIPPED';
    } else {
        return 'UNKNOWN';
    }
}

function nuke_cookie($name) {
    setcookie ($name, "", time() - 3600, '/', $_SERVER['SERVER_NAME']);
    setcookie ($name, "", time() - 3600, '/', str_replace('.staging', '', $_SERVER['SERVER_NAME']));
    setcookie ($name, "", time() - 3600, '/', 'shopinterest.co');
    Log::write(INFO, 'Nuke the order cookie '.$_SERVER['SERVER_NAME']);
}

function sanitize_string($str) {
    return strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $str));
}

function sanitize_words($str, $replaceSpaceWith=null, $toLower = true) {
    $str = preg_replace("/[^a-zA-Z0-9\- ]+/", "", $str);
    if(!is_null($replaceSpaceWith)) {
        if($toLower) {
            $str = strtolower(preg_replace("/[- ]+/", $replaceSpaceWith, $str));
        } else {
            $str = preg_replace("/[- ]+/", $replaceSpaceWith, $str);
        }
        
    }
    return $str;
}

function query_words($query) {
    return '%'.sanitize_words($query, '%', true).'%';
}

// the object must be iterable
function obj_clone($obj) {
    $new_obj = new stdClass();
    foreach($obj as $key=>$value) {
        $new_obj->$key = $value;
    }
    return $new_obj;
}

function error_type($errno) {
    if($errno>=200 && $errno<=299) {
        return 'success';
    } else if($errno>=500 && $errno<600) {
        return 'warning';
    } else if($errno>=600) {
        return 'alert';
    } else {
        return 'unknown';
    }

}

function getRequestUrl() {
    return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

function checkRemoteFile($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if(curl_exec($ch)!==FALSE){
        return true;
    }
    else{
        return false;
    }
}

function checkRemoteFile2($url) {
    if(@file_get_contents($url)) {
        return true;
    } else {
        return false;
    }
}

function checkRemoteFile3($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    // don't download content
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $user_agents = array(
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:11.0) Gecko/20100101 Firefox/11.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
        'Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.10.229 Version/11.62'
    );
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agents[rand(0, 3)]);
    if(curl_exec($ch)!==FALSE){
        return true;
    }
    else{
        return false;
    }
}

function getStoreUrl($subdomain) {
    $p = "";
    if(substr_count(getSiteDomain(), ".") <= 1) $p = 'www.';
    return "http://$p".getSiteDomain().'/store/'.$subdomain;
}

function get_product_item_url($subdomain, $product_id) {
    return getStoreUrl($subdomain)."/products/item?id=".$product_id;
}

function parse_csv($file_path, $delimiter = "\t") {

    $data = file_get_contents($file_path);
    $lines = explode("\n", $data);
    $results = array();
    $header = array();
    foreach($lines as $i => $line) {
        $items = str_getcsv($line, $delimiter);

        foreach($items as $j=>$item) {
            if($i === 0) {
                $header[$j] = $item;
            } else {
                $results[$i-1][$header[$j]] = $item;
            }

        }
    }
    return $results;
}

function get_cookie_filename($account){
    return COOKIE_PATH."cookie_{$account}.txt";
}

//set cookie
function login(){

    global $pinterest_config;
    $postdata=array();
    $login=false;
    $login_url=$pinterest_config->api->login;
    $pinterest_account=$pinterest_config->email;
    $pinterest_password=$pinterest_config->password;

    mkdir2(COOKIE_PATH);
    $cookie_file=  get_cookie_filename($pinterest_account);
    $html = http_method($login_url,'GET',$cookie_file,array(),array());
    $headers = array(
                "Referer:$login_url"
            );
    $dom = new Zend_Dom_Query($html['html']);
    $token_elems = $dom->query('input[name="csrfmiddlewaretoken"]');
    $postdata['email'] = $pinterest_account;
    $postdata['password'] = $pinterest_password;
    $postdata['next'] = '/';
    foreach($token_elems as $token_elem) {
        $postdata['csrfmiddlewaretoken'] = $token_elem->getAttribute('value');
    }

    $return=  http_method($login_url,'POST',$cookie_file,$headers,$postdata);

    if(is_array($return)&&$return['http_code']===200){
        $home_dom = new Zend_Dom_Query($return['html']);
        $title = get_innerHTML($home_dom->query('title'));
        if(!empty($title)){
            if('Pinterest / Login'!=  trim($title)){
                $login=true;
            }
        }
    }
    return $login;
}

//method: GET POST DELETE
function http_method($base_url, $method='GET', $cookie_file='', $postfields=array(), $headers=array()){

    $return = array();
    $query = http_build_query($postfields);
    $url = ($method === 'GET')?($base_url.'?'.$query):$base_url;

    $ch=  curl_init();
    if($method === 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    if($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    }
    if(!empty($cookie_file)) {
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);  // read from
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);   // write to
    }
    curl_setopt($ch, CURLOPT_URL, $base_url);
    $user_agents = array(
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.142 Safari/535.19',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:11.0) Gecko/20100101 Firefox/11.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50',
        'Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; en) Presto/2.10.229 Version/11.62'
    );
    curl_setopt($ch, CURLOPT_USERAGENT, $user_agents[rand(0, 3)]);
    if(!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $html = curl_exec($ch);
    $http_code =  curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $return['http_code'] = $http_code;
    $return['html'] = $html;

    return $return;
}

function get_csrftocken_from_cookie($account){
    $csrftocken='';
    $filename=get_cookie_filename($account);
    $file_in=  fopen($filename, 'r');
    while(!feof($file_in)){
        $line=  fgets($file_in);
        if(!strncmp($line,'pinterest.com',13)){
            $arr=  stristr($line,'csrftoken');
            $csrftocken=str_replace('csrftoken',' ',$arr);
            $csrftocken=  trim($csrftocken);
            //echo $csrftocken;
            break;
        }
    }
    return $csrftocken;
}


function pinuploader($postitem){
    global $pinterest_config;
    $url=$pinterest_config->api->uploadpin;
    $pinterest_account=$pinterest_config->email;
    $cookie_file=  get_cookie_filename($pinterest_account);

    $result=  http_method($url,'POST', $cookie_file, array(), $postitem);
    if(is_array($result)&&$result['http_code']===200){
        $result['html']=json_decode($result['html'],TRUE);
    }
    return $result;
}

function boardcreator($postitem,$csrftoken){
    global $pinterest_config;
    $url=$pinterest_config->api->createboard;
    $pinterest_account=$pinterest_config->email;
    $cookie_file=  get_cookie_filename($pinterest_account);
    $headers = array(
                'Content-Type:application/x-www-form-urlencoded',
                'X-CSRFToken:'."$csrftoken",
                'X-Requested-With:XMLHttpRequest'
            );
    $result=  http_method($url,'POST', $cookie_file,$headers, $postitem);
    if(is_array($result)&&$result['http_code']===200){
        $result['html']=json_decode($result['html'],TRUE);
    }
    return $result;
}

//end_url:http://pinterest.com/pin/$pin_externalid/delete/
//referrer_url:http://pinterest.com/pin/$pin_externalid/edit/
function pindeleter($pin_externalid,$csrftoken){
    global $pinterest_config;
    $pinterest_home_page=$pinterest_config->homepage;
    $pinterest_account=$pinterest_config->email;
    $end_url=$pinterest_home_page.'pin/'."$pin_externalid".'/delete/';
    $referrer_url=$pinterest_home_page.'pin/'."$pin_externalid".'/edit/';
    $cookie_file=  get_cookie_filename($pinterest_account);
    $headers = array(
                'X-Pinterest-Referrer:'."$referrer_url",
                "X-CSRFToken:$csrftoken",
                'X-Requested-With:XMLHttpRequest'
            );

    $result=  http_method($end_url,'POST', $cookie_file,$headers, array());

    if(is_array($result)&&$result['http_code']===200){
        $result['html']=json_decode($result['html'],TRUE);
    }
    return $result;
}

//end_url:http://pinterest.com/shopinterest2/$boardname/settings/
//referrer_url:http://pinterest.com/pin/$boardname/edit/
function boardeleter($boardname,$csrftoken){
    global $pinterest_config;
    $pinterest_home_page=$pinterest_config->homepage;
    $pintereset_username=$pinterest_config->username;
    $pinterest_account=$pinterest_config->email;
    $end_url=$pinterest_home_page.$pintereset_username.'/'."$boardname".'/settings/';
    $referrer_url=$pinterest_home_page.'pin/'."$boardname".'/edit/';
    $cookie_file=  get_cookie_filename($pinterest_account);
    $headers = array(
                'X-Pinterest-Referrer:'."$referrer_url",
                "X-CSRFToken:$csrftoken",
                'X-Requested-With:XMLHttpRequest'
            );

    $result= http_method($end_url,'DELETE', $cookie_file,$headers, array());

    if(is_array($result)&&$result['http_code']===200){
        $result['html']=json_decode($result['html'],TRUE);
    }
    return $result;
}

//url : /pin/69172544247848366/
function getPinIdFromUrl($url){
    $pin_id='0';
    $strs=explode('/', $url);
    $pin_id=$strs[2];
    return $pin_id;
}

//http://pinterest.com/shopinterest2/djdeals/
function boardname_exists($boardname){
    global $pinterest_config;
    $pinterest_name=$pinterest_config->username;
    $home_page=$pinterest_config->homepage;
    $board_url=$home_page."$pinterest_name/".$boardname.'/';
    return checkRemoteFile($board_url);
}

function get_product_description($description){
    if(empty($description)){
        return PRODUCT_DESCRIPTION;
    }elseif(strlen($description)>500){
        return substr($description, 0, 500);
    }
    return $description;
}

function pinupdate($postitem,$pin_externalid,$pinterest_account){
    global $pinterest_config;
    $pinterest_home_page=$pinterest_config->homepage;
    $end_url=$pinterest_home_page.'pin/'."$pin_externalid".'/edit/';
    $cookie_file=  get_cookie_filename($pinterest_account);

    $result=  http_method($end_url,'POST', $cookie_file,array(), $postitem);

    if(is_array($result)&&$result['http_code']===200){
        $result['html']=json_decode($result['html'],TRUE);
    }
    return $result;
}

function prepare_pin_postitem($board_id,$product_id,$store_id,$pinterest_account,$account_dbobj){

    $store_obj = new Store($account_dbobj);
    $store_obj->findOne("id=$store_id");
    $subdomain=$store_obj->getSubdomain();
    $domain=$subdomain.'.'.getSiteDomain();

    $store_dbobj=  DBObj::getStoreDBObj($store_obj->getHost(), $store_obj->getId());
    $product_obj=new Product($store_dbobj);
    $product_obj->findOne('id='.$product_id);

    $csrftoken=get_csrftocken_from_cookie($pinterest_account);
    $board_obj=new PinterestBoard($account_dbobj);
    $board_obj->findOne('id='.$board_id);

    $postitem=array(
        'details'=>get_product_description($product_obj->getDescription()),
        'link'=>$domain ."/products/item?id=".$product_obj->getId(),
        'board'=>$board_obj->getExternalId(),//'69172612965664913',
        'csrfmiddlewaretoken'=>$csrftoken,
        'pin_replies'=>'',
        'buyable'=>'$'.$product_obj->getPrice(),
    );
    return $postitem;
}

function substitute($string, $data) {
    $m = new Mustache_Engine;
    return $m->render($string, $data);
}

function encrypt($string, $key='xxx')
{
    $result ='';
     for($i=0; $i<strlen($string); $i++)
    {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
      }

      return base64_encode($result);
}

function decrypt($string, $key='xxx')
{
    $result = '';
    $string = base64_decode($string);

    for($i=0; $i<strlen($string); $i++)
    {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $char = chr(ord($char)-ord($keychar));
        $result.=$char;
    }
    return $result;
}

function get_search_product_status($store_status,$product_status){
    $search_product_status=CREATED;
    if($store_status===ACTIVATED&&$product_status===CREATED){
        $search_product_status=ACTIVATED;
    }
    return $search_product_status;
}
//7-clothes,9-gifts,11-shoes,23-pants,24-apparel
function get_store_tag_from_ids_tags($ids_tags){
    $store_tag='';
    if(empty($ids_tags)){
        return $store_tag;
    }
    $id_tages=  explode(',', $ids_tags);
    foreach ($id_tages as $id_tage) {
        $tags=explode('-', $id_tage);
        $store_tag.=','.$tags[1];
    }
    $store_tag= trim($store_tag,',');
    return $store_tag;
}

function strtotime2($str) {
    date_default_timezone_set('America/Los_Angeles');
    return strtotime($str);
}

// array: two-dimensional array
// sortby: the name of the key sorted by
function array_sort($array, $sortby) {

    $size = sizeof($array);

    for($i=0;$i<$size;$i++) {
        for($j=$i+1;$j<$size;$j++) {
            $temp = array();
            if($array[$i]['priority']<$array[$j]['priority']) {
                $temp = $array[$i];
                $array[$i] = $array[$j];
                $array[$j] = $temp;
            }
        }
    }
    return $array;
}

function split_pic_urls($pic_url){
    $record=array();

    $urls = explode(',', $pic_url);

    foreach($urls as $u){
        if(preg_match('/^.*(_t)\..*$/i', $u)){
            $record['thumbnail_img'] = $u;
        }
        else if(preg_match('/^.*(_c)\..*$/i', $u)){
            $record['closeup_img'] = $u;
        }
        else if(preg_match('/^.*(_b)\..*$/i', $u)){
            $record['board_img'] = $u;
        }
        else if(preg_match('/^.*(_f)\..*$/i', $u)){
            $record['mobile_img'] = $u;
        }
        else{
            $record['board_img'] = $u;
            $record['mobile_img'] = $u;
            $record['closeup_img'] = $u;
            $record['thumbnail_img'] = $u;
            break;
        }
    }
    //$record['board_img'] = get_valid_pinterest_image_url($record['board_img']);
    return $record;
}

function parse_signed_request($signed_request, $secret) {
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);

    // decode the data
    $sig = base64_url_decode($encoded_sig);
    $data = json_decode(base64_url_decode($payload), true);

    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
        error_log('Unknown algorithm. Expected HMAC-SHA256');
        return false;
    }

    // check sig
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) {
        error_log('Bad Signed JSON signature!');
        return false;
    }

    return $data;
}

function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}

function shorten_description($description, $readmore_link = NULL, $characters_limit=100) {
    if(strlen($description) > $characters_limit) {
        $return = '';
        $return .= substr(str_replace('"', '&quot;', $description), 0, $characters_limit);
        $return .= "...";
        if(!empty($readmore_link)){
            $return .= " <a href="."'$readmore_link'"."target='_blank'>Read More</a>";
        }
        return $return;
    }
    return $description;
}

function shorten_link($description, $url, $characters_limit=20) {
    $return = '';
    if(strlen($description) > $characters_limit) {
        $return = "<a href='$url' target='_blank'>".substr($description, 0, $characters_limit)."...</a>";
    } else {
        $return = "<a href='$url' target='_blank'>$description</a>";
    }
    return $return;
}

function migrate_merchant($merchant_id, $account_dbobj) {
    $profile = MerchantsMapper::getProfile($merchant_id, $account_dbobj);
    ddd("merchant $merchant_id profile:");
    ddd($profile);
    if(!empty($profile)) {
        $merchant_username = $profile['username'];
        ddd("merchant $merchant_id $merchant_username");
        $user = new User($account_dbobj);
        $user->findOne("username='".$account_dbobj->escape($merchant_username)."'");
        if($user->getId() === 0) {
            $user->setUsername($merchant_username);
            $user->setStatus($profile['merchant_status']);
            $user->setPassword($profile['password']);
            $user->setFirstName($profile['first_name']);
            $user->setLastName($profile['last_name']);
            $user->setPhone($profile['phone_number']);
            $user->setMerchantId($merchant_id);
            $user->save();
            ddd("merchant $merchant_id user saved ".$user->getId());
            if(!empty($profile['address_id'])) {
                $address = new Address($account_dbobj);
                $address->setId($profile['address_id']);
                BaseMapper::saveAssociation($user, $address, $account_dbobj);
                ddd("merchant $merchant_id save the user address assoc");
            } else {
                ddd("merchant $merchant_id address is empty");
            }
            if(!empty($profile['paypal_account_id'])) {
                $payment_account = new PaymentAccount($account_dbobj);
                $payment_account->findOne('paypal_account_id='.$profile['paypal_account_id']);
                if($payment_account->getId() === 0) {
                    $payment_account->setPaypalAccountId($profile['paypal_account_id']);
                    $payment_account->save();
                    ddd("merchant $merchant_id payment account created");
                } else {
                    ddd("merchant $merchant_id payment account already created");
                }
                BaseMapper::saveAssociation($user, $payment_account, $account_dbobj);
                ddd("merchant $merchant_id save user payment_account assoc");
            } else {
                ddd("merchant $merchant_id no paypal account");
            }
        } else {
            ddd("merchant $merchant_id $merchant_username already exists in users table, no need to migrate");
        }
    } else {
        ddd("merchant $merchant_id profile is empty, no migration is needed");
    }
}

function get_email_username($email) {
    $parts = explode('@', $email);
    return $parts[0];
}

function exportCSV($data, $filename, $col_headers = array(), $return_string = false){

    header("Content-type: text/csv");
    header("Cache-Control: no-store, no-cache");
    header('Content-Disposition: attachment; filename='.$filename.'.csv');

    $stream = ($return_string) ? fopen ('php://temp/maxmemory', 'w+') : fopen ('php://output', 'w');

    if (!empty($col_headers)){
        fputcsv($stream, $col_headers);
    }

    foreach ($data as $record){
        fputcsv($stream, $record);
    }

    if ($return_string){
        rewind($stream);
        $retVal = stream_get_contents($stream);
        fclose($stream);
        return $retVal;
    } else {
        fclose($stream);
    }
}

function generate_product_quantity_key($store_id, $product_id, $session_id) {
    return $store_id.':'.$product_id.':'.$session_id.':quantity';
}

function get_product_quantity_keys($store_id, $product_id) {
    return $store_id.':'.$product_id.':*:quantity';
}

function get_valid_pinterest_image_url($url) {
    if((!strpos($url, 'pinterest.com') && !strpos($url, 'pinimg.com')) || url_exists($url)) {
        return $url;
    }
    $parts = parse_url($url);
    $new_hosts = array('media-cache-ec0.pinterest.com', 'media-cache-ec1.pinterest.com',
        'media-cache-ec0.pinimg.com', 'media-cache-ec0.pinimg.com');
    $found = false;
    foreach($new_hosts as $new_host) {
        $parts['host'] = $new_host;
        $new_url = http_build_url($parts);
        if(url_exists($new_url)) {
            $found = true;
            break;
        }
    }
    return $found?$new_url:$url;
}

function get_valid_pinterest_image_urls($urls) {
    foreach($urls as $i=>$url) {
        $urls[$i] = get_valid_pinterest_image_url($url);
    }
    return $urls;
}

function get_valid_pinterest_image_string($str) {
    $urls = explode(',', $str);
    $urls = get_valid_pinterest_image_urls($urls);
    return implode(',', $urls);
}

function get_abtests_cookie($cookie_name='abtests_number') {
    if(empty($_COOKIE[$cookie_name])) {
        $number = rand(1, 100);
        $_COOKIE[$cookie_name] = $number;
        setcookie($cookie_name, $number);
        return $number;
    } else {
        return intval($_COOKIE[$cookie_name]);
    }
}

function get_abtests_version($name, $dbobj) {
    $abtest = new Abtest($dbobj);
    $abtest->findOne("name='".$dbobj->escape($name)."' and status=".CREATED);;
    if($abtest->getId() !== 0) {
        $num_shards = $abtest->getNumShards();
        $cookie_number = empty($_REQUEST['version'])?get_abtests_cookie():$_REQUEST['version'];
        return $cookie_number % $num_shards;
    }
    return false;
}

function imagerotate2($src_image) {
    $image = imagecreatefromstring(file_get_contents($src_image));
    $exif = exif_read_data($src_image);
    if(!empty($exif['Orientation'])) {
        switch($exif['Orientation']) {
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
        }
    }
    imagejpeg($image, $src_image, 100);
    imagedestroy($image);
}


//use zcat jobtest-20121001.bak.gz | more | grep -i "Current database:" to check db exits
function backupjobdb($outputpath, $dbname='job') {

    $timestamp = get_timestamp();
    $backup_code = "tar -zcvPf $outputpath/$dbname-$timestamp.tar.gz /var/lib/mysql;";
    exec($backup_code, $output=array(), $results);
    if($results === 0) {
        debug("BACKUPDB:$dbname SUCCESS");
    } else {
        debug("BACKUPDB:$dbname FAILED");
    }
}


function get_table_schema($table_name, $type='job') {

    if($type !== 'account' && $type !== 'job' && $type !== 'store') {
        die("ERROR: createDBIfNotExist($type)\n");
    }
    $sql_str = file_get_contents(__DIR__."/../configs/$type.sql");
    $sqls = explode(';', $sql_str);
    foreach($sqls as $sql) {
        if(strpos($sql, "`$table_name`")) {
            return $sql.';';
        }
    }
}

function drop_old_table($table_schema, $table_name1, $table_name2, $days=7) {

    $limit = getNdaysago($days);
    $select_table = "select concat('drop table ',TABLE_SCHEMA,'.',TABLE_NAME) from information_schema.tables
            where TABLE_SCHEMA = '".$table_schema."'
            and (TABLE_NAME like '${table_name1}_%' or TABLE_NAME like '${table_name2}_%')
            and CREATE_TIME < '".$limit."'";
    $conn = 'mysql -uroot';
    $drop_table = 'echo '."\"$select_table\"". "| $conn | grep -v 'concat' | xargs $conn";
    exec($drop_table);
}

function getServerAddress() {
    return gethostbyname(gethostname());
}

// this function is solving the problem of the php function "empty" which can not
// take a function as a parameter
function empty2($mixed) {
    return empty($mixed);
}

function default2Int(&$var, $default = 0) {
    return $var ? intval($var) : $default;
}

function default2String(&$var, $default = '') {
    return trim($var ?: $default);
}

function default2Array(&$var, $default = array()) {
    return $var ?: $default;
}

function default2Bool(&$var, $default = false) {
    return $var ?: $default;
}

function set2bool(&$var) {
    return $var ? 1 : 0;
}
// only for logs & jobs tables in job db now
function rotate_tables($table_name) {

    global $dbconfig;
    $db = DB::getInstance($dbconfig->job->host, $dbconfig->job->name, $dbconfig->job->user, $dbconfig->job->password);
    $allowed_tables = array('jobs', 'logs');
    if (!in_array($table_name, $allowed_tables)) {
        Log::write(ERROR, 'Try to rotate a table not logs or jobs in job db', true);
        return false;
    }

    Log::write(INFO, "rotate table: $table_name", true);

    $renamed_table_name = $table_name . '_' . get_timestamp();
    $sql = "rename table $table_name to $renamed_table_name";
    if (!$db->query($sql)) {
        Log::write(ERROR, "fail on rename the table from $table_name to $renamed_table_name", true);
        return false;
    }

    Log::write(INFO, "renamed the table from $table_name to $renamed_table_name");

    $sql = DB::show_create_table('job', $table_name);
    if (!$db->query($sql)) {
        Log::write(ERROR, "fail on creating the table $table_name", true);
        return false;
    }

    Log::write(INFO, "created the table $table_name", true);

    return $renamed_table_name;
}

function rotate_logs() {
    if (!rotate_tables('logs')) {
        Log::write(ERROR, 'rotate logs failed', true);
        return false;
    }
    Log::write(INFO, 'rotate logs succeeded', true);
    return true;
}

function rotate_jobs() {
    $renamed_table_name = rotate_tables('jobs');
    if (!$renamed_table_name) {
        Log::write(ERROR, 'rotate jobs failed', true);
        return false;
    }

    global $dbconfig;
    $db = DB::getInstance($dbconfig->job->host, $dbconfig->job->name, $dbconfig->job->user, $dbconfig->job->password);
    $sql = "insert into jobs select * from $renamed_table_name where status=0 or status=4";
    if (!$db->query($sql)) {
        Log::write(ERROR, 'failed to move unprocessed jobs to newly created jobs table ' . $renamed_table_name, true);
        return false;
    }

    Log::write(INFO, 'move unprocessed jobs to newly created jobs table ' . $renamed_table_name, true);
    Log::write(INFO, 'rotate jobs succeeded', true);
    return true;
}

function format_get_image($camel) {
    return preg_replace("/setImage/i", "_image_", $camel);
}

//input: http://media-cache-ec3.pinimg.com/45x45/3d/da/9a/3dda9ae34a91689b9ee60206233250aa.jpg
function get_image_url_from_exist_image($exist_image_url) {
    $return = array();
    $return['image_45'] = preg_replace("/\d{2,3}x\d{0,2}/", "45x45", $exist_image_url);
    $return['image_70'] = preg_replace("/\d{2,3}x\d{0,2}/", "70x", $exist_image_url);
    $return['image_192'] = preg_replace("/\d{2,3}x\d{0,2}/", "192x", $exist_image_url);
    $return['image_236'] = preg_replace("/\d{2,3}x\d{0,2}/", "236x", $exist_image_url);
    $return['image_550'] = preg_replace("/\d{2,3}x\d{0,2}/", "550x", $exist_image_url);
    $return['image_736'] = preg_replace("/\d{2,3}x\d{0,2}/", "736x", $exist_image_url);
    return $return;
}

function http_method2($base_url, $assoc = false , $method = 'GET', $cookie_file = '', $postfields = array(), $headers = array()){
    $response = http_method($base_url, $method, $cookie_file, $postfields, $headers);
    if($response['http_code'] === 200) {
        if($assoc) {
            return json_decode($response['html'], true);
        }
        return $response['html'];
    }
    return false;
}

function get_product_image_name($salt, $image_type) {
    return $salt.'_'.$image_type.'.jpg';
}

function get_product_image_upload_dst($store_id, $uniqid, $image_type) {
    global $fileuploader_config, $amazonconfig;
    $bucket = $fileuploader_config->store_bucket;
    $stores_folder = $amazonconfig->s3->stores_folder;
    $file_name = get_product_image_name($uniqid, $image_type);
    return $dst = "/$bucket/$stores_folder/$store_id/$file_name";
}

function get_csv_file_name($salt) {
    return $salt.'.csv';
}

function get_store_csv_file_upload_dst($store_id, $uniqid) {
    global $fileuploader_config, $amazonconfig;
    $bucket = $fileuploader_config->store_bucket;
    $stores_folder = $amazonconfig->s3->stores_folder;
    $file_name = get_csv_file_name($uniqid);
    return $dst = "/$bucket/$stores_folder/$store_id/$file_name";
}

function is_pinterest_image_url($url) {
    return preg_match('/(.*pinimg\.com.*|.*pinterest\.com.*)/', $url);
}

function stored_in_s3($url) {
    return preg_match('#(.*s3\.amazonaws\.com/shopinterest_stage.*|.*s3\.amazonaws\.com/shopinterest_production.*)#', $url);
}

function stored_in_s3_stores($url) {
    return preg_match('#(.*s3\.amazonaws\.com/shopinterest_stage/stores/.*|.*s3\.amazonaws\.com/shopinterest_production/stores/.*)#', $url);
}

function checkRemoteFileIsImage($url) {
    $response = http_method($url);
    return @imagecreatefromstring($response['html']);
}

function scale_image($original_image_width, $original_image_height, $converted_image_width) {
    list($w,$h)=array($original_image_width,$original_image_height);

    if ($original_image_width >= $converted_image_width) {
        if ($original_image_width > 0) $ratios = $converted_image_width/$original_image_width;
        $w = intval($original_image_width * $ratios);
        $h = ($w === CONVERTED45) ? CONVERTED45 : intval($original_image_height * $ratios);
    }

    return array($w,$h);
}

function get_converted_image_path($path, $salt, $type, $format='jpg') {
    return $path.$salt."_$type.$format";
}

function convert($origin_image, $type, $salt, $output_path = '/tmp/') {
    $image_obj = new Imagick($origin_image);

    list($width, $height) = scale_image($image_obj->getimagewidth(), $image_obj->getimageheight(), $type);
    $image_obj->resizeimage($width, $height, Imagick::FILTER_LANCZOS, 1);
    //$image_obj->thumbnailimage($width, $height, $bestfit = TRUE);
    $image_obj->setformat('JPEG');
    $image_obj->setCompressionQuality(100);
    $output_image = get_converted_image_path($output_path, $salt, $type);
    $image_obj->writeimage($output_image);
    //$image_obj->writeimages($filename, $adjoin);
    $image_obj->clear();
    $image_obj->destroy();
    return $output_image;
}


// input :
// $images = array(
//  'src' => '',
//  'dst' => '',
//  'image_type' => ''
// );
function upload_image2($images) {

    $image_urls = array();

    foreach ($images as $image) {
        $src_url = $image['src'];
        $dst_url = $image['dst'];
        $type = $image['image_type'];

        if($stored_url = upload_image($dst_url, $src_url)) {
            $image_urls[$type] = $stored_url;
        }
    }
    return $image_urls;
}

function trim2($item, $charlist=" \t\n\r\0\x0B") {
    if(is_array($item)) {
        foreach($item as $key => $val) {
            $item[$key] = trim($val, $charlist);
        }
    } else {
        $item = trim($item, $charlist);
    }
    return $item;
}

function uuid() {
    return uniqid().time().uniqid();
}

// s3 file_path csv/159/stores_159_csv.csv
// filepicker file_path https://www.filepicker.io/api/file/TJj6GxHGSJSgePrBdy6G
function get_csv_from_remote($file_path) {

    $file_name = '/tmp/csv/'.uuid().'.csv';

    // from s3
    if(preg_match('#csv/\d{0,}/stores_\d{0,}_csv.csv#', $file_path)) {
        global $fileuploader_config;
        $bucket_name = $fileuploader_config->store_bucket;
        download_file_from_s3($bucket_name, $file_path, $file_name);
    }

    // from filepicker
    if(preg_match('#www.filepicker.io/api/file/#', $file_path)) {
        copy($file_path, $file_name);
    }

    if(file_exists($file_name)) {
        return $file_name;
    }
    return false;
}

function timer($tag='') {
    if(!empty($tag)) {
        $tag .= " : ";
    }
    ddd($tag.time()) ;
}


function is_json($string) {
    if(is_string($string)) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    } else {
        return false;
    }

}

function is_singular($word) {
    if(depluralize($word) === $word) {
        return true;
    }
    return false;
}

function depluralize($word){
    // Here is the list of rules. To add a scenario,
    // Add the plural ending as the key and the singular
    // ending as the value for that key. This could be
    // turned into a preg_replace and probably will be
    // eventually, but for now, this is what it is.
    //
    // Note: The first rule has a value of false since
    // we don't want to mess with words that end with
    // double 's'. We normally wouldn't have to create
    // rules for words we don't want to mess with, but
    // the last rule (s) would catch double (ss) words
    // if we didn't stop before it got to that rule.
    $rules = array(
        'ss' => false,
        'os' => 'o',
        'ies' => 'y',
        'xes' => 'x',
        'oes' => 'o',
        'ies' => 'y',
        'ves' => 'f',
        'ses' => 's',
        's' => '');
    // Loop through all the rules and do the replacement.
    foreach(array_keys($rules) as $key){
        // If the end of the word doesn't match the key,
        // it's not a candidate for replacement. Move on
        // to the next plural ending.
        if(substr($word, (strlen($key) * -1)) != $key)
            continue;
        // If the value of the key is false, stop looping
        // and return the original version of the word.
        if($key === false)
            return $word;
        // We've made it this far, so we can do the
        // replacement.
        return substr($word, 0, strlen($word) - strlen($key)) . $rules[$key];
    }
    return $word;
}

function startsWith($haystack, $needle)
{
    return $needle === "" || strpos($haystack, $needle) === 0;
}
function endsWith($haystack, $needle)
{
    return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
}

function array_key_endswith($array, $string) {
    $keys = array_keys($array);
    foreach($keys as $key) {
        if(endsWith($key, $string)) {
            return true;
        }
    }
    return false;
}

function array_value_endswith($array, $string) {
    foreach($array as $value) {
        if(endsWith($value, $string)) {
            return true;
        }
    }
    return false;
}

function valid_associate_objects($obj1_name, $obj2_name) {
    $singular_obj1_name = depluralize($obj1_name);
    $singular_obj2_name = depluralize($obj2_name);
    if($associate_info = associate_objects($obj1_name, $obj2_name)) {
        if($associate_info['where'] === 'parent' && ($singular_obj2_name !== $obj2_name)) {
            return false;
        } else if($associate_info['where'] === 'child' && ($singular_obj2_name === $obj2_name)) {
            return false;
        } else if($associate_info['where'] === 'associate' && ($singular_obj2_name === $obj2_name)) {
            return false;
        }
        return $associate_info;
    }
    return false;
}

function associate_objects($obj1_name, $obj2_name) {
    $singular_obj1_name = depluralize($obj1_name);
    $singular_obj2_name = depluralize($obj2_name);
    $plural_obj1_name = to_plural($singular_obj1_name);
    $plural_obj2_name = to_plural($singular_obj2_name);

    $obj1_properties = table_info($plural_obj1_name);
    $obj2_properties = table_info($plural_obj2_name);

    if(empty($obj1_properties) || empty($obj2_properties)) {
        return false;
    }

    $parent_associate_field = $singular_obj2_name.'_id';
    $child_associate_field = $singular_obj1_name.'_id';
    $associate_table_name = $plural_obj1_name.'_'.$plural_obj2_name;

    if(in_array($parent_associate_field, $obj1_properties)) {
        return array('where' => 'parent', 'field' => $parent_associate_field);
    } else if(in_array($child_associate_field, $obj2_properties)) {
        return array('where' => 'child', 'field' => $child_associate_field);
    } else if(!empty2(table_info($associate_table_name))) {
        return array('where' => 'associate');
    }
    return false;
}

function errmsg($errno, $data) {
    return substitute($GLOBALS['errors'][$errno]['msg'], $data);
}

function array_push2(&$array, $val) {
    if(!is_array($val)) {
        $var = array($val);
    }
    return $array = array_merge($array, $val);
}

if (!function_exists('array_column')) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

}

function filter_json_array($input_array, $filter, $name) {
    
    if($name == depluralize($name)) {
        $input_array = array($input_array);
    }
    $t = array();
    foreach ($input_array as $i => $input) {
        $t[$i] = array_intersect_key2($input, $filter, $name);

        foreach ($t[$i] as $key => $sub_array) {
            if(is_array($sub_array)) {

                $singular_key = depluralize($key);
                if(array_key_exists($singular_key, $filter)) {
                    $t[$i][$key] = filter_json_array($input[$key], $filter, $key);
                } else {
                    unset($t[$i][$key]);
                }
            }
        }
    }

    return $t;
}

function array_intersect_key2($input, $filter, $name) {
    $name = depluralize($name);
    if(!is_array($filter[$name])) {
        return FALSE;
    }

    return array_intersect_key($input, $filter[$name]);

}

function explode3($delimiter, $string) {
    if(empty($string)) {
        return array();
    }
    return explode($delimiter, $string);
}

function setifnotset(&$array, $k, $v){
    if(!isset($array[$k])){
        $array[$k] = $v;
    }
}

function get_image_url_from_pin_id($account_dbobj, $pinterest_pin_id) {

    $pin_image = new PinImage($account_dbobj);
    $pin_image->findOne("pinterest_pin_id = $pinterest_pin_id");
    $image_736_url = $pin_image->getImage_736();

    if(empty($image_736_url)) {
        $pin = new PinterestPin($account_dbobj);
        $pin->findOne('id='.$pinterest_pin_id);
        $pin_external_id = $pin->getExternalId();
        $pinterest_pin_page = new PinterestPinPage($pin_external_id);
        $pin_info = $pinterest_pin_page->getPinInfo();
        $image_736_url = !empty($pin_info['image_736']) ? $pin_info['image_736'] : '';
    }
    return $image_736_url;
}

function convertImage($picture_url) {

    $filepicker = new Filepicker();
    $return = array();

    $image_input = $picture_url;
    $image_type = array(CONVERTED45, CONVERTED70, CONVERTED192, CONVERTED236, CONVERTED550, CONVERTED736);

    $resource = $filepicker->store_image($image_input);

    foreach ($image_type as $i => $type) {
        $options = array(
            'fit' => 'max',
            'w' => $type,
            'format' => 'jpg',
            'quality' => '100'
        );
        if($type === CONVERTED45) {
            $options['h'] = $type;
            $options['fit'] = 'crop';
        }
        $converted_image_url = $filepicker->convert_image($resource, $options);
        if(@file_get_contents($converted_image_url)) {
            $return[$i]['converted_image_url'] = $converted_image_url;
            $return[$i]['converted_image_type'] = $type;
        } else {
            return array();
        }
    }

    return $return;
}

function uploadConvertedImageToS3(&$converted_images, $store_id) {
    global $amazonconfig;

    $base_url = $amazonconfig->api->s3->url;
    foreach($converted_images as $i => $converted_image) {
        $src_url = $converted_image['converted_image_url'];
        $dst_url = get_product_image_upload_dst($store_id, uniqid(), $converted_image['converted_image_type']);
        if(upload_image($dst_url, $src_url)) {
            $converted_images[$i]['converted_image_url'] = $base_url.$dst_url;
        }
    }
}

// input $image = array(
//  'url' => ''
//  'type' => ''
// )
function uploadImageToS3IfNotExist(&$image, $store_id) {

    $src_url = $image['url'];

    if(!stored_in_s3_stores($src_url) || !checkRemoteFileIsImage($src_url)) {
        $dst_url = get_product_image_upload_dst($store_id, uniqid(), $image['type']);

        if($stored_image_url = upload_image($dst_url, $src_url)) {
            $image['url'] = $stored_image_url;
            $image['uploaded'] = true;
        }
    }
}

function isSecureConnect() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
}

function https_enable() {
    global $site_https_enable;
    // check secure (only on production or staging, not for development env)
    return $site_https_enable === 'true' && ($_SERVER['SERVER_PORT'] === '80' || $_SERVER['SERVER_PORT'] === '443');
}

function getProtocol($page = '') {
    global $page_acl;

    if(php_sapi_name() === 'cli' || !https_enable()) {
        return "http://";
    }

    if(array_key_exists($page, $page_acl)) {
        if($page_acl[$page]['scheme'] === HTTPS) {
            return 'https://';
        }
        return 'http://';
    }
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
    isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
}


// $array = array(
//     0 => array(
//         'key1' => '',
//         'key2' => ''
//     )
//     1 => array(
//         'key1' => '',
//         'key2' => ''
//     )
// )
// $keys is the list of keys that need to be unset

function unset_array_keys($array, $keys) {
    $new_array = array();
    foreach($array as $arr) {
        foreach($arr as $key => $val) {
            if(in_array($key, $keys)) {
                unset($arr[$key]);
            }
        }
        array_push($new_array, $arr);
    }
    return $new_array;
}

function _u($str){
    return htmlentities($str,  ENT_QUOTES, "UTF-8");
}

function currency_symbol($code){
    global $currencies;
    if($code === "AUD") return "AUD $";
    if($code === "CAD") return "CAD $";
    if($code === "USD") return "USD $";
    if(empty($code) || !array_key_exists($code, $currencies)) {
        return 'USD $';
        //return "BAD CURRENCY";
    }
    return _u($currencies[$code]['symbol']);
}

function dump_simple_array($data, $depth = 0){
    if(is_array($data)){
        $lines = array(str_repeat("\t", $depth) . "array(");
        foreach($data as $k => $v){
            $k = dump_simple_array($k, $depth + 1);
            $v = dump_simple_array($v, $depth + 1);
            $lines[] = str_repeat("\t", $depth) . "$k\t=>\t$v,";
        }
        $lines[] = str_repeat("\t", $depth) . ")";
        return implode("\n", $lines);
    } else if(is_numeric($data)){
        return $data;
    } else if(is_string($data)){
        return '"' . str_replace('"', "\\\"", $data) . '"';
    }
    return '"unknow"';
}

function get_table_info($force = false){

    global $redis;
    if(!$force && $value = $redis->get('db_schema')) {
        return json_decode($value, true);
    }

    $reg_table_start = "/CREATE\s+TABLE\s+`(.+?)`/";
    $reg_table_end = "/\)\s?.+;/";
    $reg_table_field = "/^\s*?`(.+?)`\s+(\w+).+,?/";
    $reg_table_uk = "/^\s*UNIQUE KEY `.*?` \(`(.*?)`\)/";

    $return = array();
    $match = array();
    $types = array(
        'account', 'store', 'job',
    );
    foreach($types as $type){
        $sql_text = file_get_contents(__DIR__ . "/../configs/$type.sql");
        $lines = explode("\n", $sql_text);
        $table = null;
        foreach($lines as $l){
            if(preg_match($reg_table_start, $l, $match)){
                $table = array(
                    "__name" => $match[1],
                    "__db_type" => $type,
                    "__unique_keys" => array(),
                );
            } else if(preg_match($reg_table_end, $l, $match)){
                if(empty($table)) continue;
                $return[$table['__name']] = $table;
                $table = null;
            } else if(preg_match($reg_table_field, $l, $match)){
                if(empty($table)) continue;
                $table[$match[1]]=$match[2];
            } else if(preg_match($reg_table_uk, $l, $match)){
                if(empty($table)) continue;
                $table['__unique_keys'][] = preg_split("/\s*`\s*,\s*`\s*/", $match[1]);
            }
        }
    }
    $redis->set('db_schema', json_encode($return));
    return $return;
}

function table_info($table) {
    $ret = get_table_info();
    $ret = $ret[$table];
    unset($ret["__name"]);
    unset($ret["__db_type"]);
    unset($ret["__unique_keys"]);
    return array_keys($ret);
}

function multi($v, $w){
    return $v * $w;
}

function array_combinate($data) {
    if(count($data)<1) return array();
    $size = 1;
    foreach($data as $col){
        $size = $size * count($col);
    }
    if($size<1) return array();
    $ret = array_fill(0, $size, array());
    $width = count($data);
    $cols = array_map("count", $data);
    for($n = 0; $n < $width; $n++){
        $p = 1;
        if($n < $width-1) {
            $p = array_reduce(array_slice($cols, $n + 1), "multi", 1);
        }
        for($r = 0; $r < $size; $r++){
            $index = ((int)($r/$p))%($cols[$n]);
            $ret[$r][$n] = $data[$n][$index];
        }
    }
    return $ret;
}

function get_entity_mapper_class($entity) {
    return ucfirst(to_camel_case(to_plural($entity))).'Mapper';
}

function get_entity_datatable_class($entity) {
    return ucfirst(to_camel_case(to_plural($entity))).'Datatable';
}

function serializeObject($object) {
    return base64_encode(serialize($object));
}

function unSerializeObject($string) {
    return unserialize(base64_decode($string));
}

function is_authenticated($auth_token = 'dummy') {
    
    $base_url = 'https://shopintoit.firebaseio.com/'; 
    $path = '/name';
    $firebase = new Firebase($base_url, $auth_token);
    $response = $firebase->get($path);
    return $response === '"shopintoit"';
}

function get_product_ck($store_dbname, $product_id) {
    return CacheKey::q($store_dbname.".product?id=".$product_id);
}

function is_store_subdomain_action($action='') {
    return !in_array($action, array('info', 'product-item'));
}

// path: /var1/var2/var3...
function get_path_parts($path) {
    $return = array();
    $parts = explode('/', $path);
    foreach($parts as $part) {
        if(!empty($part)) {
            $return[] = $part;
        }
    }
    return $return;
}

// array(1, 2, 3) after array_shift_over(array(1, 2, 3), 1)
// array(1, 3)
function array_shift_over($array, $index) {

    $size = sizeof($array);
    if($index >= $size || $index < 0) {
        return $array;
    } else {
        for($i=$index;$i<$size;$i++) {
            if($i === ($size-1)) {
                unset($array[$i]);
            } else {
                $array[$i] = $array[$i+1];
            }
        }
    }
    return $array;
}

function checkIsSet() {
    $args = func_get_args();
    if(func_num_args() < 2 || !is_array($array = $args[0]) || empty($array)) {
        return false;
    }
    
    $args = func_get_args();
    for($i=1;$i<func_num_args();$i++) {
        if(!array_key_exists($args[$i], $array)) {
            return false;
        }
    }
    return true;
    
}

function url_append($data) {
    $query = array_merge($_GET, $data);
    $query_str = http_build_query($query);
    $request_path = default2String($_SERVER['REDIRECT_URL']);
    $request_path = preg_replace("/\?.*$/", "", $request_path);
    $query_str = $request_path . '?'.$query_str;
    return $query_str;
}

function getExpMonthList() {
    for ($i = 1; $i < 13; $i++) {
        $si[] = sprintf("%02d", $i);
    }
    return $si;
}

function getExpYearList() {
    $y = get_current_year();
    for($i=0; $i<12; $i++) {
        $si[] = sprintf("%02d", $y+$i);
    }         
    return $si;
}

function json2AssocArray(&$jsonData) {
    $a = json_decode($jsonData, true);
    return $jsonData = is_array($a) ? $a : array();
}

// this function only support pass args by value
function json2AssocArrayX() {
    $args = func_get_args();
    foreach($args as $arg) {
        json2AssocArray($arg);
    }
}

// return the store subdomain & product id
function parseProductUrl($product_url) {
    $matches = array();
    preg_match("#.*/store/([^/]+)/products/item\?id=(\d+)#i", $product_url, $matches);
    if(empty($matches[1]) || empty($matches[2])) {
        return false;
    }
    
    return array('subdomain' => $matches[1], 'store_subdomain' => $matches[1], 'product_id' => $matches[2]);
}

function getTableObjectSelectOptions() {
    $tableConfigs = DatatableService::getTableConfigs();
    $options = array();
    $i = 0;
    foreach($tableConfigs as $key => $value) {
        if(!strpos($key, '_featured_products')) {
            continue;
        }
        $options[$i]['text'] = getUnderscoreName($key);
        $options[$i]['value'] = $key;
        $i++;
    }
    
    return $options;
}

function getUnderscoreName($name) {
    $name = ucwords(str_replace('_', ' ', $name));
    return $name;
}

function arrayFirstKey($array) {
    reset($array);
    return key($array);
}

function XMLPageToJSON($url) {
    $fileContents = file_get_contents($url);
    XMLToJSON($fileContents);
}

function XMLToJSON($string) {

    $string = str_replace(array("\n", "\r", "\t"), '', $string);

    $string = trim(str_replace('"', "'", $string));

    $simpleXml = simplexml_load_string($string);

    $json = json_encode($simpleXml);

    return $json;
}

function is_store_dbname($dbname) {
    global $dbconfig;
    $store_dbname = $dbconfig->store->name;
    $store_dbname_reg = '/^'.$store_dbname.'_\d+$/';
    if($dbname === $store_dbname) {
        return true;
    }
    return preg_match($store_dbname_reg, $dbname);
}

function parse_store_dbname($dbname) {
    global $dbconfig;
    $store_dbname = $dbconfig->store->name;
    $store_dbname_reg = '/^'.$store_dbname.'_(\d+)$/';
    preg_match($store_dbname_reg, $dbname, $match);
    return isset($match[1]) ? $match[1] : 0;
}

function need_admin($table_object) {
    if(endsWith($table_object, '_featured_products')){
        return true;
    }
    return false;
}

function parse_product_url($url) {

    global $site_merchant_url;

    $subdomain_product_id_reg = "/(http:\/\/)?".$site_merchant_url."\/store\/(.*)\/products\/item\?id=(\d+)/";
    if(preg_match($subdomain_product_id_reg, $url, $match)) {
        return array(
            'subdomain' => $match[2],
            'product_id' => $match[3]
        );
    };
    return false;
}

function parse_store_url($url) {

    global $site_merchant_url;

    $subdomain_reg = "#^(http://)?".$site_merchant_url."/store/([^/]+)#";
    if(preg_match($subdomain_reg, $url, $match)) {
        return array(
            'subdomain' => $match[2]
        );
    };
    return false;
}

// 2014-02-25 00:00:00 => 2014-02-25
function format_data_time($data) {
    return strftime('%F', strtotime2($data));
}

function npercent($num, $p = 100){
    return  ((int)($num * $p)) / 100.0;
}

// cloudinary utils
function cloudinary_store_misc_ns($store_id){
    // env:
    $env = 'e-t'; // for testing
    if(APPLICATION_ENV == 'production'){
        $env = 'e-p';
    } else {//if(APPLICATION_ENV == 'staging'){
        $env = 'e-s';
    }
    return "$env/s-s/s-$store_id/m/";
}

function cloudinary_store_product_ns($store_id, $product_id){
    // env:
    $env = 'e-t'; // for testing
    if(APPLICATION_ENV == 'production'){
        $env = 'e-p';
    } else {//if(APPLICATION_ENV == 'staging'){
        $env = 'e-s';
    }
    return "$env/s-s/s-$store_id/p/p-$product_id/";
}

function cloudinary_product_picture(&$product, $pic_name, $options = array()){
    $default_options = array("width" => 550, "crop" => "fill");
    $options = array_merge($default_options, $options);
    $ns = cloudinary_store_product_ns($product['store_id'], $product['id']);
    $ppid =  $ns . $pic_name . ".jpg";
    return cloudinary_url($ppid, $options);
}

function cloudinary_product_pictures(&$product, $options = array()){
    $return = array();
    $ns = cloudinary_store_product_ns($product['store_id'], $product['id']);
    foreach($product['pictures'] as $i => $p){
            if($i>40) continue;
            if(empty($p['name'])) continue;
            $return[] = cloudinary_product_picture($product, $p['name'], $options);
    }
    return $return;
}

function format_price($currency, $amount){
    global $currencies;
    $prefix = "";
    if($amount < 0){
        $prefix = "- ";
        $amount = 0 - $amount;
    }
    if(isset($currencies[$currency])){
        $currency = currency_symbol($currency);
    }
    return $prefix . $currency . $amount;
}

function get_sessid(){
    return session_id();
    //return default2String($_COOKIE['PHPSESSID']);
}
