<?php

$term = request('term');   // $_GET is not populated when the request is dispatched internally
$users = app\User::where('email', 'LIKE', '%' . $term . '%')->where('active', '=', 1)->where('role', '=', 'user')->get();
$json = [];

foreach ($users as $user) {

    $json[] = [
    'value' => $user["email"],
    'label' => 'Name: '.$user["first_name"] .' '.$user["last_name"].' | '.('Email: <'.$user["email"].'>'),
    'email' => $user["email"],
    'user_name' => $user["user_name"],
    'first_name' => $user["first_name"],
    'last_name' => $user["last_name"],
    'country_code' => $user["country_code"],
    'mobile' => $user["mobile"],
    'phone_number' => $user["phone_number"]

    ];
}

echo json_encode($json);
