<?php

namespace App\Enum;

/**
 * Représente le “type d’usage” d’une planche :
 *  – INDIVIDUELLE : pochettes individuelles
 *  – FRATRIE      : pochettes fratries
 *  – SEULE        : planche libre (utilisable hors pochette)
 */
enum PlancheUsage: string
{
    case INDIVIDUELLE = 'individuelle';
    case FRATRIE      = 'fratrie';
    case SEULE        = 'seule';
}
