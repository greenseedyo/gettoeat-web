<?php

class ShiftHelper
{
    public static function getAdjustmentTypeText(int $adjustment_type)
    {
        switch ($adjustment_type) {
        case Shift::ADJUSTMENT_PASS:
            return '略過';
        case Shift::ADJUSTMENT_TAKEOUT:
            return '營收取出';
        case Shift::ADJUSTMENT_ADD:
            return '錢櫃補款';
        }
    }
}
