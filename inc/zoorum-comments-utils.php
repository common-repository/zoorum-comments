<?php
/**
 * Utility functions including calls
 *
 *
 * @package zoorum-comments
 * @since zoorum-comments 0.8
 */

/* number of comments */
function zoorum_get_post_count($wpid = 0) {
    $wpid = absint( $wpid );
    if ( !$wpid )
        $wpid = get_the_ID();
    $zoorum_post_count = 0;
    if (!$wpid) {
	    $zoorum_post_count = get_post_meta(get_the_ID(), 'zoorum_post_count', true);
    } else {
        $zoorum_post_count = get_post_meta($wpid, 'zoorum_post_count', true);
    }
	if( $zoorum_post_count ) {
		return $zoorum_post_count;
	}
	//$zoorum_post_count = get_post_meta(get_the_ID(), 'zoorum_post_count', true);
	return $zoorum_post_count;
}
function zoorum_set_post_count($number, $wpid = 0) {
    if ($wpid === 0) {
	    update_post_meta(get_the_ID(), 'zoorum_post_count', $number);
    } else {
        update_post_meta($wpid, 'zoorum_post_count', $number);
    }
    return ($number);
}
add_filter('get_comments_number', 'zoorum_get_post_count');



/* threadid */
function zoorum_set_threadid($threadid, $wpid = 0) {
    if ($wpid === 0) {
        update_post_meta(get_the_ID(), 'zoorum_threadid', $threadid);
	    
    } else {
        update_post_meta($wpid, 'zoorum_threadid', $threadid);
    }
    return ( intval($threadid));
}
function zoorum_get_threadid($wpid = 0) {
    $z_threadid;
    if ($wpid === 0) {
	    $z_threadid = get_post_meta(get_the_ID(), 'zoorum_threadid', true );
    } else {
        $z_threadid = get_post_meta($wpid, 'zoorum_threadid', true );
    }
	if( !$z_threadid ) {
	    return 0;
	}
	return( intval($z_threadid) );
}

/* Returns thread prepend which is used when a topic is created */
function zoorum_get_thread_prepend($z_wp_pid) {
    $categories = get_the_category($z_wp_pid);
    if($categories){
        $c_to_use = $categories[0];
        $break_cat_loop = FALSE;
        foreach($categories as $category) {
            $id_to_find = intval($category->parent);
            if ( !( $id_to_find == 0) ) {
                foreach($categories as $c) {
                    if( intval($c->term_id) == $id_to_find ) {
                        $c_to_use = $c;
                        $break_cat_loop = true;
                        break;
                    }
                }
            }
            if($break_cat_loop) {
                break;
            }
        }
        $output = $c_to_use->name;
        return($output.': ');
    }
    return __('page: ', 'zoorum-comments');
}

/* wrapper for call function to get thread. correctly sets postcounts and errormessages */
function zoorum_get_thread($z_wp_pid) {
	$z_threadid = zoorum_get_threadid($z_wp_pid);
    if( !$z_threadid ) {
        zoorum_set_post_count(0, $z_wp_pid);
        $response = array('data'=>__('pid has no zoorum thread', 'zoorum-comments'), 'status'=>'500');
        return $response;
    }
    
    $response = zoorum_call_get_thread($z_threadid);

    if( !(intval($response['status']) == 200) ) { // some type of problem
        if(intval($response['status']) == 500 || intval($response['status']) == 404) { //threadid finns ej
            $response['data'] = __('Thread have been deleted in Zoorum.', 'zoorum-comments');
        } elseif(intval($response['status']) == 302) { // korrekt subforum?
            $response['data'] = __('Could not connect to subforum', 'zoorum-comments');
        } elseif (!$response['status']) {
            $response['data'] = __('Could not connect to server', 'zoorum-comments');
        } else {
            $response['data'] = __('Unknown error', 'zoorum-comments');
        }
    } else {
        if ( $response['data'] == NULL ) {
            $response['status'] = 500;
        }
        foreach($response['data']->Posts as $z_post) {
            $z_post->ReadableDate = date_format(new DateTime($z_post->Date), get_option('date_format').' '.get_option('time_format'));
            $z_post->TimeDiffDate = human_time_diff( strtotime($z_post->Date), time() ). __(' ago', 'zoorum-comments');
        }
    }
    if( intval($response['status']) == 500  || intval($response['status']) == 404 ) { //threadid finns ej
        zoorum_set_post_count(0, $z_wp_pid);
        zoorum_set_threadid(0, $z_wp_pid);
    } else {
        zoorum_set_post_count($response['data']->ReplyCount, $z_wp_pid);
    }
    return $response;
}
/* wrapper for call function to create thread, correctly builds post data, errormessages and sets threadid */
function zoorum_create_thread($z_wp_post) {
    $z_wp_thecontent = strip_tags($z_wp_post->post_content);
    $z_wp_thecontent = strip_shortcodes( $z_wp_thecontent );
    $z_wp_thecontent = preg_replace('|[[\/\!]*?[^\[\]]*?]|si', '', $z_wp_thecontent);
    $z_wp_thecontent = html_entity_decode($z_wp_thecontent, ENT_NOQUOTES, 'UTF-8');

    $z_wp_thecontent_old_length = strlen($z_wp_thecontent);
    $z_wp_thelength = 200;
    $z_wp_thecontent = substr($z_wp_thecontent, 0, $z_wp_thelength);
    if ($z_wp_thecontent_old_length > $z_wp_thelength) {
        $z_wp_thecontent .= '...';
    }

    $z_text = sprintf(__('Article by %s on ', 'zoorum-comments'), get_the_author_meta('nickname', $z_wp_post->post_author) ); //TODO
    $z_text .= get_permalink($z_wp_post).PHP_EOL;
    $z_text .= $z_wp_thecontent;

    $z_title = zoorum_get_thread_prepend($z_wp_post->ID).$z_wp_post->post_title;

    if(strlen($z_title) > 75 ) {
        $z_title = substr($z_title, 0, 72);
        $z_title .= '...';
    }

    $tagstr = '';
    $tags = wp_get_post_tags($z_wp_post->ID);
    foreach($tags as $tag) {
        $tagstr .= $tag->name.', ';
    }
    $tagstr = rtrim($tagstr, ", ");

    $data = array();
    $data['hash'] = sha1(get_option('zoorum_api_key').$z_title);
    $data['title'] = $z_title;
    $data['text'] = $z_text;
    $data['tags'] = $tagstr;

    $response = zoorum_call_create_thread($data);

    if ( !(intval($response['status']) == 200) ) { // some type of problem
        if (intval($response['status']) == 401) { // Incorrect key
            $response['data'] = __('Incorrect API-key', 'zoorum-comments');
            //$response['data'] = wp_remote_retrieve_body($response);
        } elseif(intval($response['status']) == 302) { // korrekt subforum?
            $response['data'] = __('Could not connect to subforum', 'zoorum-comments');
        } else { //bad url (reply is bool false)
            $response['data'] = __('Could not connect to server', 'zoorum-comments');
        }
    }

    if( (intval($response['status']) == 200) && $response['data'] == NULL) {
        $response['status'] = 500;
    }
    if ( (intval($response['status']) == 200) ) { // Correct: set threadid
            zoorum_set_threadid($response['data'], $z_wp_post->ID);
    }
    return $response;
}





/* Call functions */

/**
 * Makes call to get a 'latest' feed
 * @return array with 'data'=response, 'status'=http-status-code
 */
function zoorum_call_get_feed() {
    $url = get_option('zoorum_url').'Feed/';
    $response = wp_remote_get($url, array(
        'method' => 'GET',
    ));
    $response = array('data'=>json_decode(wp_remote_retrieve_body($response)), 'status'=>wp_remote_retrieve_response_code($response));
    return $response;
}

/**
 * Makes call to create Thread
 * @return array with 'data'=response, 'status'=http-status-code
 */
function zoorum_call_create_thread($data) {
    $url = get_option('zoorum_url').'topic/api/create/';
    $response = wp_remote_post($url, array(
        'method' => 'POST',
        'body' => $data,
    ));
    $response = array('data'=>json_decode(wp_remote_retrieve_body($response)), 'status'=>wp_remote_retrieve_response_code($response));
    return $response;
}

/**
 * Makes call to get Thread
 * @return array with 'data'=response, 'status'=http-status-code
 */
function zoorum_call_get_thread($z_threadid) {
    $url = get_option('zoorum_url')."$z_threadid.json";
    $response = wp_remote_get($url, array(
        'method' => 'GET',
    ));
    $response = array('data'=>json_decode(wp_remote_retrieve_body($response)), 'status'=>wp_remote_retrieve_response_code($response));
    return $response;
}

?>