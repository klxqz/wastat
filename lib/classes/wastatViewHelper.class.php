<?php

class wastatViewHelper extends waAppViewHelper {

    public function priceFormat($price) {
        return number_format($price, 2, ',', ' ');
    }

    public function dateFormat($date) {
        if (!$date) {
            return false;
        }
        $time = strtotime($date);
        return date('d.m.Y', $time);
    }

    public function datetimeFormat($date) {
        if (!$date) {
            return false;
        }
        $time = strtotime($date);
        return date('d.m.Y H:i:s', $time);
    }

    public function totalSum($date_from = null, $date_to = null) {
        return wastat::totalSum($date_from, $date_to);
    }

    public function totalCount($date_from = null, $date_to = null) {
        return wastat::totalCount($date_from, $date_to);
    }

}
