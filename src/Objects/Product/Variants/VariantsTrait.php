<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Product Variation Data Access
 */
trait VariantsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Variation Fields using FieldFactory
     */
    protected function buildVariationFields()
    {
        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//
        
        //====================================================================//
        // Product Variation List - Product Link
        $this->fieldsFactory()->Create(self::objects()->Encode("Product", SPL_T_ID))
            ->Identifier("id")
            ->Name(__("Children"))
            ->InList("variants")
            ->MicroData("http://schema.org/Product", "Variants")
            ->isNotTested();
        
        //====================================================================//
        // Product Variation List - Product SKU
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("sku")
            ->Name(__("SKU"))
            ->InList("variants")
            ->MicroData("http://schema.org/Product", "VariationName")
            ->isReadOnly();
        
        //====================================================================//
        // Product Variation List - Variation Attribute
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("attribute")
            ->Name(__("Attribute"))
            ->InList("variants")
            ->MicroData("http://schema.org/Product", "VariationAttribute")
            ->isReadOnly();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getVariationsFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "variants", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Check if Product is Variant Product
        if (!$this->isVariantsProduct()) {
            unset($this->in[$key]);
            
            return;
        }
        
wc_delete_product_transients($this->product->get_parent_id());        
var_dump($this->baseProduct->get_children());        
var_dump(wc_get_product($this->product->get_parent_id())->get_children());        
        //====================================================================//
        // READ Fields
        foreach ($this->baseProduct->get_children() as $index => $productId) {
            //====================================================================//
            // SKIP Current Variant When in PhpUnit/Travis Mode
            // Only Existing Variant will be Returned
            if (!empty(Splash::input('SPLASH_TRAVIS')) && ($productId == $this->object->ID)) {
                continue;
            }
            //====================================================================//
            // Read requested Field
            switch ($fieldId) {
                case 'id':
                    $value = self::objects()->Encode("Product", $productId);
                    
                    break;
                case 'sku':
                    $value = get_post_meta($productId, "_sku", true);

                    break;
                case 'attribute':
                    $value = implode(" | ", wc_get_product($productId)->get_attributes());

                    break;
                default:
                    $value = null;
                    
                    break;
            }
            
            self::lists()->Insert($this->out, "variants", $fieldId, $index, $value);
        }
        unset($this->in[$key]);
    }
    
    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setVariationsFields($fieldName, $fieldData)
    {
        if ("variants" === $fieldName) {
            unset($this->in[$fieldName]);
        }
    }
}
