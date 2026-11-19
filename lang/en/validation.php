<?php

/*
| Mensajes de validación en INGLÉS (los demás vienen de Laravel).
*/

return [
    'accepted' => 'You must accept :attribute.',
    'after' => ':Attribute must be a date after :date.',
    'before' => ':Attribute must be a date before :date.',
    'confirmed' => 'The passwords do not match.',
    'date' => ':Attribute is not a valid date.',
    'email' => 'Enter a valid email address.',
    'image' => ':Attribute must be an image.',
    'in' => ':Attribute is not valid.',
    'integer' => ':Attribute must be a whole number.',
    'max' => [
        'numeric' => ':Attribute may not be greater than :max.',
        'file' => ':Attribute may not be larger than :max KB.',
        'string' => ':Attribute may not be longer than :max characters.',
        'array' => ':Attribute may not have more than :max items.',
    ],
    'min' => [
        'numeric' => ':Attribute must be at least :min.',
        'file' => ':Attribute must be at least :min KB.',
        'string' => ':Attribute must be at least :min characters.',
        'array' => ':Attribute must have at least :min items.',
    ],
    'password' => [
        'letters' => 'The password must contain at least one letter.',
        'mixed' => 'The password must contain upper and lower case letters.',
        'numbers' => 'The password must contain at least one number.',
        'symbols' => 'The password must contain at least one symbol.',
        'uncompromised' => 'This password appeared in a data leak. Please choose another one.',
    ],
    'regex' => 'The format of :attribute is not valid.',
    'required' => 'Please enter :attribute.',
    'string' => ':Attribute must be text.',
    'unique' => ':Attribute is already registered. Do you want to log in?',

    'attributes' => [
        'nombre' => 'your name',
        'correo' => 'your email',
        'contrasena' => 'the password',
        'telefono' => 'the phone number',
        'rol' => 'the account type',
        'nombre_comercial' => 'the agency name',
        'whatsapp' => 'the WhatsApp number',
        'direccion' => 'the address',
        'descripcion' => 'the description',
        'fecha' => 'the date',
        'personas' => 'the number of people',
        'cantidad_personas' => 'the number of people',
        'comentario' => 'the comment',
        'precio' => 'the price',
        'destino' => 'the destination',
    ],
];
