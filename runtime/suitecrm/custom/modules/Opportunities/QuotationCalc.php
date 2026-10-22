<?php
// custom/modules/Opportunities/QuotationCalc.php
// Quotation maths: net selling price shown on the opportunity.
class QuotationCalc
{
    public static function netSellingPrice(array $q)
    {
        $fees = $q['inspectionFee'] + $q['adminFee'] + $q['numberPlate'] + $q['colourEffect'];
        $promos = $q['brandPromotion'] + $q['csEuroPromotion'];
        return $q['vehiclePrice'] + $fees - $promos + $q['salesConsultantPromotion'];
    }
}
