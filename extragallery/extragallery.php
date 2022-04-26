<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright  2007-2019 PrestaShop SA
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */


 if(!defined('_PS_VERSION_')) {
     exit;
 }


class ExtraGallery extends Module{

    public function __construct() {
        $this->name = "extragallery";
        $this->tab = "front_office_features";
        $this->version = "1.0";
        $this->author = "Gabrielius Mazeikis";
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            "min" => "1.6",
            "max" => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l("Extra Gallery");
        $this->description = $this->l("Add an additional image gallery to the product page");
        $this->confirmUninstall = $this->l("Please do not uninstall me...");
    }

    public function install()
    {
        
        return $this->dbInstall() && $this->installtab() && parent::install() && $this->registerHook('displayFooterProduct');
    }

    public function uninstall()
    {
        return $this->dbUninstall() && $this->uninstalltab() && parent::uninstall() && $this->unregisterHook('displayFooterProduct') && $this->deleteFiles();
    }
 
    protected function dbInstall() {
        $sqlCreate = "CREATE TABLE `" . _DB_PREFIX_ . "extra_gallery` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `product_id` varchar(255) DEFAULT NULL,
            `image` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
        return Db::getInstance()->execute($sqlCreate);
    }

    protected function dbUninstall() {
        $sql = "DROP TABLE " . _DB_PREFIX_ . "extra_gallery";
        return Db::getInstance()->execute($sql);
    }

    public function installtab() {
        $tab = new Tab();
        $tab->class_name = "AdminGallery";
        $tab->module = $this->name;
        $tab->id_parent = (int)Tab::getIdFromClassName('DEFAULT');
        $tab->icon = 'image';
        $languages = Language::getLanguages();
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Extra Gallery');
        }

        try {
            $tab->save();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }

        return true;

    }
    public function uninstalltab() {
        $idTab = (int)Tab::getClassNameById('AdminGallery');
        if ($idTab) {
            $tab = new Tab($idTab);
            try {
                $tab->delete();
            } catch (Exception $e) {
                echo $e->getMessage();
                return false;
            }
        }
        return true;
    }

    public function deleteFiles() {
        $files = glob(dirname(__FILE__) . DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'*'); // get all file names
        foreach($files as $file){
            if(is_file($file)) {
                unlink($file); // delete files
            }
        }
        return true;
    }

    public function hookDisplayFooterProduct($params) {
        $product_id = Tools::getValue('id_product');
        $sql = new DbQuery();
        $sql->select('*')->from('extra_gallery')->where('product_id = '. $product_id);
        $maximumImages = Configuration::get('IMAGE_COUNT');
        $labeltext = Configuration::get('LABEL_TEXT');
        $data = Db::getInstance()->executeS($sql);
        if($data) {
            $this->context->smarty->assign([ // Make sure to customise maximum images and label text in configuration
                'data' => $data,
                'maxImg' => $maximumImages,
                'labelText' => $labeltext
            ]);
        }
        else {
            $this->context->smarty->assign([
                'data' => 0
            ]);
        }
        
        return $this->display(__FILE__, 'views/templates/hook/gallery.tpl');
    }
    public function getContent() {
        
        $output = "";
        if(Tools::isSubmit('submit' . $this->name)) {
            $imagecount = Tools::getValue('imagecount');
            $labeltext = Tools::getValue('labeltext');
            if($imagecount && !empty($imagecount)) {
                Configuration::updateValue('IMAGE_COUNT', Tools::getValue("imagecount"));
                $output .= $this->displayConfirmation($this->trans('Image count saved succcessfully'));
            }
            else { 
                $output .= $this->displayError($this->trans('Error: Image count has not been saved'));
            }
            if($labeltext && !empty($labeltext)) {
                Configuration::updateValue('LABEL_TEXT', Tools::getValue("labeltext"));
                $output .= $this->displayConfirmation($this->trans('Gallery label text saved succcessfully'));
            }
            else { 
                $output .= $this->displayError($this->trans('Error: Gallery label text was not saved'));
            }
        }
        return $output . $this->displayForm();
    }

    public function displayForm() {
        $defaultLang = (int) Configuration::get('PS_LANG_DEFAULT');

        $fields[0]['form'] = [
            'legend' => [
                'title' => $this->trans('Gallery module settings')
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Gallery label text'),
                    'name' => 'labeltext',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Maximum amount of images per product'),
                    'name' => 'imagecount',
                    'size' => 20,
                    'required' => true
                ]
                ],
            'submit' => [
                'title' => $this->trans('Save'),
                'class' => 'btn btn-primary pull-right'
            ]
            ];

            $helper = new HelperForm();
            $helper->module = $this;
            $helper->name_controller = $this->name;
            $helper->token = Tools::getAdminTokenLite('AdminModules');
            $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
            $helper->default_form_language = $defaultLang;
            $helper->allow_employee_form_lang = $defaultLang;

            $helper->title = $this->displayName;
            $helper->show_toolbar = true;
            $helper->submit_action = 'submit' . $this->name;
            $helper->toolbar_btn = [
                'save' => [
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                    '&token' . Tools::getAdminTokenLite('AdminModules'),
                ],
                'back' => [
                    'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                    'desc' => $this->l('Back to list')
                ]
                ];
            $helper->fields_value['imagecount'] = Configuration::get('IMAGE_COUNT');
            $helper->fields_value['labeltext'] = Configuration::get('LABEL_TEXT');
            return $helper->generateForm($fields);
    }
    
}
