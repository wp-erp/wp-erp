<?php

namespace WeDevs\ERP;

/**
 * People Class
 */
class Uploader {

    /**
     * Uploads files.
     *
     * @return mixed
     */
    public function upload_file() {
        if ( ! current_user_can( 'upload_files' ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to upload files.', 'erp' ) );
        }

        $upload = [
            'name'     => isset( $_FILES['file'], $_FILES['file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['file']['name'] ) ) : '',
            'type'     => isset( $_FILES['file'], $_FILES['file']['type'] ) ? sanitize_mime_type( wp_unslash( $_FILES['file']['type'] ) ) : '',
            'tmp_name' => isset( $_FILES['file'], $_FILES['file']['tmp_name'] ) ? sanitize_url( wp_unslash( $_FILES['file']['tmp_name'] ) ) : '',
            'error'    => isset( $_FILES['file'], $_FILES['file']['error'] ) ? sanitize_text_field( wp_unslash( $_FILES['file']['error'] ) ) : '',
            'size'     => isset( $_FILES['file'], $_FILES['file']['size'] ) ? sanitize_text_field( wp_unslash( $_FILES['file']['size'] ) ) : '',
        ];

        header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

        $attach = $this->handle_upload( $upload );

        if ( $attach['success'] ) {
            $response = [ 'success' => true ];

            return $this->attach_html( $attach['attach_id'] );
        } else {
            return 'error';
        }

        exit;
    }

    /**
     * Generic function to upload a file
     *
     * @param string $field_name file input field name
     *
     * @return array
     */
    public function handle_upload( $upload_data ) {

        /*** Necessary if called from API ***/
        if ( ! function_exists( 'wp_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $uploaded_file = wp_handle_upload( $upload_data, ['test_form' => false] );

        // If the wp_handle_upload call returned a local path for the image
        if ( isset( $uploaded_file['file'] ) ) {
            $file_loc  = $uploaded_file['file'];
            $file_name = basename( $upload_data['name'] );
            $file_type = wp_check_filetype( $file_name );

            /**
             * To modify uploaded attachment data before inserting into database.
             *
             * @since 1.10.6
             *
             * @param array $attachment_data
             *              post_mime_type The mime type of the file.
             *              post_title     The generated title of the post.
             *              post_content   The generated post content.
             *              post_status    The initial post status.
             *
             * @param array $upload_data
             *              name           The name of the file as provided by the user.
             *              type           The mime type of the file.
             *              tmp_name       The absolute path to the uploaded file.
             *              error          The error data if any.
             *              size           The size of the file in bytes.
             */
            $attachment = apply_filters(
                'erp_upload_attachment_data',
                [
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => ! empty( $upload_data['post_title'] ) ? $upload_data['post_title'] : preg_replace( '/\.[^.]+$/', '', basename( $file_name ) ),
                    'post_content'   => ! empty( $upload_data['post_content'] ) ? $upload_data['post_content'] : '',
                    'post_status'    => ! empty( $upload_data['post_status'] ) ? $upload_data['post_status'] : 'inherit',
                ],
                $upload_data
            );

            $attach_id   = wp_insert_attachment( $attachment, $file_loc );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file_loc );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            return ['success' => true, 'attach_id' => $attach_id];
        }

        return ['success' => false, 'error' => $uploaded_file['error']];
    }

    public static function attach_html( $attach_id, $custom_attr = [] ) {
        $attachment = get_post( $attach_id );

        if ( ! $attachment ) {
            return;
        }

        if ( wp_attachment_is_image( $attach_id ) ) {
            $image = wp_get_attachment_image_src( $attach_id, [ '80', '80' ] );
            $image = $image[0];
        } else {
            $image = wp_mime_type_icon( $attach_id );
        }

        $html = '<li class="erp-image-wrap thumbnail">';
        $html .= sprintf( '<div class="attachment-name"><img class="erp-file-mime" ' . implode( ' data-', $custom_attr ) . ' height="80" width="80" src="%s" alt="%s" /></div>', $image, esc_attr( $attachment->post_title ) );
        $html .= sprintf( '<input type="hidden" name="files[]" value="%d">', $attach_id );
        $html .= sprintf( '<div class="caption"><a href="#" class="erp-del-attc-button btn-danger btn-small attachment-delete" data-attach_id="%d">X</a></div>', $attach_id );
        $html .= '</li>';

        return $html;
    }

    public function delete_file( $attach_id ) {
        $attachment = get_post( $attach_id );

        //post author or editor role
        if ( get_current_user_id() == $attachment->post_author || current_user_can( 'delete_private_pages' ) ) {
            wp_delete_attachment( $attach_id, true );

            return true;
        }

        exit;
    }
}
