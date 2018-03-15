<?php

class noteestiamnotationModuleFrontController extends FrontController
{
    public function initContent()
    {
        parent::initContent();

        $product_id = (int)$_POST['id_product'];
        $note = (int)$_POST['note'];
        $comment = $_POST['comment'];
        $user_id = $this->context->customer->id;

        $db = Db::getInstance();

        //insert note in db
        $query = 'INSERT INTO `' . _DB_PREFIX_ . 'notation`(`id_notation`, `note`, `comment`, `id_product`, `id_customer`) VALUES ("?", ' . $note . ',"' . $comment . '", ' . $product_id . ', ' . $user_id . ' )';
        $db->execute($query);

        //redirect to same page
        if (($back = Tools::getValue('back')) && $back == Tools::secureReferrer($back))
            Tools::redirect(html_entity_decode($back));
        Tools::redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
    }
}

?>
