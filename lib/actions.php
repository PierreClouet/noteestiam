<?php


class Actions
{
    public function updateSettings($module)
    {
        $output = '';
        $notename = strval(Tools::getValue('NOTEESTIAM_NAME'));

        if (!$notename || empty($notename) || !Validate::isGenericName($notename)) {
            $output .= $module->displayError(
                $module->l('Invalid config')
            );
        } else {
            Configuration::updateValue(
                'NOTEESTIAM_NAME',
                $notename
            );
            $output .= $module->displayConfirmation(
                $module->l('Settings updated')
            );
        }

        return $output;
    }

    public function getNote($module, $db)
    {
        $output = '';
        $note = null;

        $id_notation = (int)Tools::getValue('id_notation');
        $notes = $db->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'notation` WHERE id_notation = ' . $id_notation);

        if (!$notes) {
            $output .= $module->displayError($db->getMsgError());
        } else {
            $note = $notes[0];
        }

        return array(
            'output' => $output,
            'note' => $note
        );
    }

    public function deleteNote($module, $db)
    {
        $output = '';
        $id_notation = (int)Tools::getValue('id_notation');
        $result = $db->delete('notation', 'id_notation = ' . $id_notation);

        if (!$result) {
            $output .= $module->displayError($db->getMsgError());
        } else {
            $output .= $module->displayConfirmation($module->l('Note deleted'));
        }

        return $output;
    }
}

?>
