<?php

namespace StdNumCentroamerica\Documents\CostaRica;

use StdNumCentroamerica\Documents\AbstractDocument;

/** DIMEX (Migración): 11 o 12 dígitos sin cero inicial. Nivel: formato. */
class Dimex extends AbstractDocument
{
    protected const PATTERN = '/^[1-9]\d{10,11}$/';
}
