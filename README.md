# Dependency Detective for Magento 2
Never trust a Magento 2 module composer.json or etc/module.xml to correctly report all dependencies as these files often leave out many required modules.

This is frustrating if you are trying to remove modules (bloat) from Magento as you don't truly know what you can remove.

The Dependency Detective helps by allowing you to see all modules referenced from a Magento module and see what files it uses that are missing from your system.


## Install using Composer
`composer require fishpig/magento2-dependency-detective`

## Check Dependencies
`vendor/bin/deps_detective Magento_Catalog`
