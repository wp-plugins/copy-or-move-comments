<?php 
class copy_move_functions
{
    function get_posts($post_type)
    {
        global $wpdb;
        $data = array();

        $data = $wpdb->get_results( $wpdb->prepare( "select id, post_title from $wpdb->posts where post_status = 'publish' and post_type = %s order by id desc", $post_type ) );
        //$data = $wpdb->get_results("select id, post_title from $wpdb->posts where post_status = 'publish' and post_type='".$post_type."' order by id desc");
        return $data;
    }
    
    function get_all_comments_by_postid($id)
    {
        global $wpdb;
        $data = array();

        if(is_numeric($id))
        {        
            $data = $wpdb->get_results( $wpdb->prepare( "select comment_id, comment_author,comment_date,comment_content,comment_approved from $wpdb->comments where comment_post_id = %s order by comment_id desc", $id ) );
            //$data = $wpdb->get_results("select comment_id, comment_author,comment_date, comment_content from $wpdb->comments where comment_post_id = $id order by comment_id desc");
        }
        return $data;
    }
    
    function get_current_all_posts($post_type,$post_id)
    {
        global $wpdb;
        $post_types = get_post_types( '', 'names' );
                                //unset($post_types['attachment']);
                                unset($post_types['revision']);
                                unset($post_types['nav_menu_item']);
                   $post_type = "'".implode("','",$post_types)."'";
                        //DebugBreak();
        $data = array();
        $data = $wpdb->get_results( $wpdb->prepare("select ID, post_title from $wpdb->posts where post_status = 'publish' and post_type IN (%d) and ID NOT IN(SELECT ID FROM $wpdb->posts where ID=%s) order by id desc", $post_type,$post_id ) );
        //$data = $wpdb->get_results("select ID, post_title from $wpdb->posts where post_status = 'publish' and post_type IN ($post_type) and ID NOT IN(SELECT ID FROM $wpdb->posts where ID='$post_id') order by id desc");
        return $data;
    }
    
    function perform_action($source_post_id, $target_post_id, $get_action_type,$comment_id)
    {
        global $wpdb;
        
        if($get_action_type == 'move'){
            // update the comment_post_id to $target_post_id
        $sql[] = "update {$wpdb->comments} set comment_post_id = $target_post_id where comment_id IN ($comment_id)";
            
        //Decrement the comment_count in the $source_post_id
        $sql[] = "update {$wpdb->posts} set comment_count = comment_count-1 where id = $source_post_id and post_status = 'publish'";
        
        // Increment the comment_count in the $target_post_id
        $sql[] = "update {$wpdb->posts} set comment_count = comment_count+1 where id = $target_post_id and post_status = 'publish'";
        
        foreach($sql as $query)
        {
            $wpdb->query($wpdb->prepare($query));
        }
        
        }
        if($get_action_type == 'copy')
        {
              
           // $all_comments = $wpdb->get_results($wpdb->prepare( "select * from $wpdb->comments where comment_id IN (%s)", $comment_id ));
            $all_comments = $wpdb->get_results("select * from $wpdb->comments where comment_id IN ($comment_id)");
            foreach($all_comments as $data1)
            {
                $data = array(
              'comment_post_ID' => $target_post_id,
              'comment_author' => $data1->comment_author,
              'comment_author_email' => $data1->comment_author_email,
              'comment_author_url' => $data1->comment_author_url,
              'comment_content' => $data1->comment_content,
              'comment_type' => $data1->comment_type,
              'comment_parent' => $data1->comment_parent,
              'user_id' => $data1->user_id,
              'comment_author_IP' => $data1->comment_author_IP,
              'comment_agent' => $data1->comment_agent,
              'comment_date' => $data1->comment_date,
              'comment_approved' => $data1->comment_approved,
              );    
              wp_insert_comment($data);
            }
        }
    }
}
?>