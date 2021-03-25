<?php

/**
 * Copyright (c) 2021, Todd Lininger Design, LLC
 * All rights reserved.
 * 
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace ToddLininger\AstraLoginActivity\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Search Suite Autocomplete config data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_ENABLED_ADMIN_LOGIN_TRACKING = 'toddlininger_astraLoginActivity/login_tracking/enable_admin_login_tracking';
    const XML_ENABLED_CUSTOMER_LOGIN_TRACKING = 'toddlininger_astraLoginActivity/login_tracking/enable_customer_login_tracking';

    /**
     * Data constructor.
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnableAdminLoginTracking($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_ENABLED_ADMIN_LOGIN_TRACKING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnableCustomerLoginTracking($storeId = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_ENABLED_CUSTOMER_LOGIN_TRACKING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
