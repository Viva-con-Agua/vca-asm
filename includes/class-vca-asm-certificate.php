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

    private $user;
    private $cerificate;

    private $regions;

    private $language_templates = array(
        'de' => array(
            'date_format' => 'd.m.Y',
            'output_filename' => 'viva_con_agua_ehrenamtsbestaetigung',
            'registration' => array(
                'x' => 70, 'y' => 86.5
            ),'date' => array(
                'x' => 41.1, 'y' => 177.3
            ),'thankyou' => array(
                'x' => 161.7, 'y' => 176, 'rotation' => 10
            )
        ),
        'en' => array(
            'date_format' => 'm-d-Y',
            'output_filename' => 'viva_con_agua_volunteer_certificate',
            'registration' => array(
                'x' => 121, 'y' => 86.5
            ),'date' => array(
                'x' => 41.1, 'y' => 177.3
            ),'thankyou' => array(
                'x' => 161.7, 'y' => 176, 'rotation' => 10
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
    public function getCertificate()
    {

        if (!empty($this->cerificate)) {
            return $this->cerificate;
        }

        $lang = $this->getLanguage();
        $user_registration = $this->parseRegistration($lang);

        // Prepare PDF and load

        $pdf = new FPDI();
        $pdf->AddFont('MuseoSans500', '', 'MuseoSans500.php');
        $pdf->AddFont('MuseoSans300', '', 'MuseoSans300.php');

        $pdf->AddPage();

        //$lang = 'en';

        $pdf->setSourceFile(VCA_ASM_ABSPATH . '/pdf-templates/volunteer_certificate_' . $lang . '.pdf');

        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);

        // Write Name of supporter

        $pdf->SetFont('MuseoSans500', '', '20');
        $pdf->SetTextColor(255,255,255);

        $pdf->SetY(22);
        $pdf->Cell(0, 20, utf8_decode($this->user->first_name . " " . $this->user->last_name), 0, 1, 'C');

        // Write date of registration

        $pdf->SetFont('MuseoSans300', '', '13');
        $pdf->SetTextColor(0,0,0);

        $registration_position = $this->language_templates[$lang]['registration'];
        $pdf->SetXY($registration_position['x'], $registration_position['y']);
        $pdf->Write(9, $user_registration);

        // Write active city

        $pdf->SetX(0);
        $pdf->Cell(0, 20, utf8_decode($this->regions[$this->user->city]) . '.', 0, 1, 'C');

        // Write date of creation

        $pdf->SetFont('MuseoSans300', '', '12');

        $date_position = $this->language_templates[$lang]['date'];
        $pdf->SetXY($date_position['x'], $date_position['y']);
        $pdf->Write(0, date($this->language_templates[$lang]['date_format']));

        // Write Thanks

        $pdf->SetFont('MuseoSans500', '', '11');

        $thankyou_position = $this->language_templates[$lang]['thankyou'];
        $pdf->SetXY($thankyou_position['x'], $thankyou_position['y']);
        $pdf->Rotate($thankyou_position['rotation']);
        $pdf->Write(0, $this->user->first_name);

        $pdf->Output($this->language_templates[$lang]['output_filename'] . '.pdf', 'D');

    }

    private function parseRegistration($lang)
    {

        $user_registration = strtotime($this->user->user_registered);
        $registration_year = date('Y', $user_registration);

        switch ($lang) {
            case 'de':
                $registration_string = $registration_year;
                break;
            case 'en':
            default:
                $registration_string = $registration_year;
                break;
        }

        return $registration_string;

    }

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