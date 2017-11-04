<?php

require_once VCA_ASM_ABSPATH . '/lib/fpdf181/fpdf.php';
require_once VCA_ASM_ABSPATH . '/lib/fpdi162/fpdi.php';

/**
 * Created by PhpStorm.
 * User: tobias
 * Date: 14.09.2017
 * Time: 13:55
 */
class VcA_ASM_Certificate
{

    /** @var  wp_user $user */
    private $user;

    /**
     * @var string[]
     */
    private $regions;

    /**
     * @var array
     */
    private $template_informations = array(
        'de' => array(
            'date_format' => 'd.m.Y',
            'output_filename' => 'viva_con_agua_ehrenamtsbestaetigung'
        ),
        'en' => array(
            'date_format' => 'm-d-Y',
            'output_filename' => 'viva_con_agua_volunteer_certificate'
        ),
        'positions' => array(
            'registration' => array(
                'x' => 10, 'y' => 86.5
            ),'date' => array(
                'x' => 20, 'y' => 177.3
            ),'thankyou' => array(
                'x' => 156, 'y' => 168, 'rotation' => 10
            )
        )
    );

    /**
     * class-vca-asm-certificate constructor.
     */
    public function __construct()
    {
        /** @var vca_asm_geography $vca_asm_geography */
        global $vca_asm_geography;
        $this->regions = $vca_asm_geography->get_names();
    }

    /**
     * @param WP_User $user
     */
    public function setUser($user)
    {
        $this->user = get_user_to_edit( $user->ID );
    }

    /**
     * @return mixed
     */
    public function outputCertificate()
    {

        $lang = $this->getLanguage();
        $user_registration = $this->parseRegistration($lang);

        // Prepare PDF and load

        /** @var fpdf $pdf */
        $pdf = new FPDI();
        $pdf->AddFont('MuseoSans500', '', 'MuseoSans500.php');
        $pdf->AddFont('MuseoSans300', '', 'MuseoSans300.php');

        $pdf->AddPage();

        $pdf->setSourceFile(VCA_ASM_ABSPATH . '/pdf-templates/volunteer_certificate.pdf');

        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);

        // Write Name of supporter

        $pdf->SetFont('MuseoSans500', '', '20');
        $pdf->SetTextColor(255,255,255);

        $pdf->SetY(22);
        $pdf->Cell(0, 20, utf8_decode($this->user->first_name . " " . $this->user->last_name), 0, 1, 'C');

        // Write content

        $textContent = __("is a voluntary supporter of Viva con Agua since %since% in our regional group in %city%.\n\nViva con Agua is a network of people and organisations committed to\nestablish access to clean drinking water and basic sanitation for all humans\nworldwide.\n\nThere are thousands of voluntary supporters which are using creative and\njoyful activities to raise awareness for the global issues WATER, SANITATION\nand HYGIENE (short WASH) and simultaneously raise funds for our water\nprojects.", 'vca-asm');

        $textContent = str_replace('%city%', $this->regions[$this->user->city], $textContent);
        $textContent = str_replace('%since%', $user_registration, $textContent);

        $pdf->SetFont('MuseoSans300', '', '13');
        $pdf->SetTextColor(0,0,0);

        $registration_position = $this->template_informations['positions']['registration'];
        $pdf->SetXY($registration_position['x'], $registration_position['y']);
        $pdf->MultiCell(0, 5, utf8_decode($textContent), 0, 'C');

        // Write date of creation

        $textCity = __("Hamburg, %date%\n\n\nMario Dresing, Coordination and supervision of voluntary", 'vca-asm');
        $textCity = str_replace('%date%', date($this->template_informations[$lang]['date_format']), $textCity);

        $pdf->SetFont('MuseoSans300', '', '12');

        $date_position = $this->template_informations['positions']['date'];
        $pdf->SetXY($date_position['x'], $date_position['y']);
        $pdf->MultiCell(0, 5, utf8_decode($textCity));

        // Write Thanks

        $textThanks = __("Thanks\n%name%", 'vca-asm');
        $textThanks = str_replace('%name%', $this->user->first_name, $textThanks);

        $pdf->SetFont('MuseoSans500', '', '11');

        $thankyou_position = $this->template_informations['positions']['thankyou'];
        $pdf->SetXY($thankyou_position['x'], $thankyou_position['y']);
        $pdf->Rotate($thankyou_position['rotation']);
        $pdf->MultiCell(22, 5, utf8_decode($textThanks), 0, 'C');

        $pdf->Output($this->template_informations[$lang]['output_filename'] . '.pdf', 'D');

    }

    /**
     * @param string $lang
     * @return string
     */
    private function parseRegistration($lang)
    {

        $user_registration = strtotime($this->user->user_registered);
        $registration_year = date('Y', $user_registration);
        $registration_month = date('m', $user_registration);

        switch ($lang) {
            case 'de':
                $registration_string = $registration_month . '.' . $registration_year;
                break;
            case 'en':
            default:
                $registration_string = $registration_year . '-' . $registration_month;
                break;
        }

        return $registration_string;

    }

    /**
     * @return string
     */
    private function getLanguage()
    {
        $lang = get_bloginfo('language');
        $lang_parts = explode('-', $lang);

        switch ($lang_parts[0]) {
            case 'de':
                $real_language = 'de';
                break;
            case 'en':
            default:
                $real_language = 'en';
                break;
        }

        return $real_language;
    }

}