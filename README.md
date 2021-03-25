# Astra Login Activity extension for Magento 2

<p style="text-align:center;">
<img src="https://img.shields.io/badge/magento-2.4.x-brightgreen.svg?logo=magento&longCache=true&style=flat-square" alt="Supported Magento Versions" />
<a href="https://opensource.org/licenses/BSD-3-Clause" target="_blank"><img src="https://img.shields.io/badge/license-BSD3-blue.svg?longCache=true&style=flat-square" alt="BSD-3-Clause License" /></a>
</p>

This extension adds the necessary API code for monitoring Magento 2 login activity in your Astra firewall dashboard. It allows you to choose whether you want to monitor admin logins, customer logins or both, showing both failed and successful login attempts for each. From the Astra dashboard you have the option to **Trust** or **Block** the user's IP address, if desired.

The extension was developed independently of Astra, based on their [login activity API](https://www.getastra.com/kb/documentation/integration-login-activity-for-custom-php-websites/). Astra Website Security service ([pricing](https://vrlps.co/7KT2B3k/cp)) is required in order for login activity to be tracked.

## Usage

As long as Astra has been installed on the server the extension will automatically begin monitoring admin logins. If you would also like to track customer logins, you can enable them in the configuration options under _Security > Astra Login Protection_.

![Configuration Options](https://raw.githubusercontent.com/toddliningerdesign/astra-login-activity/master/docs/configuration.png)

Once installed, monitored logins should immediately begin appearing in the _Activity_ section of your Astra dashboard. From there you have the option to **Trust** or **Block** the user's IP address, if desired.

![Astra Activity Dashboard](https://raw.githubusercontent.com/toddliningerdesign/astra-login-activity/master/docs/astra-dashboard.png)

## Installation

Tested with Magento 2.4.1, 2.4.2

### Composer

```bash
composer config repositories.toddlininger/astra git git@github.com:toddliningerdesign/astra-login-activity.git
composer require toddlininger/astraLoginActivity
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

### Manual Installation

Upload the files to `app/code/ToddLininger/AstraLoginActivity` in your root Magento directory, then run:

```bash
composer require toddlininger/astraLoginActivity
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## License

Astra Login Activity extension for Magento 2 is licensed under the BSD-3-Clause License - see the LICENSE file for details