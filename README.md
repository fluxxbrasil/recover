# Recover

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a4d2fb1046a34ea7805cfcae0a7f8272)](https://app.codacy.com/manual/DevMagentoFluxx/recover?utm_source=github.com&utm_medium=referral&utm_content=DevMagentoFluxx/recover&utm_campaign=Badge_Grade_Dashboard)
[![StyleCI](https://github.styleci.io/repos/260533921/shield?branch=master)](https://github.styleci.io/repos/260533921)

## Extension features

Recover canceled sales by Boleto Parcelado - Fluxx
*   Recovery during checkout
*   Email recovery

## Installation

We recommend installing by [composer](README.md#via-composer), but you can also do it [manually](README.md#manual).after installation it is necessary to [enable](README.md#enable) the module.

### Composer (recommend)

``` sh
composer require fluxxbrasil/recover
```

### Manually

*   [Download](https://github.com/DevMagentoFluxx/recover/archive/master.zip)
*   On your computer unzip the file
*   Navigate to the [root directory](https://devdocs.magento.com/guides/v2.3/install-gde/basics/basics_docroot.html) of the Magento 2
*   On your server create folder Fluxx in public_html/app/code/
*   Send folder Magento2 to public_html/app/code/Fluxx

#### Enable

``` sh
php bin/magento module:enable Fluxx_Recover
bin/magento setup:upgrade --keep-generated 
```

## Configuration

Attribute Relationship Definition:

In STORES -> Configuration -> Fluxx -> Recover -> Offer at checkout

*   Enable
*   Select one or more payment methods in Applicable to payment methods

In STORES -> Configuration -> Fluxx -> Recover -> Offer by email

*   Enable
*   Select one or more payment methods in Applicable to payment methods

## Technical feature

### Module configuration
*   Package details [composer.json](composer.json).
*   Module configuration details (sequence) in [module.xml](etc/module.xml).
*   Module configuration available through Stores->Configuration [system.xml](etc/adminhtml/system.xml)

### Dependency Injection configuration
> To get more details about dependency injection configuration in Magento 2, please see [DI docs](http://devdocs.magento.com/guides/v2.0/extension-dev-guide/depend-inj.html).

## License
[Open Source License](LICENSE.txt)

