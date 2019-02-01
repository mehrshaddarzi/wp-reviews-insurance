<div class="wp-insurance-form">
    <table border="0">
		<?php
		foreach ( $list as $ID => $val ) {
			?>
            <tr style="height: 50px;">
                <td>
                <div style="margin-bottom:10px;"><?php echo $val['title']; ?></div>
                <div class="post_score_<?php echo $ID; ?>"></div>
                <script>
                    jQuery(document).ready(function () {
                        jQuery(".post_score_<?php echo $ID; ?>").raty({
                            starType: "i",
                            readOnly: true,
                            score: <?php echo $val['rate']; ?>,
                            half: false,
                            halfShow: true
                        });
                    });
                </script>
                </td>
            </tr>

			<?php
		}
		?>
    </table>
</div>