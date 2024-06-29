<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generalsetting extends Model
{
    
    protected $fillable = ['logo','sidebar_logo','logo_ar','management','management_ar','press','press_ar','press_image','contact_emails','paymentsicon','favicon', 'home_about','home_about_ar', 'home_about_img1','home_about_img2', 'home_about_img3','home_about_img4', 'title', 'title_ar','contact_icon','is_brand','messenger','is_messenger','is_shop','policy','policy_ar','brandphoto','header_email','free_shipping','refelar_points','header_phone', 'footer', 'footer_ar','copyright','copyright_ar','phone','points','colors','loader','accept_key','paymentid','accept_merchant','framepaymentt','admin_loader','messagee' ,'messagee_ar','subscribee_message','subscribee_message_ar','wallet_photo','loyalty_photo','talkto','drift','address','address_ar','phon','email','map_key','disqus','stripe_key','stripe_secret','currency_format','withdraw_fee','withdraw_charge','tax','shipping_cost','smtp_host','smtp_port','smtp_user','smtp_pass','from_email','from_name','add_cart','out_stock','already_cart','add_wish','already_wish','wish_remove','add_compare','already_compare','compare_remove','color_change','coupon_found','no_coupon','already_coupon','order_title','order_text','is_affilate','affilate_charge','affilate_banner','fixed_commission','templatee_select','percentage_commission','multiple_shipping','vendor_ship_info','cod_text','paypal_text','stripe_text','header_color','footer_color','copyright_color','menu_color','menu_hover_color','is_verification_email','instamojo_key','instamojo_token','instamojo_text','instamojo_sandbox','paystack_key','paystack_email','paystack_text','wholesell','is_capcha','error_banner','popup_title' ,'popup_title_ar','popup_text','popup_text_ar','popup_background','invoice_logo','user_image','vendor_color','is_secure','paypal_business','footer_logo','email_encryption','paytm_merchant','paytm_secret','paytm_website','paytm_industry','is_paytm','paytm_text','paytm_mode','molly_key','molly_text','razorpay_key','razorpay_secret','razorpay_text','maintain_text','cancel_url','fawry_secret','bostaakey','working_hours','name_api','password_api','merchant_name','merchant_id','password_nbe','merchant_nbe','merchant_id_nbe','name_nbe','fastlookey','aramexuser','aramex_password','accountnum','api_key','publickey','privatekey','fedex_password','fedexaccount','feature_icon','best_icon','top_icon','big_icon','new_icon','hot_icon','trending_icon','discount_icon','abs_username','abs_password','allow_zip','allow_shipto','allow_pickup','mylerz_username','mylerz_password','thawani_publish','thawani_secret','thawani_link','fatoratoken','paypal_secret','paypal_client_id','paypal_status','percent_title','percent_title_ar','percent_value','choose_title','choose_detail','choose_title_ar','choose_detail_ar','home_about_link','about_background','service_background','contact_background','about_page_background'];

    public $timestamps = false;


    public function upload($name,$file,$oldname)
    {
                $file->move('assets/images',$name);
                if($oldname != null)
                {
                    if (file_exists(public_path().'/assets/images/'.$oldname)) {
                        unlink(public_path().'/assets/images/'.$oldname);
                    }
                }
    }
}
