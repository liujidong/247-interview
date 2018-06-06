<?php

error_reporting(0);

function get_etsy_products($store_name) {
    $page = 1;
    while (!$run) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://www.etsy.com/shop/" . $store_name . "?page=" . $page);
//				模仿浏览器
//				curl_setopt($ch, CURLOPT_REFERER, "http://www.etsy.com/");
//				curl_setopt($ch, CURLOPT_USERAGENT, "");
//				伪造COOKIE，适合需登入的网站抓取
//				curl_setopt($ch, CURLOPT_COOKIEJAR, url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        $tmp_a = explode('class="clear listings ">', $output, 2);
        if ($tmp_a[1]) {
            $tmp_b = explode('class="listing-card">', $tmp_a[1]);
            unset($tmp_a);
            if ($tmp_b[1]) {



                foreach ($tmp_b as $key => $value) {
                    if ($key) {
                        $tmp_c = explode('listing-thumb', $value);
                        if ($tmp_c[1]) {
                            $tmp_d = explode('listing-title', $tmp_c[1]);
                            unset($tmp_c);
                            if ($tmp_d[0]) {
                                $tmp_x = explode('<img src=\'', $tmp_d[0]);
                                if ($tmp_x[1]) {
                                    $tmp_y = explode('\'', $tmp_x[1], 2);
                                    if ($tmp_y[0]) {
                                        $tmp_etsy['product_image'] = $tmp_y[0];
                                    }
                                }
                                unset($tmp_x);
                                unset($tmp_y);
                            }
                            if ($tmp_d[1]) {
                                $tmp_e = explode('listing-price', $tmp_d[1]);
                                if ($tmp_e[0]) {


                                    $tmp_x = explode('"title" title="', $tmp_e[0]);
                                    if ($tmp_x[1]) {
                                        $tmp_y = explode('" >', $tmp_x[1], 2);
                                        if ($tmp_y[0]) {
                                            $tmp_etsy['product_name'] = $tmp_y[0];
                                        }
                                    }
                                }

                                unset($tmp_x);
                                unset($tmp_y);
                                if ($tmp_e[1]) {

                                    $tmp_x = explode('currency-symbol">', $tmp_e[1]);
                                    if ($tmp_x[1]) {
                                        $tmp_y = explode('</span>', $tmp_x[1], 2);
                                        if ($tmp_y[0]) {
                                            $tmp_etsy['product_price'] = $tmp_y[0];
                                        }
                                    }
                                    $tmp_x = explode('currency-value">', $tmp_e[1]);
                                    if ($tmp_x[1]) {
                                        $tmp_y = explode('</span>', $tmp_x[1], 2);
                                        if ($tmp_y[0]) {
                                            $tmp_etsy['product_price'] = $tmp_etsy['product_price'] . $tmp_y[0];
                                        }
                                    }
                                    $tmp_x = explode('currency-code">', $tmp_e[1]);
                                    if ($tmp_x[1]) {
                                        $tmp_y = explode('</span>', $tmp_x[1], 2);
                                        if ($tmp_y[0]) {
                                            $tmp_etsy['product_price'] = $tmp_etsy['product_price'] . $tmp_y[0];
                                        }
                                    }
                                }
                            }
                            unset($tmp_x);
                            unset($tmp_y);
                            unset($tmp_e);
                            unset($tmp_d);
                        }
                        $get_etsy[] = $tmp_etsy;
                    }
                    unset($tmp_etsy);
                }
            } else {
                $run = '!';
            }
            unset($tmp_b);
        }
        $page++;
    }
    return $get_etsy;
}

print_r(get_etsy_products($_GET['name']));
?>




