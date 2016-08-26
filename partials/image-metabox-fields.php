<div class="jj_img_metabox">
	<button class="button" onclick="JJImageMetabox.uploader( '#jj_image_metabox' ); return false;">
		Select an Image
	</button>

	<input type="hidden"
				 class="image_id"
				 id="jj_img_metabox_img"
				 name="jj_img_metabox_img"
				 value="<?php echo intval( $custom_image ); ?>" />

	<p class="img_preview">
	<?php if ( $custom_image ) : ?>
		<img src="<?php echo esc_url( $img_src[0] ); ?>"
				 width="<?php echo intval( $img_src[1] ); ?>"
				 height="<?php echo intval( $img_src[1] ); ?>" />

		<button class="clear"
						onclick="JJImageMetabox.clear( '#jj_image_metabox' ); return false;"
						aria-label="<?php __( 'Clear image', 'jj-image-metabox' ); ?>">
			X
		</button>
	<?php endif; ?>
	</p>
</div>
