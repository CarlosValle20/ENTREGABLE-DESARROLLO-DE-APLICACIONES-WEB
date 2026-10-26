<?php

return [
    'current_password' => 'La contraseña actual no es correcta.',
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'Ingresa un correo electrónico válido.',
    'unique' => 'Este :attribute ya está registrado.',
    'confirmed' => 'La confirmación de la contraseña no coincide.',
    'regex' => 'El DNI debe contener exactamente 8 dígitos.',
    'string' => 'El campo :attribute debe ser texto.',
    'boolean' => 'El valor de :attribute no es válido.',
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.'],
    'max' => ['string' => 'El campo :attribute no puede superar :max caracteres.'],
    'password' => ['mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.', 'numbers' => 'La contraseña debe incluir al menos un número.'],
    'attributes' => ['name' => 'nombre completo', 'dni' => 'DNI', 'email' => 'correo electrónico', 'password' => 'contraseña', 'token' => 'token', 'remember' => 'recordar sesión'],
];
