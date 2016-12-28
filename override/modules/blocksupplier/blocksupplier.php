<?php

class BlockSupplierOverride extends BlockSupplier
{
    public function hookHomeManufacturer($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    function hookDisplayLeftColumn($params)
	{
        $id_lang = (int)Context::getContext()->language->id;
        $suppliers = Supplier::getSuppliers(false, $id_lang);
        foreach ($suppliers as &$supplier)
        {
            $supplier['image'] = $this->context->language->iso_code.'-default';
            if (file_exists(_PS_SUPP_IMG_DIR_.$supplier['id_supplier'].'-'.ImageType::getFormatedName('medium').'.jpg'))
                $supplier['image'] = $supplier['id_supplier'];
        }

        if (!$this->isCached('blocksupplier.tpl', $this->getCacheId()))
			$this->smarty->assign(array(
				'suppliers' => $suppliers,
				'link' => $this->context->link,
				'text_list' => Configuration::get('SUPPLIER_DISPLAY_TEXT'),
				'text_list_nb' => Configuration::get('SUPPLIER_DISPLAY_TEXT_NB'),
				'form_list' => Configuration::get('SUPPLIER_DISPLAY_FORM'),
				'display_link_supplier' => Configuration::get('PS_DISPLAY_SUPPLIERS')
			));
		return $this->display(__FILE__, 'blocksupplier.tpl', $this->getCacheId());
	}

}
