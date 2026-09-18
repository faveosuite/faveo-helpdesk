<?php

declare(strict_types=1);

namespace Intervention\Gif;

enum DisposalMethod: int
{
    case UNDEFINED = 0;
    case NONE = 1; // overlay each frame in sequence
    case BACKGROUND = 2; // clear to background (as indicated by the logical screen descriptor)
    case PREVIOUS = 3; // restore the canvas to its previous state
}
