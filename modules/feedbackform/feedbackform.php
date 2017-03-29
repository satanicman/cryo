<?php

if (!defined('_PS_VERSION_'))

    exit;

class feedbackform extends Module

{
    public function __construct()
    {
        $this->name = 'feedbackform';
        $this->tab = 'other';
        $this->version = '0.1';
        $this->author = 'http://vk.com/id24260100';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('feedbackform');
        $this->description = $this->l('feedback Form in your shop');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MUMODULE_NAME'))
            $this->warning = $this->l('No name provided');
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
            !$this->registerHook('header') ||
            !$this->registerHook('footer') ||
            !Configuration::updateValue('FEEDBACK_FORM_EMAIL', Configuration::get('PS_SHOP_EMAIL')) ||
            !$this->setConfiguration()
        )
            return false;

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('FEEDBACK_FORM_EMAIL') ||
            !$this->unsetConfiguration())
            return false;

        return true;

    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitFeedbackForm'))
        {
            Configuration::updateValue('FEEDBACK_FORM_EMAIL', Tools::getValue('FEEDBACK_FORM_EMAIL', Configuration::get('PS_SHOP_EMAIL')));
            foreach (Language::getLanguages(false) as $language) {
                Configuration::updateValue('FEEDBACK_FORM_TITLE_'.$language['id_lang'], Tools::getValue('FEEDBACK_FORM_TITLE_'.$language['id_lang']));
                Configuration::updateValue('FEEDBACK_FORM_TEXT_'.$language['id_lang'], Tools::getValue('FEEDBACK_FORM_TEXT_'.$language['id_lang']));
                Configuration::updateValue('FEEDBACK_FORM_FIELDS_'.$language['id_lang'], Tools::getValue('FEEDBACK_FORM_FIELDS_'.$language['id_lang']));
            }
            Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&conf=6');
        }
        return $output.$this->renderForm();
    }

    public function hookHeader($params)
    {
        $this->context->controller->addJS(array(
            ($this->_path).'views/js/feedbackform.js',
            _PS_JS_DIR_.'validate.js'
        ));
        $this->context->controller->addCSS(($this->_path).'views/css/feedbackform.css', 'all');
    }

    public function hookFooter($params)
    {
        $id_product = (int)Tools::getValue('id_product');
        $this->context->smarty->assign(array(
            'title' => Configuration::get('FEEDBACK_FORM_TITLE_'.$this->context->language->id),
            'text' => Configuration::get('FEEDBACK_FORM_TEXT_'.$this->context->language->id),
            'fields' => $this->getFields(),
            'id_product' => $id_product ? $id_product : 0
        ));

        return $this->display(__FILE__, 'views/templates/front/feedback-form.tpl');
    }

    public function ajaxCall()
    {
        $fields = $this->getFields();
        $text = '<table>';
        foreach($_GET as $key => $value) {
            if(!preg_match('/field_/', $key))
                continue;

            $text .= '<tr>';
            $id = str_replace('field_', '', $key);
            $text .= '<td>';
            $text .= $fields[$id][1];
            $text .= '</td>';
            $text .= '<td>';
            $text .= $value;
            $text .= '</td>';
            $text .= '</tr>';
        }
        if(isset($_GET['id']) && $id = $_GET['id']) {
            $product = new Product((int)$id, $this->context->language->id, $this->context->shop->id);
            $link = new Link();
            $text .= '<tr>';
            $text .= '<td>';
            $text .= $this->l('Product name');
            $text .= '</td>';
            $text .= '<td>';
            $text .= $product->name;
            $text .= '</td>';
            $text .= '</tr>';
            $text .= '<tr>';
            $text .= '<td>';
            $text .= $this->l('Product link');
            $text .= '</td>';
            $text .= '<td>';
            $text .= $link->getProductLink($product);
            $text .= '</td>';
            $text .= '</tr>';
        }
        $text .= '</table>';
        $subject = 'feedback form ' . Configuration::get('PS_SHOP_NAME');
        $headers  = "Content-type: text/html; charset=windows-1251 \r\n";
        if(mail(Configuration::get('FEEDBACK_FORM_EMAIL'), $subject, $text, $headers)) {
            $return = array('hasError' => 0, 'message' => $this->displayConfirmation($this->l('You request send')));
        } else {
            $return = array('hasError' => 1, 'error' => $this->displayError($this->l('Error email sending!!')));
        }
        die(Tools::jsonEncode($return));

    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Email'),
                        'name' => 'FEEDBACK_FORM_EMAIL',
                        'desc' => $this->l('Email address where data will come from the form'),
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Title'),
                        'name' => 'FEEDBACK_FORM_TITLE',
                        'desc' => $this->l('Title of your form'),
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Field'),
                        'name' => 'FEEDBACK_FORM_FIELDS',
                        'desc' => $this->l('Your form fields'),
                    ),
                    array(
                        'type' => 'text',
                        'lang' => true,
                        'label' => $this->l('Text of your form'),
                        'name' => 'FEEDBACK_FORM_TEXT',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitFeedbackForm';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);
        $result['FEEDBACK_FORM_EMAIL'] = Tools::getValue('FEEDBACK_FORM_EMAIL', Configuration::get('FEEDBACK_FORM_EMAIL'));
        foreach ($languages as $language) {
            $result['FEEDBACK_FORM_TITLE'][$language['id_lang']] = Tools::getValue('FEEDBACK_FORM_TITLE_'.$language['id_lang'], Configuration::get('FEEDBACK_FORM_TITLE_'.$language['id_lang']));
            $result['FEEDBACK_FORM_FIELDS'][$language['id_lang']] = Tools::getValue('FEEDBACK_FORM_FIELDS_'.$language['id_lang'], Configuration::get('FEEDBACK_FORM_FIELDS_'.$language['id_lang']));
            $result['FEEDBACK_FORM_TEXT'][$language['id_lang']] = Tools::getValue('FEEDBACK_FORM_TEXT_'.$language['id_lang'], Configuration::get('FEEDBACK_FORM_TEXT_'.$language['id_lang']));
        }
        return $result;
    }

    protected function getFields() {
        $fields = Configuration::get('FEEDBACK_FORM_FIELDS_'.$this->context->language->id);
        $fields = explode('|', $fields);
        foreach ($fields as $key => &$field) {
            $field = explode(':', $field);
            if(isset($field[4]) && $field[4]) {
                $field[4] = explode(',', $field[4]);
            }
        }
        return $fields;
    }

    protected function unsetConfiguration() {
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            if(!Configuration::deleteByName('FEEDBACK_FORM_TITLE_'.$language["id_lang"]) ||
                !Configuration::deleteByName('FEEDBACK_FORM_TEXT_'.$language["id_lang"]) ||
                !Configuration::deleteByName('FEEDBACK_FORM_FIELDS_'.$language["id_lang"]))
                return false;
        }

        return true;
    }
    protected function setConfiguration() {
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            if(!Configuration::updateValue('FEEDBACK_FORM_TITLE_'.$language["id_lang"], 'SEND YOUR FEEDBACK') ||
                !Configuration::updateValue('FEEDBACK_FORM_TEXT_'.$language["id_lang"], '') ||
                !Configuration::updateValue('FEEDBACK_FORM_FIELDS_'.$language["id_lang"], 'text:Название фирмы:1:isName|text:Телефон для связи:1:isPhoneNumber|text:Контактное лицо:1:isName|text:Электронная почта:1:isEmail|text:Должность:1:isName|textarea:Сообщение:1:isMessage'))
                return false;
        }
        return true;
    }
}