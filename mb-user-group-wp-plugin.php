<?php
/*
Plugin Name: MB User Groups Manager
Description: Plugin to manage user groups.
Version: 1.0
Author: Manoj Bist
*/

// Function to create user group taxonomy
// Register taxonomy on init
function mb_user_groups_manager_register_taxonomy() {
    $labels = array(
        'name'              => _x( 'User Groups', 'taxonomy general name' ),
        'singular_name'     => _x( 'User Group', 'taxonomy singular name' ),
        'search_items'      => __( 'Search User Groups' ),
        'all_items'         => __( 'All User Groups' ),
        'edit_item'         => __( 'Edit User Group' ),
        'update_item'       => __( 'Update User Group' ),
        'add_new_item'      => __( 'Add New User Group' ),
        'new_item_name'     => __( 'New User Group Name' ),
        'menu_name'         => __( 'User Groups' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => false, // Change this to true if you want users to see and select groups
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'user-group' ),
    );

    register_taxonomy( 'user_group', 'user', $args );
}
add_action( 'init', 'mb_user_groups_manager_register_taxonomy' );

// Activation hook: Create user group taxonomy on plugin activation
function mb_user_groups_manager_activate() {
    mb_user_groups_manager_register_taxonomy(); // Call the function to register the taxonomy
    flush_rewrite_rules(); // Refresh permalinks to ensure the new taxonomy is recognized
}
register_activation_hook( __FILE__, 'mb_user_groups_manager_activate' );


// Add menu item for managing user groups
function MB_user_groups_manager_menu() {
    add_menu_page(
        'User Groups Manager',
        'User Groups',
        'manage_options',
        'user-groups-manager',
        'MB_user_groups_manager_page'
    );
}
add_action( 'admin_menu', 'MB_user_groups_manager_menu' );

// Content of the user groups manager page
function MB_user_groups_manager_page() {
    ?>
    <div class="wrap">
        <h1>User Groups Manager</h1>

        <h2>Create New User Group</h2>
        <form method="post" action="">
            <label for="new-user-group">User Group Name:</label>
            <input type="text" id="new-user-group" name="new_user_group" required>
            <input type="submit" class="button button-primary" value="Create">
        </form>

        <?php
        // Handle form submission to create new user group
       
        if (isset($_POST['new_user_group']) && !empty($_POST['new_user_group'])) {
            $new_group_name = sanitize_text_field($_POST['new_user_group']);
            $result = wp_insert_term($new_group_name, 'user_group');
            if (!is_wp_error($result)) {
                echo '<p style="color: green;">User group created successfully.</p>';
            } else {
                echo '<p style="color: red;">Error creating user group: ' . esc_html($result->get_error_message()) . '</p>';
            }
        }
        ?>

        <h2>User Groups</h2>
        <?php
        // Display existing user groups
        $user_groups = get_terms(array(
            'taxonomy' => 'user_group',
            'hide_empty' => false,
        ));

        if ($user_groups && !is_wp_error($user_groups)) {
            echo '<ul>';
            foreach ($user_groups as $group) {
                echo '<li>' . esc_html($group->name) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No user groups found.</p>';
        }
        ?>
    </div>
    <?php
}



// Add custom column to the Users table
function MB_user_groups_manager_add_user_group_column( $columns ) {
    $columns['user_group'] = 'User Group'; // Add a new column named "User Group"
    return $columns;
}
add_filter( 'manage_users_columns', 'MB_user_groups_manager_add_user_group_column' );

// Populate custom column with user group name
function MB_user_groups_manager_display_user_group_data( $value, $column_name, $user_id ) {
    if ( 'user_group' === $column_name ) {
        $user_groups = wp_get_object_terms( $user_id, 'user_group' );
        if ( ! empty( $user_groups ) && ! is_wp_error( $user_groups ) ) {
            $group_names = array();
            foreach ( $user_groups as $group ) {
                $group_names[] = $group->name;
            }
            $value = implode( ', ', $group_names );
        } else {
            $value = 'No Group';
        }
    }
    return $value;
}
add_filter( 'manage_users_custom_column', 'MB_user_groups_manager_display_user_group_data', 10, 3 );

// Register custom post type for sent emails
function MB_user_groups_manager_register_sent_emails_post_type() {
    $labels = array(
        'name'               => __( 'Sent Emails', 'text-domain' ),
        'singular_name'      => __( 'Sent Email', 'text-domain' ),
        'menu_name'          => __( 'Sent Emails', 'text-domain' ),
        'add_new'            => __( 'Add New', 'text-domain' ),
        'add_new_item'       => __( 'Add New Sent Email', 'text-domain' ),
        'new_item'           => __( 'New Sent Email', 'text-domain' ),
        'edit_item'          => __( 'Edit Sent Email', 'text-domain' ),
        'view_item'          => __( 'View Sent Email', 'text-domain' ),
        'all_items'          => __( 'All Sent Emails', 'text-domain' ),
        'search_items'       => __( 'Search Sent Emails', 'text-domain' ),
        'not_found'          => __( 'No sent emails found', 'text-domain' ),
        'not_found_in_trash' => __( 'No sent emails found in Trash', 'text-domain' ),
    );

    $args = array(
        'labels'              => $labels,
        'public'              => false, // Change this to true if you want to make the emails publicly accessible
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'show_ui'             => true,
        'show_in_menu'        => false,
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array( 'title', 'editor' ),
        'rewrite'             => false,
    );

    register_post_type( 'sent_email', $args );
}
add_action( 'init', 'MB_user_groups_manager_register_sent_emails_post_type' );

// Add menu item for sending emails
function MB_user_groups_manager_sent_emails_menu() {
    add_menu_page(
        'Sent Emails',
        'Sent Emails',
        'manage_options',
        'sent-emails',
        'MB_user_groups_manager_sent_emails_page'
    );
}
add_action( 'admin_menu', 'MB_user_groups_manager_sent_emails_menu' );
// Content of the sent emails page
function MB_user_groups_manager_sent_emails_page() {
    
    // Current page number
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

    // Query to retrieve sent emails
    $sent_emails_query = new WP_Query( array(
        'post_type'      => 'sent_email',
        'posts_per_page' => 10, // Number of posts per page
        'paged'          => $paged, // Current page number
    ) );

    ?>
    <div class="wrap">
        <h1>Sent Emails</h1>
       
        <table class="widefat">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Group Name</th>
                    <th>Date Sent</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ( $sent_emails_query->have_posts() ) {
                    while ( $sent_emails_query->have_posts() ) {
                        $sent_emails_query->the_post();
                        $email_title = get_the_title();
                        $group_name = ''; // Retrieve group name
						 // Retrieve the user group taxonomy term associated with the email post
                        $terms = get_the_terms( get_the_ID(), 'user_group' );
                        if ( $terms && ! is_wp_error( $terms ) ) {
                            $group_name = $terms[0]->name;
                        }
                        $date_sent = get_the_date();

                        echo '<tr>';
                        echo '<td>' . esc_html( $email_title ) . '</td>';
                        echo '<td>' . esc_html( $group_name ) . '</td>';
                        echo '<td>' . esc_html( $date_sent ) . '</td>';
                        echo '</tr>';
                    }
                    wp_reset_postdata();
                } else {
                    echo '<tr><td colspan="3">No sent emails found.</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <?php
        // Pagination
        echo '<div class="pagination">';
        echo paginate_links( array(
            'total'   => $sent_emails_query->max_num_pages,
            'current' => $paged,
        ) );
        echo '</div>';
        ?>
    </div>
    <?php
}

// Add menu item for sending emails
function MB_user_groups_manager_send_emails_menu() {
    add_menu_page(
        'Send Emails to Group',
        'Send Emails to Group',
        'manage_options',
        'send-emails',
        'MB_user_groups_manager_send_emails_page'
    );
}
add_action( 'admin_menu', 'MB_user_groups_manager_send_emails_menu' );

// Content of the send emails page
function MB_user_groups_manager_send_emails_page() {
    ?>
    <div class="wrap">
		<?php
			$success_message = '';
			if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) {
				$success_message = '<div class="notice notice-success"><p>Emails sent successfully!</p></div>';
			}
    	?>
        <h1>Send Emails</h1>
		<?php echo ($success_message); ?>
        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
            <input type="hidden" name="action" value="send_email">
            <?php wp_nonce_field( 'send_email_nonce', 'send_email_nonce' ); ?>

            <label for="email-title">Title:</label><br />
            <input type="text" id="email-title" name="email_title" required><br /><br />

            
            <label for="user-group">User Group:</label><br />
            <select id="user-group" name="user_group">
                <?php
                $user_groups = get_terms( array(
                    'taxonomy' => 'user_group',
                    'hide_empty' => false,
                ) );

                foreach ( $user_groups as $group ) {
                    echo '<option value="' . esc_attr( $group->term_id ) . '">' . esc_html( $group->name ) . '</option>';
                }
                ?>
            </select><br /><br />
			<div>
				<label>Lagends</label>
				<p>
					<strong>{full_name}</strong>: Print user's full name in email
				</p>
				<p>
					<strong>{group_name}</strong>: Print user's Group Name in email
				</p>
			</div>
            <label for="email-content">Content:</label><br />
            <?php wp_editor( '', 'email_content' ); ?><br /><br />

            <input type="submit" name="send_email" class="button button-primary" value="Send Email">
        </form>
    </div>
    <?php
}


// Handle form submission with validation
function MB_user_groups_manager_handle_email_submission() {
    if ( isset( $_POST['send_email'] ) && $_POST['send_email'] && isset( $_POST['send_email_nonce'] ) && wp_verify_nonce( $_POST['send_email_nonce'], 'send_email_nonce' ) ) {
        // Validate form fields
        if ( empty( $_POST['email_title'] ) || empty( $_POST['email_content'] ) || empty( $_POST['user_group'] ) ) {
            wp_die( 'Error: All fields are required.' );
        }

        // Retrieve form data
        $email_title = sanitize_text_field( $_POST['email_title'] );
        $email_content = wp_kses_post( $_POST['email_content'] );
        $user_group_id = intval( $_POST['user_group'] );

        // Validate user group
        $user_group = get_term( $user_group_id, 'user_group' );
        if ( ! $user_group || is_wp_error( $user_group ) ) {
            wp_die( 'Error: Invalid user group selected.' );
        }

        // Get users for the selected User Group
        $user_query = new WP_User_Query( array(
            'meta_key'     => 'user_group',
            'meta_value'   => $user_group_id,
            'meta_compare' => '=',
        ) );

        $users = $user_query->get_results();
		

        // Replace placeholders in email content with real values and send email
        foreach ( $users as $user ) {
            $full_name = $user->first_name . ' ' . $user->last_name;
            $group_name = $user_group->name;

            // Replace placeholders with real values
            $email_content_temp = str_replace( '{full_name}', $full_name, $email_content );
            $email_content_final = str_replace( '{group_name}', $group_name, $email_content_temp );

            // Send email to the user (example)
            wp_mail( $user->user_email, $email_title, $email_content_final );
        }

        // Save sent email as a post
        $post_data = array(
            'post_title'   => $email_title,
            'post_content' => $email_content,
            'post_type'    => 'sent_email',
            'post_status'  => 'publish',
        );

        $post_id = wp_insert_post( $post_data );

        if ( ! is_wp_error( $post_id ) ) {
            // Set the user group taxonomy for the sent email
            wp_set_object_terms( $post_id, $user_group_id, 'user_group' );
        }

        // Redirect after successful submission
        wp_redirect( admin_url( 'admin.php?page=send-emails&success=1' ) );
        exit;
    }
}
add_action( 'admin_post_send_email', 'MB_user_groups_manager_handle_email_submission' );

