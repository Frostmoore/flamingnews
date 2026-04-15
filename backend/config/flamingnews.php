<?php

return [

    /*
     | ACTIVE=true  → registrazioni chiuse (solo utenti esistenti possono fare login)
     | ACTIVE=false → registrazioni aperte
     */
    'registrations_closed' => filter_var(env('ACTIVE', false), FILTER_VALIDATE_BOOLEAN),

];
