<?php

/**
 * Copyright (c) 2021, Todd Lininger Design, LLC
 * All rights reserved.
 * 
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace ToddLininger\AstraLoginActivity\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\User\Model\User;
use ToddLininger\AstraLoginActivity\Helper\Data as HelperAstraLoginActivity;

class AdminLoginSuccess implements ObserverInterface
{
    /**
     * @var HelperAstraLoginActivity
     */
    protected $helperAstraLoginActivity;

    /**
     * @var DirectoryList
     */
    protected $dir;

    /**
     * AuthObserver constructor.
     * @param HelperAstraLoginActivity $helperAstraLoginActivity
     * @param DirectoryList $dir
     */
    public function __construct(
        HelperAstraLoginActivity $helperAstraLoginActivity,
        DirectoryList $dir
    )
    {
        $this->helperAstraLoginActivity = $helperAstraLoginActivity;
        $this->dir = $dir;
    }

    public function execute(EventObserver $observer)
    {
        /** @var User $user */
        $user = $observer->getEvent()->getUser();
        $this->sendSuccessRequest($user);
    }

    /**
     * @param User $user
     */
    protected function sendSuccessRequest($user)
    {
        $astra_path = $this->dir->getRoot() . '/astra/';
        if ($this->helperAstraLoginActivity->isEnableAdminLoginTracking() && is_dir($astra_path)) {
            require_once($astra_path . 'Astra.php');
            require_once($astra_path . 'libraries/API_connect.php');
            $client_api = new \Api_connect();
            $userData = array(
                'user_login' => $user->getUserName(),
                'user_email' => $user->getEmail(),
                'display_name' => $user->getFirstname() . ' ' .$user->getLastname()
            );
            $client_api->send_request("has_loggedin", array("user" => $userData, "success" => 1), "magento");
        }
    }
}
