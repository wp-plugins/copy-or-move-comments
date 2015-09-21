<?php 
/*
Plugin Name: Copy Or Move Comments
Plugin URI: 
Description: Using Copy/Move WordPress Plugin the admin can copy or move any comment from several types of pages to any other page!
Version: 2.0.0
Author: biztechc
Author URI: https://profiles.wordpress.org/biztechc/
License: GPLv2
*/
?>
<?php 
include_once('copy_move_functions.php');
if (!class_exists('copy_move_comments')) 
{
    class copy_move_comments
    {
        public function __construct()
        {
            add_action('admin_menu', array($this,'copy_move_menu'));   
        }
            
            function copy_move_menu() // Dispaly Seperate Menu
            {
                add_menu_page('Copy/Move Comments', 'Copy/Move Comments', 'administrator', 'copy-move', array($this,'copy_move_settings_page'),'dashicons-format-chat');
                add_action( 'admin_init', array($this,'register_copy_move__suggest_settings')); // Register all post type
            }
            
            
            function register_copy_move__suggest_settings()
            {
                register_setting( 'copy-move-settings-group', 'all_post_type' );
            }
            
            
            function copy_move_settings_page() // Display Setting page
            {?>
                <div class="wrap">
                <h2>Copy/Move Comments</h2><br>
                <form id="copy_move_form" action="admin-post.php" method="post">
                
               <?php  
                $set_post_type = get_option('all_post_type');
                settings_fields( 'copy-move-settings-group' );
                
                do_settings_sections( 'copy-move-settings-group' );
                ?>
                <div class="tablenav top">
                <big style="float: left; margin-top: 5px; margin-right: 7px;"><?php _e("Action"); ?>:</big> 
                <div class="alignleft actions">
                <label class="screen-reader-text">Select Action</label>
                    <select id="copy-move" name="copy-move">
                    <option value="">Select Action</option>
                    <option value="copy">Copy</option>
                    <option value="move">Move</option>
                    </select>
                </div>
                <big style="float: left; margin-top: 5px; margin-right: 7px;"><?php _e("Source"); ?>:</big> 
                <div class="alignleft actions">
                    <?php
                $post_types = get_post_types( '', 'names' );
                                //unset($post_types['attachment']);
                                unset($post_types['revision']);
                                unset($post_types['nav_menu_item']);?>
                                <label for="cat" class="screen-reader-text">All Post Types</label>
                                 <select name="all_post_types" id="all_post_types">
                                 <option value="0">Select Post Type</option>
                                <?php 
                                foreach ( $post_types as $post_type ) 
                                {?>
                                    <option value="<?php echo $post_type;?>"><?php echo $post_type;?></option>  
                                    
                                <?php 
                                }?>
                               </select>
                
                </div>
                
                <div class="alignleft actions" id="">
                    <select id="source_post" name="source_post">
                    <option value="">Select Post</option>
                    </select>
                <span id="bc_loader" style="display: none;"><img src="<?php echo plugins_url( 'ajax-loader.gif', __FILE__ );?>" alt=""></span>
                </div>
                
                </div>
                <div id="get_comments"></div>
                <div class="tablenav bottom" style="display: none;">
                <big style="float: left; margin-top: 5px; margin-right: 10px;"><?php _e("Target"); ?>:</big> 
                <div class="alignleft actions"> 
                <?php
                    $target_post_types = get_post_types( '', 'names' );
                    
                    unset($target_post_types['revision']);
                    unset($target_post_types['nav_menu_item']);
                ?>                                
                <select name="target_all_post_types" id="target_all_post_types">
                    <option value="0">Select Post Type</option>
                    <?php 
                    foreach($target_post_types as $target_post_type) {
                        ?>
                            <option value="<?php echo $target_post_type;?>"><?php echo $target_post_type;?></option>  
                        <?php 
                    }
                    ?>
                </select> 
                </div>
                <div class="alignleft actions">
                <select id="target_post" name="target_post">
                    <option value="0">Select Post</option>                    
                </select>
                <span id="target_bc_loader" style="display: none;"><img src="<?php echo plugins_url( 'ajax-loader.gif', __FILE__ );?>" alt=""></span></div>
                <input type="submit" value="Perform Action" class="button action" id="doaction2" name="" onclick="return chk_val();">
                </div>
                <input type="hidden" name="action" value="action_move">    
                                </form>
                                <script type="text/javascript">
function checkAll(ele) {
     var checkboxes = document.getElementsByTagName('input');
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
</script>
            </div>
            <?php 
           }
            }
}
new copy_move_comments(); // Initiate object
    
add_action( 'admin_footer', 'copy_move_get_all_posts');
add_action( 'admin_footer', 'copy_move_add_validation');
add_action( 'wp_ajax_get_all_posts', 'get_all_posts_callback');
add_action( 'wp_ajax_get_post_comments', 'get_post_comments_callback');
add_action( 'wp_ajax_perform_action', 'perform_action_callback');

function copy_move_get_all_posts() { 
    
    wp_enqueue_script( 'jquery' );
    ?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        jQuery("#all_post_types").change(function (){
            var post_type = jQuery(this).val();
            jQuery("#bc_loader").show();
            var data = {
            'action': 'get_all_posts',
            'post_type': post_type
            };
            jQuery.post(ajaxurl, data, function(response) { 
                jQuery("#source_post").html(response);
                jQuery("#bc_loader").hide();
            });        
        }); 
        
        jQuery("#target_all_post_types").change(function (){
            var post_type = jQuery(this).val();
            jQuery("#target_bc_loader").show();
            var data = {
            'action': 'get_all_posts',
            'post_type': post_type
            };
            jQuery.post(ajaxurl, data, function(response) { 
                jQuery("#target_post").html(response);
                jQuery("#target_bc_loader").hide();
                
                var source_post_value = jQuery("#source_post").val();  
                jQuery("#target_post option[value='"+source_post_value+"']").remove(); 
            });        
        });
        
        jQuery("#source_post").on("change", function(){
            jQuery(".tablenav.bottom").show();
            var post_type_value = jQuery("#target_all_post_types").val();            
            if(post_type_value != 0) {
                jQuery("#target_all_post_types").trigger("change"); 
            }
        });       
    });
</script>
<?php 
}
function get_all_posts_callback()
{
   $post_type = sanitize_text_field($_POST['post_type']);
   $action_type = sanitize_text_field($_POST['action_type']);
    $get_res = new copy_move_functions();
    $get_posts = $get_res->get_posts($post_type);?>
    <option value="">Select Post</option>
    <?php foreach($get_posts as $get_post){?>
        <option value="<?php echo $get_post->id;?>"><?php echo $get_post->post_title;?></option>    
    <?php 
    }
              wp_enqueue_script( 'jquery' );
                ?>
                <script type="text/javascript">
                jQuery(document).ready(function($) {
                jQuery('#source_post').on('change', function() {
                        var post_id  = this.value;
                         jQuery("#bc_loader").show();
                        
                        var data = {
                        'action': 'get_post_comments',
                        'post_id': post_id,
                        'action_type': '<?php echo $action_type?>',
                        'post_type' : '<?php echo $post_type?>'
                        };

                        $.post(ajaxurl, data, function(response){ 
                        jQuery("#get_comments").html(response);
                         jQuery("#bc_loader").hide();
                        });        
                    });        
                });
</script>  
<?php  exit;
}
function get_post_comments_callback()
{
    $post_id = sanitize_text_field($_POST['post_id']);
    $post_type = sanitize_text_field($_POST['post_type']);
    $action_type = sanitize_text_field($_POST['action_type']);
    $get_res1 = new copy_move_functions(); 
    $get_comments = $get_res1->get_all_comments_by_postid($post_id); 
    ?>
  
            <table class="wp-list-table widefat fixed posts">
                <thead>
                <tr>
                    <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox" value="0" name="move_comment_id[]" onchange="checkAll(this);"></th>
                    <th style="" class="manage-column column-author" scope="col">Author</th>
                    <th width="400" style="" class="manage-column column-title sortable desc" scope="col">Comment</th>
                    <th style="" class="manage-column column-date sortable asc" scope="col">Status</th>
                    <th style="" class="manage-column column-date sortable asc" scope="col">Date</th>
                </tr>
                </thead>
            <tbody>
            <?php 
            $c=0;
            if(!empty($get_comments)){
                foreach($get_comments as $get_comment){ 
                $c++;
                if($c %2 == 0)
                {
                    $cls = 'alternate';
                }else
                {
                    $cls = '';
                }
                ?> 
            <tr class="<?php echo $cls;?>" id="<?php echo $c;?>">
                <td><input type="checkbox" value="<?php echo $get_comment->comment_id;?>" name="move_comment_id[]" class="chkbox_val"></td>
                <td><?php echo $get_comment->comment_author;?></td>
                <td><?php echo $get_comment->comment_content;?></td>
                <?php if($get_comment->comment_approved == '1'){
                    $status = 'Approved';
                }else
                {
                    $status = 'Pending';
                }?>
                <td><?php echo $status;?></td>
                <td><?php echo $get_comment->comment_date;?></td>
            </tr>
            <?php }
            }else
            {?>
                <tr>
                <td class="source_error" colspan="4" align="center">No Comments found. Please change Source Post.</td>
                </tr>
                
            <?php }
            ?>
            <tfoot>
            <tr>
                <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox" value="0" name="move_comment_id[]" onchange="checkAll(this);"></th>
                    <th style="" class="manage-column column-author" scope="col">Author</th>
                    <th width="400" style="" class="manage-column column-title sortable desc" scope="col">Comment</th>
                    <th style="" class="manage-column column-date sortable asc" scope="col">Status</th>
                    <th style="" class="manage-column column-date sortable asc" scope="col">Date</th>
            </tr>
            </tfoot>
            </tbody>
            </table>
<?php 

exit;
}
function copy_move_add_validation(){
?>
<script type="text/javascript">
function chk_val()
{
    var target_type = jQuery('#target_all_post_types').val();
    var target_post = jQuery('#target_post').val();
    
    var flag = 0;
    jQuery('input[type=checkbox]').each(function () {
       var sThisVal = (this.checked ? jQuery(this).val() : "");
       
       if(sThisVal != ''){
        flag = 1;   
       }
  });
  if(flag == 0)
  {
      alert('Select any one comment');
      return false;  
  }
  if(target_type == 0)
  {
      alert('Select any one post type');
      return false;  
      
  }
  if(target_post == 0)
  {
      alert('Select any one target post');
      return false;  
      
  }
    
}
</script>
<?php 
}
add_action( 'admin_post_action_move', 'prefix_admin_action_move' );
function prefix_admin_action_move()
{
    $get_source_id = $_REQUEST['source_post'];
    $get_target_id = $_REQUEST['target_post'];
    $get_action_type = $_REQUEST['copy-move'];
    $get_comment_ids = $_REQUEST['move_comment_id'];
    if(!empty($get_comment_ids)){
        $get_comment_id = implode(',',$get_comment_ids);
    }
    
    if(isset($get_source_id) && isset($get_target_id) && isset($get_action_type) && isset($get_comment_ids) && $get_source_id !='' && $get_target_id !='' && $get_action_type !='' && !empty($get_comment_ids)){
        $perform_action = new copy_move_functions();
    
    $transfer_comments = $perform_action->perform_action($get_source_id,$get_target_id,$get_action_type,$get_comment_id);   
    $url = admin_url();
    wp_redirect( $url.'/admin.php?page=copy-move&success=1');
    exit;
}
else
{
    $url = admin_url();
    wp_redirect( $url.'/admin.php?page=copy-move&error=1');        
    exit;
} 
}

add_action('admin_footer','error_message');
function error_message(){ 
  if($_REQUEST['success'] && $_REQUEST['success'] == 1)
  {
      $url = admin_url();
      $all_comment = $url.'edit-comments.php';
      ?>
  <div  class="notice notice-success">
        <p><?php _e( 'Comments moved/copied successfully.  &nbsp;&nbsp;Click here to <a href="'.$all_comment.'">view.</a>'); ?></p>
    </div>      
      
  <?php 
  }
    if($_REQUEST['error'] && $_REQUEST['error'] == 1)
  {?>
  <div class="error">
        <p><?php _e( 'Please select atleast one comment to copy/move.'); ?></p>
    </div>      
  <?php 
  }  
}
?>