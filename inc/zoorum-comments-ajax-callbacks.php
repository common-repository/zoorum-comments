<?php
/**
 * Ajax callbacks
 *
 *
 * @package zoorum-comments
 * @since zoorum-comments 0.8
 */

function zoorum_create_topic_callback() {
    $z_wp_pid = $_POST['zoorum_wp_pid'];
    if( !($z_wp_pid) ) { //none or 0 wp_pid passed
        echo json_encode(array('data'=>__('no pid passed', 'zoorum-comments'), 'status'=>'0'));
        die();
    }
    $z_wp_post = get_post($z_wp_pid);
    if( !($z_wp_post) ) {
        echo json_encode(array('data'=>__('no post with pid', 'zoorum-comments'), 'status'=>'0'));
        die();
    }
    if( zoorum_get_threadid($z_wp_pid) ) {
        echo json_encode(array('data'=>__('pid already has zoorum thread', 'zoorum-comments'), 'status'=>'0'));
        die();
    }
    if( $z_wp_post ) {
        $response = zoorum_create_thread($z_wp_post);
        $response = json_encode($response);
    }
    echo $response;
    die();
}

function zoorum_get_topic_callback() {
    $z_wp_pid = $_POST['zoorum_wp_pid'];
    if( !($z_wp_pid) ) { //none or 0 wp_pid passed
        echo json_encode(array('data'=>__('no pid passed', 'zoorum-comments'), 'status'=>'0'));
        die();
    }
    $z_wp_post = get_post($z_wp_pid);
    if( !($z_wp_post) ) {
        echo json_encode(array('data'=>__('no post with pid', 'zoorum-comments'), 'status'=>'0'));
        die();
    }
    $response = zoorum_get_thread($z_wp_pid);
    $response = json_encode($response);
    echo $response;
    die;
}

function zoorum_get_feed_callback() {
    $response = zoorum_call_get_feed();
    $response = json_encode($response);
    echo $response;
    die();
}

?>