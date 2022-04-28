<?php

add_action('woocommerce_before_cart', 'ac_cart_upsells');
add_action('woocommerce_cart_updated ', 'ac_cart_upsells', 9999, 0 );

function ac_cart_upsells_variations($product_id)
{
  $args = array(
    'post_type'     => 'product_variation',
    'post_status'   => array('private', 'publish'),
    'numberposts'   => -1,
    'orderby'       => 'menu_order',
    'order'         => 'asc',
    'post_parent'   => $product_id // get parent post-ID
  );
  $variations = get_posts($args);

  $variation_ids = [];

  foreach ($variations as $variation) {

    // get variation ID
    $variation_ID = $variation->ID;
    $product_variation = new WC_Product_Variation($variation_ID);
    $variation_name = $product_variation->get_formatted_name();
    $variation_name = strtolower($variation_name);

    if (strpos($variation_name, "single") !== FALSE) {
      $variation_ids["single"] = $variation_ID;
    }

    if (strpos($variation_name, "couples") !== FALSE) {
      $variation_ids["couples"] = $variation_ID;
    }

    if (strpos($variation_name, "family") !== FALSE) {
      $variation_ids["family"] = $variation_ID;
    }
  }
  return $variation_ids;
}

function ac_cart_upsells()
{
  $upgrades = [];

  // Hair upgrades
  $sensitivity_id = wc_get_product_id_by_sku("sensitivity");
  $sensitivity_variations = ac_cart_upsells_variations($sensitivity_id);
  $sensitivity_h4 = "Why not upgrade to a more comprehensive test?";
  $sensitivity_p = "Our <a href='/sensitivity-test-plus/'>Sensitivity Test Plus </a> is our best test for identifying any possible sensitivities you may have. <span>With testing up to 950 items, we can be sure we will find you're trigger and you can start living a happier healhier life!</span>";

  $sensitivityplus_id = wc_get_product_id_by_sku("sensitivity-plus");
  $sensitivityplus_variations = ac_cart_upsells_variations($sensitivityplus_id);

  // Sensitivity to Sensitivity Plus
  foreach ($sensitivity_variations as $var_quantity => $var_id) {
    $upgrades[$var_id] = [
      "product_id" => $sensitivity_id,
      "h4_text" => $sensitivity_h4,
      "p_text" => $sensitivity_p,
      "upgrade_id" => $sensitivityplus_variations[$var_quantity],
      "img" => wp_get_attachment_image_src(get_post_thumbnail_id($sensitivityplus_id), 'single-post-thumbnail')[0],
    ];
  }


  // Blood upgrades
  $select35_id = wc_get_product_id_by_sku("select35");
  $select35_variations = ac_cart_upsells_variations($select35_id);
  $select35_h4 = "Why not get tested for intolerances as well?";
  $select35_p = "Our <a href='/allergy-intolerance-test/'>Allergy & Intolerance Test</a> tests for 35 allergies and 35 intolerances. If you are unsure if you have an allergy or an intolerance this is the test for you! ";

  $choice70_id = wc_get_product_id_by_sku("choice70");
  $choice70_variations = ac_cart_upsells_variations($choice70_id);
  $choice70_h4 = "Why not upgrade to our most comprehensive test?";
  $choice70_p = "With testing up to 110 food items, we can be sure we will find you're trigger and you can start living a happier healhier life!";


  $intolerancescreen_id = wc_get_product_id_by_sku("intolerancescreen");
  $intolerancescreen_variations = ac_cart_upsells_variations($intolerancescreen_id);
  $intolerancescreen_h4 = "Why not upgrade to a more comprehensive test?";
  $intolerancescreen_p = "Get tested on more intolerances PLUS allergies with our most comprehensive test! Our <a href='/allergy-intolerance-test-plus/'>Allergy & Intolerance Test Plus </a> is our best test for identifying any possible allergies or intolerances you may have. ";
  $prime110_id = wc_get_product_id_by_sku("prime110");
  $prime110_variations = ac_cart_upsells_variations($prime110_id);

  // Select35 to Choice70
  foreach ($select35_variations as $var_quantity => $var_id) {
    $upgrades[$var_id] = [
      "product_id" => $select35_id,
      "h4_text" => $select35_h4,
      "p_text" => $select35_p,
      "upgrade_id" => $choice70_variations[$var_quantity],
      "img" => wp_get_attachment_image_src(get_post_thumbnail_id($choice70_id), 'single-post-thumbnail')[0],
    ];
  }

  // Intolerance Screen to Prime 110
  foreach ($intolerancescreen_variations as $var_quantity => $var_id) {
    $upgrades[$var_id] = [
      "product_id" => $intolerancescreen_id,
      "h4_text" => $intolerancescreen_h4,
      "p_text" => $intolerancescreen_p,
      "upgrade_id" => $prime110_variations[$var_quantity],
      "img" => wp_get_attachment_image_src(get_post_thumbnail_id($prime110_id), 'single-post-thumbnail')[0],
    ];
  }

  // Choice70 to Prime110
  foreach ($choice70_variations as $var_quantity => $var_id) {
    $upgrades[$var_id] = [
      "product_id" => $choice70_id,
      "h4_text" => $choice70_h4,
      "p_text" => $choice70_p,
      "upgrade_id" => $prime110_variations[$var_quantity],
      "img" => wp_get_attachment_image_src(get_post_thumbnail_id($prime110_id), 'single-post-thumbnail')[0],
    ];
  }

  foreach (WC()->cart->get_cart() as $cart_item) {
    $product_in_cart = $cart_item['variation_id'];
    $quantity = $cart_item['quantity'];

    if ($upgrades[$product_in_cart]) {
      // Calculate price difference
      $old_product = wc_get_product($product_in_cart);
      $old_product_price = $old_product->get_price();

      $new_product = wc_get_product($upgrades[$product_in_cart]["upgrade_id"]);
      $new_product_price = $new_product->get_price();

      $price_difference = floatval($new_product_price) - floatval($old_product_price);
  ?>
      <div class="upsell flex-container">

        <a id="remove-btn" class="remove">×</a>

        <div class="img"> <img src="<?php echo $upgrades[$product_in_cart]["img"]; ?>" /> </div>

        <div class="desc">
          <h4><?php echo $upgrades[$product_in_cart]["h4_text"]; ?> <span>Only £<?php echo $price_difference; ?> extra!</span></h4>
          <p><?php echo $upgrades[$product_in_cart]["p_text"]; ?></p>
          <form method="POST" action="/cart/">
            <input type="hidden" name="upgrade" value="<?php echo $upgrades[$product_in_cart]["upgrade_id"]; ?>" />
            <input type="hidden" name="old" value="<?php echo $upgrades[$product_in_cart]["product_id"]; ?>" />
            <input type="hidden" name="quantity" value="<?php echo $quantity; ?>" />
            <button class="j-btn">Upgrade test</button>
          </form>
        </div>

      </div>

      <script>
        $(document).ready(function() {
          $("#remove-btn").click(function(){
            $(".upsell").fadeOut(600);
          });
        });
      </script>

      <style>

        .upsell {
          position: relative;
          text-align: left;
          justify-content: center !important;
          margin-bottom: 45px;
          background: #f6f6f6;
          padding: 30px 20px;
          box-shadow: 0px 8px 15px rgb(0 0 0 / 10%);
          -webkit-box-shadow: 0px 8px 15px rgb(0 0 0 / 10%);
        }

        .upsell img {
          width: 200px;
          /* margin-right: 20px; */
        }

        .desc {
          width: 80%;
        }

        /* .desc span {
          color: red;
        } */

        .upsell h4 {
          font-size: 1.1rem;
        }

        .upsell p {
          margin: 7px 0;
        }

        .upsell button {
          margin-top: 7px;
          font-size: 1rem;
          padding: 10px 22px;
        }

        .upsell a {
          text-decoration: none;
        }

        .upsell a.remove {
          position: absolute;
          left: 10px;
          top: 10px;
          font-size: 1.75rem;
          transition: 0.3s;
          padding: 10px;
          width: 30px;
          height: 30px;
          color: red;
          border-radius: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
          color: #fff!important;
          background: red;
        }


        @media(max-width: 768px) {
          .upsell {
            padding: 30px 20px 30px 20px;
          }

          .upsell p {
            margin: 15px 0;
          }

          .upsell img {
            width: 100%;
            margin-right: 0;
          }

          .desc {
            width: 100%;
            position: relative;
            top: -20px;
          }

          /* Line break for text on mobile */
          .desc p span {
            display: inline-block;
            margin-top: 15px;
          }
        }

      </style>
<?php
      break;
    }
  }
}
