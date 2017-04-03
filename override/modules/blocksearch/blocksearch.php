<?php

class BlockSearchOverride extends BlockSearch
{
    public function hookMenuBottom($params)
    {
        $params['mobile'] = true;
        return $this->hookTop($params);
    }

    public function hookTop($params)
    {
        $key = $this->getCacheId('blocksearch-top'.((!isset($params['hook_mobile']) || !$params['hook_mobile']) ? '' : '-hook_mobile'));
        if (Tools::getValue('search_query') || !$this->isCached('blocksearch-top.tpl', $key))
        {
            $this->calculHookCommon($params);
            $this->smarty->assign(array(
                    'mobile' => $params['mobile'] ? 1 : 0,
                    'blocksearch_type' => 'top',
                    'search_query' => (string)Tools::getValue('search_query')
                )
            );
        }
        Media::addJsDef(array('blocksearch_type' => 'top'));
        return $this->display(__FILE__, 'blocksearch-top.tpl', Tools::getValue('search_query') ? null : $key);
    }

    private function calculHookCommon($params)
    {
        $this->smarty->assign(array(
            'ENT_QUOTES' =>		ENT_QUOTES,
            'search_ssl' =>		Tools::usingSecureMode(),
            'ajaxsearch' =>		Configuration::get('PS_SEARCH_AJAX'),
            'instantsearch' =>	Configuration::get('PS_INSTANT_SEARCH'),
            'self' =>			dirname(__FILE__),
        ));

        return true;
    }
}
