<?php

/*
Plugin name: Woocommerce Storebadge
*/

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    // Settings
    add_action('admin_menu', 'storebadge_admin_menu', 99);
    function storebadge_admin_menu() {
        add_submenu_page('woocommerce', 'Storebadge', 'Storebadge', 'manage_options', 'wc-storebadge', 'storebadge_settings');
    }

    function storebadge_settings() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['sb_store'])) {
                update_option('sb_store', trim($_POST['sb_store']));
            }
            if (isset($_POST['sb_secret'])) {
                update_option('sb_secret', trim($_POST['sb_secret']));
            }
        }
    ?>
    <div class="wrap">
        <h2>WooCommerce Storebadge</h2>
        <form method="post">
            <p>
                <label for="sb_store">Store:</label><br>
                <input type="text" name="sb_store" id="sb_store" value="<?php echo get_option('sb_store'); ?>">
            </p>
            <p>
                <label for="sb_secret">Store secret:</label><br>
                <input type="text" name="sb_secret" id="sb_secret" value="<?php echo get_option('sb_secret'); ?>">
            </p>
            <p><button type="submit" class="button-primary">Save</button></p>
        </form>
    </div>
    <?php
    }

    // Thank you
    add_action('woocommerce_thankyou', 'storebadge_thankyou');
    function storebadge_thankyou() {
        $order_id = (isset($_GET['order-received']) && ctype_digit($_GET['order-received'])) ? $_GET['order-received'] : null;
        if ($order_id) {
            $meta = get_post_meta($order_id);
            ?>
            <!-- storebadge rating & review tracking -->
            <script type="text/javascript">
            // Mandatory below
            var sb_store = '<?php echo get_option('sb_store'); ?>';
            var sb_email = '<?php echo $meta['_billing_email'][0]; ?>';
            var sb_reference = '<?php echo $_GET['order-received']; ?>';
            var sb_secret = '<?php echo md5($meta['_billing_email'][0].$_GET['order-received'].get_option('sb_secret')); ?>';
            // Optional below
            var sb_fname = '<?php echo $meta['_billing_first_name'][0]; ?>';
            var sb_lname = '<?php echo $meta['_billing_last_name'][0]; ?>';
            var sb_currency = '<?php echo $meta['_order_currency'][0]; ?>';
            var sb_amount = '<?php echo $meta['_order_total'][0]; ?>';
            var sb_delay = false;
            </script>
            <script async type="text/javascript" src="//cdn.storebadge.com/js/tracking.js"></script>
            <?php
        }
    }

    add_action('widgets_init', function() {
        register_widget('Storebadge_Widget');
        register_widget('Storebadge_Popup');
    });

    class Storebadge_Widget extends WP_Widget
    {
    	public function __construct() {
        parent::__construct(
			'storebadge_widget',
			'Storebadge widget',
			array('description' => 'Show your latest reviews from Storebadge.'));
    	}

    	public function widget($args, $instance)
    	{
        	$store          = get_option('sb_store');
        	$limit          = ($instance['limit'])          ? $instance['limit']        : 10;
        	$lang           = ($instance['lang'])           ? $instance['lang']         : 'en';
        	$trans          = ($instance['trans'])          ? $instance['trans']        : 'en';
        	$min_rating     = ($instance['min_rating'])     ? $instance['min_rating']   : 0;
        	$read_status    = ($instance['read_status'])    ? $instance['read_status']  : 'all';
        	$encoding    	= ($instance['encoding'])    	? $instance['encoding']  	: 'UTF-8';
        ?>
			<!-- Start Storebadge Widget -->
			<script>
			var sb_store 		= '<?php echo $store; ?>';
			var sb_limit 		= '<?php echo $limit; ?>';
			var sb_offset		= '0';
			var sb_language		= '<?php echo $lang; ?>';
			var sb_translation  = '<?php echo $trans; ?>';
			var sb_min_rating 	= '<?php echo $min_rating; ?>';
			var sb_read_status	= '<?php echo $read_status; ?>';
			var sb_encoding		= '<?php echo $encoding; ?>';
			</script>
			<script async src="//cdn.storebadge.com/js/widget.js"></script>
			<div id="storebadge-div"></div>
			<!-- End Storebadge Widget -->
        <?php
    	}

    	public function form($instance)
    	{ ?>
    	    <?php if (get_option('sb_store')): ?>
    	        <?php
                $limit       = !empty($instance['limit'])       ? $instance['limit']        : 10;
                $lang        = !empty($instance['lang'])        ? $instance['lang']         : 'en';
                $trans       = !empty($instance['trans'])       ? $instance['trans']        : 'en';
                $min_rating  = !empty($instance['min_rating'])  ? $instance['min_rating']   : 0;
                $read_status = !empty($instance['read_status']) ? $instance['read_status']  : 'all';
                $encoding 	 = !empty($instance['encoding']) 	? $instance['encoding']  	: 'UTF-8';
        	    ?>
        	    <p><label>How many reviews?</label><input class="widefat" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo esc_attr($limit); ?>"></p>
        	    <p><label>Review language</label><input class="widefat" name="<?php echo $this->get_field_name('lang'); ?>" type="text" value="<?php echo esc_attr($lang); ?>"></p>
        	    <p><label>Widget translation</label><input class="widefat" name="<?php echo $this->get_field_name('trans'); ?>" type="text" value="<?php echo esc_attr($trans); ?>"></p>
        	    <p><label>Show reviews with rating over</label><input class="widefat" name="<?php echo $this->get_field_name('min_rating'); ?>" type="text" value="<?php echo esc_attr($min_rating); ?>"></p>
                <p><label>Read status</label>
                    <select class="widefat" name="<?php echo $this->get_field_name('read_status'); ?>">
                        <option value="all" <?php if (esc_attr($read_status) == 'all'): echo 'selected="selected"'; endif; ?>>All reviews</option>
                        <option value="read" <?php if (esc_attr($read_status) == 'read'): echo 'selected="selected"'; endif; ?>>Only read</option>
                    </select>
                </p>
        	    <p><label>Encoding</label><input class="widefat" name="<?php echo $this->get_field_name('encoding'); ?>" type="text" value="<?php echo esc_attr($encoding); ?>"></p>
    	    <?php else: ?>
                <p>No store set in Storebadge settings.</p>
    	    <?php endif; ?>
    	<?php }

    	public function update($new_instance, $old_instance)
    	{
            $instance = array();
            $instance['limit']       = (!empty($new_instance['limit']))       ? strip_tags($new_instance['limit'])        : 10;
            $instance['lang']        = (!empty($new_instance['lang']))        ? strip_tags($new_instance['lang'])         : 'en';
            $instance['trans']       = (!empty($new_instance['trans']))       ? strip_tags($new_instance['trans'])        : 'en';
            $instance['min_rating']  = (!empty($new_instance['min_rating']))  ? strip_tags($new_instance['min_rating'])   : 0;
            $instance['read_status'] = (!empty($new_instance['read_status'])) ? strip_tags($new_instance['read_status'])  : 'all';
            $instance['encoding']    = (!empty($new_instance['encoding']))    ? strip_tags($new_instance['encoding'])     : 'UTF-8';

            return $instance;
    	}
    }

    class Storebadge_Popup extends WP_Widget
    {
    	public function __construct() {
        parent::__construct(
			'storebadge_popup',
			'Storebadge popup',
			array('description' => 'Show the Storebadge popup.'));
    	}

    	public function widget($args, $instance)
    	{
        	$store          = get_option('sb_store');
        	$trans          = ($instance['trans'])          ? $instance['trans']        : 'en';
        	$position       = ($instance['position'])       ? $instance['position']     : 'top';
        	$encoding    	= ($instance['encoding'])    	? $instance['encoding']  	: 'UTF-8';
        	$img_size       = ($instance['img_size'])       ? $instance['img_size']     : 120;
        	$trigger        = ($instance['trigger'])        ? $instance['trigger']      : 'onmouseover';
        	$html           = ($instance['html'])           ? $instance['html']         : '<span>What our customers say</span>';
        ?>
			<!-- Storebadge Popup -->
			<script>
			var sb_store 		= '<?php echo $store; ?>';
			var sb_translation  = '<?php echo $trans; ?>';
			var sb_position	    = '<?php echo $position; ?>';
			var sb_encoding		= '<?php echo $encoding; ?>';
			var sb_img_size     = '<?php echo $img_size; ?>';
			var sb_trigger      = '<?php echo $trigger; ?>';
			var sb_html         = '<?php echo $html; ?>';
			</script>
			<script async src="//cdn.storebadge.com/js/popup.js"></script>
			<div id="storebadge-popup"></div>
			<!-- Storebadge Popup -->
        <?php
    	}

    	public function form($instance)
    	{ ?>
    	    <?php if (get_option('sb_store')): ?>
    	        <?php
            	$trans          = ($instance['trans'])          ? $instance['trans']        : 'en';
            	$position       = ($instance['position'])       ? $instance['position']     : 'top';
            	$encoding    	= ($instance['encoding'])    	? $instance['encoding']  	: 'UTF-8';
            	$img_size       = ($instance['img_size'])       ? $instance['img_size']     : 120;
            	$trigger        = ($instance['trigger'])        ? $instance['trigger']      : 'onmouseover';
            	$html           = ($instance['html'])           ? $instance['html']         : '<span>What our customers say</span>';
        	    ?>
        	    <p><label>Widget translation</label><input class="widefat" name="<?php echo $this->get_field_name('trans'); ?>" type="text" value="<?php echo esc_attr($trans); ?>"></p>
        	    <p><label>Position</label>
            	    <select name="<?php echo $this->get_field_name('position'); ?>" class="widefat">
                        <option value="top" <?php if ($position == 'top'): echo 'selected="selected"'; endif; ?>>Top</option>
                        <option value="right" <?php if ($position == 'right'): echo 'selected="selected"'; endif; ?>>Right</option>
                        <option value="bottom" <?php if ($position == 'bottom'): echo 'selected="selected"'; endif; ?>>Bottom</option>
                        <option value="left" <?php if ($position == 'left'): echo 'selected="selected"'; endif; ?>>Left</option>
            	    </select>
        	    </p>
        	    <p><label>Image size</label><input class="widefat" name="<?php echo $this->get_field_name('img_size'); ?>" type="text" value="<?php echo esc_attr($img_size); ?>"></p>
        	    <p><label>Trigger</label>
            	    <select name="<?php echo $this->get_field_name('trigger'); ?>" class="widefat">
                        <option value="mouseover" <?php if ($position == 'mouseover'): echo 'selected="selected"'; endif; ?>>Mouseover</option>
                        <option value="click" <?php if ($position == 'click'): echo 'selected="selected"'; endif; ?>>Click</option>
            	    </select>
        	    </p>
        	    <p><label>Encoding</label><input class="widefat" name="<?php echo $this->get_field_name('encoding'); ?>" type="text" value="<?php echo esc_attr($encoding); ?>"></p>
        	    <p><label>HTML</label><textarea class="widefat" name="<?php echo $this->get_field_name('html'); ?>"><?php echo esc_attr($html); ?></textarea></p>
    	    <?php else: ?>
                <p>No store set in Storebadge settings.</p>
    	    <?php endif; ?>
    	<?php }

    	public function update($new_instance, $old_instance)
    	{
            $instance = array();
            $instance['trans']       = (!empty($new_instance['trans']))       ? strip_tags($new_instance['trans'])        : 'en';
            $instance['position']    = (!empty($new_instance['position']))    ? strip_tags($new_instance['position'])     : 'top';
            $instance['encoding']    = (!empty($new_instance['encoding']))    ? strip_tags($new_instance['encoding'])     : 'UTF-8';
            $instance['img_size']    = (!empty($new_instance['img_size']))    ? strip_tags($new_instance['img_size'])     : '120';
            $instance['trigger']     = (!empty($new_instance['trigger']))     ? strip_tags($new_instance['trigger'])      : 'mouseover';
            $instance['html']        = (!empty($new_instance['html']))        ? strip_tags($new_instance['html'])         : false;

            return $instance;
    	}
    }
}