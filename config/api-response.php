<?php

return [

    /*
    | --------------------------------------------------------------------------
    | Default HTTP Code for Failed Validation.
    | --------------------------------------------------------------------------
    */
    'validation_http_code' => \Illuminate\Http\Response::HTTP_BAD_REQUEST,

    /*
    |--------------------------------------------------------------------------
    | Show Validation Message
    |--------------------------------------------------------------------------
    |
    | first | all
    |
    | first => Display the first validation error message
    | all => Display all errors messages
    |
    */
    'show_validation_failed_message' => 'all',

];
