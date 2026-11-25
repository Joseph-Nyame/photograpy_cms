<?php

namespace App\Enums;

enum GalleryStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
}
