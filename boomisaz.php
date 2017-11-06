<?php
/**
 * Localization Plugin for PrestaPlus
 *
 * @website		Prestafa.com
 * @copyright	(c) 2017 - Prestafa Team
 * @since		5 Aug 2017
 * @license		GPL V3
 */

if( !defined('_PS_VERSION_'))
    exit;

class Prestaplus_boomisaz extends PrestaplusPlugin
{
    /** Errors */
    public $errors = array();

    /**
     * Initialize plugin
     *
     * @return	void
     */
    public function init()
    {
        $this->name 		= 'boomisaz';
        $this->version 		= '0.9.7';
        $this->displayName 	= $this->module->l('بومی ساز پرستافا','boomisaz');
        $this->description 	= $this->module->l('به کمک این پلاگین میتوان پرستاشاپ را بومی سازی کرد','boomisaz');
        $this->authorName 	= 'پرستافا';
        $this->authorUrl 	= 'http://prestafa.com';
        $this->adminIcon 	= 'icon-globe';

        $this->hooks = array(
            'actionDispatcher',
            'actionObjectUpdateBefore',
            'actionObjectAddBefore',
            'actionAdminLoginControllerSetMedia',
            'actionAdminControllerSetMedia',
            'displayBackOfficeHeader',
            'displayHeader',
            'displayCustomerAccountForm',
            'displayCustomDate',
            'dashboardZoneOne',
        );

        $this->configs = array(
            'PSFPLUS_FONT_BACKOFFICE' 	=> 'vazir',
            'PSFPLUS_FONT_FRONTOFFICE' 	=> 'vazir',
            'PSFPLUS_JALALI_DATE' 	 	=> 1,
            'PSFPLUS_TINYMCE' 	 		=> 1,
        );
    }

    /**
     * Install Plugin
     *
     * @return	bool
     */
    public function install()
    {
        if ( !parent::install() )
            return false;

        $this->encryptedCompatibility();
        $this->setRightToLeft();
        $this->modifyTranslation();
        $this->fixJavaScriptBug();

        $this->module->updatePosition(Hook::getIdByName('dashboardZoneOne'),0,1);

        return true;
    }

    /*
     |--------------------------------------------------------------------------
     | install
     |--------------------------------------------------------------------------
     */

    /**
     * Get Plugin Fonts Dir
     *
     * @return	string
     */
    public function getPluginFontsDir()
    {
        return $this->getPluginPath().'views/fonts';
    }

    /**
     * Get Plugin Css Dir
     *
     * @return	string
     */
    public function getPluginCssDir()
    {
        return $this->getPluginPath().'views/css';
    }

    /**
     * Compatiple PrestaShop core with ioncube
     *
     * @return	bool
     */
    public function encryptedCompatibility()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true )
            return true;

        $classPath = _PS_CLASS_DIR_.'module/Module.php';

        if( !file_exists($classPath) )
            return false;

        $fileOpen = fopen($classPath, 'r');
        $fileData = fread($fileOpen,filesize($classPath));
        fclose($fileOpen);

        if( !file_exists($classPath.'.backup') )
        {
            $backupOpen = fopen($classPath.'.backup', 'w');
            fwrite($backupOpen, $fileData);
            fclose($backupOpen);
        }

        $replaces = array(
            'eval(\'if (false){  \'.$file.\' }\') !== false'     	=> '$file /*pplus*/',
            'eval(\'if (false){  \'.$file."\n".\' }\') !== false'   => '$file /*pplus*/',
            'eval(\'if (false){ \'.$file.\' }\') !== false'     	=> '$file /*pplus*/',
            'eval(\'if (false){	\'.$file."\n".\' }\') !== false'   	=> '$file /*pplus*/',
        );

        foreach($replaces as $old => $new)
        {
            $fileData = str_replace( $old, $new, $fileData);
        }

        $writeFile = fopen($classPath, 'w');
        fwrite($writeFile, $fileData);
        fclose($writeFile);

        return true;
    }

    /**
     * Set RTL for Persian/Farsi
     *
     * @return	bool
     */
    public function setRightToLeft()
    {
        $persianLangId = (int) LanguageCore::getIdByIso('fa');
        if ($persianLangId) {
            $language = new LanguageCore($persianLangId);
            if (!$language->is_rtl) {
                $language->is_rtl = 1;
                return $language->update();
            }
        }
    }


    /**
     * Modify & Update langs for default-bootstrap
     *
     * @return	bool
     */
    public function modifyTranslation()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true )
            return true;

        if( $this->context->theme->name != 'default-bootstrap' )
            return true;

        require_once( $this->getPluginPath().'_import/Lang_default_theme.php');

        $file_lang_theme = _PS_THEME_DIR_.'lang/fa.php';
        $_LANG = array();

        if (!file_exists($file_lang_theme))
        {
            if (!file_exists(dirname($file_lang_theme)) && !mkdir(dirname($file_lang_theme), 0777, true))
            {
                return false;
            }
            else if (!touch($file_lang_theme))
            {
                return false;
            }
        }

        if (file_exists($file_lang_theme))
            include_once($file_lang_theme);

        $_LANG_MERGED = array_merge($_LANG,$_LANG_PLUS);
        ksort($_LANG_MERGED);
        $open = @fopen($file_lang_theme, 'w');

        if ( !$open )
            return false;

        $tab = '_LANG';
        fwrite($open, "<?php\n\nglobal \$".$tab.";\n\$".$tab." = array();\n");
        foreach ($_LANG_MERGED as $key => $value) {
            fwrite($open, '$'.$tab.'[\''.pSQL($key, true).'\'] = \''.pSQL($value, true).'\';'."\n");
        }

        fwrite($open, "\n?>");
        fclose($open);

        return true;
    }

    /**
     * Fix rtl.js Bug for BackOffice
     *
     * @return	bool
     */
    public function fixJavaScriptBug()
    {
        if ( version_compare(_PS_VERSION_, '1.7.0', '>=') && version_compare(_PS_VERSION_, '1.7.3.0', '<=') )
        {
            return copy( $this->getPluginPath().'views/js/rtl.js', _PS_ROOT_DIR_ .'/js/rtl.js');
        }

        return true;
    }

    /*
     |--------------------------------------------------------------------------
     | Configure
     |--------------------------------------------------------------------------
     */

    /**
     * Configure page for plugin
     *
     * @return	string
     */
    public function configure()
    {
        $output = '';
        $fields = array(
            'PSFPLUS_FONT_BACKOFFICE'	=>	'',
            'PSFPLUS_FONT_FRONTOFFICE'	=>	'',
            'PSFPLUS_JALALI_DATE'		=>	'',
            'PSFPLUS_TINYMCE'		    =>	'',
        );

        $soption = array(
            array(
                'id' => 'active_on',
                'value' => 1,
                'label' => $this->module->l('Enabled','boomisaz')
            ),
            array(
                'id' => 'active_off',
                'value' => 0,
                'label' => $this->module->l('Disabled','boomisaz')
            )
        );

        if ( !Tools::isSubmit('submitRemode')  and Tools::isSubmit('submit'.$this->module->name) )
        {
            foreach( $fields as $key => $field )
            {
                if( in_array($key,array('PSFPLUS_FONT_FRONTOFFICE','PSFPLUS_FONT_BACKOFFICE')) and Configuration::get($key) != Tools::getValue($key)){
                    $this->createFileCssFont($key);
                }
                else
                    Configuration::updateValue($key, Tools::getValue($key) );
            }

            if( count( $this->errors ) )
                $output .= $this->module->displayError($this->errors);
            else
                $output .= $this->module->displayConfirmation($this->module->l('تنظیمات با موفقیت به روز شد !','boomisaz'));

        }

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->module->l('تنظیمات','boomisaz'),
                'icon' => 'icon-globe'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->module->l('فونت بخش مدیریت','boomisaz'),
                    'name' => 'PSFPLUS_FONT_BACKOFFICE',
                    'options' => array(
                        'query' => $this->getFonts(),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->module->l('نوع فونت بخش مدیریت را مشخص می کند ، بعد از تغییر فونت برای اعمال صفحه را با ctrl+f5 مجدد بارگیری کنید.','boomisaz'),
                ),
                array(
                    'type' => 'select',
                    'label' => $this->module->l('فونت بخش کاربری - قالب پیش فرض','boomisaz'),
                    'name' => 'PSFPLUS_FONT_FRONTOFFICE',
                    'options' => array(
                        'query' => $this->getFonts(),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->module->l('نوع فونت بخش کاربری را مشخص می کند ، بعد از تغییر فونت برای اعمال صفحه را با ctrl+f5 مجدد بارگیری کنید.','boomisaz'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('فعال سازی تاریخ جلالی','boomisaz'),
                    'name' => 'PSFPLUS_JALALI_DATE',
                    'default' => 1,
                    'values' => $soption,
                    'required' => true,
                    'desc' => $this->module->l('در صورت فعال کردن این گزینه تاریخ پرستاشاپ به جلالی تغییر خواهد کرد.','boomisaz'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->module->l('راست چین سازی ادیتور متن','boomisaz'),
                    'name' => 'PSFPLUS_TINYMCE',
                    'default' => 1,
                    'values' => $soption,
                    'required' => true,
                    'desc' => $this->module->l('این گزینه برای اصلاح ادیتور متن می باشد ، در صورتی که نیاز دارید بومی ساز تغییری در ادیتور متن ایجاد نکند ، آن را غیرفعال کنید.','boomisaz'),
                )
            ),
            'submit' => array(
                'title' => $this->module->l('Save','boomisaz'),
                'class' => 'btn btn-default pull-right'
            )
        );



        if( Tools::isSubmit('submitRemode') )
        {
            if( Tools::getValue('PSFPLUS_REMODE') == '1' )
            {
                if( $this->encryptedCompatibility() ){
                    $output .= $this->module->displayConfirmation($this->module->l('سازگاری با ماژول های کد شده انجام شد.','boomisaz'));
                }else{
                    $output .= $this->module->displayError($this->module->l('مشکل در انجام عملیات سازگاری','boomisaz'));
                }
            }
        }

        // Init Fields form array
        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->module->l('سازگاری با ماژول های کد شده','boomisaz'),
                'icon' => 'icon-eraser'
            ),
            'input' => array(
                array(
                    'type' => 'switch',
                    'is_bool' => true,
                    'label' => $this->module->l('سازگاری با ماژول های کد شده','boomisaz'),
                    'name' => 'PSFPLUS_REMODE',
                    'values' => $soption,
                    'desc' => array(
                        $this->module->l('در صورتی که پرستاشاپ با ماژول های کد شده بعد از نصب اولیه یا ارتقا به نسخه بالاتر مشکل داشت از این گزینه استفاده کنید.','boomisaz'),
                        $this->module->l('توصیه میشود بعد از هر بار ارتقا این کار را انجام دهید.','boomisaz'),
                    )
                ),
            ),
            'submit' => array(
                'title' => $this->module->l('سازگاری','boomisaz'),
                'class' => 'btn btn-default pull-right',
                'name' => 'submitRemode',
                'id' => 'submitRemode',
            )
        );

        $form = new PrestaplusForm($this->module);

        $form->setFieldValue('PSFPLUS_FONT_BACKOFFICE');
        $form->setFieldValue('PSFPLUS_FONT_FRONTOFFICE');
        $form->setFieldValue('PSFPLUS_JALALI_DATE');
        $form->setFieldValue('PSFPLUS_TINYMCE');
        $form->setFieldValue('PSFPLUS_REMODE');

        return	$output.$form->generateForm( $fields_form );

    }

    /**
     * get all fonts as «views/fonts»
     *
     * @return	array
     */
    public function getFonts()
    {
        $list_fonts = $this->getDefaultFonts();
        $custum_fonts =  array_diff( scandir($this->getPluginFontsDir()) , array('..', '.') );

        foreach($custum_fonts as $font)
        {
            if( !is_dir( $this->getPluginFontsDir() .'/'.$font ))
                continue;

            if( !array_key_exists($font, $list_fonts)) {
                $list_fonts[$font] = array(
                    'name' => $font,
                    'id'   => $font,
                );
            }
        }

        return $list_fonts;
    }

    /**
     * get default fonts
     *
     * @return	array
     */
    public function getDefaultFonts()
    {
        return array(
            'vazir' 	=> array('name'=>'وزیر','id'=>'vazir'),
            'tanha' 	=> array('name'=>'تنها','id'=>'tanha'),
            'shabnam' 	=> array('name'=>'شبنم','id'=>'shabnam'),
            'samim' 	=> array('name'=>'صمیم','id'=>'samim'),
            'parastoo' 	=> array('name'=>'پرستو','id'=>'parastoo'),
            'sahel' 	=> array('name'=>'ساحل','id'=>'sahel'),
            'gandom' 	=> array('name'=>'گندم','id'=>'gandom'),
            'yekan' 	=> array('name'=>'یکان','id'=>'yekan'),
        );
    }

    /**
     * create css file by font setting
     *
     * @param 	$key
     * @return	bool
     */
    public function createFileCssFont( $key  )
    {
        $font = Tools::getValue($key);

        $woff_file_path  =  $this->getPluginFontsDir() .'/' .$font .'/' .$font .'.woff';
        $woff2_file_path =  $this->getPluginFontsDir() .'/' .$font .'/' .$font .'.woff2';

        // check exist files font
        if ( !file_exists($woff_file_path) && !file_exists($woff2_file_path) ) {
            $this->errors[] = $this->l('فایل های فونت انتخاب شده موجود نمی باشد.');
            $this->errors[] = $this->l(sprintf('یکی از دو فایل %s یا %s را باید در فولدر فونت آپلود کنید.', $font .'.woff' , $font .'.woff2' ));
            return false;
        }

        // set options BO or FO
        $font_backoffice = ($key == 'PSFPLUS_FONT_BACKOFFICE')? true : false;
        $content_css 	 = $this->getCssTheme($font_backoffice,$font);

        if( $font_backoffice) {
            $file_font_css = $this->getPluginCssDir().'/admin/prestaplus-font.css';
        } else {
            if( version_compare(_PS_VERSION_, '1.7.0', '>=') )
                $file_font_css = $this->getPluginCssDir().'/prestaplus-font-classic.css';
            else
                $file_font_css = $this->getPluginCssDir().'/prestaplus-font-default-bootstrap.css';
        }

        // wirte array in file
        if ( file_exists($file_font_css) and  $fd = fopen($file_font_css, 'w')) {
            fwrite($fd, $content_css );
            fclose($fd);
        } else {
            $this->errors[] = $this->l('امکان ویرایش فایل استایل وجود ندارد.');
            return false ;
        }

        Configuration::updateValue($key, Tools::getValue($key) );
        return true;
    }

    /**
     * get stylesheet fontface
     *
     * @param $font_backoffice
     * @param $font
     * @return mixed|string
     */
    public function getCssTheme($font_backoffice , $font)
    {
        // path files font
        $woff_bold_file_path    =  $this->getPluginFontsDir() .'/' . $font . '/' . $font .'-bold.woff';
        $woff2_bold_file_path   =  $this->getPluginFontsDir() .'/' . $font . '/' . $font .'-bold.woff2';

        // create font-face bold
        $content_css = "@font-face {font-family: {nameFont};src:url('{revertFolder}fonts/{folder}/{folder}.woff') format('woff'),url('{revertFolder}fonts/{folder}/{folder}.woff2') format('woff2');font-weight: normal}";
        if( file_exists($woff_bold_file_path) or file_exists($woff2_bold_file_path)  )
            $content_css .= "@font-face {font-family: {nameFont};src:url('{revertFolder}fonts/{folder}/{folder}-bold.woff') format('woff'),url('{revertFolder}fonts/{folder}/{folder}-bold.woff2') format('woff2');font-weight: bold}";

        if( $font_backoffice )
        {
            $revertFolder = '../../';
            $content_css .= "@font-face{font-family:'Open Sans';src:url('{revertFolder}fonts/{folder}/{folder}.woff') format('woff'),url('{revertFolder}fonts/{folder}/{folder}.woff2') format('woff2'),local(tahoma)}@font-face{font-family:arial;src:url('{revertFolder}fonts/{folder}/{folder}.woff') format('woff'),url('{revertFolder}fonts/{folder}/{folder}.woff2') format('woff2'),local(tahoma)}@font-face{font-family:helvetica;src:url('{revertFolder}fonts/{folder}/{folder}.woff') format('woff'),url('{revertFolder}fonts/{folder}/{folder}.woff2') format('woff2'),local(tahoma)}@font-face{font-family:'Ubuntu Condensed';src:url('{revertFolder}fonts/{folder}/{folder}.woff') format('woff'),url('{revertFolder}fonts/{folder}/{folder}.woff2') format('woff2'),local(tahoma)}";
            $content_css .= ".h1,.h2,.h3,.h4,.h5,.h6,.module-modal-title>h4,.module-search-result-wording,.onboarding .panel .onboarding-intro h3,.popover,.pstaggerTagsWrapper,.select2-container--prestakit .select2-search--dropdown .select2-search__field,.select2-container--prestakit .select2-selection,.tooltip,body,h1,h2,h3,h4,h5,h6,.bootstrap #login-panel #shop_name,.onboarding-intro h3.text-center{font-family:{nameFont},sans-serif}";
        }
        else{
            $revertFolder = '../';

            if( version_compare(_PS_VERSION_, '1.7.0', '>=') )
                $content_css .=  "body,.ui-menu-item{font-family:{nameFont}, sans-serif}";
            else
                $content_css .=  "body {font-family: {nameFont}, sans-serif}#availability_value,#cart_summary .product-name a,#cart_summary tfoot td#total_price_container,#cart_summary tfoot td.total_price_container span,#cart_summary tfoot tr td,#categories_block_left li a,#categories_block_left li li a,#cmsinfo_block h3,#cmsinfo_block p,#contact-link a,#currencies-block-top div.current,#currencies-block-top div.current strong,#currencies-block-top ul li,#facebook_block h4,#footer #newsletter_block_left h4,#home-page-tabs>li a,#homepage-slider .homeslider-description button,#languages-block-top div.current,#languages-block-top ul li a,#languages-block-top ul li>span,#last_quantities,#layer_cart .layer_cart_cart .layer_cart_row span,#layer_cart .layer_cart_cart .layer_cart_row strong,#layer_cart .layer_cart_cart .title,#layer_cart .layer_cart_cart h2,#layer_cart .layer_cart_product .layer_cart_product_info #layer_cart_product_attributes,#layer_cart .layer_cart_product .layer_cart_product_info #layer_cart_product_title,#layer_cart .layer_cart_product .layer_cart_product_info>div span,#layer_cart .layer_cart_product .layer_cart_product_info>div strong,#layer_cart .layer_cart_product .title,#layer_cart .layer_cart_product h2,#layered_block_left .layered_subtitle,#layered_price_range,#listpage_content,#my-account ul.myaccount-link-list li a span,#mywishlist table,#new_comment_form #new_comment_form_footer,#new_comment_form .product .product_desc .product_name strong,#pQuantityAvailable span,#pagenotfound .pagenotfound h1,#pagenotfound .pagenotfound h3,#pagenotfound .pagenotfound p,#pagenotfound form label,#pagination,#product_comments_block_tab .comment_author_infos,#product_comments_block_tab .comment_author_infos em,#product_comments_block_tab div.comment .comment_author span,#product_condition label,#product_condition span,#product_reference label,#sitemap_content,#subcategories ul li .subcategory-name,#usefull_link_block li a,#wishlist_button,#wishlist_button_nopop,.ac_results li,.alert,.block .products-block .price-percent-reduction,.block .title_block,.block h4,.breadcrumb,.button.ajax_add_to_cart_button,.button.button-medium,.button.button-small span,.button.lnk_view,.cart_block .cart-buttons a#button_order_cart span,.cart_block .cart-info .product-name a,.cart_block .cart-prices,.cart_voucher h4,.checkbox label,.comment_details ul li,.comment_details ul li .button.button-small span,.content_prices #old_price_display,.content_prices #reduction_percent_display,.content_scene_cat span.category-name,.content_sortPagiBar .display li a,.content_sortPagiBar .display_m li a,.fancybox-error,.fancybox-title-float-wrap .child,.footer-container #footer #block_contact_infos>div ul li,.footer-container #footer #social_block h4,.footer-container #footer .bottom-footer,.footer-container #footer h4,.footer-container #footer ul li a,.grid .product-container .functional-buttons .compare a:after,.grid .product-container .functional-buttons .wishlist a:after,.grid .product-container .functional-buttons a,.header_user_info a,.label,.layered_filter,.layered_filter label,.new-label,.old-price,.old-price.product-price,.our_price_display,.our_price_display #our_price_display,.page-heading,.page-heading span.heading-counter,.page-subheading,.pb-left-column #image-block #view_full_size .span_link,.price,.price-percent-reduction,.price-percent-reduction.small,.price.product-price,.product-count,.product-flags,.quantity-formated .quantity,.quick-view-wrapper-mobile,.radio label,.right-block .content_price,.sale-label,.sf-menu li li li a,.sf-menu>li>a,.sf-menu>li>ul>li>a,.shop-phone,.shopping_cart>a:first-child,.shopping_cart>a:first-child b,.table td.history_detail a+a,a.button,a.button_large,a.button_mini,a.button_small,a.exclusive,a.exclusive_large,a.exclusive_mini,a.exclusive_small,button,div.selector select,div.selector span,div.uploader span,h1,h2,h3,h3.page-product-heading,h4,h5,h6,input,input.button,input.button_disabled,input.button_large,input.button_large_disabled,input.button_mini,input.button_mini_disabled,input.button_small,input.button_small_disabled,input.exclusive,input.exclusive_disabled,input.exclusive_large,input.exclusive_large_disabled,input.exclusive_mini,input.exclusive_mini_disabled,input.exclusive_small,input.exclusive_small_disabled,label,select,span.button,span.button_large,span.button_mini,span.button_small,span.exclusive,span.exclusive_large,span.exclusive_large_disabled,span.exclusive_mini,span.exclusive_small,table .history_date,table#product_comparison tbody tr td.td_empty>span,textarea,th,ul.product_list .availability span,ul.product_list .product-image-container .quick-view,ul.product_list.grid>li .product-container .content_price,ul.product_list.list>li .right-block .right-block-content .functional-buttons a,ul.step li a,ul.step li span,ul.step li.step_current span,ul.step li.step_current_end span,ul.wlp_bought_list li .product-name{font-family: {nameFont}, sans-serif}@media (max-width:767px){.cat-title{font-family: {nameFont}, sans-serif}}";
        }

        $content_css = str_replace('{revertFolder}', $revertFolder , $content_css );
        $content_css = str_replace('{nameFont}',ucfirst( $font ) , $content_css );
        $content_css = str_replace('{folder}',$font , $content_css );

        return $content_css;
    }

    /*
     |--------------------------------------------------------------------------
     | Hook Methods
     |--------------------------------------------------------------------------
     */

    public function hookActionObjectUpdateBefore($params)
    {
        $this->convertDate($params['object']);
    }

    public function hookActionObjectAddBefore($params)
    {
        $this->convertDate($params['object']);
    }

    /**
     * Check value object and convert Date
     * @param $object
     */
    public function convertDate($object)
    {
        $class_name = get_class($object);
        $definition = ObjectModel::getDefinition($class_name);

        foreach ($definition['fields'] as  $field => $def)
        {
            if( isset($def['validate']) and in_array($def['validate'],array('isDate','isDateFormat')) )
            {
                $date = $object->$field;
                if ($date == '0000-00-00 00:00:00' || $date == '0000-00-00' || !$date )
                    continue;

                $dateArray = $this->getDateArray($date);
                if ( !( $dateArray['year'] > 1900 && $dateArray['year'] < 2100 )  )
                {
                    $object->$field = $this->getDate($date);
                }

            }
        }
    }

    /**
     * inculde class pdate the all pages
     * @param $params
     */
    public function hookActionDispatcher($params)
    {
        if( !class_exists('Pdate') && Configuration::get('PSFPLUS_JALALI_DATE') )
        {
            require_once( $this->getPluginPath().'_import/Pdate.php');
        }
    }

    /**
     * Add stylesheet on the login page
     */
    public function hookActionAdminLoginControllerSetMedia()
    {
        if( $this->context->language->is_rtl )
        {
            $this->context->controller->addCss($this->_path.'views/css/admin/prestaplus-font.css');
            $this->context->controller->addCss($this->_path.'views/css/admin/prestaplus-rtl.css');
        }
    }

    /**
     * add stylesheet and js on the back office
     */
    public function hookDisplayBackOfficeHeader()
    {
        if ( !$this->module->active )
            return null;

        if (method_exists($this->context->controller, 'addJquery'))
            $this->context->controller->addJquery();

        if ( !$this->isNewTheme() and  method_exists($this->context->controller, 'addJqueryUI'))
            $this->context->controller->addJqueryUI('ui.datepicker');

        if ($this->context->language->iso_code == 'fa')
        {
            $jalali_status = Configuration::get('PSFPLUS_JALALI_DATE');
            Media::addJsDef(array('psf_plus_jalali_status' => $jalali_status ));

            if( $jalali_status )
            {
                $path_timepicker = _PS_JS_DIR_ . 'jquery/plugins/timepicker/jquery-ui-timepicker-addon.js';
                $check_timepicker = array_search( $path_timepicker , $this->context->controller->js_files );

                if ( $check_timepicker )
                    $this->context->controller->removeJS($path_timepicker, false);

                $this->context->controller->addJS($this->_path.'views/js/admin/jquery.ui.datepicker-fa.js', 'all');
                $this->context->controller->addJS($this->_path.'views/js/admin/library-date.js', 'all');
                $this->context->controller->addJS($this->_path.'views/js/admin/admin.js', 'all');

                if ( $check_timepicker )
                    $this->context->controller->addJS($path_timepicker, 'all');
            }

            $tinymce_status = Configuration::get('PSFPLUS_TINYMCE');

            if( $tinymce_status ){
                $skin_tinymce = $this->_path .'views/css/tinymce/prestafa-skin.css';
                Media::addJsDef(array('psf_plus_skin_tinymce' => $skin_tinymce ));
                return '
                <style>
                /* EDITOR - CSS */
                .mce-rtl .mce-flow-layout{
                    text-align: right !important;
                    margin-left: inherit;
                    margin-right: 5px;
                }
                .mce-window.mce-in{
                    left: auto !important;
                    right: calc(50% - 215px) !important;
                }
                .mce-btn-group .mce-first {
                     border-radius: 3px 0 0 3px;
                }
                .mce-btn-group .mce-last {
                    border-right: solid 1px #ccc !important;
                }
                .mce-btn-group .mce-btn  {
                    border: solid 1px #ccc;
                    border-right: none;
                }
                </style>
                <script type="text/javascript" src="'. $this->_path. 'views/js/admin/tinymce-psf-plus.js"></script>';
            }
        }

    }


    public function hookActionAdminControllerSetMedia()
    {
        if( $this->context->language->is_rtl )
        {
            if ( $this->isNewTheme() )
                $this->context->controller->addCss($this->_path.'/views/css/admin/prestaplus-new-rtl.css');
            else
                $this->context->controller->addCss($this->_path.'/views/css/admin/prestaplus-rtl.css');

            $this->context->controller->addCss($this->_path.'/views/css/admin/prestaplus-font.css');
        }
    }

    /**
     * add stylesheet and js on the front office
     */
    public function hookDisplayHeader()
    {
        // if defualt theme /*#help# 1.7 */
        if ( $this->context->language->is_rtl)
        {
            if( version_compare(_PS_VERSION_, '1.7.0', '>=') /*and $this->context->theme->name == 'classic'*/  )
            {
                $this->context->controller->registerStylesheet(
                    'prestaplus-font-classic',
                    'modules/'.$this->module->name.'/'.$this->_plugDir.'/'.$this->name.'/views/css/prestaplus-font-classic.css',
                    [
                        'media' => 'all',
                        'priority' => 901,
                    ]
                );
                $this->context->controller->registerStylesheet(
                    'prestaplus-rtl-classic',
                    'modules/'.$this->module->name.'/'.$this->_plugDir.'/'.$this->name.'/views/css/prestaplus-rtl-classic.css',
                    [
                        'media' => 'all',
                        'priority' => 901,
                    ]
                );
            }
            else{
                if( $this->context->theme->name == 'default-bootstrap' ){

                    $this->context->controller->addCss($this->_path.'views/css/prestaplus-rtl-default-bootstrap.css');
                    $this->context->controller->addCss($this->_path.'views/css/prestaplus-font-default-bootstrap.css');
                }

                if ($this->context->language->iso_code == 'fa')
                {
                    $this->context->controller->addJS($this->_path.'views/js/admin/library-date.js', 'all');
                    $this->context->controller->addJS($this->_path.'views/js/brithdayJalali.js', 'all');
                }
            }
        }
    }

    /**
     * add js brithday on the front office
     */
    public function hookDisplayCustomerAccountForm()
    {
        if( version_compare(_PS_VERSION_, '1.7.0', '<')  )
        {
            if ($this->context->language->iso_code == 'fa')
                return '<script type="text/javascript" src="'.$this->_path. 'views/js/admin/library-date.js"></script>
                            <script type="text/javascript" src="'.$this->_path. 'views/js/brithdayJalali.js"></script>';
        }
    }

    /**
     * Show date jalali
     * @param $args
     * @return bool|string
     */
    public function hookDisplayCustomDate($args)
    {
        $jalali = Configuration::get('PSFPLUS_JALALI_DATE');

        if( !$jalali )
            return false;

        $time 	= isset($args['time'])? $args['time'] : null;
        $format = isset($args['date_format'])? $args['date_format'] : $this->context->language->date_format_full;

        if ($this->context->language->iso_code == 'fa')
        {
            if( !class_exists('Pdate') )
                require_once( $this->getPluginPath().'_import/Pdate.php');

            if( $time === null )
                return Pdate::pdate($format);

            return Pdate::pdate($format, $time);
        }

        return date($format, $time);
    }

    /**
     * Add Banner prestafa on the dashboard
     * @return string
     */
    public function hookDashboardZoneOne()
    {
        $imgLink = $this->getPluginUrl(). '/views/img/prestafa-tg.png';
        return '<a href="https://t.me/prestafa" target="_blank"><img style="width: 100%;padding-bottom: 10px;" src="'.$imgLink.'" /></a>';
    }

    /**
     * is new theme back office
     * @return bool
     */
    public function isNewTheme()
    {
        $page_new = array(
            'AdminProducts',
            'AdminModules',
            'AdminAddonsCatalog',
            'AdminTranslations'
        );

        $controller_class = get_class(  $this->context->controller );
        if ( version_compare(_PS_VERSION_, '1.7.0', '>=') === true &&
            (in_array($this->context->controller->controller_name,$page_new) && $controller_class != 'AdminTranslationsController' ) or
            $controller_class == "AdminLegacyLayoutControllerCore"
        )
            return true;

        return false;
    }

    public function getDateArray($date = NULL )
    {
        if ( !$date )
            return false;

        $dateParts = explode("/",  $date );
        if ( count($dateParts) > 1 ) {
            // '2017/07/10
            $dateParts = explode("/",  $date );
            return [
                "year"      => sprintf("%04d", intval($dateParts[0]) ) ,
                "month"     => sprintf("%02d", intval($dateParts[1]) ) ,
                "day"       => sprintf("%02d", intval($dateParts[2]) )
            ];
        } else {
            //hour, minute, second, month, day, year
            $dateParts = explode(" ",  $date );
            $dataDateParts = explode("-",  $dateParts[0] );

            if ( count($dateParts) > 1 ) {
                // '2017-07-10 15:00:00'
                $dataTimeParts = explode(":",  $dateParts[1] );
                return [
                    "year"      => sprintf("%04d", intval($dataDateParts[0]) ) ,
                    "month"     => sprintf("%02d", intval($dataDateParts[1]) ) ,
                    "day"       => sprintf("%02d", intval($dataDateParts[2]) ) ,
                    "hour"      => sprintf("%02d", intval($dataTimeParts[0]) ) ,
                    "minute"    => sprintf("%02d", intval($dataTimeParts[1]) ) ,
                    "second"    => sprintf("%02d", intval($dataTimeParts[2]) )
                ];
            } else {
                // '2017-07-10
                return [
                    "year"      => sprintf("%04d", intval($dataDateParts[0]) ) ,
                    "month"     => sprintf("%02d", intval($dataDateParts[1]) ) ,
                    "day"       => sprintf("%02d", intval($dataDateParts[2]) )
                ];
            }
        }
    }

    // get date gregorian for save database
    public function getDate( $date )
    {
        if( !class_exists('Pdate') )
            return $date;

        $dateArray = $this->getDateArray($date);
        if( $dateArray && count($dateArray) >= 3 )
        {
            if( $dateArray['year'] > 0 && $dateArray['year'] < 1600 )
            {
                $gDate = Pdate::jalali_to_gregorian($dateArray['year'], $dateArray['month'], $dateArray['day']);

                $gDate = sprintf("%04d", intval($gDate[0]) ) . '-' . sprintf("%02d", intval($gDate[1]) ) . '-' . sprintf("%02d", intval($gDate[2]) );
                $gTime = isset($dateArray['hour']) ? ' '. $dateArray['hour'].':'.$dateArray['minute'].':'.$dateArray['second'] : '';

                return $gDate .  $gTime ;
            }
        }
        return $date;
    }
}
