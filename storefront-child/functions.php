<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// 워드프레스 우커머스: 새 주문 이메일에 제품 이름 표시하기
add_filter('woocommerce_email_subject_new_order', 'change_admin_email_subject', 10, 2);
function change_admin_email_subject( $subject, $order ) {
    $products_names = array();

    foreach ( $order->get_items() as $item ) {
        $products_names[] = $item->get_name();
    }

    return sprintf( '[%s] 신규주문#%s [%s] [%s] %s', 
        wp_specialchars_decode(get_option('blogname'), ENT_QUOTES), 
        $order->get_id(), 
        implode(', ', $products_names),
        $order->get_billing_first_name(),  
        $order->get_billing_last_name()
    );
}

add_action('wp_enqueue_scripts', 'my_theme_enqueue_style_css');

if( !function_exists( 'my_theme_enqueue_style_css') ) :
    function my_theme_enqueue_style_css() {
        
        $parent_style = 'parent-style';

        wp_enqueue_style($parent_style, get_template_directory_uri().'/style.css');
        wp_enqueue_style('child-style', get_stylesheet_directory_uri().'/style.css', array($parent_style), wp_get_theme()->get('Version'));
    }
endif;

add_action('admin_enqueue_scripts', 'my_theme_enqueue_admin_style_css');

if( !function_exists( 'my_theme_enqueue_admin_style_css') ) :
    function my_theme_enqueue_admin_style_css() {
        wp_enqueue_style('child-admin-style', get_stylesheet_directory_uri().'/admin-style.css', false, '1.0.0');
    
    }
endif;

wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-widget');


add_action('wp_enqueue_scripts', 'ng_enqueue_scripts');
function ng_enqueue_scripts() {

    wp_register_script('mobilenav-scroll', get_stylesheet_directory_uri() . '/assets/js/mobilenav-scroll.js', array(), '1.0.0', true);
    wp_enqueue_script('mobilenav-scroll');
   
    wp_register_script('logo-request-allcheck', get_stylesheet_directory_uri() . '/assets/js/logo-request-allcheck.js', array(), '1.0.0', true);
    wp_register_style('logo-request-page', get_stylesheet_directory_uri() . '/assets/css/logo-request-page.css');    
    
    if( is_page('logo') || is_front_page()) {
        wp_enqueue_script('logo-request-allcheck');
        wp_enqueue_style('logo-request-page');
    }

    wp_register_script('singleproduct-qty-cal', get_stylesheet_directory_uri() . '/assets/js/singleproduct-qty-cal.js', array(), '1.0.0', true);
    wp_register_script('ajax-add-to-cart', get_stylesheet_directory_uri() . '/assets/js/ajax-add-to-cart.js', array(), '1.0.0', true);
    if( function_exists('is_product') && is_product()) {
        wp_enqueue_script('singleproduct-qty-cal');
        wp_enqueue_script('ajax-add-to-cart');
    }
}


// Change add to cart text on single product page
add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_add_to_cart_button_text_single' );
function woocommerce_add_to_cart_button_text_single() {
    return __( '장바구니 담기', 'woocommerce' );
}

// Change add to cart text on product archives page

add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_add_to_cart_button_text_archives' );
function woocommerce_add_to_cart_button_text_archives($label) {
    $label =  __( '장바구니 담기', 'woocommerce' );
    return $label;
}

// Change order button text on checkout page
add_filter('woocommerce_order_button_text', 'woocommerce_order_button_text_change');
function woocommerce_order_button_text_change() {
    if(!is_user_logged_in()) {
        return __( '비회원 주문하기', 'woocommerce' );
    }
    return __( '주문하기', 'woocommerce' );  
}
add_filter('wc_cart_totals_coupon_label', 'wc_cart_totals_coupon_label_change');
function wc_cart_totals_coupon_label_change() {
    return __('할인', 'woocommerce');
}


/*로그아웃 시 메인페이지 이동 */
add_action('wp_logout', 'redirect_after_logout');
function redirect_after_logout() {
    wp_safe_redirect(home_url());
    exit;
}

/**
 * @snippet WooCommerce Show Product Image @ Checkout Page
 */

add_filter( 'woocommerce_cart_item_name', 'ts_product_image_on_checkout', 10, 3);

function ts_product_image_on_checkout( $name, $cart_item, $cart_item_key ) {
    
    if( !is_checkout() ) {
        return $name;
    }
    
    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

    $thumbnail = $_product->get_image();

    $image = '<div class="ts-product-image">'.$thumbnail.'</div>';

    return $image.$name;
}

/**
  * @snippet Woocommerce Show Product Image @ Order-Pay Page
*/
add_filter( 'woocommerce_order_item_name', 'ts_product_image_on_order_pay', 10, 3);

function ts_product_image_on_order_pay($name, $item, $extra) {

    /* Return if not checkout page */

    if( !is_checkout() ) {
        return $name;
    }

   
    $product_id = $item->get_product_id();

    /*get product object */
    $_product = wc_get_product($product_id);

    $thumbnail = $_product->get_image();

    $image = '<div class="ts-product-image">'.$thumbnail.'</div>';

    return $image.$name;


}


function woocommerce_product_category( $args = array() ) {

    $woocommerce_category_id = get_queried_object_id();
    $args = array(
        'parent' => $woocommerce_category_id
    );
    
    $terms = get_terms( 'product_cat', $args);
    

    if($terms) {
        echo '<ul class="woocommerce-categories">';

        foreach( $terms as $term ) {
            
            echo '<li class="woocommerce-product-category-page">';

            woocommerce_subcategory_thumbnail($term);

            echo '<h2>';

            echo '<a href="'.esc_url(get_term_link($term)).'"class="'.$term->slug.'">';

            echo $term->name;

            echo '</a>';

            echo '</h2>';

            echo '</li>';

        }

        echo '</ul>';
    }

}
add_action( 'woocommerce_before_shop_loop', 'woocommerce_product_category', 100);


// Display Fields
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_fields' );



function woo_add_custom_fields() {
    
    global $woocommerce;
    global $post;

    echo '<div class="options_group">';


    //Text field
    woocommerce_wp_text_input(
        array(
            'id' => '_text_field',
            'label' => __( 'Shipping info', 'woocommerce'),
            'placeholder' => 'Enter your predicted shipping time here',
            'desc_tip' => 'true',
            'description' => __( 'Enter your predicted shipping time here', 'woocommerce' )
        )
        
    );
    echo '</div>';
        
}

// Save Fields
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_fields_save');

function woo_add_custom_fields_save( $post_id ) {

    $woocommerce_text_field = $_POST['_text_field'];
    
    if( !empty( $woocommerce_text_field )) {
        update_post_meta( $post_id, '_text_field', esc_attr( $woocommerce_text_field) );
    }

}

add_action( 'woocommerce_single_product_summary', 'display_custom_field_value', 22 );

function display_custom_field_value() {
    
    $value = get_post_meta( get_the_ID(), '_text_field', true);

    if(strlen($value) != null && strlen($value) > 0) {
        echo '<div class="woocommerce-message">'.get_post_meta( get_the_ID(), '_text_field', true).'</div>';
    }
}


/*Remove upsells products from original location */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );



/* Related products IN CUSTOM TAB */

//remove related products form original location
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);




/*워드프레스 내장 에디터 사용시 kboard 미디어 추가 버튼 삭제*/

function remove_kboard_add_media_button() {
    remove_action( 'media_buttons', 'kboard_editor_button' );
    remove_filter( 'mce_buttons', 'kboard_register_media_button' );
    remove_filter( 'mce_external_plugins', 'kboard_add_media_button' );
}

add_action( 'kboard_skin_header', 'remove_kboard_add_media_button');


/*woocomerce customization*/
add_action( 'woocommerce_before_main_content', 'woocamp_open_div', 5 );
function woocamp_open_div() {

    /* if we are on a single product page */
    if( !is_product() ) {
        return;
    }

    echo '<div class="woocamp_wrap">';
}


add_action( 'woocommerce_after_main_content', 'woocamp_close_div', 50 );
function woocamp_close_div() {

    if( !is_product() ) {
        return;
    }

    echo '</div>';
}

add_action( 'get_header', 'ng_my_wp_remove_storefront_sidebar' );
function ng_my_wp_remove_storefront_sidebar() {
  
        remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10);
    
}



/* 상점 페이지 아이템 description 적용 */

add_action( 'woocommerce_after_shop_loop_item', 'woocamp_add_content_after_loop_item', 10);

function woocamp_add_content_after_loop_item() {

    global $product;
   
    /*
    if( $product->is_on_sale() ) {
        echo '<p class="after-loop-item-text">On sale now!</p>';
    } else {
        echo '<p class="after-loop-item-text">Not on sale</p>';
    }
    */
    if ( $product->get_short_description() ) {
        echo '<div class="after-loop-item-description">'.$product->get_short_description().'</div>';
    }
}

/*상품 리스트 장바구니 버튼 제거 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );


/*제품 가격 심볼 변경 */
add_filter( 'woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);

function change_existing_currency_symbol( $currency_symbol, $currency ) {
    switch($currency) {
        case 'KRW' : 
            $currency_symbol = '원';
            break;
    }
    return $currency_symbol;
}



/*상품 상세 페이지 총 상품 금액 계산 */
add_action( 'woocommerce_after_add_to_cart_form', 'my_product_price_calculator',10);

function my_product_price_calculator() {

    global $product;


    echo '<div class="total-price-wrapper">';
    echo '<span class="left">총 상품 금액 : </span>';
    echo '<div class="right">';
    echo '<span class="total-price">'.$product->get_price().'</span>';
    echo '<span class="total-price-unit">원</span>';
    echo '<span class="total-price-qty">1</span>';
    echo '</div>';    
    echo '</div>';

}


/* 상품 리스트 - 기존의 상품 타이틀 action 제거  */
remove_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10);

/* 상품 리스트 - 타이틀 앞에 'new' 텍스트 추가 상품 별 조건문으로 특정 상품 카테고리 타이틀 수정 가능*/
add_action( 'woocommerce_shop_loop_item_title', 'woocamp_shop_loop_title', 10, 1);

function woocamp_shop_loop_title($title) {

    if( has_term( 'Logo', 'product_cat' ) ) { 
        
        $additinal_text = 'New ';

        echo '<h2 class="woocommerce-loop-product__title">'. '<span class="new">'.$additinal_text.'</span>'.get_the_title().'</h2>';
    
    } else {
       echo '<h2 class="woocommerce-loop-product__title">'.get_the_title().'</h2>';    
    }
}



add_action( 'woocommerce_cart_is_empty', 'empty_cart_message', 1);

function empty_cart_message() {
    echo '<p class="empty-message">장바구니가 비어 있습니다.</p>';
}


add_action( 'woocommerce_after_cart_contents', 'woocommerce_empty_cart_button', 999 );
function woocommerce_empty_cart_button() {
    
    echo '<a href="' . esc_url( add_query_arg( 'empty_cart', 'yes' ) ) . '" class="button cart" title="' . esc_attr( 'emptycart', 'woocommerce' ) . '">' . esc_html( '카트전체비우기', 'woocommerce' ) . '</a>';

}


add_action( 'wp_loaded', 'woocommerce_empty_cart_action', 20);

function woocommerce_empty_cart_action() {
    
    if ( isset( $_GET['empty_cart'] ) && 'yes' === esc_html( $_GET['empty_cart'] ) ) {
       
        WC()->cart->empty_cart();
        
        $referer = wp_get_referer() ? esc_url( remove_query_arg( 'empty_cart') ) : wc_get_cart_url();
        wp_safe_redirect( $referer);
        
    }
}

add_action( 'woocommerce_after_cart_totals', 'button_direct_to_archive', 22);
function button_direct_to_archive() {

    echo '<a href="'.esc_url(get_permalink( get_page_by_title( '로고 제작비용' ))).'">쇼핑 계속하기</a>';

}

add_action( 'wp_footer', 'cart_update_qty_script' ); 
function cart_update_qty_script() { 
    if (is_cart()) : 
    ?>
<script>
jQuery('div.woocommerce').on('change', '.qty', function() {
    jQuery("[name='update_cart']").trigger("click");
});
</script>
<?php 
    endif; 
}

/**
 * Remove all possible fields
 */

 function wc_remove_checkout_fields($fields) {
    //Billing fields
    unset( $fields['billing']['billing_country'] );
    unset( $fields['billing']['billing_state'] );
    unset( $fields['billing']['billing_last_name'] );
    unset( $fields['billing']['billing_address_1'] );
    unset( $fields['billing']['billing_address_2'] );
    unset( $fields['billing']['billing_city'] );
    unset( $fields['billing']['billing_postcode'] );

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'wc_remove_checkout_fields');




 /*주문 완료 후, 인터뷰 작성 페이지로 이동 버튼 */
add_action('woocommerce_before_order_overview', 'link_button_to_interview', 10);

function link_button_to_interview($order) {
    foreach( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id();
        $product_name = $item->get_name();
    }

?>

<section class="woocommerce-link-to-interview">


    <h2 class="woocommerce-order-details__title">상품 인터뷰 작성</h2>
    <div class="link-inner">

        <p class="link-btn-description">
            고객님의 의뢰 내용에 관한 인터뷰 작성입니다.
        </p>
        <a class="woocommerce-Button button"
            href="<?php echo esc_url(get_permalink( get_page_by_title( '인터뷰작성' ))).'?prod='. $product_name; ?>"><?php esc_html_e('인터뷰 작성하기', 'woocommerce'); ?></a>
    </div>

</section>

<?php
}
add_action('do_action_test_hook', 'test_do_action');
function test_do_action() {
    echo '<p>Test do action!!!!!!!!!!!!!</p>';
}

/*회원정보 수정 시 billing_first_name 업데이트*/

// woocommerce 회원정보 업데이트 hook

add_action( 'wpmem_post_update_data', 'action_woocommerce_save_account_details', 10, 1);

function action_woocommerce_save_account_details( $fields ) {
    //$data = get_user_meta($user_id, 'first_name', true);
    $data = get_user_meta($fields["ID"], 'first_name', true);
    
    update_user_meta($fields["ID"], 'billing_first_name', $data );
}

/*프로필 페이지 적립 포인트 링크 추가*/
add_filter('wpmem_member_links_args', 'my_member_links_args');

function my_member_links_args( $args ) {
    $woocommerce_myaccount_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
			
   
    $args['rows'][] = '<li class="points"><a href="'.wc_get_endpoint_url('view-log', '', $woocommerce_myaccount_url).'">'.__('적립 포인트', 'cosmosfarm-members').'</a></li>';
			
    
    return $args;
    
}

/*프로필 페이지 아바타 제거 */
add_filter('wpmem_member_links_args', 'my_member_profile_header_args', 999, 1);
function my_member_profile_header_args($args){
	$current_user = wp_get_current_user();
	
	$args['wrapper_before'] = '<div class="cosmosfarm-members-form">';
	$args['wrapper_before'] .= '<div class="profile-header">';
	$args['wrapper_before'] .= '<div class="display-name">'.$current_user->display_name.'</div>';
	$args['wrapper_before'] .= '</div>';
	$args['wrapper_before'] .= '<ul class="members-link">';
	$args['wrapper_after'] = '</ul></div>';
	
	return $args;
}




 add_action('woocommerce_after_checkout_form', 'wc_additional_html_nonmem_buyconfirm_back', 999);
 function wc_additional_html_nonmem_buyconfirm_back() {
    ?>
<section class="modal modal-section type-confirm nonMember-buynow-confirm-wrap">
    <div class="nonMember-buynow-confirm-inner">
        <button class="btn modal_close">구매 취소</button>
        <h1 class="confirm-title">비회원으로 구매하시겠습니까?</h1>

        <div class="guest-privacy-agreements">
            <p>1. 개인정보 수집 및 이용목적</p>
            <p><br></p>

            <p>제로디자인은 수집된 개인정보를 아래의 목적을 위해 활용됩니다.</p>
            <p>a. 로고제작에 필요한 정보를 전달받고 제작된 시안과 데이터를 전송하기위해 사용됩니다.</p>
            <p>b. 인쇄물의 정확한 배송을 위해 사용됩니다.</p>
            <p>c. 제로디자인의 서비스변경에 대한 알림과 기타 공지에 사용됩니다.</p>

            <p><br></p>
            <p>2. 개인정보 수집항목</p>
            <p><br></p>
            <p>이름, 핸드폰, 이메일, 회사명(국,영문), 홈페이지, 전화</p>
            <p><br></p>
            <p>3. 개인정보 보유 및 이용기간</p>
            <p><br></p>
            <p>제로디자인의 수집된 개인정보는 로고제작이 완료되면 원칙적으로 삭제되나 아래의 목적을 위해 개인정보를 보관합니다.</p>
            <p>(단, 고객이 완전 삭제를 요할경우 바로 삭제됩니다.)</p>
            <p>a. 신청자가 작업한 내용을 확인할수 있도록 하기 위해서</p>
            <p>b. 추가적인 작업을 요할경우 정보의 중복된 기재를 줄이기위해서</p>
            <p>제로디자인 개인정보취급방침은 다음과 같은 내용을 담고있습니다.</p>
            <p><br></p>
            <p>1. 개인정보수집 및 이용목적</p>
            <p>2. 개인정보 수집항목</p>
            <p>3. 개인정보 보유 및 이용기간</p>
            <p>4. 개인정보 공유 및 제공</p>
            <p>5. 개인정보 취급위탁</p>
            <p>6. 이용자및 법정대리인의 권리와 그 행사방법</p>
            <p>7. 개인정보 자동수집 장치의 설치 운영 및 그 거부에 관한 사항</p>
            <p>8. 개인정보 관리 책임자</p>
            <p><br></p>
            <p>1. 개인정보 수집 및 이용</p>

            <p>제로디자인는 수집된 개인정보를 아래의 목적을 위해 활용됩니다.</p>
            <p>a. 로고제작에 필요한 정보를 전달받고 제작된 시안과 데이터를 전송하기위해 사용됩니다.</p>
            <p>b. 인쇄물의 정확한 배송을 위해 사용됩니다.</p>
            <p>c. 제로디자인의 서비스변경에 대한 알림과 기타 공지에 사용됩니다.</p>
            <p><br></p>
            <p>2. 개인정보 수집항목</p>
            <p><br></p>
            <p>이름, 핸드폰, 이메일, 회사명(국,영문), 홈페이지, 전화</p>

            <p>3. 개인정보 보유 및 이용기간</p>
            <p>제로디자인의 수집된 개인정보는 로고제작이 완료되면 원칙적으로 삭제되나 아래의 목적을 위해 개인정보를 보관합니다.</p>
            <p>(단, 고객이 완전 삭제를 요할경우 바로 삭제됩니다.)</p>
            <p>a. 신청자가 작업한 내용을 확인할수 있도록 하기 위해서</p>
            <p>b. 추가적인 작업을 요할경우 정보의 중복된 기재를 줄이기 위해서</p>
            <p><br></p>
            <p>4. 개인정보 공유 및 제공</p>
            <p>제로디자인는 신청자의 개인정보를 윈칙적으로 제3자나 외부에 제공하지 않습니다.</p>
            <p>로고 제작에 필요한 목적 외에는 다른 용도로 제공하지 않습니다.(아래의 경우는 예외)</p>
            <p>a. 신청자가 사전에 동의 한 경우</p>
            <p>b. 법령의 규정에 의하거나, 수사 목적으로 법령에 정해진 절차와 방법에 따라 요구가 있는 경우</p>
            <p><br></p>
            <p>5. 개인정보 취급 위탁</p>
            <p>제로디자인는 신청자의 개인정보를 외부 업체에 위탁하지 않습니다.</p>
            <p>그러한 필요성이 발생하면 모든 내용을 신청자에게 통보하여 사전동의를 받도록 하겠습니다.</p>
            <p><br></p>
            <p>6. 이용자 및 법정 대리인의 권리와 그 행사방법</p>
            <p>신청자와 법정 대리인은 언제든지 등록된 개인정보 수정과 삭제를 요청할 수 있습니다.</p>
            <p>전화나 이메일 팩스를 통해 요구사항을 전달해 주시면 바로 조치하겠습니다.</p>
            <p><br></p>
            <p>7. 개인정보 자동수집장치의 설치 운영 및 그 거부에 관한 사항</p>
            <p>제로디자인는 신청자의 정보를 수시로 저장하고 찾아내는"쿠키"등 개인정보를 자동으로 수집하는 장치를 사용하지 않습니다.</p>
            <p><br></p>
            <p>8. 개인정보 관리 책임자</p>
            <p>제로디자인는 고객의 개인정보를 보호와 불만을 신속하게 처리할 수 있도록 아래와 같이 개인정보관리책임자를 지정하고 있습니다.</p>
            <p><br></p>
            <p>이름 : 이준상</p>
            <p>전화 : 02-3445-1851</p>
            <p>이메일 : zerologo@nate.com</p>
            <p><br></p>

        </div>
        <div class="btn-check-agree">
            <input type="checkbox" name="agree" id="agree">
            <label for="agree"><span>개인정보수집에 대한 내용을 읽었으며 이에 동의합니다.</span></label>
        </div>
        <h5>
        </h5>
        <p>회원가입시 적립금 10,000원 제공
            <br>구매하신 상품의 2% 추가적립
        </p>
        <ul>
        </ul>
    </div>
    <div class="controll_btn">
        <button class="btn pink_btn btn_ok">비회원 구매하기</button>
        <a class="link-button-signup"
            href="<?php echo esc_url(get_permalink( get_page_by_title( '회원가입' ))); ?>"><?php esc_html_e('회원가입', 'woocommerce'); ?></a>
    </div>

    <a class="link-button-login"
        href="<?php echo esc_url(get_permalink( get_page_by_title( '로그인' )));?>"><?php esc_html_e('로그인하기', 'woocommerce'); ?></a>
</section>

<?php
}

// add_filter('kboard_list_where', 'my_kboard_list_where', 10, 3);

// function my_kboard_list_where($where, $board_id, $content_list){
// 	if($board_id == '2'){ 
// 		$user_id = get_current_user_id();
// 		if($user_id){
// 			$where .= "AND (`status`='' OR `status` IS NULL) OR `member_uid`='{$user_id}'";
// 		}
// 		else{
// 			$where .= "AND (`status`='' OR `status` IS NULL)";
// 		}
// 	}
// 	return $where;
// }


add_filter('kboard_list_where', 'my_kboard_list_where2', 10, 3);
function my_kboard_list_where2($where, $board_id, $content_list){
	if($board_id == 2){
        
		$user_id = get_current_user_id();
		$category_name = isset($_GET['category1']) && $_GET['category1'] ? sanitize_text_field($_GET['category1']) : '';

        if($user_id){
			$board = new KBoard($board_id);
			if(!$board->isAdmin()){
                if($category_name){
                    $where .= "AND (`status`='' OR `status` IS NULL) OR ( `category1`='{$category_name}' AND (`member_uid`='{$user_id}' AND `board_id` = '2'))";
			
                } else {
                    $where .= "AND (`status`='' OR `status` IS NULL) OR (`member_uid`='{$user_id}' AND `board_id` = '2')";

                }

            }
		}
		else{
			$where .= "AND (`status`='' OR `status` IS NULL)";
		}
	}
	return $where;
}



add_action('admin_init', 'remove_admin_menu');

if (!function_exists('remove_admin_menu')) {
	function remove_admin_menu() {
		
        //remove_menu_page( 'index.php' );        
        //remove_menu_page( 'separator1' );
        //remove_menu_page('edit.php'); //글 
        //remove_menu_page('upload.php'); //미디어              
        //remove_menu_page( 'edit.php?post_type=page' ); // 페이지    
        remove_menu_page( 'edit-comments.php' ); // 댓글      
        //remove_menu_page( 'kboard_store' ); // 스토어        
        remove_menu_page( 'scs-custom-script.php' );
        remove_menu_page( 'separator-woocommerce' ); // 구분선        
        remove_menu_page( 'woocommerce-marketing' ); //마케팅
        //remove_menu_page( 'separator-elementor' ); // 구분선
        //remove_menu_page( 'elementor' ); //엘레멘토
        //remove_menu_page( 'edit.php?post_type=elementor_library' ); // 템플릿
        //remove_menu_page( 'separator2' ); // 구분선
        //remove_menu_page( 'themes.php' ); // 테마
        //remove_menu_page( 'plugins.php' ); // 플러그인
        //remove_menu_page( 'tools.php' ); //도구
        //remove_menu_page( 'options-general.php' ); //설정
        //remove_menu_page( 'cosmosfarm_members_setting' ); //회원가입관리
        remove_menu_page( 'separator-last' ); //구분선
        remove_menu_page( 'premium-addons' ); // premium addons for elementor
        remove_menu_page( 'NinjaFirewall' ); // ninjaFirewall
        remove_menu_page( 'Security Settings' ); //xml-rpc security
         
        }
}



add_action('admin_init', 'change_admin_menu_label');

function change_admin_menu_label() {
    global $menu;
    global $submenu;
    //$menu[5][0] = '결제 관리';
    
    //$menu[7][0] = '게시판 관리';
    //$menu[9][0] = '고객 문의 관리';
    //$menu[12][0] = '쇼핑몰 관리';
    //$menu[22][0] = '회원관리';
   
    $submenu['woocommerce'][2][0] = '포인트 적립';
    $submenu['woocommerce'][3][0] = '주문 취소 요청';
    $submenu['quform.dashboard'][0][0] ='대시보드';
    $submenu['quform.dashboard'][1][0] ='양식';
    $submenu['quform.dashboard'][2][0] ='양식추가';

    $submenu['quform.dashboard'][3][0] ='접수된문의';
    $submenu['quform.dashboard'][4][0] ='도구';
    $submenu['quform.dashboard'][5][0] ='설정';


    remove_submenu_page( 'options-general.php', 'options-writing.php');  
    remove_submenu_page('themes.php', 'widgets.php');
    remove_submenu_page('themes.php', 'customize.php?return=%2Fwp-admin%2F');
    
    remove_submenu_page('wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/coupons'); //분석->쿠폰
}



add_action('admin_bar_menu', 'change_admin_var_item_label', 999);

function change_admin_var_item_label($wp_admin_bar) {
    $wp_admin_bar->remove_node('wp-logo');
    $wp_admin_bar->remove_node('updates');
    $wp_admin_bar->remove_node('comments');
    $wp_admin_bar->remove_node('new-content');
    $wp_admin_bar->remove_node('quform');
    $wp_admin_bar->remove_node('premium-addons');
    

}



/*이용 후기 게시글 관리자 [승인 대기] 문구 제거 */

add_filter('kboard_pending_approval_title', 'my_kboard_pending_approval_title', 10, 2);
function my_kboard_pending_approval_title($title, $content) {
    //$user_id = get_current_user_id();
    if(current_user_can('administrator')) {
        return $title;
    }
    $title = $content->row->title;
    return $title;
    
}

add_filter('kboard_pending_approval_content', 'my_kboard_pending_approval_content', 10, 2);
function my_kboard_pending_approval_content($title, $content) {
    $content = $content->row->content;
    return $content;
    
}


add_action('woocommerce_order_tracking_form_start', 'order_tracking_form_start_addtags');

function order_tracking_form_start_addtags() {
    echo '<div class="track-order-header">';   
    echo '<h1>비회원 주문조회</h1>';
    echo '<p>주문번호는 주문에 사용된 이메일 주소에서 확인할 수 있습니다.</p>';
    echo '</div>';
}

add_action('woocommerce_order_tracking_form_end', 'order_tracking_form_end_addtags');

function order_tracking_form_end_addtags() {
    echo '<div class="track-order-footer">';    
    echo '<p>주문번호를 잊으신 경우, 고객센터 02-3345-1851로 문의하여 주시기 바랍니다.</p>';
    echo '</div>';
}


/*** 회원정보 주문내역 상세 ***/
/**
 * woocommerce-order-details hook
 */

 add_action('woocommerce_order_details_before_order_table', 'test_woocommerce_order_details_before_order_table');


 function test_woocommerce_order_details_before_order_table() {
    
 }

 /**
  *  회원정보-주문내역 없을 시 상점 링크 
  */
  add_action('wc_after_no_order_message', 'btn_redirect_to_shop');

  function btn_redirect_to_shop($has_orders) {
    $shop_url = esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); 
    echo '<div class="container-link-to-shop-redirect">';    
    echo "<a href='{$shop_url}'>";
    echo '<span>로고 제작 신청하기</span>';
    echo '</a>';
    echo '</div>';

}


add_action( 'wp_footer', 'auto_refresh_cart_amount' ); 

function auto_refresh_cart_amount() { 
    if (is_checkout() && !is_order_received_page() && !is_user_logged_in()) { 
        
        ?>
<script type="text/javascript">
const action_popup = {
    BuyConfirmNonmember: function(callback) {

        jQuery('.type-confirm .btn_ok').on('click', function(e) {

            if (!jQuery('.btn-check-agree input[name="agree"]').is(":checked")) {
                e.preventDefault();
                alert('개인정보수집을 읽고 이에 대해 동의하셔야 합니다.');
            } else {
                jQuery('.btn-check-agree input[name="agree"]').prop("checked", false);
                jQuery(this).unbind('click');

                callback(true);
                action_popup.close(this);
            }


        });
        this.open();
    },
    open: function() {
        jQuery('body').append('<div class="dimLayer"></div>');

        jQuery('.dimLayer').css('height', '100vh');
        jQuery('.type-confirm').fadeIn(100);
    },
    close: function(target) {
        const modal = jQuery(target).closest('.modal-section');
        const dimLayer = jQuery('.dimLayer');
        if (modal.hasClass('type-confirm')) {
            jQuery('.type-confirm .btn_ok').unbind('click');
        } else {
            console.warn('close unknown target');
            return;
        }

        modal.fadeOut(100);
        setTimeout(() => {
            dimLayer != null ? dimLayer.remove() : '';
        }, (100));
    }
}

jQuery('.checkout-button').on('click', function(e) {

});

action_popup.BuyConfirmNonmember(function(res) {
    if (res) {
        action_popup.close(this);

    }
});

jQuery('.modal_close').on('click', function() {
    action_popup.close(this);
    history.back();
});



setTimeout(function() {
    if (jQuery('.variation_id').val() == '0') {
        jQuery('.cosmosfarm-quick-buy.button').css('opacity', '0.5');
        jQuery('.cosmosfarm-quick-buy.button').css('cursor', 'not-allowed');
    } else {
        jQuery('.cosmosfarm-quick-buy.button').css('opacity', 'unset');
        jQuery('.cosmosfarm-quick-buy.button').css('cursor', 'pointer');
    }
}, 120);
jQuery('.variation_id').change(function() {
    if (jQuery('.variation_id').val() == '') {
        jQuery('.cosmosfarm-quick-buy.button').css('opacity', '0.5');
        jQuery('.cosmosfarm-quick-buy.button').css('cursor', 'not-allowed');
    } else {
        jQuery('.cosmosfarm-quick-buy.button').css('opacity', 'unset');
        jQuery('.cosmosfarm-quick-buy.button').css('cursor', 'pointer');
    }
});
</script>
<?php 
    } 
}


function filter_woocommerce_checkout_cart_item_quantity( $item_qty, $cart_item, $cart_item_key ) {
    $remove_link = apply_filters('woocommerce_cart_item_remove_link',
    sprintf(
        '<a href="#" class="remove checkout-product-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s">&times;</a>',
        __( 'Remove this item', 'woocommerce' ),
        esc_attr( $cart_item['product_id'] ),
        esc_attr( $cart_item['data']->get_sku() ),
        esc_attr( $cart_item_key )
    ),
    $cart_item_key );

    // Return
    return $item_qty . $remove_link;
}
add_filter( 'woocommerce_checkout_cart_item_quantity', 'filter_woocommerce_checkout_cart_item_quantity', 10, 3 );

// jQuery - Ajax script
function action_wp_footer() {
    // Only checkout page
    if ( ! is_checkout() )
        return;
    ?>
<script type="text/javascript">
jQuery(function($) {
    $('form.checkout').on('click', '.cart_item a.remove', function(e) {
        e.preventDefault();

        var cart_item_key = $(this).attr("data-cart_item_key");

        $.ajax({
            type: 'POST',
            url: wc_checkout_params.ajax_url,
            data: {
                'action': 'woo_product_remove',
                'cart_item_key': cart_item_key,
            },
            success: function(result) {
                $('body').trigger('update_checkout');
                //console.log( 'response: ' + result );
            },
            error: function(error) {
                //console.log( error );
            }
        });
    });
});
</script>
<?php

}
add_action( 'wp_footer', 'action_wp_footer', 10, 0 );

// Php Ajax
function woo_product_remove() { 
    if ( isset( $_POST['cart_item_key'] ) ) {
        $cart_item_key = sanitize_key( $_POST['cart_item_key'] );
        
        // Remove cart item
        WC()->cart->remove_cart_item( $cart_item_key );
    }
    
    // Alway at the end (to avoid server error 500)
    die();
}
add_action( 'wp_ajax_woo_product_remove', 'woo_product_remove' );
add_action( 'wp_ajax_nopriv_woo_product_remove', 'woo_product_remove' );


/**
 * 포트폴리오 카테고리 정렬 순서 변경
 */
function portfolio_category_by_asc($query) {
    $query->set('order', 'ASC');
}
add_action('elementor/query/asc_sort_filter', 'portfolio_category_by_asc');