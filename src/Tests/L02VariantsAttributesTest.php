<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace Splash\Tests;

use Splash\Tests\Tools\ObjectsCase;

use Splash\Client\Splash;

use Splash\Local\Core\PluginManger;
use Splash\Local\Objects\Core\MultilangTrait;

/**
 * @abstract    Local Objects Test Suite - Specific Verifications for Products Variants Attributes.
 *
 * @author SplashSync <contact@splashsync.com>
 */
class L02VariantsAttributesTest extends ObjectsCase
{
    use PluginManger;
    
    /**
     * @dataProvider sequencesProvider
     */
    public function testCreateAttributeGroup($Sequence)
    {
        /** Check if WooCommerce is active **/
        if (!Splash::local()->hasWooCommerce()) {
            return $this->markTestSkipped("WooCommerce Plugin is Not Active");
        }
        
        $this->loadLocalTestSequence($Sequence);
        
        //====================================================================//
        //   Load Known Attribute Group
        $Name   =   "CustomVariant";
        $Code   =   strtolower($Name);
        
        //====================================================================//
        //   Ensure Attribute Group is Deleted
        $this->ensureAttributeGroupIsDeleted($Code);
        
        //====================================================================//
        //   Create a New Attribute Group
        $AttributeGroupId   =   Splash::object("Product")->addAttributeGroup($Code, $Name);
        $AttributeGroup     =   wc_get_attribute($AttributeGroupId);
        
        //====================================================================//
        //   Verify Attribute Group
        $this->assertNotEmpty($AttributeGroupId);
        $this->assertNotEmpty($AttributeGroup->id);
        $this->assertEquals("pa_" . $Code, $AttributeGroup->slug);
        $this->assertEquals($Name, $AttributeGroup->name);
        
        //====================================================================//
        //   Verify Attributes Group Identification
        $this->assertEquals(
            $AttributeGroup->id,
            Splash::object("Product")->getAttributeGroupByCode($Code)
        );
        
        //====================================================================//
        //   Create a New Attribute Values
        for ($i=0; $i<5; $i++) {
            $Value  =   "Custom Value " . $i;
            
            //====================================================================//
            //   Verify Attributes Value Identification
            $this->assertFalse(
                Splash::object("Product")->getAttributeByCode($AttributeGroup->slug, $Value)
            );
            
            
            //====================================================================//
            //   Create Attribute Value
            $AttributeId =  Splash::object("Product")
                    ->addAttributeValue($AttributeGroup->slug, $Value);
            $this->assertNotEmpty($AttributeId);
            $Attribute  =   get_term($AttributeId);
            $this->assertNotEmpty($Attribute->term_id);
            $this->assertContains($Value, $Attribute->name);
            
            //====================================================================//
            //   Verify Attributes Value Identification
            $this->assertEquals(
                $Attribute->term_id,
                Splash::object("Product")->getAttributeByCode(wc_attribute_taxonomy_name($Code), $Value)
            );
        }
    }
    
    public function testIdentifyAttributeGroup()
    {
        /** Check if WooCommerce is active **/
        if (!Splash::local()->hasWooCommerce()) {
            return $this->markTestSkipped("WooCommerce Plugin is Not Active");
        }
        
        //====================================================================//
        //   Load Known Attribute Group
        $AttributeGroupId   =   Splash::object("Product")->getAttributeGroupByCode("CustomVariant");
        $AttributeGroup     =   wc_get_attribute($AttributeGroupId);
        $this->assertNotEmpty($AttributeGroupId);
        $this->assertContains("pa_", $AttributeGroup->slug);
        $this->assertContains(strtolower("CustomVariant"), $AttributeGroup->slug);
        //====================================================================//
        //   Load UnKnown Attribute Group
        $UnknownGroupId     =   Splash::object("Product")->getAttributeGroupByCode(base64_encode(uniqid()));
        $this->assertFalse($UnknownGroupId);
    }
    
    private function ensureAttributeGroupIsDeleted($Code)
    {
        global $wp_taxonomies;
        
        //====================================================================//
        //   Load Known Attribute Group
        $AttributeGroupId   =   Splash::object("Product")->getAttributeGroupByCode($Code);
        //====================================================================//
        //   Delete Attribute Group
        if ($AttributeGroupId) {
            wc_delete_attribute($AttributeGroupId);
            clean_taxonomy_cache(wc_attribute_taxonomy_name($Code));
            unset($wp_taxonomies[wc_attribute_taxonomy_name($Code)]);
        }
        //====================================================================//
        //   Load Known Attribute Group
        $DeletedGroupId   =   Splash::object("Product")->getAttributeGroupByCode($Code);
        $this->assertFalse($DeletedGroupId);
    }
    
    public function sequencesProvider()
    {
        $Result =   array();
        self::setUp();
        //====================================================================//
        // Check if Local Tests Sequences are defined
        if (!is_null(Splash::local()) && method_exists(Splash::local(), "TestSequences")) {
            foreach (Splash::local()->testSequences("List") as $Sequence) {
                $Result[]   =   array($Sequence);
            }
        } else {
            $Result[]   =   array( 1 => "None");
        }
        return $Result;
    }
}