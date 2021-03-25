<?php

/**
 * Copyright (c) 2021, Todd Lininger Design, LLC
 * All rights reserved.
 * 
 * This source code is licensed under the BSD-style license found in the
 * LICENSE file in the root directory of this source tree.
 */

namespace ToddLininger\AstraLoginActivity\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AddressRegistry;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use \Magento\Framework\Filesystem\DirectoryList;
use ToddLininger\AstraLoginActivity\Helper\Data as HelperAstraLoginActivity;

class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var DirectoryList
     */
    protected $dir;

    /**
     * @var HelperAstraLoginActivity
     */
    protected $helperAstraLoginActivity;

    /**
     * AccountManagement constructor.
     * @param CustomerFactory $customerFactory
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Random $mathRandom
     * @param Validator $validator
     * @param ValidationResultsInterfaceFactory $validationResultsDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerMetadataInterface $customerMetadataService
     * @param CustomerRegistry $customerRegistry
     * @param PsrLogger $logger
     * @param Encryptor $encryptor
     * @param ConfigShare $configShare
     * @param StringHelper $stringHelper
     * @param CustomerRepositoryInterface $customerRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param DataObjectProcessor $dataProcessor
     * @param Registry $registry
     * @param CustomerViewHelper $customerViewHelper
     * @param DateTime $dateTime
     * @param CustomerModel $customerModel
     * @param ObjectFactory $objectFactory
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     * @param DirectoryList $dir
     * @param HelperAstraLoginActivity $helperAstraLoginActivity
     * @param CredentialsValidator|null $credentialsValidator
     * @param DateTimeFactory|null $dateTimeFactory
     * @param AccountConfirmation|null $accountConfirmation
     * @param SessionManagerInterface|null $sessionManager
     * @param SaveHandlerInterface|null $saveHandler
     * @param CollectionFactory|null $visitorCollectionFactory
     * @param SearchCriteriaBuilder|null $searchCriteriaBuilder
     * @param AddressRegistry|null $addressRegistry
     * @param GetCustomerByToken|null $getByToken
     * @param AllowedCountries|null $allowedCountriesReader
     * @param SessionCleanerInterface|null $sessionCleaner
     */
    public function __construct(
        CustomerFactory $customerFactory,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        CustomerRegistry $customerRegistry,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        DirectoryList $dir,
        HelperAstraLoginActivity $helperAstraLoginActivity,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        AccountConfirmation $accountConfirmation = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null,
        SearchCriteriaBuilder $searchCriteriaBuilder = null,
        AddressRegistry $addressRegistry = null,
        GetCustomerByToken $getByToken = null,
        AllowedCountries $allowedCountriesReader = null,
        SessionCleanerInterface $sessionCleaner = null
    )
    {
        parent::__construct(
            $customerFactory,
            $eventManager,
            $storeManager,
            $mathRandom,
            $validator,
            $validationResultsDataFactory,
            $addressRepository,
            $customerMetadataService,
            $customerRegistry,
            $logger,
            $encryptor,
            $configShare,
            $stringHelper,
            $customerRepository,
            $scopeConfig,
            $transportBuilder,
            $dataProcessor,
            $registry,
            $customerViewHelper,
            $dateTime,
            $customerModel,
            $objectFactory,
            $extensibleDataObjectConverter,
            $credentialsValidator,
            $dateTimeFactory,
            $accountConfirmation,
            $sessionManager,
            $saveHandler,
            $visitorCollectionFactory,
            $searchCriteriaBuilder,
            $addressRegistry,
            $getByToken,
            $allowedCountriesReader,
            $sessionCleaner
        );
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->eventManager = $eventManager;
        $this->dir = $dir;
        $this->helperAstraLoginActivity = $helperAstraLoginActivity;
    }

    /**
     * @inheritdoc
     */
    public function authenticate($username, $password)
    {
        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            $this->sendErrorRequest($username);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        $customerId = $customer->getId();
        if ($this->getAuthentication()->isLocked($customerId)) {
            $this->sendErrorRequest($username);
            throw new UserLockedException(__('The account is locked.'));
        }
        try {
            $this->getAuthentication()->authenticate($customerId, $password);
            $this->sendSuccessRequest($customer, $username);

        } catch (InvalidEmailOrPasswordException $e) {
            $this->sendErrorRequest($username);
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }
        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            $this->sendErrorRequest($username);
            throw new EmailNotConfirmedException(__("This account isn't confirmed. Verify and try again."));
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    /**
     * Get authentication
     *
     * @return AuthenticationInterface
     */
    private function getAuthentication()
    {
        if (!($this->authentication instanceof AuthenticationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Customer\Model\AuthenticationInterface::class
            );
        } else {
            return $this->authentication;
        }
    }

    /**
     * @param $customer
     * @param String $username
     */
    protected function sendSuccessRequest($customer, $username)
    {
        $astra_path = $this->dir->getRoot() . '/astra/';
        if ($this->helperAstraLoginActivity->isEnableCustomerLoginTracking() && is_dir($astra_path)) {
            require_once($astra_path . 'Astra.php');
            require_once($astra_path . 'libraries/API_connect.php');
            $client_api = new \Api_connect();
            $user = array(
                'user_login' => $username,
                'user_email' => $customer->getEmail(),
                'display_name' => $customer->getFirstname(). ' ' .$customer->getLastname(),
            );
            $client_api->send_request("has_loggedin", array("user" => $user, "success" => 1), "magento");
        }
    }

    /**
     * @param String $username
     */
    protected function sendErrorRequest($username)
    {
        $astra_path = $this->dir->getRoot() . '/astra/';
        if ($this->helperAstraLoginActivity->isEnableCustomerLoginTracking() && is_dir($astra_path)) {
            require_once($astra_path . 'Astra.php');
            require_once($astra_path . 'libraries/API_connect.php');
            $client_api = new \Api_connect();
            $client_api->send_request("has_loggedin", array("username" => $username, "success" => 0), "magento");
        }
    }
}
