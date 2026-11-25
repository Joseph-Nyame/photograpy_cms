<?php

namespace App\Enums;

enum GalleryAccessAction: string
{
    case View = 'view';
    case Favourite = 'favourite';
    case Download = 'download';
}
