<?php
namespace BooklyPackages\Backend\Components\Gutenberg\PackagesList;

use Bookly\Lib as BooklyLib;

/**
 * Class Block
 * @package BooklyPackages\Backend\Components\Gutenberg\PackagesList
 */
class Block extends BooklyLib\Base\Block
{
    /**
     * @inheritdoc
     */
    public static function registerBlockType()
    {
        self::enqueueScripts( array(
            'module' => array(
                'js/packages-list-block.js' => array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor' ),
            ),
        ) );

        wp_localize_script( 'bookly-packages-list-block.js', 'BooklyPackagesListL10n', array(
            'block' => array(
                'title'       => 'Bookly - ' . __( 'Packages list', 'bookly' ),
                'description' => __( 'A custom block for displaying packages list', 'bookly' ),
            ),
        ) );

        register_block_type( 'bookly/packages-list-block', array(
            'editor_script' => 'bookly-packages-list-block.js',
        ) );
    }
}
