<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include __DIR__ . '/lib/actions.php';

class NoteEstiam extends Module
{
    public function __construct()
    {
        $this->name = 'noteestiam';
        $this->tab = 'Notation';
        $this->version = '1.0.0';
        $this->author = 'ESTIAM';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array(
            'min' => '1.5',
            'max' => '1.7'
        );
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Notation');
        $this->description = $this->l('Notation de produits');

        $this->confirmUninstall = $this->l(
            'Are you sure you want to uninstall?'
        );

        if (!Configuration::get('NOTEESTIAM_NAME')) {
            $this->warning = $this->l('No name provided');
        }

        $this->actions = new Actions();
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install()) {
            return false;
        }
        $this->registerHook('displayNotation');

        if (!$this->registerHook('displayNotation')) {
            return false;
        }

        if (!Configuration::updateValue('NOTEESTIAM_NAME', 'Estiam')) {
            return false;
        }

        $db = Db::getInstance();
        $db->execute('CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'notation` (
            `id_notation` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `note` INT NOT NULL,
            `comment` VARCHAR(255) NULL,
            `id_product` INT UNSIGNED NOT NULL,
            `id_customer` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`id_notation`),
            CONSTRAINT FOREIGN KEY (`id_product`) REFERENCES ps_product(`id_product`),
            CONSTRAINT FOREIGN KEY (`id_customer`) REFERENCES ps_customer(`id_customer`)
        )ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;');

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        if (!Configuration::deleteByName('NOTEESTIAM_NAME')) {
            return false;
        }

        $this->unregisterHook('DisplayNotation');

        if (!$this->unregisterHook('DisplayOverrideTemplate')) {
            return false;
        }

        $db = Db::getInstance();
        $db->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'notation`;');

        return true;
    }

    public function getContent()
    {
        $db = Db::getInstance();
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $this->actions->updateSettings($this);
        } elseif (Tools::isSubmit('deleteps_notation')) {
            $output .= $this->actions->deleteNote($this, $db);
        }

        $output .= $this->displaySettingsForm();
        $output .= $this->displayNoteList();

        return $output;
    }

    public function displaySettingsForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form = array(array());
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings')
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Note name'),
                    'name' => 'NOTEESTIAM_NAME',
                    'size' => 255,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => $helper->currentIndex . '&token=' . $helper->token
            ),
            'back' => array(
                'desc' => $this->l('Back to list'),
                'href' => AdminController::$currentIndex . '&token=' . $helper->token
            )
        );

        $helper->fields_value['NOTEESTIAM_NAME'] = Configuration::get('NOTEESTIAM_NAME');

        return $helper->generateForm($fields_form);
    }

    public function displayNoteList()
    {
        $db = Db::getInstance();
        $notes = $db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'notation`;');

        $fields_list = array(
            'id_notation' => array(
                'title' => $this->l('Note ID'),
                'width' => 140,
                'type' => 'text'
            ),
            'note' => array(
                'title' => $this->l('Note'),
                'width' => 255,
                'type' => 'text'
            ),
            'comment' => array(
                'title' => $this->l('Commentaire'),
                'width' => 255,
                'type' => 'text'
            ),
            'id_product' => array(
                'title' => $this->l('Produit noté'),
                'width' => 255,
                'type' => 'text',
                'callback' => 'getProductName',
                'callback_object' => $this
            )
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = array('delete');
        $helper->identifier = 'id_notation';
        $helper->show_toolbar = true;
        $helper->title = $this->l('Published notes');
        $helper->table = 'ps_notation';

        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        return $helper->generateList($notes, $fields_list);
    }

    public function hookDisplayNotation($params)
    {

        $id_product = Tools::getValue('id_product');
        $user_id = $this->context->customer->id;
        $moyenne = 0;
        $countNotes = 0;
        $canUserNote = 0;

        $db = Db::getInstance();

        //return all notes for the current product
        $notes = $db->executeS('SELECT note FROM `' . _DB_PREFIX_ . 'notation` WHERE `id_product` = ' . $id_product . ';');

        //check if user has already note the current product
        if ($user_id) {
            $uniqueNote = $db->executeS('SELECT note FROM `' . _DB_PREFIX_ . 'notation` WHERE `id_product` = ' . $id_product . ' AND `id_customer` = ' . $user_id . ';');
            $canUserNote = count($uniqueNote);
        }

        //get all comments
        $comments = $db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'customer` INNER JOIN `' . _DB_PREFIX_ . 'notation` ON ps_customer.`id_customer` = ps_notation.`id_customer` WHERE `id_product` = ' . $id_product . ';');

        if (count($notes)) {
            $int = array();

            //convert note string into int type
            foreach ($notes as $note) {
                $int[] = (int)$note['note'];
            }

            $totalNotes = array_sum($int);
            $countNotes = count($int);
            $moyenne = $totalNotes / $countNotes;
        }

        $this->context->smarty->assign(
            array(
                'title' => Configuration::get('NOTEESTIAM_NAME'),
                'moyenne' => $moyenne,
                'nb_notes' => $countNotes,
                'notes' => $comments,
                'link' => $this->context->link->getModuleLink('noteestiam', 'notation'),
                'logged' => $this->context->customer->isLogged(),
                'user_id' => $user_id,
                'can_user_note' => $canUserNote
            )
        );

        return $this->display(__FILE__, 'notation.tpl');
    }

    public function getProductName($param)
    {
        $db = Db::getInstance();
        $result = $db->executeS('SELECT name FROM `' . _DB_PREFIX_ . 'product_lang` INNER JOIN `' . _DB_PREFIX_ . 'product` ON ps_product_lang.`id_product` = ps_product.`id_product` WHERE ps_product.`id_product` = ' . $param . ';');

        foreach ($result as $el) {
            $name = $el['name'];
        }
        return $name;
    }
}
