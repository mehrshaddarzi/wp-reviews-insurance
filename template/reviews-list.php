<div class="wp-insurance-form">
	<?php
	foreach ( $list as $ID => $val ) {
		?>
        <div class="comment-reviews">
            <div class="pull-left">
                <div class="comment_score_<?php echo $ID; ?>"></div>
                <script>
                    jQuery(document).ready(function () {
                        jQuery(".comment_score_<?php echo $ID; ?>").raty({
                            starType: "i",
                            readOnly: true,
                            score: <?php echo $val['score']; ?>,
                            half: false,
                            halfShow: true
                        });
                    });
                </script>
            </div>
            <div class="pull-right">
				<?php echo date_i18n( get_option( 'date_format' ), strtotime( $val['comment_date'] ) ); ?>
            </div>
            <div class="clearfix"></div>
            <br/>
            <p>
				<?php
				//Show User Full Name For Example : echo $val['comment_author'];
				echo $val['comment_content'];
				?>
            </p>
        </div>
		<?php
	}
	?>
</div>
<style>
    .comment-reviews {
        border-bottom: 1px solid #e3e3e3;
        padding: 5px;
        padding-bottom: 20px;
    }
</style>