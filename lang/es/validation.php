<?php

/*
| Mensajes de validación en español.
| «:attribute» se cambia por el nombre del campo (ver 'attributes' al final).
*/

return [
    'accepted' => 'Debes aceptar :attribute.',
    'array' => ':Attribute no es válido.',
    'boolean' => ':Attribute debe ser sí o no.',
    'confirmed' => 'Las contraseñas no coinciden.',
    'current_password' => 'La contraseña no es correcta.',
    'date' => ':Attribute no es una fecha válida.',
    'after' => ':Attribute debe ser posterior a :date.',
    'before' => ':Attribute debe ser anterior a :date.',
    'decimal' => ':Attribute debe tener :decimal decimales.',
    'email' => 'Escribe un correo electrónico válido.',
    'exists' => ':Attribute no existe.',
    'image' => ':Attribute debe ser una imagen.',
    'in' => ':Attribute no es válido.',
    'integer' => ':Attribute debe ser un número entero.',
    'max' => [
        'numeric' => ':Attribute no puede ser mayor que :max.',
        'file' => ':Attribute no puede pesar más de :max KB.',
        'string' => ':Attribute no puede tener más de :max caracteres.',
        'array' => ':Attribute no puede tener más de :max elementos.',
    ],
    'mimes' => ':Attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => ':Attribute debe ser al menos :min.',
        'file' => ':Attribute debe pesar al menos :min KB.',
        'string' => ':Attribute debe tener al menos :min caracteres.',
        'array' => ':Attribute debe tener al menos :min elementos.',
    ],
    'numeric' => ':Attribute debe ser un número.',
    'password' => [
        'letters' => 'La contraseña debe tener al menos una letra.',
        'mixed' => 'La contraseña debe tener mayúsculas y minúsculas.',
        'numbers' => 'La contraseña debe tener al menos un número.',
        'symbols' => 'La contraseña debe tener al menos un símbolo.',
        'uncompromised' => 'Esta contraseña apareció en una filtración de datos. Elige otra.',
    ],
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'Escribe :attribute.',
    'string' => ':Attribute debe ser texto.',
    'unique' => ':Attribute ya está registrado. ¿Quieres iniciar sesión?',
    'url' => ':Attribute debe ser una dirección web válida.',

    // Nombres de los campos tal como los ve la persona
    'attributes' => [
        'nombre' => 'tu nombre',
        'correo' => 'tu correo',
        'contrasena' => 'la contraseña',
        'telefono' => 'el teléfono',
        'rol' => 'el tipo de cuenta',
        'nombre_comercial' => 'el nombre de la agencia',
        'whatsapp' => 'el WhatsApp',
        'direccion' => 'la dirección',
        'descripcion' => 'la presentación',
        'fecha' => 'la fecha',
        'personas' => 'la cantidad de personas',
        'cantidad_personas' => 'la cantidad de personas',
        'comentario' => 'el comentario',
        'precio' => 'el precio',
        'destino' => 'el destino',
    ],
];
