# CatalogThemeInheritFix

EDIT: Use patch from here instead of this module: https://gist.github.com/kirmorozov/38b26b88e959cb5487b0f722090749e1

Provides a workaround for issues documented here:
1. https://github.com/magento/magento2/issues/4330
2. https://github.com/magento/magento2/issues/7710
3. https://github.com/magento/magento2/issues/26295

By providing custom theme layout files which only work on custom design themes inside the main theme folder:
catalog_product_view_{normalized_theme_name}.xml
catalog_category_view_{normalized_theme_name}.xml

Cannot provide override functionality, only merge layouts.



## License

MIT
