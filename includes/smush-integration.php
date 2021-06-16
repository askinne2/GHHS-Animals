<?php
/**
 * For integration with Smush CDN - don't cache our animal images!
 */
function ghhs_smush_integration($status, $src) {
                static $thumbnail_url;
                if( is_null( $thumbnail_url ) ){
                        if ( is_singular( 'animal' ) || (is_archive() && get_post_type() == 'animal') ) {                                $img = get_the_post_thumbnail_url(get_queried_object_id());
                                if( $img ){
                                        $thumbnail_url = substr($img, 0, strrpos( $img, '.' ) );
                                }else{
                                        $thumbnail_url = false;
                                }
                        }else{
                                $thumbnail_url = false;
                        }
                }
                if( $thumbnail_url && 0 === strpos( $src, $thumbnail_url ) ) {
                        return true;
                }
                return $status;
}
add_filter( 'smush_skip_image_from_cdn', 'ghhs_smush_integration', 11, 2);
add_filter( 'smush_cdn_skip_image', 'ghhs_smush_integration', 11, 2 );