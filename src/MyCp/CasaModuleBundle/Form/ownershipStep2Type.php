<?php

namespace MyCp\CasaModuleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ownershipStep2Type extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('own_name')
            ->add('own_licence_number')
            ->add('own_mcp_code')
            ->add('own_address_street')
            ->add('own_address_number')
            ->add('own_address_between_street_1')
            ->add('own_address_between_street_2')
            ->add('own_mobile_number')
            ->add('own_homeowner_1')
            ->add('own_homeowner_2')
            ->add('own_phone_code')
            ->add('own_phone_number')
            ->add('own_email_1')
            ->add('own_email_2')
            ->add('own_category')
            ->add('own_type')
            ->add('own_facilities_breakfast')
            ->add('own_facilities_breakfast_price')
            ->add('own_facilities_dinner')
            ->add('own_facilities_dinner_price_from')
            ->add('own_facilities_dinner_price_to')
            ->add('own_facilities_parking')
            ->add('own_facilities_parking_price')
            ->add('own_facilities_notes')
            ->add('own_langs')
            ->add('own_water_jacuzee')
            ->add('own_water_sauna')
            ->add('own_water_piscina')
            ->add('own_description_bicycle_parking')
            ->add('own_description_pets')
            ->add('own_description_laundry')
            ->add('own_description_internet')
            ->add('own_geolocate_x')
            ->add('own_geolocate_y')
            ->add('own_top_20')
            ->add('own_commission_percent')
            ->add('own_ranking')
            ->add('own_comment')
            ->add('own_not_recommendable')
            ->add('own_sended_to_team')
            ->add('own_saler')
            ->add('own_visit_date')
            ->add('own_creation_date')
            ->add('own_publish_date')
            ->add('own_last_update')
            ->add('own_rating')
            ->add('own_maximun_number_guests')
            ->add('own_minimum_price')
            ->add('own_maximum_price')
            ->add('own_comments_total')
            ->add('own_rooms_total')
            ->add('own_sync_st')
            ->add('own_selection')
            ->add('own_inmediate_booking')
            ->add('own_automatic_mcp_code')
            ->add('own_mcp_code_generated')
            ->add('own_cubacoupon')
            ->add('own_sms_notifications')
            ->add('own_address_province')
            ->add('own_address_municipality')
            ->add('own_destination')
            ->add('own_status')
            ->add('own_owner_photo')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MyCp\mycpBundle\Entity\ownership'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mycp_mycpbundle_ownership_step2';
    }
}
