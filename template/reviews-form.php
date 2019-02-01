<div class="wp-insurance-form">
    <form action="#" id="wp_insurance_reviews" method="post">
        <table border="0">
            <tr>
                <td><?php _e( 'Full name', 'wp-reviews-insurance' ); ?></td>
                <td>
                    <input type="text" name="full_name" value="<?php echo $full_name; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td><?php _e( 'Your email', 'wp-reviews-insurance' ); ?></td>
                <td>
                    <input type="email" name="your_email" value="<?php echo $user_email; ?>" required=""/>
                </td>
            </tr>
            <tr id="scroll_submit">
                <td><?php _e( 'Insurance Company', 'wp-reviews-insurance' ); ?></td>
                <td>
                    <select name="insurance_company">
						<?php
						foreach ( $insurance as $key => $val ) {
							?>
                            <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
							<?php
						}
						?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php _e( 'Rating', 'wp-reviews-insurance' ); ?></td>
                <td>
                    <div class="raty"></div>
                </td>
            </tr>

            <tr>
                <td colspan="2"><?php _e( 'Comment', 'wp-reviews-insurance' ); ?></td>
            </tr>

            <tr>
                <td colspan="2">
                    <textarea name="wp_reviews_comment" required="required"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <input type="submit" value="<?php _e( 'Submit Review', 'wp-reviews-insurance' ); ?>">
                </td>
            </tr>
        </table>
    </form>
    <div id="wp-reviews-form-action"></div>
    <div id="wp_reviews_thank_you" style="display: none"><?php echo $thank_you; ?></div>
</div>
<script>
    jQuery(document).ready(function ($) {

        //Set Star Rating
        jQuery('.raty').raty({starType: 'i', scoreName: 'wp_reviews_score'});

        //Submit Form
        $(document).on("submit", "form#wp_insurance_reviews", function (e) {
            e.preventDefault();

            let reviews_form_action = $("#wp-reviews-form-action");

            //Scroll to Alert
            $('html, body').animate({scrollTop: jQuery("tr#scroll_submit").offset().top}, 1500);

            //Check Score Rating
            if ($("input[name=wp_reviews_score]").val().length === 0) {
                reviews_form_action.hide().html(`<div class="alert error">Please Select Your Star Vote.</div>`).fadeIn('fast').delay(5000).fadeOut('normal');
                return false;
            }

            //Prepare Form Data
            var formdata = new FormData($("#wp_insurance_reviews")[0]);
            formdata.append('action', 'add_reviews_insurance');

            //Show Loading
            reviews_form_action.show().html(`<div class="text-center"><img src="<?php echo admin_url( "/images/spinner-2x.gif" ); ?>" class="wps_spinner_btn"></div>`);

            //Start Ajax Request
            jQuery.post({
                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                processData: false,
                contentType: false,
                dataType: "json",
                cache: false,
                data: formdata,
                success: function (data) {

                    if (data.state_request === "error") {
                        reviews_form_action.hide().html(`<div class="alert error">` + data.text + `</div>`).fadeIn('fast').delay(6000).fadeOut('normal');
                    } else {
                        jQuery("#wp-reviews-form-action, #wp_insurance_reviews").remove();
                        jQuery("#wp_reviews_thank_you").fadeOut();
                    }

                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert('request ajax error, Please Try again.');
                }
            });

            return false;
        });

    });
</script>