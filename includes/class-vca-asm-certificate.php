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

        $user_registration = $this->parseRegistration();

        // Prepare PDF and load

        $pdf = new FPDI();

        $pdf->AddPage();

        $pdf->setSourceFile(VCA_ASM_ABSPATH . '/pdf-templates/volunteer_certificate.pdf');

        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 0, 0, true);

        // Write Name of supporter

        $pdf->SetFont('Arial', 'B', '20');
        $pdf->SetTextColor(255,255,255);

        $pdf->SetY(27);
        $pdf->Cell(0, 20, utf8_decode($this->user->first_name . " " . $this->user->last_name), 0, 1, 'C');

        // Write date of registration

        $pdf->SetFont('Arial', '', '13');
        $pdf->SetTextColor(0,0,0);

        $pdf->SetXY(70.2, 67.2);
        $pdf->Write(8, $user_registration);

        // Write active city

        $pdf->SetX(0);
        $pdf->Cell(0, 20, utf8_decode($this->regions[$this->user->city]), 0, 1, 'C');

        // Write date of creation

        $pdf->SetFont('Arial', '', '11');

        $pdf->SetXY(39, 160);
        $pdf->Write(0, date('d.m.Y'));

        // Write Thanks

        $pdf->SetXY(165, 156);
        $pdf->Rotate(10);
        $pdf->Write(0, $this->user->first_name);

        $pdf->Output('viva_con_agua_ehrenamtsbestaetigung.pdf', 'D');

    }

    private function parseRegistration()
    {

        $user_registration = strtotime($this->user->user_registered);

        $month = date('F', $user_registration);
        $registration_month = _x( $month, 'Months', 'vca-asm' );
        $registration_year = date('Y', $user_registration);

        $registration_string = $registration_month  . ' ' . $registration_year;
        $registration_string = $registration_year;

        return $registration_string;

    }

}