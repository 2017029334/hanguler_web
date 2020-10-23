<?php
namespace BooklyPackages\Lib\Notifications\Assets\Package;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Notifications\Assets\Base;
use Bookly\Lib\Utils;
use BooklyPackages\Lib;

/**
 * Class Codes
 * @package BooklyPackages\Lib\Notifications\Assets\Package
 */
class Codes extends Base\Codes
{
    // Core
    public $cancellation_reason;
    public $category_name;
    public $client_address;
    public $client_email;
    public $client_first_name;
    public $client_last_name;
    public $client_name;
    public $client_phone;
    public $package_life_time;
    public $package_name;
    public $package_price;
    public $package_size;
    public $service_duration;
    public $service_info;
    public $service_name;
    public $service_price;
    public $service_tax;        // Frontend/Notification codes service_tax contain tax of extra items
    public $service_tax_rate;
    public $staff_email;
    public $staff_info;
    public $staff_name;
    public $staff_phone;
    public $staff_photo;
    public $total_price_no_tax;
    public $total_tax;

    /** @var BooklyLib\CartItem */
    protected $cart_item;

    /**
     * Constructor.
     *
     * @param Lib\Entities\Package $package
     * @param string               $type
     * @param string               $reason
     */
    public function __construct( Lib\Entities\Package $package, $type = 'created', $reason = '' )
    {
        $customer = BooklyLib\Entities\Customer::find( $package->getCustomerId() );
        $service  = BooklyLib\Entities\Service::find( $package->getServiceId() );
        /** @var BooklyLib\Entities\Service $sub_service */
        $sub_service = current( $service->getSubServices() );
        $category    = $sub_service ? BooklyLib\Entities\Category::find( $sub_service->getCategoryId() ) : null;

        $this->client_address    = $customer->getAddress();
        $this->client_email      = $customer->getEmail();
        $this->client_first_name = $customer->getFirstName();
        $this->client_last_name  = $customer->getLastName();
        $this->client_name       = $customer->getFullName();
        $this->client_phone      = $customer->getPhone();
        $this->package_name      = $service ? $service->getTitle() : '';
        $this->package_size      = $service ? $service->getPackageSize() : '';
        $this->package_life_time = $service ? $service->getPackageLifeTime() : '';
        $this->category_name     = $category ? $category->getName() : '';
        $this->service_info      = $sub_service ? $sub_service->getInfo() : '';
        $this->service_name      = $sub_service ? $sub_service->getTitle() : '';
        $this->service_duration  = $sub_service ? $sub_service->getDuration() : '';
        $this->service_price     = $sub_service ? $sub_service->getPrice() : '';
        $this->cancellation_reason = $reason;

        if ( $sub_service ) {
            $this->cart_item = new BooklyLib\CartItem();
            $this->cart_item
                ->setNumberOfPersons( 1 )
                ->setServiceId( $sub_service->getId() )
                ->setStaffIds( $package->getStaffId() ? array( $package->getStaffId() ) : array() )
                ->setLocationId( BooklyLib\Proxy\Locations::prepareStaffLocationId( $package->getLocationId(), $package->getStaffId() ) ?: null );
        }

        if ( $package->getStaffId() == null ) {
            $this->package_price = $service->getPrice();
            $this->staff_email   = '';
            $this->staff_info    = '';
            $this->staff_name    = __( 'Any', 'bookly' );
            $this->staff_phone   = '';
            $this->staff_photo   = '';
        } else {
            $staff         = BooklyLib\Entities\Staff::find( $package->getStaffId() );
            $staff_service = BooklyLib\Entities\StaffService::query()
                ->select( 'price' )
                ->where( 'staff_id', $package->getStaffId() )
                ->where( 'service_id', $service->getId() )
                ->where( 'location_id', BooklyLib\Proxy\Locations::prepareStaffLocationId( $package->getLocationId(), $package->getStaffId() ) ?: null )
                ->fetchRow();
            $staff_photo   = wp_get_attachment_image_src( $staff->getAttachmentId(), 'full' );

            $this->package_price = $staff_service ? $staff_service['price'] : '';
            $this->staff_email   = $staff->getEmail();
            $this->staff_info    = $staff->getInfo();
            $this->staff_name    = $staff->getFullName();
            $this->staff_phone   = $staff->getPhone();
            $this->staff_photo   = $staff_photo ? $staff_photo[0] : '';
        }
    }

    /**
     * @inheritdoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        $staff_photo = '';
        if ( $format === 'html' ) {
            if ( $this->staff_photo != '' ) {
                // Staff photo as <img> tag.
                $staff_photo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( $this->staff_photo ),
                    esc_attr( $this->staff_name )
                );
            }
        }

        $codes = array(
            '{cancellation_reason}' => $this->cancellation_reason,
            '{category_name}'     => $this->category_name,
            '{client_address}'    => $format === 'html' ? nl2br( $this->client_address ) : $this->client_address,
            '{client_email}'      => $this->client_email,
            '{client_first_name}' => $this->client_first_name,
            '{client_last_name}'  => $this->client_last_name,
            '{client_name}'       => $this->client_name,
            '{client_phone}'      => $this->client_phone,
            '{package_life_time}' => $this->package_life_time,
            '{package_name}'      => $this->package_name,
            '{package_price}'     => Utils\Price::format( $this->package_price ),
            '{package_size}'      => $this->package_size,
            '{service_duration}'  => Utils\DateTime::secondsToInterval( $this->service_duration ),
            '{service_info}'      => $format === 'html' ? nl2br( $this->service_info ) : $this->service_info,
            '{service_name}'      => $this->service_name,
            '{service_price}'     => Utils\Price::format( $this->service_price ),
            '{service_tax}'       => Utils\Price::format( 0 ),
            '{service_tax_rate}'  => '',
            '{staff_email}'       => $this->staff_email,
            '{staff_info}'        => $format === 'html' ? nl2br( $this->staff_info ) : $this->staff_info,
            '{staff_name}'        => $this->staff_name,
            '{staff_phone}'       => $this->staff_phone,
            '{staff_photo}'       => $staff_photo,
            '{total_price_no_tax}' => Utils\Price::format( $this->package_price ),
            '{total_tax}'         => Utils\Price::format(0 ),
        );

        if ( $this->cart_item && BooklyLib\Config::taxesActive() ) {
            $service_id = $this->cart_item->getService()->getId();
            $item_tax   = BooklyLib\Proxy\Taxes::getItemTaxAmount( $this->cart_item );
            $rates = BooklyLib\Proxy\Taxes::getServiceTaxRates();
            $rate  = array_key_exists( $service_id, $rates ) ? $rates[ $service_id ] : 0;
            $total_tax = BooklyLib\Proxy\Taxes::calculateTax( $this->package_price, $rate );
            $codes['{service_tax}']      = Utils\Price::format( $item_tax );
            $codes['{service_tax_rate}'] = $rate;
            $codes['{total_price_no_tax}'] = Utils\Price::format( $this->package_price - $total_tax );
            $codes['{total_tax}']        = Utils\Price::format( $total_tax );
        }

        // Add replace codes.
        return array_merge( $replace_codes, $codes );
    }
}